<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\OrderStatusNotification;

class OrderController extends Controller
{
    /**
     * Liste des commandes de MA boutique uniquement
     */
    public function index()
    {
        // Supporte: propriétaire (->shop) ou employé rattaché (->assignedShop via shop_id)
        $user   = Auth::user();
        $shop   = $user->shop ?: $user->assignedShop;   // <= clé de l’isolation
        $shopId = $shop?->id;

        if (!$shopId) {
            return redirect()->route('shop.index')
                ->with('error', 'Aucune boutique rattachée à ce compte.');
        }

        $orders = Order::with(['items.product', 'client', 'livreur'])
            ->where('shop_id', $shopId)               // <= filtre strict
            ->latest()
            ->paginate(10);

        return view('vendeur.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $user   = Auth::user();
        $shop   = $user->shop ?: $user->assignedShop;
        $shopId = $shop?->id;

        abort_unless($shopId && $order->shop_id === $shopId, 403, 'Action non autorisée.');

        $order->load(['items.product', 'client', 'livreur', 'payment', 'commission']);

        return view('vendeur.orders.show', compact('order', 'shop'));
    }

    /**
     * Confirmer une commande de MA boutique
     * -> envoie vers l'écran d'assignation pour choisir un livreur
     */
    public function confirm(Order $order)
    {
        $user   = Auth::user();
        $shop   = $user->shop ?: $user->assignedShop;
        $shopId = $shop?->id;

        abort_unless($shopId && $order->shop_id === $shopId, 403, 'Action non autorisée.');

        // Confirmer une commande
        if ($order->status !== Order::STATUS_EN_ATTENTE) {
            return back()->with('warning', 'Cette commande ne peut pas être confirmée.');
        }

        $order->update(['status' => Order::STATUS_CONFIRMEE]);

        // Redirection vers la page d'assignation (où tu verras les livreurs)
        return redirect()->route('orders.assign.show', $order->id)
            ->with('success', 'Commande confirmée — assignez un livreur.');
    }

    /**
     * Afficher la page d'assignation pour une commande (liste des livreurs)
     */
    public function showAssign(Order $order)
    {
        $user   = Auth::user();
        $shop   = $user->shop ?: $user->assignedShop;
        $shopId = $shop?->id;

        abort_unless($shopId && $order->shop_id === $shopId, 403, 'Action non autorisée.');

        // Récupère les livreurs rattachés à la boutique
        // ⚠️ Adapte 'shop_id' / 'is_available' si tes colonnes ont un autre nom
        $livreurs = User::query()
            ->where('role', 'livreur')
            ->where('shop_id', $shopId)
            ->orderByDesc('is_available') // disponibles en haut
            ->orderBy('name')
            ->get();

        return view('vendeur.orders.assign', compact('order', 'livreurs'));
    }

    /**
     * Assignation du livreur à la commande (soumission depuis la page d'assignation)
     */
   public function assign(Request $request, Order $order)
{
    $user   = Auth::user();
    $shop   = $user->shop ?: $user->assignedShop;
    $shopId = $shop?->id;

    abort_unless($shopId && $order->shop_id === $shopId, 403, 'Action non autorisée.');

    $data = $request->validate([
        'livreur_id'          => ['required', 'exists:users,id'],
        'delivery_fee'        => ['required', 'numeric', 'min:0'],
        'delivery_destination'=> ['nullable', 'string', 'max:255'],
    ], [
        'delivery_fee.required' => 'Le frais de livraison est obligatoire.',
        'delivery_fee.min'      => 'Le frais doit être un montant positif.',
    ]);

    // Vérifier que le livreur appartient bien à la boutique
    $livreur = User::where('id', $data['livreur_id'])
        ->where('role', 'livreur')
        ->where('shop_id', $shopId)
        ->firstOrFail();

    // Assigner le livreur + enregistrer frais et destination
    $order->update([
        'livreur_id'           => $livreur->id,
        'delivery_fee'         => $data['delivery_fee'],
        'delivery_destination' => $data['delivery_destination'] ?? null,
    ]);

    // Créer ou mettre à jour la commission avec le montant fixe
    \App\Models\CourierCommission::updateOrCreate(
        ['order_id' => $order->id],
        [
            'livreur_id'  => $livreur->id,
            'shop_id'     => $order->shop_id,
            'order_total' => $order->total,
            'rate'        => 0,
            'amount'      => $data['delivery_fee'],
            'status'      => 'en_attente',
        ]
    );

    // Notifier le livreur
    if (method_exists($livreur, 'notify')) {
        try {
            $livreur->notify(new OrderStatusNotification($order, 'Une commande vous a été assignée.'));
        } catch (\Throwable $e) {}
    }

    if ($request->ajax()) {
        return response()->json([
            'success'      => true,
            'livreur_name' => $livreur->name,
            'delivery_fee' => $data['delivery_fee'],
        ]);
    }

    return redirect()->route('vendeur.orders.index')
        ->with('success', 'Commande assignée à ' . $livreur->name . ' · Frais : ' . number_format($data['delivery_fee'], 0, ',', ' ') . ' ' . ($shop?->currency ?? 'GNF') . '.');
}


    /**
     * Annuler une commande de MA boutique
     */
    public function cancel(Order $order)
    {
        $user   = Auth::user();
        $shop   = $user->shop ?: $user->assignedShop;
        $shopId = $shop?->id;

        abort_unless($shopId && $order->shop_id === $shopId, 403, 'Action non autorisée.');

        // Annuler une commande
        if (!in_array($order->status, [Order::STATUS_EN_ATTENTE, Order::STATUS_CONFIRMEE], true)) {
            return back()->with('warning', 'Cette commande ne peut pas être annulée.');
        }

        $order->update(['status' => Order::STATUS_ANNULEE]);

        return back()->with('success', 'Commande annulée.');
    }
}
