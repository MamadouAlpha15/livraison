<?php

namespace App\Services\ShopioAI;

/**
 * Dernière vérification avant d'envoyer une réponse de l'IA : chaque montant (GNF), stock ou pourcentage
 * cité doit venir des données lues pendant cette réponse (outils) ou de la conversation.
 * Sinon la réponse est bloquée : on ne laisse jamais passer un prix, un stock ou une promotion inventés.
 */
class ReplyGuard
{
    /** @return string[] valeurs citées dans la réponse mais absentes des données */
    public static function unsupported(string $reply, array $toolResults, string $conversation = ''): array
    {
        $known = self::numbersIn(json_encode($toolResults, JSON_UNESCAPED_UNICODE) . ' ' . $conversation);
        $problems = [];

        // Montants : « 150 000 GNF », « 150.000 FG », « 150000 francs »
        preg_match_all('/(\d{1,3}(?:[\s\x{00A0}\x{202F}.]\d{3})+|\d+)\s*(?:GNF|FG|francs?\b)/iu', $reply, $amounts);
        // Quantités en stock : « 3 en stock », « il en reste 3 », « 3 pièces »
        preg_match_all('/(\d+)\s*(?:en stock|pi[eè]ces?|exemplaires?|disponibles?|restants?)|reste\D{0,12}(\d+)/iu', $reply, $stocks);
        // Remises : « -10 % », « 10% »
        preg_match_all('/(\d+)\s*%/u', $reply, $percents);

        foreach (array_merge($amounts[1], array_filter($stocks[1]), array_filter($stocks[2]), $percents[1]) as $raw) {
            $value = (int) preg_replace('/\D/u', '', $raw);
            if (!in_array($value, $known, true)) {
                $problems[] = trim($raw);
            }
        }
        return array_values(array_unique($problems));
    }

    /** Tous les nombres présents dans un texte (« 150 000 », « 150000 », « 3 ») */
    private static function numbersIn(string $text): array
    {
        preg_match_all('/\d{1,3}(?:[\s\x{00A0}\x{202F}.]\d{3})+|\d+/u', str_replace([' ', ' '], ' ', $text), $m);
        return array_values(array_unique(array_map(fn ($n) => (int) preg_replace('/\D/u', '', $n), $m[0])));
    }
}
