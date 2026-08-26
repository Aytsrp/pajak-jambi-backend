<?php

namespace App\Services\Pemda;

use App\Contracts\BapendaServiceInterface;
use App\Enums\BillStatus;
use App\Models\Bill;
use App\Models\Nop;
use App\Models\Npwpd;
use Illuminate\Support\Carbon;

class BillSyncService
{
    public function __construct(
        private readonly BapendaServiceInterface $bapenda,
    ) {}

    public function syncForNop(Nop $nop): void
    {
        $bills = $this->bapenda->getBillsForNop($nop->nop_number);
        $this->upsertBills($nop, $bills);
    }

    public function syncForNpwpd(Npwpd $npwpd): void
    {
        $bills = $this->bapenda->getBillsForNpwpd($npwpd->npwpd_number);
        $this->upsertBills($npwpd, $bills);
    }

    /**
     * @param Nop|Npwpd $billable
     * @param array<int, array{tax_period: string, amount_due: float, penalty_amount: float, due_date: string}> $bills
     */
    private function upsertBills($billable, array $bills): void
    {
        foreach ($bills as $data) {
            /** @var Bill $existing */
            $existing = $billable->bills()
                ->where('tax_period', $data['tax_period'])
                ->first();

            // Kalau sudah lunas, jangan ditimpa lagi oleh hasil sync
            // (status 'paid' di aplikasi kita adalah sumber kebenaran lokal
            // setelah transaksi sukses — sync dari Bapenda tidak boleh menimpanya
            // balik ke unpaid kalau ada delay data di sisi mereka).
            if ($existing && $existing->status === BillStatus::Paid) {
                continue;
            }

            $dueDate = Carbon::parse($data['due_date']);
            $totalAmount = $data['amount_due'] + $data['penalty_amount'];
            $status = $dueDate->isPast() ? BillStatus::Overdue : BillStatus::Unpaid;

            $billable->bills()->updateOrCreate(
                ['tax_period' => $data['tax_period']],
                [
                    'amount_due' => $data['amount_due'],
                    'penalty_amount' => $data['penalty_amount'],
                    'total_amount' => $totalAmount,
                    'status' => $status,
                    'due_date' => $dueDate,
                    'fetched_at' => now(),
                ]
            );
        }
    }
}