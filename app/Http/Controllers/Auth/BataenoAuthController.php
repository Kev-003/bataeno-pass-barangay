<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\BarangayRole;
use App\Models\Municipality;
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
            'client_id' => config('services.bataeno.client_id'),
            'redirect_uri' => config('services.bataeno.redirect'),
            'response_type' => 'code',
            'scope' => 'view-user find-users',
            'state' => $state,
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
                'grant_type' => 'authorization_code',
                'client_id' => config('services.bataeno.client_id'),
                'client_secret' => config('services.bataeno.client_secret'),
                'redirect_uri' => config('services.bataeno.redirect'),
                'code' => $request->query('code'),
            ]
        );

        if ($tokenResponse->failed()) {
            return redirect('/login')->withErrors(['bataeno' => 'Token exchange failed. Please try again.']);
        }

        $tokens = $tokenResponse->json();
        $accessToken = $tokens['access_token'] ?? null;

        if (!$accessToken) {
            return redirect('/login')->withErrors(['bataeno' => 'No access token returned.']);
        }

        // ✅ Cache the token — no DB column needed
        $bataeno->storeOfficialToken($accessToken);

        // Fetch the user's profile from Bataeno
        $govData = $bataeno->fetchAuthenticatedProfile($accessToken);
        $govUserData = $govData['data'] ?? $govData['raw'] ?? $govData;

        if (!$govUserData) {
            return redirect('/login')->withErrors(['bataeno' => 'Could not fetch user profile.']);
        }

        $user = User::firstOrNew(['email' => $govUserData['email'] ?? null]);

        $egovMunicityCode = $govUserData['municity_code'] ?? null;
        $egovBarangayCode = $govUserData['barangay_code'] ?? null;
        $municityId = $egovMunicityCode ? (Municipality::where('municity_code', $egovMunicityCode)->value('id')) : null;
        $barangayId = $egovBarangayCode ? (Barangay::where('barangay_code', $egovBarangayCode)->value('id')) : null;

        $user->fill([
            'uuid' => $govUserData['uuid'] ?? null,
            // Identity
            'first_name' => $govUserData['first_name'] ?? $govUserData['fName'] ?? null,
            'middle_name' => $govUserData['middle_name'] ?? $govUserData['mName'] ?? null,
            'last_name' => $govUserData['last_name'] ?? $govUserData['lName'] ?? null,
            'suffix' => $govUserData['ext_name'] ?? null,

            // Profile Details
            'date_of_birth' => $govUserData['birthday'] ?? $govUserData['birthdate'] ?? $govUserData['dob'] ?? null,
            'place_of_birth' => $govUserData['birth_place'] ?? null,
            'gender' => $govUserData['sex'] ?? $govUserData['gender'] ?? null,
            'civil_status' => $govUserData['civil_status'] ?? null,

            // Location IDs Lookups
            'municity_id' => $municityId,
            'barangay_id' => $barangayId,

            'egov_data' => $govUserData['identities'] ?? $govUserData ?? null,
        ]);

        if (!$user->exists) {
            $user->password = bcrypt(Str::random(16));
            $user->registered_at = now();
        }

        $user->save();
        Auth::login($user);

        if ($user->hasRole('Admin')) {
            return redirect('/admin');
        }

        if ($user->hasAnyRole(BarangayRole::officialRoles())) {
            return redirect('/official/' . $user->barangay?->barangay_code);
        }

        return redirect('/dashboard');
    }

    public function logout()
    {
        Auth::logout();
        session()->forget(BataenoService::SESSION_KEY);
        return redirect('/');
    }
}