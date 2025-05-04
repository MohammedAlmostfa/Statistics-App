<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\InstallmentPayment;
use App\Services\InstallmentPaymentService;
use App\Http\Requests\StoreInstallmentPaymentRequest;
use App\Http\Requests\InstallmentPaymentRequest\StoreInstallmentPaymentData;

class InstallmentPaymentController extends Controller
{
    protected InstallmentPaymentService $installmentPaymentService;

    public function __construct(InstallmentPaymentService $installmentPaymentService)
    {
        $this->installmentPaymentService = $installmentPaymentService;
    }

    /**
     * Store a new installment payment
     *
     * @param StoreInstallmentPaymentRequest $request
     * @param Installment $installment
     * @return JsonResponse
     */
    public function store(StoreInstallmentPaymentData $request, $id): JsonResponse
    {
        $validatedData = $request->validated();

        $result = $this->installmentPaymentService->createInstallmentPayment(
            $validatedData,
            $id
        );

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }



    /**
     * Display the specified resource.
     */
    public function show(InstallmentPayment $installmentPayment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InstallmentPayment $installmentPayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InstallmentPayment $installmentPayment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InstallmentPayment $installmentPayment)
    {
        //
    }
}
