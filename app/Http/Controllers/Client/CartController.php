<?php

// ============================================================
// FICHIER : app/Http/Controllers/Client/CartController.php
// RÔLE    : Panier d'achat multi-boutiques, façon "grand site".
//           Le client ajoute des produits de N'IMPORTE QUELLE boutique
//           dans un seul panier. Au moment de valider, on découpe
//           automatiquement en PLUSIEURS commandes (une par boutique),
//           car le schéma de la base (table "orders") rattache chaque
//           commande à une seule boutique — comme sur un vrai marketplace.
// ============================================================

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Rules\RealisticGuineaPhone;
use App\Services\PushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Ajoute un produit au panier (AJAX). Si la même ligne (produit + variante)
     * existe déjà, on augmente simplement la quantité au lieu de dupliquer.
     */
    public function add(Request $request, Product $product)
    {
        abort_unless(optional($product->shop)->is_approved, 404);

        $request->validate([
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'quantity'   => 'nullable|integer|min:1|max:99',
        ]);

        $qty = (int) ($request->input('quantity', 1) ?: 1);

        $variant = null;
        if ($product->has_variants && $request->filled('variant_id')) {
            $variant = $product->variants()->findOrFail($request->variant_id);
        }

        $stock = $variant ? $variant->stock : $product->stock;
        if ($stock !== null && $stock <= 0) {
            return response()->json(['message' => 'Ce produit est en rupture de stock.'], 422);
        }

        $user = Auth::user();

        $line = CartItem::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if ($line) {
            $newQty = $line->quantity + $qty;
            if ($stock !== null) $newQty = min($newQty, $stock);
            $line->update(['quantity' => $newQty]);
        } else {
            $line = CartItem::create([
                'user_id'            => $user->id,
                'product_id'         => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity'           => $stock !== null ? min($qty, $stock) : $qty,
            ]);
        }

        $count = CartItem::where('user_id', $user->id)->sum('quantity');

        return response()->json([
            'message' => $product->name . ' ajouté au panier.',
            'count'   => (int) $count,
        ]);
    }

    /** Affiche le panier, groupé par boutique. */
    public function index()
    {
        $user = Auth::user();

        $items = CartItem::where('user_id', $user->id)
            ->with(['product.shop', 'variant'])
            ->latest()
            ->get()
            // Un produit supprimé/désactivé ou une boutique désapprouvée entretemps
            // ne doit plus apparaître dans le panier — on nettoie l'affichage sans
            // supprimer la ligne (au cas où le produit redevienne actif).
            ->filter(fn ($i) => $i->product && $i->product->is_active && optional($i->product->shop)->is_approved)
            ->values();

        $groups = $items->groupBy(fn ($i) => $i->product->shop_id)
            ->map(function ($group) {
                return [
                    'shop'     => $group->first()->product->shop,
                    'items'    => $group,
                    'subtotal' => $group->sum(fn ($i) => $i->subtotal),
                ];
            })
            ->values();

        $grandTotal = $groups->sum('subtotal');
        $cartCount  = $items->sum('quantity');

        return view('client.cart.index', compact('groups', 'grandTotal', 'cartCount'));
    }

    /** Modifie la quantité d'une ligne du panier (AJAX). */
    public function updateQuantity(Request $request, CartItem $item)
    {
        abort_unless($item->user_id === Auth::id(), 403);

        $request->validate(['quantity' => 'required|integer|min:1|max:99']);

        $qty = (int) $request->quantity;
        if ($item->available_stock !== null) {
            $qty = min($qty, max(1, $item->available_stock));
        }
        $item->update(['quantity' => $qty]);
        $item->refresh();

        $cartCount = CartItem::where('user_id', Auth::id())->sum('quantity');

        return response()->json([
            'quantity'    => $item->quantity,
            'lineTotal'   => $item->subtotal,
            'cartCount'   => (int) $cartCount,
        ]);
    }

    /** Retire une ligne du panier. */
    public function remove(CartItem $item)
    {
        abort_unless($item->user_id === Auth::id(), 403);
        $item->delete();

        if (request()->ajax() || request()->wantsJson()) {
            $cartCount = CartItem::where('user_id', Auth::id())->sum('quantity');
            return response()->json(['cartCount' => (int) $cartCount]);
        }

        return back()->with('success', 'Produit retiré du panier.');
    }

    /**
     * Valide le panier : une commande est créée PAR BOUTIQUE représentée dans
     * le panier (même principe qu'un marketplace : un même paiement/livraison
     * mais des commandes séparées pour chaque vendeur).
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'delivery_destination' => ['required', 'string', 'min:2', 'max:255'],
            'client_phone'         => ['required', 'string', 'max:30', new RealisticGuineaPhone],
        ], [
            'delivery_destination.required' => "L'adresse de livraison est obligatoire.",
            'delivery_destination.min'      => 'Merci de préciser une adresse plus complète.',
            'client_phone.required'         => 'Le numéro de téléphone est obligatoire.',
        ]);

        $user = Auth::user();

        $items = CartItem::where('user_id', $user->id)
            ->with(['product.shop', 'variant'])
            ->get()
            ->filter(fn ($i) => $i->product && $i->product->is_active && optional($i->product->shop)->is_approved);

        if ($items->isEmpty()) {
            return back()->withErrors(['cart' => 'Votre panier est vide.']);
        }

        // Vérification du stock AVANT de créer quoi que ce soit (pour ne rien commander à moitié).
        foreach ($items as $item) {
            if ($item->available_stock !== null && $item->quantity > $item->available_stock) {
                return back()->withErrors(['cart' => "Stock insuffisant pour \"{$item->product->name}\" (il en reste {$item->available_stock})."]);
            }
        }

        $createdOrders = [];

        DB::transaction(function () use ($items, $request, $user, &$createdOrders) {
            foreach ($items->groupBy(fn ($i) => $i->product->shop_id) as $shopId => $group) {
                $shop  = $group->first()->product->shop;
                $total = $group->sum(fn ($i) => $i->subtotal);

                $order = Order::create([
                    'user_id'              => $user->id,
                    'shop_id'              => $shopId,
                    'total'                => $total,
                    'status'               => Order::STATUS_EN_ATTENTE,
                    'delivery_destination' => $request->delivery_destination,
                    'client_phone'         => $request->client_phone,
                ]);

                foreach ($group as $item) {
                    OrderItem::create([
                        'order_id'           => $order->id,
                        'product_id'         => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'variant_name'       => $item->variant?->name,
                        'quantity'           => $item->quantity,
                        'price'              => $item->unit_price,
                    ]);

                    if ($item->variant) {
                        $item->variant->decrement('stock', $item->quantity);
                    } elseif ($item->product->stock !== null) {
                        $item->product->decrement('stock', $item->quantity);
                    }
                }

                Payment::create([
                    'order_id' => $order->id,
                    'method'   => 'cash',
                    'amount'   => $total,
                    'status'   => 'en_attente',
                ]);

                try {
                    $push = app(PushService::class);
                    $shopOwner = $shop->user ?? null;
                    if ($shopOwner) {
                        $push->sendToUser(
                            $shopOwner,
                            'Nouvelle commande !',
                            $group->count() . ' article(s) — ' . number_format($total, 0, ',', ' ') . ' GNF',
                            $push->vendorBadgeCount($shopOwner),
                            '/employe/orders'
                        );
                    }
                    $push->notifyShopStaff(
                        $shop,
                        'Nouvelle commande !',
                        $group->count() . ' article(s) — ' . number_format($total, 0, ',', ' ') . ' GNF',
                        '/employe/orders',
                        $shop->user_id
                    );
                } catch (\Throwable $e) {}

                $createdOrders[] = $order->id;

                // On retire du panier uniquement les lignes qui viennent d'être commandées.
                $group->each(fn ($i) => $i->delete());
            }
        });

        $count = count($createdOrders);

        return redirect()->route('client.orders.index')->with(
            'success',
            $count > 1
                ? "$count commandes passées avec succès (une par boutique) ! 🎉"
                : 'Commande passée avec succès ! 🎉'
        );
    }
}
