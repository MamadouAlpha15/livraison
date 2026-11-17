<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Réglages globaux de Shopio IA (une ligne) : activation, messages par mois selon le plan de la boutique. */
class ShopioAiSetting extends Model
{
    public const DEFAULTS = ['enabled' => true, 'free_monthly_limit' => 50, 'pro_monthly_limit' => 1000, 'free_auto_allowed' => false];

    protected $fillable = ['enabled', 'free_monthly_limit', 'pro_monthly_limit', 'free_auto_allowed'];

    protected $casts = [
        'enabled' => 'boolean', 'free_monthly_limit' => 'integer', 'pro_monthly_limit' => 'integer', 'free_auto_allowed' => 'boolean',
    ];

    public static function current(): self
    {
        return static::query()->orderBy('id')->first() ?? static::create(self::DEFAULTS);
    }
}
