<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Foundation\Events\Dispatchable;

class TransactionCompleted
{
    use Dispatchable;

    public function __construct(
        public readonly Transaction $transaction,
    ) {}
}