<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Produit promu par une campagne ; product_id null = toute la boutique. */
class AdCampaignItem extends Model
{
    protected $fillable = ['ad_campaign_id', 'product_id'];

    public function campaign()
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
