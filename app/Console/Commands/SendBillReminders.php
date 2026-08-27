<?php

namespace App\Console\Commands;

use App\Enums\BillStatus;
use App\Enums\NotificationType;
use App\Models\Bill;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendBillReminders extends Command
{
    protected $signature = 'tax:send-reminders';
    protected $description = 'Kirim notifikasi pengingat jatuh tempo (H-7) dan peringatan denda (sudah lewat tenggat)';

    public function handle(): int
    {
        $this->sendUpcomingDueReminders();
        $this->sendOverdueWarnings();

        return self::SUCCESS;
    }

    /**
     * H-7 sebelum jatuh tempo, kirim 1x pengingat.
     */
    private function sendUpcomingDueReminders(): void
    {
        $targetDate = Carbon::today()->addDays(7);

        $bills = Bill::with('billable.user')
            ->where('status', BillStatus::Unpaid)
            ->whereDate('due_date', $targetDate)
            ->get();

        foreach ($bills as $bill) {
            $user = $bill->billable?->user;
            if (! $user) {
                continue;
            }

            // Cegah kirim dobel kalau command ini kejalan 2x di hari yang sama
            $alreadySent = $user->notifications()
                ->where('type', NotificationType::BillReminder)
                ->whereDate('sent_at', today())
                ->where('body', 'like', "%{$bill->id_bills}%")
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $user->notifications()->create([
                'type' => NotificationType::BillReminder,
                'title' => 'Pengingat Jatuh Tempo',
                'body' => "Tagihan {$this->billLabel($bill)} sebesar Rp "
                    . number_format((float) $bill->total_amount, 0, ',', '.')
                    . " akan jatuh tempo pada {$bill->due_date->translatedFormat('d F Y')}. (ref:bill#{$bill->id_bills})",
                'is_read' => false,
                'sent_at' => now(),
            ]);

            $this->info("Reminder terkirim: user #{$user->id_user}, bill #{$bill->id_bills}");
        }
    }

    /**
     * Sudah lewat tenggat & belum lunas → update status jadi overdue + kirim peringatan.
     * Dikirim 1x per hari selama masih overdue (supaya user terus diingatkan denda menumpuk).
     */
    private function sendOverdueWarnings(): void
    {
        $bills = Bill::with('billable.user')
            ->whereIn('status', [BillStatus::Unpaid, BillStatus::Overdue])
            ->whereDate('due_date', '<', today())
            ->get();

        foreach ($bills as $bill) {
            if ($bill->status !== BillStatus::Overdue) {
                $bill->update(['status' => BillStatus::Overdue]);
            }

            $user = $bill->billable?->user;
            if (! $user) {
                continue;
            }

            $alreadySentToday = $user->notifications()
                ->where('type', NotificationType::PenaltyWarning)
                ->whereDate('sent_at', today())
                ->where('body', 'like', "%bill#{$bill->id_bills}%")
                ->exists();

            if ($alreadySentToday) {
                continue;
            }

            $user->notifications()->create([
                'type' => NotificationType::PenaltyWarning,
                'title' => 'Tunggakan Pajak',
                'body' => "Tagihan {$this->billLabel($bill)} sudah melewati tenggat waktu. "
                    . "Denda saat ini: Rp " . number_format((float) $bill->penalty_amount, 0, ',', '.')
                    . ". Segera lakukan pembayaran untuk menghindari denda bertambah. (ref:bill#{$bill->id_bills})",
                'is_read' => false,
                'sent_at' => now(),
            ]);

            $this->warn("Peringatan denda terkirim: user #{$user->id_user}, bill #{$bill->id_bills}");
        }
    }

    private function billLabel(Bill $bill): string
    {
        return match ($bill->billable_type) {
            'nop' => "PBB-P2 ({$bill->billable->object_name})",
            'npwpd' => "Pajak Usaha ({$bill->billable->business_name})",
            default => "Pajak periode {$bill->tax_period}",
        };
    }
}