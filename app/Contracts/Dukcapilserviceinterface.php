<?php

namespace App\Contracts;

/**
 * Kontrak untuk cek keberadaan NIK di sistem Dukcapil.
 *
 * PENTING (keputusan produk): service ini HANYA mengecek apakah NIK
 * terdaftar/ada di sistem kependudukan. TIDAK melakukan pencocokan NIK
 * dengan kepemilikan NOP/NPWPD — itu dua hal yang sengaja dipisah.
 *
 * Implementasi saat ini: DummyDukcapilService (data hardcoded).
 * Nanti diganti: RealDukcapilService (butuh PKS resmi ke Dukcapil/Kemendagri).
 */
interface DukcapilServiceInterface
{
    /**
     * Cek apakah NIK terdaftar di sistem Dukcapil.
     *
     * @return array{
     *     valid: bool,
     *     nama: string|null,
     *     tanggal_lahir: string|null,
     *     alamat: string|null,
     *     raw: array
     * }
     */
    public function verifyNik(string $nik): array;
}