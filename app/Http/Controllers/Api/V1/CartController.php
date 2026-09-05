<?php

namespace App\Http\Controllers\Api\V1;

// ─────────────────────────────────────────────────────────────────────────────
// CartController (API v1) — reprend exactement la même logique que
// Client\CartController côté web (panier multi-boutiques, découpé en une
// commande par boutique au moment de valider), adaptée en JSON pour Flutter.
// ─────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CartItemResource;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Rules\RealisticGuineaPhone;
use App\Services\PushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /** GET /api/v1/cart */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = CartItem::where('user_id', $user->id)
            ->with(['product.shop', 'variant'])
            ->latest()
            ->get()
            ->filter(fn ($i) => $i->product && $i->product->is_active && optional($i->product->shop)->is_approved)
            ->values();

        $groups = $items->groupBy(fn ($i) => $i->product->shop_id)->map(function ($group) {
            return [
                'shop'     => [
                    'id'   => $group->first()->product->shop->id,
                    'name' => $group->first()->product->shop->name,
                ],
                'items'    => CartItemResource::collection($group->values()),
                'subtotal' => $group->sum(fn ($i) => $i->subtotal),
            ];
        })->values();

        return response()->json([
            'data' => [
                'groups'      => $groups,
                'grand_total' => $items->sum(fn ($i) => $i->subtotal),
                'count'       => (int) $items->sum('quantity'),
            ],
        ]);
    }

    /** POST /api/v1/cart/items — { product_id, variant_id?, quantity? } */
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'quantity'   => 'nullable|integer|min:1|max:99',
        ]);

        $product = Product::findOrFail($request->product_id);
        abort_unless(optional($product->shop)->is_approved, 404);

        $qty = (int) ($request->input('quantity', 1) ?: 1);

        $variant = null;
        if ($product->has_variants && $request->filled('variant_id')) {
            $variant = $product->variants()->findOrFail($request->variant_id);
        }

        $stock = $variant ? $variant->stock : $product->stock;
        if ($stock !== null && $stock <= 0) {
            return response()->json(['message' => 'Ce produit est en rupture de stock.'], 422);
        }

        $user = $request->user();

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

    /** PATCH /api/v1/cart/items/{item} — { quantity } */
    public function updateQuantity(Request $request, CartItem $item): JsonResponse
    {
        abort_unless($item->user_id === $request->user()->id, 403);

        $request->validate(['quantity' => 'required|integer|min:1|max:99']);

        $qty = (int) $request->quantity;
        if ($item->available_stock !== null) {
            $qty = min($qty, max(1, $item->available_stock));
        }
        $item->update(['quantity' => $qty]);
        $item->refresh();

        $cartCount = CartItem::where('user_id', $request->user()->id)->sum('quantity');

        return response()->json([
            'quantity'   => $item->quantity,
            'line_total' => $item->subtotal,
            'cart_count' => (int) $cartCount,
        ]);
    }

    /** DELETE /api/v1/cart/items/{item} */
    public function remove(Request $request, CartItem $item): JsonResponse
    {
        abort_unless($item->user_id === $request->user()->id, 403);
        $item->delete();

        $cartCount = CartItem::where('user_id', $request->user()->id)->sum('quantity');

        return response()->json(['cart_count' => (int) $cartCount]);
    }

    /** POST /api/v1/cart/checkout — { delivery_destination, client_phone } */
    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'delivery_destination' => ['required', 'string', 'min:2', 'max:255'],
            'client_phone'         => ['required', 'string', 'max:30', new RealisticGuineaPhone],
        ], [
            'delivery_destination.required' => "L'adresse de livraison est obligatoire.",
            'delivery_destination.min'      => 'Merci de préciser une adresse plus complète.',
            'client_phone.required'         => 'Le numéro de téléphone est obligatoire.',
        ]);

        $user = $request->user();

        $items = CartItem::where('user_id', $user->id)
            ->with(['product.shop', 'variant'])
            ->get()
            ->filter(fn ($i) => $i->product && $i->product->is_active && optional($i->product->shop)->is_approved);

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Votre panier est vide.'], 422);
        }

        foreach ($items as $item) {
            if ($item->available_stock !== null && $item->quantity > $item->available_stock) {
                return response()->json([
                    'message' => "Stock insuffisant pour \"{$item->product->name}\" (il en reste {$item->available_stock}).",
                ], 422);
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

                $group->each(fn ($i) => $i->delete());
            }
        });

        return response()->json([
            'message'    => count($createdOrders) > 1
                ? count($createdOrders) . ' commandes passées avec succès (une par boutique) !'
                : 'Commande passée avec succès !',
            'order_ids'  => $createdOrders,
        ], 201);
    }
}
