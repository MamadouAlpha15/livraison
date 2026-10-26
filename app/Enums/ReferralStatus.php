<?php

namespace App\Enums;

/** Boutique parrainée : inscrite → active (produits + commandes) → payante (abonnement payé) | annulée. */
enum ReferralStatus: string
{
    case Registered = 'registered';
    case Active = 'active';
    case Paying = 'paying';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Registered => 'Inscrite',
            self::Active     => 'Active',
            self::Paying     => 'Abonnée',
            self::Cancelled  => 'Annulée',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Registered => 'muted',
            self::Active     => 'info',
            self::Paying     => 'ok',
            self::Cancelled  => 'err',
        };
    }
}
