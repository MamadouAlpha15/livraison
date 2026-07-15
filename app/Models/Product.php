<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\StockAlertService;

class Product extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::updated(function (Product $product) {
            if ($product->wasChanged('stock')) {
                app(StockAlertService::class)->checkProduct($product);
            }
        });
    }

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
        'low_stock_threshold', // seuil d'alerte stock faible
        'flash_price',         // prix pendant une vente flash
        'flash_starts_at',     // début de la vente flash
        'flash_ends_at',       // fin de la vente flash (compte à rebours)
    ];

    /*
    |------------------------------------------------------------------
    | Casts automatiques
    |------------------------------------------------------------------
    */
    protected $casts = [
        'price'            => 'decimal:2',
        'original_price'   => 'decimal:2',
        'flash_price'      => 'decimal:2',
        'flash_starts_at'  => 'datetime',
        'flash_ends_at'    => 'datetime',
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

    /** Variantes du produit (taille/couleur/etc.), triées par ordre défini par le vendeur */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    /** Variantes actives uniquement, pour l'affichage côté client */
    public function activeVariants()
    {
        return $this->variants()->where('is_active', true);
    }

    /** Vrai si ce produit gère des variantes (sinon on utilise le stock global) */
    public function getHasVariantsAttribute(): bool
    {
        return $this->variants()->exists();
    }

    /** Clients ayant mis ce produit dans leur liste de souhaits */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'product_favorites')->withTimestamps();
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

    /** Vrai si une vente flash est active en ce moment (prix + fenêtre de temps valides) */
    public function getIsFlashActiveAttribute(): bool
    {
        if ($this->flash_price === null || $this->flash_ends_at === null) {
            return false;
        }
        if ($this->flash_starts_at && $this->flash_starts_at->isFuture()) {
            return false;
        }
        return $this->flash_ends_at->isFuture();
    }

    /** Prix réellement appliqué en ce moment : prix flash si actif, sinon prix normal */
    public function getCurrentPriceAttribute(): float
    {
        return $this->is_flash_active ? (float) $this->flash_price : (float) $this->price;
    }

    /** Secondes restantes avant la fin de la vente flash (0 si inactive) */
    public function getFlashSecondsRemainingAttribute(): int
    {
        if (!$this->is_flash_active) return 0;
        return max(0, (int) round(now()->diffInSeconds($this->flash_ends_at, false)));
    }

    /** Pourcentage de remise de la vente flash (par rapport au prix normal) */
    public function getFlashDiscountPercentAttribute(): int
    {
        if (!$this->is_flash_active || (float) $this->price <= 0) return 0;
        return (int) round((1 - $this->flash_price / $this->price) * 100);
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