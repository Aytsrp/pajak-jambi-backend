<?php

namespace App\Http\Controllers;

use App\Enums\BillStatus;
use App\Models\Bill;
use Illuminate\Http\Request;

class TaxSummaryController extends Controller
{
    /**
     * GET /api/summary
     */
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
            'ada_tunggakan_lewat_tenggat' => $unpaidBills->contains(fn ($b) => $b->status === BillStatus::Overdue),
        ]);
    }
}