<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use App\Models\Shop;
use App\Support\OrderBoard;
use App\Support\PageModels;
use App\Support\Storefront;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Commandes → « Paniers abandonnés » : les clients qui ont laissé leur numéro dans le formulaire de
 * commande sans le valider. Ils apparaissent « À rappeler » après le délai choisi dans les réglages.
 * Onglets : À rappeler, Injoignables, Pas intéressés, Récupérés (devenus une commande).
 * Visible des employés (« closers ») si le réglage le permet.
 */
class AbandonedCartController extends Controller
{
    private function shop(): Shop
    {
        $u = Auth::user();
        $shop = $u->shop ?? $u->assignedShop;
        abort_unless($shop, 404, 'Aucune boutique associée à ce compte.');
        // Employé : seulement si la boutique ouvre la page à ses closers
        if ((int) $shop->user_id !== (int) $u->id) abort_unless(OrderBoard::settings(Storefront::forShop($shop))['carts']['closers'], 403, 'La boutique n’a pas ouvert cette page à son équipe.');
        return $shop;
    }

    private function isOwner(Shop $shop): bool
    {
        return (int) $shop->user_id === (int) Auth::id();
    }

    public function index(Request $request)
    {
        $shop = $this->shop();
        $set = OrderBoard::settings(Storefront::forShop($shop))['carts'];
        $tab = array_key_exists($request->query('onglet'), AbandonedCart::STATES) ? $request->query('onglet') : 'a_rappeler';
        $cutoff = now()->subMinutes($set['delay']);
        $cur = $shop->currency ?? 'GNF';
        $money = fn ($v) => number_format((float) $v, 0, ',', ' ') . ' ' . $cur;

        // Un panier « À rappeler » n'apparaît qu'après le délai sans activité du client
        $q = fn () => AbandonedCart::where('shop_id', $shop->id)->whereNotNull('phone');
        $waiting = fn () => $q()->where('state', 'a_rappeler')->where('last_activity_at', '<=', $cutoff);
        $counts = ['a_rappeler' => $waiting()->count()];
        foreach (['injoignable', 'pas_interesse', 'recupere'] as $k) $counts[$k] = $q()->where('state', $k)->count();
        $withPhone = $q()->where(fn ($w) => $w->where('state', '!=', 'a_rappeler')->orWhere('last_activity_at', '<=', $cutoff))->count();
        $stats = [
            'waiting' => $counts['a_rappeler'],
            'amount' => $money($waiting()->sum('total')),
            'recovered' => $counts['recupere'],
            'rate' => $withPhone ? (int) round($counts['recupere'] / $withPhone * 100) : 0,
            'nophone' => AbandonedCart::where('shop_id', $shop->id)->whereNull('phone')->where('last_activity_at', '<=', $cutoff)->whereNull('order_id')->count(),
        ];

        $list = ($tab === 'a_rappeler' ? $waiting() : $q()->where('state', $tab))->with('order:id')->latest('last_activity_at')->limit(100)->get()
            ->map(function ($c) use ($shop, $set, $money) {
                $first = Str::of((string) $c->name)->explode(' ')->first() ?: '';
                $products = collect($c->items ?? [])->map(fn ($i) => ($i['name'] ?? 'Produit') . ' × ' . ($i['qty'] ?? 1))->implode(', ');
                $wa = OrderBoard::fill($set['wa'], ['prenom' => $first ?: 'à vous', 'boutique' => $shop->name, 'produits' => $products, 'total' => $money($c->total), 'numero' => '']);
                $intl = OrderBoard::intl($c->phone);
                return [
                    'id' => $c->id, 'name' => $c->name ?: 'Client sans nom', 'phone' => $c->phone,
                    'phoneShow' => trim(chunk_split((string) $c->phone, 3, ' ')),
                    'commune' => $c->commune, 'landmark' => $c->landmark, 'items' => $c->items ?? [], 'products' => $products,
                    'total' => $money($c->total), 'ago' => $c->last_activity_at?->locale('fr')->diffForHumans(), 'state' => $c->state,
                    'order' => $c->order_id,
                    'tel' => $intl ? 'tel:+' . $intl : null, 'wa' => $intl ? 'https://wa.me/' . $intl . '?text=' . rawurlencode($wa) : null,
                    'urls' => ['state' => route(($this->isOwner($shop) ? 'boutique' : 'employe') . '.carts.state', $c)],
                ];
            });

        return view('boutique.carts.index', [
            'shop' => $shop, 'tab' => $tab, 'counts' => $counts, 'stats' => $stats, 'carts' => $list, 'settings' => $set,
            'isOwner' => $this->isOwner($shop), 'communes' => PageModels::COMMUNES, 'devise' => $cur,
        ]);
    }

    /** Range un panier : à rappeler, injoignable, pas intéressé (JSON). */
    public function state(Request $request, AbandonedCart $cart): JsonResponse
    {
        $shop = $this->shop();
        abort_unless((int) $cart->shop_id === (int) $shop->id, 404);
        $data = $request->validate(['state' => ['required', Rule::in(['a_rappeler', 'injoignable', 'pas_interesse'])]]);
        if ($cart->state === 'recupere') return response()->json(['message' => 'Ce panier est déjà devenu une commande.'], 422);
        $cart->update(['state' => $data['state']]);
        return response()->json(['ok' => true]);
    }

    public function editSettings()
    {
        $shop = $this->shop();
        abort_unless($this->isOwner($shop), 403);
        return view('boutique.carts.settings', [
            'shop' => $shop, 'settings' => OrderBoard::settings(Storefront::forShop($shop))['carts'], 'delays' => OrderBoard::DELAYS,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $shop = $this->shop();
        abort_unless($this->isOwner($shop), 403);
        $data = $request->validate([
            'delay' => ['required', Rule::in(array_keys(OrderBoard::DELAYS))],
            'wa' => ['required', 'string', 'max:600'],
        ], ['wa.required' => 'Écrivez le message WhatsApp.', 'wa.max' => 'Le message ne peut pas dépasser 600 caractères.']);
        $sf = Storefront::forShop($shop);
        $sf['carts'] = ['enabled' => $request->boolean('enabled'), 'delay' => (int) $data['delay'], 'closers' => $request->boolean('closers'), 'wa' => $data['wa']];
        $shop->update(['storefront' => Storefront::normalize($sf)]);
        return redirect()->route('boutique.carts.settings')->with('success', 'Réglages enregistrés.');
    }
}
