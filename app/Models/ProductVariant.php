<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\StockAlertService;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'name', 'image', 'price', 'stock', 'sku', 'is_active', 'sort_order'];

    protected $casts = [
        'price'     => 'decimal:2',
        'stock'     => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::updated(function (ProductVariant $variant) {
            if ($variant->wasChanged('stock')) {
                app(StockAlertService::class)->checkVariant($variant);

                // Une variante revient en stock : le produit redevient commandable
                $before = $variant->getOriginal('stock');
                if ($before !== null && $before <= 0 && $variant->stock > 0 && $variant->product) {
                    app(\App\Services\ClientProductAlertService::class)->productRestocked($variant->product);
                }
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_variant_id');
    }

    /**
     * Prix effectif : celui de la variante si défini, sinon celui du produit.
     * Si une vente flash est active sur le produit, la même remise (en %) s'applique
     * à TOUTES les variantes — y compris celles avec un prix personnalisé — puisque
     * c'est le même produit, juste une couleur/taille différente.
     */
    public function getEffectivePriceAttribute(): float
    {
        $base = $this->price !== null ? (float) $this->price : (float) $this->product->price;

        if ($this->product->is_flash_active && (float) $this->product->price > 0) {
            $ratio = (float) $this->product->flash_price / (float) $this->product->price;
            return round($base * $ratio);
        }

        return $base;
    }

    /** URL de la photo de la variante (null si elle n'en a pas — on retombe alors sur la photo du produit) */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getOutOfStockAttribute(): bool
    {
        return $this->stock <= 0;
    }

    public function getLowStockAttribute(): bool
    {
        return $this->stock > 0 && $this->stock <= 5;
    }
}
