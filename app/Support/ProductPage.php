<?php

namespace App\Support;

use App\Models\Product;
use App\Models\Shop;
use App\Services\ImageOptimizer;
use Illuminate\Support\Str;

/**
 * Page produit longue (modèle « Express ») : adresse publique, photos, contenu, packs.
 *
 * Contenu (colonne products.landing, JSON) — rempli par le vendeur ou par Shopio IA, tout est facultatif :
 *   hook       Accroche sous le titre (220 car.)
 *   benefits   Avantages courts, une ligne chacun (8 max)
 *   promise    Promesse de délai réelle de la boutique, ex. « Commandez avant 18 h, livré aujourd'hui à Conakry »
 *   packs      [{qty: 2, price: 450000, label: "Populaire"}, …] (3 max). Prix TOTAL du pack, forcément
 *              moins cher que qty × prix unitaire. Appliqués aussi par le serveur à la commande (lineTotal).
 *   usage, details, shipping   Textes des volets « Comment l'utiliser ? », « Description détaillée », « Livraison et garantie »
 *   highlight  {title, text, cta}                        Bloc mis en avant avec photos inclinées
 *   story      {title, kicker, text}                     Photo + texte (ex. « Avant / Après »)
 *   audience   {title, kicker, text}                     « C'est pour qui ? »
 *   love       {title, kicker, items: [{title, text}]}   « Pourquoi vous allez l'adorer » (6 max)
 *   compare    {title, rows: ["Qualité garantie", …]}    Tableau « Notre produit / Autres » (8 max)
 *   faq        [{q, a}]                                  Questions sur le produit (10 max)
 *
 * Les avis et les chiffres (« clients satisfaits »…) ne viennent jamais de ce contenu : ils sont calculés
 * sur les vrais avis et les vraies commandes de la boutique.
 */
class ProductPage
{
    public const PACKS_MAX = 3;

    /** Paramètre d'adresse « 12-sac-a-dos-urbain » : identifiant puis nom lisible. */
    public static function param(Product $p): string
    {
        $slug = Str::limit(Str::slug($p->name), 60, '');
        return $p->id . ($slug !== '' ? '-' . rtrim($slug, '-') : '');
    }

    public static function idFrom(string $param): int
    {
        return preg_match('/^(\d+)/', $param, $m) ? (int) $m[1] : 0;
    }

    public static function url(Shop $shop, Product $p): string
    {
        return $shop->slug
            ? route('public.shops.slug.product', [$shop->slug, self::param($p)])
            : route('public.shops.product', [$shop, self::param($p)]);
    }

    /** Photo principale puis galerie, en taille moyenne. */
    public static function photos(Product $p): array
    {
        $paths = array_values(array_filter(array_merge([$p->image], $p->gallery_array), fn ($x) => is_string($x) && $x !== ''));
        return array_values(array_unique(array_map(fn ($x) => ImageOptimizer::url($x, 'medium') ?? asset('storage/' . $x), $paths)));
    }

    private static function txt($v, int $max): string
    {
        return Str::limit(trim(preg_replace('/[ \t]+/', ' ', strip_tags(mb_scrub((string) $v, 'UTF-8')))), $max, '…');
    }

    private static function block($v, array $keys): array
    {
        $v = is_array($v) ? $v : [];
        $out = [];
        foreach ($keys as $k => $max) $out[$k] = self::txt($v[$k] ?? '', $max);
        return $out;
    }

    /** Packs valides pour ce produit : quantité 2 à 10, prix total plus bas que l'achat à l'unité. */
    public static function packs(Product $p): array
    {
        $unit = (int) round($p->current_price);
        $raw = is_array($p->landing['packs'] ?? null) ? $p->landing['packs'] : [];
        $packs = [];
        foreach ($raw as $k) {
            $qty = (int) ($k['qty'] ?? 0);
            $price = (int) round((float) ($k['price'] ?? 0));
            if ($qty < 2 || $qty > 10 || $price <= 0 || $price >= $qty * $unit || isset($packs[$qty])) continue;
            $packs[$qty] = ['qty' => $qty, 'price' => $price, 'label' => self::txt($k['label'] ?? '', 20)];
        }
        ksort($packs);
        return array_slice(array_values($packs), 0, self::PACKS_MAX);
    }

    /**
     * Prix d'une ligne de commande : la combinaison la moins chère de packs et d'unités.
     * Même calcul côté navigateur (_kit, page produit) pour que le total affiché soit celui payé.
     */
    public static function lineTotal(Product $p, int $qty, ?array $packs = null): int
    {
        $unit = (int) round($p->current_price);
        $packs ??= self::packs($p);
        if (!$packs || $qty < 2) return $unit * max(0, $qty);
        $best = [0];
        for ($q = 1; $q <= $qty; $q++) {
            $best[$q] = $best[$q - 1] + $unit;
            foreach ($packs as $k) if ($k['qty'] <= $q) $best[$q] = min($best[$q], $best[$q - $k['qty']] + $k['price']);
        }
        return $best[$qty];
    }

    /** Contenu nettoyé, prêt pour la vue. Repli sur la description du produit quand rien n'est rempli. */
    public static function content(Product $p): array
    {
        $l = is_array($p->landing) ? $p->landing : [];
        $desc = trim(strip_tags((string) $p->description));
        $list = fn ($v, int $n, int $max) => array_values(array_filter(array_map(fn ($x) => self::txt($x, $max), array_slice(is_array($v) ? $v : [], 0, $n)), fn ($x) => $x !== ''));

        $love = self::block($l['love'] ?? [], ['title' => 80, 'kicker' => 120]);
        $love['items'] = array_values(array_filter(array_map(fn ($i) => self::block($i, ['title' => 60, 'text' => 160]), array_slice(is_array($l['love']['items'] ?? null) ? $l['love']['items'] : [], 0, 6)), fn ($i) => $i['title'] !== ''));
        $compare = self::block($l['compare'] ?? [], ['title' => 80]);
        $compare['rows'] = $list($l['compare']['rows'] ?? [], 8, 60);
        $faq = array_values(array_filter(array_map(fn ($f) => self::block($f, ['q' => 140, 'a' => 600]), array_slice(is_array($l['faq'] ?? null) ? $l['faq'] : [], 0, 10)), fn ($f) => $f['q'] !== '' && $f['a'] !== ''));

        return [
            'hook'      => self::txt(($l['hook'] ?? '') !== '' ? $l['hook'] : Str::before($desc, "\n"), 220),
            'benefits'  => $list($l['benefits'] ?? [], 8, 90),
            'promise'   => self::txt($l['promise'] ?? '', 90),
            'usage'     => self::txt($l['usage'] ?? '', 1500),
            'details'   => self::txt(($l['details'] ?? '') !== '' ? $l['details'] : $desc, 3000),
            'shipping'  => self::txt($l['shipping'] ?? '', 1500),
            'highlight' => self::block($l['highlight'] ?? [], ['title' => 70, 'text' => 600, 'cta' => 24]),
            'story'     => self::block($l['story'] ?? [], ['title' => 80, 'kicker' => 140, 'text' => 1200]),
            'audience'  => self::block($l['audience'] ?? [], ['title' => 60, 'kicker' => 80, 'text' => 1200]),
            'love'      => $love,
            'compare'   => $compare,
            'faq'       => $faq,
        ];
    }
}
