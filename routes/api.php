<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum', \App\Http\Middleware\EnsureUserBelongsToBarangay::class])->group(function () {

    // The Request Endpoint
    Route::post('/barangay/{barangay_code}/documents/request', [DocumentController::class, 'request']);

    // The Sign Endpoint
    Route::patch('/barangay/{barangay_code}/documents/{transaction_id}/sign', [DocumentController::class, 'sign']);

});
