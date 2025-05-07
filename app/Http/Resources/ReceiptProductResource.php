<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'product_id' => $this->product_id,
            'product_name' => $this->product->name,
            'unit_price' => $this->selling_price,
            'quantity' => $this->quantity,
            'pay_cont' => optional($this->installment)->pay_cont,
            'installment' => optional($this->installment)->installment,
            'first_pay' => optional($this->installment)->first_pay,
            'installment_type' => optional($this->installment)->installment_type,




        ];
    }
}
