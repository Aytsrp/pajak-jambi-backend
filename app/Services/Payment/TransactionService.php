<?php

namespace App\Services\Transaction;

use App\Contracts\QrisGatewayInterface;
use App\Enums\BankCode;
use App\Enums\BillStatus;
use App\Enums\PaymentChannel;
use App\Enums\TaxType;
use App\Enums\TransactionStatus;
use App\Exceptions\TransactionException;
use App\Models\Bill;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BankGatewayManager;
use Illuminate\Support\Str;

class TransactionService
{
    public function __construct(
        private readonly BankGatewayManager $bankManager,
        private readonly QrisGatewayInterface $qrisGateway,
    ) {}

    /**
     * @throws TransactionException
     */
    public function initiate(
        User $user,
        int $billId,
        PaymentChannel $channel,
        ?BankCode $bankCode,
        string $idempotencyKey,
    ): Transaction {
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

        if ($channel === PaymentChannel::BankTransfer && ! $bankCode) {
            throw TransactionException::bankCodeRequired();
        }

        $taxType = match ($bill->billable_type) {
            'nop' => TaxType::Pbb,
            'npwpd' => TaxType::PajakUsaha,
            default => throw TransactionException::billNotFound(),
        };

        $transaction = Transaction::create([
            'id_user' => $user->id_user,
            'id_bill' => $bill->id_bills,
            'transaction_ref' => 'TRX-' . strtoupper(Str::random(10)),
            'idempotency_key' => $idempotencyKey,
            'tax_type' => $taxType,
            'reference_type' => $bill->billable_type,
            'reference_id' => $bill->billable_id,
            'amount' => $bill->total_amount,
            'payment_channel' => $channel,
            'bank_code' => $channel === PaymentChannel::BankTransfer ? $bankCode : null,
            'status' => TransactionStatus::Pending,
        ]);

        match ($channel) {
            PaymentChannel::BankTransfer => $this->attachVirtualAccount($transaction, $bankCode),
            PaymentChannel::Qris => $this->attachQris($transaction),
        };

        return $transaction->fresh();
    }

    private function attachVirtualAccount(Transaction $transaction, BankCode $bankCode): void
    {
        $vaData = $this->bankManager->driver($bankCode)->createVirtualAccount($transaction);

        $transaction->update([
            'va_number' => $vaData['va_number'],
            'va_expired_at' => $vaData['expired_at'],
        ]);
    }

    private function attachQris(Transaction $transaction): void
    {
        $qrData = $this->qrisGateway->generate($transaction);

        $transaction->update([
            'qr_string' => $qrData['qr_string'],
            'qr_image_url' => $qrData['qr_image_url'],
            'qr_expired_at' => $qrData['expired_at'],
        ]);
    }
}