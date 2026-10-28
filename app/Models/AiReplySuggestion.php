<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Réponse proposée par Shopio IA au vendeur (mode « suggestion »), qu'il envoie en un clic. */
class AiReplySuggestion extends Model
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_SENT      = 'sent';
    public const STATUS_DISMISSED = 'dismissed';

    protected $fillable = ['shop_id', 'client_id', 'product_id', 'reply_to_message_id', 'body', 'status', 'shop_message_id'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function replyTo()
    {
        return $this->belongsTo(ShopMessage::class, 'reply_to_message_id');
    }
}
