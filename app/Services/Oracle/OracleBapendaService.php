<?php

namespace App\Services\Oracle;

use App\Support\Nop;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDOException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * Data objek pajak (NOP) & tagihan PBB dari Oracle (read-only).
 * Sumber: PBBDUMMY.DAT_OBJEK_PAJAK, PBBDUMMY.DAT_SUBJEK_PAJAK, PBBDUMMY.SPPT.
 *
 * NPWPD tidak ditangani di sini -- lihat App\Services\Pemda\HybridBapendaService.
 */
class OracleBapendaService
{
    private const TABLE_OBJEK = 'PBBDUMMY.DAT_OBJEK_PAJAK';

    private const TABLE_SUBJEK = 'PBBDUMMY.DAT_SUBJEK_PAJAK';

    private const TABLE_SPPT = 'PBBDUMMY.SPPT';

    // Urutan HARUS sama dengan App\Support\Nop::SEGMENT_LENGTHS.
    private const NOP_COLUMNS = [
        'KD_PROPINSI', 'KD_DATI2', 'KD_KECAMATAN', 'KD_KELURAHAN', 'KD_BLOK', 'NO_URUT', 'KD_JNS_OP',
    ];

    private const STATUS_BELUM_BAYAR = '0';

    /**
     * Info objek pajak untuk ditampilkan sebelum user konfirmasi pendaftaran NOP.
     * Nama pemilik (owner_name) bisa null -- artinya SUBJEK_PAJAK_ID di Oracle
     * belum tersambung ke DAT_SUBJEK_PAJAK. Itu kondisi wajar, BUKAN error.
     *
     * @return array{object_address: string, land_area_m2: float, building_area_m2: float, njop_land: float, njop_building: float, owner_name: ?string}|null
     */
    public function findNop(string $nopNumber): ?array
    {
        if (! Nop::isValid($nopNumber)) {
            return null;
        }

        $segments = Nop::split($nopNumber);

        $row = $this->run(function () use ($segments) {
            $query = DB::connection('oracle')
                ->table(self::TABLE_OBJEK.' as o')
                ->leftJoin(self::TABLE_SUBJEK.' as s', 's.SUBJEK_PAJAK_ID', '=', 'o.SUBJEK_PAJAK_ID')
                ->select([
                    'o.JALAN_OP', 'o.BLOK_KAV_NO_OP', 'o.RW_OP', 'o.RT_OP',
                    'o.TOTAL_LUAS_BUMI', 'o.TOTAL_LUAS_BNG', 'o.NJOP_BUMI', 'o.NJOP_BNG',
                    's.NM_WP',
                ]);

            $this->whereNop($query, 'o.', $segments);

            return $query->first();
        });

        if ($row === null) {
            return null;
        }

        $row = array_change_key_case((array) $row, CASE_LOWER);

        return [
            'object_address' => $this->composeAddress($row),
            'land_area_m2' => (float) $row['total_luas_bumi'],
            'building_area_m2' => (float) $row['total_luas_bng'],
            'njop_land' => (float) $row['njop_bumi'],
            'njop_building' => (float) $row['njop_bng'],
            // null = SUBJEK_PAJAK_ID belum tersambung ke DAT_SUBJEK_PAJAK di data ini.
            'owner_name' => isset($row['nm_wp']) ? trim((string) $row['nm_wp']) : null,
        ];
    }

    /**
     * Semua tahun pajak yang STATUS_PEMBAYARAN_SPPT-nya masih '0' (belum dibayar),
     * terurut dari yang paling lama. Satu NOP bisa punya tunggakan beberapa tahun sekaligus.
     *
     * 'penalty_amount' adalah ESTIMASI (2%/bulan keterlambatan, maks 24 bulan) --
     * SPPT tidak menyimpan denda untuk tagihan yang belum dibayar. Nominal final
     * ditentukan sistem pemerintah saat pembayaran diproses.
     *
     * @return list<array{tax_period: string, amount_due: float, penalty_amount: float, due_date: string, payment_code: ?string}>
     */
    public function getBillsForNop(string $nopNumber): array
    {
        if (! Nop::isValid($nopNumber)) {
            return [];
        }

        $segments = Nop::split($nopNumber);

        $rows = $this->run(function () use ($segments) {
            $query = DB::connection('oracle')
                ->table(self::TABLE_SPPT)
                ->select(['THN_PAJAK_SPPT', 'PBB_YG_HARUS_DIBAYAR_SPPT', 'TGL_JATUH_TEMPO_SPPT', 'KODE_BAYAR'])
                ->where('STATUS_PEMBAYARAN_SPPT', self::STATUS_BELUM_BAYAR)
                ->orderBy('THN_PAJAK_SPPT');

            $this->whereNop($query, '', $segments);

            return $query->get();
        });

        return $rows
            ->map(fn ($row) => array_change_key_case((array) $row, CASE_LOWER))
            ->filter(function ($row) {
                if ($row['tgl_jatuh_tempo_sppt'] === null) {
                    Log::warning('SPPT tanpa tanggal jatuh tempo, dilewati', ['thn' => $row['thn_pajak_sppt']]);

                    return false;
                }

                return true;
            })
            ->map(function ($row) {
                $principal = (float) $row['pbb_yg_harus_dibayar_sppt'];
                $dueDate = Carbon::parse($row['tgl_jatuh_tempo_sppt']);

                return [
                    'tax_period' => trim((string) $row['thn_pajak_sppt']),
                    'amount_due' => $principal,
                    'penalty_amount' => $this->estimatePenalty($principal, $dueDate),
                    'due_date' => $dueDate->toDateString(),
                    'payment_code' => isset($row['kode_bayar']) ? trim((string) $row['kode_bayar']) : null,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Estimasi denda PBB: 2% per bulan keterlambatan dari jatuh tempo, maksimal 24 bulan.
     * ESTIMASI untuk ditampilkan ke user -- bukan nominal final.
     */
    private function estimatePenalty(float $principal, Carbon $dueDate): float
    {
        if ($dueDate->isFuture()) {
            return 0.0;
        }

        $maxMonths = (int) config('pemda_services.denda_pbb.maksimal_bulan');
        $ratePerMonth = ((float) config('pemda_services.denda_pbb.persen_per_bulan')) / 100;

        $monthsLate = min((int) ceil($dueDate->diffInDays(now()) / 30), $maxMonths);

        return round($principal * $ratePerMonth * $monthsLate, 0);
    }

    private function composeAddress(array $row): string
    {
        $parts = array_values(array_filter([
            trim((string) ($row['jalan_op'] ?? '')),
            trim((string) ($row['blok_kav_no_op'] ?? '')),
        ], fn ($v) => $v !== ''));

        $rt = trim((string) ($row['rt_op'] ?? ''));
        $rw = trim((string) ($row['rw_op'] ?? ''));

        if ($rt !== '' || $rw !== '') {
            $parts[] = "RT {$rt}/RW {$rw}";
        }

        return $parts === [] ? '-' : implode(', ', $parts);
    }

    /** @param list<string> $segments */
    private function whereNop(\Illuminate\Database\Query\Builder $query, string $prefix, array $segments): void
    {
        foreach (self::NOP_COLUMNS as $i => $column) {
            $query->where($prefix.$column, $segments[$i]);
        }
    }

    private function run(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (QueryException|PDOException $e) {
            Log::error('Query Oracle (Bapenda/NOP) gagal', [
                'error' => ($e->getPrevious() ?? $e)->getMessage(),
            ]);

            throw new ServiceUnavailableHttpException(
                30,
                'Layanan data pajak sedang tidak tersedia. Silakan coba lagi nanti.'
            );
        }
    }
}