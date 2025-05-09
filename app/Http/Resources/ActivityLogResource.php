<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'name'        => optional($this->user)->name,
            'description' => $this->description,
            'created_at'  => $this->created_at?->format('Y-m-d h:i'),
         'type_type'   => class_basename($this->type_type),
            'type_id'     => $this->type_id,
        ];
    }
}
