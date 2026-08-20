<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'product_id', 'product_variant_id', 'quantity'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /** Prix unitaire au moment de l'affichage : celui de la variante si choisie, sinon celui du produit. */
    public function getUnitPriceAttribute(): float
    {
        return $this->variant ? $this->variant->effective_price : $this->product->current_price;
    }

    public function getSubtotalAttribute(): float
    {
        return $this->unit_price * $this->quantity;
    }

    /** Stock disponible pour cette ligne (variante si choisie, sinon produit — null = illimité). */
    public function getAvailableStockAttribute(): ?int
    {
        return $this->variant ? $this->variant->stock : $this->product->stock;
    }
}
