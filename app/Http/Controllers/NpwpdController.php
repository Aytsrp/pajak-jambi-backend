<?php

namespace App\Http\Controllers;

use App\Exceptions\PemdaVerificationException;
use App\Http\Requests\RegisterNpwpdRequest;
use App\Http\Resources\NpwpdResource;
use App\Services\Pemda\BillSyncService;
use App\Services\Pemda\NpwpdRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NpwpdController extends Controller
{
    public function __construct(
        private readonly NpwpdRegistrationService $registrationService,
        private readonly BillSyncService $billSync,
    ) {}

    public function show(Request $request)
    {
        $npwpd = $request->user()->npwpd()->with('bills')->first();

        if (! $npwpd) {
            return response()->json([
                'message' => 'Belum ada NPWPD terdaftar.',
                'data' => null,
            ], 200);
        }

        return NpwpdResource::make($npwpd);
    }

    public function store(RegisterNpwpdRequest $request)
    {
        try {
            $npwpd = $this->registrationService->register(
                user: $request->user(),
                npwpdNumber: $request->npwpd_number,
                ipAddress: $request->ip(),
            );
        } catch (PemdaVerificationException $e) {
            throw ValidationException::withMessages([
                'npwpd_number' => $e->getMessage(),
            ]);
        }

        return NpwpdResource::make($npwpd->load('bills'))
            ->response()
            ->setStatusCode(201);
    }

    public function refresh(Request $request)
    {
        $npwpd = $request->user()->npwpd()->firstOrFail();

        $this->billSync->syncForNpwpd($npwpd);

        $npwpd = $npwpd->fresh('bills');

        return NpwpdResource::make($npwpd)->additional([
            'meta' => [
                'has_new_bill' => $npwpd->bills->isNotEmpty(),
                'note' => $npwpd->bills->isEmpty()
                    ? 'Belum ada tagihan bulan ini. Pastikan sudah lapor di Lapor Pajak Bapenda Kota Jambi.'
                    : null,
            ],
        ]);
    }
}