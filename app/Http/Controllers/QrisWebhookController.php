<?php

namespace App\Http\Controllers;

use App\Contracts\QrisGatewayInterface;
use App\Enums\BillStatus;
use App\Enums\TransactionStatus;
use App\Events\TransactionCompleted;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QrisWebhookController extends Controller
{
    public function __construct(
        private readonly QrisGatewayInterface $gateway,
    ) {}

    public function handle(Request $request)
    {
        $payload = $request->all();

        if (! $this->gateway->verifyNotification($payload)) {
            Log::warning('QRIS webhook: signature/secret tidak valid', ['payload' => $payload]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $parsed = $this->gateway->parseNotification($payload);

        $transaction = Transaction::where('transaction_ref', $parsed['order_id'])->first();

        if (! $transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        if ($transaction->status !== TransactionStatus::Pending) {
            return response()->json(['message' => 'Already processed']);
        }

        DB::transaction(function () use ($transaction, $parsed) {
            if ($parsed['is_success']) {
                $transaction->update([
                    'status' => TransactionStatus::Success,
                    'gateway_ref' => $parsed['gateway_ref'],
                    'paid_at' => now(),
                    'proof_url' => route('transactions.proof', $transaction->id_transactions),
                ]);
                $transaction->bill->update(['status' => BillStatus::Paid]);
            } elseif ($parsed['is_failed']) {
                $transaction->update(['status' => TransactionStatus::Failed]);
            }
        });

        if (in_array($transaction->fresh()->status, [TransactionStatus::Success, TransactionStatus::Failed])) {
            TransactionCompleted::dispatch($transaction->fresh());
        }

        return response()->json(['message' => 'OK']);
    }
}