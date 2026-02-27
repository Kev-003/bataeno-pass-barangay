<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\User;
use App\Services\BataenoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BataenoAuthController extends Controller
{
    public function redirect()
    {
        $state = Str::random(40);
        session()->put('oauth_state', $state);

        // Use the URI configured in .env to ensure it matches the Bataan Portal
        $registered_uri = config('services.bataeno.redirect');

        $params = [
            'client_id'     => config('services.bataeno.client_id'),
            'redirect_uri'  => config('services.bataeno.redirect'),
            'response_type' => 'code',
            'scope'         => 'view-user',
            'state'         => $state,
        ];

        return redirect(
            config('services.bataeno.base_url') . '/oauth/authorize?' . http_build_query($params)
        );
    }

    public function callback(Request $request, BataenoService $bataeno)
    {
        // State check
        $state = session()->pull('oauth_state');
        if ($state && $request->state && $state !== $request->state) {
            abort(403, 'Invalid OAuth state parameter.');
        }

        // Exchange code for token
        $tokenResponse = Http::asForm()->post(
            config('services.bataeno.base_url') . '/oauth/token',
            [
                'grant_type'    => 'authorization_code',
                'client_id'     => config('services.bataeno.client_id'),
                'client_secret' => config('services.bataeno.client_secret'),
                'redirect_uri'  => config('services.bataeno.redirect'),
                'code'          => $request->query('code'),
            ]
        );

        if ($tokenResponse->failed()) {
            return redirect('/login')->withErrors(['bataeno' => 'Token exchange failed. Please try again.']);
        }

        $tokens      = $tokenResponse->json();
        $accessToken = $tokens['access_token'] ?? null;
        $expiresIn   = (int) ($tokens['expires_in'] ?? 3600);

        if (! $accessToken) {
            return redirect('/login')->withErrors(['bataeno' => 'No access token returned.']);
        }

        // ✅ Cache the token — no DB column needed
        $bataeno->storeOfficialToken($accessToken);

        // Fetch the user's profile from Bataeno
        $govData = $bataeno->fetchAuthenticatedProfile($accessToken);

        if (! $govData) {
            return redirect('/login')->withErrors(['bataeno' => 'Could not fetch user profile.']);
        }

        $raw = $govData['raw'] ?? [];

        // Upsert local user — only identity fields, no token columns
        $user = User::firstOrNew(['email' => $raw['email'] ?? $govData['email'] ?? null]);

        $egovMunicityCode = $govUserData['data']['municity_code'] ?? null;
        $egovBarangayCode = $govUserData['data']['barangay_code'] ?? null;

        $municityId = $egovMunicityCode ? (\App\Models\Municipality::where('municity_code', $egovMunicityCode)->value('id')) : null;
        $barangayId = $egovBarangayCode ? (\App\Models\Barangay::where('barangay_code', $egovBarangayCode)->value('id')) : null;

        $user->fill([
            'uuid' => $govUserData['data']['uuid'] ?? null,
            // Identity
            'first_name' => $govUserData['data']['first_name'] ?? null,
            'middle_name' => $govUserData['data']['middle_name'] ?? null,
            'last_name' => $govUserData['data']['last_name'] ?? null,
            'suffix' => $govUserData['data']['ext_name'] ?? null,

            // Profile Details
            'date_of_birth' => $govUserData['data']['birthday'] ?? null,
            'place_of_birth' => $govUserData['data']['birth_place'] ?? null,
            'gender' => $govUserData['data']['sex'] ?? null,
            'civil_status' => $govUserData['data']['civil_status'] ?? null,

            // Location IDs Lookups
            'municity_id' => $municityId,
            'barangay_id' => $barangayId,

            'egov_data' => $govUserData['data']['identities'] ?? $govUserData['data'] ?? null,
        ]);

        if (! $user->exists) {
            $user->password      = bcrypt(Str::random(16));
            $user->registered_at = now();
        }

        $user->save();
        Auth::login($user);

        if ($user->isAdmin()) {
            return redirect('/admin');
        }

        if ($user->isOfficial()) {
            return redirect('/official/' . $user->barangay?->barangay_code);
        }

        return redirect('/dashboard');
    }
}