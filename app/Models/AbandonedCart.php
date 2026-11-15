<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Panier abandonné : un client a commencé le formulaire de commande (page produit ou panier) sans le valider.
 * Rempli par StorefrontCartController@capture, affiché dans Commandes → Paniers abandonnés.
 */
class AbandonedCart extends Model
{
    public const STATES = [
        'a_rappeler'    => 'À rappeler',
        'injoignable'   => 'Injoignables',
        'pas_interesse' => 'Pas intéressés',
        'recupere'      => 'Récupérés',
    ];

    protected $fillable = ['shop_id', 'visitor', 'name', 'phone', 'commune', 'landmark', 'items', 'total', 'state', 'order_id', 'last_activity_at'];

    protected $casts = ['items' => 'array', 'last_activity_at' => 'datetime'];

    public function shop() { return $this->belongsTo(Shop::class); }
    public function order() { return $this->belongsTo(Order::class); }
}
