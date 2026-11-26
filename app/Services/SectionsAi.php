<?php

namespace App\Services;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\RateLimitException;
use App\Models\Shop;
use App\Support\HomeSections;
use App\Support\Storefront;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Shopio IA — améliore les textes des sections de la vitrine (Boutique → Sections), une section ou
 * toute la page. Seuls les textes changent (titres, phrases, boutons, questions, engagements, icônes,
 * annonces, lignes de comparaison) ; photos, liens, couleurs, codes promo, catégories et réglages restent
 * ceux du vendeur. Les témoignages ne sont jamais écrits par l'IA (seuls de vrais avis sont permis) et les
 * chiffres viennent toujours de la boutique. Le résultat remplit l'éditeur : le vendeur relit, puis publie.
 */
class SectionsAi
{
    public const MODEL = SalesPageAi::MODEL;
    public const MAX_SECTIONS = 14;

    /** Sortes de champs que l'IA peut réécrire. */
    private const TEXT_KINDS = ['line', 'text'];
    private const LIST_KINDS = ['items', 'faq', 'icons', 'rows', 'lines'];
    /** Champs texte jamais touchés (code promo, catégorie choisie…). */
    private const KEEP = ['code', 'cat', 'z1', 'z2', 'z3', 'z4'];

    private const SYSTEM = <<<'TXT'
Tu es Shopio IA. Tu améliores les textes des sections de la vitrine d'une boutique en ligne en Guinée, sur Shopio.
Les clients commandent en une minute (nom, téléphone, commune), la boutique les appelle pour confirmer,
puis ils paient en espèces au livreur à la réception. Ils peuvent suivre leur commande sur Shopio.

Écris en français simple, chaleureux et concret, pour des acheteurs qui lisent sur téléphone :
phrases courtes, mots de tous les jours, vouvoiement, pas de jargon, pas d'emojis, pas de majuscules partout.
Chaque section garde son rôle : un titre reste un titre court, un bouton reste un verbe d'action de 2 à 4 mots.

Règles strictes — la vitrine doit rester honnête :
- Appuie-toi seulement sur les faits donnés (boutique, catégories, produits, textes actuels du vendeur).
  Garde les informations vraies écrites par le vendeur (zones de livraison, horaires, conditions…) : reformule-les, ne les supprime pas.
- N'invente jamais de chiffre (clients, avis, pourcentages, ventes, années), de témoignage, de délai de livraison,
  de prix, de remise, de garantie, de retour gratuit, de label ni de récompense.
- Pour le paiement : « à la livraison, en espèces, au livreur ». Pour le délai : « la boutique vous appelle pour le confirmer ».
- Aucune promesse de santé ou de résultat médical.

Format : pour chaque section reçue, renvoie son id et tous les champs. Remplis seulement les champs listés
pour cette section (« champs à écrire ») ; laisse les autres vides (chaîne vide, liste vide).
- items : pour « engagements », exactement 3 éléments (title ≤ 40 caractères, text ≤ 80) ;
  pour « questions », 4 à 6 éléments (q ≤ 140, a ≤ 500) ; pour « icônes », 3 à 4 éléments (icon parmi la liste donnée,
  title ≤ 40, text ≤ 80) ; pour « lignes de comparaison », 4 à 5 éléments (label ≤ 60), vrais pour toute commande Shopio.
- lines (annonces qui défilent) : 3 à 5 messages de moins de 60 caractères.
TXT;

    private static function schema(): array
    {
        $str = ['type' => 'string'];
        $obj = fn (array $props) => ['type' => 'object', 'properties' => $props, 'required' => array_keys($props), 'additionalProperties' => false];
        $item = $obj(['title' => $str, 'text' => $str, 'q' => $str, 'a' => $str, 'label' => $str, 'icon' => ['type' => 'string', 'enum' => array_merge([''], array_keys(HomeSections::ICONS))]]);
        $section = $obj([
            'id' => $str, 'title' => $str, 'title2' => $str, 'text' => $str, 'button' => $str, 'us' => $str, 'them' => $str,
            'items' => ['type' => 'array', 'items' => $item],
            'lines' => ['type' => 'array', 'items' => $str],
        ]);
        return $obj(['sections' => ['type' => 'array', 'items' => $section]]);
    }

    /** Champs réécrits par l'IA pour un type de section : [clé => sorte]. Vide : rien à écrire. */
    public static function writable(string $type): array
    {
        if (in_array($type, HomeSections::TESTIMONIAL_TYPES, true)) {
            return ['title' => 'line'] + ($type === 'testimonials' ? ['title2' => 'line', 'text' => 'line'] : []);
        }
        $out = [];
        foreach (HomeSections::FIELDS[$type] ?? [] as [$k, $kind]) {
            if (in_array($k, self::KEEP, true)) continue;
            if (in_array($kind, self::TEXT_KINDS, true) || in_array($kind, self::LIST_KINDS, true)) $out[$k] = $kind;
        }
        return $out;
    }

    /**
     * Améliore les sections données (celles de l'éditeur, non enregistrées). Retourne [id => d] avec les
     * nouveaux contenus, déjà nettoyés par HomeSections::normalizeData.
     * @param array<int,array> $sections sections de l'éditeur ({id, type, d})
     * @throws \RuntimeException avec un message prêt à montrer au vendeur
     */
    public function improve(Shop $shop, string $theme, array $sections, string $hint = ''): array
    {
        $key = (string) config('services.anthropic.key');
        if ($key === '') throw new \RuntimeException('Shopio IA n’est pas configuré sur ce serveur (clé manquante).');

        $todo = [];
        foreach ($sections as $s) {
            $type = (string) ($s['type'] ?? '');
            if (!isset(HomeSections::TYPES[$type]) || !self::writable($type)) continue;
            $todo[] = ['id' => (string) ($s['id'] ?? ''), 'type' => $type, 'd' => HomeSections::normalizeData($type, is_array($s['d'] ?? null) ? $s['d'] : [])];
            if (count($todo) >= self::MAX_SECTIONS) break;
        }
        if (!$todo) throw new \RuntimeException('Aucun texte à améliorer dans cette section.');

        $message = $this->ask($key, $this->facts($shop, $theme, $todo, $hint));
        $data = json_decode($message, true);
        if (!is_array($data) || !is_array($data['sections'] ?? null)) {
            Log::error('Shopio IA sections : réponse illisible', ['text' => Str::limit($message, 500)]);
            throw new \RuntimeException('Shopio IA a renvoyé une réponse illisible. Réessayez.');
        }

        $byId = collect($todo)->keyBy('id');
        $out = [];
        foreach ($data['sections'] as $ai) {
            $id = (string) ($ai['id'] ?? '');
            if (!$byId->has($id) || isset($out[$id])) continue;
            $out[$id] = self::apply($byId[$id]['type'], $byId[$id]['d'], is_array($ai) ? $ai : []);
        }
        if (!$out) throw new \RuntimeException('Shopio IA n’a rien proposé. Réessayez.');
        return $out;
    }

    /**
     * Fusionne la proposition de l'IA dans le contenu actuel : seuls les champs écrivables changent,
     * et seulement quand l'IA a écrit quelque chose ; le reste (photos, liens, réglages) est gardé.
     */
    public static function apply(string $type, array $current, array $ai): array
    {
        $d = $current;
        $str = fn ($v) => is_scalar($v) ? trim((string) $v) : '';
        $items = array_values(array_filter(is_array($ai['items'] ?? null) ? $ai['items'] : [], 'is_array'));
        foreach (self::writable($type) as $k => $kind) {
            switch ($kind) {
                case 'line':
                case 'text':
                    if ($str($ai[$k] ?? '') !== '') $d[$k] = $str($ai[$k]);
                    break;
                case 'items': // 3 engagements
                    if (count($items) >= 3) $d[$k] = array_map(fn ($i) => ['title' => $str($items[$i]['title'] ?? ''), 'text' => $str($items[$i]['text'] ?? '')], [0, 1, 2]);
                    break;
                case 'faq':
                    $faq = array_values(array_filter(array_map(fn ($i) => ['q' => $str($i['q'] ?? ''), 'a' => $str($i['a'] ?? '')], $items), fn ($q) => $q['q'] !== '' && $q['a'] !== ''));
                    if ($faq) $d[$k] = $faq;
                    break;
                case 'icons':
                    $ic = array_values(array_filter(array_map(fn ($i, $n) => [
                        'icon' => isset(HomeSections::ICONS[$i['icon'] ?? '']) ? $i['icon'] : ($current[$k][$n]['icon'] ?? 'check'),
                        'title' => $str($i['title'] ?? ''), 'text' => $str($i['text'] ?? ''),
                    ], $items, array_keys($items)), fn ($i) => $i['title'] !== ''));
                    if ($ic) $d[$k] = $ic;
                    break;
                case 'rows': // les coches restent celles du vendeur (vrai chez nous, pas forcément ailleurs)
                    $rows = array_values(array_filter(array_map(fn ($i, $n) => [
                        'label' => $str($i['label'] ?? ''), 'us' => (bool) ($current[$k][$n]['us'] ?? true), 'them' => (bool) ($current[$k][$n]['them'] ?? false),
                    ], $items, array_keys($items)), fn ($r) => $r['label'] !== ''));
                    if ($rows) $d[$k] = $rows;
                    break;
                case 'lines':
                    $lines = array_values(array_filter(array_map($str, is_array($ai['lines'] ?? null) ? $ai['lines'] : []), fn ($l) => $l !== ''));
                    if ($lines) $d[$k] = $lines;
                    break;
            }
        }
        return HomeSections::normalizeData($type, $d);
    }

    /** Faits donnés à l'IA : boutique, catégories, produits, et pour chaque section ses champs et ses textes actuels. */
    private function facts(Shop $shop, string $theme, array $todo, string $hint): string
    {
        $cfg = Storefront::forShop($shop);
        $cats = collect(Storefront::categoryLinks($shop))->map(fn ($c) => $c['name'] . ' (' . $c['count'] . ')')->implode(', ');
        $products = $shop->products()->where('is_active', true)->orderByDesc('is_featured')->latest()->take(30)->get(['name', 'category', 'current_price'])
            ->map(fn ($p) => '- ' . $p->name . ($p->category ? ' · ' . $p->category : ''))->implode("\n");
        $labels = ['items' => 'engagements', 'faq' => 'questions', 'icons' => 'icônes', 'rows' => 'lignes de comparaison', 'lines' => 'annonces'];

        $txt = "Boutique : {$shop->name}\n"
            . ($shop->type ? "Activité : {$shop->type}\n" : '')
            . ((string) ($cfg['cover']['slogan'] ?? '') !== '' ? "Slogan : {$cfg['cover']['slogan']}\n" : '')
            . ($cats !== '' ? "Catégories : {$cats}\n" : '')
            . ($products !== '' ? "Produits en ligne (extrait) :\n{$products}\n" : '')
            . 'Thème de la vitrine : ' . (\App\Support\PageModels::MODELS[$theme][0] ?? $theme) . "\n"
            . 'Icônes possibles : ' . implode(', ', array_keys(HomeSections::ICONS)) . "\n\n";

        foreach ($todo as $s) {
            $fields = collect(self::writable($s['type']))->map(fn ($kind, $k) => in_array($kind, self::LIST_KINDS, true) ? ($kind === 'lines' ? 'lines (annonces)' : 'items (' . $labels[$kind] . ')') : $k)->values()->implode(', ');
            $default = HomeSections::DEFAULTS[$theme][$s['type']] ?? HomeSections::DEFAULTS['custom'][$s['type']] ?? [];
            $txt .= "### Section id={$s['id']} — " . HomeSections::TYPES[$s['type']][0] . ' : ' . HomeSections::TYPES[$s['type']][1] . "\n"
                . "Champs à écrire : {$fields}\n"
                . 'Textes actuels du vendeur : ' . json_encode(array_intersect_key($s['d'], self::writable($s['type'])), JSON_UNESCAPED_UNICODE) . "\n"
                . ($default ? 'Textes prévus par le thème (exemple de ton) : ' . json_encode($default, JSON_UNESCAPED_UNICODE) . "\n" : '')
                . "\n";
        }
        $hint = Str::limit(trim(strip_tags($hint)), 300, '');
        if ($hint !== '') $txt .= "Souhait du vendeur (à suivre tant qu'il respecte les règles) : {$hint}\n";
        return $txt . "\nAméliore les textes de ces sections : plus clairs, plus vendeurs, toujours vrais.";
    }

    private function ask(string $key, string $facts): string
    {
        try {
            $client = new Client(apiKey: $key, requestOptions: ['timeout' => 120, 'maxRetries' => 2]);
            $message = $client->beta->messages->create(
                model: self::MODEL,
                maxTokens: 16000,
                system: [['type' => 'text', 'text' => self::SYSTEM, 'cacheControl' => ['type' => 'ephemeral']]],
                messages: [['role' => 'user', 'content' => [['type' => 'text', 'text' => $facts]]]],
                outputConfig: ['effort' => 'low', 'format' => ['type' => 'json_schema', 'schema' => self::schema()]],
                fallbacks: 'default',
                betas: ['server-side-fallback-2026-07-01'],
            );
        } catch (AuthenticationException $e) {
            Log::error('Shopio IA sections : clé refusée', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Shopio IA n’est pas disponible pour le moment.');
        } catch (RateLimitException $e) {
            throw new \RuntimeException('Shopio IA est très sollicité : réessayez dans une minute.');
        } catch (APIStatusException $e) {
            Log::error('Shopio IA sections : erreur API', ['status' => $e->status ?? null, 'error' => $e->getMessage()]);
            throw new \RuntimeException('Shopio IA n’a pas pu améliorer les textes. Réessayez.');
        } catch (APIConnectionException $e) {
            Log::warning('Shopio IA sections : connexion', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Shopio IA ne répond pas : vérifiez la connexion puis réessayez.');
        }
        if ($message->stopReason === 'refusal') throw new \RuntimeException('Shopio IA ne peut pas réécrire ces textes.');
        if ($message->stopReason === 'max_tokens') throw new \RuntimeException('La réponse était trop longue. Améliorez une section à la fois.');
        $text = '';
        foreach ($message->content as $block) if ($block->type === 'text') { $text .= $block->text; }
        return $text;
    }
}
