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
     'messages' => collect($this->resource['messages'])->lazy()->map(function ($message) {

         $translatedStatus = match ($message['status']) {
             'sent'    => 'تم الإرسال',
             'invalid' => 'غير صالح',
             'unsent'  => 'لم يتم الإرسال',
             default   => 'حالة غير معروفة',
         };
         return [
             'body'        => $message['body'],
             'status'      => $translatedStatus,
             'created_at'  => date('Y-m-d H:i:s', $message['created_at']),
         ];
     }),
        ];
    }
}
