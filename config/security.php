<?php

return [
    'login' => [
        'max_attempts' => 5,
        'lock_minutes' => 15,
    ],
    'pin' => [
        'max_attempts' => 3,
        'lock_minutes' => 30,
    ],
    'otp' => [
        'length' => 6,
        'expiry_minutes' => 5,
        'max_verify_attempts' => 3,
    ],

    /*
     * Rate limit endpoint publik (per menit).
     * Berbeda dari kunci akun (login 5x / PIN 3x) — ini membatasi request HTTP
     * supaya tidak bisa di-spam dari satu IP atau satu NIK.
     */
    'rate_limit' => [
        'login_per_minute' => 5,
        'register_per_minute' => 5,
        'otp_per_minute' => 3,
    ],

    /*
     * Perilaku dummy/local supaya Collection Runner Postman bisa di-run berulang
     * tanpa mengutak-atik script. Tidak aktif di production.
     */
    'dummy' => [
        'otp_code' => env('DUMMY_OTP_CODE', '000000'),
        'passwords' => ['password123', 'passwordBaru456'],
        'pins' => ['123456', '654321'],
    ],
];