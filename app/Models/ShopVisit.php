<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopVisit extends Model
{
    protected $fillable = ['shop_id', 'visited_on', 'count'];

    protected $casts = ['visited_on' => 'date'];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public static function record(int $shopId): void
    {
        static::upsert(
            [['shop_id' => $shopId, 'visited_on' => now()->toDateString(), 'count' => 1, 'created_at' => now(), 'updated_at' => now()]],
            ['shop_id', 'visited_on'],
            ['count' => \Illuminate\Support\Facades\DB::raw('count + 1'), 'updated_at' => now()]
        );
    }
}
