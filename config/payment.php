<?php

return [
    'lifetime' => [
        'bank_transfer_minutes' => (int) env('PAYMENT_VA_LIFETIME_MINUTES', 1440),
        'qris_minutes' => (int) env('PAYMENT_QRIS_LIFETIME_MINUTES', 15),
    ],
];