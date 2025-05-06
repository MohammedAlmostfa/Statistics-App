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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ReceiptProduct $receiptProduct)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReceiptProduct $receiptProduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReceiptProduct $receiptProduct)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReceiptProduct $receiptProduct)
    {
        //
    }
}
