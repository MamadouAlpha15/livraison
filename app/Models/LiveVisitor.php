<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Visiteur présent sur la vitrine (vue en direct). Identifiant aléatoire du navigateur, pays à 2 lettres,
 * étape (browse, product, cart, form), produit regardé. Jamais d'adresse IP. Voir App\Support\LiveStats.
 */
class LiveVisitor extends Model
{
    public $timestamps = false;

    protected $fillable = ['shop_id', 'visitor', 'stage', 'product_id', 'cart_items', 'country', 'first_seen_at', 'last_seen_at'];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at'  => 'datetime',
        'cart_items'    => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
