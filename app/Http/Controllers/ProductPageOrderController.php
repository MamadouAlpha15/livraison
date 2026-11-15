<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Rules\RealisticFullName;
use App\Rules\RealisticGuineaPhone;
use App\Services\PushService;
use App\Support\PageModels;
use App\Support\ProductPage;
use App\Support\Storefront;
use App\Support\TrafficSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Commande depuis la page produit (formulaire « Commande maintenant ») : l'offre choisie (prix lu côté
 * serveur dans les réglages du vendeur), payée à la livraison, en espèces. Mêmes contrôles que les autres
 * commandes : boutique vérifiée, mode vacances, stock, nom complet et numéro guinéen pour un invité.
 * Les frais de livraison de la commune (si la boutique les a indiqués) sont notés sur la commande.
 */
class ProductPageOrderController extends Controller
{
    public function store(Product $product, Request $request): JsonResponse
    {
        $shop = $product->shop;
        abort_unless($shop && $shop->is_approved && $product->is_active, 404);
        if (Storefront::onVacation($shop)) {
            return response()->json(['message' => Storefront::vacationMessage($shop)], 423);
        }

        $guest = !Auth::check();
        $data = $request->validate([
            'offer'      => ['required', 'string', 'max:20'],
            'variant_id' => ['nullable', 'integer'],
            'name'       => $guest ? ['required', 'string', 'max:255', new RealisticFullName] : ['nullable', 'string', 'max:255'],
            'phone'      => ['required', 'string', 'max:30', new RealisticGuineaPhone],
            'commune'    => ['required', Rule::in(PageModels::COMMUNES)],
            'quartier'   => ['nullable', 'string', 'max:120'],
            'confirm_duplicate' => ['nullable', 'boolean'],
        ], [
            'name.required'    => 'Indique ton prénom et ton nom.',
            'phone.required'   => 'Indique ton numéro de téléphone.',
            'commune.required' => 'Choisis ta commune.',
            'commune.in'       => 'Choisis ta commune dans la liste.',
        ]);

        $offer = ProductPage::findOffer($product, $data['offer']);
        if (!$offer) {
            return response()->json(['message' => 'Cette offre n\'existe plus : recharge la page.'], 422);
        }
        $fee = ProductPage::deliveryFee($shop, $data['commune']);

        // Commande en double (même numéro, même produit, il y a moins de 30 min) : le client confirme d'abord
        $duplicate = \App\Support\DuplicateOrders::recent($shop->id, $data['phone'], $product->id);
        if ($duplicate && !$request->boolean('confirm_duplicate')) {
            return response()->json(\App\Support\DuplicateOrders::response($duplicate, $product->name), 409);
        }

        try {
            $order = DB::transaction(function () use ($product, $shop, $offer, $data, $guest, $fee, $request, $duplicate) {
                $p = Product::whereKey($product->id)->lockForUpdate()->first();
                $variant = null;
                if (!empty($data['variant_id'])) {
                    $variant = $p->variants()->where('is_active', true)->find($data['variant_id']);
                    if (!$variant) throw new \RuntimeException('Cette option n\'est plus disponible : choisis-en une autre.');
                    if ($variant->stock !== null && $variant->stock < $offer['qty']) {
                        throw new \RuntimeException($variant->stock > 0 ? "Il ne reste que {$variant->stock} « {$variant->name} »." : "« {$variant->name} » est en rupture de stock.");
                    }
                } elseif (!$p->has_variants && $p->stock !== null && $p->stock < $offer['qty']) {
                    throw new \RuntimeException($p->stock > 0 ? "Il ne reste que {$p->stock} en stock : choisis une offre plus petite." : 'Ce produit est en rupture de stock.');
                }

                $order = Order::create([
                    'user_id'              => Auth::id(),
                    'client_name'          => $guest ? trim($data['name']) : null,
                    'shop_id'              => $shop->id,
                    'total'                => $offer['price'],
                    'status'               => Order::STATUS_EN_ATTENTE,
                    'delivery_destination' => $data['commune'] . (trim((string) ($data['quartier'] ?? '')) !== '' ? ' — ' . trim($data['quartier']) : ''),
                    'delivery_fee'         => $fee,
                    'client_phone'         => $data['phone'],
                    'source'               => TrafficSource::forOrder($shop->id),
                    'campaign_id'          => TrafficSource::campaignForOrder($shop->id),
                    'ad_campaign_id'       => \App\Support\Campaigns\AdAttribution::campaignForOrder($shop->id, $request->user()),
                ]);
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_id'         => $p->id,
                    'product_variant_id' => $variant?->id,
                    'variant_name'       => $offer['qty'] > 1 || $offer['label'] !== '1 ' . ($p->unit ?: 'article')
                        ? trim(($variant?->name ? $variant->name . ' · ' : '') . $offer['label'])
                        : $variant?->name,
                    'quantity'           => $offer['qty'],
                    'price'              => round($offer['price'] / $offer['qty'], 2),
                    'options'            => $duplicate ? ['duplicate_of' => $duplicate->id] : null, // nouvelle commande confirmée par le client
                ]);
                if ($variant && $variant->stock !== null) {
                    $variant->decrement('stock', $offer['qty']);
                } elseif (!$variant && !$p->has_variants && $p->stock !== null) {
                    $p->decrement('stock', $offer['qty']);
                }
                Payment::create(['order_id' => $order->id, 'method' => Payment::METHOD_CASH, 'amount' => $offer['price'], 'status' => 'en_attente']);
                return $order;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($guest) {
            \App\Support\DeliveryCode::rememberGuestOrder($order);
        }

        $summary = $product->name . ' · ' . $offer['label'] . ' — ' . number_format($order->total, 0, ',', ' ') . ' ' . ($shop->currency ?? 'GNF');
        try {
            $push = app(PushService::class);
            if ($shop->user) $push->sendToUser($shop->user, 'Nouvelle commande !', $summary, $push->vendorBadgeCount($shop->user), '/employe/orders');
            $push->notifyShopStaff($shop, 'Nouvelle commande !', $summary, '/employe/orders', $shop->user_id);
        } catch (\Throwable $e) {
        }
        \App\Support\MetaPixel::queuePurchase($order);

        return response()->json([
            'id'       => $order->id,
            'total'    => (int) $order->total + (int) ($fee ?? 0),
            'tracking' => \App\Support\OrderAccess::trackingUrl($order),
        ], 201);
    }
}
