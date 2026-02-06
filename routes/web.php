<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\BataenoAuthController;

Route::view('/', 'welcome');

// The link you put on your "Login with Bataeno Pass" button
Route::get('/auth/bataeno', [BataenoAuthController::class, 'redirect'])->name('bataeno.login');

// The link the government website sends the user back to
Route::get('/callback', [BataenoAuthController::class, 'callback']);

Route::view('login', 'livewire.pages.auth.login')
    ->name('login');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
