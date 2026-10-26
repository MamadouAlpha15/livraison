<?php

namespace App\Models;

use App\Enums\ReferralStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Boutique parrainée par un affilié (une seule par boutique). */
class Referral extends Model
{
    use HasFactory;

    /** Réponses à « Comment as-tu connu Shopio ? » */
    public const SOURCES = [
        'ami'       => 'Un ami ou un vendeur Shopio',
        'facebook'  => 'Facebook',
        'tiktok'    => 'TikTok',
        'instagram' => 'Instagram',
        'whatsapp'  => 'WhatsApp',
        'google'    => 'Recherche Google',
        'radio_tv'  => 'Radio ou télévision',
        'affiche'   => 'Affiche ou flyer',
        'autre'     => 'Autre',
    ];

    protected $fillable = [
        'affiliate_id', 'referred_shop_id', 'status', 'source', 'activated_at', 'first_paid_at',
        'flagged', 'flag_reason', 'ip_hash', 'device_hash',
    ];

    protected $casts = [
        'status'        => ReferralStatus::class,
        'activated_at'  => 'datetime',
        'first_paid_at' => 'datetime',
        'flagged'       => 'boolean',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'referred_shop_id');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function reward()
    {
        return $this->hasOne(AffiliateReward::class);
    }

    public function sourceLabel(): ?string
    {
        return $this->source ? (self::SOURCES[$this->source] ?? $this->source) : null;
    }
}
