<?php

namespace App\Contracts;

interface BapendaServiceInterface
{
    /**
     * Cek eksistensi NOP di sistem Bapenda.
     * Return null kalau tidak ditemukan, atau array data objek kalau ada.
     *
     * @return array{object_name: string, owner_name: string, object_address: string}|null
     */
    public function findNop(string $nopNumber): ?array;

    /**
     * Cek eksistensi NPWPD di sistem Bapenda.
     *
     * @return array{business_name: string, business_type: string, owner_name: string}|null
     */
    public function findNpwpd(string $npwpdNumber): ?array;

    /**
     * Ambil daftar tagihan aktif untuk sebuah NOP dari Bapenda.
     * Dipanggil saat pertama daftar & saat refresh/pull-to-refresh.
     *
     * @return array<int, array{tax_period: string, amount_due: float, penalty_amount: float, due_date: string}>
     */
    public function getBillsForNop(string $nopNumber): array;

    /**
     * Ambil daftar tagihan aktif untuk sebuah NPWPD dari Bapenda.
     * Catatan bisnis: tagihan Pajak Usaha baru muncul SETELAH user lapor
     * bulanan di website Lapor Pajak milik Bapenda (di luar aplikasi ini) —
     * jadi bisa saja hasilnya kosong kalau belum ada laporan bulan berjalan.
     *
     * @return array<int, array{tax_period: string, amount_due: float, penalty_amount: float, due_date: string}>
     */
    public function getBillsForNpwpd(string $npwpdNumber): array;
}