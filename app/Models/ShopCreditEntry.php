<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mouvement du « Crédit Shopio » d'une boutique : + remboursement, − paiement de campagne.
 * Le solde est la somme des mouvements ; il sert à payer des campagnes et ne se retire pas.
 * Les débits passent par App\Support\Campaigns\ShopCredit (contrôle du solde sous verrou).
 */
class ShopCreditEntry extends Model
{
    public const REASON_REMBOURSEMENT     = 'remboursement';
    public const REASON_PAIEMENT_CAMPAGNE = 'paiement_campagne';
    public const REASON_AJUSTEMENT        = 'ajustement';
    public const REASON_PARRAINAGE        = 'parrainage'; // mois offert au parrain sans abonnement en cours

    protected $fillable = ['shop_id', 'amount_gnf', 'reason', 'ad_campaign_id', 'note', 'created_by'];

    protected $casts = ['amount_gnf' => 'integer'];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function campaign()
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }
}
