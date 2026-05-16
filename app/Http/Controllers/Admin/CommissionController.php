<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourierCommission;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $meInit = strtoupper(substr(Auth::user()->name ?? 'S', 0, 1));

        $shopId    = $request->filled('shop_id')    ? (int) $request->shop_id    : null;
        $livreurId = $request->filled('livreur_id') ? (int) $request->livreur_id : null;
        $shopFilter    = $shopId    ? Shop::find($shopId)            : null;
        $livreurFilter = $livreurId ? User::find($livreurId)         : null;

        $query = CourierCommission::with(['order', 'shop', 'livreur'])->latest();

        if ($shopId)    $query->where('shop_id',    $shopId);
        if ($livreurId) $query->where('livreur_id', $livreurId);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('id', 'like', "%{$s}%")
                  ->orWhereHas('order',   fn($o)  => $o->where('id', 'like', "%{$s}%"))
                  ->orWhereHas('shop',    fn($sh) => $sh->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('livreur', fn($u)  => $u->where('name', 'like', "%{$s}%"));
            });
        }

        $period = $request->input('period', 'all');
        match($period) {
            'today'     => $query->whereDate('created_at', today()),
            'yesterday' => $query->whereDate('created_at', today()->subDay()),
            'week'      => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month'     => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            default     => null,
        };

        $commissions = $query->paginate(20)->withQueryString();

        $statsBase = function() use ($period, $shopId, $livreurId) {
            $q = CourierCommission::query()
                ->when($shopId,    fn($q) => $q->where('shop_id', $shopId))
                ->when($livreurId, fn($q) => $q->where('livreur_id', $livreurId));
            match($period) {
                'today'     => $q->whereDate('created_at', today()),
                'yesterday' => $q->whereDate('created_at', today()->subDay()),
                'week'      => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'month'     => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                default     => null,
            };
            return $q;
        };

        $stats = [
            'total'           => $statsBase()->count(),
            'pending_count'   => $statsBase()->where('status', CourierCommission::STATUS_EN_ATTENTE)->count(),
            'paid_count'      => $statsBase()->where('status', CourierCommission::STATUS_PAYEE)->count(),
            'pending_amount'  => $statsBase()->where('status', CourierCommission::STATUS_EN_ATTENTE)->sum('amount'),
            'paid_amount'     => $statsBase()->where('status', CourierCommission::STATUS_PAYEE)->sum('amount'),
            'total_amount'    => $statsBase()->sum('amount'),
        ];

        $shops    = Shop::orderBy('name')->get(['id', 'name']);
        $livreurs = User::where('role', 'livreur')->orderBy('name')->get(['id', 'name']);

        return view('admin.commissions.index', compact(
            'commissions', 'stats', 'period', 'meInit',
            'shops', 'livreurs', 'shopId', 'livreurId', 'shopFilter', 'livreurFilter'
        ));
    }

    public function markPaid(CourierCommission $commission)
    {
        $commission->update([
            'status'  => CourierCommission::STATUS_PAYEE,
            'paid_at' => now(),
        ]);

        return back()->with('success', "Commission #" . str_pad($commission->id, 5, '0', STR_PAD_LEFT) . " marquée comme payée.");
    }
}
