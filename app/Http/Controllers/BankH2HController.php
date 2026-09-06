<?php

namespace App\Http\Controllers;

use App\Enums\BankCode;
use App\Enums\BillStatus;
use App\Enums\TransactionStatus;
use App\Events\TransactionCompleted;
use App\Models\Transaction;
use App\Services\BankGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankH2HController extends Controller
{
    public function __construct(
        private readonly BankGatewayManager $bankManager,
    ) {}

    public function inquiry(Request $request, string $bankCode)
    {
        $bank = BankCode::from($bankCode);
        $driver = $this->bankManager->driver($bank);

        if (! $driver->verifyIncomingRequest($request->headers->all(), $request->all())) {
            return response()->json(['response_code' => '99', 'response_message' => 'Unauthorized'], 401);
        }

        $vaNumber = $request->input('va_number');
        $transaction = Transaction::with(['bill', 'user'])
            ->where('va_number', $vaNumber)
            ->where('bank_code', $bank)
            ->first();

        if (! $transaction) {
            return response()->json(['response_code' => '01', 'response_message' => 'VA tidak ditemukan'], 404);
        }

        if ($transaction->status !== TransactionStatus::Pending) {
            return response()->json(['response_code' => '02', 'response_message' => 'VA sudah tidak aktif'], 200);
        }

        if ($transaction->va_expired_at->isPast()) {
            return response()->json(['response_code' => '03', 'response_message' => 'VA sudah kedaluwarsa'], 200);
        }

        return response()->json($driver->formatInquiryResponse($transaction));
    }

    public function payment(Request $request, string $bankCode)
    {
        $bank = BankCode::from($bankCode);
        $driver = $this->bankManager->driver($bank);

        if (! $driver->verifyIncomingRequest($request->headers->all(), $request->all())) {
            return response()->json(['response_code' => '99', 'response_message' => 'Unauthorized'], 401);
        }

        $vaNumber = $request->input('va_number');
        $transaction = Transaction::with('bill')
            ->where('va_number', $vaNumber)
            ->where('bank_code', $bank)
            ->first();

        if (! $transaction) {
            return response()->json(['response_code' => '01', 'response_message' => 'VA tidak ditemukan'], 404);
        }

        if ($transaction->status !== TransactionStatus::Pending) {
            return response()->json($driver->formatPaymentResponse($transaction, true));
        }

        DB::transaction(function () use ($transaction, $request) {
            $transaction->update([
                'status' => TransactionStatus::Success,
                'gateway_ref' => $request->input('reference_number', 'H2H-' . now()->timestamp),
                'paid_at' => now(),
                'proof_url' => route('transactions.proof', $transaction->id_transactions),
            ]);

            $transaction->bill->update(['status' => BillStatus::Paid]);
        });

        TransactionCompleted::dispatch($transaction->fresh());

        return response()->json($driver->formatPaymentResponse($transaction->fresh(), true));
    }
}