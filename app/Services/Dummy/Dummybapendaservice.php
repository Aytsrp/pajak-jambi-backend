<?php

namespace App\Services\Dummy;

use App\Contracts\BapendaServiceInterface;

/**
 * Implementasi SEMENTARA selagi integrasi SIMPAD Bapenda belum tersedia.
 * Data di bawah 100% fiktif, dipakai untuk keperluan development & demo magang.
 *
 * Cara pakai untuk testing: gunakan salah satu NOP/NPWPD di bawah untuk
 * mensimulasikan "ditemukan", nomor lain di luar itu -> "tidak ditemukan".
 */
class DummyBapendaService implements BapendaServiceInterface
{
    /**
     * @var array<string, array{nama_wp: string, alamat_objek: string}>
     */
    private array $dummyNops = [
        '157101010001000001' => [
            'nama_wp' => 'Budi Santoso',
            'alamat_objek' => 'Jl. Sultan Thaha No. 10, Telanaipura, Kota Jambi',
        ],
        '157102020002000002' => [
            'nama_wp' => 'Siti Aminah',
            'alamat_objek' => 'Jl. Hayam Wuruk No. 25, Jelutung, Kota Jambi',
        ],
        '157103030003000003' => [
            'nama_wp' => 'PT Sumber Rejeki Jambi',
            'alamat_objek' => 'Jl. Gatot Subroto No. 88, Pasar Jambi, Kota Jambi',
        ],
    ];

    /**
     * @var array<string, array{nama_usaha: string, jenis_usaha: string, alamat_usaha: string}>
     */
    private array $dummyNpwpds = [
        'NPWPD-JBI-0001' => [
            'nama_usaha' => 'Rumah Makan Sederhana Jambi',
            'jenis_usaha' => 'restoran',
            'alamat_usaha' => 'Jl. Kolonel Abunjani No. 12, Kota Jambi',
        ],
        'NPWPD-JBI-0002' => [
            'nama_usaha' => 'Hotel Abadi Jambi',
            'jenis_usaha' => 'hotel',
            'alamat_usaha' => 'Jl. Sultan Agung No. 45, Kota Jambi',
        ],
    ];

    public function verifyNop(string $nop): array
    {
        $data = $this->dummyNops[$nop] ?? null;

        if ($data === null) {
            return [
                'valid' => false,
                'nama_wp' => null,
                'alamat_objek' => null,
                'raw' => ['source' => 'dummy_bapenda', 'nop' => $nop, 'found' => false],
            ];
        }

        return [
            'valid' => true,
            'nama_wp' => $data['nama_wp'],
            'alamat_objek' => $data['alamat_objek'],
            'raw' => ['source' => 'dummy_bapenda', 'nop' => $nop, 'found' => true, 'data' => $data],
        ];
    }

    public function verifyNpwpd(string $npwpd): array
    {
        $data = $this->dummyNpwpds[$npwpd] ?? null;

        if ($data === null) {
            return [
                'valid' => false,
                'nama_usaha' => null,
                'jenis_usaha' => null,
                'alamat_usaha' => null,
                'raw' => ['source' => 'dummy_bapenda', 'npwpd' => $npwpd, 'found' => false],
            ];
        }

        return [
            'valid' => true,
            'nama_usaha' => $data['nama_usaha'],
            'jenis_usaha' => $data['jenis_usaha'],
            'alamat_usaha' => $data['alamat_usaha'],
            'raw' => ['source' => 'dummy_bapenda', 'npwpd' => $npwpd, 'found' => true, 'data' => $data],
        ];
    }
}