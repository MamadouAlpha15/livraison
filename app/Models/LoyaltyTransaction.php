<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyTransaction extends Model
{
    const TYPE_EARN              = 'earn';
    const TYPE_REDEEM            = 'redeem';
    const TYPE_REDEEM_REFUND     = 'redeem_refund';
    const TYPE_REFERRAL_REFERRER = 'referral_referrer';
    const TYPE_REFERRAL_REFERRED = 'referral_referred';

    protected $fillable = ['user_id', 'order_id', 'type', 'points', 'balance_after', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
