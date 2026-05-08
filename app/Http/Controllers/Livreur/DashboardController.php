<?php

namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $livreur = Auth::user();
        $shop    = $livreur->shop ?? $livreur->assignedShop;
        $devise  = $shop?->currency ?? 'GNF';

        // Livreur entreprise : driver lié au compte
        $driver = Driver::where('user_id', $livreur->id)->first();

        $orders = Order::with(['client', 'user', 'shop'])
            ->where(function ($q) use ($livreur, $driver) {
                $q->where('livreur_id', $livreur->id);
                if ($driver) {
                    $q->orWhere('driver_id', $driver->id);
                }
            })
            ->latest()
            ->get();

        $totalAssigned = $orders->count();
        $enCours       = $orders->whereIn('status', ['delivering', 'en_livraison', 'shipped'])->count();
        $terminees     = $orders->whereIn('status', ['delivered', 'livrée', 'completed'])->count();
        $enAttente     = $orders->whereIn('status', ['ready', 'prête', 'assigned', 'confirmée', 'en_attente'])->count();

        $totalCommission = $livreur->courierCommissions()->sum('amount');

        // Grouper par client + destination : même client même adresse = 1 ligne dans "commandes récentes"
        $statusPriority = ['en_attente' => 0, 'confirmée' => 1, 'en_livraison' => 2, 'livrée' => 3, 'annulée' => 4];
        $recentOrders = $orders->groupBy(function ($o) {
                $dest = strtolower(trim($o->delivery_destination ?? $o->client?->address ?? ''));
                return ($o->user_id ?? 'anon') . '::' . $dest;
            })->map(function ($grp) use ($statusPriority) {
                $first  = $grp->first();
                $status = $grp->sortBy(fn($o) => $statusPriority[$o->status] ?? 99)->first()->status;
                return [
                    'order'  => $first,
                    'count'  => $grp->count(),
                    'total'  => $grp->sum('total'),
                    'status' => $status,
                    'client' => $first->client ?? $first->user,
                ];
            })->values()->take(5);

        return view('dashboards.livreur', compact(
            'livreur', 'devise', 'shop',
            'totalAssigned', 'enCours', 'terminees', 'enAttente',
            'totalCommission', 'recentOrders'
        ));
    }
}
