<?php

namespace App\Services;

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
     * Look up a resident by NFC card UID from the Bataeno Pass API.
     */
    public function findByCardUid(string $uid): ?array
    {
        $cacheKey = 'bataeno_resident_uid_' . md5($uid);
        $ttl      = (int) env('BATAENO_CACHE_TTL', 300);

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $token = Session::get(self::SESSION_KEY);

        if (! $token) {
            throw new RuntimeException(
                'Your Bataeno session is missing. Please log out and log back in via Bataeno Pass.'
            );
        }

        $resident = $this->attemptLookup($token, $uid);

        if ($resident) {
            Cache::put($cacheKey, $resident, $ttl);
        }

        return $resident;
    }

    /**
     * Fetch the authenticated user's own profile using their OAuth access token.
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
    // Internal
    // -------------------------------------------------------------------------

    protected function attemptLookup(string $token, string $uid): ?array
    {
        $client = Http::withToken($token)->acceptJson()->timeout(8);

        foreach (["/api/users?uuid={$uid}", "/api/users?card_uid={$uid}"] as $endpoint) {
            $res = $client->get($this->baseUrl . $endpoint);

            if ($res->status() === 401) {
                // Token expired — clear session so login prompt appears
                Session::forget(self::SESSION_KEY);

                throw new RuntimeException(
                    'Your Bataeno session has expired. Please log out and log back in.'
                );
            }

            if (! $res->successful()) {
                Log::debug('Bataeno: non-success response', [
                    'endpoint' => $endpoint,
                    'status'   => $res->status(),
                ]);
                continue;
            }

            $json = $res->json();
            $raw  = $json['data'] ?? $json;

            if (isset($raw[0])) {
                return $this->normalise($raw[0]);
            }

            $normalised = $this->normalise($raw);
            if ($normalised) return $normalised;
        }

        return null;
    }

    public function normalise(mixed $raw): ?array
    {
        if (! is_array($raw) || empty($raw)) return null;

        $d = $raw['data'] ?? $raw;

        if (! is_array($d) || empty($d)) return null;

        $first  = $d['first_name']  ?? $d['given_name'] ?? $d['fname'] ?? null;
        $middle = $d['middle_name'] ?? $d['mname']      ?? null;
        $last   = $d['last_name']   ?? $d['surname']    ?? $d['lname'] ?? null;

        $name = trim(implode(' ', array_filter([$first, $middle, $last])))
            ?: ($d['name'] ?? $d['email'] ?? null);

        return [
            'first_name'     => $first,
            'middle_name'    => $middle,
            'last_name'      => $last,
            'name'           => $name,
            'address'        => $d['barangay_name'] ?? $d['address']        ?? null,
            'birthdate'      => $d['birthday']      ?? $d['birthdate']      ?? null,
            'contact_number' => $d['mobile']        ?? $d['phone']          ?? $d['contact_number'] ?? null,
            'sex'            => $d['sex']            ?? $d['gender']         ?? null,
            'civil_status'   => $d['civil_status']  ?? null,
            'birth_place'    => $d['birth_place']   ?? $d['place_of_birth'] ?? null,
            'email'          => $d['email']          ?? null,
            'uuid'           => $d['uuid']           ?? null,
            'raw'            => $d,
            '_source'        => 'bataeno',
        ];
    }
}