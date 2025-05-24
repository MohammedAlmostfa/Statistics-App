<?php

namespace App\Http\Controllers;

use App\Models\DebtPayment;

use App\Services\DebtPaymentService;
use App\Http\Requests\DebtPaymentRequest\StoreDebtPaymentData;
use Illuminate\Http\JsonResponse;

class DebtPaymentController extends Controller
{ /**
    * Handles debt-related business logic.
    *
    * @var DebtService
    */
    protected DebtPaymentService $DebtPaymentService;

    /**
     * DebtController Constructor
     *
     * @param DebtService $DebtService
     */
    public function __construct(DebtPaymentService $DebtPaymentService)
    {
        $this->DebtPaymentService = $DebtPaymentService;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDebtPaymentData $request): JsonResponse
    {
        $validatedData = $request->validated();

        // Retrieve the report from the service
        $result = $this->DebtPaymentService->createDebtPayment($validatedData);

        // Return appropriate API response based on the result status
        return $result['status'] === 200
            ? $this->success($result['data'], $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DebtPayment $debtPayment): JsonResponse
    { // Retrieve the report from the service
        $result = $this->DebtPaymentService->deleteDebtPayment($debtPayment);

        // Return appropriate API response based on the result status
        return $result['status'] === 200
            ? $this->success($result['data'], $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }
}
