<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\CourierCommission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user    = Auth::user();
        $shopId  = $user->currentShopId();
        $isSuper = ($user->role === 'superadmin');
        $now     = Carbon::now();
        $shop    = $user->shop ?? $user->assignedShop;
        $devise  = $shop?->currency ?? 'GNF';

        $period = $request->get('period', 'month');
        [$dateFrom, $dateTo, $prevFrom, $prevTo, $periodLabel] = $this->resolvePeriod(
            $period, $request->get('from'), $request->get('to'), $now
        );

        /* ── Queries de base ── */
        $baseQ = Order::query();
        if (!$isSuper) $baseQ->where('shop_id', $shopId);

        $allLivrees = (clone $baseQ)->where('status', 'livrée');
        $allValid   = (clone $baseQ)->whereNotIn('status', ['annulée', 'cancelled']);

        $periodQ       = (clone $baseQ)->whereBetween('created_at', [$dateFrom, $dateTo]);
        $periodLivrees = (clone $periodQ)->where('status', 'livrée');
        $periodValid   = (clone $periodQ)->whereNotIn('status', ['annulée', 'cancelled']);

        /* ── Global (all time) ── */
        $totalOrders    = $baseQ->count();
        $totalRevGross  = (float)(clone $allLivrees)->sum('total');
        $totalCommPaid  = $this->sumComm($isSuper, $shopId, null, null);
        $totalRevenue   = max(0, $totalRevGross - $totalCommPaid);

        /* ── Pipeline période ── */
        $ordersThisMonth  = (clone $periodValid)->count();
        $pendingOrders    = (clone $periodQ)->where('status', Order::STATUS_EN_ATTENTE)->count();
        $deliveringOrders = (clone $periodQ)->where('status', Order::STATUS_EN_LIVRAISON)->count();
        $deliveredOrders  = (clone $periodLivrees)->count();
        $cancelledOrders  = (clone $periodQ)->whereIn('status', ['annulée', 'cancelled'])->count();

        /* ── Revenue période ── */
        $revGross         = (float)(clone $periodLivrees)->sum('total');
        $commPaidPeriod   = $this->sumComm($isSuper, $shopId, $dateFrom, $dateTo);
        $revenueThisMonth = max(0, $revGross - $commPaidPeriod);

        /* ── Delta vs période précédente ── */
        $prevLivrees  = (clone $baseQ)->where('status', 'livrée')->whereBetween('created_at', [$prevFrom, $prevTo]);
        $prevRevGross = (float)$prevLivrees->sum('total');
        $prevCommPaid = $this->sumComm($isSuper, $shopId, $prevFrom, $prevTo);
        $prevRevNet   = max(0, $prevRevGross - $prevCommPaid) ?: 1;
        $revenueDelta = round((($revenueThisMonth - $prevRevNet) / $prevRevNet) * 100, 1);

        /* ── Performance ── */
        $periodValidCount = (clone $periodValid)->count();
        $tauxLivraison  = $periodValidCount > 0
            ? round(($deliveredOrders / $periodValidCount) * 100, 1) : 0;
        $totalInPeriod  = $periodValidCount + $cancelledOrders;
        $tauxAnnulation = $totalInPeriod > 0
            ? round(($cancelledOrders / $totalInPeriod) * 100, 1) : 0;
        $panierMoyen    = $deliveredOrders > 0 ? round($revenueThisMonth / $deliveredOrders) : 0;

        /* ── Commissions ── */
        $commissionsPending = $this->sumComm($isSuper, $shopId, null, null, 'en_attente');
        $commissionsPaid    = $this->sumComm($isSuper, $shopId, null, null, 'payée');

        /* ── Graphique 6 mois ── */
        $chartMois = collect(range(5, 0))->map(function ($i) use ($allLivrees, $allValid, $now, $shopId, $isSuper) {
            $mois    = $now->copy()->subMonths($i);
            $mStart  = $mois->copy()->startOfMonth();
            $mEnd    = $mois->copy()->endOfMonth();
            $revG    = (float)(clone $allLivrees)->whereBetween('created_at', [$mStart, $mEnd])->sum('total');
            $comm    = $this->sumComm($isSuper, $shopId, $mStart, $mEnd);
            return [
                'label'   => $mois->isoFormat('MMM YY'),
                'orders'  => (clone $allValid)->whereBetween('created_at', [$mStart, $mEnd])->count(),
                'revenue' => max(0, $revG - $comm),
                'actuel'  => $i === 0,
            ];
        });
        $maxRevenue = $chartMois->max('revenue') ?: 1;
        $maxOrders  = $chartMois->max('orders')  ?: 1;

        /* ── Équipe ── */
        if ($isSuper) {
            $vendors  = User::where('role', 'vendeur')->count();
            $livreurs = User::where('role', 'livreur')->orWhere('role_in_shop', 'livreur')->count();
        } else {
            $vendors = User::where('role', 'vendeur')
                ->whereIn('id', fn($s) => $s->select('user_id')->from('orders')->where('shop_id', $shopId)->groupBy('user_id'))
                ->count();
            $livreurs = User::where(fn($q) => $q->where('role', 'livreur')->orWhere('role_in_shop', 'livreur'))
                ->whereIn('id', fn($s) => $s->select('livreur_id')->from('orders')->where('shop_id', $shopId)->whereNotNull('livreur_id')->groupBy('livreur_id'))
                ->count();
        }

        /* ── Top produits (période) ── */
        $topProducts = collect();
        if ($shop) {
            $topProducts = $shop->products()
                ->withCount(['orderItems as ventes' => fn($q) => $q
                    ->whereHas('order', fn($o) => $o
                        ->where('status', 'livrée')
                        ->whereBetween('created_at', [$dateFrom, $dateTo]))])
                ->orderByDesc('ventes')
                ->take(5)
                ->get();
        }

        /* ── Top livreurs (période) ── */
        $topLivreurs = collect();
        if ($shopId) {
            $topLivreurs = collect(
                DB::table('orders')
                    ->join('users', 'orders.livreur_id', '=', 'users.id')
                    ->where('orders.shop_id', $shopId)
                    ->where('orders.status', 'livrée')
                    ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
                    ->select(
                        'users.id',
                        'users.name',
                        DB::raw('COUNT(*) as livraisons'),
                        DB::raw('SUM(orders.total) as ca_livre')
                    )
                    ->groupBy('users.id', 'users.name')
                    ->orderByDesc('livraisons')
                    ->limit(5)
                    ->get()
            );
        }

        /* ── Clients nouveaux vs fidèles ── */
        $clientStats = $this->clientStats($shopId, $dateFrom, $dateTo);

        return view('admin.reports.index', compact(
            'totalOrders', 'totalRevenue',
            'pendingOrders', 'deliveringOrders', 'deliveredOrders', 'cancelledOrders',
            'tauxLivraison', 'tauxAnnulation', 'panierMoyen',
            'ordersThisMonth', 'revenueThisMonth', 'revenueDelta',
            'chartMois', 'maxRevenue', 'maxOrders',
            'commissionsPending', 'commissionsPaid',
            'vendors', 'livreurs',
            'topProducts', 'topLivreurs', 'clientStats',
            'shopId', 'isSuper', 'shop', 'devise',
            'period', 'periodLabel', 'dateFrom', 'dateTo'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $user    = Auth::user();
        $shopId  = $user->currentShopId();
        $isSuper = ($user->role === 'superadmin');
        $now     = Carbon::now();
        $shop    = $user->shop ?? $user->assignedShop;
        $devise  = $shop?->currency ?? 'GNF';

        $period = $request->get('period', 'month');
        [$dateFrom, $dateTo, , , $periodLabel] = $this->resolvePeriod(
            $period, $request->get('from'), $request->get('to'), $now
        );

        $q = Order::with(['client:id,name,phone', 'livreur:id,name'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderByDesc('id');
        if (!$isSuper) $q->where('shop_id', $shopId);
        $orders = $q->get();

        $filename = 'rapport_' . Str::slug($periodLabel) . '_' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($orders, $devise) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Réf', 'Client', 'Téléphone', 'Livreur',
                'Statut', 'Total (' . $devise . ')',
                'Frais livraison (' . $devise . ')', 'Date',
            ], ';');
            foreach ($orders as $o) {
                fputcsv($out, [
                    '#' . $o->id,
                    $o->client?->name  ?? '—',
                    $o->client?->phone ?? '—',
                    $o->livreur?->name ?? '—',
                    $o->status,
                    number_format($o->total, 0, ',', ' '),
                    number_format($o->delivery_fee ?? 0, 0, ',', ' '),
                    $o->created_at->format('d/m/Y H:i'),
                ], ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /* ── Helpers privés ── */

    private function resolvePeriod(string $period, ?string $from, ?string $to, Carbon $now): array
    {
        switch ($period) {
            case '3months':
                return [
                    $now->copy()->subMonths(3)->startOfDay(),
                    $now->copy()->endOfDay(),
                    $now->copy()->subMonths(6)->startOfDay(),
                    $now->copy()->subMonths(3)->subDay()->endOfDay(),
                    '3 derniers mois',
                ];
            case '6months':
                return [
                    $now->copy()->subMonths(6)->startOfDay(),
                    $now->copy()->endOfDay(),
                    $now->copy()->subMonths(12)->startOfDay(),
                    $now->copy()->subMonths(6)->subDay()->endOfDay(),
                    '6 derniers mois',
                ];
            case 'year':
                return [
                    $now->copy()->startOfYear(),
                    $now->copy()->endOfDay(),
                    $now->copy()->subYear()->startOfYear(),
                    $now->copy()->subYear()->endOfYear(),
                    'Cette année (' . $now->year . ')',
                ];
            case 'custom':
                $df   = $from ? Carbon::parse($from)->startOfDay() : $now->copy()->startOfMonth();
                $dt   = $to   ? Carbon::parse($to)->endOfDay()     : $now->copy()->endOfDay();
                $diff = max(1, $df->diffInDays($dt));
                $pTo  = $df->copy()->subDay()->endOfDay();
                $pFr  = $pTo->copy()->subDays($diff)->startOfDay();
                return [$df, $dt, $pFr, $pTo, $df->format('d/m/Y') . ' → ' . $dt->format('d/m/Y')];
            default: // month
                return [
                    $now->copy()->startOfMonth(),
                    $now->copy()->endOfDay(),
                    $now->copy()->subMonth()->startOfMonth(),
                    $now->copy()->subMonth()->endOfMonth()->endOfDay(),
                    'Ce mois-ci (' . $now->isoFormat('MMMM YYYY') . ')',
                ];
        }
    }

    private function sumComm(bool $isSuper, $shopId, $from, $to, string $status = 'payée'): float
    {
        $q = CourierCommission::where('status', $status);
        if (!$isSuper && $shopId) $q->where('shop_id', $shopId);
        if ($from && $to) $q->whereBetween('created_at', [$from, $to]);
        return (float) $q->sum('amount');
    }

    private function clientStats($shopId, Carbon $dateFrom, Carbon $dateTo): array
    {
        if (!$shopId) return ['nouveaux' => 0, 'fideles' => 0, 'total' => 0];

        $clientsInPeriod = Order::where('shop_id', $shopId)
            ->whereNotNull('user_id')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->pluck('user_id')
            ->unique();

        $fideles = Order::where('shop_id', $shopId)
            ->whereNotNull('user_id')
            ->where('created_at', '<', $dateFrom)
            ->whereIn('user_id', $clientsInPeriod)
            ->pluck('user_id')
            ->unique()
            ->count();

        $total    = $clientsInPeriod->count();
        $nouveaux = max(0, $total - $fideles);

        return ['nouveaux' => $nouveaux, 'fideles' => $fideles, 'total' => $total];
    }
}
