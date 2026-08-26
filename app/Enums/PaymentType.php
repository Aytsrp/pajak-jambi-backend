<?php

namespace App\Enums;

enum PaymentType: string
{
    case CreditCard = 'credit_card';
    case EWallet = 'e_wallet';
    case BankTransfer = 'bank_transfer';
    case Qris = 'qris';

    public function label(): string
    {
        return match ($this) {
            self::CreditCard => 'Kartu Kredit',
            self::EWallet => 'E-Wallet',
            self::BankTransfer => 'Transfer Bank',
            self::Qris => 'QRIS',
        };
    }
}