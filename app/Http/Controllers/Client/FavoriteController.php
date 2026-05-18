<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function toggle(Shop $shop)
    {
        $user = Auth::user();
        $result = $user->favorites()->toggle($shop->id);

        $favorited = count($result['attached']) > 0;
        $count = $user->favorites()->count();

        return response()->json(['favorited' => $favorited, 'count' => $count]);
    }

    public function index()
    {
        $user = Auth::user();
        $favorites = $user->favorites()
            ->where('is_approved', true)
            ->withCount(['products as products_count' => fn($q) => $q->where('is_active', true)])
            ->withCount(['orders as sales_count'])
            ->get();

        $favoriteIds = $favorites->pluck('id')->toArray();

        return response()->json([
            'shops' => $favorites->map(fn($s) => [
                'id'             => $s->id,
                'name'           => $s->name,
                'type'           => $s->type,
                'image'          => $s->image,
                'country'        => $s->country,
                'products_count' => $s->products_count,
                'sales_count'    => $s->sales_count,
            ])
        ]);
    }
}
