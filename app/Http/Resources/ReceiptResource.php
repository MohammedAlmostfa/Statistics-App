<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ReceiptProductResource;

class ReceiptResource extends JsonResource
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
            'phone' => $this->phone,
            'sponsor_name' => $this->sponsor_name,
            'sponsor_phone' => $this->sponsor_phone,
            'Record_id' => $this->Record_id,
            'Page_id' => $this->Page_id,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'receipts' => ReceiptProductResource::collection($this->receipts->flatMap(function ($receipt) {
                return $receipt->receipt_products;
            })),
        ];
    }
}
