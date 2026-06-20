<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'tenant'])->prefix('v1')->name('api.v1.')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::apiResource('meetings', \App\Http\Controllers\MeetingEventController::class)->only(['index', 'show']);
    Route::apiResource('participants', \App\Http\Controllers\ParticipantController::class)->only(['index', 'show', 'store']);
    Route::post('/scanner/validate', [\App\Http\Controllers\ScannerController::class, 'validateQr'])->middleware(['permission:redemption.scan', 'throttle:60,1'])->name('scanner.validate');
    Route::post('/scanner/redeem', [\App\Http\Controllers\ScannerController::class, 'redeem'])->middleware(['permission:redemption.scan', 'throttle:60,1'])->name('scanner.redeem');
});
