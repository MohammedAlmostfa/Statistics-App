<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerReceiptProduct extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $installment = $this->installment;
        $payments = $installment->installmentPayments->toArray();



        return [
            'receipt_id' => $this->receipt_id,
            'receipt_number' => $this->receipt->receipt_number,
            'receipt_date' => $this->receipt->receipt_date->format('Y-m-d'),
            'product_id' => $this->product_id,
            'product_name' => $this->product->name,
            'quantity' => $this->quantity,
            'description'     => $this->description,
            'product_price' => $this->selling_price,
            'pay_cont' => $installment ? $installment->pay_cont : null,
            'installment_id' => $installment ? $installment->id : null,
            'first_pay' => $installment ? $installment->first_pay: null,
            'status' => $installment ? $installment->status: null,
            'installment_type' => $installment ? $installment->installment_type : null,
            'installment_amount' => $installment ? $installment->installment : null,
            'payments' => $payments,
        ];
    }
}
