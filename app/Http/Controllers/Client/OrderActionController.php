<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PushService;
use App\Support\Storefront;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** Actions du client sur ses propres commandes : annuler avant confirmation, commander à nouveau. */
class OrderActionController extends Controller
{
    public const CANCEL_REASONS = [
        'changed_mind' => "J'ai changé d'avis",
        'mistake'      => 'Erreur dans ma commande',
        'too_long'     => 'Le délai est trop long',
        'cheaper'      => "J'ai trouvé moins cher ailleurs",
        'other'        => 'Autre raison',
    ];

    /** Commandes livrées ensemble (même trajet) appartenant au client, ou la commande seule. */
    private function groupOf(Order $order)
    {
        return $order->delivery_batch_id
            ? Order::where('delivery_batch_id', $order->delivery_batch_id)->where('user_id', Auth::id())->get()
            : $order->newCollection([$order]);
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless((int) $order->user_id === (int) Auth::id(), 403);

        $data = $request->validate([
            'reason' => ['required', 'in:' . implode(',', array_keys(self::CANCEL_REASONS))],
        ], ['reason.required' => "Choisissez la raison de l'annulation."]);

        $orders = $this->groupOf($order)->load(['items.product', 'items.variant', 'payment', 'shop.user']);

        if ($orders->contains(fn (Order $o) => $o->status !== Order::STATUS_EN_ATTENTE)) {
            return back()->with('warning', 'La boutique a déjà confirmé cette commande. Écrivez-lui pour demander une annulation.');
        }
        if ($orders->contains(fn (Order $o) => $o->payment && $o->payment->method !== Payment::METHOD_CASH && $o->payment->status === Payment::STATUS_PAYE)) {
            return back()->with('warning', 'Cette commande a été payée en ligne. Écrivez à la boutique pour l\'annuler et être remboursé.');
        }

        DB::transaction(function () use ($orders, $data) {
            foreach ($orders as $o) {
                // Les articles réservés reviennent en stock
                foreach ($o->items as $item) {
                    if ($item->variant && $item->variant->stock !== null) {
                        $item->variant->increment('stock', $item->quantity);
                    } elseif (! $item->product_variant_id && $item->product && $item->product->stock !== null) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }
                $o->update([
                    'status'        => Order::STATUS_ANNULEE,
                    'cancel_reason' => self::CANCEL_REASONS[$data['reason']],
                    'cancelled_by'  => 'client',
                ]);
            }
        });

        // Prévenir chaque boutique concernée
        foreach ($orders as $o) {
            if (! $o->shop) continue;
            $title = 'Commande annulée par le client';
            $body  = 'Commande #' . str_pad($o->id, 4, '0', STR_PAD_LEFT) . ' — ' . self::CANCEL_REASONS[$data['reason']];
            try {
                $push = app(PushService::class);
                if ($o->shop->user) $push->sendToUser($o->shop->user, $title, $body, $push->vendorBadgeCount($o->shop->user), '/employe/orders');
                $push->notifyShopStaff($o->shop, $title, $body, '/employe/orders', $o->shop->user_id);
            } catch (\Throwable $e) {}
        }

        return back()->with('success', $orders->count() > 1 ? 'Vos commandes ont été annulées.' : 'Votre commande a été annulée.');
    }

    public function reorder(Order $order)
    {
        abort_unless((int) $order->user_id === (int) Auth::id(), 403);

        $orders  = $this->groupOf($order)->load(['items.product.shop', 'items.variant']);
        $added   = 0;
        $missing = [];

        foreach ($orders->flatMap->items as $item) {
            $product = $item->product;
            $name    = $product->name ?? 'Un produit';

            $available = $product
                && $product->is_active
                && $product->is_available !== false
                && optional($product->shop)->is_approved
                && ! Storefront::onVacation($product->shop);
            $variant = null;
            if ($available && $item->product_variant_id) {
                $variant   = $item->variant;
                $available = $variant && $variant->is_active;
            }
            $stock = $variant ? $variant->stock : $product?->stock;
            if (! $available || ($stock !== null && $stock <= 0)) {
                $missing[] = $name;
                continue;
            }

            $line = CartItem::firstOrNew([
                'user_id'            => Auth::id(),
                'product_id'         => $product->id,
                'product_variant_id' => $variant?->id,
            ]);
            $qty = ($line->exists ? $line->quantity : 0) + $item->quantity;
            $line->quantity = $stock !== null ? min($qty, $stock) : $qty;
            $line->save();
            $added++;
        }

        if ($added === 0) {
            return back()->with('warning', 'Ces produits ne sont plus disponibles pour le moment.');
        }

        $redirect = redirect()->route('client.cart.index')
            ->with('success', $added . ' produit' . ($added > 1 ? 's' : '') . ' ajouté' . ($added > 1 ? 's' : '') . ' au panier, au prix actuel.');

        return $missing
            ? $redirect->with('warning', 'Plus disponible : ' . implode(', ', array_unique($missing)) . '.')
            : $redirect;
    }
}
