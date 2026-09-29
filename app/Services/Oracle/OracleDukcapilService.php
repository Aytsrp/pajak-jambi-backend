<?php

namespace App\Services\Oracle;

use App\Contracts\DukcapilServiceInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDOException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * Verifikasi NIK ke database Oracle (read-only).
 * Sumber: PBBDUMMY.DAT_SUBJEK_PAJAK -> SUBJEK_PAJAK_ID = NIK, NM_WP = nama.
 */
class OracleDukcapilService implements DukcapilServiceInterface
{
    private const TABLE = 'PBBDUMMY.DAT_SUBJEK_PAJAK';

    private const NIK_COLUMN = 'SUBJEK_PAJAK_ID';

    // Kolom NIK bertipe CHAR(30): Oracle mengisi sisa panjangnya dengan spasi.
    private const NIK_LENGTH = 30;

    private const NAME_COLUMN = 'NM_WP';

    public function verifyNik(string $nik): ?array
    {
        // RPAD memotong nilai yang lebih panjang dari kolom -> jangan sampai salah cocok.
        if (strlen($nik) > self::NIK_LENGTH) {
            return null;
        }

        try {
            // NIK dikirim sebagai bind parameter (aman dari SQL injection).
            // RPAD di sisi nilai (bukan RTRIM di sisi kolom) supaya index primary key terpakai.
            $row = DB::connection('oracle')
                ->table(self::TABLE)
                ->select(self::NAME_COLUMN)
                ->whereRaw(self::NIK_COLUMN.' = RPAD(?, '.self::NIK_LENGTH.')', [$nik])
                ->first();
        } catch (QueryException|PDOException $e) {
            // Yang dicatat hanya pesan ORA-xxxxx; pesan QueryException memuat NIK di binding.
            Log::error('Query Oracle (verifikasi NIK) gagal', [
                'error' => ($e->getPrevious() ?? $e)->getMessage(),
            ]);

            // 503, BUKAN "NIK tidak ditemukan": Oracle mati bukan berarti NIK user salah.
            throw new ServiceUnavailableHttpException(
                30,
                'Layanan data pajak sedang tidak tersedia. Silakan coba lagi nanti.'
            );
        }

        if ($row === null) {
            return null;
        }

        // Huruf besar/kecil nama kolom hasil query tergantung driver -> samakan.
        $row = array_change_key_case((array) $row, CASE_LOWER);

        return ['full_name' => trim((string) $row[strtolower(self::NAME_COLUMN)])];
    }
}