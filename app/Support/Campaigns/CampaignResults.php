<?php

namespace App\Support\Campaigns;

use App\Models\AdCampaign;
use App\Models\AdCampaignPayment;
use App\Models\AdCampaignStat;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Résultats des campagnes : affichages, clics, messages et dépense viennent des statistiques
 * par jour ; commandes et chiffre d'affaires viennent des commandes attribuées (orders.ad_campaign_id),
 * hors commandes annulées.
 */
class CampaignResults
{
    /** @return array<int, array{impressions:int, clicks:int, messages:int, orders:int, revenue:int, spend:int}> */
    public static function forCampaigns(Collection $campaigns): array
    {
        $ids = $campaigns->pluck('id')->all();
        if ($ids === []) {
            return [];
        }

        $stats = AdCampaignStat::whereIn('ad_campaign_id', $ids)
            ->selectRaw('ad_campaign_id, SUM(impressions) AS impressions, SUM(clicks) AS clicks, SUM(messages) AS messages, SUM(spend_gnf) AS spend')
            ->groupBy('ad_campaign_id')->get()->keyBy('ad_campaign_id');

        $orders = Order::whereIn('ad_campaign_id', $ids)->where('status', '!=', Order::STATUS_ANNULEE)
            ->selectRaw('ad_campaign_id, COUNT(*) AS n, SUM(total) AS revenue')
            ->groupBy('ad_campaign_id')->get()->keyBy('ad_campaign_id');

        $out = [];
        foreach ($ids as $id) {
            $s = $stats->get($id);
            $o = $orders->get($id);
            $out[$id] = [
                'impressions' => (int) ($s->impressions ?? 0),
                'clicks'      => (int) ($s->clicks ?? 0),
                'messages'    => (int) ($s->messages ?? 0),
                'orders'      => (int) ($o->n ?? 0),
                'revenue'     => (int) round((float) ($o->revenue ?? 0)),
                'spend'       => (int) ($s->spend ?? 0),
            ];
        }
        return $out;
    }

    public static function forCampaign(AdCampaign $campaign): array
    {
        return self::forCampaigns(collect([$campaign]))[$campaign->id];
    }

    /** Coût par commande en GNF, null tant qu'il n'y a pas de commande. */
    public static function costPerOrder(AdCampaign $campaign, int $orders): ?int
    {
        return $orders > 0 ? intdiv($campaign->price_gnf, $orders) : null;
    }

    /**
     * Visites (clics) et commandes jour par jour, du début de la campagne à aujourd'hui (ou sa fin).
     * @return Collection<int, array{day: Carbon, clicks: int, orders: int}>
     */
    public static function daily(AdCampaign $campaign): Collection
    {
        $from = $campaign->starts_on->copy();
        $to   = $campaign->ends_on->copy()->min(today());
        if ($from->gt($to)) {
            return collect();
        }

        $clicks = AdCampaignStat::where('ad_campaign_id', $campaign->id)
            ->selectRaw('day, SUM(clicks) AS clicks')->groupBy('day')->get()
            ->mapWithKeys(fn ($r) => [Carbon::parse($r->day)->toDateString() => (int) $r->clicks]);
        $orders = Order::where('ad_campaign_id', $campaign->id)->where('status', '!=', Order::STATUS_ANNULEE)
            ->get(['created_at'])->groupBy(fn ($o) => $o->created_at->toDateString())->map->count();

        $days = collect();
        for ($d = $from; $d->lte($to); $d = $d->copy()->addDay()) {
            $key = $d->toDateString();
            $days->push(['day' => $d, 'clicks' => $clicks[$key] ?? 0, 'orders' => $orders[$key] ?? 0]);
        }
        return $days;
    }

    /**
     * Chiffres du mois pour la boutique : campagnes en cours, commandes et CA générés, montant dépensé
     * (paiements du mois moins remboursements du mois).
     */
    public static function monthSummary(Shop $shop): array
    {
        $start = now()->startOfMonth();
        $campaignIds = $shop->adCampaigns()->pluck('id');

        $orders = Order::whereIn('ad_campaign_id', $campaignIds)->where('status', '!=', Order::STATUS_ANNULEE)
            ->where('created_at', '>=', $start);
        $paid = AdCampaignPayment::whereIn('ad_campaign_id', $campaignIds)
            ->whereIn('status', [AdCampaignPayment::STATUS_PAYE, AdCampaignPayment::STATUS_REMBOURSE])
            ->where('paid_at', '>=', $start)->sum('amount_gnf');
        $refunded = AdCampaignPayment::whereIn('ad_campaign_id', $campaignIds)
            ->where('refunded_at', '>=', $start)->sum('refund_amount_gnf');

        return [
            'running' => $shop->adCampaigns()->where('status', AdCampaign::STATUS_EN_COURS)->count(),
            'orders'  => (clone $orders)->count(),
            'revenue' => (int) round((float) (clone $orders)->sum('total')),
            'spent'   => max(0, (int) $paid - (int) $refunded),
        ];
    }
}
