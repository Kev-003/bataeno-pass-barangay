<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Barangay;
use Illuminate\Support\Facades\Auth;

class DemoLoginController extends Controller
{
    public function loginAsResident()
    {
        Auth::logout();
request()->session()->invalidate();
request()->session()->regenerateToken();
        // All users are residents; find a user that doesn't have an official/admin role
        $user = User::doesntHave('roles')->first() ?? User::first();
        
        if (!$user) return back()->with('error', 'No user found in the database. Please seed.');
        
        Auth::login($user);
        return redirect()->route('dashboard');
    }

    public function loginAsOfficial()
    {
Auth::logout();
request()->session()->invalidate();
request()->session()->regenerateToken();        // Match the roles in RolesAndPermissionsSeeder.php
        $user = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Captain', 'Secretary', 'Treasurer', 'Kagawad']);
        })->first();
        
        if (!$user) return back()->with('error', 'No official user found.');
        
        Auth::login($user);
        return redirect()->route('official.dashboard', ['barangay_code' => $user->barangay?->barangay_code ?? '01']);
    }

    public function loginAsAdmin()
    {
Auth::logout();
request()->session()->invalidate();
request()->session()->regenerateToken();        $user = User::role('Super Admin')->first() ?? User::role('Admin')->first();
                
        if (!$user) return back()->with('error', 'No Admin found.');
        
        Auth::login($user);
        return redirect('/admin');
    }

    public function loginAsCityAdmin()
    {
Auth::logout();
request()->session()->invalidate();
request()->session()->regenerateToken();        $user = User::role('City Admin')->first();
                
        if (!$user) return back()->with('error', 'No City Admin found.');
        
        Auth::login($user);
        return redirect('/city-admin');
    }
}
