<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Question de la FAQ Shopio, modifiable par l'admin ; Shopio IA la lit en direct (outil faq_shopio). */
class FaqEntry extends Model
{
    public const CATEGORIES = [
        'paiement'   => 'Paiement',
        'livraison'  => 'Livraison',
        'retours'    => 'Retours',
        'offres'     => 'Offres et négociation',
        'compte'     => 'Compte',
        'plans'      => 'Plans Pro et Business',
        'parrainage' => 'Parrainage',
        'autre'      => 'Autre',
    ];

    protected $fillable = ['category', 'question', 'answer', 'is_active', 'position'];

    protected $casts = ['is_active' => 'boolean', 'position' => 'integer'];
}
