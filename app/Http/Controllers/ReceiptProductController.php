<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\ReceiptProduct;
use App\Services\ReceiptProductService;
use App\Http\Resources\CustomerReceiptProductResource;
use App\Http\Resources\ReceiptProductResource;

class ReceiptProductController extends Controller
{
    protected ReceiptProductService $ReceiptProductService;

    public function __construct(ReceiptProductService $ReceiptProductService)
    {
        $this->ReceiptProductService = $ReceiptProductService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $result = $this->ReceiptProductService->getCustomerReceiptProducts($id);

        return $result['status'] === 200
            ? $this->success($result['data'], $result['message'], $result['status'])
            : $this->error(null, $result['message'], $result['status']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getreciptProduct($id)
    {
        $result = $this->ReceiptProductService->getreciptProduct($id);

        return $result['status'] === 200
              ? $this->success(ReceiptProductResource::collection($result['data']), $result['message'], $result['status'])
              : $this->error(null, $result['message'], $result['status']);

    }

}
