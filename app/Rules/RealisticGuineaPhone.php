<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * ═══════════════════════════════════════════════════════════════
 * RealisticGuineaPhone — numéro de téléphone guinéen plausible.
 * ═══════════════════════════════════════════════════════════════
 * Exige 9 chiffres commençant par 6 (format guinéen : 622 00 00 00),
 * et rejette les suites évidemment fictives qu'un client peu sérieux
 * tape pour passer le formulaire sans donner de vrai numéro :
 *   - chiffre répété (6222222222)
 *   - suite strictement croissante/décroissante (612345678)
 * But : réduire les commandes injoignables qui compliquent la vie
 * des vendeurs (numéro bidon = livraison impossible).
 */
class RealisticGuineaPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        // Tolère un préfixe international +224
        if (strlen($digits) === 12 && str_starts_with($digits, '224')) {
            $digits = substr($digits, 3);
        }

        if (!preg_match('/^6\d{8}$/', $digits)) {
            $fail('Le numéro doit être un numéro guinéen valide (9 chiffres commençant par 6). Ex : 622 00 00 00.');

            return;
        }

        // Trop peu de chiffres différents utilisés (ex: 622222222 → seulement {6,2})
        if (count(array_unique(str_split($digits))) <= 2) {
            $fail('Ce numéro ne semble pas valide. Merci de vérifier votre saisie.');

            return;
        }

        // Suite strictement croissante ou décroissante (ex: 612345678 / 698765432)
        $diffs = [];
        for ($i = 1; $i < strlen($digits); $i++) {
            $diffs[] = (int) $digits[$i] - (int) $digits[$i - 1];
        }
        $isSequential = count(array_unique($diffs)) === 1 && in_array($diffs[0], [1, -1], true);

        if ($isSequential) {
            $fail('Ce numéro ne semble pas valide. Merci de vérifier votre saisie.');
        }
    }
}
