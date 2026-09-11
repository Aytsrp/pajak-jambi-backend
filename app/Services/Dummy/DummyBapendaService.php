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
        '3671010203040004' => [
            'object_name' => 'Rumah Tinggal - Jl. Gedang Goreng',
            'owner_name' => 'Ranu Karnu',
            'object_address' => 'Jl. Gedang Goreng, Kel. Pasir Putih, Kec. Jambi Selatan, Kota Jambi',
        ],
        '3671010203040005' => [
            'object_name' => 'Ruko 25 Lantai - Jl. Kolonel Abunjani',
            'owner_name' => 'Rayhan',
            'object_address' => 'Jl. Kolonel Abunjani No. 21, Kel. Selamat, Kec. Telanaipura, Kota Jambi',
        ],
        '3671010203040006' => [
            'object_name' => 'Rumah Tinggal - Jl. Sultan Thaha No. 55',
            'owner_name' => 'Maya Putri',
            'object_address' => 'Jl. Sultan Thaha No. 55, Kel. Simpang IV Sipin, Kec. Telanaipura, Kota Jambi',
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
        '01.234.568.1-331' => [
            'business_name' => 'Parkir Simpang Kawat',
            'business_type' => 'PBJT Parkir',
            'owner_name' => 'Rudi Hartono',
        ],
        '01.234.568.2-331' => [
            'business_name' => 'Karaoke Melati',
            'business_type' => 'PBJT Kesenian & Hiburan',
            'owner_name' => 'Maya Putri',
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

        $currentYear = (int) date('Y');

        return match ($nopNumber) {
            '3671010203040002' => [
                $this->makeDummyBill(
                    taxPeriod: (string) $currentYear,
                    amountDue: 850_000,
                    dueDate: Carbon::create($currentYear, 9, 30),
                ),
                $this->makeDummyBill(
                    taxPeriod: (string) ($currentYear - 1),
                    amountDue: 1_200_000,
                    dueDate: Carbon::create($currentYear - 1, 9, 30),
                    simulateOverdue: true,
                ),
            ],
            '3671010203040004' => [
                $this->makeDummyBill(
                    taxPeriod: (string) $currentYear,
                    amountDue: 1_050_000,
                    dueDate: Carbon::create($currentYear, 10, 31),
                ),
            ],
            '3671010203040005' => [
                $this->makeDummyBill(
                    taxPeriod: (string) $currentYear,
                    amountDue: 720_000,
                    dueDate: Carbon::create($currentYear, 9, 30),
                ),
                $this->makeDummyBill(
                    taxPeriod: (string) ($currentYear - 1),
                    amountDue: 900_000,
                    dueDate: Carbon::create($currentYear - 1, 9, 30),
                    simulateOverdue: true,
                ),
            ],
            '3671010203040006' => [
                $this->makeDummyBill(
                    taxPeriod: (string) $currentYear,
                    amountDue: 430_000,
                    dueDate: Carbon::create($currentYear, 11, 30),
                ),
            ],
            default => [
                $this->makeDummyBill(
                    taxPeriod: (string) $currentYear,
                    amountDue: 850_000,
                    dueDate: Carbon::create($currentYear, 9, 30),
                ),
            ],
        };
    }

    public function getBillsForNpwpd(string $npwpdNumber): array
    {
        if (! isset(self::NPWPD_DATASET[$npwpdNumber])) {
            return [];
        }

        $period = date('Y-m');
        $lastMonth = Carbon::now()->subMonth()->format('Y-m');

        return match ($npwpdNumber) {
            '01.234.567.8-331' => [
                $this->makeDummyBill(
                    taxPeriod: $period,
                    amountDue: 450_000,
                    dueDate: Carbon::now()->addDays(10),
                    taxComponent: 'pbjt_makanan_minuman',
                ),
                $this->makeDummyBill(
                    taxPeriod: $period,
                    amountDue: 275_000,
                    dueDate: Carbon::now()->addDays(10),
                    taxComponent: 'pbjt_tenaga_listrik',
                ),
            ],
            '01.234.567.9-331' => [
                $this->makeDummyBill(
                    taxPeriod: $period,
                    amountDue: 3_500_000,
                    dueDate: Carbon::now()->addDays(14),
                    taxComponent: 'pbjt_perhotelan',
                ),
                $this->makeDummyBill(
                    taxPeriod: $period,
                    amountDue: 250_000,
                    dueDate: Carbon::now()->addDays(14),
                    taxComponent: 'pbjt_parkir',
                ),
                $this->makeDummyBill(
                    taxPeriod: $period,
                    amountDue: 680_000,
                    dueDate: Carbon::now()->addDays(14),
                    taxComponent: 'pbjt_tenaga_listrik',
                ),
                $this->makeDummyBill(
                    taxPeriod: $lastMonth,
                    amountDue: 3_200_000,
                    dueDate: Carbon::now()->subMonth()->endOfMonth(),
                    taxComponent: 'pbjt_perhotelan',
                    simulateOverdue: true,
                ),
            ],
            '01.234.568.1-331' => [
                $this->makeDummyBill(
                    taxPeriod: $period,
                    amountDue: 320_000,
                    dueDate: Carbon::now()->addDays(7),
                    taxComponent: 'pbjt_parkir',
                ),
            ],
            '01.234.568.2-331' => [
                $this->makeDummyBill(
                    taxPeriod: $period,
                    amountDue: 1_150_000,
                    dueDate: Carbon::now()->addDays(5),
                    taxComponent: 'pbjt_kesenian_hiburan',
                ),
                $this->makeDummyBill(
                    taxPeriod: $period,
                    amountDue: 190_000,
                    dueDate: Carbon::now()->addDays(5),
                    taxComponent: 'pbjt_tenaga_listrik',
                ),
            ],
            default => [],
        };
    }

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
            'tax_component' => $taxComponent,
            'amount_due' => $amountDue,
            'penalty_amount' => $penaltyAmount,
            'due_date' => $dueDate->toDateString(),
        ];
    }
}
