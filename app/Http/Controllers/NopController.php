<?php

namespace App\Http\Controllers;

use App\Exceptions\PemdaVerificationException;
use App\Http\Requests\RegisterNopRequest;
use App\Http\Resources\NopResource;
use App\Services\Pemda\BillSyncService;
use App\Services\Pemda\NopRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NopController extends Controller
{
    public function __construct(
        private readonly NopRegistrationService $registrationService,
        private readonly BillSyncService $billSync,
    ) {}

    public function index(Request $request)
    {
        $nops = $request->user()->nops()->with('bills')->get();

        return NopResource::collection($nops);
    }
    public function store(RegisterNopRequest $request)
    {
        try {
            $nop = $this->registrationService->register(
                user: $request->user(),
                nopNumber: $request->nop_number,
                ipAddress: $request->ip(),
            );
        } catch (PemdaVerificationException $e) {
            throw ValidationException::withMessages([
                'nop_number' => $e->getMessage(),
            ]);
        }

        return NopResource::make($nop->load('bills'))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $id)
    {
        $nop = $request->user()->nops()->with('bills')->findOrFail($id);

        return NopResource::make($nop);
    }

    public function refresh(Request $request, int $id)
    {
        $nop = $request->user()->nops()->findOrFail($id);

        $this->billSync->syncForNop($nop);

        return NopResource::make($nop->fresh('bills'));
    }
}