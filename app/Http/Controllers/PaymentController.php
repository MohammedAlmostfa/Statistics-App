<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest\StorePaymentData;
use App\Http\Requests\PaymentRequest\UpdatePaymentData;
use App\Http\Resources\PaymantResource;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    /**
     * Handles payment-related business logic.
     *
     * @var PaymentService
     */
    protected PaymentService $paymentService;

    /**
     * PaymentController Constructor
     *
     * @param PaymentService $paymentService
     */
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of the payments.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $result = $this->paymentService->getAllPayments();

        return $result['status'] === 200
            ? $this->paginated($result['data'], PaymantResource::class, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Store a newly created payment in storage.
     *
     * @param StorePaymentData $request
     * @return JsonResponse
     */
    public function store(StorePaymentData $request): JsonResponse
    {
        $result = $this->paymentService->createPaymant($request->validated());

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Update the specified payment in storage.
     *
     * @param UpdatePaymentData $request
     * @param Payment $payment
     * @return JsonResponse
     */
    public function update(UpdatePaymentData $request, Payment $payment): JsonResponse
    {
        $result = $this->paymentService->updatePayment($request->validated(), $payment);

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Remove the specified payment from storage.
     *
     * @param Payment $payment
     * @return JsonResponse
     */
    public function destroy(Payment $payment): JsonResponse
    {
        $result = $this->paymentService->deletePayment($payment);

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }
}
