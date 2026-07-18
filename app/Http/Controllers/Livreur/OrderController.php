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
use App\Services\PushService;

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
        $driver = $this->myDriver();
        $devise = $shop?->currency ?? $driver?->company?->currency ?? 'GNF';

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
            // Clé d'appartenance :
            //   batch_id présent → même lot intentionnel
            //   livreur entreprise (driver_id) sans batch → auto-grouper par client + boutique
            //     même client + même boutique = même trajet = 1 seule carte
            //   livreur boutique sans batch → solo par commande
            if ($order->delivery_batch_id) {
                $key = 'batch_' . $order->delivery_batch_id;
            } elseif ($order->driver_id) {
                // Livreur entreprise : même client + même boutique = 1 trajet
                $key = 'drv_u' . ($order->user_id ?? 'x') . '_s' . ($order->shop_id ?? '0');
            } elseif ($order->livreur_id) {
                // Livreur boutique : même client + même boutique = 1 trajet
                $key = 'liv_u' . ($order->user_id ?? 'x') . '_s' . ($order->shop_id ?? '0');
            } else {
                $key = '__solo__' . $order->id;
            }

            if (!isset($map[$key])) {
                $map[$key] = count($result);
                $result[] = [
                    'is_group'     => false, // devient true si une 2ème commande rejoint le même batch
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
                // Client différent du groupe → sa fee s'ajoute (chaque client paie sa livraison)
                $knownUserIds = array_map(fn($o) => $o->user_id, $result[$idx]['orders']);
                if (!in_array($order->user_id, $knownUserIds)) {
                    $result[$idx]['delivery_fee'] += (float)($order->delivery_fee ?? 0);
                }
                $result[$idx]['orders'][]  = $order;
                $result[$idx]['total']    += (float)$order->total;
                $cur = $statusPriority[$result[$idx]['status']] ?? 99;
                $new = $statusPriority[$order->status] ?? 99;
                if ($new < $cur) {
                    $result[$idx]['status'] = $order->status;
                }
            }
        }

        // Optimisation de tournée : pour les trajets à plusieurs arrêts, on réordonne
        // les commandes du plus proche au plus loin (au lieu de l'ordre d'arrivée des commandes),
        // pour minimiser la distance totale parcourue par le livreur.
        foreach ($result as &$group) {
            if (count($group['orders']) > 1) {
                $group['orders'] = $this->optimizeRouteOrder($group['orders']);
            }
        }
        unset($group);

        return $result;
    }

    /**
     * Réordonne une liste de commandes d'un même trajet groupé selon l'algorithme
     * du "plus proche voisin" : depuis le point de départ (la boutique), on va toujours
     * à l'arrêt le plus proche restant, puis au suivant le plus proche de là, etc.
     * Ne fait rien si une commande du groupe n'a pas de coordonnées GPS client
     * (on ne touche pas à l'ordre si on n'est pas sûr de la distance réelle).
     */
    private function optimizeRouteOrder(array $orders): array
    {
        foreach ($orders as $o) {
            if (is_null($o->client_lat) || is_null($o->client_lng)) {
                return $orders;
            }
        }

        $first  = $orders[0];
        $curLat = $first->vendor_lat ?? $first->client_lat;
        $curLng = $first->vendor_lng ?? $first->client_lng;

        $remaining = $orders;
        $sequenced = [];

        while (count($remaining) > 0) {
            usort($remaining, function ($a, $b) use ($curLat, $curLng) {
                return $this->haversineKm($curLat, $curLng, $a->client_lat, $a->client_lng)
                     <=> $this->haversineKm($curLat, $curLng, $b->client_lat, $b->client_lng);
            });
            $next = array_shift($remaining);
            $sequenced[] = $next;
            $curLat = $next->client_lat;
            $curLng = $next->client_lng;
        }

        return $sequenced;
    }

    /** Distance à vol d'oiseau entre deux points GPS, en kilomètres (formule de Haversine). */
    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadiusKm * $c;
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
            if ($order->client) {
                try {
                    app(PushService::class)->sendToUser(
                        $order->client,
                        'Commande en route 🚚',
                        'Votre commande #' . str_pad($order->id, 4, '0', STR_PAD_LEFT) . ' est en cours de livraison !',
                        1,
                        '/client/orders'
                    );
                } catch (\Throwable $e) {}
            }
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
        $data   = $request->validate([
            'order_ids'   => 'required|array|min:1',
            'proof_photo' => ['nullable', 'image', 'mimes:jpeg,png,webp,gif', 'max:8192'],
        ]);
        $ids = $data['order_ids'];

        // Une seule photo prise pour tout le trajet groupé (même client) → appliquée à chaque commande du lot
        $proofPhotoPath = $request->hasFile('proof_photo')
            ? \App\Services\ImageOptimizer::store($request->file('proof_photo'), 'delivery_proofs')
            : null;

        $orders = Order::with(['payment'])->whereIn('id', $ids)->get();

        DB::beginTransaction();
        try {
            // Mémoriser les batch_id déjà traités pour n'émettre qu'une commission par trajet groupé
            $seenBatches = [];

            foreach ($orders as $order) {
                if ($order->livreur_id !== $user->id && (!$driver || (int)$order->driver_id !== $driver->id)) continue;
                $order->status = Order::STATUS_LIVREE;
                if ($proofPhotoPath) {
                    $order->delivery_proof_photo = $proofPhotoPath;
                }
                $order->save();
                if ($order->payment) {
                    $order->payment->update(['status' => 'payé']);
                } else {
                    Payment::create(['order_id' => $order->id, 'method' => 'cash', 'amount' => $order->total, 'status' => 'payé']);
                }

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

                // Multi-client dans le même batch → sommer les fees de chaque commande
                if ($batchId) {
                    $batchOrders   = Order::where('delivery_batch_id', $batchId)->get();
                    $isMultiClient = $batchOrders->pluck('user_id')->unique()->count() > 1;
                    $commAmount    = $isMultiClient
                        ? ((float)$batchOrders->sum('delivery_fee') ?: (float)(\App\Models\DeliveryZone::find($order->delivery_zone_id)?->price) ?: 0)
                        : ((float)($order->delivery_fee) ?: (float)(\App\Models\DeliveryZone::find($order->delivery_zone_id)?->price) ?: 0);
                } else {
                    $commAmount = (float)($order->delivery_fee) ?: ((float)(\App\Models\DeliveryZone::find($order->delivery_zone_id)?->price) ?: 0);
                }

                CourierCommission::create([
                    'order_id'          => $order->id,
                    'livreur_id'        => $order->livreur_id ?? Auth::id(),
                    'shop_id'           => $order->shop_id,
                    'delivery_batch_id' => $batchId,
                    'order_total'       => $order->total,
                    'rate'              => 0,
                    'amount'            => $commAmount,
                    'status'            => 'en_attente',
                ]);
            }
            if ($driver) $driver->update(['status' => $user->is_available ? 'available' : 'offline']);
            DB::commit();
            foreach ($orders as $order) {
                if (!$order->client) continue;
                try {
                    app(PushService::class)->sendToUser(
                        $order->client,
                        'Commande livrée ✅',
                        'Votre commande #' . str_pad($order->id, 4, '0', STR_PAD_LEFT) . ' a été livrée avec succès !',
                        1,
                        '/client/orders'
                    );
                } catch (\Throwable $e) {}
            }

            // Objectif journalier (gamification) : vérifie si le livreur vient de débloquer sa prime du jour
            try {
                if (app(\App\Services\GamificationService::class)->checkAndAwardDailyBonus($user, $driver)) {
                    session()->flash('daily_bonus_unlocked', true);
                }
            } catch (\Throwable $e) {}

            return redirect()->route('livreur.orders.index')->with('success', 'Commandes livrées avec succès.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Regroupe plusieurs commandes solo dans un batch commun puis redirige vers la navigation.
     * Utilisé quand plusieurs commandes d'un même client sont affichées en une seule carte.
     */
    public function autoBatchNav(Request $request)
    {
        $driver = $this->myDriver();
        $user   = Auth::user();
        $ids    = $request->validate(['order_ids' => 'required|array|min:1'])['order_ids'];

        $orders = Order::whereIn('id', $ids)->get();

        foreach ($orders as $order) {
            if ($order->livreur_id !== $user->id && (!$driver || (int)$order->driver_id !== $driver->id)) {
                abort(403);
            }
        }

        // Créer un batch_id commun si les commandes n'en ont pas encore
        $batchId = $orders->first()?->delivery_batch_id;
        if (!$batchId) {
            $batchId = (string)\Illuminate\Support\Str::uuid();
            Order::whereIn('id', $ids)->whereNull('delivery_batch_id')->update(['delivery_batch_id' => $batchId]);
        }

        return redirect()->route('orders.nav', $orders->first());
    }

    /**
     * Livreur boutique : démarre toutes les commandes du groupe (en_livraison) et redirige
     * directement vers la Phase 2 (client) — pas de Phase 1, il est déjà à la boutique.
     */
    public function startBulkAndNav(Request $request)
    {
        $driver = $this->myDriver();
        $user   = Auth::user();
        $ids    = $request->validate(['order_ids' => 'required|array|min:1'])['order_ids'];

        $orders = Order::whereIn('id', $ids)->get();

        foreach ($orders as $order) {
            if ($order->livreur_id !== $user->id && (!$driver || (int)$order->driver_id !== $driver->id)) {
                abort(403);
            }
        }

        // Créer un batch commun si plusieurs commandes sans batch
        $batchId = $orders->first()?->delivery_batch_id;
        if (!$batchId && $orders->count() > 1) {
            $batchId = (string)\Illuminate\Support\Str::uuid();
            Order::whereIn('id', $ids)->whereNull('delivery_batch_id')->update(['delivery_batch_id' => $batchId]);
        }

        // Passer toutes en en_livraison → Phase 2 s'affiche directement dans nav.blade.php
        foreach ($orders as $order) {
            if ($order->status === Order::STATUS_CONFIRMEE) {
                $order->status = Order::STATUS_EN_LIVRAISON;
                $order->save();
                if ($order->client) {
                    try {
                        app(PushService::class)->sendToUser(
                            $order->client,
                            'Commande en route 🚚',
                            'Votre commande #' . str_pad($order->id, 4, '0', STR_PAD_LEFT) . ' est en cours de livraison !',
                            1,
                            '/client/orders'
                        );
                    } catch (\Throwable $e) {}
                }
            }
        }

        if ($driver) $driver->update(['status' => 'busy']);

        return redirect()->route('orders.nav', $orders->first());
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

        if ($order->client) {
            try {
                app(PushService::class)->sendToUser(
                    $order->client,
                    'Commande en route 🚚',
                    'Votre commande #' . str_pad($order->id, 4, '0', STR_PAD_LEFT) . ' est en cours de livraison !',
                    1,
                    '/client/orders'
                );
            } catch (\Throwable $e) {
                Log::warning('Push start order failed: ' . $e->getMessage());
            }
        }

        session()->flash('autostart_gps_order_id', $order->id);

        return redirect()->route('livreur.orders.index')->with('success', 'Livraison démarrée !');
    }

    /**
     * Livreur termine la livraison → statut "livrée", chauffeur → available.
     */
    public function complete(Request $request, Order $order)
    {
        $driver = $this->authorizeOrder($order);

        $request->validate([
            'proof_photo' => ['nullable', 'image', 'mimes:jpeg,png,webp,gif', 'max:8192'],
        ]);

        DB::beginTransaction();

        try {
            $order->status = Order::STATUS_LIVREE;
            if ($request->hasFile('proof_photo')) {
                $order->delivery_proof_photo = \App\Services\ImageOptimizer::store($request->file('proof_photo'), 'delivery_proofs');
            }
            $order->save();

            if ($order->payment) {
                $order->payment->update(['status' => 'payé']);
            } else {
                Payment::create(['order_id' => $order->id, 'method' => 'cash', 'amount' => $order->total, 'status' => 'payé']);
            }

            // Créer une commission si elle n'existe pas encore.
            // Pour un batch groupé : une seule commission pour tout le trajet.
            // Pour une commande individuelle : une commission (montant 0 si pas de delivery_fee — sera saisi manuellement).
            $batchId = $order->delivery_batch_id;
            $commExists = $batchId
                ? CourierCommission::where('delivery_batch_id', $batchId)->exists()
                : CourierCommission::where('order_id', $order->id)->exists();

            if (! $commExists) {
                // Multi-client dans le même batch → sommer les fees de chaque commande
                if ($batchId) {
                    $batchOrders   = Order::where('delivery_batch_id', $batchId)->get();
                    $isMultiClient = $batchOrders->pluck('user_id')->unique()->count() > 1;
                    $commAmount    = $isMultiClient
                        ? ((float)$batchOrders->sum('delivery_fee') ?: (float)(\App\Models\DeliveryZone::find($order->delivery_zone_id)?->price) ?: 0)
                        : ((float)($order->delivery_fee) ?: (float)(\App\Models\DeliveryZone::find($order->delivery_zone_id)?->price) ?: 0);
                } else {
                    $commAmount = (float)($order->delivery_fee) ?: ((float)(\App\Models\DeliveryZone::find($order->delivery_zone_id)?->price) ?: 0);
                }
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

            if ($order->client) {
                try {
                    app(PushService::class)->sendToUser(
                        $order->client,
                        'Commande livrée ✅',
                        'Votre commande #' . str_pad($order->id, 4, '0', STR_PAD_LEFT) . ' a été livrée avec succès !',
                        1,
                        '/client/orders'
                    );
                } catch (\Throwable $e) {
                    Log::warning('Push complete order failed: ' . $e->getMessage());
                }
            }

            // Objectif journalier (gamification) : vérifie si le livreur vient de débloquer sa prime du jour
            try {
                if (app(\App\Services\GamificationService::class)->checkAndAwardDailyBonus(Auth::user(), $driver)) {
                    session()->flash('daily_bonus_unlocked', true);
                }
            } catch (\Throwable $e) {}

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

    public function carte(Order $order)
    {
        $this->authorizeOrder($order);
        $order->load(['client', 'shop', 'items.product']);
        return view('livreur.orders.carte', compact('order'));
    }
}
