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
            'name' => $this->name,
            'buying_price' => $this->buying_price,
            'quantity' => $this->quantity,
            'installment_price' => $this->installment_price,
            'created_at' => $this->created_at,
            'origin' => $this->origin->name ?? null,
            'category' => $this->category->name ?? null,

        ];
    }
}
