<?php

namespace App\Services\Dummy;

use App\Contracts\DukcapilServiceInterface;

class DummyDukcapilService implements DukcapilServiceInterface
{
    private const NIK_DATASET = [
        '1671010101010001' => ['full_name' => 'Ahmad Fauzi'],
        '1671010101010002' => ['full_name' => 'Siti Rahma'],
        '1671010101010003' => ['full_name' => 'Budi Santoso'],
    ];

    public function verifyNik(string $nik): ?array
    {
        return self::NIK_DATASET[$nik] ?? null;
    }
}