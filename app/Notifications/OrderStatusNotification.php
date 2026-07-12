<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $message;

    public function __construct(Order $order, string $message)
    {
        $this->order = $order;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        // Notification par base de données + email
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Mise à jour de votre commande')
            ->line($this->message)
            ->action('Voir ma commande', url('/client/orders'))
            ->line('Merci d’avoir utilisé '.config('app.name').' 🚀'); 
    }

    public function toDatabase($notifiable) // Notification stockée en base de données
    {
        return [
            'order_id' => $this->order->id,
            'message' => $this->message,
            'status' => $this->order->status,
            // 🔹 Lien dynamique selon le rôle
        'url' => match($notifiable->role) {
            'client' => url('/client/orders'), // Tous les clients voient leurs commandes
            'livreur' => url('/livreur/orders'),
            'vendeur' => url('/employe/orders'),
            default => url('/')
        }
        ];
    }
}
