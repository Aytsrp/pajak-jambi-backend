<?php

namespace App\Contracts;

/**
 * Kontrak integrasi ke Bapenda Kota Jambi (SIMPAD).
 *
 * PENTING (keputusan produk): verifyNop() dan verifyNpwpd() HANYA mengecek
 * apakah nomor tersebut ada/terdaftar di database Bapenda. Tidak ada
 * pengecekan kecocokan dengan NIK pemilik — user boleh input NOP/NPWPD
 * siapa saja yang mereka mau bayarkan (misal bayarkan pajak orang tua),
 * selama nomornya valid ada di Bapenda.
 *
 * Method lain (getTagihanPbb, dll) disiapkan untuk modul selanjutnya
 * (Modul 4/5) — Bapenda-lah yang menghitung nominal, bukan aplikasi ini.
 *
 * Implementasi saat ini: DummyBapendaService (data hardcoded).
 * Nanti diganti: RealBapendaService (butuh endpoint resmi SIMPAD dari tim IT Bapenda).
 */
interface BapendaServiceInterface
{
    /**
     * Cek apakah NOP terdaftar di Bapenda.
     *
     * @return array{valid: bool, nama_wp: string|null, alamat_objek: string|null, raw: array}
     */
    public function verifyNop(string $nop): array;

    /**
     * Cek apakah NPWPD terdaftar di Bapenda.
     *
     * @return array{valid: bool, nama_usaha: string|null, jenis_usaha: string|null, alamat_usaha: string|null, raw: array}
     */
    public function verifyNpwpd(string $npwpd): array;
}