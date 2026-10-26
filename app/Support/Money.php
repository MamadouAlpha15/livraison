<?php

namespace App\Support;

/**
 * Montants en francs guinéens : entiers, espace entre les milliers (insécable, pour ne pas couper
 * le nombre en fin de ligne). Dans les vues : @gnf($montant) → « 150 000 GNF ».
 */
class Money
{
    public static function gnf(int|float|string|null $amount, bool $withCurrency = true): string
    {
        $formatted = number_format((int) round((float) $amount), 0, ',', "\u{00A0}");
        return $withCurrency ? $formatted . "\u{00A0}GNF" : $formatted;
    }
}
