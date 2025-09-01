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
            'type_type'   =>$this->type_type,
            'type_id'     => $this->type_id,
          'created_at' => $this->created_at
    ? $this->created_at->timezone('Asia/Baghdad')->format('Y-m-d H:i')
    : null,

        ];
    }
}
