<?php

namespace App\Channels;

use App\Services\WhatsAppService;
use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    public function __construct(private WhatsAppService $whatsApp) {}

    public function send(mixed $notifiable, Notification $notification): void
    {
        /** @var WhatsAppMessage $message */
        $message = $notification->toWhatsApp($notifiable);

        $phone = $message->phone ?? $notifiable->routeNotificationFor('whatsapp');

        if (! $phone) {
            return;
        }

        if (! $this->whatsApp->send($phone, $message->content)) {
            throw new \RuntimeException("WhatsApp send failed to {$phone}");
        }
    }
}
