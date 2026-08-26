<?php

namespace App\Enums;

enum TaxType: string
{
    case Pbb = 'pbb';
    case PajakUsaha = 'pajak_usaha';
    case Bphtb = 'bphtb';

    public function label(): string
    {
        return match ($this) {
            self::Pbb => 'PBB-P2',
            self::PajakUsaha => 'Pajak Usaha',
            self::Bphtb => 'BPHTB',
        };
    }
}