<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialTransactionsProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'financial_id'       => $this->financial_id,
            'product_id'         => $this->product_id,
            'product_name'       => $this->product->name,
            'selling_price'      => $this->selling_price,
            'dollar_buying_price'=> $this->dollar_buying_price,
            'dollar_exchange'    => $this->dollar_exchange,
            'installment_price'  => $this->installment_price,
            'quantity'           => $this->quantity,
            'created_at'         => $this->created_at->format('Y-m-d '),
        ];

    }
}
