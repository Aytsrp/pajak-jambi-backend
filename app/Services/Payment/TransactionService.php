<?php

namespace App\Services\Transaction;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\BillStatus;
use App\Enums\TaxType;
use App\Enums\TransactionStatus;
use App\Exceptions\TransactionException;
use App\Models\Bill;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Security\AccountSecurityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TransactionService
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
        private readonly AccountSecurityService $accountSecurity,
    ) {}

    /**
     * Langkah 1: user pilih tagihan + metode bayar → buat transaksi PENDING.
     * Belum ada uang bergerak sama sekali di titik ini.
     *
     * @throws TransactionException
     */
    public function initiate(User $user, int $billId, int $paymentId, string $idempotencyKey): Transaction
    {
        $existing = Transaction::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $existing;
        }

        $bill = Bill::with('billable')->find($billId);
        if (! $bill || $bill->billable->id_user !== $user->id_user) {
            throw TransactionException::billNotFound();
        }

        if ($bill->status === BillStatus::Paid) {
            throw TransactionException::billAlreadyPaid();
        }

        $payment = $user->payments()->find($paymentId);
        if (! $payment) {
            throw TransactionException::paymentMethodNotFound();
        }

        $taxType = match ($bill->billable_type) {
            'nop' => TaxType::Pbb,
            'npwpd' => TaxType::PajakUsaha,
            default => throw TransactionException::billNotFound(),
        };

        return Transaction::create([
            'id_user' => $user->id_user,
            'id_bill' => $bill->id_bills,
            'id_payment' => $payment->id_payment,
            'transaction_ref' => 'TRX-' . strtoupper(Str::random(10)),
            'idempotency_key' => $idempotencyKey,
            'tax_type' => $taxType,
            'reference_type' => $bill->billable_type,
            'reference_id' => $bill->billable_id,
            'amount' => $bill->total_amount,
            'status' => TransactionStatus::Pending,
        ]);
    }

    /**
     * Langkah 2: konfirmasi PIN → eksekusi pembayaran via gateway.
     *
     * @throws TransactionException
     */
    public function confirmPin(User $user, Transaction $transaction, string $pin): Transaction
    {
        if ($transaction->id_user !== $user->id_user) {
            throw TransactionException::billNotFound();
        }

        if ($transaction->status !== TransactionStatus::Pending) {
            throw TransactionException::transactionNotPending();
        }

        if ($user->isPinLocked()) {
            throw TransactionException::pinLocked();
        }

        if (! Hash::check($pin, $user->pin_number)) {
            $this->accountSecurity->registerFailedPin($user);
            throw TransactionException::invalidPin();
        }

        $this->accountSecurity->resetPinAttempts($user);

        return DB::transaction(function () use ($user, $transaction) {
            $payment = $transaction->payment;
            $result = $this->gateway->charge($payment, (float) $transaction->amount);

            if (! $result['success']) {
                $transaction->update(['status' => TransactionStatus::Failed]);
                throw TransactionException::gatewayFailed($result['message']);
            }

            $transaction->update([
                'status' => TransactionStatus::Success,
                'gateway_ref' => $result['gateway_ref'],
                'paid_at' => now(),
                'proof_url' => route('transactions.proof', $transaction->id_transactions),
            ]);

            $transaction->bill->update(['status' => BillStatus::Paid]);

            return $transaction->fresh(['bill', 'payment']);
        });
    }
}