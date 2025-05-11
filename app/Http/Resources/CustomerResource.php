<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'Record_id' => $this->Record_id,
            'Page_id' => $this->Page_id,
            'notes' => $this->notes,
            'status' => $this->status,
            'created_at' => $this->created_at,

        ];
    }
}
