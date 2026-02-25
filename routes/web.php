<?php

use App\Livewire\Officials\WalkInRequest;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\BataenoAuthController;
use App\Livewire\DocumentRequestForm;
use App\Livewire\Documents;
use App\Livewire\HouseholdProfiles;

use App\Livewire\Officials\Dashboard;
use App\Livewire\Officials\ResidentsIndex;
use App\Livewire\Officials\DocumentProcessing;
use App\Livewire\Officials\DocumentApprovalProcess;
use App\Livewire\Officials\Profile;
use App\Livewire\Officials\OfficialManagement;


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
Route::get('/auth/bataeno', [BataenoAuthController::class, 'redirect'])->name('bataeno.login');

// The link the government website sends the user back to
Route::get('/callback', [BataenoAuthController::class, 'callback']);

Route::view('login', 'livewire.pages.auth.login')
    ->name('bataeno.login');

// Standard Resident Dashboard
Route::middleware(['auth'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/documents', Documents::class)->name('documents');

// // Official Panel (Restricted)
// Route::middleware(['auth'])->get('/official', function () {
//     if (!auth()->user()->isOfficial()) {
//         abort(403, 'You are not a registered official.');
//     }
//     return view('livewire.officials.dashboard');
// })->name('official.dashboard');

// Route::get('/official', Dashboard::class)->middleware(['auth']);

//route implementing middleware
// Route::middleware(['auth', 'barangay.access'])
//     ->prefix('official/{barangay_code}')
//     ->group(function () {
//         Route::get('/', Dashboard::class)->name('official.dashboard');
//         Route::get('/residents', ResidentsIndex::class)->name('official.residents');
//         Route::get('/document-processing', DocumentProcessing::class)->name('official.document-processing');
//         Route::get('/document-approval-process/{id}', DocumentApprovalProcess::class)
//             ->name('official.document-approval-process')
//             ->middleware('can:approve requests');
//         Route::get('/walk-in-request', WalkInRequest::class)
//             ->name('official.walk-in-request')
//             ->middleware('permission:manage my barangay info|make requests for residents');
//         Route::get('/profile', Profile::class)->name('official.profile');
//         Route::get('/official-management', OfficialManagement::class)
//             ->name('official.official-management')
//             ->middleware('can:manage my officials');
//     });

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

use App\Http\Controllers\DocumentRequestController;

Route::get('/document-request', DocumentRequestForm::class)->name('document.request');
Route::post('/document-request', [DocumentRequestController::class, 'store'])->name('document-request.store');

Route::get('documents/temp/{path}', function (Illuminate\Http\Request $request, string $path) {
    $transaction = \App\Models\DocumentTransaction::where('file_path', $path)->firstOrFail();

    // Check if the user is authorized (Owner ONLY)
    if ((int) auth()->id() !== (int) $transaction->requester_id) {
        abort(403, 'Unauthorized: Only the document owner can download this file.');
    }

    // Single-use token logic
    $providedToken = $request->query('token');
    if (!$transaction->download_token || $transaction->download_token !== $providedToken) {
        abort(403, 'This download link has already been used or is invalid.');
    }

    // Invalidate the token immediately
    $transaction->update(['download_token' => null]);

    return \Illuminate\Support\Facades\Storage::disk('documents')->download($path);
})
    ->where('path', '.*')
    ->middleware(['auth', 'signed'])
    ->name('documents.temp');

Route::get('household-profiles', HouseholdProfiles::class)->name('household-profiles');
//logout route
Route::get('logout', [BataenoAuthController::class, 'logout'])->name('logout');

require __DIR__ . '/auth.php';
