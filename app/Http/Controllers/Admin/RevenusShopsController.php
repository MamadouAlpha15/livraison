<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RevenusShopsController extends Controller
{
    public function index(Request $request)
    {
        $meInit  = strtoupper(substr(Auth::user()->name ?? 'S', 0, 1));
        $period  = $request->input('period', 'all');
        $country = $request->input('country');
        $search  = $request->input('search');

        $periodScope = $this->periodScope($period);

        $query = Shop::withCount(['orders as livrees_count' => function ($q) use ($periodScope) {
                $q->where('status', Order::STATUS_LIVREE);
                $periodScope($q);
            }])
            ->withSum(['orders as total_brut' => function ($q) use ($periodScope) {
                $q->where('status', Order::STATUS_LIVREE);
                $periodScope($q);
            }], 'total')
            ->withSum(['orders as total_livraison' => function ($q) use ($periodScope) {
                $q->where('status', Order::STATUS_LIVREE);
                $periodScope($q);
            }], 'delivery_fee')
            ->orderByDesc('total_brut');

        if ($country) {
            $query->where('country', $country);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $shops = $query->paginate(25)->withQueryString();

        // Liste des pays disponibles pour le filtre
        $countries = Shop::select('country')
            ->whereNotNull('country')->where('country', '!=', '')
            ->distinct()->orderBy('country')->pluck('country');

        // Devise commune pour le hero (une seule devise = on l'affiche, sinon "mixte")
        $currencyQ = Shop::select('currency')->whereNotNull('currency')->where('currency', '!=', '');
        if ($country) $currencyQ->where('country', $country);
        $uniqueCurrencies = $currencyQ->distinct()->pluck('currency');
        $heroDevise = $uniqueCurrencies->count() === 1 ? $uniqueCurrencies->first() : null;

        // Stats globales filtrées par pays si actif
        $globalBase = Order::where('status', Order::STATUS_LIVREE);
        if ($country) {
            $globalBase->whereHas('shop', fn($q) => $q->where('country', $country));
        }
        $periodScope($globalBase);
        $globalStats = [
            'boutiques'  => $country ? Shop::where('country', $country)->count() : Shop::count(),
            'livrees'    => (clone $globalBase)->count(),
            'total_brut' => (float)(clone $globalBase)->sum('total'),
            'total_livr' => (float)(clone $globalBase)->sum('delivery_fee'),
        ];
        $globalStats['total_net'] = $globalStats['total_brut'] - $globalStats['total_livr'];

        return view('admin.revenus.boutiques', compact(
            'shops', 'period', 'globalStats', 'meInit', 'countries', 'country', 'heroDevise', 'search'
        ));
    }

    public function show(Shop $shop, Request $request)
    {
        $meInit = strtoupper(substr(Auth::user()->name ?? 'S', 0, 1));
        $period = $request->input('period', 'all');

        $periodScope = $this->periodScope($period);

        $query = Order::where('shop_id', $shop->id)
            ->where('status', Order::STATUS_LIVREE)
            ->with(['client', 'deliveryCompany'])
            ->latest();
        $periodScope($query);
        $orders = $query->paginate(25)->withQueryString();

        $statsBase = Order::where('shop_id', $shop->id)->where('status', Order::STATUS_LIVREE);
        $periodScope($statsBase);
        $stats = [
            'livrees'         => (clone $statsBase)->count(),
            'total_brut'      => (float)(clone $statsBase)->sum('total'),
            'total_livraison' => (float)(clone $statsBase)->sum('delivery_fee'),
        ];
        $stats['total_net'] = $stats['total_brut'] - $stats['total_livraison'];

        return view('admin.revenus.boutique-show', compact('shop', 'orders', 'stats', 'period', 'meInit'));
    }

    private function periodScope(string $period): \Closure
    {
        return function ($q) use ($period) {
            match($period) {
                'today'     => $q->whereDate('created_at', today()),
                'yesterday' => $q->whereDate('created_at', today()->subDay()),
                'week'      => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'month'     => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                'year'      => $q->whereYear('created_at', now()->year),
                default     => null,
            };
        };
    }
}
