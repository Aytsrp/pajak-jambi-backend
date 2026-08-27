<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NopController;
use App\Http\Controllers\NpwpdController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\TaxSummaryController;
use Illuminate\Support\Facades\Route;

// ── Public ──────────────────────────────────────────

Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::prefix('otp')->middleware('throttle:5,1')->group(function () {
    Route::post('/request', [OtpController::class, 'request']);
    Route::post('/verify', [OtpController::class, 'verify'])
        ->middleware('throttle:10,1');
});

// ── Authenticated (Sanctum) ─────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/summary', [TaxSummaryController::class, 'index']);

    Route::get('/nops', [NopController::class, 'index']);
    Route::post('/nops', [NopController::class, 'store'])
        ->middleware('throttle:10,1');
    Route::get('/nops/{id}', [NopController::class, 'show']);
    Route::post('/nops/{id}/refresh', [NopController::class, 'refresh'])
        ->middleware('throttle:20,1');

    Route::get('/npwpd', [NpwpdController::class, 'show']);
    Route::post('/npwpd', [NpwpdController::class, 'store'])
        ->middleware('throttle:10,1');
    Route::post('/npwpd/refresh', [NpwpdController::class, 'refresh'])
        ->middleware('throttle:20,1');

});