<?php

namespace App\Http\Controllers;

use App\Exceptions\PemdaVerificationException;
use App\Http\Requests\RegisterNopRequest;
use App\Http\Resources\NopResource;
use App\Services\Pemda\BillSyncService;
use App\Services\Pemda\NopRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class NopController extends Controller
{
    public function __construct(
        private readonly NopRegistrationService $registrationService,
        private readonly BillSyncService $billSync,
    ) {}

    #[OA\Get(
        path: "/api/nops",
        summary: "List semua NOP milik user yang login beserta tagihannya",
        tags: ["NOP (PBB-P2)"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Daftar NOP berhasil diambil"),
            new OA\Response(response: 401, description: "Belum login / token tidak valid"),
        ]
    )]
    public function index(Request $request)
    {
        $nops = $request->user()->nops()->with('bills')->get();

        return NopResource::collection($nops);
    }

    #[OA\Post(
        path: "/api/nops",
        summary: "Daftarkan NOP baru (dicek eksistensinya ke Bapenda)",
        tags: ["NOP (PBB-P2)"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nop_number"],
                properties: [
                    new OA\Property(property: "nop_number", type: "string", example: "3671010203040001"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "NOP berhasil didaftarkan, langsung berisi tagihan"),
            new OA\Response(response: 422, description: "NOP tidak ditemukan di sistem Bapenda"),
            new OA\Response(response: 429, description: "Terlalu banyak percobaan, coba lagi nanti"),
        ]
    )]
    public function store(RegisterNopRequest $request)
    {
        try {
            $nop = $this->registrationService->register(
                user: $request->user(),
                nopNumber: $request->nop_number,
                ipAddress: $request->ip(),
            );
        } catch (PemdaVerificationException $e) {
            throw ValidationException::withMessages([
                'nop_number' => $e->getMessage(),
            ]);
        }

        return NopResource::make($nop->load('bills'))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: "/api/nops/{id}",
        summary: "Detail 1 NOP beserta tagihannya",
        tags: ["NOP (PBB-P2)"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Detail NOP"),
            new OA\Response(response: 404, description: "NOP tidak ditemukan / bukan milik user ini"),
        ]
    )]
    public function show(Request $request, int $id)
    {
        $nop = $request->user()->nops()->with('bills')->findOrFail($id);

        return NopResource::make($nop);
    }

    #[OA\Post(
        path: "/api/nops/{id}/refresh",
        summary: "Tarik ulang tagihan terbaru dari Bapenda untuk NOP ini",
        tags: ["NOP (PBB-P2)"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Tagihan berhasil disinkron ulang"),
            new OA\Response(response: 404, description: "NOP tidak ditemukan / bukan milik user ini"),
        ]
    )]
    public function refresh(Request $request, int $id)
    {
        $nop = $request->user()->nops()->findOrFail($id);

        $this->billSync->syncForNop($nop);

        return NopResource::make($nop->fresh('bills'));
    }
}