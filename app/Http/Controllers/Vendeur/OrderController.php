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
        'livreur_id' => ['required', 'exists:users,id'],
    ]);

    // Vérifier que le livreur appartient bien à la boutique
    $livreur = User::where('id', $data['livreur_id'])
        ->where('role', 'livreur')
        ->where('shop_id', $shopId)
        ->firstOrFail();

    // ⚡ On assigne uniquement le livreur
    $order->livreur_id = $livreur->id;
    $order->save();

    // notifier le livreur (optionnel)
    if (method_exists($livreur, 'notify')) {
        try {
            $livreur->notify(new OrderStatusNotification($order, 'Une commande vous a été assignée.'));
        } catch (\Throwable $e) {
            // ignore si notification non configurée
        }
    }

    // Vérifier si la requête est AJAX (fetch)
    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'livreur_name' => $livreur->name,
        ]);
    }

    // fallback pour navigation classique
    return redirect()->route('vendeur.orders.index')
        ->with('success', 'Commande assignée à ' . $livreur->name . '.');
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
