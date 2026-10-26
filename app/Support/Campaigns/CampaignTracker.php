<?php

namespace App\Support\Campaigns;

use App\Models\AdCampaign;
use App\Models\AdCampaignStat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Compteurs des campagnes (affichages, clics, messages), tenus en cache par campagne et par jour,
 * puis recopiés dans ad_campaign_stats_daily (source auto) par flush() : aucune ligne n'est
 * écrite en base par affichage. flush() écrit des totaux, il peut être relancé sans doubler.
 */
class CampaignTracker
{
    public const METRICS = ['impressions', 'clicks', 'messages'];

    /** Durée de vie des compteurs : assez pour qu'un flush de la veille passe toujours. */
    private const TTL_DAYS = 3;

    /** @param iterable<int> $campaignIds une campagne par élément affiché */
    public static function impressions(iterable $campaignIds): void
    {
        foreach (collect($campaignIds)->countBy() as $id => $n) {
            self::add((int) $id, 'impressions', $n);
        }
    }

    public static function click(int $campaignId): void
    {
        self::add($campaignId, 'clicks');
    }

    public static function message(int $campaignId): void
    {
        self::add($campaignId, 'messages');
    }

    /** Valeur du compteur pour un jour (tests, suivi en direct). */
    public static function count(int $campaignId, string $metric, ?Carbon $day = null): int
    {
        return (int) Cache::get(self::key($campaignId, $metric, $day ?? today()), 0);
    }

    /**
     * Recopie les compteurs d'hier et d'aujourd'hui dans les statistiques par jour.
     * Renvoie le nombre de lignes écrites.
     */
    public static function flush(): int
    {
        $written = 0;
        $campaigns = AdCampaign::whereIn('status', [AdCampaign::STATUS_EN_COURS, AdCampaign::STATUS_EN_PAUSE, AdCampaign::STATUS_TERMINEE])
            ->whereDate('ends_on', '>=', today()->subDays(2)->toDateString())
            ->pluck('id');

        foreach ($campaigns as $id) {
            foreach ([today()->subDay(), today()] as $day) {
                $values = [];
                foreach (self::METRICS as $metric) {
                    $values[$metric] = self::count($id, $metric, $day);
                }
                if (array_sum($values) === 0) {
                    continue;
                }
                AdCampaignStat::updateOrCreate(
                    ['ad_campaign_id' => $id, 'day' => $day->toDateString(), 'source' => 'auto'],
                    $values
                );
                $written++;
            }
        }
        return $written;
    }

    private static function add(int $campaignId, string $metric, int $n = 1): void
    {
        $key = self::key($campaignId, $metric, today());
        Cache::add($key, 0, now()->addDays(self::TTL_DAYS));
        Cache::increment($key, $n);
    }

    private static function key(int $campaignId, string $metric, Carbon $day): string
    {
        return "adstat:{$campaignId}:{$day->toDateString()}:{$metric}";
    }
}
