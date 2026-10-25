<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Formule de campagne (Boost produit, Top catégorie, Boutique à la une, Starter, Pro, Premium).
 * Les prix sont rangés par durée en jours : {"3": 25000, "7": 50000, "30": 150000}.
 * Voir App\Support\Campaigns\CampaignPricing pour le calcul d'un devis.
 */
class AdPackage extends Model
{
    public const TYPE_BOOST  = 'boost';
    public const TYPE_SOCIAL = 'social';

    protected $fillable = [
        'type', 'code', 'name', 'placement', 'duration_days', 'prices', 'ad_budget_gnf', 'fee_gnf',
        'max_items', 'allows_shop_wide', 'max_actives', 'capacity_scope', 'details', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'prices'           => 'array',
        'details'          => 'array',
        'allows_shop_wide' => 'boolean',
        'is_active'        => 'boolean',
        'duration_days'    => 'integer',
        'ad_budget_gnf'    => 'integer',
        'fee_gnf'          => 'integer',
        'max_items'        => 'integer',
        'max_actives'      => 'integer',
    ];

    public function campaigns()
    {
        return $this->hasMany(AdCampaign::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isBoost(): bool
    {
        return $this->type === self::TYPE_BOOST;
    }

    public function isSocial(): bool
    {
        return $this->type === self::TYPE_SOCIAL;
    }

    /** Durées proposées, en jours, de la plus courte à la plus longue. */
    public function durations(): array
    {
        $days = array_map('intval', array_keys($this->prices ?? []));
        sort($days);
        return $days;
    }

    /** Prix en GNF pour une durée proposée, null si la formule ne la propose pas. */
    public function priceFor(int $days): ?int
    {
        $price = ($this->prices ?? [])[(string) $days] ?? null;
        return $price === null ? null : (int) $price;
    }
}
