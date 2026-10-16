<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** Visites d'une boutique par jour et par source (voir App\Support\TrafficSource). */
class ShopTraffic extends Model
{
    protected $table = 'shop_traffic';

    protected $fillable = ['shop_id', 'day', 'source', 'campaign_id', 'visits'];

    protected $casts = ['day' => 'date'];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /** $campaignId : 0 si la visite ne vient pas d'un lien de campagne. */
    public static function record(int $shopId, string $source, int $campaignId = 0): void
    {
        static::upsert(
            [['shop_id' => $shopId, 'day' => now()->toDateString(), 'source' => $source, 'campaign_id' => $campaignId, 'visits' => 1, 'created_at' => now(), 'updated_at' => now()]],
            ['shop_id', 'day', 'source', 'campaign_id'],
            ['visits' => DB::raw('visits + 1'), 'updated_at' => now()]
        );
    }
}
