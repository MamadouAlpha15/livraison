<?php

namespace App\Notifications;

use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Programme d'affiliation : nouveau filleul, commission, retrait… Envoyée en file d'attente,
 * dans le compte Shopio et par e-mail, et par SMS quand un texte court est fourni.
 * Un affilié externe sans compte la reçoit seulement par e-mail.
 */
class AffiliateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $event,
        public string $title,
        public string $body,
        public string $url,
        public ?string $sms = null,
        public ?string $smsPhone = null,
    ) {}

    public function via(object $notifiable): array
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return ['mail'];
        }
        $channels = ['database'];
        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }
        if ($this->sms && $this->smsPhone) {
            $channels[] = SmsChannel::class;
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Shopio · ' . $this->title)
            ->greeting('Bonjour,')
            ->line($this->body)
            ->action('Voir mon parrainage', $this->url);
    }

    public function toSms(object $notifiable): array
    {
        return [$this->smsPhone, $this->sms];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'affiliation',
            'event' => $this->event,
            'title' => $this->title,
            'body'  => $this->body,
            'url'   => $this->url,
        ];
    }
}
