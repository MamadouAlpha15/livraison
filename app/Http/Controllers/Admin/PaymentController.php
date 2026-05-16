<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $meInit = strtoupper(substr(Auth::user()->name ?? 'S', 0, 1));

        $shopId     = $request->filled('shop_id') ? (int) $request->shop_id : null;
        $shopFilter = $shopId ? Shop::find($shopId) : null;
        $country    = $request->filled('country') ? trim($request->country) : null;

        $query = Payment::with(['order.shop', 'order.client'])->latest();

        if ($shopId) {
            $query->whereHas('order', fn($o) => $o->where('shop_id', $shopId));
        } elseif ($country) {
            $query->whereHas('order.shop', fn($s) => $s->where('country', $country));
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('id', 'like', "%{$s}%")
                  ->orWhereHas('order', fn($o) => $o->where('id', 'like', "%{$s}%"))
                  ->orWhereHas('order.shop',   fn($sh) => $sh->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('order.client', fn($cl) => $cl->where('name', 'like', "%{$s}%")
                                                              ->orWhere('phone', 'like', "%{$s}%"));
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

        $payments = $query->paginate(20)->withQueryString();

        $statsBase = function() use ($period, $shopId, $country) {
            $q = Payment::query()
                ->when($shopId,  fn($q) => $q->whereHas('order', fn($o) => $o->where('shop_id', $shopId)))
                ->when(!$shopId && $country, fn($q) => $q->whereHas('order.shop', fn($s) => $s->where('country', $country)));
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
            'total'          => $statsBase()->count(),
            'pending_count'  => $statsBase()->where('status', 'en_attente')->count(),
            'paid_count'     => $statsBase()->where('status', 'payé')->count(),
            'pending_amount' => $statsBase()->where('status', 'en_attente')->sum('amount'),
            'paid_amount'    => $statsBase()->where('status', 'payé')->sum('amount'),
        ];

        $shops     = Shop::orderBy('name')->get(['id', 'name', 'country']);
        $countries = Shop::whereNotNull('country')->where('country', '!=', '')
                         ->distinct()->orderBy('country')->pluck('country');

        return view('admin.paiements.index', compact(
            'payments', 'stats', 'period', 'meInit', 'shops', 'shopId', 'shopFilter', 'country', 'countries'
        ));
    }

    public function markPaid(Payment $payment)
    {
        $payment->update(['status' => 'payé']);
        return back()->with('success', "Paiement #" . str_pad($payment->id, 5, '0', STR_PAD_LEFT) . " marqué comme payé.");
    }
}
