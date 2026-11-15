<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle de page produit : fourni par Shopio (shop_id vide, « key » fixe) ou enregistré par un vendeur
 * à partir d'une de ses pages, pour l'appliquer à d'autres produits.
 */
class PageTemplate extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'general'      => 'Tous produits',
        'mode'         => 'Mode',
        'beaute'       => 'Beauté et parfum',
        'electronique' => 'Électronique',
        'unique'       => 'Produit unique',
    ];

    protected $fillable = ['shop_id', 'key', 'name', 'category', 'description', 'data', 'thumbnail', 'is_active', 'position', 'created_by'];

    protected $casts = ['data' => 'array', 'is_active' => 'boolean', 'position' => 'integer'];

    /** Mêmes valeurs par défaut qu'en base, déjà présentes sur un modèle tout juste créé */
    protected $attributes = ['is_active' => true, 'category' => 'general', 'position' => 0];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function pages()
    {
        return $this->hasMany(ProductPage::class);
    }

    public function isShopio(): bool
    {
        return $this->shop_id === null;
    }

    /** Modèles qu'une boutique peut utiliser : ceux de Shopio et les siens, jamais ceux d'une autre boutique */
    public function scopeAvailableTo(Builder $query, Shop|int $shop): Builder
    {
        $shopId = $shop instanceof Shop ? $shop->id : $shop;
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('shop_id')->orWhere('shop_id', $shopId))
            ->orderByRaw('case when shop_id is null then 0 else 1 end')->orderBy('position')->orderBy('name');
    }
}
