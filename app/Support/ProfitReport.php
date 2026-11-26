<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Bénéfice réel par produit, sur un mois, à partir des commandes réellement livrées :
 *   chiffre d'affaires (lignes livrées, remise répartie) − prix d'achat − livraison − retours − pub.
 * La livraison et les retours sont des montants fixes par commande, choisis par la boutique, répartis
 * entre les produits de la commande selon leur part du montant. Rien n'est estimé : un produit sans
 * prix d'achat est signalé comme « incomplet ».
 */
class ProfitReport
{
    /** Mois au format « 2026-11 », borné aux 24 derniers mois. */
    public static function month(?string $m): string
    {
        $now = now('Africa/Conakry');
        if (!is_string($m) || !preg_match('/^\d{4}-\d{2}$/', $m)) return $now->format('Y-m');
        try { $d = Carbon::createFromFormat('!Y-m', $m, 'Africa/Conakry'); } catch (\Throwable) { return $now->format('Y-m'); }
        if ($d->greaterThan($now) || $d->lessThan($now->copy()->startOfMonth()->subMonths(23))) return $now->format('Y-m');
        return $m;
    }

    /** Les 12 derniers mois pour le sélecteur : ['2026-11' => 'novembre 2026', …]. */
    public static function months(): array
    {
        $out = [];
        $d = now('Africa/Conakry')->startOfMonth();
        for ($k = 0; $k < 12; $k++) { $out[$d->format('Y-m')] = $d->locale('fr')->isoFormat('MMMM YYYY'); $d->subMonth(); }
        return $out;
    }

    public static function build(Shop $shop, string $month): array
    {
        $tz = 'Africa/Conakry';
        $from = Carbon::createFromFormat('!Y-m', $month, $tz)->startOfMonth()->utc();
        $to = $from->copy()->setTimezone($tz)->endOfMonth()->utc();
        $deliveryCost = (int) ($shop->delivery_cost ?? 0);
        $returnCost = (int) ($shop->return_cost ?? 0);

        $rows = [];
        $blank = ['qty' => 0, 'revenue' => 0.0, 'delivery' => 0.0, 'returns' => 0.0, 'ads' => 0, 'orders' => 0, 'returned' => 0];

        // Commandes livrées ce mois (date de livraison, sinon dernière mise à jour)
        $delivered = Order::where('shop_id', $shop->id)->where('status', 'livrée')
            ->where(fn ($q) => $q->whereBetween('delivered_at', [$from, $to])
                ->orWhere(fn ($q2) => $q2->whereNull('delivered_at')->whereBetween('updated_at', [$from, $to])))
            ->with('items:id,order_id,product_id,quantity,price')->get(['id', 'discount_amount', 'total']);

        $unassigned = ['orders' => 0, 'amount' => 0];
        foreach ($delivered as $o) {
            $lines = $o->items->filter(fn ($l) => $l->product_id);
            $sum = (float) $lines->sum(fn ($l) => $l->price * $l->quantity);
            // Commande livrée sans détail des produits : comptée à part, jamais attribuée au hasard
            if ($sum <= 0) { $unassigned['orders']++; $unassigned['amount'] += (int) $o->total; continue; }
            $keep = max(0, 1 - ((float) $o->discount_amount) / $sum); // remise répartie sur les lignes
            foreach ($lines as $l) {
                $share = ($l->price * $l->quantity) / $sum;
                $pid = (int) $l->product_id;
                $rows[$pid] ??= $blank;
                $rows[$pid]['qty'] += (int) $l->quantity;
                $rows[$pid]['revenue'] += $l->price * $l->quantity * $keep;
                $rows[$pid]['delivery'] += $deliveryCost * $share;
                $rows[$pid]['orders']++;
            }
        }

        // Commandes retournées ce mois (Commandes → « Retournée ») : seul le coût du retour est perdu
        $returned = Order::where('shop_id', $shop->id)->where('status', 'annulée')->where('followup', 'retournee')
            ->whereBetween('updated_at', [$from, $to])
            ->with('items:id,order_id,product_id,quantity,price')->get(['id']);
        foreach ($returned as $o) {
            $lines = $o->items->filter(fn ($l) => $l->product_id);
            $sum = (float) $lines->sum(fn ($l) => $l->price * $l->quantity);
            if ($sum <= 0) continue;
            foreach ($lines as $l) {
                $pid = (int) $l->product_id;
                $rows[$pid] ??= $blank;
                $rows[$pid]['returns'] += $returnCost * ($l->price * $l->quantity) / $sum;
                $rows[$pid]['returned']++;
            }
        }

        // Pub du mois
        $ads = DB::table('product_ad_spends')->where('shop_id', $shop->id)->where('month', $month)->pluck('amount', 'product_id');
        foreach ($ads as $pid => $amount) { $pid = (int) $pid; $rows[$pid] ??= $blank; $rows[$pid]['ads'] = (int) $amount; }

        $products = Product::where('shop_id', $shop->id)->whereIn('id', array_keys($rows))->get(['id', 'name', 'image', 'cost_price', 'price'])->keyBy('id');

        $list = [];
        $tot = ['qty' => 0, 'revenue' => 0, 'buy' => 0, 'delivery' => 0, 'returns' => 0, 'ads' => 0, 'profit' => 0];
        $missing = 0;
        foreach ($rows as $pid => $r) {
            $p = $products[$pid] ?? null;
            if (!$p) continue; // produit supprimé
            $known = $p->cost_price !== null;
            $buy = $known ? $r['qty'] * (int) $p->cost_price : 0;
            $profit = $r['revenue'] - $buy - $r['delivery'] - $r['returns'] - $r['ads'];
            if (!$known && $r['qty'] > 0) $missing++;
            $list[] = [
                'id' => $pid, 'name' => $p->name, 'image' => $p->image, 'cost' => $p->cost_price, 'price' => (int) $p->price,
                'qty' => $r['qty'], 'returned' => $r['returned'],
                'revenue' => (int) round($r['revenue']), 'buy' => (int) round($buy), 'delivery' => (int) round($r['delivery']),
                'returns' => (int) round($r['returns']), 'ads' => (int) $r['ads'], 'profit' => (int) round($profit),
                'margin' => $r['revenue'] > 0 ? round($profit / $r['revenue'] * 100) : null,
                'complete' => $known || $r['qty'] === 0,
            ];
            foreach (['qty', 'revenue', 'buy', 'delivery', 'returns', 'ads', 'profit'] as $k) $tot[$k] += end($list)[$k];
        }
        usort($list, fn ($a, $b) => $b['profit'] <=> $a['profit']);
        $tot['margin'] = $tot['revenue'] > 0 ? round($tot['profit'] / $tot['revenue'] * 100) : null;
        $tot['costs'] = $tot['buy'] + $tot['delivery'] + $tot['returns'] + $tot['ads'];

        return ['rows' => $list, 'total' => $tot, 'missing' => $missing, 'orders' => $delivered->count(), 'returnedOrders' => $returned->count(), 'unassigned' => $unassigned];
    }
}
