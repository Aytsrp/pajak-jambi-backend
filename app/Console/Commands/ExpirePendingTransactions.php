<?php

namespace App\Console\Commands;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Console\Command;

class ExpirePendingTransactions extends Command
{
    protected $signature = 'tax:expire-transactions';

    public function handle(): int
    {
        $count = Transaction::where('status', TransactionStatus::Pending)
            ->where(function ($q) {
                $q->where('va_expired_at', '<', now())
                    ->orWhere('qr_expired_at', '<', now());
            })
            ->update(['status' => TransactionStatus::Expired]);

        $this->info("{$count} transaksi ditandai expired.");

        return self::SUCCESS;
    }

}