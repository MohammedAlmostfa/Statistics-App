<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Customer;
use App\Services\ReceiptService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ReceiptResource;
use App\Http\Resources\CustomerReceiptResource;
use App\Http\Requests\ReceiptRequest\StoreReceiptData;
use App\Http\Requests\ReceiptRequest\UpdateReceiptData;

class ReceiptController extends Controller
{
    protected ReceiptService $receiptService;

    public function __construct(ReceiptService $receiptService)
    {
        $this->receiptService = $receiptService;
    }
    public function index(): JsonResponse
    {
        $result = $this->receiptService->getAllReceipt();

        return $result['status'] === 200
            ? $this->paginated($result["data"], ReceiptResource::class, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    public function getCustomerReceipt($id)
    {
        $result = $this->receiptService->getCustomerReceipt($id);

        return $result['status'] === 200
                 ? $this->paginated($result["data"], CustomerReceiptResource::class, $result['message'], $result['status'])
                 : $this->error(null, $result['message'], $result['status']);

    }
    /**
     * Store a new receipt in the database.
     *
     * @param StoreReceiptData $request
     * @return JsonResponse
     */
    public function store(StoreReceiptData $request): JsonResponse
    {
        $result = $this->receiptService->createReceipt($request->validated());

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Update an existing receipt.
     *
     * @param UpdateReceiptData $request
     * @param Receipt $receipt
     * @return JsonResponse
     */
    // public function update(UpdateReceiptData $request, Receipt $receipt): JsonResponse
    // {
    //     $result = $this->receiptService->updateReceiptWithProducts($receipt, $request->validated());

    //     return $result['status'] === 200
    //         ? $this->success(null, $result['message'], $result['status'])
    //         : $this->error(null, $result['message'], $result['status']);
    // }



    /**
     * Delete a receipt from the database.
     *
     * @param Receipt $receipt
     * @return JsonResponse
     */
    public function destroy(Receipt $receipt): JsonResponse
    {
        $result = $this->receiptService->deleteReceipt($receipt);

        return $result['status'] === 200
            ? $this->success(null, $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }
}
