<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Models\Shop;
use App\Support\Storefront;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Création de la boutique en 8 petites étapes, une question par écran (façon Sellio) :
 * bienvenue → nom → expérience → ventes → équipe → livraison → pays → WhatsApp,
 * puis « Création en cours… » et le tableau de bord.
 * Les réponses (expérience, ventes, équipe, livraison) sont gardées dans shops.onboarding.
 */
class ShopOnboardingController extends Controller
{
    /** Pays proposés : [nom, indicatif, chiffres du numéro sans l'indicatif (min, max)]. */
    public const COUNTRIES = [
        'GN' => ['Guinée', '224', 9, 9],
        'SN' => ['Sénégal', '221', 9, 9],
        'ML' => ['Mali', '223', 8, 8],
        'CI' => ['Côte d’Ivoire', '225', 10, 10],
        'BF' => ['Burkina Faso', '226', 8, 8],
        'NE' => ['Niger', '227', 8, 8],
        'TG' => ['Togo', '228', 8, 8],
        'BJ' => ['Bénin', '229', 8, 10],
        'CM' => ['Cameroun', '237', 9, 9],
        'GA' => ['Gabon', '241', 7, 9],
        'CG' => ['Congo', '242', 9, 9],
        'CD' => ['RD Congo', '243', 9, 9],
        'MR' => ['Mauritanie', '222', 8, 8],
        'MA' => ['Maroc', '212', 9, 9],
        'TN' => ['Tunisie', '216', 8, 8],
        'DZ' => ['Algérie', '213', 9, 9],
        'NG' => ['Nigeria', '234', 10, 10],
        'GH' => ['Ghana', '233', 9, 9],
        'FR' => ['France', '33', 9, 9],
        'BE' => ['Belgique', '32', 8, 9],
        'CA' => ['Canada', '1', 10, 10],
        'US' => ['États-Unis', '1', 10, 10],
    ];

    /** Questions à choix : clé => [libellé, icône]. */
    public const EXPERIENCE = [
        'debute'  => ['Je débute dans le e-commerce', 'sprout'],
        'reseaux' => ['Je vends sur les réseaux sociaux', 'phone'],
        'boutique'=> ['J’ai déjà une boutique en ligne', 'store'],
    ];

    /** Ventes par mois, en commandes (la même question quel que soit le pays et la devise). */
    public const SALES = [
        'aucune'  => ['Pas encore de ventes', 'seed'],
        'lt30'    => ['Moins de 30 commandes', 'bar1'],
        '30_100'  => ['30 à 100 commandes', 'bar2'],
        '100_500' => ['100 à 500 commandes', 'bar3'],
        'gt500'   => ['Plus de 500 commandes', 'rocket'],
    ];

    public const TEAM = [
        'seul'  => ['Je suis seul(e)', 'user'],
        '2_5'   => ['2 à 5 personnes', 'users'],
        '6_20'  => ['6 à 20 personnes', 'users'],
        'gt20'  => ['Plus de 20 personnes', 'building'],
    ];

    public const DELIVERY = [
        'aucune'   => ['Pas encore de solution', 'help'],
        'agence'   => ['Une entreprise de livraison', 'truck'],
        'livreurs' => ['Mes propres livreurs', 'bike'],
        'les_deux' => ['Les deux', 'both'],
    ];

    public function create()
    {
        $user = Auth::user();
        $country = strtoupper((string) $user->country);
        return view('vendeur.shop.onboarding', [
            'user'      => $user,
            'country'   => isset(self::COUNTRIES[$country]) ? $country : 'GN',
            'countries' => self::COUNTRIES,
            'questions' => [
                'experience' => self::EXPERIENCE,
                'sales'      => self::SALES,
                'team'       => self::TEAM,
                'delivery'   => self::DELIVERY,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'min:2', 'max:60'],
            'experience' => ['required', Rule::in(array_keys(self::EXPERIENCE))],
            'sales'      => ['required', Rule::in(array_keys(self::SALES))],
            'team'       => ['required', Rule::in(array_keys(self::TEAM))],
            'delivery'   => ['required', Rule::in(array_keys(self::DELIVERY))],
            'country'    => ['required', Rule::in(array_keys(self::COUNTRIES))],
            'whatsapp'   => ['required', 'string', 'max:24'],
        ], [
            'name.required'     => 'Donne un nom à ta boutique.',
            'name.min'          => 'Le nom est trop court.',
            'whatsapp.required' => 'Entre ton numéro WhatsApp.',
        ]);

        // Numéro : chiffres seulement, sans l'indicatif du pays
        [$cname, $dial, $min, $max] = self::COUNTRIES[$data['country']];
        $digits = preg_replace('/\D/', '', $data['whatsapp']);
        if (str_starts_with($digits, '00' . $dial)) $digits = substr($digits, 2 + strlen($dial));
        elseif (str_starts_with($digits, $dial) && strlen($digits) > $max) $digits = substr($digits, strlen($dial));
        if ($data['country'] === 'FR' && strlen($digits) === 10 && $digits[0] === '0') $digits = substr($digits, 1);
        if (strlen($digits) < $min || strlen($digits) > $max || ($data['country'] === 'GN' && $digits[0] !== '6')) {
            return response()->json(['errors' => ['whatsapp' => ['Ce numéro ne semble pas valide pour ' . $cname . '.']]], 422);
        }

        $user = Auth::user();
        $name = trim(preg_replace('/\s+/', ' ', strip_tags($data['name'])));

        $shop = DB::transaction(function () use ($user, $data, $name, $dial, $digits) {
            $shop = new Shop([
                'name'     => $name,
                'email'    => $user->email,
                'phone'    => '+' . $dial . $digits,
                'country'  => $data['country'],
                'currency' => DeliveryCompany::currencyForCountry($data['country']),
            ]);
            $shop->user_id     = $user->id;
            $shop->is_approved = true;   // approbation automatique, les limites sont gérées par le plan
            $shop->plan        = 'free'; // écrasé juste après par startShopTrial()
            $shop->slug        = self::freeSlug($name);
            $shop->onboarding  = [
                'experience' => $data['experience'],
                'sales'      => $data['sales'],
                'team'       => $data['team'],
                'delivery'   => $data['delivery'],
                'at'         => now()->toIso8601String(),
            ];
            $shop->save();

            // Essai gratuit du Plan Pro, comme pour toute nouvelle boutique
            app(\App\Services\SubscriptionService::class)->startShopTrial($shop);
            // Invitée par un lien de parrainage : 1 mois de Pro en plus (le parrain sera récompensé au 1er paiement)
            \App\Support\Referral::attach($shop->fresh(), request());

            // Le vendeur devient admin de sa boutique
            $user->update(['shop_id' => $shop->id, 'role_in_shop' => 'admin']);
            return $shop;
        });

        if (session('payment_intent') === 'pro') {
            session()->forget('payment_intent');
            $to = route('payment.checkout', ['type' => 'shop', 'id' => $shop->id]);
        } else {
            session()->flash('success', 'Ta boutique « ' . $shop->name . ' » est prête. Tu profites d’un essai gratuit du Plan Pro.');
            $to = route('boutique.dashboard');
        }

        return response()->json(['redirect' => $to, 'slug' => $shop->slug]);
    }

    /** Adresse /s/{slug} libre tirée du nom (« Maison Kadi » → maison-kadi, puis maison-kadi-2…). */
    public static function freeSlug(string $name): string
    {
        $base = Storefront::slugify($name);
        if (mb_strlen($base) < 3) $base = trim($base . '-boutique', '-');
        $slug = $base;
        for ($i = 2; Storefront::slugError($slug, null) !== null && $i < 200; $i++) {
            $slug = mb_substr($base, 0, 36) . '-' . $i;
        }
        return $slug;
    }
}
