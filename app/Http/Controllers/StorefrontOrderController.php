<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shop;
use App\Rules\RealisticFullName;
use App\Rules\RealisticGuineaPhone;
use App\Services\PushService;
use App\Support\PageModels;
use App\Support\Storefront;
use App\Support\TrafficSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Commande passée depuis un modèle de page boutique (Prestige, Pop, Épuré, Éditorial, Fil) :
 * le panier de la page (un ou plusieurs produits d'une même boutique) devient UNE commande,
 * payée à la livraison, en espèces, au livreur. Mêmes contrôles que la fiche produit :
 * boutique vérifiée, mode vacances, stock, nom complet et numéro guinéen pour un invité.
 * Les produits à options (taille, couleur…) se commandent depuis leur fiche.
 */
class StorefrontOrderController extends Controller
{
    public function store(Shop $shop, Request $request): JsonResponse
    {
        abort_unless($shop->is_approved, 404);
        if (Storefront::onVacation($shop)) {
            return response()->json(['message' => Storefront::vacationMessage($shop)], 423);
        }

        $guest = !Auth::check();
        $data = $request->validate([
            'items'         => ['required', 'array', 'min:1', 'max:30'],
            'items.*.id'    => ['required', 'integer'],
            'items.*.qty'   => ['required', 'integer', 'min:1', 'max:99'],
            'name'          => $guest ? ['required', 'string', 'max:255', new RealisticFullName] : ['nullable', 'string', 'max:255'],
            'phone'         => ['required', 'string', 'max:30', new RealisticGuineaPhone],
            'commune'       => ['required', Rule::in(PageModels::COMMUNES)],
            'landmark'      => ['nullable', 'string', 'max:120'],
        ], [
            'items.required'   => 'Votre panier est vide.',
            'name.required'    => 'Indiquez votre prénom et votre nom.',
            'phone.required'   => 'Indiquez votre numéro de téléphone.',
            'commune.required' => 'Choisissez votre commune.',
            'commune.in'       => 'Choisissez votre commune dans la liste.',
            'landmark.max'     => 'Le repère ne peut pas dépasser 120 caractères.',
        ]);

        // Quantités regroupées par produit (un même produit ajouté deux fois = une ligne)
        $qty = [];
        foreach ($data['items'] as $it) $qty[(int) $it['id']] = ($qty[(int) $it['id']] ?? 0) + (int) $it['qty'];

        try {
            $order = DB::transaction(function () use ($shop, $qty, $data, $guest) {
                $products = Product::where('shop_id', $shop->id)->where('is_active', true)->whereIn('id', array_keys($qty))->lockForUpdate()->get()->keyBy('id');
                $total = 0;
                foreach ($qty as $id => $n) {
                    $p = $products->get($id);
                    if (!$p) throw new \RuntimeException('Un produit de votre panier n’est plus en vente. Retirez-le puis réessayez.');
                    if ($p->has_variants) throw new \RuntimeException('« ' . $p->name . ' » a des options (taille, couleur…) : commandez-le depuis sa fiche.');
                    if ($p->stock !== null && $p->stock < $n) {
                        throw new \RuntimeException($p->stock > 0 ? 'Il ne reste que ' . $p->stock . ' « ' . $p->name . ' » en stock.' : '« ' . $p->name . ' » est en rupture de stock.');
                    }
                    $total += $p->current_price * $n;
                }

                $order = Order::create([
                    'user_id'              => Auth::id(),
                    'client_name'          => $guest ? trim($data['name']) : null,
                    'shop_id'              => $shop->id,
                    'total'                => $total,
                    'status'               => Order::STATUS_EN_ATTENTE,
                    'delivery_destination' => $data['commune'] . (trim((string) ($data['landmark'] ?? '')) !== '' ? ' — ' . trim($data['landmark']) : ''),
                    'client_phone'         => $data['phone'],
                    'source'               => TrafficSource::forOrder($shop->id),
                    'campaign_id'          => TrafficSource::campaignForOrder($shop->id),
                ]);
                foreach ($qty as $id => $n) {
                    $p = $products[$id];
                    OrderItem::create(['order_id' => $order->id, 'product_id' => $p->id, 'quantity' => $n, 'price' => $p->current_price]);
                    if ($p->stock !== null) $p->decrement('stock', $n);
                }
                Payment::create(['order_id' => $order->id, 'method' => Payment::METHOD_CASH, 'amount' => $total, 'status' => 'en_attente']);
                return $order;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        // Prévenir la boutique (propriétaire et équipe)
        $lines = $order->items()->with('product:id,name')->get();
        $summary = $lines->map(fn ($l) => ($l->product->name ?? 'Produit') . ' × ' . $l->quantity)->implode(', ') . ' — ' . number_format($order->total, 0, ',', ' ') . ' ' . ($shop->currency ?? 'GNF');
        try {
            $push = app(PushService::class);
            if ($shop->user) $push->sendToUser($shop->user, 'Nouvelle commande !', $summary, $push->vendorBadgeCount($shop->user), '/employe/orders');
            $push->notifyShopStaff($shop, 'Nouvelle commande !', $summary, '/employe/orders', $shop->user_id);
        } catch (\Throwable $e) {}
        \App\Support\MetaPixel::queuePurchase($order);

        // Mode WhatsApp : message prêt pour la boutique
        $wa = null;
        if ($shop->phone && Storefront::settings($shop)['wa_mode']) {
            $phone = preg_replace('/\D/', '', $shop->phone);
            if (strlen($phone) === 9) $phone = '224' . $phone;
            $wa = 'https://wa.me/' . $phone . '?text=' . rawurlencode('Bonjour ' . $shop->name . ', je viens de commander sur Shopio : ' . $summary . '. Commande n° ' . $order->id . '.');
        }

        return response()->json([
            'id'       => $order->id,
            'total'    => (int) $order->total,
            'tracking' => route('suivi.show', $order),
            'wa'       => $wa,
        ], 201);
    }
}
