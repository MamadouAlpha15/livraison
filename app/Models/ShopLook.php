<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Look du thème Lookbook : une tenue mise en scène (titre, scène, photo portée) composée de plusieurs
 * produits de la boutique. Le client commande le look entier ou une seule pièce (Boutique → Looks).
 */
class ShopLook extends Model
{
    public const MAX_ITEMS = 6;

    protected $fillable = ['shop_id', 'title', 'scene', 'image', 'position', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'position' => 'integer'];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'shop_look_items', 'look_id', 'product_id')
            ->withPivot('position', 'pin_x', 'pin_y')->orderBy('shop_look_items.position');
    }
}
