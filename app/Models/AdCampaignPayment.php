<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Paiement (et éventuel remboursement) d'une campagne. */
class AdCampaignPayment extends Model
{
    public const METHOD_MOBILE_MONEY = 'mobile_money'; // ChapChap Pay : Orange Money ou MTN, choisi sur la page de paiement
    public const METHOD_CREDIT       = 'credit';       // Crédit Shopio de la boutique
    public const METHOD_MANUEL       = 'manuel';       // marqué payé par l'équipe Shopio (virement, espèces)

    public const STATUS_EN_ATTENTE = 'en_attente';
    public const STATUS_PAYE       = 'paye';
    public const STATUS_ECHOUE     = 'echoue';
    public const STATUS_REMBOURSE  = 'rembourse';

    protected $fillable = [
        'ad_campaign_id', 'method', 'operator', 'amount_gnf', 'status', 'gateway_operation_id', 'transaction_ref',
        'refund_amount_gnf', 'paid_at', 'refunded_at', 'recorded_by',
    ];

    protected $casts = [
        'amount_gnf'        => 'integer',
        'refund_amount_gnf' => 'integer',
        'paid_at'           => 'datetime',
        'refunded_at'       => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }
}
