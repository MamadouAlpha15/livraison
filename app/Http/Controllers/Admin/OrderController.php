<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['client', 'shop', 'livreur', 'items.product', 'deliveryCompany'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('id', 'like', "%$q%")
                    ->orWhere('client_phone', 'like', "%$q%")
                    ->orWhere('delivery_destination', 'like', "%$q%")
                    ->orWhereHas('client', fn($u) => $u->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%"))
                    ->orWhereHas('shop', fn($s) => $s->where('name', 'like', "%$q%"));
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        // Stats scopées à la boutique filtrée (si applicable)
        $statsBase = Order::query();
        if ($request->filled('shop_id')) {
            $statsBase->where('shop_id', $request->shop_id);
        }

        $stats = [
            'total'        => (clone $statsBase)->count(),
            'en_attente'   => (clone $statsBase)->where('status', 'en_attente')->count(),
            'confirmee'    => (clone $statsBase)->where('status', 'confirmée')->count(),
            'en_livraison' => (clone $statsBase)->where('status', 'en_livraison')->count(),
            'livree'       => (clone $statsBase)->where('status', 'livrée')->count(),
            'annulee'      => (clone $statsBase)->where('status', 'annulée')->count(),
            'revenue'      => (clone $statsBase)->where('status', 'livrée')->sum('total'),
        ];

        $shops        = Shop::orderBy('name')->get(['id', 'name']);
        $filteredShop = $request->filled('shop_id') ? Shop::find($request->shop_id) : null;

        return view('admin.orders.index', compact('orders', 'stats', 'shops', 'filteredShop'));
    }

    public function show(Order $order)
    {
        $order->load(['client', 'shop.owner', 'livreur', 'items.product', 'deliveryCompany', 'driver', 'payment', 'review']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'in:en_attente,confirmée,en_livraison,livrée,annulée'],
        ]);

        $order->update(['status' => $request->status]);

        return back()->with('success', "Statut de la commande #{$order->id} mis à jour.");
    }
}
