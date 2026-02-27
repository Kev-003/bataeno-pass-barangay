<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use RuntimeException;

class BataenoService
{
    protected string $baseUrl;

    const SESSION_KEY = 'bataeno_access_token';

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('services.bataeno.base_url', env('BATAENO_PASS_BASE_URL', 'https://bataeno-pass.bataan.gov.ph')),
            '/'
        );
    }

    /**
     * Store the official's token in their session after OAuth login.
     */
    public function storeOfficialToken(string $accessToken): void
    {
        Session::put(self::SESSION_KEY, $accessToken);
    }

    /**
     * PRIMARY LOOKUP — called when a card is tapped.
     *
     * Flow:
     *   1. Look up the UUID in the local database (residents must register first).
     *   2. If found locally, verify against Bataeno /api/verify-card/{uid}
     *      to get the live profile photo and confirm the card is still valid.
     *   3. Merge local DB data with Bataeno verification response.
     *   4. If not found locally, return null — card is not registered here.
     */
    public function findByCardUid(string $uid): ?array
    {
        // Step 1: Local DB lookup — this is the source of truth
        $user = User::where('uuid', $uid)->first();

        if (! $user) {
            Log::info('NFC tap: UUID not registered in local DB', ['uid' => $uid]);
            return null; // Resident hasn't registered at this barangay yet
        }

        // Step 2: Verify card against Bataeno and get live photo/signature
        $verified = $this->verifyCard($uid);

        // Step 3: Merge — local DB is the base, Bataeno verification enriches it
        return $this->buildResidentPayload($user, $verified);
    }

    /**
     * Hit the Bataeno /api/verify-card/{uid} endpoint to confirm the card
     * is valid and retrieve the live profile photo.
     * Returns null gracefully if the API is unreachable — local data still shows.
     */
    public function verifyCard(string $uid): ?array
    {
        $cacheKey = 'bataeno_verify_' . md5($uid);
        $ttl      = (int) env('BATAENO_CACHE_TTL', 300);

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $token = Session::get(self::SESSION_KEY);

        if (! $token) {
            Log::debug('BataenoService: no session token, skipping card verification', ['uid' => $uid]);
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(8)
                ->get("{$this->baseUrl}/api/verify-card/{$uid}");

            if ($response->status() === 401) {
                Session::forget(self::SESSION_KEY);
                Log::warning('Bataeno verify-card: token expired', ['uid' => $uid]);
                return null;
            }

            if (! $response->successful()) {
                Log::debug('Bataeno verify-card: non-success', [
                    'uid'    => $uid,
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();

            // API returns { valid: true, message: '...', user: { ... } }
            if (! ($data['valid'] ?? false)) {
                Log::info('Bataeno verify-card: card marked invalid', ['uid' => $uid]);
                return null;
            }

            $result = $data['user'] ?? $data;
            Cache::put($cacheKey, $result, $ttl);

            return $result;

        } catch (\Exception $e) {
            Log::warning('Bataeno verify-card request failed', [
                'uid'   => $uid,
                'error' => $e->getMessage(),
            ]);
            return null; // Degrade gracefully — local data still works
        }
    }

    /**
     * Fetch the authenticated user's own profile using their OAuth access token.
     * Called right after the OAuth callback.
     */
    public function fetchAuthenticatedProfile(string $accessToken): ?array
    {
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->timeout(8)
            ->get("{$this->baseUrl}/api/user");

        if ($response->failed()) {
            Log::warning('Bataeno: /api/user fetch failed', ['status' => $response->status()]);
            return null;
        }

        $data = $response->json('data') ?? $response->json();
        return $this->normalise($data);
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Build the final resident payload by merging local DB + Bataeno verification.
     * Local DB is always the base. Bataeno adds photo, signature, and validity.
     */
    protected function buildResidentPayload(User $user, ?array $bataeno): array
    {
        $name = trim(implode(' ', array_filter([
            $user->first_name,
            $user->middle_name,
            $user->last_name,
        ]))) ?: $user->email;

        // Profile photo: prefer Bataeno live photo, fall back to nothing
        $profilePhoto = $bataeno['profile_photos']['medium']
            ?? $bataeno['profile_photos']['original']
            ?? null;

        return [
            // Identity — from local DB
            'first_name'      => $user->first_name,
            'middle_name'     => $user->middle_name,
            'last_name'       => $user->last_name,
            'name'            => $name,
            'email'           => $user->email,
            'uuid'            => $user->uuid,

            // Personal details — from local DB (populated during OAuth login)
            'birthdate'       => $user->date_of_birth ?? $user->birthdate ?? null,
            'birthdate_formal'=> $bataeno['birthday_formal'] ?? null,
            'birth_place'     => $user->place_of_birth ?? null,
            'sex'             => $user->gender ?? null,
            'civil_status'    => $user->civil_status ?? null,
            'contact_number'  => $user->contact_number ?? $user->phone ?? $user->mobile ?? null,

            // Address — from local DB
            'address'         => implode(', ', array_filter([
                                    $user->address ?? null,
                                    $user->barangay_name ?? null,
                                    $user->municity_name ?? null,
                                ])),
            'barangay_name'   => $user->barangay_name ?? null,
            'municity_name'   => $user->municity_name ?? null,

            // From Bataeno verification (may be null if API unreachable)
            'profile_photo'   => $profilePhoto,
            'card_valid'      => $bataeno !== null,
            'signature'       => $bataeno['signature'] ?? null,

            // Raw data for anything else the blade needs
            'raw'             => $user->toArray(),
            'bataeno_raw'     => $bataeno,
            '_source'         => $bataeno ? 'local+bataeno' : 'local',
        ];
    }

    /**
     * Normalise a raw Bataeno API response — used for /api/user during OAuth login.
     */
    public function normalise(mixed $raw): ?array
    {
        if (! is_array($raw) || empty($raw)) return null;

        $d = $raw['data'] ?? $raw['user'] ?? $raw;

        if (! is_array($d) || empty($d)) return null;

        if (empty($d['first_name']) && empty($d['full_name']) && empty($d['email'])) {
            return null;
        }

        $first  = $d['first_name']  ?? null;
        $middle = $d['middle_name'] ?? null;
        $last   = $d['last_name']   ?? null;

        $name = $d['full_name']
            ?? trim(implode(' ', array_filter([$first, $middle, $last])))
            ?: ($d['email'] ?? null);

        return [
            'first_name'      => $first,
            'middle_name'     => $middle,
            'last_name'       => $last,
            'name'            => trim($name),
            'address'         => implode(', ', array_filter([
                                    $d['address']       ?? null,
                                    $d['barangay_name'] ?? null,
                                    $d['municity_name'] ?? null,
                                ])),
            'birthdate'       => $d['birthday']       ?? null,
            'birthdate_formal'=> $d['birthday_formal'] ?? null,
            'birth_place'     => $d['birth_place']    ?? null,
            'contact_number'  => $d['mobile_number']  ?? $d['mobile'] ?? $d['phone'] ?? null,
            'sex'             => $d['sex']             ?? null,
            'civil_status'    => $d['civil_status']   ?? null,
            'email'           => $d['email']           ?? null,
            'uuid'            => $d['uuid']            ?? null,
            'profile_photo'   => $d['profile_photos']['medium']
                                    ?? $d['profile_photos']['original']
                                    ?? null,
            'barangay_name'   => $d['barangay_name']  ?? null,
            'municity_name'   => $d['municity_name']  ?? null,
            'raw'             => $d,
            '_source'         => 'bataeno',
        ];
    }
}