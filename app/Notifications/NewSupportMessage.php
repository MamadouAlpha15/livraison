<?php
namespace App\Notifications;

use App\Models\SupportMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSupportMessage extends Notification
{
    use Queueable; public function __construct(public SupportMessage $message) {}

    public function via($notifiable){ return ['mail']; }

    public function toMail($n)
    {
        $ticket = $this->message->ticket;
        return (new MailMessage)
            ->subject('Nouveau message sur votre ticket #'.$ticket->id)
            ->greeting('Bonjour')
            ->line($this->message->author->name.' a répondu :')
            ->line('"'.$this->message->body.'"')
            ->action('Voir la conversation', route('support.show', $ticket));
    }
}
