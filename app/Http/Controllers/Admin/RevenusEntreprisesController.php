<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RevenusEntreprisesController extends Controller
{
    public function index(Request $request)
    {
        $meInit  = strtoupper(substr(Auth::user()->name ?? 'S', 0, 1));
        $period  = $request->input('period', 'all');
        $country = $request->input('country');
        $search  = $request->input('search');

        $periodScope = $this->periodScope($period);

        $query = DeliveryCompany::withCount(['orders as livrees_count' => function ($q) use ($periodScope) {
                $q->where('status', Order::STATUS_LIVREE);
                $periodScope($q);
            }])
            ->withSum(['orders as total_frais' => function ($q) use ($periodScope) {
                $q->where('status', Order::STATUS_LIVREE);
                $periodScope($q);
            }], 'delivery_fee')
            ->orderByDesc('total_frais');

        if ($country) {
            $query->where('country', $country);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $companies = $query->paginate(25)->withQueryString();

        // Pays disponibles
        $countries = DeliveryCompany::select('country')
            ->whereNotNull('country')->where('country', '!=', '')
            ->distinct()->orderBy('country')->pluck('country');

        // Devise commune pour le hero
        $currencyQ = DeliveryCompany::select('currency')->whereNotNull('currency')->where('currency', '!=', '');
        if ($country) $currencyQ->where('country', $country);
        $uniqueCurrencies = $currencyQ->distinct()->pluck('currency');
        $heroDevise = $uniqueCurrencies->count() === 1 ? $uniqueCurrencies->first() : null;

        // Stats globales
        $globalBase = Order::where('status', Order::STATUS_LIVREE)->whereNotNull('delivery_company_id');
        if ($country) {
            $globalBase->whereHas('deliveryCompany', fn($q) => $q->where('country', $country));
        }
        $periodScope($globalBase);
        $globalStats = [
            'entreprises' => $country ? DeliveryCompany::where('country', $country)->count() : DeliveryCompany::count(),
            'livrees'     => (clone $globalBase)->count(),
            'total_frais' => (float)(clone $globalBase)->sum('delivery_fee'),
        ];

        return view('admin.revenus.entreprises', compact(
            'companies', 'period', 'globalStats', 'meInit',
            'countries', 'country', 'heroDevise', 'search'
        ));
    }

    public function show(DeliveryCompany $company, Request $request)
    {
        $meInit = strtoupper(substr(Auth::user()->name ?? 'S', 0, 1));
        $period = $request->input('period', 'all');

        $periodScope = $this->periodScope($period);

        $query = Order::where('delivery_company_id', $company->id)
            ->where('status', Order::STATUS_LIVREE)
            ->with(['shop', 'client', 'driver'])
            ->latest();
        $periodScope($query);
        $orders = $query->paginate(25)->withQueryString();

        $statsBase = Order::where('delivery_company_id', $company->id)->where('status', Order::STATUS_LIVREE);
        $periodScope($statsBase);
        $stats = [
            'livrees'     => (clone $statsBase)->count(),
            'total_frais' => (float)(clone $statsBase)->sum('delivery_fee'),
        ];

        return view('admin.revenus.entreprise-show', compact('company', 'orders', 'stats', 'period', 'meInit'));
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
