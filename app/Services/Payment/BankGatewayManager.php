<?php

namespace App\Services\Payment;

use App\Contracts\BankGatewayInterface;
use App\Enums\BankCode;
use RuntimeException;
class BankGatewayManager
{
    public function driver(BankCode $bank): BankGatewayInterface
    {
        $class = config("bank_gateway.drivers.{$bank->value}");

        if (! $class || ! class_exists($class)) {
            throw new RuntimeException("Driver bank '{$bank->value}' belum dikonfigurasi.");
        }

        return app($class);
    }
}