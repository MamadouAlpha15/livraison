<?php

namespace App\Support;

use App\Models\Shop;

/**
 * Données du thème « Cabine » (cabine d'essayage), en plus de PageModels::data() :
 * - les produits avec leurs photos sans fond, motifs et tailles (comme Lookbook) ;
 * - les rayons de la cabine (au plus 4), rangés de haut en bas :
 *     · ceux choisis par le vendeur dans la section « Cabine d'essayage » (Boutique → Sections) ;
 *     · sinon, chaque produit est placé d'après son nom et sa catégorie (haut, bas, chaussures, accessoire) :
 *       avec au moins deux places de vêtements remplies, la cabine montre une « tenue » (silhouette) ;
 *     · sinon un « coffret » : les catégories côte à côte, ou 3 pièces d'un même rayon (ex. parfums).
 */
class Cabine
{
    public const MAX_SLOTS = 4;

    /** Place dans la silhouette (1 haut, 2 bas, 3 chaussures, 4 accessoire) d'après les mots du produit. */
    private const ZONES = [
        3 => '/chaussure|basket|sneaker|sandale|botte|bottine|talon|escarpin|mocassin|claquette|\bmules?\b|soulier|tennis/u',
        4 => '/\bsacs?\b|cabas|pochette|sacoche|accessoire|bijou|collier|bracelet|boucles? d|montre|lunette|ceinture|chapeau|casquette|foulard|[ée]charpe|portefeuille/u',
        2 => '/jupe|pantalon|\bjeans?\b|short|\bbas\b|legging|jogging|culotte|bermuda/u',
        1 => '/\bhauts?\b|\btops?\b|chemise|t-?shirt|\btee\b|blouse|robe|boubou|tunique|\bpull|veste|manteau|sweat|hoodie|cardigan|\bbody\b|crop|kaftan|caftan|polo|maillot|gilet/u',
    ];
    private const ZONE_LABELS = [1 => 'Hauts', 2 => 'Bas', 3 => 'Chaussures', 4 => 'Accessoires'];

    public static function zoneOf(string $name, string $cat = ''): int
    {
        $t = mb_strtolower($name . ' ' . $cat);
        foreach (self::ZONES as $z => $re) if (preg_match($re, $t)) return $z;
        return 0;
    }

    public static function data(Shop $shop, array $pm, array $studio): array
    {
        $lb = Lookbook::data($shop, $pm);
        $products = collect($lb['products'])->filter(fn ($p) => $p['photo'] || count($p['opts']));
        $catOf = fn ($p) => trim((string) $p['cat']) !== '' ? $p['cat'] : 'Nos produits';
        $cats = $products->groupBy($catOf)->map(fn ($l) => $l->pluck('id')->values()->all());

        $slots = [];
        $layout = 'coffret';
        // 1. Rayons choisis par le vendeur (dans l'ordre)
        $picked = collect(['z1', 'z2', 'z3', 'z4'])->map(fn ($k) => (string) ($studio['d'][$k] ?? ''))->filter(fn ($c) => $c !== '' && $cats->has($c))->unique()->values();
        if ($picked->count() >= 2) {
            // Silhouette si au moins deux rayons sont des vêtements (d'après le nom du rayon ou de ses produits)
            $zones = $picked->map(function ($c) use ($cats, $products) {
                $z = self::zoneOf('', $c);
                if (!$z) $z = collect($cats[$c])->map(fn ($id) => self::zoneOf($products[$id]['name'], $c))->filter()->countBy()->sortDesc()->keys()->first() ?? 0;
                return $z;
            });
            $layout = $zones->filter(fn ($z) => $z >= 1 && $z <= 3)->unique()->count() >= 2 ? 'tenue' : 'coffret';
            $used = [];
            foreach ($picked as $i => $c) {
                $z = $zones[$i] ?: 4;
                if ($layout === 'tenue') { while (isset($used[$z]) && $z < 4) $z++; $used[$z] = true; }
                $slots[] = ['label' => $c, 'cat' => $c, 'ids' => $cats[$c], 'zone' => $z, 'shared' => false];
            }
        } else {
            // 2. Placement automatique, produit par produit
            $byZone = $products->groupBy(fn ($p) => self::zoneOf($p['name'], (string) $p['cat']))->map(fn ($l) => $l->pluck('id')->values()->all());
            $clothes = collect([1, 2, 3])->filter(fn ($z) => isset($byZone[$z]))->count();
            if ($clothes >= 2) {
                $layout = 'tenue';
                foreach ([1, 2, 3, 4] as $z) {
                    if (!isset($byZone[$z])) continue;
                    $ids = $byZone[$z];
                    $sameCat = collect($ids)->map(fn ($id) => $catOf($products[$id]))->unique();
                    $slots[] = ['label' => self::ZONE_LABELS[$z], 'cat' => $sameCat->count() === 1 ? $sameCat->first() : null, 'ids' => $ids, 'zone' => $z, 'shared' => false];
                }
            } elseif ($cats->count() >= 2) {
                // 3. Coffret : les plus grandes catégories côte à côte
                foreach ($cats->sortByDesc(fn ($ids) => count($ids))->take(self::MAX_SLOTS) as $c => $ids) {
                    $slots[] = ['label' => $c, 'cat' => $c, 'ids' => $ids, 'zone' => count($slots) + 1, 'shared' => false];
                }
            } elseif ($cats->count() === 1) {
                // 4. Un seul rayon (ex. parfums) : un coffret de 3 pièces au plus, prises dans ce rayon
                $c = $cats->keys()->first();
                $n = min(3, count($cats[$c]));
                for ($i = 0; $i < $n; $i++) $slots[] = ['label' => $n > 1 ? 'Pièce ' . ($i + 1) : $c, 'cat' => $c, 'ids' => $cats[$c], 'zone' => $i + 1, 'shared' => true];
            }
        }
        foreach ($slots as $i => $sl) $slots[$i]['key'] = 'r' . $i;

        return [
            'products' => $products->all(),
            'slots' => array_values($slots),
            'layout' => $layout,
            'cats' => $cats->keys()->values()->all(),
        ];
    }
}
