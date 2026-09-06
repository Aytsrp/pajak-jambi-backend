<?php

namespace App\Enums;

enum TaxComponent: string
{
    case MakananMinuman = 'pbjt_makanan_minuman';
    case TenagaListrik = 'pbjt_tenaga_listrik';
    case Perhotelan = 'pbjt_perhotelan';
    case Parkir = 'pbjt_parkir';
    case KesenianHiburan = 'pbjt_kesenian_hiburan';
    case Reklame = 'pajak_reklame';
    case AirTanah = 'pajak_air_tanah';

    public function label(): string
    {
        return match ($this) {
            self::MakananMinuman => 'PBJT Makanan & Minuman',
            self::TenagaListrik => 'PBJT Tenaga Listrik',
            self::Perhotelan => 'PBJT Perhotelan',
            self::Parkir => 'PBJT Parkir',
            self::KesenianHiburan => 'PBJT Kesenian & Hiburan',
            self::Reklame => 'Pajak Reklame',
            self::AirTanah => 'Pajak Air Tanah',
        };
    }
}