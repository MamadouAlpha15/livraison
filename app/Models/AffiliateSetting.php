<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Réglages du programme d'affiliation (une seule ligne), modifiables dans Admin → Affiliation → Réglages. */
class AffiliateSetting extends Model
{
    use HasFactory;

    /** Valeurs de départ de la migration, reprises si la ligne a disparu */
    public const DEFAULTS = [
        'default_rate' => 20, 'commission_months' => 12, 'validation_days' => 30, 'payout_threshold' => 50000,
        'reward_months' => 1, 'cookie_days' => 30, 'annual_reward_cap' => 6, 'active_min_products' => 3, 'active_min_orders' => 1,
    ];

    protected $fillable = [
        'default_rate', 'commission_months', 'validation_days', 'payout_threshold', 'reward_months',
        'cookie_days', 'annual_reward_cap', 'active_min_products', 'active_min_orders',
    ];

    protected $casts = [
        'default_rate'        => 'decimal:2',
        'commission_months'   => 'integer',
        'validation_days'     => 'integer',
        'payout_threshold'    => 'integer',
        'reward_months'       => 'integer',
        'cookie_days'         => 'integer',
        'annual_reward_cap'   => 'integer',
        'active_min_products' => 'integer',
        'active_min_orders'   => 'integer',
    ];

    public static function current(): self
    {
        return static::query()->orderBy('id')->first() ?? static::create(self::DEFAULTS);
    }
}
