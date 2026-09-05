<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrderDetailResource;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shop;
use App\Rules\RealisticGuineaPhone;
use App\Services\LoyaltyService;
use App\Services\PromoCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /** GET /api/v1/orders?status=en_attente|confirmée|en_livraison|livrée|annulée */
    public function index(Request $request): JsonResponse
    {
        $user   = $request->user();
        $status = $request->get('status', 'all');

        $query = $user->orders()->with(['shop', 'items', 'review'])->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $orders = $query->paginate(15)->withQueryString();

        return response()->json([
            'data' => OrderResource::collection($orders->items()),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    /** GET /api/v1/orders/{order} */
    public function show(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load(['shop', 'items.product', 'livreur', 'payment']);

        return response()->json(['data' => new OrderDetailResource($order)]);
    }

    /**
     * POST /api/v1/orders/direct — commande immédiate d'un seul produit (bouton
     * "Commander"), distincte du panier. { product_id, variant_id?, quantity,
     * delivery_destination, client_phone, promo_code?, points_to_use? }
     */
    public function storeDirect(Request $request): JsonResponse
    {
        // Commande possible sans compte (invité), comme sur le site : il faut
        // alors le nom complet en plus (pas de profil pour le récupérer).
        $user = $request->user('sanctum');

        $rules = [
            'product_id'           => 'required|exists:products,id',
            'variant_id'           => 'nullable|integer|exists:product_variants,id',
            'quantity'             => 'required|integer|min:1',
            'delivery_destination' => ['required', 'string', 'min:2', 'max:255'],
            'client_phone'         => ['required', 'string', 'max:30', new RealisticGuineaPhone],
            'promo_code'           => ['nullable', 'string', 'max:30'],
            'points_to_use'        => ['nullable', 'integer', 'min:0'],
        ];
        if (!$user) {
            $rules['client_name'] = ['required', 'string', 'max:255', new \App\Rules\RealisticFullName];
        }

        $request->validate($rules, [
            'delivery_destination.required' => "L'adresse de livraison est obligatoire.",
            'delivery_destination.min'      => 'Merci de préciser une adresse plus complète.',
            'client_phone.required'         => 'Le numéro de téléphone est obligatoire.',
            'client_name.required'          => 'Le nom complet est obligatoire.',
        ]);

        $product = Product::with('shop')->findOrFail($request->product_id);
        abort_unless($product->shop && $product->shop->is_approved, 403);

        $variant = null;
        if ($product->has_variants && $request->filled('variant_id')) {
            $variant = $product->variants()->findOrFail($request->variant_id);
            if ($variant->stock < $request->quantity) {
                return response()->json(['message' => "Stock insuffisant pour \"{$variant->name}\". Seulement {$variant->stock} disponible(s)."], 422);
            }
        } elseif (!$product->has_variants && $product->stock !== null && $product->stock < $request->quantity) {
            return response()->json(['message' => "Stock insuffisant. Seulement {$product->stock} disponible(s)."], 422);
        }

        $unitPrice = $variant ? $variant->effective_price : $product->current_price;
        $total     = $unitPrice * $request->quantity;

        $promo         = null;
        $promoDiscount = 0;
        if ($request->filled('promo_code')) {
            $result = app(PromoCodeService::class)->findValid($request->promo_code, $product->shop, $total);
            if (!$result['promo']) {
                return response()->json(['message' => $result['error']], 422);
            }
            $promo         = $result['promo'];
            $promoDiscount = $result['discount'];
            $total         = $total - $promoDiscount;
        }

        // Points fidélité : uniquement pour un client connecté (un invité n'a pas de solde)
        $pointsToUse = 0;
        if ($user) {
            $loyalty     = app(LoyaltyService::class);
            $maxRedeem   = $loyalty->maxRedeemableFor($user, $total);
            $pointsToUse = max(0, min((int) $request->input('points_to_use', 0), $maxRedeem));
            $total       = $total - $pointsToUse;
        }

        $order = Order::create([
            'user_id'              => $user?->id,
            'client_name'          => $user ? null : $request->client_name,
            'shop_id'              => $product->shop->id,
            'total'                => $total,
            'loyalty_points_used'  => $pointsToUse,
            'promo_code_id'        => $promo?->id,
            'discount_amount'      => $promoDiscount,
            'status'               => Order::STATUS_EN_ATTENTE,
            'delivery_destination' => $request->delivery_destination,
            'client_phone'         => $request->client_phone,
        ]);

        if ($user && $pointsToUse > 0) {
            $loyalty->redeemPoints($user, $pointsToUse, $order->id);
        }
        if ($promo) {
            app(PromoCodeService::class)->redeem($promo);
        }

        OrderItem::create([
            'order_id'           => $order->id,
            'product_id'         => $product->id,
            'product_variant_id' => $variant?->id,
            'variant_name'       => $variant?->name,
            'quantity'           => $request->quantity,
            'price'              => $unitPrice,
        ]);

        if ($variant) {
            $variant->decrement('stock', $request->quantity);
        } elseif (!$product->has_variants && $product->stock !== null) {
            $product->decrement('stock', $request->quantity);
        }

        Payment::create([
            'order_id' => $order->id,
            'method'   => 'cash',
            'amount'   => $total,
            'status'   => 'en_attente',
        ]);

        try {
            $push = app(\App\Services\PushService::class);
            $shopOwner = $product->shop->user;
            if ($shopOwner) {
                $push->sendToUser($shopOwner, 'Nouvelle commande !', $product->name . ' — ' . number_format($total, 0, ',', ' ') . ' GNF', $push->vendorBadgeCount($shopOwner), '/employe/orders');
            }
            $push->notifyShopStaff($product->shop, 'Nouvelle commande !', $product->name . ' — ' . number_format($total, 0, ',', ' ') . ' GNF', '/employe/orders', $product->shop->user_id);
        } catch (\Throwable $e) {}

        return response()->json([
            'message'  => 'Commande passée avec succès !',
            'order_id' => $order->id,
        ], 201);
    }

    /** POST /api/v1/orders/check-promo — { code, shop_id, subtotal } */
    public function checkPromoCode(Request $request): JsonResponse
    {
        $request->validate([
            'code'     => 'required|string|max:30',
            'shop_id'  => 'required|exists:shops,id',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $shop   = Shop::findOrFail($request->shop_id);
        $result = app(PromoCodeService::class)->findValid($request->code, $shop, (float) $request->subtotal);

        if (!$result['promo']) {
            return response()->json(['valid' => false, 'message' => $result['error']]);
        }

        return response()->json(['valid' => true, 'discount' => $result['discount']]);
    }
}
