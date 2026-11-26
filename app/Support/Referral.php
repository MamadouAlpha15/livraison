<?php

namespace App\Support;

use App\Models\Shop;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Parrainage : chaque boutique a un lien /r/{code}. Le lien garde le code 30 jours (cookie) ;
 * la boutique créée ensuite reçoit 1 mois de Pro en plus de son essai, et le parrain reçoit
 * 1 mois de Pro au premier vrai paiement de cette boutique (SubscriptionService::rewardReferrer).
 */
class Referral
{
    public const COOKIE = 'shopio_ref';
    public const COOKIE_DAYS = 30;

    /** Code d'invitation de la boutique (créé à la première demande). */
    public static function code(Shop $shop): string
    {
        if ($shop->referral_code) return $shop->referral_code;
        do { $code = Str::upper(Str::random(7)); } while (Shop::where('referral_code', $code)->exists());
        $shop->forceFill(['referral_code' => $code])->saveQuietly();
        return $code;
    }

    public static function link(Shop $shop): string
    {
        return url('/r/' . self::code($shop));
    }

    /** Boutique qui invite, d'après le cookie (null si absent, inconnu, ou si c'est la même personne). */
    public static function referrerFromRequest(Request $request, ?int $userId = null): ?Shop
    {
        $code = strtoupper((string) $request->cookie(self::COOKIE));
        if (!preg_match('/^[A-Z0-9]{5,12}$/', $code)) return null;
        $shop = Shop::where('referral_code', $code)->first();
        if (!$shop || ($userId && (int) $shop->user_id === $userId)) return null;
        return $shop;
    }

    /** À la création d'une boutique : lien avec le parrain et 1 mois de Pro en plus de l'essai. */
    public static function attach(Shop $shop, Request $request): ?Shop
    {
        $referrer = self::referrerFromRequest($request, (int) $shop->user_id);
        if (!$referrer || $shop->referred_by_shop_id) return null;
        $shop->forceFill(['referred_by_shop_id' => $referrer->id])->saveQuietly();
        app(SubscriptionService::class)->addProDays($shop->fresh(), SubscriptionService::REFERRAL_DAYS, 'parrainage', 'filleul-' . $shop->id);
        return $referrer;
    }
}
