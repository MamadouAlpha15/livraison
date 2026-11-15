<?php

namespace App\PageBuilder\Ai;

use App\Models\Product;
use App\Models\Shop;
use App\PageBuilder\PageFactory;
use App\Support\Ai\AiUnavailable;
use App\Support\Ai\TextModel;
use Illuminate\Support\Str;

/**
 * Shopio IA écrit les textes d'une page construite à partir de la fiche produit : bannière, présentation et
 * points forts, questions fréquentes, référencement Google. L'IA ne reçoit que la fiche du produit et a
 * pour consigne de ne rien inventer. Le résultat va dans le brouillon (le vendeur relit, modifie, publie).
 * Les sections écrites par l'IA ont des identifiants fixes (ia_…) : relancer l'IA les remplace.
 */
class PageWriter
{
    /** Pages écrites par boutique et par jour */
    public const DAILY_LIMIT = 20;

    public const ANNOUNCEMENT = 'ia_annonce';
    public const STORY = 'ia_presentation';
    public const FAQ = 'ia_faq';

    public function __construct(private TextModel $model) {}

    /** @throws AiUnavailable */
    public function write(Product $product, Shop $shop): array
    {
        $raw = $this->model->complete(self::system(), [['role' => 'user', 'content' => self::brief($product, $shop)]], 1800);
        return self::parse($raw);
    }

    public static function system(): string
    {
        return <<<'TXT'
Tu écris les textes de la page de vente d'un produit sur Shopio, une place de marché guinéenne où le client paie à la livraison.
Tu tutoies le client, en français simple, avec des phrases courtes.

Règles absolues :
- N'invente aucun fait. Utilise seulement les informations fournies : nom, description, catégorie, prix, options.
- Pas de garantie, certification, origine, composition, dimension, durée, délai de livraison, nombre de clients, avis, note, promotion ou stock qui ne figure pas dans les informations.
- Pas de promesse de santé, de guérison ou de résultat garanti.
- Si la description est courte, reste général et court plutôt que d'inventer.
- Les seuls faits sur la commande que tu peux affirmer : le client paie en espèces à la livraison ; la boutique l'appelle ou lui écrit sur WhatsApp pour confirmer ; il peut suivre le livreur.

Réponds uniquement avec un objet JSON valide, sans aucun texte autour, de cette forme :
{
  "seo_title": "titre pour Google, 70 caractères au plus",
  "seo_description": "description pour Google, 160 caractères au plus",
  "announcement": "une ligne courte pour la bannière du haut, 90 caractères au plus",
  "headline": "titre accrocheur de la présentation, 80 caractères au plus",
  "intro": "2 ou 3 phrases de présentation",
  "benefits": ["3 à 5 points forts tirés de la description, 100 caractères au plus chacun"],
  "faq": [{"q": "question qu'un client se pose sur ce produit ou sa commande", "a": "réponse vraie selon les informations"}]
}
Entre 2 et 5 questions dans "faq".
TXT;
    }

    public static function brief(Product $product, Shop $shop): string
    {
        $variants = $product->activeVariants()->pluck('name')->filter()->take(20)->implode(', ');
        $original = (float) ($product->original_price ?? 0);
        $price = (float) $product->current_price;

        return implode("\n", array_filter([
            'Boutique : ' . $shop->name,
            'Produit : ' . $product->name,
            $product->category ? 'Catégorie : ' . $product->category : null,
            'Prix : ' . number_format($price, 0, ',', ' ') . ' GNF' . ($original > $price ? ' (au lieu de ' . number_format($original, 0, ',', ' ') . ' GNF)' : ''),
            $variants !== '' ? 'Options : ' . $variants : null,
            'Description du vendeur : ' . (Str::limit(trim(strip_tags((string) $product->description)), 3000) ?: '(aucune description)'),
        ]));
    }

    /** Lit la réponse JSON de l'IA et la remet aux bonnes longueurs. @throws AiUnavailable */
    public static function parse(string $raw): array
    {
        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');
        $json = $start !== false && $end > $start ? json_decode(substr($raw, $start, $end - $start + 1), true) : null;
        if (!is_array($json)) {
            throw new AiUnavailable('Shopio IA a répondu dans un format inattendu. Réessaie.');
        }

        $text = fn ($v, int $max) => Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) (is_scalar($v) ? $v : '')))), $max, '…');
        $copy = [
            'seo_title'       => $text($json['seo_title'] ?? '', 70),
            'seo_description' => $text($json['seo_description'] ?? '', 160),
            'announcement'    => $text($json['announcement'] ?? '', 100),
            'headline'        => $text($json['headline'] ?? '', 90),
            'intro'           => $text($json['intro'] ?? '', 600),
            'benefits'        => collect(is_array($json['benefits'] ?? null) ? $json['benefits'] : [])
                ->map(fn ($b) => $text($b, 110))->filter()->take(5)->values()->all(),
            'faq'             => collect(is_array($json['faq'] ?? null) ? $json['faq'] : [])
                ->map(fn ($f) => is_array($f) ? ['q' => $text($f['q'] ?? '', 150), 'a' => $text($f['a'] ?? '', 500)] : null)
                ->filter(fn ($f) => $f && $f['q'] !== '' && $f['a'] !== '')->take(5)->values()->all(),
        ];
        if ($copy['headline'] === '' || $copy['intro'] === '') {
            throw new AiUnavailable('Shopio IA a répondu de façon incomplète. Réessaie.');
        }
        return $copy;
    }

    /** Place les textes dans le brouillon : référencement, bannière en haut, présentation sous le produit, questions en plus */
    public static function apply(array $page, array $copy): array
    {
        $page['seo'] = array_filter(['title' => $copy['seo_title'] ?: null, 'description' => $copy['seo_description'] ?: null]);

        if ($copy['announcement'] !== '') {
            $section = $page['sections'][self::ANNOUNCEMENT] ?? PageFactory::section('announcement');
            $section['settings']['text'] = $copy['announcement'];
            $page = self::place($page, self::ANNOUNCEMENT, $section, 0);
        }

        $content = '<p>' . e($copy['intro']) . '</p>'
            . ($copy['benefits'] ? '<ul>' . implode('', array_map(fn ($b) => '<li>' . e($b) . '</li>', $copy['benefits'])) . '</ul>' : '');
        $story = $page['sections'][self::STORY] ?? PageFactory::section('rich-text');
        $story['settings'] = array_replace($story['settings'], ['heading' => $copy['headline'], 'content' => $content, 'align' => 'left']);
        $afterMain = array_search('main', $page['order'], true);
        $page = self::place($page, self::STORY, $story, $afterMain === false ? null : $afterMain + 1);

        if ($copy['faq']) {
            $faqId = collect($page['order'])->first(fn ($id) => ($page['sections'][$id]['type'] ?? null) === 'faq') ?? self::FAQ;
            $faq = $page['sections'][$faqId] ?? PageFactory::section('faq');
            $known = collect($faq['blocks'])->map(fn ($b) => mb_strtolower(trim((string) ($b['settings']['question'] ?? ''))))->all();
            foreach ($copy['faq'] as $f) {
                if (count($faq['block_order']) >= 20 || in_array(mb_strtolower($f['q']), $known, true)) {
                    continue;
                }
                $id = 'ia_q' . (count($faq['block_order']) + 1) . '_' . Str::lower(Str::random(4));
                $faq['blocks'][$id] = PageFactory::block('question', ['question' => $f['q'], 'answer' => '<p>' . e($f['a']) . '</p>']);
                $faq['block_order'][] = $id;
                $known[] = mb_strtolower($f['q']);
            }
            $page = self::place($page, $faqId, $faq, null);
        }

        return $page;
    }

    /** Met la section à sa place : déjà présente, elle reste où le vendeur l'a mise ; sinon à la position donnée (null : à la fin) */
    private static function place(array $page, string $id, array $section, ?int $position): array
    {
        $page['sections'][$id] = $section;
        if (!in_array($id, $page['order'], true)) {
            $order = $page['order'];
            array_splice($order, $position ?? count($order), 0, [$id]);
            $page['order'] = $order;
        }
        return $page;
    }
}
