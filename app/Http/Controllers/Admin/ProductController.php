<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('shop.owner')
            ->latest()
            ->paginate(20);

        $stats = [
            'total'      => Product::count(),
            'active'     => Product::where('is_active', true)->count(),
            'inactive'   => Product::where('is_active', false)->count(),
            'out_stock'  => Product::where('stock', '<=', 0)->count(),
            'featured'   => Product::where('is_featured', true)->count(),
            'promo'      => Product::whereNotNull('original_price')
                                   ->whereColumn('original_price', '>', 'price')
                                   ->count(),
        ];

        $shops = Shop::orderBy('name')->get(['id', 'name']);

        return view('admin.products.index', compact('products', 'stats', 'shops'));
    }

    public function toggle(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);

        $label = $product->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Produit « {$product->name} » {$label} avec succès.");
    }
}
