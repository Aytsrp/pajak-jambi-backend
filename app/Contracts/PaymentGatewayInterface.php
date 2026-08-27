<?php

namespace App\Contracts;

use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Proses pembayaran lewat metode yang tersimpan.
     *
     * @return array{success: bool, gateway_ref: string, message: string}
     */
    public function charge(Payment $payment, float $amount): array;
}