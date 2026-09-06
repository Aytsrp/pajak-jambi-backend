<?php

namespace App\Contracts;

interface DukcapilServiceInterface
{
    /**
     * @return array{full_name: string}|null null kalau NIK tidak ditemukan
     */
    public function verifyNik(string $nik): ?array;
}