<?php

namespace App\Support;

use App\Models\LiveVisitor;
use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopTraffic;
use Illuminate\Support\Str;

/**
 * Vue en direct d'une boutique : uniquement des chiffres réels, calculés à chaque appel.
 *  - Visiteurs en direct : signal reçu de la vitrine dans les 60 dernières secondes (envoyé toutes les 20 s).
 *  - Visiteurs du jour : visites comptées aujourd'hui (shop_traffic, une par visiteur et par jour).
 *  - Ventes et commandes du jour : commandes passées aujourd'hui, hors commandes annulées.
 *  - Comportement : paniers remplis et formulaires ouverts parmi les visiteurs en direct, commandes des 10 dernières minutes.
 *  - Carte : visiteurs en direct par pays (déduit de l'IP, jamais enregistrée) ; commandes du jour dans le pays de la boutique.
 */
class LiveStats
{
    public const WINDOW_SECONDS = 60;
    public const RECENT_ORDERS_MINUTES = 10;

    public static function for(Shop $shop): array
    {
        $cur = $shop->currency ?? 'GNF';
        $money = fn ($v) => number_format((float) $v, 0, ',', ' ') . ' ' . $cur;
        $now = now();

        $live = LiveVisitor::with('product:id,name')->where('shop_id', $shop->id)
            ->where('last_seen_at', '>=', $now->copy()->subSeconds(self::WINDOW_SECONDS))
            ->orderByDesc('last_seen_at')->get();

        $today = Order::where('shop_id', $shop->id)->where('created_at', '>=', $now->copy()->startOfDay())->where('status', '!=', Order::STATUS_ANNULEE);
        $todayCount = (clone $today)->count();
        $todaySales = (int) round((float) (clone $today)->sum('total'));
        $recentOrders = Order::where('shop_id', $shop->id)->where('created_at', '>=', $now->copy()->subMinutes(self::RECENT_ORDERS_MINUTES))
            ->where('status', '!=', Order::STATUS_ANNULEE)->latest()->limit(5)->get(['id', 'total', 'client_name', 'created_at']);

        // « En ce moment » : visiteurs présents puis nouvelles commandes, du plus récent au plus ancien
        $events = $live->take(8)->map(function (LiveVisitor $v) use ($now) {
            $what = match ($v->stage) {
                'form'    => 'Remplit le formulaire de commande',
                'cart'    => 'A ' . $v->cart_items . ' article' . ($v->cart_items > 1 ? 's' : '') . ' dans son panier',
                'product' => $v->product ? 'Regarde « ' . Str::limit($v->product->name, 40) . ' »' : 'Regarde un produit',
                default   => 'Parcourt la boutique',
            };
            if ($v->stage !== 'cart' && $v->cart_items > 0) $what .= ' · panier de ' . $v->cart_items;
            $mins = (int) floor($v->first_seen_at?->diffInSeconds($now) / 60);
            return [
                'kind' => 'visitor', 'stage' => $v->stage, 'text' => $what,
                'place' => LiveMaps::name($v->country) ?? ($v->country ? $v->country : 'Lieu inconnu'),
                'since' => $mins < 1 ? 'arrivé à l’instant' : 'depuis ' . $mins . ' min',
                'at' => $v->last_seen_at?->timestamp ?? 0,
            ];
        })->concat($recentOrders->map(fn (Order $o) => [
            'kind' => 'order', 'stage' => 'order',
            'text' => 'Nouvelle commande n° ' . $o->id . ' · ' . $money($o->total),
            'place' => $o->client_name ? Str::of($o->client_name)->explode(' ')->first() : 'Client',
            'since' => 'il y a ' . max(1, (int) floor($o->created_at->diffInSeconds($now) / 60)) . ' min',
            'at' => $o->created_at->timestamp,
        ]))->sortByDesc('at')->values()->take(8)->all();

        $visitorsByCountry = $live->whereNotNull('country')->countBy('country')->all();
        $ordersByCountry = $todayCount && $shop->country ? [strtoupper($shop->country) => $todayCount] : [];

        return [
            'at'            => $now->timestamp,
            'live'          => $live->count(),
            'todayVisitors' => (int) ShopTraffic::where('shop_id', $shop->id)->whereDate('day', $now->toDateString())->sum('visits'),
            'todaySales'    => $money($todaySales),
            'todayOrders'   => $todayCount,
            'behavior'      => [
                'carts'  => $live->where('cart_items', '>', 0)->count(),
                'form'   => $live->where('stage', 'form')->count(),
                'orders' => Order::where('shop_id', $shop->id)->where('created_at', '>=', $now->copy()->subMinutes(self::RECENT_ORDERS_MINUTES))->where('status', '!=', Order::STATUS_ANNULEE)->count(),
            ],
            'events'        => $events,
            'map'           => [
                'visitors' => $visitorsByCountry,
                'orders'   => $ordersByCountry,
                'unplaced' => $live->whereNull('country')->count(),
            ],
        ];
    }
}
