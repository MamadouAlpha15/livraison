<?php

namespace App\Models;

use App\Enums\CommissionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Commission d'un affilié sur un paiement d'abonnement d'une boutique parrainée (montant entier en GNF). */
class Commission extends Model
{
    use HasFactory;

    protected $fillable = ['affiliate_id', 'referral_id', 'subscription_payment_id', 'payout_id', 'amount', 'status', 'available_at'];

    protected $casts = [
        'status'       => CommissionStatus::class,
        'amount'       => 'integer',
        'available_at' => 'datetime',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }

    public function subscriptionPayment()
    {
        return $this->belongsTo(Subscription::class, 'subscription_payment_id');
    }

    public function payout()
    {
        return $this->belongsTo(Payout::class);
    }
}
