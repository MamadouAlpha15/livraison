<?php

namespace App\PageBuilder\Sections;

use App\Models\Product;
use App\PageBuilder\RenderContext;
use App\PageBuilder\SectionType;
use App\PageBuilder\Settings\ProductList;
use App\PageBuilder\Settings\Range;
use App\PageBuilder\Settings\Select;
use App\PageBuilder\Settings\Text;
use Illuminate\Support\Collection;

/** Autres produits de la boutique : choisis à la main ou les plus récents */
class ProductGrid extends SectionType
{
    public function type(): string { return 'product-list'; }

    public function name(): string { return 'Produits de la boutique'; }

    public function category(): string { return 'produit'; }

    public function icon(): string { return 'grid'; }

    public function description(): ?string
    {
        return 'Fais découvrir d\'autres produits de ta boutique.';
    }

    public function settings(): array
    {
        return [
            new Text('heading', 'Titre', 'Tu aimeras aussi', null, 80),
            new Select('source', 'Produits', 'latest', null, ['latest' => 'Les plus récents', 'manual' => 'Choisis à la main']),
            new ProductList('products', 'Produits choisis', [], 'Pour « Choisis à la main ».', 8),
            new Range('count', 'Nombre de produits', 4, null, 2, 8, 1),
        ];
    }

    /** Produits affichés : en ligne, de la boutique, sans le produit de la page */
    public static function products(array $settings, RenderContext $ctx): Collection
    {
        $base = Product::where('shop_id', $ctx->shop->id)->where('is_active', true)->whereKeyNot($ctx->product->id);
        if ($settings['source'] === 'manual') {
            $ids = array_map('intval', (array) $settings['products']);
            $found = $base->whereIn('id', $ids)->get()->keyBy('id');
            return collect($ids)->map(fn ($id) => $found[$id] ?? null)->filter()->take((int) $settings['count'])->values();
        }
        return $base->latest()->take((int) $settings['count'])->get();
    }
}
