<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function show(Product $product, Request $request)
    {
        $shop = $product->shop;

        // Visite et vue du produit, une fois par jour, hors vendeur et robots
        if ($shop) {
            \App\Support\TrafficSource::recordLanding($shop, $request);
            \App\Support\TrafficSource::recordProductView($product, $request);
        }

        // Page construite et publiée par le vendeur (constructeur de page) : elle remplace la page produit actuelle
        $built = $product->productPage;
        if ($shop && $built?->isLive()) {
            return response(\App\PageBuilder\Renderer::published($built, new \App\PageBuilder\RenderContext($shop, $product)));
        }

        $isFavorited = auth()->check()
            ? auth()->user()->favoriteProducts()->where('product_id', $product->id)->exists()
            : false;

        $variants = $product->activeVariants()->get();

        // Page produit personnalisée par le vendeur (App\Support\ProductPageSettings)
        $shopPage = \App\Support\ProductPageSettings::shop($shop);
        $productPage = \App\Support\ProductPageSettings::product($product);
        $offers = \App\Support\ProductPageSettings::offers($product);

        // Avis réels : laissés sur des commandes livrées qui contiennent ce produit
        $reviews = \App\Models\Review::with('client:id,name')
            ->whereHas('order.items', fn ($q) => $q->where('product_id', $product->id))
            ->latest()->limit(6)->get();

        return view('client.product-page', [
            'product'     => $product,
            'shop'        => $shop,
            'isFavorited' => $isFavorited,
            'variants'    => $variants,
            'shopPage'    => $shopPage,
            'productPage' => $productPage,
            'offers'      => $offers,
            'selected'    => \App\Support\ProductPageSettings::defaultOffer($offers),
            'faq'         => \App\Support\ProductPageSettings::faq($shop, $product),
            'showOffer'   => \App\Support\ProductPageSettings::showOfferButton($shop, $product),
            // Modèle de page : celui du produit ou de la boutique ; ?modele= pour l'aperçu depuis l'espace vendeur
            'template'    => \App\Support\ProductPageSettings::template($shop, $product, $request->query('modele')),
            'waUrl'       => \App\Support\ProductPageSettings::whatsappUrl($shop, $product),
            'reviews'     => $reviews,
            'reviewAvg'   => $reviews->isNotEmpty() ? round((float) \App\Models\Review::whereHas('order.items', fn ($q) => $q->where('product_id', $product->id))->avg('rating'), 1) : null,
            'reviewCount' => $reviews->isNotEmpty() ? \App\Models\Review::whereHas('order.items', fn ($q) => $q->where('product_id', $product->id))->count() : 0,
        ]);
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Product::where('is_active', true)
            ->whereHas('shop', function ($q) use ($user) {
                $q->where('is_approved', true);
                if ($user->country) {
                    $q->where('country', $user->country);
                }
            })
            ->with(['shop:id,name,image,country,type']);

        if ($s = $request->get('s')) {
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhere('category', 'like', "%{$s}%");
            });
        }

        if ($cat = $request->get('cat')) {
            $query->where('category', $cat);
        }

        $products = $query->latest()->paginate(24)->withQueryString();

        // Campagnes payées : produits sponsorisés dans la recherche et les catégories
        \App\Support\Campaigns\SponsoredPlacements::blend($products, $request->get('s'), $request->get('cat'), $user->country);

        $categories = Product::select('category')
            ->where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->whereHas('shop', function ($q) use ($user) {
                $q->where('is_approved', true);
                if ($user->country) {
                    $q->where('country', $user->country);
                }
            })
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        $countryNames = [
            'GN' => 'Guinée', 'SN' => 'Sénégal', 'CI' => "Côte d'Ivoire",
            'ML' => 'Mali', 'BF' => 'Burkina Faso', 'CM' => 'Cameroun',
            'TG' => 'Togo', 'BJ' => 'Bénin', 'NE' => 'Niger',
            'CD' => 'Congo RDC', 'CG' => 'Congo', 'GA' => 'Gabon',
        ];
        $countryName = $countryNames[$user->country ?? ''] ?? $user->country ?? '';

        return view('client.products', compact('products', 'categories', 'countryName'));
    }
}
