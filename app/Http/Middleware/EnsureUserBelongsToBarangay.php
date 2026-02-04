<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToBarangay
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Get the Barangay ID from the current URL or Request Data
        $targetBarangayId = $request->route('barangay_id') ?? $request->input('barangay_id');

        if (!$user || $user->getActiveBarangayId() != $targetBarangayId) {
            abort(403, 'Unauthorized access to this barangay.');
        }
        return $next($request);
    }
}
