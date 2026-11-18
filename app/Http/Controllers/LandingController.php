<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopTestimonial;
use App\Services\ImageOptimizer;
use App\Services\SubscriptionService;
use App\Support\Growth;
use App\Support\Storefront;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Page d'accueil de Shopio (/) : présente les fonctionnalités aux vendeurs, avec de petites démos
 * animées (création de boutique, produit avec l'IA, page produit en sections, commandes, paniers
 * abandonnés, thèmes). Le marché des clients (produits, recherche) est à /marche (route « welcome »).
 * Les chiffres affichés sont réels (base de données) et n'apparaissent qu'à partir d'un vrai volume.
 */
class LandingController extends Controller
{
    /** En dessous, les chiffres ne sont pas affichés (une vitrine de 3 boutiques n'aide personne). */
    private const STATS_MIN_SHOPS = 50;

    public function index(): View
    {
        $stats = Cache::remember('landing.stats', 600, fn () => [
            'shops'     => Shop::where('is_approved', true)->count(),
            'products'  => Product::where('is_active', true)->count(),
            'delivered' => Order::where('status', 'livrée')->count(),
        ]);

        $user = Auth::user();
        // Où envoie le bouton principal : création de boutique (nouveau), tableau de bord (vendeur), marché (client)
        $cta = match (true) {
            !$user                                         => route('register', ['role' => 'admin']),
            $user->role === 'admin' && $user->shop_id      => route('boutique.dashboard'),
            $user->role === 'admin'                        => route('boutique.shops.create'),
            default                                        => route('welcome'),
        };

        // Témoignages validés par l'équipe Shopio, avec le vrai nombre de commandes livrées de chaque boutique
        $testimonials = ShopTestimonial::where('status', 'approved')
            ->whereHas('shop', fn ($q) => $q->where('is_approved', true))
            ->with('shop:id,name,slug,image,user_id')
            ->latest('approved_at')->take(6)->get()
            ->map(fn (ShopTestimonial $t) => [
                'text'      => $t->text,
                'rating'    => $t->rating,
                'author'    => $t->author,
                'role'      => $t->author_role,
                'shop'      => $t->shop->name,
                'url'       => Storefront::publicUrl($t->shop),
                'logo'      => $t->shop->image ? (ImageOptimizer::url($t->shop->image, 'thumb') ?? asset('storage/' . $t->shop->image)) : null,
                'initials'  => Growth::initials($t->shop->name),
                'delivered' => Order::where('shop_id', $t->shop_id)->where('status', 'livrée')->count(),
            ]);

        // WhatsApp du support (config/shopio.php) : sans numéro, le bouton n'apparaît pas
        $waDigits = preg_replace('/\D/', '', (string) config('shopio.support_whatsapp'));
        $whatsapp = strlen($waDigits) >= 8 ? 'https://wa.me/' . $waDigits . '?text=' . rawurlencode((string) config('shopio.support_whatsapp_text')) : null;

        return view('landing', [
            'testimonials' => $testimonials,
            'whatsapp'     => $whatsapp,
            'stats'      => $stats['shops'] >= self::STATS_MIN_SHOPS ? $stats : null,
            'trialDays'  => SubscriptionService::TRIAL_DAYS,
            // Tarifs réels : prix du Plan Pro (ChapChap Pay), durée d'un abonnement, limites du plan gratuit
            'pro'        => [
                'price'    => (int) config('chapchappay.plans.pro', 150000),
                'days'     => SubscriptionService::SUBSCRIPTION_DAYS,
                'products' => SubscriptionService::SHOP_FREE_MAX_PRODUCTS,
                'orders'   => SubscriptionService::SHOP_FREE_MAX_ORDERS,
            ],
            'cta'        => $cta,
            'ctaLabel'   => match (true) {
                !$user, $user->role === 'admin' && !$user->shop_id => 'Créer ma boutique',
                $user->role === 'admin'                           => 'Mon tableau de bord',
                default                                           => 'Voir le marché',
            },
            'user'       => $user,
        ]);
    }
}
