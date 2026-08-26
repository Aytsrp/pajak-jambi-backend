<?php

namespace App\Services\Dummy;

use App\Contracts\DukcapilServiceInterface;

/**
 * Implementasi SEMENTARA selagi Pemda belum menyediakan akses API Dukcapil asli.
 * Data di bawah 100% fiktif, dipakai untuk keperluan development & demo.
 *
 * Cara pakai untuk testing: gunakan salah satu NIK di $dummyNiks di bawah untuk
 * mensimulasikan "NIK ditemukan", NIK lain di luar itu -> dianggap "tidak ditemukan".
 */
class DummyDukcapilService implements DukcapilServiceInterface
{
    /**
     * @var array<string, array{nama: string, tanggal_lahir: string, alamat: string}>
     */
    private array $dummyNiks = [
        '1571010101900001' => [
            'nama' => 'Budi Santoso',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Sultan Thaha No. 10, Telanaipura, Kota Jambi',
        ],
        '1571020202920002' => [
            'nama' => 'Siti Aminah',
            'tanggal_lahir' => '1992-02-02',
            'alamat' => 'Jl. Hayam Wuruk No. 25, Jelutung, Kota Jambi',
        ],
        '1571030303880003' => [
            'nama' => 'Ahmad Fauzi',
            'tanggal_lahir' => '1988-03-03',
            'alamat' => 'Jl. Kapten Pattimura No. 5, Kotabaru, Kota Jambi',
        ],
    ];

    public function verifyNik(string $nik): array
    {
        $data = $this->dummyNiks[$nik] ?? null;

        if ($data === null) {
            return [
                'valid' => false,
                'nama' => null,
                'tanggal_lahir' => null,
                'alamat' => null,
                'raw' => ['source' => 'dummy_dukcapil', 'nik' => $nik, 'found' => false],
            ];
        }

        return [
            'valid' => true,
            'nama' => $data['nama'],
            'tanggal_lahir' => $data['tanggal_lahir'],
            'alamat' => $data['alamat'],
            'raw' => ['source' => 'dummy_dukcapil', 'nik' => $nik, 'found' => true, 'data' => $data],
        ];
    }
}