<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// Controllers
use App\Http\Controllers\Auth\BataenoAuthController;
use App\Http\Controllers\ResidentLookupController;
use App\Http\Controllers\DocumentRequestController;
// Livewire Components
use App\Livewire\DocumentRequestForm;
use App\Livewire\Documents;
use App\Livewire\HouseholdProfiles;
use App\Livewire\Officials\Dashboard;
use App\Livewire\Officials\ResidentsIndex;
use App\Livewire\Officials\DocumentProcessing;
use App\Livewire\Officials\DocumentApprovalProcess;
use App\Livewire\Officials\Profile;
use App\Livewire\Officials\OfficialManagement;
use App\Livewire\Officials\WalkInRequest;

Route::view('/', 'welcome');

Route::get('/document-test', function () {
    // Mocking the officials collection
    $officials = collect([
        (object) [
            'user' => (object) ['name' => 'HIEL SHADDAI PASCUAL'],
            'position' => (object) ['name' => 'Captain']
        ],
        (object) [
            'user' => (object) ['name' => 'RUSSEL SANTOS'],
            'position' => (object) ['name' => 'Kagawad']
        ],
        (object) [
            'user' => (object) ['name' => 'JC GAB MANUEL'],
            'position' => (object) ['name' => 'Kagawad']
        ],
    ]);

    return view('livewire.document-test', [
        'officials' => $officials,
        'resident' => (object) [
            'name' => 'KEVERN ANGELES',
            'civil_status' => 'SINGLE',
            'barangay_name' => 'SANTO DOMINGO',
            'municity_name' => 'ORION'
        ],
        'details' => (object) [
            'business_name' => 'Shadi Corp.',
            'business_type' => 'General Merchandise',
            'location' => '#67 Shadi St., Santo Domingo'
        ],
        'barangay' => (object) ['name' => 'SANTO DOMINGO'],
        'transaction' => (object) ['purpose' => 'Business Registration'],
        'provincialSeal' => null,
        'municipalSeal' => null,
        'barangaySeal' => null,
        'citySeal' => null,
    ]);
});

// The link you put on your "Login with Bataeno Pass" button
Route::get('/auth/bataeno', [BataenoAuthController::class, 'redirect'])
    ->name('bataeno.login')
    ->middleware('throttle:6,1');

// The link the government website sends the user back to
Route::get('/callback', [BataenoAuthController::class, 'callback'])
    ->middleware('throttle:auth');

Route::view('login', 'livewire.pages.auth.login')
    ->name('bataeno.login')
    ->middleware('throttle:auth');

// Standard Resident Dashboard
Route::middleware(['auth'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/documents', Documents::class)->name('documents');

// Official Panel (Restricted)
Route::middleware(['auth', 'barangay.access'])
    ->prefix('official/{barangay_code}')
    ->group(function () {
        Route::get('/', Dashboard::class)->name('official.dashboard');
        Route::get('/residents', ResidentsIndex::class)->name('official.residents');
        Route::get('/document-processing', DocumentProcessing::class)->name('official.document-processing');

        Route::get('/document-approval-process/{id}', DocumentApprovalProcess::class)
            ->name('official.document-approval-process')
            ->middleware('can:approve requests');

        Route::get('/walk-in-request', WalkInRequest::class)
            ->name('official.walk-in-request')
            ->middleware('permission:manage my barangay info|make requests for residents');

        Route::get('/profile', Profile::class)->name('official.profile');

        Route::get('/official-management', OfficialManagement::class)
            ->name('official.official-management')
            ->middleware('can:manage my officials');
    });

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/document-request', DocumentRequestForm::class)
    ->name('document.request')
    ->middleware('throttle:document-request');

Route::post('/document-request', [DocumentRequestController::class, 'store'])
    ->name('document-request.store')
    ->middleware('throttle:document-request');

Route::get('documents/temp/{path}', function (Request $request, string $path) {
    $transaction = \App\Models\DocumentTransaction::where('file_path', $path)->firstOrFail();

    // Check if the user is authorized (Owner OR Official of the issuing Barangay)
    $user = auth()->user();
    $isOwner = (int) $user->id === (int) $transaction->requester_id;

    if (!$isOwner) {
        abort(403, 'Unauthorized: You do not have permission to download this file.');
    }

    // Single-use token logic
    $providedToken = $request->query('token');
    if (!$transaction->download_token || $transaction->download_token !== $providedToken) {
        abort(403, 'This download link has already been used or is invalid.');
    }

    // Invalidate the token immediately
    $transaction->update(['download_token' => null]);

    return Storage::disk('documents')->download($path);
})
    ->where('path', '.*')
    ->middleware(['auth', 'signed'])
    ->name('documents.temp');

Route::get('household-profiles', HouseholdProfiles::class)->name('household-profiles');

// Logout route
Route::get('logout', [BataenoAuthController::class, 'logout'])->name('logout');

require __DIR__ . '/auth.php';


// ==========================================
// NFC & Walk-In Features
// ==========================================

// NFC/resident lookup used by the NFC scanner frontend
// Match allows both GET (for browser testing) and POST (for the JS fetch API) safely
Route::match(['get', 'post'], '/residents/lookup', ResidentLookupController::class)
    ->name('residents.lookup')
    ->middleware('throttle:lookup');

// Endpoints used by external NFC client to push latest resident data
Route::post('/nfc/set', [App\Http\Controllers\NfcController::class, 'setLatest'])
    ->middleware('throttle:lookup');

Route::get('/nfc/latest', [App\Http\Controllers\NfcController::class, 'latest'])
    ->middleware('throttle:lookup');

// NFC scanner page — use Livewire `NfcListener` component directly
Route::get('/nfc-scanner', \App\Livewire\Officials\NfcListener::class)->name('nfc.scanner');

