<?php

namespace App\Models;

use App\Enums\AffiliateStatus;
use App\Enums\AffiliateType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Affilié du programme de parrainage : une boutique Shopio (vendor) ou une personne externe.
 * Son code sert dans les liens ?ref=CODE. Logique : App\Services\AffiliateService.
 */
class Affiliate extends Model
{
    use HasFactory, SoftDeletes;

    /** Moyens de retrait (Mobile Money) */
    public const PAYOUT_METHODS = [
        'orange_money' => 'Orange Money',
        'mtn_momo'     => 'MTN MoMo',
        'paycard'      => 'PayCard',
    ];

    protected $fillable = [
        'user_id', 'type', 'code', 'name', 'email', 'phone', 'commission_rate', 'status',
        'payout_method', 'payout_phone', 'last_ip_hash', 'device_hash',
    ];

    protected $casts = [
        'type'            => AffiliateType::class,
        'status'          => AffiliateStatus::class,
        'commission_rate' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clicks()
    {
        return $this->hasMany(ReferralClick::class);
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    public function rewards()
    {
        return $this->hasMany(AffiliateReward::class);
    }

    public function isActive(): bool
    {
        return $this->status === AffiliateStatus::Active;
    }

    /** Boutique du parrain vendeur (null pour un affilié externe) */
    public function shop(): ?Shop
    {
        return $this->type === AffiliateType::Vendor && $this->user?->shop_id
            ? Shop::find($this->user->shop_id)
            : null;
    }

    public function displayName(): string
    {
        return $this->name ?: ($this->shop()?->name ?? $this->user?->name ?? 'Affilié #' . $this->id);
    }

    public function contactEmail(): ?string
    {
        return $this->user?->email ?: $this->email;
    }

    public function contactPhone(): ?string
    {
        return $this->payout_phone ?: ($this->phone ?: $this->user?->phone);
    }

    /** Lien de parrainage : page d'accueil, le cookie suit ensuite le visiteur jusqu'à l'inscription */
    public function link(): string
    {
        return rtrim(url('/'), '/') . '/?ref=' . $this->code;
    }
}
