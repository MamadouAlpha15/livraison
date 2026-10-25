<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Visuel (image ou vidéo) d'une campagne réseaux sociaux, à valider par le vendeur. */
class AdCampaignCreative extends Model
{
    public const STATUS_EN_ATTENTE            = 'en_attente';
    public const STATUS_VALIDEE               = 'validee';
    public const STATUS_MODIFICATION_DEMANDEE = 'modification_demandee';

    protected $fillable = ['ad_campaign_id', 'file_path', 'type', 'caption', 'status', 'seller_comment', 'round'];

    protected $casts = ['round' => 'integer'];

    public function campaign()
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }
}
