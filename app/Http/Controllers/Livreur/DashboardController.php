<?php

namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use App\Services\GamificationService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $livreur = Auth::user();
        $shop    = $livreur->shop ?? $livreur->assignedShop;
        $driver  = Driver::where('user_id', $livreur->id)->first();
        $devise  = $shop?->currency ?? $driver?->company?->currency ?? 'GNF';

        $data = $this->buildData($livreur, $driver);

        $totalCommission = $livreur->courierCommissions()->sum('amount');
        $todayCommission = $this->todayCommission($livreur);
        $isCompanyDriver = (bool) ($driver && $driver->delivery_company_id);

        $gamification   = app(GamificationService::class);
        $dailyProgress  = $gamification->dailyProgress($livreur, $driver);
        $leaderboard    = $gamification->weeklyLeaderboard($livreur, $driver, $shop);

        return view('dashboards.livreur', array_merge($data, compact(
            'livreur', 'devise', 'shop', 'totalCommission', 'todayCommission', 'dailyProgress', 'leaderboard', 'isCompanyDriver'
        )));
    }

    /** Commissions (ou frais collectés) enregistrées aujourd'hui pour ce livreur. */
    private function todayCommission($livreur): float
    {
        return (float) $livreur->courierCommissions()->whereDate('created_at', today())->sum('amount');
    }

    public function data()
    {
        $livreur = Auth::user();
        $driver  = Driver::where('user_id', $livreur->id)->first();
        $devise  = ($livreur->shop ?? $livreur->assignedShop)?->currency
                   ?? $driver?->company?->currency
                   ?? 'GNF';

        $data          = $this->buildData($livreur, $driver);
        $dailyProgress = app(GamificationService::class)->dailyProgress($livreur, $driver);

        $fmt = fn($n) => number_format($n ?? 0, 0, ',', "\u{00A0}") . "\u{00A0}" . $devise;

        return response()->json([
            'totalAssigned' => $data['totalAssigned'],
            'enCours'       => $data['enCours'],
            'terminees'     => $data['terminees'],
            'enAttente'     => $data['enAttente'],
            'earnedToday'   => $fmt($this->todayCommission($livreur)),
            'nextKey'       => $data['nextOrder'] ? $data['nextOrder']->id . '|' . $data['nextOrder']->status : '',
            'dailyProgress' => $dailyProgress,
            'recentOrders'  => $data['recentOrders']->map(fn($g) => [
                'order_id' => $g['order']->id,
                'count'    => $g['count'],
                'total'    => $fmt($g['total']),
                'status'   => $g['status'],
                'client'   => ($g['client'])?->name ?? $g['order']->client_name ?? 'Client',
                'shop'     => $g['order']->shop?->name,
                'dest'     => $g['order']->delivery_destination,
            ])->values(),
        ]);
    }

    private function buildData($livreur, $driver): array
    {
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

        $statusPriority = ['en_attente' => 0, 'confirmée' => 1, 'en_livraison' => 2, 'livrée' => 3, 'annulée' => 4];
        $recentOrders = $orders
            ->groupBy(fn($o) => $o->delivery_batch_id ? 'batch_' . $o->delivery_batch_id : '__solo__' . $o->id)
            ->map(function ($grp) use ($statusPriority) {
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

        // Prochaine course : la livraison déjà en route d'abord, sinon le plus ancien colis à récupérer
        $nextOrder = $orders->where('status', Order::STATUS_EN_LIVRAISON)->sortBy('created_at')->first()
            ?? $orders->where('status', Order::STATUS_CONFIRMEE)->sortBy('created_at')->first();

        return compact('totalAssigned', 'enCours', 'terminees', 'enAttente', 'recentOrders', 'nextOrder');
    }
}
