<?php

namespace App\Services\FollowUp;

/**
 * Remplace les variables d'un modèle de relance ({prenom_client}, {prix}…).
 * Un passage entre [[ ]] est retiré si une de ses variables est vide ; les passages peuvent s'imbriquer.
 */
class TemplateRenderer
{
    public static function render(string $template, array $vars): string
    {
        // Passages conditionnels, du plus intérieur au plus extérieur
        $pattern = '/\[\[((?:(?!\[\[|\]\]).)*)\]\]/su';
        while (preg_match($pattern, $template)) {
            $template = preg_replace_callback($pattern, function ($m) use ($vars) {
                preg_match_all('/\{[a-z_]+\}/', $m[1], $found);
                foreach ($found[0] as $var) {
                    if (($vars[$var] ?? '') === '') {
                        return '';
                    }
                }
                return $m[1];
            }, $template);
        }

        $text = strtr($template, array_map(fn ($v) => (string) $v, $vars));
        // Variables inconnues ou vides restantes : retirées, espaces et ponctuation remis en ordre
        $text = preg_replace('/\{[a-z_]+\}/', '', $text);
        // Espace avant , . ) retirée ; celle avant : ; ! ? gardée (typographie française)
        $text = preg_replace('/[ \t]+([,.)])/u', '$1', $text);
        $text = preg_replace('/\(\s*\)/u', '', $text);
        return trim(preg_replace('/[ \t]{2,}/u', ' ', $text));
    }

    /** Variables inconnues utilisées dans un modèle (pour prévenir le vendeur) */
    public static function unknownVariables(string $template): array
    {
        preg_match_all('/\{[a-z_]+\}/', $template, $found);
        return array_values(array_diff(array_unique($found[0]), array_keys(FollowUpCatalog::VARIABLES)));
    }
}
