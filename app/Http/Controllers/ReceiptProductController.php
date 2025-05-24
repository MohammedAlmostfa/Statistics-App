<?php

namespace App\Http\Controllers;

use App\Services\ReceiptProductService;
use App\Http\Resources\ReceiptProductResource;
use Illuminate\Http\JsonResponse;

class ReceiptProductController extends Controller
{
    protected ReceiptProductService $ReceiptProductService;

    public function __construct(ReceiptProductService $ReceiptProductService)
    {
        $this->ReceiptProductService = $ReceiptProductService;
    }


    /**
     * Show the form for creating a new resource.
     */
    public function getreciptProduct($id): JsonResponse
    {
        $result = $this->ReceiptProductService->getreciptProduct($id);

        return $result['status'] === 200
              ? $this->success(ReceiptProductResource::collection($result['data']), $result['message'], $result['status'])
              : $this->error(null, $result['message'], $result['status']);

    }

}
