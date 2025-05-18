<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DebtResource extends JsonResource
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
            'customer_id' => $this->customer_id,
            'total_debt' => $this->total_debt,
            'customer_name' =>  $this->customer->name,
            'debt_date' => $this->debt_date->format('Y-m-d'),
            'remaining_debt' => $this->remaining_debt,
            'user_id' => $this->user_id,
            'name' => $this->user->name,
        ];
    }
}
