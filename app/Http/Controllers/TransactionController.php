<?php

namespace App\Http\Controllers;

use App\Enums\BankCode;
use App\Enums\PaymentChannel;
use App\Http\Requests\InitiateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Services\Transaction\TransactionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionService $service,
    ) {}

    #[OA\Post(
        path: "/api/transactions/initiate",
        summary: "Langkah 1: pilih tagihan + metode bayar, buat transaksi PENDING",
        tags: ["Transactions"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["id_bill", "id_payment", "idempotency_key"],
                properties: [
                    new OA\Property(property: "id_bill", type: "integer", example: 1),
                    new OA\Property(property: "id_payment", type: "integer", example: 1),
                    new OA\Property(property: "idempotency_key", type: "string", example: "a1b2c3d4-uuid-dari-flutter"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Transaksi PENDING dibuat, tampilkan rincian ke user"),
            new OA\Response(response: 404, description: "Tagihan/metode bayar tidak ditemukan"),
            new OA\Response(response: 422, description: "Tagihan sudah lunas"),
        ]
    )]
    public function initiate(InitiateTransactionRequest $request)
    {
        $channel = PaymentChannel::from($request->payment_channel);
        $bankCode = $channel === PaymentChannel::BankTransfer ? BankCode::from($request->bank_code) : null;

        $transaction = $this->service->initiate(
            user: $request->user(),
            billId: $request->id_bill,
            channel: $channel,
            bankCode: $bankCode,
            idempotencyKey: $request->idempotency_key,
        );

        return TransactionResource::make($transaction->load('bill'))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Post(
        path: "/api/transactions/{id}/confirm-pin",
        summary: "Langkah 2: konfirmasi PIN, eksekusi pembayaran",
        tags: ["Transactions"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ["pin"], properties: [
                new OA\Property(property: "pin", type: "string", example: "123456"),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: "Pembayaran berhasil, bill jadi lunas, bukti tersedia"),
            new OA\Response(response: 402, description: "Gateway menolak pembayaran"),
            new OA\Response(response: 422, description: "PIN salah / transaksi bukan pending"),
            new OA\Response(response: 423, description: "PIN terkunci karena terlalu banyak percobaan"),
        ]
    )]

    #[OA\Get(
        path: "/api/transactions",
        summary: "Riwayat transaksi, dengan filter jenis pajak & rentang tanggal",
        tags: ["Transactions"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "tax_type", in: "query", schema: new OA\Schema(type: "string", enum: ["pbb", "pajak_usaha", "bphtb"])),
            new OA\Parameter(name: "from", in: "query", schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "to", in: "query", schema: new OA\Schema(type: "string", format: "date")),
        ],
        responses: [new OA\Response(response: 200, description: "Daftar riwayat transaksi")]
    )]
    public function index(Request $request)
    {
        $query = $request->user()->transactions()
            ->with(['bill', 'payment', 'reference' => fn($q) => $q->withTrashed()])
            ->latest('id_transactions');

        if ($request->filled('tax_type')) {
            $query->where('tax_type', $request->tax_type);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        return TransactionResource::collection($query->paginate(20));
    }

    #[OA\Get(
        path: "/api/transactions/{id}",
        summary: "Detail 1 transaksi",
        tags: ["Transactions"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Detail transaksi"),
            new OA\Response(response: 404, description: "Tidak ditemukan / bukan milik user ini"),
        ]
    )]
    public function show(Request $request, int $id)
    {
        $transaction = $request->user()->transactions()
            ->with(['bill', 'payment', 'reference' => fn($q) => $q->withTrashed()])
            ->findOrFail($id);

        return TransactionResource::make($transaction);
    }

    #[OA\Get(
        path: "/api/transactions/{id}/proof",
        summary: "Unduh bukti pembayaran dalam format PDF",
        tags: ["Transactions"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "File PDF bukti pembayaran", content: new OA\MediaType(mediaType: "application/pdf")),
            new OA\Response(response: 404, description: "Transaksi tidak ditemukan / belum sukses"),
        ]
    )]
    public function proof(Request $request, int $id)
    {
        $transaction = $request->user()->transactions()
            ->with(['bill', 'payment', 'reference' => fn($q) => $q->withTrashed()])
            ->where('status', 'success')
            ->findOrFail($id);

        $objectName = $transaction->reference?->object_name ?? $transaction->reference?->business_name ?? '-';

        $pdf = Pdf::loadView('pdf.proof', [
            'transaction' => $transaction,
            'objectName' => $objectName,
        ]);

        $filename = "bukti-{$transaction->transaction_ref}.pdf";

        return $request->boolean('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }
}
