<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Réglages des relances des inactifs (une seule ligne), modifiables dans Admin → Relances. */
class ReminderSetting extends Model
{
    /** Valeurs de départ de la migration, reprises si la ligne a disparu */
    public const DEFAULTS = [
        'enabled' => true, 'client_days' => 30, 'company_days' => 14, 'interval_days' => 15, 'max_steps' => 3,
        'daily_limit' => 300, 'channel_push' => true, 'channel_mail' => true, 'channel_sms' => false,
    ];

    protected $fillable = [
        'enabled', 'client_days', 'company_days', 'interval_days', 'max_steps', 'daily_limit',
        'channel_push', 'channel_mail', 'channel_sms',
    ];

    protected $casts = [
        'enabled'       => 'boolean',
        'client_days'   => 'integer',
        'company_days'  => 'integer',
        'interval_days' => 'integer',
        'max_steps'     => 'integer',
        'daily_limit'   => 'integer',
        'channel_push'  => 'boolean',
        'channel_mail'  => 'boolean',
        'channel_sms'   => 'boolean',
    ];

    public static function current(): self
    {
        return static::query()->orderBy('id')->first() ?? static::create(self::DEFAULTS);
    }

    /** Jours d'inactivité avant le 1er rappel pour ce public */
    public function daysFor(string $audience): int
    {
        return $audience === InactivityReminder::AUDIENCE_COMPANY ? $this->company_days : $this->client_days;
    }
}
