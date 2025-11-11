<?php

namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
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
     * Liste des commandes assignées au livreur connecté
     */
    public function index()
    {
        // Récupère toutes les commandes assignées au livreur actuellement connecté,
        // triées par date décroissante et paginées.
        $orders = Order::where('livreur_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('livreur.orders.index', compact('orders'));
    }

    /**
     * Marque la commande comme "en cours de livraison" (start tracking / commencer)
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function start(Order $order)
    {
        // Sécurité : vérifier que le livreur actuel est bien assigné à la commande
        if ($order->livreur_id !== Auth::id()) {
            abort(403, 'Accès interdit');
        }

        // Change le statut de la commande à "en_livraison"
        $order->status = 'en_livraison';
        $order->save();

        // Notification optionnelle au client pour l'informer
        try {
            if ($order->client) {
                $order->client->notify(new OrderStatusNotification(
                    $order,
                    '🚚 Votre commande est en cours de livraison.'
                ));
            }
        } catch (\Throwable $e) {
            // On n'interrompt pas le flux si la notification échoue, mais on logge l'erreur.
            Log::warning('Notification start order failed: ' . $e->getMessage());
        }

        // Indicateur de session pour auto-démarrer le GPS côté front si nécessaire
        session()->flash('autostart_gps_order_id', $order->id);

        return redirect()->route('livreur.orders.index')->with('success', 'Commande démarrée : suivi actif.');
    }

    /**
     * Marque la commande comme "livrée" et crée la commission livreur + met à jour le paiement.
     *
     * Utilise une transaction pour s'assurer que les étapes critiques (paiement, commission)
     * soient atomiques.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(Order $order)
    {
        // Sécurité : vérifier que le livreur actuel est bien assigné à la commande
        if ($order->livreur_id !== Auth::id()) {
            abort(403, 'Accès interdit');
        }

        // Commence la transaction DB
        DB::beginTransaction();

        try {
            // 1) Mettre à jour le statut de la commande
            // NOTE: la valeur 'livrée' doit exister/être acceptée par ta colonne status
            $order->status = 'livrée';
            $order->save();

            // 2) Mettre à jour le paiement associé (si présent)
            // On suppose une relation one-to-one $order->payment
            if ($order->payment) {
                // Utiliser la valeur de statut attendue par la colonne payments.status
                $order->payment->status = 'payé';
                $order->payment->save();
            }

            // 3) Créer la commission du livreur si elle n'existe pas encore
            $exists = CourierCommission::where('order_id', $order->id)->exists();

            if (! $exists) {
                // Récupérer le taux de commission depuis la boutique, sinon utiliser config par défaut
                $rate = $order->shop->commission_rate ?? config('delivery.commission_rate', 0.20);

                // Calculer le montant (arrondi à 2 décimales)
                $amount = round($order->total * $rate, 2);

                // Créer la commission : status 'en_attente' (non payée)
                CourierCommission::create([
                    'order_id'    => $order->id,
                    'livreur_id'  => $order->livreur_id,
                    'shop_id'     => $order->shop_id,
                    'order_total' => $order->total,
                    'rate'        => $rate,
                    'amount'      => $amount,
                    'status'      => 'en_attente',
                ]);
            }

            // Commit si tout s'est bien passé
            DB::commit();

            // 4) Notification finale au client (hors transaction de préférence)
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
            // Rollback en cas d'erreur et loguer pour debug
            DB::rollBack();
            Log::error('Erreur lors du marquage commande comme livrée : ' . $e->getMessage(), [
                'order_id' => $order->id,
                'livreur_id' => Auth::id(),
            ]);

            // Si l'erreur vient d'une valeur non acceptée par la colonne status (ENUM),
            // le message renvoyé aide à diagnostiquer, mais n'affiche pas la stack trace en prod.
            return back()->with('error', "Impossible de marquer la commande comme livrée. Erreur : " . $e->getMessage());
        }
    }
}
