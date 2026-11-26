<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Témoignage d'un vendeur sur Shopio. Écrit par le vendeur (Tableau de bord → Ton avis sur Shopio),
 * validé par l'équipe Shopio (Admin → Avis → Témoignages Shopio), affiché sur la page d'accueil.
 * Toute modification par le vendeur le remet « en attente ».
 */
class ShopTestimonial extends Model
{
    public const STATUSES = [
        'pending'  => 'En attente',
        'approved' => 'Publié',
        'rejected' => 'Refusé',
    ];

    protected $fillable = ['shop_id', 'user_id', 'rating', 'text', 'author', 'author_role', 'video', 'status', 'approved_at'];

    protected $casts = [
        'rating'      => 'integer',
        'approved_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
