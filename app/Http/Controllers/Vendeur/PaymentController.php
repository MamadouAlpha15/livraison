<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()->route('vendeur.dashboard')
                ->with('error', 'Vous devez avoir une boutique pour consulter vos revenus.');
        }

        $devise = $shop->currency ?? 'GNF';
        $period = in_array($request->input('period'), ['month', 'last_month', 'all'])
                  ? $request->input('period') : 'month';

        $sub = now()->subMonthNoOverflow();

        $payments = Payment::with('order.user')
            ->whereHas('order', fn($q) => $q->where('shop_id', $shop->id)->where('status', 'livrée'))
            ->where('status', 'payé')
            ->when($period === 'month',      fn($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
            ->when($period === 'last_month', fn($q) => $q->whereMonth('created_at', $sub->month)->whereYear('created_at', $sub->year))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalRevenue = (float) Payment::whereHas('order', fn($q) => $q->where('shop_id', $shop->id)->where('status', 'livrée'))
            ->where('status', 'payé')
            ->when($period === 'month',      fn($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
            ->when($period === 'last_month', fn($q) => $q->whereMonth('created_at', $sub->month)->whereYear('created_at', $sub->year))
            ->sum('amount');

        return view('vendeur.payments.index', compact(
            'payments', 'totalRevenue', 'shop', 'devise', 'period'
        ));
    }
}