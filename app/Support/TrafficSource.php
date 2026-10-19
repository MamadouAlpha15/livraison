<?php

namespace App\Support;

use App\Models\Shop;
use App\Models\MarketingCampaign;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\ShopTraffic;
use App\Models\ShopVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * D'où viennent les visiteurs et les commandes d'une boutique.
 *
 * Source d'une arrivée, par ordre de priorité :
 *  1. le paramètre ?utm_source=… des liens de suivi (page Marketing du vendeur) ;
 *  2. le site d'où vient le visiteur (Facebook, Instagram, TikTok, WhatsApp, Google…) ;
 *  3. « direct » (lien tapé, favori, application qui ne transmet pas sa provenance).
 *
 * Une visite est comptée une fois par jour et par boutique pour une même session, jamais
 * pour le propriétaire, l'aperçu de la vitrine ou les robots. La dernière source connue
 * (hors « direct » et hors navigation interne) est gardée 7 jours en session puis
 * attribuée à la commande (orders.source).
 */
class TrafficSource
{
    /** Sources reconnues => libellé affiché au vendeur. */
    public const LABELS = [
        'facebook'  => 'Facebook',
        'instagram' => 'Instagram',
        'tiktok'    => 'TikTok',
        'whatsapp'  => 'WhatsApp',
        'qr'        => 'QR code',
        'google'    => 'Google',
        'shopio'    => 'Marketplace Shopio',
        'app'       => 'Application Shopio',
        'autre'     => 'Autres sites',
        'direct'    => 'Accès direct',
    ];

    /** Canaux proposés pour les liens de suivi (page Marketing). */
    public const LINK_CHANNELS = ['facebook', 'instagram', 'tiktok', 'whatsapp', 'qr'];

    /** Raccourcis acceptés dans utm_source. */
    private const ALIASES = ['fb' => 'facebook', 'ig' => 'instagram', 'tt' => 'tiktok', 'wa' => 'whatsapp', 'qrcode' => 'qr', 'qr_code' => 'qr'];

    /** Fragments de domaine => source. */
    private const HOSTS = [
        'facebook' => 'facebook', 'fb.com' => 'facebook', 'fb.me' => 'facebook', 'messenger.com' => 'facebook',
        'instagram' => 'instagram',
        'tiktok' => 'tiktok',
        'whatsapp' => 'whatsapp', 'wa.me' => 'whatsapp',
        'google.' => 'google',
    ];

    public const ATTRIBUTION_DAYS = 7;

    public static function label(?string $source): string
    {
        return self::LABELS[$source ?? 'direct'] ?? self::LABELS['autre'];
    }

    /** Lien vers une page de la boutique, marqué avec la source (utm_source / utm_medium / utm_campaign). */
    public static function tag(string $url, string $source, string $medium = 'social', ?string $campaign = null): string
    {
        $sep = str_contains($url, '?') ? '&' : '?';
        return $url . $sep . http_build_query(array_filter(['utm_source' => $source, 'utm_medium' => $medium, 'utm_campaign' => $campaign]));
    }

    /** Source de la requête courante, ou null pour une navigation interne sans utm. */
    public static function detect(Request $request): ?string
    {
        $utm = strtolower(trim((string) $request->query('utm_source', '')));
        if ($utm !== '') {
            $utm = self::ALIASES[$utm] ?? $utm;
            return isset(self::LABELS[$utm]) && $utm !== 'direct' ? $utm : 'autre';
        }

        $referer = (string) $request->headers->get('referer', '');
        $host = strtolower((string) parse_url($referer, PHP_URL_HOST));
        if ($host === '') return 'direct';
        if ($host === strtolower($request->getHost())) return null;

        foreach (self::HOSTS as $needle => $source) {
            if (str_contains($host, $needle)) return $source;
        }
        return 'autre';
    }

    /**
     * Enregistre l'arrivée d'un visiteur sur la vitrine ou une fiche produit de la boutique.
     * $isPreview : aperçu de l'éditeur de vitrine (jamais compté).
     */
    public static function recordLanding(Shop $shop, Request $request, bool $isPreview = false): void
    {
        if ($isPreview || self::isOwner($shop) || self::isBot($request)) return;

        $detected = self::detect($request);
        $campaign = self::campaignFrom($shop, $request);
        if ($campaign && ($detected === null || $detected === 'direct' || $detected === 'autre')) {
            $detected = $campaign->channel; // lien de campagne sans utm_source reconnu
        }
        $session = $request->session();

        // Attribution : la dernière source connue l'emporte, sauf « direct » qui n'écrase rien.
        $attrKey = 'shop_attr.' . $shop->id;
        if ($detected !== null && ($detected !== 'direct' || !$session->has($attrKey))) {
            $session->put($attrKey, ['source' => $detected, 'campaign' => $campaign?->id, 'at' => now()->timestamp]);
        }

        // Comptage : une fois par jour et par boutique pour cette session.
        $seenKey = 'shop_seen.' . $shop->id;
        if ($session->get($seenKey) === now()->toDateString()) return;
        $session->put($seenKey, now()->toDateString());

        ShopTraffic::record($shop->id, $detected ?? 'shopio', (int) $campaign?->id);
        // Même visite pour les compteurs du tableau de bord (aujourd'hui / semaine / mois)
        ShopVisit::record($shop->id);
    }

    /**
     * Visite depuis l'application mobile (API sans session) : une par utilisateur — ou, sans
     * compte, par appareil (IP + navigateur) — par boutique et par jour, retenue dans le cache.
     */
    public static function recordAppVisit(Shop $shop, Request $request): void
    {
        $user = $request->user();
        if (self::isBot($request) || ($user && ((int) $user->id === (int) $shop->user_id || (int) $user->shop_id === (int) $shop->id))) return;

        $who = $user ? 'u' . $user->id : sha1($request->ip() . '|' . $request->userAgent());
        $key = 'shop_app_visit:' . $shop->id . ':' . $who . ':' . now()->toDateString();
        if (!Cache::add($key, 1, now()->endOfDay())) return;

        ShopTraffic::record($shop->id, 'app');
        ShopVisit::record($shop->id);
    }

    /** Compte une vue de fiche produit (une par visiteur, par produit et par jour). */
    public static function recordProductView(Product $product, Request $request): void
    {
        $shop = $product->shop;
        if (!$shop || self::isOwner($shop) || self::isBot($request)) return;

        $key = 'product_seen.' . $product->id;
        if ($request->session()->get($key) === now()->toDateString()) return;
        $request->session()->put($key, now()->toDateString());

        ProductView::record($product->id, $shop->id);
    }

    /** Campagne du lien (?utm_campaign=slug), si elle existe et est active dans cette boutique. */
    private static function campaignFrom(Shop $shop, Request $request): ?MarketingCampaign
    {
        $slug = strtolower(trim((string) $request->query('utm_campaign', '')));
        if ($slug === '' || strlen($slug) > 40) return null;
        return MarketingCampaign::where('shop_id', $shop->id)->where('slug', $slug)->active()->first();
    }

    /** Attribution en cours pour cette boutique (≤ 7 jours), sinon null. */
    private static function attribution(int $shopId): ?array
    {
        $attr = session('shop_attr.' . $shopId);
        if (!is_array($attr) || empty($attr['source'])) return null;
        if (now()->timestamp - (int) ($attr['at'] ?? 0) > self::ATTRIBUTION_DAYS * 86400) return null;
        return $attr;
    }

    /** Source à enregistrer sur une nouvelle commande de cette boutique (null si inconnue). */
    public static function forOrder(int $shopId): ?string
    {
        $attr = self::attribution($shopId);
        return $attr && isset(self::LABELS[$attr['source']]) ? $attr['source'] : null;
    }

    /** Campagne à enregistrer sur une nouvelle commande de cette boutique (null si aucune). */
    public static function campaignForOrder(int $shopId): ?int
    {
        $id = (int) (self::attribution($shopId)['campaign'] ?? 0);
        return $id > 0 ? $id : null;
    }

    /** Code promo de la campagne par laquelle le client est arrivé (proposé sur la fiche produit). */
    public static function campaignPromoId(int $shopId): ?int
    {
        $id = self::campaignForOrder($shopId);
        return $id ? MarketingCampaign::whereKey($id)->where('shop_id', $shopId)->value('promo_code_id') : null;
    }

    private static function isOwner(Shop $shop): bool
    {
        $user = Auth::user();
        return $user && ((int) $user->id === (int) $shop->user_id || (int) $user->shop_id === (int) $shop->id);
    }

    private static function isBot(Request $request): bool
    {
        return (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|facebookcatalog|preview|headless/i', (string) $request->userAgent());
    }
}
