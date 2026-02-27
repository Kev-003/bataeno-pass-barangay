<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserBelongsToBarangay
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Get the user safely
        $user = auth()->user(); // Helper function is often more reliable here

        // 2. If no user is logged in, force them to login page
        if (!$user) {
            // Option A: Redirect to login
            return redirect()->route('login');

            // Option B: Abort if it's an API
            // abort(401, 'Unauthenticated.');
        }

        // 3. Admins bypass restriction
        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!$user->isOfficial()) {
            abort(403, 'Access denied. You do not have official privileges.');
        }

        // 4. Get the target barangay from Route OR Input
        $targetBarangayCode = $request->route('barangay_code') ?? $request->input('barangay_code');

        // 5. If specific barangay is not required for this route, just proceed
        if (!$targetBarangayCode) {
            return $next($request);
        }

        // 6. Check permissions
        if (!in_array($targetBarangayCode, $user->getActiveBarangayCodes())) {
            abort(403, 'Unauthorized access to this barangay.');
        }

        return $next($request);
    }
}
