<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductFavoriteController extends Controller
{
    public function toggle(Product $product)
    {
        $user   = Auth::user();
        $result = $user->favoriteProducts()->toggle($product->id);

        return response()->json([
            'favorited' => count($result['attached']) > 0,
            'count'     => $user->favoriteProducts()->count(),
        ]);
    }

    public function index()
    {
        $user = Auth::user();

        $products = $user->favoriteProducts()
            ->with('shop:id,name,image,country')
            ->orderByDesc('product_favorites.created_at')
            ->paginate(24);

        return view('client.wishlist.index', compact('products'));
    }
}
