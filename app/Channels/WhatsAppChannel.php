<?php
namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class WhatsAppChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toWhatsApp($notifiable);

        $instanceId = config("services.ultramsg.instance_id");
        $token =config("services.ultramsg.api_token");
        $url = "https://api.ultramsg.com/{$instanceId}/messages/chat";

        $response = Http::post($url, [
            'token' => $token,
            'to' => $message['phone'],
            'body' => $message['body'],
        ]);

        return $response->json();
    }
}
