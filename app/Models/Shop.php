<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'type', 'description', 'address', 'phone', 'image', 'is_approved', 'email', 'status', 'commission_rate', 'currency', 'country',
        'plan', 'plan_expires_at',   // Plan d'abonnement (free/pro) + date d'expiration
        'payout_wallet_type', 'payout_wallet_number', // Mobile Money où reverser les paiements en ligne
        'accent_color',        // Couleur d'accent de la vitrine (Ma boutique → Concevoir)
        'storefront',          // Réglages de la vitrine (JSON, voir App\Support\Storefront)
        'meta_pixel_id',       // Pixel Meta (Facebook) du vendeur, chargé sur ses pages (App\Support\MetaPixel)
        'storefront_history',  // Dernières publications de la vitrine (restaurables dans l'éditeur)
        'slug',                // Adresse courte de la boutique : /s/{slug}
        'storefront_draft',    // Brouillon de la vitrine (Faire grandir ma vitrine → Personnalisation)
        'storefront_publish_at', // Publication programmée du brouillon
        'preview_token', 'preview_expires_at', 'preview_feedback', // Lien d'aperçu 48 h et avis reçus
    ];

    public function owner() {
        return $this->belongsTo(User::class, 'user_id'); // Cette méthode indique qu'une boutique (Shop) appartient à un utilisateur (User) qui est le propriétaire.
    }

    public function products() {
    return $this->hasMany(Product::class); // Cette méthode indique qu'une boutique (Shop) peut avoir plusieurs produits (Product).
}

public function orders()
{
    return $this->hasMany(Order::class);
}

public function clients()
{
    return $this->belongsToMany(\App\Models\User::class, 'shop_user')->withTimestamps();  // Une boutique (Shop) peut avoir plusieurs clients (User) abonnés via une table pivot shop_user.
}
protected $casts = [
    'commission_rate'  => 'float',
    'is_approved'      => 'boolean',
    'plan_expires_at'  => 'datetime',  // Cast auto en Carbon pour isFuture() / isPast()
    'storefront'       => 'array',     // Réglages de la vitrine (App\Support\Storefront)
    'storefront_history' => 'array',
    'storefront_draft'   => 'array',
    'storefront_publish_at' => 'datetime',
    'preview_expires_at' => 'datetime',
    'preview_feedback'   => 'array',
];

// 🟢 0.15 → 15 (pour l'affichage)
public function getCommissionRatePercentAttribute()
{
    return $this->commission_rate !== null ? round($this->commission_rate * 100, 2) : null;
}

public function user()
{
    return $this->belongsTo(User::class);
}

public function favoritedBy()
{
    return $this->belongsToMany(User::class, 'shop_favorites');
}

}
