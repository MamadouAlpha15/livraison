<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Ancienne adresse d'une fiche produit (/client/produit/{id}) : liens déjà partagés, SMS, notifications.
     * Elle mène à la page produit du thème de la boutique (/s/{slug}/p/{id-nom}), qui compte elle-même
     * la visite. Les paramètres de suivi (utm_source…) sont gardés.
     */
    public function show(Product $product, Request $request)
    {
        $shop = $product->shop;
        abort_unless($shop, 404);

        $url = \App\Support\ProductPage::url($shop, $product);
        $query = $request->query();
        return redirect()->to($query ? $url . '?' . http_build_query($query) : $url, 301);
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
            ->with(['shop:id,slug,name,image,country,type']);

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
