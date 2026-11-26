<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shop;
use App\Models\ShopLook;
use App\Services\ImageOptimizer;

/**
 * Données du thème Lookbook (défilé « Essaie le look »), en plus de PageModels::data() :
 * - chaque produit reçoit ses motifs (variantes actives avec photo et prix) et ses tailles ;
 * - le défilé : les looks en ligne (photo portée, pièces avec leur point sur la photo), puis les produits
 *   qui ne sont dans aucun look, un par silhouette, au plus MAX_RUNWAY ;
 * - les produits qui ne tiennent pas dans le défilé vont dans « Toute la boutique », sous le défilé.
 */
class Lookbook
{
    public const MAX_RUNWAY = 12;

    public static function data(Shop $shop, array $pm): array
    {
        $img = fn (?string $p, string $s = 'medium') => $p ? (ImageOptimizer::url($p, $s) ?? asset('storage/' . $p)) : null;
        $ids = collect($pm['products'])->pluck('id');

        $variants = ProductVariant::whereIn('product_id', $ids)->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()->groupBy('product_id');
        $sizes = Product::whereIn('id', $ids)->pluck('sizes', 'id');
        $large = Product::whereIn('id', $ids)->pluck('image', 'id');
        // Photos détourées (Boutique → Looks → « Retirer le fond ») : utilisées en priorité
        $cutouts = Product::whereIn('id', $ids)->whereNotNull('image_cutout')->pluck('image_cutout', 'id');

        $products = collect($pm['products'])->mapWithKeys(fn ($p) => [$p['id'] => [
            'id' => $p['id'], 'name' => $p['name'], 'cat' => $p['cat'], 'price' => $p['price'], 'old' => $p['old'], 'new' => (bool) ($p['new'] ?? false),
            'photo' => $img($cutouts[$p['id']] ?? null, 'large') ?? $img($large[$p['id']] ?? null, 'large') ?? $p['photo'],
            'thumb' => $img($cutouts[$p['id']] ?? null) ?? $img($large[$p['id']] ?? null) ?? $p['thumb'], 'stock' => $p['stock'],
            'sizes' => json_decode((string) ($sizes[$p['id']] ?? ''), true) ?: [],
            'opts' => ($variants[$p['id']] ?? collect())->map(fn ($v) => [
                'id' => $v->id, 'name' => $v->name, 'price' => (int) ((float) $v->price > 0 ? $v->price : $p['price']),
                'photo' => $img($v->image_cutout, 'large') ?? $img($v->image, 'large') ?? $img($v->image), 'thumb' => $img($v->image_cutout) ?? $img($v->image),
                'stock' => $v->stock !== null ? (int) $v->stock : null,
            ])->values()->all(),
        ]]);

        // Points sur la photo : ceux placés par le vendeur (Boutique → Looks), sinon répartis de haut en bas
        $auto = fn (int $i, int $n) => [50, $n < 2 ? 50 : (int) round(22 + 60 * $i / ($n - 1))];

        $looks = ShopLook::where('shop_id', $shop->id)->where('is_active', true)->orderBy('position')->orderBy('id')
            ->with('products:id')->get()
            ->map(function (ShopLook $l) use ($products, $img, $auto) {
                $list = $l->products->filter(fn ($p) => isset($products[$p->id]))->values();
                if ($list->isEmpty()) return null;
                $n = $list->count();
                // Sans photo du look : ses pièces sont posées côte à côte (photos détourées), un point sur chacune
                $photo = $img($l->image_cutout, 'large') ?? $img($l->image, 'large') ?? $img($l->image);
                $parts = $photo ? null : $list->map(fn ($p) => $products[$p->id]['photo'])->filter()->values()->all();
                return [
                    'key' => 'look-' . $l->id, 'kind' => 'look', 'title' => $l->title, 'scene' => $l->scene,
                    'photo' => $photo ?? ($parts[0] ?? null), 'parts' => $parts && count($parts) === $n ? $parts : null,
                    'items' => $list->map(fn ($p, $i) => ['id' => $p->id] + array_combine(['x', 'y'],
                        $p->pivot->pin_x !== null ? [(int) $p->pivot->pin_x, (int) $p->pivot->pin_y] : $auto($i, $n)))->all(),
                ];
            })->filter()->values();

        // Nom des ensembles : « Coffrets » si les looks sont surtout des parfums ou des cosmétiques, sinon « Looks »
        $inLooks = $looks->pluck('items')->flatten(1)->map(fn ($i) => mb_strtolower((string) $products[$i['id']]['cat']));
        $beauty = $inLooks->filter(fn ($c) => preg_match('/parfum|beaut|cosm|soin|maquill/u', $c))->count();
        $word = $inLooks->isNotEmpty() && $beauty * 2 >= $inLooks->count()
            ? ['one' => 'Coffret', 'plural' => 'coffrets', 'in' => 'Dans ce coffret', 'title' => 'Découvre le coffret.', 'complete' => 'Le coffret complet', 'done' => 'Coffret complet dans ton panier', 'sub' => 'à offrir ou à s’offrir']
            : ['one' => 'Look', 'plural' => 'looks', 'in' => 'Dans ce look', 'title' => 'Essaie le look.', 'complete' => 'Compléter le look', 'done' => 'Look complet dans ton panier', 'sub' => 'pensés de la tête aux pieds'];

        // Défilé : les looks, puis les produits avec photo qui ne sont dans aucun look
        $inAny = $looks->pluck('items')->flatten(1)->pluck('id')->flip();
        $runway = $looks->all();
        foreach ($products as $p) {
            if (count($runway) >= self::MAX_RUNWAY) break;
            if (isset($inAny[$p['id']]) || !$p['photo']) continue;
            $runway[] = ['key' => 'produit-' . $p['id'], 'kind' => 'product', 'title' => $p['name'], 'scene' => null, 'photo' => $p['photo'], 'items' => [['id' => $p['id'], 'x' => 50, 'y' => 45]]];
        }
        $shown = collect($runway)->pluck('items')->flatten(1)->pluck('id')->flip();
        $more = $products->keys()->filter(fn ($id) => !isset($shown[$id]))->values()->all();

        return ['word' => $word, 'products' => $products->all(), 'runway' => $runway, 'more' => $more, 'looks' => $looks->count()];
    }
}
