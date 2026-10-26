<?php

namespace App\Support\Campaigns;

use App\Models\AdCampaign;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;

/**
 * Attribution des commandes et des messages aux campagnes.
 *
 * Un clic sur un élément sponsorisé (lien avec ?c=<id de campagne>) est mémorisé 7 jours,
 * par boutique : dans un cookie chiffré sur le site (la session ne dure que 2 h), et côté
 * compte pour l'application mobile (pas de cookie). Une commande ou une conversation dans
 * la même boutique pendant ces 7 jours est attribuée à la campagne.
 */
class AdAttribution
{
    public const WINDOW_DAYS = 7;
    public const COOKIE = 'shopio_ad';

    /**
     * Clic sur une campagne : compté une fois par visiteur et par jour, attribution mise à jour à chaque fois.
     * Renvoie la campagne si elle est en cours, sinon null (lien périmé : rien n'est compté).
     */
    public static function registerClick(int $campaignId, Request $request, ?User $user = null): ?AdCampaign
    {
        $campaign = AdCampaign::find($campaignId);
        if (!$campaign || $campaign->status !== AdCampaign::STATUS_EN_COURS) {
            return null;
        }

        if (Cache::add('adclick:' . $campaign->id . ':' . self::visitor($request, $user) . ':' . today()->toDateString(), 1, now()->addDay())) {
            CampaignTracker::click($campaign->id);
        }

        $entry = [$campaign->id, now()->timestamp];
        if ($request->hasSession()) {
            $map = self::cookieMap($request);
            $map[$campaign->shop_id] = $entry;
            Cookie::queue(self::COOKIE, json_encode($map), self::WINDOW_DAYS * 24 * 60);
        }
        if ($user) {
            Cache::put(self::userKey($user, $campaign->shop_id), $entry, now()->addDays(self::WINDOW_DAYS));
        }
        return $campaign;
    }

    /** Campagne à copier dans orders.ad_campaign_id pour une commande dans cette boutique, ou null. */
    public static function campaignForOrder(int $shopId, ?User $user = null, ?Request $request = null): ?int
    {
        return self::lookup($shopId, $user, $request ?? request());
    }

    /**
     * Conversation ouverte avec la boutique : comptée comme message de la campagne,
     * une seule fois par client et par campagne.
     */
    public static function noteMessage(int $shopId, ?User $user = null, ?Request $request = null): void
    {
        $request ??= request();
        $campaignId = self::lookup($shopId, $user, $request);
        if ($campaignId && Cache::add('admsg:' . $campaignId . ':' . self::visitor($request, $user), 1, now()->addDays(self::WINDOW_DAYS + 1))) {
            CampaignTracker::message($campaignId);
        }
    }

    private static function lookup(int $shopId, ?User $user, Request $request): ?int
    {
        $entries = [];
        if ($request->hasSession() || $request->cookies->has(self::COOKIE)) {
            $entries[] = self::cookieMap($request)[$shopId] ?? null;
        }
        if ($user) {
            $entries[] = Cache::get(self::userKey($user, $shopId));
        }

        // Le clic le plus récent encore dans la fenêtre de 7 jours
        $best = collect($entries)->filter(fn ($e) => is_array($e) && count($e) === 2
                && $e[1] >= now()->subDays(self::WINDOW_DAYS)->timestamp)
            ->sortByDesc(fn ($e) => $e[1])->first();
        if (!$best) {
            return null;
        }
        $campaign = AdCampaign::whereKey((int) $best[0])->where('shop_id', $shopId)->first(['id']);
        return $campaign?->id;
    }

    private static function cookieMap(Request $request): array
    {
        $map = json_decode((string) $request->cookie(self::COOKIE, ''), true);
        return is_array($map) ? array_filter($map, 'is_array') : [];
    }

    private static function userKey(User $user, int $shopId): string
    {
        return "adattr:user:{$user->id}:{$shopId}";
    }

    /** Identifiant anonyme du visiteur, pour ne compter qu'un clic ou un message par personne. */
    private static function visitor(Request $request, ?User $user): string
    {
        $raw = $user ? 'u' . $user->id : ($request->hasSession() ? 's' . $request->session()->getId() : 'ip' . $request->ip());
        return substr(sha1($raw), 0, 20);
    }
}
