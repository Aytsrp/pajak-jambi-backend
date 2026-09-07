<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentMethodRequest;
use App\Http\Resources\PaymentResource;
use App\Services\Payment\PaymentMethodService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PaymentMethodController extends Controller
{
    public function __construct(
        private readonly PaymentMethodService $service,
    ) {}

    #[OA\Get(
        path: "/api/payment-methods",
        summary: "List metode pembayaran tersimpan milik user",
        tags: ["Payment Methods"],
        security: [["bearerAuth" => []]],
        responses: [new OA\Response(response: 200, description: "Daftar metode pembayaran")]
    )]
    public function index(Request $request)
    {
        return PaymentResource::collection($request->user()->payments()->get());
    }

    #[OA\Post(
        path: "/api/payment-methods",
        summary: "Tambah metode pembayaran baru",
        tags: ["Payment Methods"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["type", "provider"],
                properties: [
                    new OA\Property(property: "type", type: "string", enum: ["bank_transfer", "qris"], example: "qris"),
                    new OA\Property(property: "provider", type: "string", example: "OVO"),
                    new OA\Property(property: "masked_number", type: "string", example: "****1234"),
                    new OA\Property(property: "is_default", type: "boolean", example: true),
                ]
            )
        ),
        responses: [new OA\Response(response: 201, description: "Metode pembayaran tersimpan")]
    )]
    public function store(StorePaymentMethodRequest $request)
    {
        $payment = $this->service->store($request->user(), $request->validated());

        return PaymentResource::make($payment)->response()->setStatusCode(201);
    }

    #[OA\Post(
        path: "/api/payment-methods/{id}/set-default",
        summary: "Jadikan metode ini sebagai default",
        tags: ["Payment Methods"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [new OA\Response(response: 200, description: "Berhasil diset sebagai default")]
    )]
    public function setDefault(Request $request, int $id)
    {
        $payment = $request->user()->payments()->findOrFail($id);
        $this->service->setAsDefault($request->user(), $payment);

        return PaymentResource::make($payment->fresh());
    }

    #[OA\Delete(
        path: "/api/payment-methods/{id}",
        summary: "Hapus metode pembayaran",
        tags: ["Payment Methods"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Berhasil dihapus"),
            new OA\Response(response: 404, description: "Tidak ditemukan / bukan milik user ini"),
        ]
    )]
    public function destroy(Request $request, int $id)
    {
        $payment = $request->user()->payments()->findOrFail($id);
        $payment->delete(); // hard delete aman — payments tidak jadi FK restrict di transactions (nullOnDelete)

        return response()->json(['message' => 'Metode pembayaran berhasil dihapus.']);
    }
}