<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** Vues d'une fiche produit par jour (une par visiteur et par jour, voir App\Support\TrafficSource). */
class ProductView extends Model
{
    protected $fillable = ['product_id', 'shop_id', 'day', 'views'];

    protected $casts = ['day' => 'date'];

    public static function record(int $productId, int $shopId): void
    {
        static::upsert(
            [['product_id' => $productId, 'shop_id' => $shopId, 'day' => now()->toDateString(), 'views' => 1, 'created_at' => now(), 'updated_at' => now()]],
            ['product_id', 'day'],
            ['views' => DB::raw('views + 1'), 'updated_at' => now()]
        );
    }
}
