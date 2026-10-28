<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Une relance (prévue, envoyée ou arrêtée) d'une chaîne : même situation, même objet, même destinataire. */
class FollowUp extends Model
{
    use HasFactory;

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_SENT      = 'sent';
    public const STATUS_STOPPED   = 'stopped';
    public const STATUS_FAILED    = 'failed';

    public const STOP_REASONS = [
        'repondu'        => 'Le client a répondu',
        'accepte'        => 'Offre acceptée ou refusée',
        'commande'       => 'Le client a commandé',
        'annule'         => 'Commande annulée ou traitée',
        'stop'           => 'Le client a demandé STOP',
        'desactive'      => 'Relance désactivée',
        'plus_d_actualite' => 'Plus d\'actualité',
        'aucun_canal'    => 'Aucun canal disponible',
    ];

    protected $fillable = [
        'follow_up_rule_id', 'shop_id', 'user_id', 'type', 'subject_type', 'subject_id', 'product_id', 'attempt', 'status',
        'scheduled_at', 'sent_at', 'channels', 'message', 'shop_message_id', 'notification_id', 'responded_at',
        'stop_reason', 'recovered_order_id', 'recovered_amount',
    ];

    protected $casts = [
        'attempt'          => 'integer',
        'scheduled_at'     => 'datetime',
        'sent_at'          => 'datetime',
        'responded_at'     => 'datetime',
        'channels'         => 'array',
        'recovered_amount' => 'integer',
    ];

    public function rule()
    {
        return $this->belongsTo(FollowUpRule::class, 'follow_up_rule_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function shopMessage()
    {
        return $this->belongsTo(ShopMessage::class);
    }

    public function recoveredOrder()
    {
        return $this->belongsTo(Order::class, 'recovered_order_id');
    }

    /** Lue ? D'après le message de la messagerie ou la notification dans l'appli */
    public function readAt(): ?\Illuminate\Support\Carbon
    {
        if ($this->shopMessage?->read_at) {
            return $this->shopMessage->read_at;
        }
        if ($this->notification_id) {
            $read = \Illuminate\Support\Facades\DB::table('notifications')->where('id', $this->notification_id)->value('read_at');
            return $read ? \Illuminate\Support\Carbon::parse($read) : null;
        }
        return null;
    }
}
