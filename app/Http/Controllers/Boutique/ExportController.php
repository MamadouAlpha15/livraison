<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\OrdersExport;
use App\Exports\PaymentsExport;
use App\Exports\StatsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Shop;
use App\Models\Order;
use App\Models\Payment;

class ExportController extends Controller
{
    /**
     * Récupère l'ID de la boutique de l'utilisateur connecté.
     */
    protected function getCurrentUserShopId(Request $request): ?int
    {
        $user = $request->user();

        if (isset($user->shop_id) && $user->shop_id) {
            return (int) $user->shop_id;
        }

        try {
            if (method_exists($user, 'shop')) {
                $shopRel = $user->shop()->first();
                if ($shopRel) return (int) $shopRel->id;
            }
        } catch (\Throwable $e) {}

        $shop = Shop::where('user_id', $user->id)->first();
        return $shop ? (int)$shop->id : null;
    }

    /**
     * Export commandes -> Excel (.xlsx)
     */
    public function exportOrdersExcel(Request $request)
    {
        $filters = $request->only(['date_from','date_to','status','livreur_id']);
        $shopId = $this->getCurrentUserShopId($request);
        if (!$shopId) abort(403, 'Aucune boutique trouvée.');
        $filters['shop_id'] = $shopId;

        $filename = 'orders_shop_'.$shopId.'_'.now()->format('Ymd_His').'.xlsx';
        return Excel::download(new OrdersExport($filters), $filename);
    }

    /**
     * Export paiements -> Excel (.xlsx)
     */
    public function exportPaymentsExcel(Request $request)
    {
        $filters = $request->only(['date_from','date_to','order_id']);
        $shopId = $this->getCurrentUserShopId($request);
        if (!$shopId) abort(403, 'Aucune boutique trouvée.');
        $filters['shop_id'] = $shopId;

        $filename = 'payments_shop_'.$shopId.'_'.now()->format('Ymd_His').'.xlsx';
        return Excel::download(new PaymentsExport($filters), $filename);
    }

    /**
     * Export stats -> Excel (.xlsx)
     */
    public function exportStatsExcel(Request $request)
    {
        $filters = $request->only(['date_from','date_to']);
        $shopId = $this->getCurrentUserShopId($request);
        if (!$shopId) abort(403, 'Aucune boutique trouvée.');
        $filters['shop_id'] = $shopId;

        $filename = 'stats_shop_'.$shopId.'_'.now()->format('Ymd_His').'.xlsx';
        return Excel::download(new StatsExport($filters), $filename);
    }

    /**
     * Export commandes -> PDF
     */
    public function exportOrdersPdf(Request $request)
    {
        $filters = $request->only(['date_from','date_to','status','livreur_id']);
        $shopId = $this->getCurrentUserShopId($request);
        if (!$shopId) abort(403, 'Aucune boutique trouvée.');
        $filters['shop_id'] = $shopId;

        $query = Order::query()->where('shop_id', $shopId);

        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        if (!empty($filters['date_from'])) $query->whereDate('created_at','>=',$filters['date_from']);
        if (!empty($filters['date_to'])) $query->whereDate('created_at','<=',$filters['date_to']);
        if (!empty($filters['livreur_id'])) $query->where('livreur_id',$filters['livreur_id']);

        $orders = $query->with(['client', 'livreur', 'shop'])->orderByDesc('created_at')->limit(1000)->get();

        $pdf = Pdf::loadView('exports.orders_pdf', compact('orders','filters'));
        return $pdf->download('orders_shop_'.$shopId.'_'.now()->format('Ymd_His').'.pdf');
    }

    /**
     * Export paiements -> PDF
     */
    public function exportPaymentsPdf(Request $request)
    {
        $filters = $request->only(['date_from','date_to','order_id']);
        $shopId = $this->getCurrentUserShopId($request);
        if (!$shopId) abort(403, 'Aucune boutique trouvée.');

        $query = Payment::query()->whereHas('order', fn($q) => $q->where('shop_id',$shopId));

        if (!empty($filters['order_id'])) $query->where('order_id',$filters['order_id']);
        if (!empty($filters['date_from'])) $query->whereDate('created_at','>=',$filters['date_from']);
        if (!empty($filters['date_to'])) $query->whereDate('created_at','<=',$filters['date_to']);

        $payments = $query->with(['order.client','order.livreur','order.shop'])->orderByDesc('created_at')->limit(1000)->get();

        $pdf = Pdf::loadView('exports.payments_pdf', compact('payments','filters'));
        return $pdf->download('payments_shop_'.$shopId.'_'.now()->format('Ymd_His').'.pdf');
    }

    /**
     * Export stats -> PDF
     */
    public function exportStatsPdf(Request $request)
    {
        $filters = $request->only(['date_from','date_to']);
        $shopId = $this->getCurrentUserShopId($request);
        if (!$shopId) abort(403, 'Aucune boutique trouvée.');

        $from = $filters['date_from'] ?? null;
        $to   = $filters['date_to'] ?? null;

        $ordersQuery = Order::where('shop_id',$shopId);
        $paymentsQuery = Payment::whereHas('order', fn($q) => $q->where('shop_id',$shopId));

        if ($from) { $ordersQuery->whereDate('created_at','>=',$from); $paymentsQuery->whereDate('created_at','>=',$from); }
        if ($to)   { $ordersQuery->whereDate('created_at','<=',$to); $paymentsQuery->whereDate('created_at','<=',$to); }

        $totalOrders = $ordersQuery->count();
        $totalRevenue = $ordersQuery->sum('total');
        $avgOrder = $totalOrders ? ($totalRevenue/$totalOrders) : 0;
        $totalPayments = $paymentsQuery->sum('amount');

        $stats = [
            'period' => ($from ?? '—').' — '.($to ?? '—'),
            'total_orders' => $totalOrders,
            'total_revenue' => round($totalRevenue,2),
            'total_payments' => round($totalPayments,2),
            'avg_order' => round($avgOrder,2),
        ];

        $pdf = Pdf::loadView('exports.stats_pdf', compact('stats','filters'));
        return $pdf->download('stats_shop_'.$shopId.'_'.now()->format('Ymd_His').'.pdf');
    }
}
