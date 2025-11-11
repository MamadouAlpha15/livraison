<?php
namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSupportTicket extends Notification
{
    use Queueable; public function __construct(public SupportTicket $ticket) {}

    public function via($notifiable){ return ['mail']; }

    public function toMail($n)
    {
        return (new MailMessage)
            ->subject('Nouveau ticket de support : '.$this->ticket->subject)
            ->greeting('Bonjour')
            ->line('Un nouveau ticket a été créé.')
            ->line('Boutique : '.($this->ticket->shop?->name ?? '—'))
            ->action('Ouvrir le ticket', route('support.show', $this->ticket));
    }
}
