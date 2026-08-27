<?php

namespace App\Http\Controllers;

use App\Exceptions\PemdaVerificationException;
use App\Http\Requests\RegisterNpwpdRequest;
use App\Http\Resources\NpwpdResource;
use App\Services\Pemda\BillSyncService;
use App\Services\Pemda\NpwpdRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class NpwpdController extends Controller
{
    public function __construct(
        private readonly NpwpdRegistrationService $registrationService,
        private readonly BillSyncService $billSync,
    ) {}

    #[OA\Get(
        path: "/api/npwpd",
        summary: "Ambil NPWPD milik user yang login (1 user maksimal 1 NPWPD)",
        tags: ["NPWPD (Pajak Usaha)"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Data NPWPD, atau data: null kalau belum daftar"),
        ]
    )]
    public function show(Request $request)
    {
        $npwpd = $request->user()->npwpd()->with('bills')->first();

        if (! $npwpd) {
            return response()->json([
                'message' => 'Belum ada NPWPD terdaftar.',
                'data' => null,
            ], 200);
        }

        return NpwpdResource::make($npwpd);
    }

    #[OA\Post(
        path: "/api/npwpd",
        summary: "Daftarkan NPWPD (dicek eksistensinya ke Bapenda)",
        tags: ["NPWPD (Pajak Usaha)"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["npwpd_number"],
                properties: [
                    new OA\Property(property: "npwpd_number", type: "string", example: "01.234.567.8-331"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "NPWPD berhasil didaftarkan"),
            new OA\Response(response: 422, description: "NPWPD tidak ditemukan di Bapenda, atau user sudah punya NPWPD lain"),
        ]
    )]
    public function store(RegisterNpwpdRequest $request)
    {
        try {
            $npwpd = $this->registrationService->register(
                user: $request->user(),
                npwpdNumber: $request->npwpd_number,
                ipAddress: $request->ip(),
            );
        } catch (PemdaVerificationException $e) {
            throw ValidationException::withMessages([
                'npwpd_number' => $e->getMessage(),
            ]);
        }

        return NpwpdResource::make($npwpd->load('bills'))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Post(
        path: "/api/npwpd/refresh",
        summary: "Cek ulang ke Bapenda apakah ada tagihan baru (misal setelah lapor bulanan di Lapor Pajak)",
        tags: ["NPWPD (Pajak Usaha)"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Hasil sync, termasuk meta.has_new_bill"),
            new OA\Response(response: 404, description: "User belum punya NPWPD terdaftar"),
        ]
    )]
    public function refresh(Request $request)
    {
        $npwpd = $request->user()->npwpd()->firstOrFail();

        $this->billSync->syncForNpwpd($npwpd);

        $npwpd = $npwpd->fresh('bills');

        return NpwpdResource::make($npwpd)->additional([
            'meta' => [
                'has_new_bill' => $npwpd->bills->isNotEmpty(),
                'note' => $npwpd->bills->isEmpty()
                    ? 'Belum ada tagihan bulan ini. Pastikan sudah lapor di Lapor Pajak Bapenda Kota Jambi.'
                    : null,
            ],
        ]);
    }
}