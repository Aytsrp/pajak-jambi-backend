<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "API Pajak Daerah Kota Jambi",
    description: "API untuk aplikasi pembayaran pajak daerah (PBB-P2 & Pajak Usaha) Kota Jambi. Data Bapenda/Dukcapil masih menggunakan dummy service."
)]
#[OA\Server(
    url: "http://127.0.0.1:8000",
    description: "Local development server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "Sanctum Token"
)]

abstract class Controller
{
    //
}
