<?php

namespace App\Enums;

/** Affilié vendeur (une boutique Shopio qui parraine) ou externe (sans boutique). */
enum AffiliateType: string
{
    case Vendor = 'vendor';
    case External = 'external';

    public function label(): string
    {
        return match ($this) {
            self::Vendor   => 'Vendeur',
            self::External => 'Externe',
        };
    }
}
