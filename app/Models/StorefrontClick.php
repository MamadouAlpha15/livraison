<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** Clics dans une section de la vitrine, par jour (onglet Qualité de « Faire grandir ma vitrine »). */
class StorefrontClick extends Model
{
    protected $fillable = ['shop_id', 'day', 'block', 'clicks'];

    protected $casts = ['day' => 'date'];

    public static function record(int $shopId, string $block): void
    {
        static::upsert(
            [['shop_id' => $shopId, 'day' => now()->toDateString(), 'block' => $block, 'clicks' => 1, 'created_at' => now(), 'updated_at' => now()]],
            ['shop_id', 'day', 'block'],
            ['clicks' => DB::raw('clicks + 1'), 'updated_at' => now()]
        );
    }
}
