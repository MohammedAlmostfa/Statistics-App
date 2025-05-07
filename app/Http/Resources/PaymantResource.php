<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymantResource extends JsonResource
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
            'amount' => $this->amount,
            'details' => $this->details,
            'payment_date' => $this->payment_date->format('Y-m-d '),
            'user_id' => $this->user_id,
            'name' => $this->user->name,

        ];
    }
}
