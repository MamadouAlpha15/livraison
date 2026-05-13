<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Driver;
use App\Models\DeliveryCompany;
use Illuminate\Http\Request;

class SuiviController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['client', 'shop', 'driver', 'deliveryCompany', 'deliveryZone'])
            ->where('status', 'en_livraison')
            ->latest('updated_at');

        if ($request->filled('company_id')) {
            $query->where('delivery_company_id', $request->company_id);
        }

        if ($request->filled('gps')) {
            if ($request->gps === 'live') {
                $query->whereNotNull('current_lat')
                      ->whereNotNull('current_lng')
                      ->where('last_ping_at', '>=', now()->subMinutes(5));
            } elseif ($request->gps === 'with') {
                $query->whereNotNull('current_lat')->whereNotNull('current_lng');
            } elseif ($request->gps === 'without') {
                $query->where(function ($q) {
                    $q->whereNull('current_lat')->orWhereNull('current_lng');
                });
            }
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($sub) use ($s) {
                $sub->where('id', 'like', "%$s%")
                    ->orWhere('delivery_destination', 'like', "%$s%")
                    ->orWhere('client_phone', 'like', "%$s%")
                    ->orWhereHas('client', fn($u) => $u->where('name', 'like', "%$s%"))
                    ->orWhereHas('shop', fn($sh) => $sh->where('name', 'like', "%$s%"));
            });
        }

        $orders = $query->paginate(25)->withQueryString();

        // Stats globales (non scopées au filtre pour la carte complète)
        $baseActive = Order::where('status', 'en_livraison');

        $stats = [
            'en_livraison' => (clone $baseActive)->count(),
            'avec_gps'     => (clone $baseActive)->whereNotNull('current_lat')->whereNotNull('current_lng')->count(),
            'live'         => (clone $baseActive)->whereNotNull('current_lat')->whereNotNull('current_lng')
                                ->where('last_ping_at', '>=', now()->subMinutes(5))->count(),
            'sans_gps'     => (clone $baseActive)->where(function ($q) {
                                $q->whereNull('current_lat')->orWhereNull('current_lng');
                             })->count(),
            'livreurs_busy'=> Driver::where('status', 'busy')->count(),
        ];

        // Tous les points GPS pour la carte (max 200, pas paginés)
        $mapPoints = Order::with(['client', 'shop', 'driver', 'deliveryCompany'])
            ->where('status', 'en_livraison')
            ->whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->latest('last_ping_at')
            ->limit(200)
            ->get();

        $companies = DeliveryCompany::orderBy('name')->get(['id', 'name']);

        return view('admin.suivi.index', compact('orders', 'stats', 'mapPoints', 'companies'));
    }
}
