{{--
    resources/views/dashboards/client.blade.php
    Route     : GET /client/dashboard → Client\DashboardController@index
    Variables :
      $shops        → LengthAwarePaginator<Shop>
      $recentOrders → Collection<Order>
      $myMessages   → Collection groupée par shopId-productId
      $myUnread     → int
      Auth::user()  → client connecté
--}}
@extends('layouts.app')

@section('title', 'Accueil — Marketplace')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link href="{{ \App\Support\Assets::v('fonts/dashboard-fonts.css') }}" rel="stylesheet">
@include('dashboards.client.styles')
@endpush

@section('content')
<script>window.SIco = {!! json_encode(collect(['package', 'check', 'alert', 'message', 'truck', 'x', 'eye', 'eye-off', 'sparkles', 'globe'])->mapWithKeys(fn ($n) => [$n => \App\Support\IconLibrary::svg($n, '', 18)])) !!};</script>

@php
/* ════════════════════════════════════
   LIBRAIRIE ICÔNES SVG PREMIUM (Lucide)
   ════════════════════════════════════ */
$_p = [
  // UI
  'x'          => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
  'check'      => '<polyline points="20 6 9 17 4 12"/>',
  'arrow-l'    => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
  'send'       => '<line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>',
  'search'     => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
  'chevron-r'  => '<polyline points="9 18 15 12 9 6"/>',
  // Navigation
  'home'       => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
  'store'      => '<path d="M3 9h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path d="M3 9l2-6h14l2 6"/><line x1="12" y1="9" x2="12" y2="21"/>',
  'grid'       => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
  'package'    => '<line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
  'chat'       => '<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>',
  'user'       => '<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>',
  'users'      => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>',
  'logout'     => '<path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
  // Commerce
  'truck'      => '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
  'bag'        => '<path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>',
  'tag'        => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>',
  'box'        => '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
  'party'      => '<path d="M5.8 11.3L2 22l10.7-3.79"/><path d="M22 2l-7.64 19.64a.5.5 0 01-.91.01L11 13 2.36 9.55a.5.5 0 01.01-.91z"/>',
  // Hero badges
  'shield-ok'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>',
  'lock'       => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>',
  'headset'    => '<path d="M3 18v-6a9 9 0 0118 0v6"/><path d="M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3z"/><path d="M3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/>',
  // Cartes boutique
  'pin'        => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>',
  'star-o'     => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
  'heart'      => '<path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>',
  'verified'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>',
  'trending'   => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
  // Catégories
  'utensils'   => '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 002-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 00-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>',
  'cart'       => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.97-1.67l1.63-9.33H6"/>',
  'bread'      => '<path d="M6 20h12"/><path d="M3.5 10h17a1 1 0 01.95 1.316l-1.5 5A2 2 0 0118 18H6a2 2 0 01-1.95-1.684l-1.5-5A1 1 0 013.5 10z"/><path d="M8 10V8a4 4 0 118 0v2"/>',
  'shirt'      => '<path d="M20.38 3.46L16 2a4 4 0 01-8 0L3.62 3.46a2 2 0 00-1.34 2.23l.58 3.57a1 1 0 00.99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 002-2V10h2.15a1 1 0 00.99-.84l.58-3.57a2 2 0 00-1.34-2.23z"/>',
  'gem'        => '<polygon points="6 3 18 3 22 9 12 22 2 9 6 3"/><line x1="2" y1="9" x2="22" y2="9"/><polyline points="12 3 6 9 12 22 18 9 12 3"/>',
  'smartphone' => '<rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>',
  'monitor'    => '<rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
  'phone-call' => '<path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.67a19.79 19.79 0 01-3.07-8.62A2 2 0 012 .01h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 14.92v2z"/>',
  'sparkles'   => '<path d="M12 3l1.68 5.17L19 9l-3.85 3.75.91 5.32L12 15.27 7.94 18.07l.91-5.32L5 9l5.32-.83z"/>',
  'medical'    => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>',
  'flower'     => '<circle cx="12" cy="12" r="3"/><circle cx="12" cy="5" r="2"/><circle cx="17.6" cy="8.4" r="2"/><circle cx="17.6" cy="15.6" r="2"/><circle cx="12" cy="19" r="2"/><circle cx="6.4" cy="15.6" r="2"/><circle cx="6.4" cy="8.4" r="2"/>',
  'car'        => '<path d="M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v9a2 2 0 01-2 2h-2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/>',
  'activity'   => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
  'book'       => '<path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>',
  'music'      => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
  'leaf'       => '<path d="M11 20A7 7 0 014 13c0-5 3.5-9.3 8-11 4.5 1.7 8 6 8 11a7 7 0 01-9 7z"/><path d="M11 20c0-5.5 2.5-10 6-13"/>',
  'wheat'      => '<path d="M2 22L16 8"/><path d="M3.5 12.5l8-8"/><path d="M7 9a4.5 4.5 0 006.5-6.5"/><path d="M10.5 18.5l5-5"/><path d="M13 16a4.5 4.5 0 006.5-6.5"/>',
  'health'     => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
  'wrench'     => '<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>',
  'shoe'       => '<path d="M2 18l5.5-11 3.5 4 3-3.5L21 18H2z"/><line x1="2" y1="18" x2="22" y2="18"/>',
  'handbag'    => '<path d="M16 11V7a4 4 0 00-8 0v4"/><rect x="3" y="7" width="18" height="13" rx="2" ry="2"/>',
  'globe'      => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>',
];
// Icône outline
$si = function(string $k, int $sz=18) use ($_p): string {
    return '<svg class="si" width="'.$sz.'" height="'.$sz.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">'.($_p[$k]??'').'</svg>';
};
// Icône remplie (étoile, etc.)
$sif = function(string $k, int $sz=18) use ($_p): string {
    return '<svg class="si" width="'.$sz.'" height="'.$sz.'" viewBox="0 0 24 24" fill="currentColor" stroke="none">'.($_p[$k]??'').'</svg>';
};
@endphp

@php
    // Paramètres de filtre/tri courants, réutilisés par tous les liens de catégorie
    // (sidebar, chips, "voir tout") pour ne jamais réinitialiser un filtre actif.
    $baseParams = ['s' => request('s'), 'cat' => request('cat'), 'min_price' => $minPrice, 'max_price' => $maxPrice, 'sort' => ($sort ?? 'newest') !== 'newest' ? $sort : null];
@endphp

@php
    $user      = auth()->user();
    $parts     = explode(' ', $user->name);
    $initials  = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
    $firstName = $parts[0];

    // Drapeau pays
    $countryFlag = '';
    if ($user->country) {
        $c = strtoupper($user->country);
        $countryFlag = mb_convert_encoding(
            '&#'.(127397+ord($c[0])).';&#'.(127397+ord($c[1])).';',
            'UTF-8', 'HTML-ENTITIES'
        );
    }

    $countryNames = [
        // Afrique de l'Ouest
        'BJ'=>'Bénin','BF'=>'Burkina Faso','CV'=>'Cap-Vert','CI'=>"Côte d'Ivoire",
        'GM'=>'Gambie','GH'=>'Ghana','GN'=>'Guinée','GW'=>'Guinée-Bissau','LR'=>'Libéria',
        'ML'=>'Mali','MR'=>'Mauritanie','NE'=>'Niger','NG'=>'Nigéria','SN'=>'Sénégal',
        'SL'=>'Sierra Leone','TG'=>'Togo',
        // Afrique Centrale
        'CM'=>'Cameroun','CF'=>'Centrafrique','TD'=>'Tchad','CG'=>'Congo','CD'=>'RD Congo',
        'GQ'=>'Guinée Équatoriale','GA'=>'Gabon','ST'=>'São Tomé','BI'=>'Burundi','RW'=>'Rwanda',
        // Afrique de l'Est
        'DJ'=>'Djibouti','ER'=>'Érythrée','ET'=>'Éthiopie','KE'=>'Kenya','KM'=>'Comores',
        'MG'=>'Madagascar','MW'=>'Malawi','MU'=>'Maurice','MZ'=>'Mozambique','SC'=>'Seychelles',
        'SO'=>'Somalie','SS'=>'Soudan du Sud','SD'=>'Soudan','TZ'=>'Tanzanie','UG'=>'Ouganda',
        'ZM'=>'Zambie','ZW'=>'Zimbabwe',
        // Afrique du Nord
        'DZ'=>'Algérie','EG'=>'Égypte','LY'=>'Libye','MA'=>'Maroc','TN'=>'Tunisie',
        // Afrique Australe
        'AO'=>'Angola','BW'=>'Botswana','LS'=>'Lesotho','NA'=>'Namibie','ZA'=>'Afrique du Sud','SZ'=>'Eswatini',
        // Europe
        'AL'=>'Albanie','DE'=>'Allemagne','AT'=>'Autriche','BE'=>'Belgique','BA'=>'Bosnie',
        'BG'=>'Bulgarie','HR'=>'Croatie','CY'=>'Chypre','DK'=>'Danemark','ES'=>'Espagne',
        'EE'=>'Estonie','FI'=>'Finlande','FR'=>'France','GR'=>'Grèce','HU'=>'Hongrie',
        'IE'=>'Irlande','IS'=>'Islande','IT'=>'Italie','LV'=>'Lettonie','LT'=>'Lituanie',
        'LU'=>'Luxembourg','MT'=>'Malte','MD'=>'Moldavie','MC'=>'Monaco','ME'=>'Monténégro',
        'NO'=>'Norvège','NL'=>'Pays-Bas','PL'=>'Pologne','PT'=>'Portugal','CZ'=>'Rép. Tchèque',
        'RO'=>'Roumanie','GB'=>'Royaume-Uni','RU'=>'Russie','RS'=>'Serbie','SK'=>'Slovaquie',
        'SI'=>'Slovénie','SE'=>'Suède','CH'=>'Suisse','UA'=>'Ukraine',
        // Amériques
        'AR'=>'Argentine','BR'=>'Brésil','CA'=>'Canada','CL'=>'Chili','CO'=>'Colombie',
        'CU'=>'Cuba','DO'=>'Rép. Dominicaine','EC'=>'Équateur','US'=>'États-Unis',
        'GT'=>'Guatemala','HT'=>'Haïti','MX'=>'Mexique','PA'=>'Panama','PE'=>'Pérou',
        'UY'=>'Uruguay','VE'=>'Venezuela',
        // Asie
        'SA'=>'Arabie Saoudite','AM'=>'Arménie','AZ'=>'Azerbaïdjan','BD'=>'Bangladesh',
        'CN'=>'Chine','KR'=>'Corée du Sud','AE'=>'Émirats Arabes','IN'=>'Inde',
        'ID'=>'Indonésie','IR'=>'Iran','IQ'=>'Irak','IL'=>'Israël','JP'=>'Japon',
        'JO'=>'Jordanie','KW'=>'Koweït','LB'=>'Liban','MY'=>'Malaisie','NP'=>'Népal',
        'OM'=>'Oman','PK'=>'Pakistan','PH'=>'Philippines','QA'=>'Qatar','SG'=>'Singapour',
        'LK'=>'Sri Lanka','TH'=>'Thaïlande','TR'=>'Turquie','VN'=>'Viêt Nam',
        // Océanie
        'AU'=>'Australie','NZ'=>'Nouvelle-Zélande',
    ];
    $countryName = $countryNames[$user->country ?? ''] ?? $user->country ?? '';
@endphp

@php
    $myMessages ??= collect();
    $myUnread   ??= 0;

    $statusMap = [
        'livrée'       => ['pill-livree',    '✓ Livrée'],
        'pending'      => ['pill-pending',   'En attente'],
        'en attente'   => ['pill-pending',   'En attente'],
        'en_attente'   => ['pill-pending',   'En attente'],
        'confirmée'    => ['pill-livraison', '✓ Confirmée'],
        'en_livraison' => ['pill-livraison', 'En livraison'],
        'annulée'      => ['pill-cancelled', '✕ Annulée'],
        'cancelled'    => ['pill-cancelled', '✕ Annulée'],
    ];

    $typeIco = [
        'Alimentation' => ['utensils','bg-food'],  'Restaurant'  => ['utensils','bg-food'],
        'Épicerie'     => ['cart','bg-food'],       'Boulangerie' => ['bread','bg-food'],
        'Vêtements'    => ['shirt','bg-fashion'],   'Bijouterie'  => ['gem','bg-fashion'],
        'Électronique' => ['smartphone','bg-tech'], 'Informatique'=> ['monitor','bg-tech'],
        'Téléphonie'   => ['phone-call','bg-tech'], 'Beauté & Cosmétiques' => ['sparkles','bg-beauty'],
        'Pharmacie'    => ['medical','bg-beauty'],  'Parfumerie'  => ['flower','bg-beauty'],
    ];

    // Catégories à afficher seulement si des boutiques de ce type existent
    $allTypes = ['Alimentation','Restaurant','Épicerie','Boulangerie','Vêtements','Bijouterie',
                 'Électronique','Informatique','Téléphonie','Beauté & Cosmétiques','Pharmacie','Parfumerie'];
    $activeType = request('type', '');
    $favCount   = count($favoriteIds ?? []);

    // Icône d'une catégorie de boutique (menu latéral et « Catégories populaires »)
    $sbIcoMap = [
        'alimentation'=>'utensils','restaurant'=>'utensils','épicerie'=>'cart','epicerie'=>'cart',
        'boulangerie'=>'bread','pâtisserie'=>'bread','patisserie'=>'bread',
        'vêtements'=>'shirt','vetements'=>'shirt','mode'=>'shirt',
        'bijouterie'=>'gem','bijoux'=>'gem',
        'électronique'=>'smartphone','electronique'=>'smartphone','informatique'=>'monitor',
        'téléphonie'=>'phone-call','telephonie'=>'phone-call',
        'beauté & cosmétiques'=>'sparkles','beaute & cosmetiques'=>'sparkles',
        'beauté'=>'sparkles','beaute'=>'sparkles','cosmétiques'=>'sparkles','cosmetiques'=>'sparkles',
        'pharmacie'=>'medical','parfumerie'=>'flower',
        'auto & moto'=>'car','automobile'=>'car',
        'sport'=>'activity','maison'=>'home','décoration'=>'home','decoration'=>'home',
        'librairie'=>'book','musique'=>'music','jardin'=>'leaf',
        'agriculture'=>'wheat','santé'=>'health','sante'=>'health',
        'construction'=>'wrench','quincaillerie'=>'wrench',
        'supermarché'=>'cart','supermarche'=>'cart',
        'chaussures'=>'shoe','accessoires'=>'handbag','sacs'=>'handbag',
    ];
    $getSbEmoji = function(string $t) use ($sbIcoMap, $si): string {
        $k = mb_strtolower(trim($t));
        if (isset($sbIcoMap[$k])) return $si($sbIcoMap[$k], 17);
        foreach ($sbIcoMap as $key => $icoKey) { if (str_contains($k, $key)) return $si($icoKey, 17); }
        return $si('store', 17);
    };
@endphp

{{-- ══ DRAWER MESSAGES ══ --}}
@include('dashboards.client.drawers')

{{-- ══ NAVBAR ══ --}}
@include('dashboards.client.navbar')

{{-- ══ PAGE WRAP ══ --}}
<div class="page-wrap">

{{-- ══ SIDEBAR ══ --}}
@include('dashboards.client.sidebar')

{{-- ══ MAIN COLUMN ══ --}}
<div class="main-col">

{{-- Notifications push — activation manuelle si la bannière automatique n'est pas apparue --}}
@include('dashboards.client.home.push-card')

{{-- Hero + statistiques --}}
@include('dashboards.client.home.hero')

{{-- Ventes Flash — défilement automatique, urgence --}}
@include('dashboards.client.home.flash-sales')

{{-- Flash --}}
@foreach(['success','danger'] as $t)
    @if(session($t))<div class="c-flash c-flash-{{ $t }}"><span>{{ $t === 'success' ? '✓' : '✕' }}</span>{{ session($t) }}</div>@endif
@endforeach

{{-- Commandes récentes --}}
@include('dashboards.client.home.recent-orders')

{{-- Boutiques à la une — campagnes payées, rien sans campagne en cours --}}
@include('partials.featured-shops')

{{-- Recommandé pour vous — défilement automatique en boucle infinie --}}
@include('dashboards.client.home.recommended')

{{-- Populaire par catégorie — meilleures ventes réelles, groupées ("Populaire en Parfums", "Populaire en Montres"…) --}}
@include('dashboards.client.home.popular-by-category')

{{-- Catégories populaires --}}
@include('dashboards.client.home.categories')

{{-- Tous les produits --}}
@include('dashboards.client.home.catalogue')

</div>{{-- /.main-col --}}
</div>{{-- /.page-wrap --}}

{{-- ══ BARRE NAVIGATION MOBILE (bas d'écran) ══ --}}
@include('dashboards.client.mobile-nav')

{{-- ══ ASSISTANT D'ACHAT IA ══ --}}
@include('dashboards.client.ai-assistant')

{{-- ══ MODALE CLASSEMENT ══ --}}
@include('dashboards.client.ranking-modal')

{{-- ══ MODALE PROFIL (3 onglets) ══ --}}
@include('dashboards.client.profile-modal')

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
<script>
@include('dashboards.client.scripts.navigation')

@include('dashboards.client.scripts.favorites-cart')

@include('dashboards.client.scripts.carousels')

@include('dashboards.client.scripts.chat')

@include('dashboards.client.scripts.profile')

@include('dashboards.client.scripts.message-polling')

@include('dashboards.client.scripts.category-filter')
</script>

{{-- ══════════════════════════════════════════════════
     NOTIFICATIONS TEMPS RÉEL — polling client
══════════════════════════════════════════════════ --}}
@include('dashboards.client.scripts.realtime-notifications')

{{-- ══════════════════════════════════════════════════
     CLOCHE NOTIFICATIONS CLIENT — polling temps réel
══════════════════════════════════════════════════ --}}
@include('dashboards.client.scripts.notification-bell')
@endpush