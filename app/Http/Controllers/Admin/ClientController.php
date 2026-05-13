<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        // IDs clients filtrés par boutique si shop_id fourni
        $shopClientIds = null;
        if ($request->filled('shop_id')) {
            $shopClientIds = Order::where('shop_id', $request->shop_id)
                ->distinct()->pluck('user_id');
        }

        $query = User::where('role', 'client')
            ->withCount(['orders' => function ($q) use ($request) {
                if ($request->filled('shop_id')) {
                    $q->where('shop_id', $request->shop_id);
                }
            }])
            ->withSum(['orders' => function ($q) use ($request) {
                if ($request->filled('shop_id')) {
                    $q->where('shop_id', $request->shop_id);
                }
            }], 'total')
            ->withMax(['orders' => function ($q) use ($request) {
                if ($request->filled('shop_id')) {
                    $q->where('shop_id', $request->shop_id);
                }
            }], 'created_at')
            ->latest();

        if ($shopClientIds !== null) {
            $query->whereIn('id', $shopClientIds);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%");
            });
        }

        if ($request->filled('activity')) {
            if ($request->activity === 'actif') {
                $query->whereHas('orders', function ($q) use ($request) {
                    if ($request->filled('shop_id')) $q->where('shop_id', $request->shop_id);
                });
            } elseif ($request->activity === 'inactif') {
                $query->whereDoesntHave('orders', function ($q) use ($request) {
                    if ($request->filled('shop_id')) $q->where('shop_id', $request->shop_id);
                });
            }
        }

        $clients = $query->paginate(20)->withQueryString();

        // Stats filtrées par boutique si sélectionnée
        $baseOrders = Order::query();
        if ($request->filled('shop_id')) {
            $baseOrders->where('shop_id', $request->shop_id);
        }
        $clientIds = $shopClientIds ?? User::where('role','client')->pluck('id');

        $stats = [
            'total'         => $shopClientIds !== null ? $shopClientIds->count()
                                : User::where('role','client')->count(),
            'actifs'        => (clone $baseOrders)->whereIn('user_id', $clientIds)->distinct('user_id')->count('user_id'),
            'inactifs'      => ($shopClientIds !== null ? $shopClientIds->count() : User::where('role','client')->count())
                                - (clone $baseOrders)->whereIn('user_id', $clientIds)->distinct('user_id')->count('user_id'),
            'total_depense' => (clone $baseOrders)->whereIn('user_id', $clientIds)->sum('total'),
            'total_cmd'     => (clone $baseOrders)->whereIn('user_id', $clientIds)->count(),
        ];

        $filteredShop = $request->filled('shop_id') ? Shop::find($request->shop_id) : null;
        $shops = Shop::orderBy('name')->get(['id','name']);

        return view('admin.clients.index', compact('clients', 'stats', 'shops', 'filteredShop'));
    }
}
