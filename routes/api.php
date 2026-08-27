<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OtpController;

Route::prefix('otp')->middleware('throttle:5,1')->group(function () {
    Route::post('/request', [OtpController::class, 'request']);
    Route::post('/verify', [OtpController::class, 'verify'])
        ->middleware('throttle:10,1');
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
