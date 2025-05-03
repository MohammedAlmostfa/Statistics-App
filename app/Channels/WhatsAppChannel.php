<?php
namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class WhatsAppChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toWhatsApp($notifiable);

        $instanceId = env('INSTANCE_ID');
        $token = env('API_TOKEN');
        $url = "https://api.ultramsg.com/{$instanceId}/messages/chat";

        $response = Http::post($url, [
            'token' => $token,
            'to' => $message['phone'],
            'body' => $message['body'],
        ]);

        return $response->json();
    }
}
