<?php

use \App\Services\Dummy\DummyBankGatewayService;

return [
    'dummy_shared_secret' => env('BANK_GATEWAY_DUMMY_SECRET', 'secret-dummy-h2h-jambi'),

    'drivers' => [
        'bank_jambi' => DummyBankGatewayService::class,
        'mandiri' => DummyBankGatewayService::class,
        'bri' => DummyBankGatewayService::class,
        'bni' => DummyBankGatewayService::class,
        'btn' => DummyBankGatewayService::class,
    ],
];