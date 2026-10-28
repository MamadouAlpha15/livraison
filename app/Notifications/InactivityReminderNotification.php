<?php

namespace App\Notifications;

use App\Notifications\Channels\PushChannel;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Rappel « Tu nous manques » d'un client ou d'une entreprise de livraison inactif
 * (App\Services\InactivityReminderService). Envoyé en file d'attente sur les canaux choisis par le service.
 */
class InactivityReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param string[] $channels push | mail | sms */
    public function __construct(
        public string $audience,
        public int $step,
        public string $title,
        public string $body,
        public string $url,
        public array $channels,
        public string $sms,
    ) {}

    public function via(object $notifiable): array
    {
        $map = ['push' => PushChannel::class, 'mail' => 'mail', 'sms' => SmsChannel::class];
        return array_merge(['database'], array_values(array_intersect_key($map, array_flip($this->channels))));
    }

    public function toMail(object $notifiable): MailMessage
    {
        $unsubscribe = URL::signedRoute('reminders.unsubscribe', ['user' => $notifiable->id]);

        return (new MailMessage)
            ->subject($this->title)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line($this->body)
            ->action($this->audience === 'company' ? 'Ouvrir mon espace livraison' : 'Revenir sur Shopio', $this->url)
            ->line('Tu ne veux plus recevoir ces rappels ? [Me désinscrire](' . $unsubscribe . ')');
    }

    public function toPush(object $notifiable): array
    {
        return [$this->title, $this->body, $this->url];
    }

    public function toSms(object $notifiable): array
    {
        return [$notifiable->phone, $this->sms];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'rappel_inactivite',
            'audience' => $this->audience,
            'step'     => $this->step,
            'title'    => $this->title,
            'body'     => $this->body,
            'url'      => $this->url,
        ];
    }
}
