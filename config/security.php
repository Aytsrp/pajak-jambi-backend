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
     * Perilaku dummy/local supaya Collection Runner Postman bisa di-run berulang
     * tanpa mengutak-atik script. Tidak aktif di production.
     */
    'dummy' => [
        'otp_code' => env('DUMMY_OTP_CODE', '000000'),
        'passwords' => ['password123', 'passwordBaru456'],
        'pins' => ['123456', '654321'],
    ],
];