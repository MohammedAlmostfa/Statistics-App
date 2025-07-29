<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WhatsappResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer?->name,
            'message' => $this->message,

            'status' => $this->status_text,
            'response' => $this->response,
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
