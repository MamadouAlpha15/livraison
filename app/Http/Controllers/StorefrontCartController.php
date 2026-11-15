<?php

namespace App\Http\Controllers;

use App\Models\AbandonedCart;
use App\Models\Product;
use App\Models\Shop;
use App\Support\OrderBoard;
use App\Support\PageModels;
use App\Support\ProductPage;
use App\Support\Storefront;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Paniers abandonnés : le formulaire de commande (page produit, panier) envoie ce que le client a déjà
 * rempli, quelques secondes après sa saisie. Rien n'est enregistré si la boutique a coupé la collecte,
 * ni pour la boutique elle-même (vendeur, équipe). La commande validée range le panier en « Récupérés »
 * (StorefrontOrderController).
 */
class StorefrontCartController extends Controller
{
    public function capture(Shop $shop, Request $request): Response
    {
        abort_unless($shop->is_approved, 404);
        $set = OrderBoard::settings(Storefront::forShop($shop))['carts'];
        if (!$set['enabled']) return response()->noContent();
        $u = Auth::user();
        if ($u && ((int) $u->id === (int) $shop->user_id || (int) ($u->shop_id ?? 0) === (int) $shop->id)) return response()->noContent();

        $data = $request->validate([
            'visitor'  => ['required', 'string', 'regex:/^[a-z0-9]{12,40}$/'],
            'name'     => ['nullable', 'string', 'max:120'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'commune'  => ['nullable', 'string', 'max:60'],
            'landmark' => ['nullable', 'string', 'max:120'],
            'items'    => ['required', 'array', 'min:1', 'max:30'],
            'items.*.id'  => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        // Produits de la boutique seulement, totaux calculés ici (packs compris)
        $qty = [];
        foreach ($data['items'] as $it) $qty[(int) $it['id']] = ($qty[(int) $it['id']] ?? 0) + (int) $it['qty'];
        $products = Product::where('shop_id', $shop->id)->where('is_active', true)->whereIn('id', array_keys($qty))->get()->keyBy('id');
        $items = []; $total = 0;
        foreach ($qty as $id => $n) {
            if (!$p = $products->get($id)) continue;
            $line = ProductPage::lineTotal($p, $n);
            $items[] = ['id' => $p->id, 'name' => $p->name, 'qty' => $n, 'total' => $line];
            $total += $line;
        }
        if (!$items) return response()->noContent();

        // Numéro guinéen valide seulement (9 chiffres commençant par 6)
        $phone = preg_replace('/\D/', '', (string) ($data['phone'] ?? ''));
        if (strlen($phone) === 12 && str_starts_with($phone, '224')) $phone = substr($phone, 3);
        $phone = preg_match('/^6\d{8}$/', $phone) ? $phone : null;
        $clean = fn ($v, $max) => mb_substr(trim(strip_tags((string) $v)), 0, $max) ?: null;

        $cart = AbandonedCart::firstOrNew(['shop_id' => $shop->id, 'visitor' => $data['visitor']]);
        // Panier qui vient de devenir une commande : on ne le rouvre pas
        if ($cart->exists && $cart->state === 'recupere' && $cart->updated_at?->gt(now()->subMinutes(30))) return response()->noContent();
        $cart->fill([
            'name' => $clean($data['name'] ?? '', 120) ?? $cart->name,
            'phone' => $phone ?? $cart->phone,
            'commune' => in_array($data['commune'] ?? null, PageModels::COMMUNES, true) ? $data['commune'] : $cart->commune,
            'landmark' => $clean($data['landmark'] ?? '', 120) ?? $cart->landmark,
            'items' => $items, 'total' => $total, 'last_activity_at' => now(),
        ]);
        if ($cart->state === 'recupere' || !$cart->exists) { $cart->state = 'a_rappeler'; $cart->order_id = null; }
        $cart->save();
        return response()->noContent();
    }
}
