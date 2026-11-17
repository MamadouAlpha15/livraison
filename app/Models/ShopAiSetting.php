<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Réglages de Shopio IA d'une boutique (Espace vendeur → Shopio IA). */
class ShopAiSetting extends Model
{
    public const MODE_SUGGESTION = 'suggestion';
    public const MODE_AUTO       = 'auto';

    protected $fillable = [
        'shop_id', 'enabled', 'mode', 'address_form', 'max_discount_percent', 'opening_hours', 'delivery_info',
        'return_policy', 'faq', 'tone_notes',
    ];

    protected $casts = ['enabled' => 'boolean', 'max_discount_percent' => 'integer'];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public static function forShop(int $shopId): self
    {
        return static::firstOrNew(['shop_id' => $shopId], ['enabled' => false, 'mode' => self::MODE_SUGGESTION, 'address_form' => 'tu']);
    }
}
