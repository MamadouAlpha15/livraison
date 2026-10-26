<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Statistiques d'une campagne pour un jour. source = auto (compteurs de Shopio, mise en avant)
 * ou admin (chiffres saisis depuis Meta / TikTok, réseaux sociaux).
 */
class AdCampaignStat extends Model
{
    protected $table = 'ad_campaign_stats_daily';

    protected $fillable = [
        'ad_campaign_id', 'day', 'source', 'impressions', 'clicks', 'messages', 'orders', 'revenue_gnf', 'spend_gnf',
    ];

    // « day » reste une chaîne Y-m-d : un cast date l'écrirait « Y-m-d 00:00:00 » sous SQLite
    // et la recherche par jour (updateOrCreate) ne retrouverait plus la ligne.
    protected $casts = [
        'impressions' => 'integer',
        'clicks'      => 'integer',
        'messages'    => 'integer',
        'orders'      => 'integer',
        'revenue_gnf' => 'integer',
        'spend_gnf'   => 'integer',
    ];

    public function campaign()
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }
}
