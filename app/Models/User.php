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
        'google_id',
        'role',          // ex: superadmin|admin|employe|vendeur|livreur|client
        'phone',
        'address',
        'country',       // code ISO 2 lettres ex: SN, CI, ML
        'shop_id',       // FK vers la boutique principale de l'utilisateur
        'role_in_shop',  // ex: admin|vendeur|livreur (rôle dans la boutique)
        'is_available',          // pour les livreurs
        'must_change_password',  // forcé à changer son mot de passe à la 1ère connexion
        'company_id',            // FK vers delivery_companies (membres d'une entreprise)
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'must_change_password' => 'boolean',
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

    // Alias — boutique assignée (pour employés/livreurs qui n'ont pas créé la boutique)
    // Identique à shop() mais nommé explicitement pour la lisibilité
    public function assignedShop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    // Commandes passées par l'utilisateur (en tant que CLIENT — filtre sur user_id)
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    // ✅ Commandes assignées à cet utilisateur EN TANT QUE LIVREUR
    // Filtre sur livreur_id — c'est ce qu'on utilise pour compter les livraisons
    // Exemple : $lv->ordersAsLivreur()->where('status','livrée')->count()
    public function ordersAsLivreur()
    {
        return $this->hasMany(Order::class, 'livreur_id');
    }

    // Abonnements multi-boutiques (si tu as un pivot shop_user)
    public function subscribedShops()
    {
        return $this->belongsToMany(Shop::class, 'shop_user')->withTimestamps();
    }

    // Commissions de livraison (en tant que livreur)
    public function courierCommissions()
    {
        return $this->hasMany(\App\Models\CourierCommission::class, 'livreur_id');
    }

    // Entreprise de livraison dont l'utilisateur est membre (via company_id)
    public function deliveryCompany()
    {
        return $this->belongsTo(DeliveryCompany::class, 'company_id');
    }

    // Entreprise de livraison dont l'utilisateur est le PROPRIÉTAIRE (delivery_companies.user_id)
    public function ownedCompany()
    {
        return $this->hasOne(DeliveryCompany::class, 'user_id');
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

    // Filtrer uniquement les vendeurs
    public function scopeVendeurs($q)
    {
        return $q->where(function ($qq) {
            $qq->where('role', 'vendeur')
               ->orWhere('role_in_shop', 'vendeur');
        });
    }

    // Livreurs disponibles (en ligne)
    public function scopeAvailableLivreurs($q)
    {
        return $q->where(function ($qq) {
            $qq->where('role', 'livreur')
               ->orWhere('role_in_shop', 'livreur');
        })->where('is_available', true);
    }

    // Livreurs d'une boutique spécifique
    public function scopeLivreursForShop($query, $shopId)
    {
        return $query->where(function ($qq) {
            $qq->where('role', 'livreur')
               ->orWhere('role_in_shop', 'livreur');
        })->where('shop_id', $shopId);
    }

    
}