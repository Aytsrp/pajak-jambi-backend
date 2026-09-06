<?php

namespace App\Http\Controllers;

use App\Enums\BillStatus;
use App\Models\Bill;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TaxSummaryController extends Controller
{
    #[OA\Get(
        path: "/api/summary",
        summary: "Ringkasan total tagihan pajak (PBB + Pajak Usaha) milik user yang login",
        tags: ["Summary"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Ringkasan tagihan",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "total_tagihan", type: "number", example: 850000),
                        new OA\Property(property: "jumlah_tagihan_belum_dibayar", type: "integer", example: 2),
                        new OA\Property(property: "ada_tunggakan_lewat_tenggat", type: "boolean", example: false),
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request)
    {
        $user = $request->user();

        $nopIds = $user->nops()->pluck('id_nop');
        $npwpdId = $user->npwpd?->id_npwpd;

        $unpaidBills = Bill::query()
            ->where(function ($q) use ($nopIds) {
                $q->where('billable_type', 'nop')->whereIn('billable_id', $nopIds);
            })
            ->when($npwpdId, function ($q) use ($npwpdId) {
                $q->orWhere(function ($q2) use ($npwpdId) {
                    $q2->where('billable_type', 'npwpd')->where('billable_id', $npwpdId);
                });
            })
            ->whereIn('status', [BillStatus::Unpaid, BillStatus::Overdue])
            ->get();

        return response()->json([
            'total_tagihan' => (float) $unpaidBills->sum('total_amount'),
            'jumlah_tagihan_belum_dibayar' => $unpaidBills->count(),
            'ada_tunggakan_lewat_tenggat' => $unpaidBills->contains(fn($b) => $b->status === BillStatus::Overdue),
        ]);
    }

    public function onboardingStatus(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'has_nop' => $user->nops()->exists(),
            'has_npwpd' => $user->npwpd()->exists(),
            'onboarding_complete' => $user->nops()->exists() || $user->npwpd()->exists(),
        ]);
    }
}
