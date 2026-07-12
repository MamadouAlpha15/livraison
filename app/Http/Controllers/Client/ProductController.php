<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ShopVisit;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        ShopVisit::record($product->shop_id);

        $shop = $product->shop;
        return view('client.show', compact('product', 'shop'));
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
