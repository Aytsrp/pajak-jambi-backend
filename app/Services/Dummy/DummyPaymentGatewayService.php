<?php

namespace App\Services\Dummy;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;
use Illuminate\Support\Str;

class DummyPaymentGatewayService implements PaymentGatewayInterface
{
    public function charge(Payment $payment, float $amount): array
    {
        if ($payment->provider === 'FAIL_TEST') {
            return [
                'success' => false,
                'gateway_ref' => '',
                'message' => 'Pembayaran ditolak oleh penyedia (simulasi gagal).',
            ];
        }

        return [
            'success' => true,
            'gateway_ref' => 'DUMMY-' . strtoupper(Str::random(12)),
            'message' => 'Pembayaran berhasil diproses (simulasi).',
        ];
    }
}