<?php

namespace App\Services\Dummy;

use App\Contracts\DukcapilServiceInterface;

class DummyDukcapilService implements DukcapilServiceInterface
{
    private const NIK_DATASET = [
        '1671010101010001' => ['full_name' => 'Ahmad Fauzi'],
        '1671010101010002' => ['full_name' => 'Siti Rahma'],
        '1671010101010003' => ['full_name' => 'Budi Santoso'],
        '1671010101010004' => ['full_name' => 'Dewi Lestari'],
        '1671010101010005' => ['full_name' => 'Rudi Hartono'],
        '1671010101010006' => ['full_name' => 'Maya Putri'],
    ];

    public function verifyNik(string $nik): ?array
    {
        return self::NIK_DATASET[$nik] ?? null;
    }
}
