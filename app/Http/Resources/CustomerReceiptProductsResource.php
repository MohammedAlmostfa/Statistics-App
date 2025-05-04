<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerReceiptProductResource extends JsonResource // Renamed to singular
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'receipt_number' => $this->receipt_number,
            'receipt_date' => $this->receipt_date,
            'quantity' => $this->quantity,
            'product_name' => $this->product_name,
            'product_price' => $this->product_price,
            'pay_cont' => $this->pay_cont,
            'installment_type' => $this->installment_type,
            'installment' => $this->installment,
            'first_pay' => $this->first_pay,
        ];
    }
}
