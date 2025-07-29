<?php
namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SendWhatsAppNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp($notifiable)
    {
        return [
            'phone' => $notifiable->phone,  // رقم الهاتف بصيغة رقم فقط (مثلاً: 961XXXXXXXX)
            'body' => $this->message,
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
        ];
    }
}
