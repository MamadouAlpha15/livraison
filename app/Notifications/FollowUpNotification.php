<?php

namespace App\Notifications;

use App\Notifications\Channels\PushChannel;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/** Relance automatique (App\Services\FollowUpService) : notification dans l'appli + push, et/ou SMS. */
class FollowUpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param string[] $channels in_app | sms */
    public function __construct(
        public string $followUpType,
        public string $title,
        public string $body,
        public string $url,
        public array $channels,
        public ?string $stopUrl = null,
    ) {}

    public function via(object $notifiable): array
    {
        $via = [];
        if (in_array('in_app', $this->channels, true)) {
            $via[] = 'database';
            $via[] = PushChannel::class;
        }
        if (in_array('sms', $this->channels, true)) {
            $via[] = SmsChannel::class;
        }
        return $via;
    }

    public function toPush(object $notifiable): array
    {
        return [$this->title, $this->body, $this->url];
    }

    public function toSms(object $notifiable): array
    {
        return [$notifiable->phone, $this->title . ' : ' . $this->body . ($this->stopUrl ? ' STOP : ' . $this->stopUrl : '')];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'relance',
            'relance'  => $this->followUpType,
            'title'    => $this->title,
            'body'     => $this->body,
            'url'      => $this->url,
            'stop_url' => $this->stopUrl,
        ];
    }
}
