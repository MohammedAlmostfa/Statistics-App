<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'selling_price' => $this->selling_price,
            'name' => $this->name,
            'Dollar_exchange' => $this->Dollar_exchange,
            'selling_price' => $this->selling_price,
            'quantity' => $this->quantity,
            'installment_price' => $this->installment_price,
            'dolar_buying_price' => $this->dolar_buying_price,
            'origin' => $this->origin->name ?? null,
            'origin_id' => $this->origin->id ?? null,
            'category' => $this->category->name ?? null,
            'category_id' => $this->category->id ?? null,
            'user_name' => $this->user->name ?? null,
            'user_id' => $this->user->id ?? null,
            'created_at' => $this->created_at->format('Y-m-d '),

        ];
    }
}
