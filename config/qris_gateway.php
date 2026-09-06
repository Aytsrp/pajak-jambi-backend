<?php

return [
    'driver' => env('QRIS_GATEWAY_DRIVER', 'dummy'),
    'dummy_shared_secret' => env('QRIS_GATEWAY_DUMMY_SECRET', 'secret-dummy-qris-jambi'),
];