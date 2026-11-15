<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\PageBuilder\Commerce\Checkout;
use App\PageBuilder\Commerce\QuoteException;
use App\Rules\RealisticFullName;
use App\Rules\RealisticGuineaPhone;
use App\Services\PushService;
use App\Support\DuplicateOrders;
use App\Support\OrderPhoneVerification;
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
 * Commande depuis une page construite (section Produit publiée), payée à la livraison.
 * Le navigateur n'envoie que des choix : le palier, l'option, la quantité, les produits complémentaires
 * et les réponses aux champs personnalisés. Tous les prix sont recalculés par Commerce\Checkout à partir
 * de la version publiée de la page et du prix réel. Mêmes contrôles que la page produit actuelle
 * (boutique vérifiée, mode vacances, stock verrouillé, nom complet et numéro guinéen pour un invité).
 */
class PageBuilderOrderController extends Controller
{
    public function store(Product $product, Request $request): JsonResponse
    {
        $shop = $product->shop;
        $page = $product->productPage;
        abort_unless($shop && $shop->is_approved && $product->is_active && $page?->isLive(), 404);
        $checkout = Checkout::fromPage($page->published, $product);
        abort_unless($checkout, 404);

        if (Storefront::onVacation($shop)) {
            return response()->json(['message' => Storefront::vacationMessage($shop)], 423);
        }

        $guest = !Auth::check();
        $hasVariants = $product->activeVariants()->exists();
        $rules = [
            'variant_id' => [$hasVariants ? 'required' : 'nullable', 'integer'],
            'tier'       => [$checkout->offers ? 'required' : 'nullable', 'integer'],
            'quantity'   => ['nullable', 'integer', 'min:1', 'max:' . $checkout->maxQuantity()],
            'upsells'    => ['nullable', 'array', 'max:3'],
            'upsells.*'  => ['integer'],
            'name'       => $guest ? ['required', 'string', 'max:255', new RealisticFullName] : ['nullable', 'string', 'max:255'],
            'phone'      => ['required', 'string', 'max:30', new RealisticGuineaPhone],
            'commune'    => ['required', Rule::in(PageModels::COMMUNES)],
            'quartier'   => ['nullable', 'string', 'max:120'],
            'fields'     => ['nullable', 'array'],
            'otp'        => ['nullable', 'string', 'max:10'],
            'confirm_duplicate' => ['nullable', 'boolean'],
        ];
        $labels = [];
        foreach ($checkout->customFields() as $id => $f) {
            $rules["fields.{$id}"] = array_merge(
                [$f['required'] ? 'required' : 'nullable', 'string', 'max:' . ($f['type'] === 'textarea' ? 500 : 200)],
                $f['type'] === 'select' ? [Rule::in($f['options'])] : [],
            );
            $labels["fields.{$id}"] = '« ' . $f['label'] . ' »';
        }
        $data = $request->validate($rules, [
            'name.required'       => 'Indique ton prénom et ton nom.',
            'phone.required'      => 'Indique ton numéro de téléphone.',
            'commune.required'    => 'Choisis ta commune.',
            'commune.in'          => 'Choisis ta commune dans la liste.',
            'variant_id.required' => 'Choisis une option.',
            'tier.required'       => 'Choisis une offre.',
            'quantity.max'        => 'Tu peux commander :max articles au plus.',
            'fields.*.required'   => 'Réponds à :attribute.',
            'fields.*.in'         => 'Choisis une réponse dans la liste pour :attribute.',
        ], $labels);

        $fee = ProductPage::deliveryFee($shop, $data['commune']);
        $answers = collect($checkout->customFields())
            ->map(fn ($f, $id) => ['label' => $f['label'], 'value' => trim((string) ($data['fields'][$id] ?? ''))])
            ->filter(fn ($a) => $a['value'] !== '')->values()->all();

        // Commande en double (même numéro, même produit, il y a moins de 30 min) : le client confirme d'abord
        $duplicate = DuplicateOrders::recent($shop->id, $data['phone'], $product->id);
        if ($duplicate && !$request->boolean('confirm_duplicate')) {
            return response()->json(DuplicateOrders::response($duplicate, $product->name), 409);
        }

        // Code SMS, selon le réglage du formulaire (montant calculé ici sans verrou, recalculé ensuite)
        $verified = false;
        try {
            $variant = !empty($data['variant_id']) ? $product->variants()->where('is_active', true)->find($data['variant_id']) : null;
            $estimate = $checkout->quote($variant, isset($data['tier']) ? (int) $data['tier'] : null, (int) ($data['quantity'] ?? 1), $data['upsells'] ?? [])['total'];
        } catch (QuoteException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        if (OrderPhoneVerification::required($checkout->orderForm, $estimate, $data['phone'])) {
            if (blank($data['otp'] ?? null)) {
                [$status, $body] = OrderPhoneVerification::send($shop, $data['phone'], $product->id);
                return response()->json($body, $status);
            }
            if ($error = OrderPhoneVerification::check($data['phone'], $product->id, $data['otp'])) {
                return response()->json(['message' => $error, 'otp_required' => true, 'errors' => ['otp' => [$error]]], 422);
            }
            $verified = true;
        }

        try {
            [$order, $quote] = DB::transaction(function () use ($product, $shop, $page, $checkout, $data, $guest, $fee, $answers, $request, $duplicate, $verified) {
                $p = Product::whereKey($product->id)->lockForUpdate()->first();
                $variant = null;
                if (!empty($data['variant_id'])) {
                    $variant = $p->variants()->where('is_active', true)->lockForUpdate()->find($data['variant_id']);
                    if (!$variant) throw new QuoteException('Cette option n\'est plus disponible : choisis-en une autre.');
                }

                $quote = $checkout->quote($variant, isset($data['tier']) ? (int) $data['tier'] : null, (int) ($data['quantity'] ?? 1), $data['upsells'] ?? []);

                // Stock : produit (ou option), cadeau et produits complémentaires, verrouillés
                foreach ($quote['lines'] as $i => $line) {
                    $item = $i === 0 ? $p : Product::whereKey($line['product']->id)->lockForUpdate()->first();
                    $stockOwner = $line['variant'] ?? $item; // cadeaux et complémentaires n'ont pas d'options
                    if ($stockOwner && $stockOwner->stock !== null && $stockOwner->stock < $line['qty']) {
                        throw new QuoteException($i === 0
                            ? ($stockOwner->stock > 0 ? "Il ne reste que {$stockOwner->stock} en stock : choisis une quantité plus petite." : 'Ce produit est en rupture de stock.')
                            : "« {$item->name} » n'est plus en stock : recharge la page.");
                    }
                    $quote['lines'][$i]['stock_owner'] = $stockOwner;
                }

                $order = Order::create([
                    'user_id'              => Auth::id(),
                    'client_name'          => $guest ? trim($data['name']) : null,
                    'shop_id'              => $shop->id,
                    'total'                => $quote['total'],
                    'status'               => Order::STATUS_EN_ATTENTE,
                    'delivery_destination' => $data['commune'] . (trim((string) ($data['quartier'] ?? '')) !== '' ? ' — ' . trim($data['quartier']) : ''),
                    'delivery_fee'         => $fee,
                    'client_phone'         => $data['phone'],
                    'source'               => TrafficSource::forOrder($shop->id),
                    'campaign_id'          => TrafficSource::campaignForOrder($shop->id),
                    'ad_campaign_id'       => \App\Support\Campaigns\AdAttribution::campaignForOrder($shop->id, $request->user()),
                ]);

                foreach ($quote['lines'] as $i => $line) {
                    $options = $line['options'] + ['page_version' => $page->version];
                    if ($i === 0) {
                        $options += array_filter([
                            'custom'         => $answers ?: null,
                            'duplicate_of'   => $duplicate?->id,     // le client a confirmé une nouvelle commande
                            'phone_verified' => $verified ?: null,   // numéro confirmé par code SMS
                        ]);
                    }
                    OrderItem::create([
                        'order_id'           => $order->id,
                        'product_id'         => $line['product']->id,
                        'product_variant_id' => $line['variant']?->id,
                        'variant_name'       => $line['label'] ?: null,
                        'quantity'           => $line['qty'],
                        'price'              => round($line['total'] / $line['qty'], 2),
                        'options'            => $options,
                    ]);
                    if ($line['stock_owner'] && $line['stock_owner']->stock !== null) {
                        $line['stock_owner']->decrement('stock', $line['qty']);
                    }
                }

                Payment::create(['order_id' => $order->id, 'method' => Payment::METHOD_CASH, 'amount' => $quote['total'], 'status' => 'en_attente']);
                return [$order, $quote];
            });
        } catch (QuoteException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($guest) {
            \App\Support\DeliveryCode::rememberGuestOrder($order);
        }

        $first = $quote['lines'][0];
        $summary = $product->name . ($first['label'] ? ' · ' . $first['label'] : '') . (count($quote['lines']) > 1 ? ' (+' . (count($quote['lines']) - 1) . ')' : '')
            . ' — ' . number_format($order->total, 0, ',', ' ') . ' ' . ($shop->currency ?? 'GNF');
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
