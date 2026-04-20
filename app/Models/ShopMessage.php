<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopMessage extends Model
{
    // Types de messages
    const TYPE_TEXT           = 'text';
    const TYPE_IMAGES         = 'images';          // vendeur envoie des photos
    const TYPE_PRICE_PROPOSAL = 'price_proposal'; // client propose un prix
    const TYPE_PRICE_OFFER    = 'price_offer';    // vendeur envoie une offre formelle
    const TYPE_ORDER_CREATED  = 'order_created';  // confirmation commande négociée

    // Statuts de proposition/offre
    const STATUS_PENDING  = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REFUSED  = 'refused';

    protected $fillable = [
        'shop_id', 'product_id',
        'sender_id', 'receiver_id',
        'body', 'images', 'image_status', 'read_at',
        'type', 'proposed_price', 'proposal_status', 'negotiated_order_id',
    ];

    protected $casts = [
        'read_at'        => 'datetime',
        'proposed_price' => 'decimal:2',
        'images'         => 'array',
    ];

    /* ── Relations ── */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function negotiatedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'negotiated_order_id');
    }

    /* ── Helpers ── */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function isPriceProposal(): bool
    {
        return $this->type === self::TYPE_PRICE_PROPOSAL;
    }

    public function isPriceOffer(): bool
    {
        return $this->type === self::TYPE_PRICE_OFFER;
    }

    public function isPending(): bool
    {
        return $this->proposal_status === self::STATUS_PENDING;
    }
}
