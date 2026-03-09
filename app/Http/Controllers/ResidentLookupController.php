<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class ResidentLookupController extends Controller
{
    /**
     * Handle the incoming lookup request by querying the Bataeno Pass API.
     * Falls back to local DB only if configured (not used by default).
     */
    public function __invoke(Request $request)
    {
        // Accept uid via GET or JSON body; support free-text `q` as well
        $uid = $request->input('uid') ?? $request->json('uid');
        $q = $request->input('q') ?? $request->json('q');

        if (!$uid && !$q) {
            return response()->json(['message' => 'uid or q is required'], 422);
        }

        $uid = is_string($uid) ? trim($uid) : $uid;

        $cacheTtl = (int) env('BATAENO_CACHE_TTL', 300);
        $cacheKey = $uid ? 'bataeno_resident_' . md5($uid) : null;
        if ($cacheKey && ($cached = Cache::get($cacheKey))) {
            return response()->json(['resident' => $cached, 'source' => 'cache']);
        }

        $timeout = (int) env('BATAENO_TIMEOUT', 5);

        try {
            $resident = $this->fetchFromBataeno($uid, $q, $timeout);
        } catch (\Exception $e) {

            // Optionally fall back to local DB when configured
            if (filter_var(env('BATAENO_FALLBACK_LOCAL', false), FILTER_VALIDATE_BOOLEAN) && $uid) {
                $user = User::where('uuid', $uid)
                    ->orWhere('nfc_uid', $uid)
                    ->orWhere('card_uid', $uid)
                    ->first();
                if ($user) {
                    $payload = [
                        'first_name' => $user->first_name,
                        'middle_name' => $user->middle_name,
                        'last_name' => $user->last_name,
                        'name' => trim(($user->first_name ?? '') . ' ' . ($user->middle_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email,
                        'address' => ($user->barangay_name ? $user->barangay_name . ', ' : '') . ($user->municity_name ?? ''),
                        'birthdate' => $user->date_of_birth ?? $user->birthdate ?? null,
                        'contact_number' => $user->contact_number ?? $user->phone ?? $user->email ?? null,
                        'raw' => $user->toArray(),
                    ];
                    Cache::put($cacheKey, $payload, $cacheTtl);
                    return response()->json(['resident' => $payload, 'source' => 'local-fallback']);
                }
            }
            return response()->json(['message' => 'Remote lookup failed', 'error' => $e->getMessage()], 502);
        }

        if (!$resident) {
            return response()->json(['message' => 'Resident not found'], 404);
        }

        if ($cacheKey)
            Cache::put($cacheKey, $resident, $cacheTtl);
        return response()->json(['resident' => $resident, 'source' => 'bataeno']);
    }

    protected function fetchFromBataeno(?string $uid = null, ?string $q = null, int $timeout = 5)
    {
        $base = rtrim($this->getBataenoBaseUrl(), '/');

        $token = $this->getBataenoToken();
        if (!$token) {
            throw new \RuntimeException('No access token for Bataeno');
        }

        $client = Http::withToken($token)->acceptJson()->timeout(max(1, $timeout));

        $tried = [];

        // OPTIMIZED: We removed the 7 redundant endpoints. 
        // We now only check the UUID (software ID) and card_uid (hardware ID)
        if ($uid) {
            $endpoints = [
                "/api/users?uuid={$uid}",
                "/api/users?card_uid={$uid}",
            ];
        } else {
            // Free-text search options
            $endpoints = [
                "/api/users?search={$q}",
                "/api/users?query={$q}",
                "/api/users?name={$q}",
                "/api/users?q={$q}",
            ];
        }

        foreach ($endpoints as $ep) {
            $tried[] = $base . $ep;
            $res = $client->get($base . $ep);

            if ($res->successful()) {
                $json = $res->json();
                $raw = $json['data'] ?? $json;

                // If the endpoint returned a list, pick the first match
                if (is_array($raw)) {
                    if (empty($raw))
                        continue;
                    $first = $raw[0];
                    return $this->normalizePayload($first);
                }

                return $this->normalizePayload($raw);
            }
        }

        // As a last attempt, try a generic search endpoint
        $fallback = $client->get($base . '/api/users', ['q' => $uid ?? $q]);
        if ($fallback->successful()) {
            $json = $fallback->json();
            $raw = $json['data'] ?? $json;
            if (is_array($raw) && !empty($raw)) {
                return $this->normalizePayload($raw[0]);
            }
            return $this->normalizePayload($raw);
        }

        return null;
    }

    protected function getBataenoToken()
    {
        // Read env/config with fallbacks
        $cacheKey = 'bataeno_access_token';
        $cached = Cache::get($cacheKey);
        if ($cached)
            return $cached;

        $base = rtrim($this->getBataenoBaseUrl(), '/');
        $tokenUrl = $base . '/oauth/token';

        $form = ['grant_type' => 'client_credentials'];

        $useBasic = $this->getBataenoUseBasicAuth();

        if ($useBasic) {
            $resp = Http::withBasicAuth($this->getBataenoClientId(), $this->getBataenoClientSecret())
                ->asForm()
                ->post($tokenUrl, $form);
        } else {
            $form['client_id'] = $this->getBataenoClientId();
            $form['client_secret'] = $this->getBataenoClientSecret();
            $resp = Http::asForm()->post($tokenUrl, $form);
        }

        if ($resp->failed()) {
            return null;
        }

        $json = $resp->json();
        $access = $json['access_token'] ?? null;
        $expires = isset($json['expires_in']) ? (int) $json['expires_in'] : 300;

        if ($access) {
            Cache::put($cacheKey, $access, max(60, $expires - 60));
        }

        return $access;
    }

    protected function getBataenoBaseUrl()
    {
        return env('BATAENO_PASS_API_URL') ?: config('services.bataeno.base_url');
    }

    protected function getBataenoClientId()
    {
        return env('BATAENO_PASS_CLIENT_ID') ?: config('services.bataeno.client_id');
    }

    protected function getBataenoClientSecret()
    {
        return env('BATAENO_PASS_CLIENT_SECRET') ?: config('services.bataeno.client_secret');
    }

    protected function getBataenoUseBasicAuth()
    {
        $val = env('BATAENO_PASS_USE_BASIC_AUTH');
        if (!is_null($val))
            return filter_var($val, FILTER_VALIDATE_BOOLEAN);
        return config('services.bataeno.use_basic_auth', false);
    }

    protected function normalizePayload($raw)
    {
        if (!is_array($raw))
            return null;

        // Try common shapes
        $data = $raw['data'] ?? $raw;

        $first = $data['first_name'] ?? $data['given_name'] ?? $data['fname'] ?? null;
        $middle = $data['middle_name'] ?? $data['mname'] ?? null;
        $last = $data['last_name'] ?? $data['surname'] ?? $data['lname'] ?? null;
        $name = trim(implode(' ', array_filter([$first, $middle, $last])));

        return [
            'first_name' => $first,
            'middle_name' => $middle,
            'last_name' => $last,
            'name' => $name ?: ($data['email'] ?? null),
            'address' => $data['barangay_name'] ?? ($data['address'] ?? null),
            'birthdate' => $data['birthday'] ?? $data['birthdate'] ?? null,
            'contact_number' => $data['phone'] ?? $data['mobile'] ?? $data['contact_number'] ?? null,
            'raw' => $data,
        ];
    }
}