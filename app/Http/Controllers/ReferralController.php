<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Services\SubscriptionService;
use App\Support\Referral;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

/**
 * Parrainage : lien public /r/{code} (garde le code 30 jours puis ouvre la page d'accueil)
 * et page Boutique → Parrainage du vendeur (son lien, ses filleuls, ses mois gagnés).
 */
class ReferralController extends Controller
{
    public function visit(string $code): RedirectResponse
    {
        $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $code), 0, 12));
        $shop = $code !== '' ? Shop::where('referral_code', $code)->first(['id', 'name']) : null;
        if (!$shop) return redirect()->route('landing');
        Cookie::queue(Referral::COOKIE, $code, Referral::COOKIE_DAYS * 24 * 60);
        return redirect()->route('landing', ['parrain' => 1]);
    }

    public function index(): View
    {
        $user = Auth::user();
        $shop = $user->shop;
        abort_unless($shop && (int) $shop->user_id === (int) $user->id, 403, 'Seul le propriétaire de la boutique peut parrainer.');

        $link = Referral::link($shop);
        $referred = Shop::where('referred_by_shop_id', $shop->id)->latest()->get(['id', 'name', 'created_at', 'referral_rewarded_at', 'plan', 'plan_expires_at']);
        $message = 'Je vends en ligne avec Shopio : boutique, commandes et livraisons au même endroit, paiement à la livraison. '
            . 'Crée ta boutique avec mon lien, tu as ' . SubscriptionService::REFERRAL_DAYS . ' jours de Pro en plus : ' . $link;

        return view('boutique.referral', [
            'shop'     => $shop,
            'link'     => $link,
            'referred' => $referred,
            'rewarded' => $referred->whereNotNull('referral_rewarded_at')->count(),
            'days'     => SubscriptionService::REFERRAL_DAYS,
            'trial'    => SubscriptionService::TRIAL_DAYS,
            'waShare'  => 'https://wa.me/?text=' . rawurlencode($message),
            'message'  => $message,
        ]);
    }
}
