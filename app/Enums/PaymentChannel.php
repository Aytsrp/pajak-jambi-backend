<?php

namespace App\Enums;

enum PaymentChannel: string
{
    case BankTransfer = 'bank_transfer';
    case Qris = 'qris';

    public function label(): string
    {
        return match ($this) {
            self::BankTransfer => 'Transfer Virtual Account',
            self::Qris => 'QRIS',
        };
    }
}