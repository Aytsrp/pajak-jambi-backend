<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankH2HController;
use App\Http\Controllers\NopController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NpwpdController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\TaxSummaryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\QrisWebhookController;
use Illuminate\Support\Facades\Route;

// ── Public ──────────────────────────────────────────

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::prefix('otp')->group(function () {
    Route::post('/request', [OtpController::class, 'request']);
    Route::post('/verify', [OtpController::class, 'verify']);
});

Route::prefix('h2h/{bank_code}')->group(function () {
    Route::post('/inquiry', [BankH2HController::class, 'inquiry']);
    Route::post('/payment', [BankH2HController::class, 'payment']);
});

Route::post('/webhooks/qris', [QrisWebhookController::class, 'handle']);

// ── Authenticated (Sanctum) ─────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me/onboarding-status', [TaxSummaryController::class, 'onboardingstatus']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/change-pin', [AuthController::class, 'changePin']);

    Route::get('/summary', [TaxSummaryController::class, 'index']);

    Route::get('/nops', [NopController::class, 'index']);
    Route::post('/nops/check', [NopController::class, 'check']);
    Route::post('/nops', [NopController::class, 'store']);
    Route::get('/nops/{id}', [NopController::class, 'show']);
    Route::delete('/nops/{id}', [NopController::class, 'destroy']);
    Route::post('/nops/{id}/refresh', [NopController::class, 'refresh']);

    Route::get('/npwpd', [NpwpdController::class, 'show']);
    Route::post('/npwpd/check', [NpwpdController::class, 'check']);
    Route::post('/npwpd', [NpwpdController::class, 'store']);
    Route::delete('/npwpd', [NpwpdController::class, 'destroy']);
    Route::post('/npwpd/refresh', [NpwpdController::class, 'refresh']);

    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
    Route::post('/payment-methods', [PaymentMethodController::class, 'store']);
    Route::post('/payment-methods/{id}/set-default', [PaymentMethodController::class, 'setDefault']);
    Route::delete('/payment-methods/{id}', [PaymentMethodController::class, 'destroy']);

    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/monthly-summary', [TransactionController::class, 'monthlySummary']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
    Route::get('/transactions/{id}/proof', [TransactionController::class, 'proof'])
        ->name('transactions.proof');

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});

Route::middleware(['auth:sanctum', 'has.tax.object'])->group(function () {
    Route::post('/transactions/initiate', [TransactionController::class, 'initiate']);
});
