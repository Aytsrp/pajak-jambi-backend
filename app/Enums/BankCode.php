<?php

namespace App\Enums;

enum BankCode: string
{
    case BankJambi = 'bank_jambi';
    case Mandiri = 'mandiri';
    case Bri = 'bri';
    case Bni = 'bni';
    case Btn = 'btn';

    public function label(): string
    {
        return match ($this) {
            self::BankJambi => 'Bank Jambi',
            self::Mandiri => 'Bank Mandiri',
            self::Bri => 'Bank BRI',
            self::Bni => 'Bank BNI',
            self::Btn => 'Bank BTN',
        };
    }

    public function vaPrefix(): string
    {
        return match ($this) {
            self::BankJambi => '899',
            self::Mandiri => '88908',
            self::Bri => '26215',
            self::Bni => '8808',
            self::Btn => '8300',
        };
    }
}