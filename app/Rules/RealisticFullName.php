<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * ═══════════════════════════════════════════════════════════════
 * RealisticFullName — nom complet plausible.
 * ═══════════════════════════════════════════════════════════════
 * Exige au moins un prénom ET un nom (deux mots de 2+ caractères),
 * avec au moins une voyelle, et rejette les frappes clavier au
 * hasard type "LSJFLSSLFJLS" (aucune voyelle, un seul bloc de
 * lettres). But : réduire les commandes invité avec un faux nom,
 * qui compliquent la vie des vendeurs.
 */
class RealisticFullName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $name = trim((string) $value);
        $words = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);

        if (count($words) < 2) {
            $fail("Merci d'indiquer votre prénom et votre nom.");

            return;
        }

        foreach ($words as $word) {
            if (mb_strlen($word) < 2) {
                $fail("Merci d'indiquer votre prénom et votre nom complet.");

                return;
            }
        }

        if (!preg_match('/[aeiouyàâäéèêëîïôöùûüAEIOUYÀÂÄÉÈÊËÎÏÔÖÙÛÜ]/u', $name)) {
            $fail('Ce nom ne semble pas valide. Merci de vérifier votre saisie.');

            return;
        }

        // Caractère répété 4 fois ou plus d'affilée (ex: "Aaaaa Bbbbb")
        if (preg_match('/(.)\1{3,}/iu', $name)) {
            $fail('Ce nom ne semble pas valide. Merci de vérifier votre saisie.');
        }
    }
}
