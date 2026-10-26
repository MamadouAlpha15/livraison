<?php

namespace App\Enums;

/** En attente (inscrit depuis /devenir-affilie, à valider par l'équipe) → actif | suspendu. */
enum AffiliateStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'À valider',
            self::Active    => 'Actif',
            self::Suspended => 'Suspendu',
        };
    }

    /** Classe de la pastille (cp-st) */
    public function tone(): string
    {
        return match ($this) {
            self::Pending   => 'warn',
            self::Active    => 'ok',
            self::Suspended => 'err',
        };
    }
}
