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

    /** Prix effectif : celui de la variante, sinon celui du produit parent */
    public function getEffectivePriceAttribute(): float
    {
        return $this->price !== null ? (float) $this->price : (float) $this->product->price;
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
