<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentResource extends JsonResource
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
            'notes' => $this->notes,
            'total_debt'=>$this->lastfinancialTransaction->sum_amount??null,
            'last-paid_date'=>$this->lastfinancialTransactionPaid->transaction_date->format('Y-m-d')??null,
            'created_at' => $this->created_at->format('Y-m-d '),

        ];
    }
}
