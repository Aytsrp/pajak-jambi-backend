<?php

namespace App\Contracts;

use App\Models\Transaction;

interface QrisGatewayInterface
{
    /**
     * @return array{qr_string: string, qr_image_url: string, expired_at: \Illuminate\Support\Carbon}
     */
    public function generate(Transaction $transaction): array;

    public function verifyNotification(array $payload): bool;

    /**
     * @return array{order_id: string, is_success: bool, is_failed: bool, gateway_ref: string}
     */
    public function parseNotification(array $payload): array;
}