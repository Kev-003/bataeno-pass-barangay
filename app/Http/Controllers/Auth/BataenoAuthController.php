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

        $user->fill([
            'uuid'           => $raw['uuid']          ?? $govData['uuid']        ?? null,
            'first_name'     => $raw['first_name']    ?? $govData['first_name']  ?? null,
            'middle_name'    => $raw['middle_name']   ?? $govData['middle_name'] ?? null,
            'last_name'      => $raw['last_name']     ?? $govData['last_name']   ?? null,
            'suffix'         => $raw['ext_name']      ?? null,
            'date_of_birth'  => $raw['birthday']      ?? $govData['birthdate']   ?? null,
            'place_of_birth' => $raw['birth_place']   ?? $govData['birth_place'] ?? null,
            'gender'         => $raw['sex']            ?? $govData['sex']         ?? null,
            'civil_status'   => $raw['civil_status']  ?? $govData['civil_status'] ?? null,
            'municity_code'  => Barangay::normalizeCode($raw['municity_code']    ?? null),
            'barangay_code'  => Barangay::normalizeCode($raw['barangay_code']    ?? null),
            'municity_name'  => $raw['municity_name'] ?? null,
            'barangay_name'  => $raw['barangay_name'] ?? null,
            'egov_data'      => $raw['identities']    ?? $raw,
        ]);

        if (! $user->exists) {
            $user->password      = bcrypt(Str::random(16));
            $user->registered_at = now();
        }

        $user->save();
        Auth::login($user);

        return match (true) {
            $user->isAdmin()    => redirect('/admin'),
            $user->isOfficial() => redirect('/official/' . $user->barangay_code),
            default             => redirect('/dashboard'),
        };
    }
}