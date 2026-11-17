<?php

namespace App\Notifications\Channels;

use App\Services\PushService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/** Canal push des notifications : la notification fournit toPush() → [titre, message, lien], envoyé par PushService. */
class PushChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        [$title, $body, $url] = $notification->toPush($notifiable);
        try {
            app(PushService::class)->sendToUser($notifiable, $title, $body, 1, $url);
        } catch (\Throwable $e) {
            Log::warning('[Push] Notification non envoyée à #' . $notifiable->id . ' : ' . $e->getMessage());
        }
    }
}
