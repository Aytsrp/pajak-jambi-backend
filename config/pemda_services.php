<?php

/**
 * Parameter perhitungan denda PBB untuk ESTIMASI di aplikasi (bukan nominal final --
 * lihat App\Services\Oracle\OracleBapendaService::estimatePenalty()).
 * Sesuaikan kalau ada perubahan regulasi daerah.
 */
return [
    'denda_pbb' => [
        'persen_per_bulan' => 2,
        'maksimal_bulan' => 24,
    ],
];