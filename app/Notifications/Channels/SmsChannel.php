<?php

namespace App\Notifications\Channels;

use App\Support\Sms\SmsSender;
use Illuminate\Notifications\Notification;

/** Canal SMS des notifications : la notification fournit toSms() → [téléphone, texte], envoyé par SmsSender. */
class SmsChannel
{
    public function __construct(private SmsSender $sender) {}

    public function send(object $notifiable, Notification $notification): void
    {
        [$phone, $text] = $notification->toSms($notifiable);
        if ($phone && $text) {
            $this->sender->send($phone, $text);
        }
    }
}
