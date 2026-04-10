<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /*
    |------------------------------------------------------------------
    | Colonnes autorisées en mass-assignment
    | ⚠ Toutes les nouvelles colonnes doivent être listées ici
    |------------------------------------------------------------------
    */
    protected $fillable = [
        'shop_id',
        'name',
        'description',
        'price',
        'original_price',    // prix barré (promo)
        'category',          // catégorie du produit
        'stock',             // quantité en stock
        'unit',              // unité : pièce, kg, litre…
        'preparation_time',  // minutes (restaurant)
        'is_active',         // visible par les clients
        'is_featured',       // mis en avant
        'is_available',      // disponible aujourd'hui (restaurant)
        'allergens',         // liste allergènes
        'tags',              // mots-clés
        'image',             // photo principale
        'gallery',           // JSON : photos supplémentaires
    ];

    /*
    |------------------------------------------------------------------
    | Casts automatiques
    |------------------------------------------------------------------
    */
    protected $casts = [
        'price'            => 'decimal:2',
        'original_price'   => 'decimal:2',
        'stock'            => 'integer',
        'preparation_time' => 'integer',
        'is_active'        => 'boolean',
        'is_featured'      => 'boolean',
        'is_available'     => 'boolean',
        // gallery est décodé manuellement dans les vues avec json_decode()
        // pour garder la flexibilité
    ];

    /*
    |------------------------------------------------------------------
    | Relations
    |------------------------------------------------------------------
    */

    /** Boutique propriétaire du produit */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /** Articles de commandes liés à ce produit */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /*
    |------------------------------------------------------------------
    | Accesseurs utiles
    |------------------------------------------------------------------
    */

    /** Retourne le tableau des images galerie (tableau vide si aucune) */
    public function getGalleryArrayAttribute(): array
    {
        if (!$this->gallery) return [];
        $decoded = json_decode($this->gallery, true);
        return is_array($decoded) ? $decoded : [];
    }

    /** Vrai si le produit a une promotion (prix barré > prix actuel) */
    public function getHasPromoAttribute(): bool
    {
        return $this->original_price && $this->original_price > $this->price;
    }

    /** Pourcentage de remise arrondi */
    public function getDiscountPercentAttribute(): int
    {
        if (!$this->has_promo) return 0;
        return (int) round((1 - $this->price / $this->original_price) * 100);
    }

    /** Stock faible (entre 1 et 5) */
    public function getLowStockAttribute(): bool
    {
        return $this->stock > 0 && $this->stock <= 5;
    }

    /** Rupture de stock */
    public function getOutOfStockAttribute(): bool
    {
        return $this->stock <= 0;
    }

    /*
    |------------------------------------------------------------------
    | Scopes de requête
    |------------------------------------------------------------------
    */

    /** Produits actifs uniquement */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Produits en vedette */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /** Produits en stock (stock > 0) */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /** Produits disponibles aujourd'hui */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }
}