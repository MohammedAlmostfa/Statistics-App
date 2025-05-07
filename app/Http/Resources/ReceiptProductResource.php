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
            'description'=>$this->description,
            'product_id' => $this->product_id,
            'product_name' => $this->product->name,
            'unit_price' => $this->product->selling_price,
            'installment_price' => $this->product->installment_price,
            'reseipt_product_quantity' => $this->quantity,
            'pay_cont' => $this->installment->pay_cont,
            'installment' => $this->installment->installment,
            'first_pay' => $this->installment->first_pay,
            'quantity' => $this->quantity,
            'installment_type' => $this->installment->installment_type



        ];
    }
}
