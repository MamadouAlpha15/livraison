<?php

namespace App\Models;

use App\Services\FollowUp\FollowUpCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Réglage d'une situation de relance pour une boutique (ou de l'admin si shop_id est vide).
 * Sans ligne enregistrée, la boutique utilise les réglages de départ de FollowUpCatalog.
 */
class FollowUpRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id', 'type', 'enabled', 'delay_minutes', 'max_attempts', 'interval_minutes', 'template', 'tone', 'locale',
        'promo_code_id', 'send_from_hour', 'send_to_hour', 'excluded_days', 'channels',
    ];

    protected $casts = [
        'enabled'          => 'boolean',
        'delay_minutes'    => 'integer',
        'max_attempts'     => 'integer',
        'interval_minutes' => 'integer',
        'send_from_hour'   => 'integer',
        'send_to_hour'     => 'integer',
        'excluded_days'    => 'array',
        'channels'         => 'array',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    /** Règle enregistrée, ou règle de départ (non enregistrée) */
    public static function resolve(?int $shopId, string $type): self
    {
        return static::where('shop_id', $shopId)->where('type', $type)->first() ?? static::defaults($shopId, $type);
    }

    public static function defaults(?int $shopId, string $type): self
    {
        $d = FollowUpCatalog::get($type);
        return new static([
            'shop_id' => $shopId, 'type' => $type, 'enabled' => $d['enabled'], 'delay_minutes' => $d['delay'],
            'max_attempts' => $d['max'], 'interval_minutes' => $d['interval'], 'template' => null, 'tone' => 'amical',
            'locale' => 'fr', 'send_from_hour' => 8, 'send_to_hour' => 21, 'excluded_days' => [], 'channels' => $d['channels'],
        ]);
    }

    /** Texte utilisé : celui du vendeur, sinon le modèle par défaut du ton et de la langue */
    public function effectiveTemplate(): string
    {
        return filled($this->template) ? $this->template : FollowUpCatalog::template($this->type, $this->tone, $this->locale);
    }

    /** Envoi autorisé maintenant (heures et jours, heure de Conakry) ? */
    public function allowsSendingAt(\DateTimeInterface $moment): bool
    {
        $local = \Illuminate\Support\Carbon::instance($moment)->setTimezone('Africa/Conakry');
        if (in_array($local->dayOfWeek, $this->excluded_days ?? [], true)) {
            return false;
        }
        $hour = $local->hour;
        return $this->send_from_hour <= $this->send_to_hour
            ? $hour >= $this->send_from_hour && $hour < $this->send_to_hour
            : $hour >= $this->send_from_hour || $hour < $this->send_to_hour; // plage qui passe minuit
    }
}
