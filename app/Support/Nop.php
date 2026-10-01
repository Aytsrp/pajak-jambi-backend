<?php

namespace App\Support;

/**
 * NOP (Nomor Objek Pajak) 18 digit, format standar SISMIOP:
 * propinsi(2) + dati2(2) + kecamatan(3) + kelurahan(3) + blok(3) + no urut(4) + jenis OP(1).
 *
 * Di Oracle, ketujuh bagian ini adalah kolom terpisah (primary key komposit),
 * bukan satu kolom NOP -- jadi NOP yang diketik user perlu dipecah dulu.
 */
final class Nop
{
    private const SEGMENT_LENGTHS = [2, 2, 3, 3, 3, 4, 1];

    public static function isValid(string $nop): bool
    {
        return (bool) preg_match('/^\d{18}$/', $nop);
    }

    /** @return list<string> 7 segmen, urutannya sama dengan SEGMENT_LENGTHS */
    public static function split(string $nop): array
    {
        $segments = [];
        $offset = 0;

        foreach (self::SEGMENT_LENGTHS as $length) {
            $segments[] = substr($nop, $offset, $length);
            $offset += $length;
        }

        return $segments;
    }
}