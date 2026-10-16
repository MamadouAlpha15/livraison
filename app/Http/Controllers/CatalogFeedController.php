<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use App\Services\ImageOptimizer;
use App\Support\TrafficSource;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Catalogue produits d'une boutique au format Meta (Facebook / Instagram Commerce Manager).
 * Le vendeur colle l'adresse de ce fichier dans « Sources de données » ; Meta le relit
 * automatiquement et garde prix, stock et photos à jour.
 * Champs : https://www.facebook.com/business/help/120325381656392
 */
class CatalogFeedController extends Controller
{
    public const COLUMNS = ['id', 'title', 'description', 'availability', 'condition', 'price', 'sale_price', 'link', 'image_link', 'additional_image_link', 'brand', 'product_type'];

    /** Produits publiables : actifs et avec une photo (Meta refuse les articles sans image). */
    public static function query(Shop $shop)
    {
        return $shop->products()->where('is_active', true)->whereNotNull('image')->where('image', '!=', '');
    }

    public function meta(Shop $shop): StreamedResponse
    {
        abort_unless($shop->is_approved, 404);

        $currency = strtoupper($shop->currency ?: 'GNF');
        $brand = Str::limit($shop->name, 100, '');

        return response()->streamDownload(function () use ($shop, $currency, $brand) {
            $out = fopen('php://output', 'w');
            fputcsv($out, self::COLUMNS);

            self::query($shop)
                ->withSum(['variants as variants_stock' => fn ($q) => $q->where('is_active', true)], 'stock')
                ->orderBy('id')
                ->chunk(200, function ($products) use ($out, $currency, $brand) {
                    foreach ($products as $p) {
                        fputcsv($out, $this->row($p, $currency, $brand));
                    }
                });

            fclose($out);
        }, 'catalogue-' . Str::slug($shop->name) . '.csv', [
            'Content-Type'  => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'public, max-age=1800',
            // Fichier public mais sans intérêt pour les moteurs de recherche
            'X-Robots-Tag'  => 'noindex',
        ]);
    }

    private function row(Product $p, string $currency, string $brand): array
    {
        $current = $p->current_price;
        $regular = max((float) $p->price, (float) $p->original_price);
        $stock = (int) $p->stock + (int) ($p->variants_stock ?? 0);

        $description = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $p->description)));
        $gallery = collect($p->gallery_array)
            ->map(fn ($img) => ImageOptimizer::url($img, 'medium'))
            ->filter()->take(10)->implode(',');

        return [
            // Même identifiant que content_ids des événements du pixel (ViewContent, AddToCart, Purchase)
            (string) $p->id,
            Str::limit($p->name, 150, ''),
            Str::limit($description !== '' ? $description : $p->name, 5000, ''),
            $stock > 0 ? 'in stock' : 'out of stock',
            $this->condition($p->name . ' ' . $description),
            $this->money($regular, $currency),
            $current < $regular ? $this->money($current, $currency) : '',
            TrafficSource::tag(route('client.orders.createFromProduct', $p), 'facebook', 'catalogue'),
            ImageOptimizer::url($p->image, 'medium'),
            $gallery,
            $brand,
            Str::limit((string) $p->category, 750, ''),
        ];
    }

    /** État de l'article : Meta demande new, refurbished ou used. */
    private function condition(string $text): string
    {
        $text = mb_strtolower($text);
        if (str_contains($text, 'reconditionn')) return 'refurbished';
        if (preg_match('/(occasion|seconde main|déjà porté)/u', $text)) return 'used';
        return 'new';
    }

    private function money(float $amount, string $currency): string
    {
        return number_format($amount, 2, '.', '') . ' ' . $currency;
    }
}
