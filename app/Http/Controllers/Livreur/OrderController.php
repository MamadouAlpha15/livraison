<?php

namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Payment;
use App\Models\CourierCommission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\OrderStatusNotification;

class OrderController extends Controller
{
    /**
     * Retourne le Driver lié au compte connecté (null si livreur boutique).
     */
    private function myDriver(): ?Driver
    {
        return Driver::where('user_id', Auth::id())->first();
    }

    /**
     * Vérifie que l'utilisateur est autorisé à agir sur cette commande.
     * Retourne le Driver si c'est un livreur d'entreprise, null si livreur boutique.
     */
    private function authorizeOrder(Order $order): ?Driver
    {
        $user = Auth::user();

        // Livreur boutique (ancien système via livreur_id)
        if ($order->livreur_id === $user->id) {
            return null;
        }

        // Livreur entreprise (nouveau système via driver_id)
        $driver = $this->myDriver();
        if ($driver && (int) $order->driver_id === $driver->id) {
            return $driver;
        }

        abort(403, 'Accès interdit');
    }

    /**
     * Liste des commandes assignées au livreur connecté.
     * Inclut les commandes via livreur_id (boutique) ET via driver_id (entreprise).
     */
    public function index()
    {
        $user   = Auth::user();
        $shop   = $user->shop ?? $user->assignedShop;
        $devise = $shop?->currency ?? 'GNF';

        $driver = $this->myDriver();

        $query = Order::with(['items.product', 'client', 'shop'])
            ->where(function ($q) use ($user, $driver) {
                $q->where('livreur_id', $user->id);
                if ($driver) {
                    $q->orWhere('driver_id', $driver->id);
                }
            })
            ->latest();

        $orders = $query->paginate(15);

        return view('livreur.orders.index', compact('orders', 'devise'));
    }

    /**
     * Livreur démarre la livraison → statut "en_livraison", chauffeur → busy.
     */
    public function start(Order $order)
    {
        $driver = $this->authorizeOrder($order);

        $order->status = Order::STATUS_EN_LIVRAISON;
        $order->save();

        // Si livreur d'entreprise : passer le driver à busy (is_available reste true — il est toujours en ligne)
        if ($driver) {
            $driver->update(['status' => 'busy']);
        }

        try {
            if ($order->client) {
                $order->client->notify(new OrderStatusNotification(
                    $order,
                    '🚚 Votre commande est en cours de livraison.'
                ));
            }
        } catch (\Throwable $e) {
            Log::warning('Notification start order failed: ' . $e->getMessage());
        }

        session()->flash('autostart_gps_order_id', $order->id);

        return redirect()->route('livreur.orders.index')->with('success', 'Livraison démarrée !');
    }

    /**
     * Livreur termine la livraison → statut "livrée", chauffeur → available.
     */
    public function complete(Order $order)
    {
        $driver = $this->authorizeOrder($order);

        DB::beginTransaction();

        try {
            $order->status = Order::STATUS_LIVREE;
            $order->save();

            if ($order->payment) {
                $order->payment->status = 'payé';
                $order->payment->save();
            }

            $exists = CourierCommission::where('order_id', $order->id)->exists();
            if (! $exists) {
                CourierCommission::create([
                    'order_id'    => $order->id,
                    'livreur_id'  => $order->livreur_id ?? Auth::id(),
                    'shop_id'     => $order->shop_id,
                    'order_total' => $order->total,
                    'rate'        => 0,
                    'amount'      => $order->delivery_fee ?? 0,
                    'status'      => 'en_attente',
                ]);
            }

            // Libérer le chauffeur entreprise (is_available reste tel quel — le livreur reste en ligne)
            if ($driver) {
                $user = Auth::user();
                $driver->update(['status' => $user->is_available ? 'available' : 'offline']);
            }

            DB::commit();

            try {
                if ($order->client) {
                    $order->client->notify(new OrderStatusNotification(
                        $order,
                        '✅ Votre commande a été livrée avec succès.'
                    ));
                }
            } catch (\Throwable $e) {
                Log::warning('Notification complete order failed: ' . $e->getMessage());
            }

            return redirect()->route('livreur.orders.index')->with('success', 'Commande livrée avec succès.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erreur livraison : ' . $e->getMessage(), [
                'order_id'   => $order->id,
                'livreur_id' => Auth::id(),
            ]);

            return back()->with('error', "Impossible de marquer la commande comme livrée : " . $e->getMessage());
        }
    }
}
