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

    protected function handleApiError(\Illuminate\Http\Client\Response $response, string $prefix): void
    {
        if ($response->status() === 401) {
            Session::forget(self::SESSION_KEY);
            throw new \RuntimeException('Bataan Portal session expired. Please log in again.');
        }

        $data = $response->json();
        $message = is_array($data) ? ($data['message'] ?? null) : null;

        if (!$message) {
            $message = 'API request failed (Status: ' . $response->status() . ')';
            // If it's HTML/raw, just take a snippet of the body
            if (!$response->json()) {
                $snippet = substr($response->body(), 0, 200);
                $message .= " - Response: " . $snippet . (strlen($response->body()) > 200 ? '...' : '');
            }
        }

        $details = "";
        if (is_array($data) && !empty($data['errors'])) {
            $errorDetails = [];
            foreach ($data['errors'] as $field => $errors) {
                $errorDetails[] = $field . ": " . implode(", ", (array) $errors);
            }
            $details = "\n\n📋 Validation Errors:\n• " . implode("\n• ", $errorDetails);
        }

        throw new \RuntimeException("{$prefix}: {$message}{$details}");
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

        if (!$user) {
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
        $ttl = (int) env('BATAENO_CACHE_TTL', 300);

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $token = Session::get(self::SESSION_KEY);

        if (!$token) {
            Log::debug('BataenoService: no session token, skipping card verification', ['uid' => $uid]);
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(8)
                ->get("{$this->baseUrl}/api/verify-card/{$uid}");

            if ($response->status() === 401) {
                $this->handleApiError($response, 'Card Verification');
            }

            if (!$response->successful()) {
                $this->handleApiError($response, 'Card Verification');
            }

            $data = $response->json();

            // API returns { valid: true, message: '...', user: { ... } }
            if (!($data['valid'] ?? false)) {
                throw new \RuntimeException($data['message'] ?? 'This card is marked as invalid by the Bataan Portal.');
            }

            $result = $data['user'] ?? $data;
            Cache::put($cacheKey, $result, $ttl);

            return $result;

        } catch (\Exception $e) {
            Log::error('Bataeno verify-card error', [
                'uid' => $uid,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Verify a QR code payload against the Bataeno Portal.
     * Hits /api/verify-qr
     */
    public function verifyQr(string $payload): ?array
    {
        $token = Session::get(self::SESSION_KEY);

        if (!$token) {
            Log::debug('BataenoService: no session token, skipping QR verification');
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(8)
                ->post("{$this->baseUrl}/api/verify-qr", [
                    'qr_code_payload' => $payload,
                ]);

            if ($response->status() === 401) {
                $this->handleApiError($response, 'QR Verification');
            }

            if ($response->successful()) {
                $data = $response->json();
                return $data['user'] ?? $data['data'] ?? $data;
            }

            $this->handleApiError($response, 'QR Verification');
        } catch (\Exception $e) {
            Log::error('Bataeno verify-qr error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Register or update a user locally from portal data.
     */
    public function registerToBarangay(array $portalData, int $barangayId): User
    {
        $mapped = $this->mapPortalData($portalData);

        $user = User::firstOrNew(['email' => $mapped['email']]);

        $user->fill($mapped);
        $user->barangay_id = $barangayId;

        if (!$user->exists) {
            $user->password = bcrypt(\Illuminate\Support\Str::random(16));
            $user->registered_at = now();
        }

        $user->save();

        return $user;
    }

    public function searchUsers(string $query): array
    {
        $token = Session::get(self::SESSION_KEY);

        if (!$token) {
            return [];
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(8)
                ->get("{$this->baseUrl}/api/users", [
                    'q' => $query,
                ]);

            if ($response->status() === 401) {
                $this->handleApiError($response, 'Portal Search');
            }

            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? $data;
            }

            $this->handleApiError($response, 'Portal Search');
            return []; // Unreachable but satisfies lint
        } catch (\Exception $e) {
            Log::error('Bataeno searchUsers failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Look for a user in the portal using identity fields.
     */
    public function findUserByNameAndBirthday(array $data): ?array
    {
        $token = Session::get(self::SESSION_KEY);
        if (!$token)
            return null;

        try {
            // Search by name
            $name = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
            $results = $this->searchUsers($name);

            foreach ($results as $user) {
                $portalBday = $user['birthday'] ?? $user['birthdate'] ?? null;
                $searchBday = $data['date_of_birth'] ?? $data['birthday'] ?? null;

                if ($portalBday && $searchBday) {
                    if (date('Y-m-d', strtotime($portalBday)) === date('Y-m-d', strtotime($searchBday))) {
                        return $user;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Bataeno findUserByNameAndBirthday error', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Register a new user account on the Bataan Portal.
     * Required fields: first_name, middle_name, last_name, ext_name, birthday.
     */
    public function registerToPortal(array $data): ?array
    {
        $token = Session::get(self::SESSION_KEY);
        if (!$token) {
            Log::debug('BataenoService: no session token for registerToPortal');
            return null;
        }

        // Use mapPortalData to standardize the input first
        $mapped = $this->mapPortalData($data);

        // Required fields check: Portal expects at least first_name, last_name, and birthday
        if (empty($mapped['first_name']) || empty($mapped['last_name'])) {
            Log::warning('Bataeno registerToPortal: Missing required identity fields', ['mapped' => $mapped]);
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(10)
                ->post("{$this->baseUrl}/api/user", [
                    'first_name' => $mapped['first_name'],
                    'last_name' => $mapped['last_name'],
                    'middle_name' => $mapped['middle_name'],
                    'birthday' => $mapped['date_of_birth'],
                    'ext_name' => $mapped['suffix'],
                ]);

            if ($response->status() === 401) {
                $this->handleApiError($response, 'Portal Registration');
            }

            if ($response->successful()) {
                Log::info('Bataeno Portal Registration Success', ['data' => $response->json()]);
                return $response->json();
            }

            $this->handleApiError($response, 'Portal Registration');
        } catch (\Exception $e) {
            Log::error('Bataeno registerToPortal error', ['error' => $e->getMessage()]);
            throw $e;
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
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
            'name' => $name,
            'email' => $user->email,
            'uuid' => $user->uuid,

            // Personal details — from local DB (populated during OAuth login)
            'birthdate' => $user->date_of_birth ?? $user->birthdate ?? null,
            'birthdate_formal' => $bataeno['birthday_formal'] ?? null,
            'birth_place' => $user->place_of_birth ?? null,
            'sex' => $user->gender ?? null,
            'civil_status' => $user->civil_status ?? null,
            'contact_number' => $user->contact_number ?? $user->phone ?? $user->mobile ?? null,

            // Address — from local DB
            'address' => implode(', ', array_filter([
                $user->address ?? null,
                $user->barangay_name ?? null,
                $user->municity_name ?? null,
            ])),
            'barangay_name' => $user->barangay_name ?? null,
            'municity_name' => $user->municity_name ?? null,

            // From Bataeno verification (may be null if API unreachable)
            'profile_photo' => $profilePhoto,
            'card_valid' => $bataeno !== null,
            'signature' => $bataeno['signature'] ?? null,

            // Raw data for anything else the blade needs
            'raw' => $user->toArray(),
            'bataeno_raw' => $bataeno,
            '_source' => $bataeno ? 'local+bataeno' : 'local',
        ];
    }

    /**
     * Centralized mapping logic to convert raw portal data into application fields.
     */
    public function mapPortalData(array $govUserData): array
    {
        // Many QR formats (like standard PhilID/PCN) wrap the identity data inside a 'subject' property
        if (isset($govUserData['subject']) && is_array($govUserData['subject'])) {
            $govUserData = array_merge($govUserData, $govUserData['subject']);
        }

        $egovMunicityCode = $govUserData['municity_code'] ?? $govUserData['muncity_code'] ?? null;
        $egovBarangayCode = $govUserData['barangay_code'] ?? $govUserData['brgy_code'] ?? null;

        $municityId = $egovMunicityCode ? (\App\Models\Municipality::where('municity_code', $egovMunicityCode)->value('id')) : null;
        $barangayId = $egovBarangayCode ? (\App\Models\Barangay::where('barangay_code', $egovBarangayCode)->value('id')) : null;

        // Try to find First Name
        $firstName = $govUserData['first_name']
            ?? $govUserData['fName']
            ?? $govUserData['given_name']
            ?? $govUserData['given_names']
            ?? $govUserData['fname']
            ?? $govUserData['givenName']
            ?? null;

        // Try to find Last Name
        $lastName = $govUserData['last_name']
            ?? $govUserData['lName']
            ?? $govUserData['surname']
            ?? $govUserData['lname']
            ?? $govUserData['surName']
            ?? $govUserData['family_name']
            ?? $govUserData['familyName']
            ?? null;

        // Try to find Middle Name
        $middleName = $govUserData['middle_name']
            ?? $govUserData['mName']
            ?? $govUserData['mname']
            ?? $govUserData['middleName']
            ?? null;

        // Try to find Suffix
        $suffix = $govUserData['suffix']
            ?? $govUserData['ext_name']
            ?? $govUserData['extName']
            ?? $govUserData['extension_name']
            ?? null;

        // Fallback: If names are missing but we have full_name
        if (!$firstName && !empty($govUserData['full_name'])) {
            $parts = explode(' ', $govUserData['full_name']);
            if (count($parts) > 1) {
                $lastName = array_pop($parts);
                $firstName = implode(' ', $parts);
            } else {
                $firstName = $govUserData['full_name'];
            }
        }

        if (!$firstName || !$lastName) {
            Log::debug('mapPortalData: Failed to resolve name fields', ['payload_keys' => array_keys($govUserData)]);
        }

        // Civil Status Normalization
        $civilStatusRaw = $govUserData['civil_status'] ?? $govUserData['maritalStatus'] ?? $govUserData['status'] ?? 'Single';
        $civilStatus = 'Single';
        if ($civilStatusRaw) {
            $civilStatus = match (strtolower((string) $civilStatusRaw)) {
                'm', 'married' => 'Married',
                's', 'single' => 'Single',
                'w', 'widowed' => 'Widowed',
                'sep', 'separated' => 'Separated',
                default => ucfirst(strtolower((string) $civilStatusRaw)),
            };
        }

        return [
            'uuid' => $govUserData['uuid'] ?? $govUserData['id'] ?? null,
            // Identity
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'suffix' => $suffix,

            // Profile Details
            'date_of_birth' => $govUserData['birthday'] ?? $govUserData['birthdate'] ?? $govUserData['dob'] ?? $govUserData['DOB'] ?? null,
            'place_of_birth' => $govUserData['birth_place'] ?? $govUserData['pob'] ?? $govUserData['POB'] ?? null,
            'gender' => $govUserData['sex'] ?? $govUserData['gender'] ?? $govUserData['Sex'] ?? null,
            'civil_status' => $civilStatus,
            'email' => $govUserData['email'] ?? null,
            'contact_number' => $govUserData['mobile_number'] ?? $govUserData['mobile'] ?? $govUserData['contact_number'] ?? $govUserData['phone'] ?? null,
            'occupation' => $govUserData['occupation'] ?? null,

            // Location IDs Lookups
            'municity_id' => $municityId,
            'barangay_id' => $barangayId,

            'egov_data' => $govUserData['identities'] ?? $govUserData ?? null,
        ];
    }

    /**
     * Normalise a raw Bataeno API response — used for /api/user during OAuth login.
     */
    public function normalise(mixed $raw): ?array
    {
        if (!is_array($raw) || empty($raw))
            return null;

        $d = $raw['data'] ?? $raw['user'] ?? $raw;

        if (!is_array($d) || empty($d))
            return null;

        if (empty($d['first_name']) && empty($d['full_name']) && empty($d['email'])) {
            return null;
        }

        $first = $d['first_name'] ?? null;
        $middle = $d['middle_name'] ?? null;
        $last = $d['last_name'] ?? null;

        $name = $d['full_name']
            ?? trim(implode(' ', array_filter([$first, $middle, $last])))
            ?: ($d['email'] ?? null);

        return [
            'first_name' => $first,
            'middle_name' => $middle,
            'last_name' => $last,
            'name' => trim($name),
            'address' => implode(', ', array_filter([
                $d['address'] ?? null,
                $d['barangay_name'] ?? null,
                $d['municity_name'] ?? null,
            ])),
            'birthdate' => $d['birthday'] ?? null,
            'birthdate_formal' => $d['birthday_formal'] ?? null,
            'birth_place' => $d['birth_place'] ?? null,
            'contact_number' => $d['mobile_number'] ?? $d['mobile'] ?? $d['phone'] ?? null,
            'sex' => $d['sex'] ?? null,
            'civil_status' => $d['civil_status'] ?? null,
            'email' => $d['email'] ?? null,
            'uuid' => $d['uuid'] ?? null,
            'profile_photo' => $d['profile_photos']['medium']
                ?? $d['profile_photos']['original']
                ?? null,
            'barangay_name' => $d['barangay_name'] ?? null,
            'municity_name' => $d['municity_name'] ?? null,
            'raw' => $d,
            '_source' => 'bataeno',
        ];
    }
}