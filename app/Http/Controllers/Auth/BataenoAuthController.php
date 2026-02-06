<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BataenoAuthController extends Controller
{
    public function redirect()
    {
        session()->forget('oauth_state');
        $state = Str::random(40);
        session()->put('oauth_state', $state);

        // 2. MANUALLY build the URI to match what YOU typed in the Bataan Portal
        // DON'T use url() helper here, type it exactly as it appears in their dashboard
        $registered_uri = "http://localhost:8000/callback";

        $params = [
            'client_id' => config('services.bataeno.client_id'),
            'redirect_uri' => $registered_uri,
            'response_type' => 'code',
            'scope' => 'view-user', // Verify if this is exactly 'view-user'
            'state' => $state,
        ];

        $authUrl = "http://localhost:8000/oauth/authorize?" . http_build_query($params);
        // WAIT: Your doc says the auth endpoint is bataeno-pass.bataan.gov.ph
        $realAuthUrl = "https://bataeno-pass.bataan.gov.ph/oauth/authorize?" . http_build_query($params);

        return redirect($realAuthUrl);
    }

    // Step 2: Handle the return callback
    public function callback(Request $request)
    {
        // Security: Check the state matches
        $state = session()->pull('oauth_state');

        // Note: If the gov API doesn't return 'state', remove this 'if' block
        if (!$state || $state !== $request->state) {
            //abort(403, 'Invalid state parameter');
        }




        // Exchange the code for a token
        // $response = Http::asForm()->post(config('services.bataeno.base_url') . '/oauth/token', [
        //     'grant_type' => 'authorization_code',
        //     'client_id' => config('services.bataeno.client_id'),
        //     'client_secret' => config('services.bataeno.client_secret'),
        //     'redirect_uri' => config('services.bataeno.redirect'),
        //     'code' => $request->code,
        // ]);

        // $response = Http::post(config('services.bataeno.base_url') . '/oauth/token', [
        //     'grant_type' => 'authorization_code',
        //     'client_id' => config('services.bataeno.client_id'),
        //     'client_secret' => config('services.bataeno.client_secret'),
        //     'redirect_uri' => config('services.bataeno.redirect'),
        //     'code' => $request->code,
        // ]);

        // if ($response->failed()) {
        //     return response()->json(['error' => 'Token exchange failed', 'details' => $response->json()], 400);
        // }

        // $url = config('services.bataeno.base_url') . '/oauth/token?' . http_build_query([
        //     'grant_type' => 'authorization_code',
        //     'client_id' => config('services.bataeno.client_id'),
        //     'client_secret' => config('services.bataeno.client_secret'),
        //     'redirect_uri' => config('services.bataeno.redirect'),
        //     'code' => $request->code,
        // ]);

        // $response = Http::post($url);

        // $response = Http::withHeaders([
        //     'Accept' => 'application/json',
        // ])
        //     ->asForm() // This sets Content-Type to application/x-www-form-urlencoded
        //     ->post(config('services.bataeno.base_url') . '/oauth/token', [
        //         'grant_type' => 'authorization_code',
        //         'client_id' => (string) config('services.bataeno.client_id'),
        //         'client_secret' => (string) config('services.bataeno.client_secret'),
        //         'redirect_uri' => config('services.bataeno.redirect'),
        //         'code' => $request->code,
        //     ]);

        $response = Http::asForm()
            ->post(config('services.bataeno.base_url') . '/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.bataeno.client_id'),
                'client_secret' => config('services.bataeno.client_secret'),
                'redirect_uri' => config('services.bataeno.redirect'),
                'code' => $request->query('code'),
            ]);
        $tokens = $response->json();
        $accessToken = $tokens['access_token'];

        // Step 3: Fetch the user data
        $userResponse = Http::withToken($accessToken)
            ->get(config('services.bataeno.base_url') . '/api/user');

        if ($userResponse->failed()) {
            return response()->json(['error' => 'User fetch failed'], 400);
        }

        $govUserData = $userResponse->json();

        // dd($govUserData);
        $user = User::updateOrCreate(
            ['email' => $govUserData['data']['email']],
            [
                'uuid' => $govUserData['data']['uuid'] ?? null,
                // Identity
                'first_name' => $govUserData['data']['first_name'] ?? null,
                'middle_name' => $govUserData['data']['middle_name'] ?? null,
                'last_name' => $govUserData['data']['last_name'] ?? null,
                'suffix' => $govUserData['data']['ext_name'] ?? null, // Mapping ext_name to suffix

                // Profile Details
                'date_of_birth' => $govUserData['data']['birthday'] ?? null,
                'place_of_birth' => $govUserData['data']['birth_place'] ?? null,
                'gender' => $govUserData['data']['sex'] ?? null,
                'civil_status' => $govUserData['data']['civil_status'] ?? null,

                // Location Codes
                'municity_code' => $govUserData['data']['municity_code'] ?? null,
                'barangay_code' => $govUserData['data']['barangay_code'] ?? null,

                // Custom Fields (Add these to your migration/fillable)
                'municity_name' => $govUserData['data']['municity_name'] ?? null,
                'barangay_name' => $govUserData['data']['barangay_name'] ?? null,

                // System Requirements
                'password' => $user->password ?? bcrypt(\Illuminate\Support\Str::random(16)),
                'registered_at' => $user->registered_at ?? now(),
            ]
        );

        Auth::login($user);

        return redirect()->route('resident.dashboard');
    }
}
