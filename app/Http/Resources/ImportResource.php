<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'amount' => $this['amount'] ?? 0,
            'date'   => $this['date'] ?? null,
            'user'   => $this['user'] ?? 'غير معروف',
            'type'   => $this['type'] ?? 'غير محدد',
        ];
    }
}
