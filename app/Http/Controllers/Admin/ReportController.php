<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $shopId  = $user->currentShopId(); // helper ajouté dans User
        $isSuper = ($user->role === 'superadmin');

        $ordersQ   = Order::query();
        $paymentsQ = Payment::query();

        if (!$isSuper) {
            // Commandes de MA boutique
            $ordersQ->where('shop_id', $shopId);

            // Paiements liés aux commandes de MA boutique
            $paymentsQ->whereHas('order', function ($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            });
        }

        // ---------- KPIs ----------

        $totalOrders      = $ordersQ->count();
        $pendingOrders    = (clone $ordersQ)->where('status', Order::STATUS_EN_ATTENTE)->count();
        $deliveringOrders = (clone $ordersQ)->where('status', Order::STATUS_EN_LIVRAISON)->count();
        $deliveredOrders  = (clone $ordersQ)->where('status', Order::STATUS_LIVREE)->count();

        $totalRevenue = $paymentsQ->where('status', 'payé')->sum('amount');

        // Vendeurs / Livreurs
        if ($isSuper) {
            $vendors  = User::where('role', 'vendeur')->count();
            $livreurs = User::where('role', 'livreur')
                        ->orWhere('role_in_shop', 'livreur')->count();
        } else {
            $vendors = User::where('role', 'vendeur')
                ->whereIn('id', function ($sub) use ($shopId) {
                    $sub->select('user_id')
                        ->from('orders')
                        ->where('shop_id', $shopId)
                        ->groupBy('user_id');
                })->count();

            $livreurs = User::where(function ($q) {
                    $q->where('role', 'livreur')
                      ->orWhere('role_in_shop', 'livreur');
                })
                ->whereIn('id', function ($sub) use ($shopId) {
                    $sub->select('livreur_id')
                        ->from('orders')
                        ->where('shop_id', $shopId)
                        ->whereNotNull('livreur_id')
                        ->groupBy('livreur_id');
                })->count();
        }

        return view('admin.reports.index', compact(
            'totalOrders',
            'totalRevenue',
            'pendingOrders',
            'deliveringOrders',
            'deliveredOrders',
            'vendors',
            'livreurs',
            'shopId',
            'isSuper'
        ));
    }
}
