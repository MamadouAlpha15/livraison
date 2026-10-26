<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Mois offert au parrain vendeur : abonnement prolongé, ou Crédit Shopio s'il n'a pas d'abonnement en cours. */
class AffiliateReward extends Model
{
    use HasFactory;

    public const KIND_EXTENSION = 'extension';
    public const KIND_CREDIT    = 'credit';

    protected $fillable = ['affiliate_id', 'referral_id', 'shop_id', 'kind', 'months', 'amount_gnf', 'subscription_id', 'shop_credit_entry_id'];

    protected $casts = ['months' => 'integer', 'amount_gnf' => 'integer'];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
