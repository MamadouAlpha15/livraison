<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',          // ex: superadmin|admin|employe|vendeur|livreur|client
        'phone',
        'address',
        'shop_id',       // FK vers la boutique principale de l’utilisateur
        'role_in_shop',  // ex: admin|vendeur|livreur (rôle dans la boutique)
        'is_available', // pour les livreurs
        
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /* =========================
     | Relations
     |=========================*/

    // Boutique principale (FK users.shop_id)
    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    // Commandes passées par l'utilisateur (en tant que client)
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    // Abonnements multi-boutiques (si tu as un pivot shop_user)
    public function subscribedShops()
    {
        return $this->belongsToMany(Shop::class, 'shop_user')->withTimestamps();
    }

    /* =========================
     | Helper: boutique courante
     |=========================*/
    /**
     * Retourne l'ID de la boutique courante.
     * 1) Priorité à la session 'current_shop_id' si définie
     * 2) Sinon, fallback sur users.shop_id
     * 3) Sinon, tentative via la relation shop()
     */
    public function currentShopId(): ?int
    {
        if (session()->has('current_shop_id')) {
            return (int) session('current_shop_id');
        }

        return $this->shop_id ?? $this->shop?->id;
    }

    /* =========================
     | Scopes utilitaires
     |=========================*/

    // Filtrer les users d'une boutique
    public function scopeInShop($q, $shopId)
    {
        return $q->where('shop_id', $shopId);
    }

    // Filtrer uniquement les livreurs (selon tes colonnes)
    public function scopeLivreurs($q)
    {
        return $q->where(function ($qq) {
            $qq->where('role', 'livreur')
               ->orWhere('role_in_shop', 'livreur');
        });
    }

    // Filtrer uniquement les vendeurs (si besoin ailleurs)
    public function scopeVendeurs($q)
    {
        return $q->where(function ($qq) {
            $qq->where('role', 'vendeur')
               ->orWhere('role_in_shop', 'vendeur');
        });
    }

    public function scopeAvailableLivreurs($q)
{
    return $q->where(function($qq){
        $qq->where('role', 'livreur')
           ->orWhere('role_in_shop', 'livreur');
    })->where('is_available', true);
}
// app/Models/User.php

public function scopeLivreursForShop($query, $shopId)
{
    return $query->where('role', 'livreur')
                 ->where('shop_id', $shopId); // ou ->whereHas('assignedShop', fn($q) => $q->where('shops.id', $shopId))
}

public function courierCommissions()
{
    return $this->hasMany(\App\Models\CourierCommission::class, 'livreur_id'); // 
}


}
