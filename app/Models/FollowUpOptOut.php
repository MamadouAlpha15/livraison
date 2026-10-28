<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Client qui ne veut plus de relances : d'une boutique, ou de toutes (shop_id vide). Définitif. */
class FollowUpOptOut extends Model
{
    protected $fillable = ['user_id', 'shop_id', 'source'];

    public static function blocks(int $userId, ?int $shopId): bool
    {
        return static::where('user_id', $userId)
            ->where(fn ($q) => $q->whereNull('shop_id')->when($shopId, fn ($w) => $w->orWhere('shop_id', $shopId)))
            ->exists();
    }
}
