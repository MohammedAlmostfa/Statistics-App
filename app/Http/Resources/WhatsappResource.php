<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WhatsappResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {

        return [
            'messages' => collect($this->resource['messages'])->map(function ($message) {
                return [
                    'body'        => $message['body'],
                    'status'      => $message['status'],
                    'created_at'  => date('Y-m-d H:i:s', $message['created_at']),

                ];
            }),
        ];
    }
}
