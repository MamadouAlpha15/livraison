<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Lien de campagne nommé d'une boutique (« Influenceuse Awa », « Promo Tabaski »).
 * Le lien porte ?utm_source={channel}&utm_campaign={slug} ; voir App\Support\TrafficSource.
 */
class MarketingCampaign extends Model
{
    public const MAX_ACTIVE = 20;

    // landing : fin de lien de la catégorie visée (null = toute la boutique), voir Faire grandir ma vitrine → Partage
    protected $fillable = ['shop_id', 'name', 'slug', 'channel', 'landing', 'promo_code_id', 'archived_at'];

    protected $casts = ['archived_at' => 'datetime'];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    /** Identifiant court et unique dans la boutique, tiré du nom (« Promo Tabaski » → promo-tabaski). */
    public static function uniqueSlug(int $shopId, string $name): string
    {
        $base = Str::limit(Str::slug($name), 32, '') ?: 'campagne';
        $slug = $base;
        for ($i = 2; static::where('shop_id', $shopId)->where('slug', $slug)->exists(); $i++) {
            $slug = $base . '-' . $i;
        }
        return $slug;
    }
}
