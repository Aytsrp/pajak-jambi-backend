<?php

namespace App\Services\Dummy;

use App\Contracts\BapendaServiceInterface;
use Carbon\Carbon;

class DummyBapendaService implements BapendaServiceInterface
{
    /**
     * Dataset dummy NOP. Key = nomor NOP yang "valid" di sistem Bapenda simulasi.
     */
    private const NOP_DATASET = [
        '3671010203040001' => [
            'object_name' => 'Rumah Tinggal - Jl. Slamet Riyadi No. 12',
            'owner_name' => 'Ahmad Fauzi',
            'object_address' => 'Jl. Slamet Riyadi No. 12, Kel. Sungai Asam, Kec. Pasar Jambi, Kota Jambi',
        ],
        '3671010203040002' => [
            'object_name' => 'Tanah Kosong - Jl. Hayam Wuruk',
            'owner_name' => 'Ahmad Fauzi',
            'object_address' => 'Jl. Hayam Wuruk, Kel. Talang Bakung, Kec. Jambi Selatan, Kota Jambi',
        ],
        '3671010203040003' => [
            'object_name' => 'Ruko 2 Lantai - Jl. Gatot Subroto',
            'owner_name' => 'Siti Rahma',
            'object_address' => 'Jl. Gatot Subroto No. 45, Kel. Legok, Kec. Telanaipura, Kota Jambi',
        ],
    ];

    /**
     * Dataset dummy NPWPD.
     */
    private const NPWPD_DATASET = [
        '01.234.567.8-331' => [
            'business_name' => 'Rumah Makan Sedap Rasa',
            'business_type' => 'PBJT Makanan & Minuman',
            'owner_name' => 'Budi Santoso',
        ],
        '01.234.567.9-331' => [
            'business_name' => 'Hotel Mega Jambi',
            'business_type' => 'PBJT Perhotelan',
            'owner_name' => 'PT Mega Jambi Sejahtera',
        ],
    ];

    public function findNop(string $nopNumber): ?array
    {
        return self::NOP_DATASET[$nopNumber] ?? null;
    }

    public function findNpwpd(string $npwpdNumber): ?array
    {
        return self::NPWPD_DATASET[$npwpdNumber] ?? null;
    }

    public function getBillsForNop(string $nopNumber): array
    {
        if (! isset(self::NOP_DATASET[$nopNumber])) {
            return [];
        }

        // Simulasi: 1 tagihan tahun berjalan, kadang ada tunggakan tahun lalu.
        $currentYear = (int) date('Y');

        $bills = [
            $this->makeDummyBill(
                taxPeriod: (string) $currentYear,
                amountDue: 850_000,
                dueDate: Carbon::create($currentYear, 9, 30),
            ),
        ];

        // NOP kedua disimulasikan sudah lewat tenggat tahun lalu (ada denda)
        if ($nopNumber === '3671010203040002') {
            $bills[] = $this->makeDummyBill(
                taxPeriod: (string) ($currentYear - 1),
                amountDue: 1_200_000,
                dueDate: Carbon::create($currentYear - 1, 9, 30),
                simulateOverdue: true,
            );
        }

        return $bills;
    }

    public function getBillsForNpwpd(string $npwpdNumber): array
    {
        if (! isset(self::NPWPD_DATASET[$npwpdNumber])) {
            return [];
        }

        if ($npwpdNumber === '01.234.567.8-331') {
            return [
                $this->makeDummyBill(
                    taxPeriod: date('Y-m'),
                    amountDue: 450_000,
                    dueDate: Carbon::now()->addDays(10),
                    taxComponent: 'pbjt_makanan_minuman',
                ),
                $this->makeDummyBill(
                    taxPeriod: date('Y-m'),
                    amountDue: 275_000,
                    dueDate: Carbon::now()->addDays(10),
                    taxComponent: 'pbjt_tenaga_listrik',
                ),
            ];
        }

        return [];
    }

    // ── Helper ────────────────────────────────────────
    
    private function makeDummyBill(
        string $taxPeriod,
        float $amountDue,
        Carbon $dueDate,
        ?string $taxComponent = null,
        bool $simulateOverdue = false,
    ): array {
        $penaltyAmount = 0.0;
        if ($simulateOverdue || $dueDate->isPast()) {
            $monthsLate = max(1, $dueDate->diffInMonths(now()));
            $penaltyAmount = round($amountDue * 0.02 * $monthsLate, 2);
        }

        return [
            'tax_period' => $taxPeriod,
            'tax_component' => $taxComponent, // ← tambahan
            'amount_due' => $amountDue,
            'penalty_amount' => $penaltyAmount,
            'due_date' => $dueDate->toDateString(),
        ];
    }
}