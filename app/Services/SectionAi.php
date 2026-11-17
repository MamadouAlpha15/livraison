<?php

namespace App\Services;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\RateLimitException;
use App\Models\Product;
use App\Models\Shop;
use App\Support\HomeSections;
use App\Support\Storefront;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Shopio IA — rédige le contenu des sections d'une page (sections ajoutées de la bibliothèque, et textes des sections
 * du thème) : page produit (éditeur produit) ou page d'accueil Express / Signature (Boutique → Sections).
 *
 * Générique : les champs que l'IA remplit sont lus dans HomeSections::FIELDS. Une nouvelle sorte de section ajoutée
 * à la bibliothèque est donc prise en charge sans rien changer ici, dès que ses champs sont des textes.
 * L'IA n'écrit que des textes : jamais de photo, lien, vidéo, code promo, moyen de paiement, chiffre ni avis client
 * (les sections d'avis ne reçoivent que leur titre). Rien n'est enregistré : le vendeur relit, puis sauvegarde.
 */
class SectionAi
{
    /** Sortes de champs que l'IA sait remplir (les autres restent tels que le vendeur les a réglés). */
    private const KINDS = ['line', 'text', 'items', 'faq', 'icons', 'rows', 'lines', 'slides'];

    /** Champs jamais écrits par l'IA : colonne « vous » du tableau (nom de la boutique par défaut). */
    private const SKIP = ['compare' => ['us']];

    private const SYSTEM = <<<'TXT'
Tu es Shopio IA. Tu rédiges les textes des sections d'une page de boutique en ligne en Guinée, sur Shopio.
Les clients commandent en une minute (nom, téléphone, commune), la boutique les appelle pour confirmer,
puis ils paient en espèces au livreur à la réception. Ils peuvent suivre leur commande sur Shopio.

Écris en français simple, chaleureux et concret, pour des acheteurs qui lisent sur téléphone :
phrases courtes, mots de tous les jours, vouvoiement, pas de jargon, pas d'emojis.

Règles strictes — la page doit rester honnête :
- Décris uniquement ce que montrent les informations données (produit, boutique, catégories, photo).
  Une caractéristique inconnue (matière, taille, poids, origine…) ne s'invente pas : reste général.
- N'invente jamais de chiffre (clients, avis, pourcentages, ventes, années d'expérience), de témoignage, de délai
  de livraison, de prix, de remise, de code promo, de garantie, de retour gratuit ni de label. N'écris aucun prix.
- Aucune promesse de santé ou de résultat médical.
- Tableau de comparaison (rows) : seulement des points vrais pour toute commande Shopio (paiement à la livraison,
  appel de confirmation avant l'envoi, suivi de la commande sur Shopio) ou visibles sur le produit lui-même.
- Questions (faq) : des questions qu'un acheteur se pose vraiment, avec des réponses courtes et vraies.
  Paiement : « à la livraison, en espèces, au livreur ». Délai : « la boutique vous appelle pour le confirmer ».
- Icônes (icons) : choisis l'icône la plus juste parmi celles proposées.

Pour chaque section, respecte sa description, son rôle dans la page et la longueur maximale de chaque champ.
Si la section a déjà un contenu, améliore-le en gardant son idée, sauf si le vendeur demande autre chose.
Évite de répéter les mêmes phrases d'une section à l'autre.
TXT;

    /** Champs que l'IA remplit pour une sorte de section : [[clé, sorte, libellé, max], …] (vide = aucun). */
    public static function writableFields(string $type): array
    {
        return array_values(array_filter(HomeSections::FIELDS[$type] ?? [],
            fn ($f) => in_array($f[1], self::KINDS, true) && !in_array($f[0], self::SKIP[$type] ?? [], true)));
    }

    /** Sortes de sections que l'IA sait remplir (au moins un champ de texte). */
    public static function writableTypes(): array
    {
        return array_values(array_filter(array_keys(HomeSections::FIELDS), fn ($t) => self::writableFields($t) !== []));
    }

    /**
     * Rédige les sections données. $page : 'product', 'express' ou 'signature'.
     * $sections : [['id' => …, 'type' => …, 'd' => [...]], …] (contenu actuel).
     * Retourne [id => contenu complet de la section, nettoyé (HomeSections::normalizeData)], pour les sections rédigées.
     * @throws \RuntimeException avec un message prêt à montrer au vendeur
     */
    public function write(Shop $shop, string $page, array $sections, ?Product $product = null, string $instructions = ''): array
    {
        $key = (string) config('services.anthropic.key');
        if ($key === '') throw new \RuntimeException('Shopio IA n’est pas configuré sur ce serveur (clé manquante).');

        // Sections que l'IA sait remplir (les diaporamas sans photo n'ont rien à écrire)
        $todo = [];
        foreach ($sections as $s) {
            $type = (string) ($s['type'] ?? ''); $id = (string) ($s['id'] ?? '');
            if ($id === '' || !preg_match('/^[a-z0-9_]{1,24}$/', $id) || isset($todo[$id])) continue;
            $fields = array_values(array_filter(self::writableFields($type), fn ($f) => $f[1] !== 'slides' || count((array) ($s['d'][$f[0]] ?? [])) > 0));
            if (!$fields) continue;
            $todo[$id] = ['type' => $type, 'd' => HomeSections::normalizeData($type, is_array($s['d'] ?? null) ? $s['d'] : []), 'fields' => $fields];
            if (count($todo) >= 25) break;
        }
        if (!$todo) throw new \RuntimeException('Aucune de ces sections n’a de texte à rédiger.');

        $content = $product ? $this->image($product) : [];
        $content[] = ['type' => 'text', 'text' => $this->facts($shop, $page, $product, $todo, Str::limit(trim(strip_tags($instructions)), 500, ''))];

        try {
            $client = new Client(apiKey: $key, requestOptions: ['timeout' => 120, 'maxRetries' => 2]);
            $message = $client->beta->messages->create(
                model: SalesPageAi::MODEL,
                maxTokens: 16000,
                system: [['type' => 'text', 'text' => self::SYSTEM, 'cacheControl' => ['type' => 'ephemeral']]],
                messages: [['role' => 'user', 'content' => $content]],
                outputConfig: ['effort' => 'medium', 'format' => ['type' => 'json_schema', 'schema' => $this->schema($todo)]],
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
            throw new \RuntimeException('Shopio IA n’a pas pu rédiger les sections. Réessayez.');
        } catch (APIConnectionException $e) {
            Log::warning('Shopio IA sections : connexion', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Shopio IA ne répond pas : vérifiez la connexion puis réessayez.');
        }

        if ($message->stopReason === 'refusal') throw new \RuntimeException('Shopio IA ne peut pas rédiger ces sections.');
        if ($message->stopReason === 'max_tokens') throw new \RuntimeException('Le texte demandé était trop long. Réessayez avec moins de sections.');
        $text = '';
        foreach ($message->content as $block) if ($block->type === 'text') $text .= $block->text;
        $data = json_decode($text, true);
        if (!is_array($data) || !is_array($data['sections'] ?? null)) {
            Log::error('Shopio IA sections : réponse illisible', ['text' => Str::limit($text, 500)]);
            throw new \RuntimeException('Shopio IA a renvoyé une réponse illisible. Réessayez.');
        }

        $out = [];
        foreach ($todo as $id => $t) {
            if (is_array($data['sections'][$id] ?? null)) $out[$id] = self::merge($t['type'], $t['d'], $data['sections'][$id], $t['fields']);
        }
        return $out;
    }

    /** Contexte donné à l'IA : boutique, page, produit, sections à rédiger avec leurs champs et leur contenu actuel. */
    private function facts(Shop $shop, string $page, ?Product $product, array $todo, string $instructions): string
    {
        $cats = collect(Storefront::categoryLinks($shop))->pluck('name')->take(12)->implode(', ');
        $lines = ["Boutique : {$shop->name}" . ($shop->type ? " ({$shop->type})" : '')];
        if (trim((string) $shop->description) !== '') $lines[] = 'Présentation de la boutique : ' . Str::limit(trim(strip_tags($shop->description)), 600);
        if ($cats !== '') $lines[] = 'Catégories de la boutique : ' . $cats;
        if ($product) {
            $l = is_array($product->landing) ? $product->landing : [];
            $lines[] = "Page : page de vente du produit « {$product->name} »" . ($product->category ? " (catégorie {$product->category})" : '');
            $lines[] = 'Description du vendeur : ' . (Str::limit(trim(strip_tags((string) $product->description)), 1500) ?: '(aucune)');
            if (!empty($l['hook'])) $lines[] = 'Accroche déjà écrite sur la page : ' . $l['hook'];
            if (!empty($l['benefits'])) $lines[] = 'Avantages déjà écrits : ' . implode(' ; ', array_slice((array) $l['benefits'], 0, 6));
        } else {
            $names = $shop->products()->where('is_active', true)->orderByDesc('is_featured')->latest()->take(12)->pluck('name')->implode(', ');
            $lines[] = 'Page : page d’accueil de la boutique (thème ' . ucfirst($page) . ')';
            if ($names !== '') $lines[] = 'Quelques produits : ' . $names;
        }
        if ($instructions !== '') $lines[] = "\nConsigne du vendeur (à suivre tant qu'elle respecte les règles) : {$instructions}";

        $icons = implode(', ', array_map(fn ($k, $v) => "{$k} ({$v[0]})", array_keys(HomeSections::ICONS), HomeSections::ICONS));
        $desc = [];
        foreach ($todo as $id => $t) {
            [$name, $role] = HomeSections::TYPES[$t['type']] ?? [$t['type'], ''];
            $fields = array_map(function ($f) use ($t) {
                [$k, $kind, $label, $max] = $f + [3 => 0];
                return "  - {$k} : {$label}" . match ($kind) {
                    'line', 'text' => " (texte, {$max} caractères max)",
                    'items' => ' (exactement 3 engagements : title ≤ 40, text ≤ 80 caractères)',
                    'faq' => ' (4 à 6 questions : q ≤ 150, a ≤ 600 caractères)',
                    'icons' => ' (3 ou 4 icônes : icon, title ≤ 40, text ≤ 80 caractères)',
                    'rows' => ' (4 à 6 lignes : label ≤ 60 caractères)',
                    'lines' => ' (3 à 5 messages courts, ≤ 80 caractères)',
                    'slides' => ' (exactement ' . count($t['d'][$k] ?? []) . ' diapositives, dans l’ordre des photos : title ≤ 80, text ≤ 160, button ≤ 30 caractères ; vide si inutile)',
                    default => '',
                };
            }, $t['fields']);
            $current = array_intersect_key($t['d'], array_flip(array_column($t['fields'], 0)));
            $desc[] = "Section « {$id} » — {$name} : {$role}\n" . implode("\n", $fields) . "\n  Contenu actuel : " . json_encode($current, JSON_UNESCAPED_UNICODE);
        }
        return implode("\n", $lines) . "\n\nIcônes possibles : {$icons}\n\nRédige ces sections :\n\n" . implode("\n\n", $desc);
    }

    /** Schéma de la réponse : { sections: { id: { champ: … } } }, un objet par section, ses champs exacts. */
    private function schema(array $todo): array
    {
        $str = ['type' => 'string'];
        $obj = fn (array $props) => ['type' => 'object', 'properties' => $props, 'required' => array_keys($props), 'additionalProperties' => false];
        $arr = fn (array $item) => ['type' => 'array', 'items' => $item];
        $sections = [];
        foreach ($todo as $id => $t) {
            $props = [];
            foreach ($t['fields'] as [$k, $kind]) {
                $props[$k] = match ($kind) {
                    'line', 'text' => $str,
                    'items'  => $arr($obj(['title' => $str, 'text' => $str])),
                    'faq'    => $arr($obj(['q' => $str, 'a' => $str])),
                    'icons'  => $arr($obj(['icon' => ['type' => 'string', 'enum' => array_keys(HomeSections::ICONS)], 'title' => $str, 'text' => $str])),
                    'rows'   => $arr($obj(['label' => $str])),
                    'lines'  => $arr($str),
                    'slides' => $arr($obj(['title' => $str, 'text' => $str, 'button' => $str])),
                };
            }
            $sections[$id] = $obj($props);
        }
        return $obj(['sections' => $obj($sections)]);
    }

    /** Contenu final : textes de l'IA posés sur la section actuelle (photos, liens, réglages gardés), puis nettoyés. */
    public static function merge(string $type, array $current, array $ai, array $fields): array
    {
        $d = $current;
        foreach ($fields as [$k, $kind]) {
            if (!array_key_exists($k, $ai)) continue;
            $v = $ai[$k];
            $d[$k] = match ($kind) {
                'line', 'text' => is_scalar($v) ? (string) $v : ($d[$k] ?? ''),
                'items' => array_map(fn ($i) => ['title' => (string) (($v[$i] ?? [])['title'] ?? ''), 'text' => (string) (($v[$i] ?? [])['text'] ?? '')], [0, 1, 2]),
                // Lignes de comparaison : « vous » oui, « les autres » non (le vendeur ajuste ensuite)
                'rows' => array_map(fn ($r) => ['label' => (string) ($r['label'] ?? ''), 'us' => true, 'them' => false], is_array($v) ? $v : []),
                // Diapositives : textes posés sur les photos existantes, dans l'ordre
                'slides' => array_map(fn ($sl, $n) => array_merge($sl, array_intersect_key(is_array($v[$n] ?? null) ? $v[$n] : [], array_flip(['title', 'text', 'button']))), $d[$k] ?? [], array_keys($d[$k] ?? [])),
                default => is_array($v) ? $v : ($d[$k] ?? []),
            };
        }
        return HomeSections::normalizeData($type, $d);
    }

    /** Photo principale du produit (≤ 4 Mo), pour des textes fidèles à ce que l'on voit. */
    private function image(Product $product): array
    {
        try {
            $disk = Storage::disk('public');
            $path = $product->image;
            if (!is_string($path) || !$disk->exists($path) || $disk->size($path) > 4 * 1024 * 1024) return [];
            $mime = $disk->mimeType($path);
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) return [];
            return [['type' => 'image', 'source' => ['type' => 'base64', 'mediaType' => $mime, 'data' => base64_encode($disk->get($path))]]];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
