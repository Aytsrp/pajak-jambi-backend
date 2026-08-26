<?php

/**
 * Konfigurasi driver integrasi Pemda Kota Jambi.
 *
 * Saat ini hanya "dummy" yang tersedia karena akses API resmi Dukcapil & Bapenda
 * belum ada. Nanti kalau sudah dapat endpoint resmi:
 *   1. Buat class RealDukcapilService & RealBapendaService (implements contract yang sama)
 *   2. Tambahkan case 'real' di PemdaServiceProvider
 *   3. Ubah PEMDA_SERVICE_DRIVER=real di .env
 * Tidak perlu ubah controller/service lain sama sekali.
 */
return [
    'driver' => env('PEMDA_SERVICE_DRIVER', 'dummy'),
];