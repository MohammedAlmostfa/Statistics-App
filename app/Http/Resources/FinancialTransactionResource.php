<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialTransactionResource extends JsonResource
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
            'agent_id' => $this->agent_id,
            'transaction_date' => $this->transaction_date,
            'type' => $this->type,
            'total_amount' => $this->total_amount,
            'discount_amount' => $this->discount_amount,
            'paid_amount' => $this->paid_amount,
            'description' => $this->description,
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
