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
use App\Support\ProductPage;
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
            // Thème Lookbook : motif (variante du produit) et taille choisis pour chaque pièce
            'items.*.variant' => ['nullable', 'integer'],
            'items.*.size'    => ['nullable', 'string', 'max:12'],
            'name'          => $guest ? ['required', 'string', 'max:255', new RealisticFullName] : ['nullable', 'string', 'max:255'],
            'phone'         => ['required', 'string', 'max:30', new RealisticGuineaPhone],
            'commune'       => ['required', Rule::in(PageModels::COMMUNES)],
            'landmark'      => ['nullable', 'string', 'max:120'],
            'visitor'       => ['nullable', 'string', 'regex:/^[a-z0-9]{12,40}$/'],
        ], [
            'items.required'   => 'Votre panier est vide.',
            'name.required'    => 'Indiquez votre prénom et votre nom.',
            'phone.required'   => 'Indiquez votre numéro de téléphone.',
            'commune.required' => 'Choisissez votre commune.',
            'commune.in'       => 'Choisissez votre commune dans la liste.',
            'landmark.max'     => 'Le repère ne peut pas dépasser 120 caractères.',
        ]);

        // Lignes regroupées par produit, motif et taille (un même produit ajouté deux fois = une ligne)
        $lines = [];
        foreach ($data['items'] as $it) {
            $vid = !empty($it['variant']) ? (int) $it['variant'] : null;
            $size = isset($it['size']) ? mb_strtoupper(trim(strip_tags((string) $it['size']))) : '';
            $key = (int) $it['id'] . ':' . ($vid ?? 0) . ':' . $size;
            $lines[$key] ??= ['id' => (int) $it['id'], 'variant' => $vid, 'size' => $size !== '' ? $size : null, 'qty' => 0];
            $lines[$key]['qty'] += (int) $it['qty'];
        }
        // Quantités par produit (adresse obligatoire, stock et packs des produits sans motif)
        $qty = [];
        foreach ($lines as $l) $qty[$l['id']] = ($qty[$l['id']] ?? 0) + $l['qty'];

        // Adresse obligatoire si le formulaire d'un des produits l'exige (éditeur produit → Formulaire)
        if (trim((string) ($data['landmark'] ?? '')) === '') {
            $sf = Storefront::forShop($shop);
            $needs = Product::where('shop_id', $shop->id)->whereIn('id', array_keys($qty))->get()
                ->contains(fn ($p) => \App\Support\ProductDesign::for($p, $sf)['form']['address_req']);
            if ($needs) return response()->json(['message' => 'Indiquez votre adresse (quartier, rue, repère).', 'errors' => ['landmark' => ['Indiquez votre adresse (quartier, rue, repère).']]], 422);
        }

        try {
            $order = DB::transaction(function () use ($shop, $qty, $lines, $data, $guest) {
                $products = Product::where('shop_id', $shop->id)->where('is_active', true)->whereIn('id', array_keys($qty))->lockForUpdate()->get()->keyBy('id');
                $variantIds = array_values(array_filter(array_column($lines, 'variant')));
                $variants = $variantIds
                    ? \App\Models\ProductVariant::whereIn('id', $variantIds)->whereIn('product_id', array_keys($qty))->where('is_active', true)->lockForUpdate()->get()->keyBy('id')
                    : collect();
                $total = 0;
                $plainQty = [];  // produit sans motif => quantité (packs et stock du produit)
                $variantQty = []; // motif => quantité (stock du motif)
                foreach ($lines as $l) {
                    $p = $products->get($l['id']);
                    if (!$p) throw new \RuntimeException('Un produit de votre panier n’est plus en vente. Retirez-le puis réessayez.');
                    if ($l['variant']) {
                        $v = $variants->get($l['variant']);
                        if (!$v || (int) $v->product_id !== (int) $p->id) throw new \RuntimeException('Le motif choisi pour « ' . $p->name . ' » n’est plus disponible. Choisissez-en un autre.');
                        $variantQty[$v->id] = ($variantQty[$v->id] ?? 0) + $l['qty'];
                    } else {
                        if ($p->has_variants) throw new \RuntimeException('« ' . $p->name . ' » a des options (taille, couleur…) : commandez-le depuis sa fiche.');
                        $plainQty[$p->id] = ($plainQty[$p->id] ?? 0) + $l['qty'];
                    }
                }
                foreach ($variantQty as $vid => $n) {
                    $v = $variants[$vid];
                    if ($v->stock !== null && $v->stock < $n) {
                        $pn = $products[$v->product_id]->name . ' · ' . $v->name;
                        throw new \RuntimeException($v->stock > 0 ? 'Il ne reste que ' . $v->stock . ' « ' . $pn . ' » en stock.' : '« ' . $pn . ' » est en rupture de stock.');
                    }
                }
                $unitPlain = [];
                foreach ($plainQty as $id => $n) {
                    $p = $products[$id];
                    if ($p->stock !== null && $p->stock < $n) {
                        throw new \RuntimeException($p->stock > 0 ? 'Il ne reste que ' . $p->stock . ' « ' . $p->name . ' » en stock.' : '« ' . $p->name . ' » est en rupture de stock.');
                    }
                    // Packs du produit (2, 3… à prix réduit) appliqués ici, pas seulement à l'affichage
                    $sum = ProductPage::lineTotal($p, $n);
                    $unitPlain[$id] = $sum / $n;
                    $total += $sum;
                }
                foreach ($lines as $k => $l) {
                    if (!$l['variant']) continue;
                    $v = $variants[$l['variant']];
                    $unit = (float) $v->price > 0 ? (float) $v->price : (float) $products[$l['id']]->current_price;
                    $lines[$k]['unit'] = $unit;
                    $total += (int) round($unit * $l['qty']);
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
                foreach ($lines as $l) {
                    $p = $products[$l['id']];
                    $v = $l['variant'] ? $variants[$l['variant']] : null;
                    // Nom affiché au vendeur : « Soleils · Taille M », « Taille L », ou rien
                    $label = trim(($v ? $v->name : '') . ($l['size'] ? ($v ? ' · ' : '') . 'Taille ' . $l['size'] : ''));
                    OrderItem::create([
                        'order_id' => $order->id, 'product_id' => $p->id, 'quantity' => $l['qty'],
                        'product_variant_id' => $v?->id, 'variant_name' => $label !== '' ? $label : null,
                        'price' => round($v ? $l['unit'] : $unitPlain[$p->id], 2),
                    ]);
                }
                foreach ($variantQty as $vid => $n) if ($variants[$vid]->stock !== null) $variants[$vid]->decrement('stock', $n);
                foreach ($plainQty as $id => $n) if ($products[$id]->stock !== null) $products[$id]->decrement('stock', $n);
                Payment::create(['order_id' => $order->id, 'method' => Payment::METHOD_CASH, 'amount' => $total, 'status' => 'en_attente']);
                return $order;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        // Panier abandonné de ce client (même navigateur ou même numéro) : récupéré par cette commande
        $digits = preg_replace('/\D/', '', (string) $data['phone']);
        if (strlen($digits) === 12 && str_starts_with($digits, '224')) $digits = substr($digits, 3);
        \App\Models\AbandonedCart::where('shop_id', $shop->id)->where('state', '!=', 'recupere')
            ->where('last_activity_at', '>=', now()->subDays(7))
            ->where(fn ($w) => $w->where('phone', $digits)->when(!empty($data['visitor']), fn ($v) => $v->orWhere('visitor', $data['visitor'])))
            ->update(['state' => 'recupere', 'order_id' => $order->id, 'updated_at' => now()]);

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
