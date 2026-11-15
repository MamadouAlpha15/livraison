<?php

namespace App\Services;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\RateLimitException;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Shopio IA — rédige la page de vente d'un produit (format products.landing, voir App\Support\ProductPage).
 *
 * Le vendeur relit toujours avant d'enregistrer : le résultat remplit le formulaire « Page de vente »,
 * rien n'est publié directement. L'IA n'invente ni délai de livraison, ni chiffre, ni avis, ni garantie :
 * ces éléments viennent des vraies données de la boutique. Les prix des packs ne sont pas décidés par
 * l'IA : une suggestion (−10 % / −20 %) est calculée ici, et le vendeur la modifie à son gré.
 */
class SalesPageAi
{
    public const MODEL = 'claude-opus-5-5';

    /** Consignes fixes (identiques à chaque appel, pour profiter du cache). */
    private const SYSTEM = <<<'TXT'
Tu es Shopio IA. Tu rédiges la page de vente d'un produit pour une boutique en ligne en Guinée, sur Shopio.
Les clients commandent en une minute (nom, téléphone, commune), la boutique les appelle pour confirmer,
puis ils paient en espèces au livreur à la réception. Ils peuvent suivre leur commande sur Shopio.

Écris en français simple, chaleureux et concret, pour des acheteurs qui lisent sur téléphone :
phrases courtes, mots de tous les jours, tutoiement interdit (vouvoiement), pas de jargon, pas d'emojis.

Règles strictes — la page doit rester honnête :
- Décris uniquement ce que montrent le nom, la description, la catégorie et la photo du produit.
  Si une caractéristique n'est pas connue (matière, dimensions, poids, autonomie…), ne l'invente pas : reste général.
- N'invente jamais de chiffre (clients, avis, pourcentages, ventes), de témoignage, de délai de livraison,
  de prix, de remise, de garantie, de retour gratuit ni de label. N'écris aucun prix.
- Aucune promesse de santé ou de résultat médical.
- Le tableau comparatif ne liste que des points vrais pour toute commande Shopio (paiement à la livraison,
  appel de confirmation avant l'envoi, suivi de commande) ou visibles sur le produit lui-même.

Longueurs attendues :
- hook : 1 ou 2 phrases (moins de 200 caractères).
- benefits : 4 à 6 avantages, une ligne courte chacun (moins de 80 caractères).
- usage : 2 à 4 phrases. details : 3 à 6 phrases.
- highlight : titre de 3 à 5 mots, texte de 2 à 3 phrases, cta de 2 à 4 mots (ex. « Je le veux »).
- story : un avant / après ou un moment d'usage ; titre court, kicker d'une ligne, texte de 2 à 4 phrases.
- audience : titre « C'est pour qui ? », kicker d'une ligne, texte de 2 à 4 phrases.
- love : titre « Pourquoi vous allez l'adorer », kicker d'une ligne, 4 raisons (titre de 2 à 4 mots + une phrase).
- compare : titre « Pourquoi nous choisir ? », 4 à 5 points courts.
- faq : 4 à 5 questions qu'un acheteur se pose vraiment sur ce produit, avec des réponses courtes et vraies.
  Pour le paiement : « à la livraison, en espèces, au livreur ». Pour le délai : « la boutique vous appelle pour le confirmer ».
TXT;

    private static function schema(): array
    {
        $str = ['type' => 'string'];
        $obj = fn (array $props) => ['type' => 'object', 'properties' => $props, 'required' => array_keys($props), 'additionalProperties' => false];
        return $obj([
            'hook' => $str,
            'benefits' => ['type' => 'array', 'items' => $str],
            'usage' => $str,
            'details' => $str,
            'highlight' => $obj(['title' => $str, 'text' => $str, 'cta' => $str]),
            'story' => $obj(['title' => $str, 'kicker' => $str, 'text' => $str]),
            'audience' => $obj(['title' => $str, 'kicker' => $str, 'text' => $str]),
            'love' => $obj(['title' => $str, 'kicker' => $str, 'items' => ['type' => 'array', 'items' => $obj(['title' => $str, 'text' => $str])]]),
            'compare' => $obj(['title' => $str, 'rows' => ['type' => 'array', 'items' => $str]]),
            'faq' => ['type' => 'array', 'items' => $obj(['q' => $str, 'a' => $str])],
        ]);
    }

    /**
     * Rédige le contenu. Retourne ['landing' => [...], 'packs' => [...suggestion]].
     * @throws \RuntimeException avec un message prêt à montrer au vendeur
     */
    public function generate(Product $product, Shop $shop): array
    {
        $key = (string) config('services.anthropic.key');
        if ($key === '') throw new \RuntimeException('Shopio IA n’est pas configuré sur ce serveur (clé manquante).');

        $unit = (int) round($product->current_price);
        $facts = "Boutique : {$shop->name}\n"
            . "Produit : {$product->name}\n"
            . ($product->category ? "Catégorie : {$product->category}\n" : '')
            . 'Prix à l’unité : ' . number_format($unit, 0, ',', ' ') . ' ' . ($shop->currency ?? 'GNF') . " (ne l'écris pas)\n"
            . 'Description du vendeur : ' . (trim(strip_tags((string) $product->description)) ?: '(aucune)') . "\n\n"
            . 'Rédige la page de vente de ce produit.';

        $content = [];
        if ($img = $this->image($product)) $content[] = $img;
        $content[] = ['type' => 'text', 'text' => $facts];

        try {
            $client = new Client(apiKey: $key, requestOptions: ['timeout' => 120, 'maxRetries' => 2]);
            $message = $client->beta->messages->create(
                model: self::MODEL,
                maxTokens: 16000,
                system: [['type' => 'text', 'text' => self::SYSTEM, 'cacheControl' => ['type' => 'ephemeral']]],
                messages: [['role' => 'user', 'content' => $content]],
                outputConfig: ['effort' => 'medium', 'format' => ['type' => 'json_schema', 'schema' => self::schema()]],
                // Si le modèle refuse pour une raison de sécurité, l'API réessaie seule sur le modèle de repli prévu
                fallbacks: 'default',
                betas: ['server-side-fallback-2026-07-01'],
            );
        } catch (AuthenticationException $e) {
            Log::error('Shopio IA page de vente : clé refusée', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Shopio IA n’est pas disponible pour le moment.');
        } catch (RateLimitException $e) {
            throw new \RuntimeException('Shopio IA est très sollicité : réessayez dans une minute.');
        } catch (APIStatusException $e) {
            Log::error('Shopio IA page de vente : erreur API', ['status' => $e->status ?? null, 'error' => $e->getMessage()]);
            throw new \RuntimeException('Shopio IA n’a pas pu rédiger la page. Réessayez.');
        } catch (APIConnectionException $e) {
            Log::warning('Shopio IA page de vente : connexion', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Shopio IA ne répond pas : vérifiez la connexion puis réessayez.');
        }

        if ($message->stopReason === 'refusal') throw new \RuntimeException('Shopio IA ne peut pas rédiger de page pour ce produit.');
        if ($message->stopReason === 'max_tokens') throw new \RuntimeException('La page générée était trop longue. Réessayez.');
        $text = '';
        foreach ($message->content as $block) if ($block->type === 'text') { $text .= $block->text; }
        $data = json_decode($text, true);
        if (!is_array($data)) {
            Log::error('Shopio IA page de vente : réponse illisible', ['text' => Str::limit($text, 500)]);
            throw new \RuntimeException('Shopio IA a renvoyé une réponse illisible. Réessayez.');
        }

        return ['landing' => $this->clean($data), 'packs' => self::suggestPacks($unit)];
    }

    /** Photo principale en pièce jointe, si elle est stockée localement et raisonnable (≤ 4 Mo). */
    private function image(Product $product): ?array
    {
        $path = $product->image;
        if (!$path) return null;
        try {
            $disk = Storage::disk('public');
            if (!$disk->exists($path) || $disk->size($path) > 4 * 1024 * 1024) return null;
            $mime = $disk->mimeType($path);
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) return null;
            return ['type' => 'image', 'source' => ['type' => 'base64', 'mediaType' => $mime, 'data' => base64_encode($disk->get($path))]];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Limites du formulaire (mêmes que ProductPage / ProductPageController). */
    private function clean(array $d): array
    {
        $s = fn ($v, int $max) => Str::limit(trim(strip_tags(is_scalar($v) ? (string) $v : '')), $max, '');
        $arr = fn ($v) => is_array($v) ? $v : [];
        $list = fn ($v, int $n, int $max) => array_values(array_filter(array_map(fn ($x) => $s($x, $max), array_slice($arr($v), 0, $n)), fn ($x) => $x !== ''));
        $blk = function ($v, array $keys) use ($s, $arr) {
            $v = $arr($v); $out = [];
            foreach ($keys as $k => $max) $out[$k] = $s($v[$k] ?? '', $max);
            return $out;
        };
        $love = $blk($d['love'] ?? [], ['title' => 80, 'kicker' => 120]);
        $love['items'] = array_values(array_filter(array_map(fn ($i) => $blk($i, ['title' => 60, 'text' => 160]), array_slice($arr($d['love']['items'] ?? null), 0, 6)), fn ($i) => $i['title'] !== ''));
        $compare = $blk($d['compare'] ?? [], ['title' => 80]);
        $compare['rows'] = $list($d['compare']['rows'] ?? [], 8, 60);
        return [
            'hook' => $s($d['hook'] ?? '', 220),
            'benefits' => $list($d['benefits'] ?? [], 8, 90),
            'usage' => $s($d['usage'] ?? '', 1500),
            'details' => $s($d['details'] ?? '', 3000),
            'highlight' => $blk($d['highlight'] ?? [], ['title' => 70, 'text' => 600, 'cta' => 24]),
            'story' => $blk($d['story'] ?? [], ['title' => 80, 'kicker' => 140, 'text' => 1200]),
            'audience' => $blk($d['audience'] ?? [], ['title' => 60, 'kicker' => 80, 'text' => 1200]),
            'love' => $love,
            'compare' => $compare,
            'faq' => array_values(array_filter(array_map(fn ($f) => $blk($f, ['q' => 140, 'a' => 600]), array_slice($arr($d['faq'] ?? null), 0, 10)), fn ($f) => $f['q'] !== '' && $f['a'] !== '')),
        ];
    }

    /** Suggestion de packs (−10 % pour 2, −20 % pour 3), arrondie à 5 000 près, que le vendeur ajuste. */
    public static function suggestPacks(int $unit): array
    {
        if ($unit < 10000) return [];
        $round = fn ($v) => (int) (floor($v / 5000) * 5000);
        return [
            ['qty' => 2, 'price' => $round($unit * 2 * .9), 'label' => 'Populaire'],
            ['qty' => 3, 'price' => $round($unit * 3 * .8), 'label' => 'Meilleure offre'],
        ];
    }
}
