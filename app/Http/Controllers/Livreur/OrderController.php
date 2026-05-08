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

        // Mapper les filtres URL (anglais) → statuts DB (français)
        $statusFilter = match(request('status')) {
            'confirmed'  => [Order::STATUS_CONFIRMEE],
            'delivering' => [Order::STATUS_EN_LIVRAISON],
            'delivered'  => [Order::STATUS_LIVREE, Order::STATUS_ANNULEE],
            // Par défaut : seulement les commandes actives — évite de noyer la nouvelle dans l'historique
            default      => [Order::STATUS_CONFIRMEE, Order::STATUS_EN_LIVRAISON],
        };

        $query = Order::with(['items.product', 'client', 'shop'])
            ->where(function ($q) use ($user, $driver) {
                $q->where('livreur_id', $user->id);
                if ($driver) {
                    $q->orWhere('driver_id', $driver->id);
                }
            })
            ->whereIn('status', $statusFilter)
            ->latest();

        $orders = $query->paginate(15);

        /* Grouper les commandes par client + destination → 1 trajet */
        $groups = $this->groupOrders($orders->getCollection(), $driver);

        return view('livreur.orders.index', compact('orders', 'groups', 'devise'));
    }

    /**
     * Regroupe les commandes d'entreprise par (client + destination).
     * Même client, même adresse = 1 groupe = 1 seul frais de livraison.
     */
    private function groupOrders($orders, $driver): array
    {
        $statusPriority = ['en_attente' => 0, 'confirmée' => 1, 'en_livraison' => 2, 'livrée' => 3, 'annulée' => 4];
        $map    = [];
        $result = [];

        foreach ($orders as $order) {
            $isCompanyOrder = $order->driver_id && $driver && (int)$order->driver_id === $driver->id;

            if ($isCompanyOrder) {
                // Livreur entreprise (driver) : groupe par client + destination
                $destNorm = strtolower(trim($order->delivery_destination ?? $order->client?->address ?? ''));
                $key = ($order->user_id ?? 'anon') . '::' . $destNorm;
            } elseif ($order->delivery_batch_id) {
                // Bulk-assigné via checkbox : batch_id commun → 1 groupe = 1 trajet
                $key = 'batch_' . $order->delivery_batch_id;
            } else {
                // Assignation individuelle (bouton ✅ par ligne) → jamais groupée
                $key = '__solo__' . $order->id;
            }

            if (!isset($map[$key])) {
                $map[$key] = count($result);
                $result[] = [
                    // Livreur entreprise : is_group=true dès la 1ère commande (affiche bloc retrait→livraison)
                    // Livreur boutique   : is_group=false pour 1 commande, devient true si une 2ème rejoint
                    'is_group'     => $isCompanyOrder,
                    'orders'       => [$order],
                    'client'       => $order->client ?? $order->user,
                    'shop'         => $order->shop,
                    'destination'  => $order->delivery_destination ?? $order->client?->address,
                    'delivery_fee' => (float)($order->delivery_fee ?? 0),
                    'status'       => $order->status,
                    'total'        => (float)$order->total,
                    'created_at'   => $order->created_at,
                ];
            } else {
                $idx = $map[$key];
                $result[$idx]['is_group']  = true;
                $result[$idx]['orders'][]  = $order;
                $result[$idx]['total']    += (float)$order->total;
                $cur = $statusPriority[$result[$idx]['status']] ?? 99;
                $new = $statusPriority[$order->status] ?? 99;
                if ($new < $cur) {
                    $result[$idx]['status'] = $order->status;
                }
            }
        }

        return $result;
    }

    /** Démarrer plusieurs commandes d'un même groupe d'un coup. */
    public function startBulk(Request $request)
    {
        $driver = $this->myDriver();
        $user   = Auth::user();
        $ids    = $request->validate(['order_ids' => 'required|array|min:1'])['order_ids'];

        $orders = Order::whereIn('id', $ids)->get();
        foreach ($orders as $order) {
            if ($order->livreur_id !== $user->id && (!$driver || (int)$order->driver_id !== $driver->id)) continue;
            $order->status = Order::STATUS_EN_LIVRAISON;
            $order->save();
            try { $order->client?->notify(new OrderStatusNotification($order, '🚚 Votre commande est en cours de livraison.')); } catch (\Throwable $e) {}
        }

        if ($driver) $driver->update(['status' => 'busy']);
        session()->flash('autostart_gps_order_id', $ids[0] ?? null);
        return redirect()->route('livreur.orders.index')->with('success', 'Livraison démarrée !');
    }

    /** Compléter plusieurs commandes d'un même groupe d'un coup. */
    public function completeBulk(Request $request)
    {
        $driver = $this->myDriver();
        $user   = Auth::user();
        $ids    = $request->validate(['order_ids' => 'required|array|min:1'])['order_ids'];

        $orders = Order::with(['payment'])->whereIn('id', $ids)->get();

        DB::beginTransaction();
        try {
            // Mémoriser les batch_id déjà traités pour n'émettre qu'une commission par trajet groupé
            $seenBatches = [];

            foreach ($orders as $order) {
                if ($order->livreur_id !== $user->id && (!$driver || (int)$order->driver_id !== $driver->id)) continue;
                $order->status = Order::STATUS_LIVREE;
                $order->save();
                if ($order->payment) { $order->payment->status = 'payé'; $order->payment->save(); }

                // Pour un batch : une seule commission pour tout le trajet groupé
                $batchId = $order->delivery_batch_id;
                if ($batchId) {
                    if (isset($seenBatches[$batchId])) continue; // déjà créée pour ce batch
                    $alreadyExists = CourierCommission::where('delivery_batch_id', $batchId)->exists();
                    if ($alreadyExists) { $seenBatches[$batchId] = true; continue; }
                    $seenBatches[$batchId] = true;
                } else {
                    if (CourierCommission::where('order_id', $order->id)->exists()) continue;
                }

                CourierCommission::create([
                    'order_id'          => $order->id,
                    'livreur_id'        => $order->livreur_id ?? Auth::id(),
                    'shop_id'           => $order->shop_id,
                    'delivery_batch_id' => $batchId,
                    'order_total'       => $order->total,
                    'rate'              => 0,
                    'amount'            => (float)($order->delivery_fee) ?: ((float)(\App\Models\DeliveryZone::find($order->delivery_zone_id)?->price) ?: 0),
                    'status'            => 'en_attente',
                ]);
            }
            if ($driver) $driver->update(['status' => $user->is_available ? 'available' : 'offline']);
            DB::commit();
            try {
                foreach ($orders as $order) {
                    $order->client?->notify(new OrderStatusNotification($order, '✅ Votre commande a été livrée avec succès.'));
                }
            } catch (\Throwable $e) {}
            return redirect()->route('livreur.orders.index')->with('success', 'Commandes livrées avec succès.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
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

            // Créer une commission si elle n'existe pas encore.
            // Pour un batch groupé : une seule commission pour tout le trajet.
            // Pour une commande individuelle : une commission (montant 0 si pas de delivery_fee — sera saisi manuellement).
            $batchId = $order->delivery_batch_id;
            $commExists = $batchId
                ? CourierCommission::where('delivery_batch_id', $batchId)->exists()
                : CourierCommission::where('order_id', $order->id)->exists();

            if (! $commExists) {
                // Montant : frais livraison > prix zone > 0 (saisie manuelle ensuite)
                $commAmount = (float)($order->delivery_fee) ?: ((float)(\App\Models\DeliveryZone::find($order->delivery_zone_id)?->price) ?: 0);
                CourierCommission::create([
                    'order_id'           => $order->id,
                    'livreur_id'         => $order->livreur_id ?? Auth::id(),
                    'shop_id'            => $order->shop_id,
                    'delivery_batch_id'  => $batchId,
                    'order_total'        => $order->total,
                    'rate'               => 0,
                    'amount'             => $commAmount,
                    'status'             => 'en_attente',
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
