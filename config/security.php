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
];