{{--
    resources/views/client/orders/create_from_product.blade.php
    Route     : GET /client/orders/create-from-product/{product}
    Rôle      : fiche produit + tunnel de commande en 3 étapes (Adresse → Paiement → Confirmation),
                sur la même page. Le formulaire envoie les mêmes champs qu'avant à client.orders.storeProduct.
    Variables : $product, $variants, $selectedVariantId, $activePromo, $relatedProducts, $loyaltyBalance,
                $productRating, $productReviewCount, $productReviews, $shopRating, $shopReviewCount,
                $isFavorited, $cartCount
--}}
@extends('layouts.app')
@section('title', $product->name . ' · ' . ($product->shop->name ?? 'Shopio'))
@php $bodyClass = 'is-dashboard'; @endphp

@php
    use App\Services\ImageOptimizer;
    use App\Support\IconLibrary as Ico;

    $shop     = $product->shop;
    $devise   = $shop->currency ?? 'GNF';
    $fmt      = fn ($n) => number_format((float) $n, 0, ',', ' ');

    $isFlash  = $product->is_flash_active;
    $hasPromo = $product->original_price && $product->original_price > $product->price;
    $nowPrice = (float) $product->current_price;
    $oldPrice = $isFlash ? (float) $product->price : ($hasPromo ? (float) $product->original_price : null);
    $pct      = $isFlash ? $product->flash_discount_percent : ($hasPromo ? (int) round((1 - $product->price / $product->original_price) * 100) : 0);

    // Photos : principale + galerie, en taille moyenne (affichage) et miniature (vignettes)
    $gallery  = json_decode($product->gallery ?? '[]', true) ?: [];
    $paths    = array_values(array_filter(array_merge([$product->image], $gallery)));
    $photos   = array_map(fn ($p) => ImageOptimizer::url($p, 'medium') ?? asset('storage/' . $p), $paths);
    $thumbs   = array_map(fn ($p) => ImageOptimizer::url($p, 'thumb') ?? asset('storage/' . $p), $paths);

    // Stock : avec des variantes, c'est le stock de chaque option qui compte
    $stockVal = $product->stock;
    $stockOut = $variants->isEmpty() && $stockVal !== null && $stockVal <= 0;
    // Mode vacances (éditeur de boutique) : produit visible, commande et panier en pause
    $onVacation = \App\Support\Storefront::onVacation($shop);
    // Mode WhatsApp (Faire grandir ma vitrine → Conversion) : commande enregistrée ici, puis envoyée à la boutique sur WhatsApp
    $waMode = $shop->phone && \App\Support\Storefront::settings($shop)['wa_mode'];
    $stockLow = $variants->isEmpty() && $stockVal !== null && $stockVal > 0 && $stockVal <= 5;

    $here      = url()->current();
    $loginUrl  = route('login', ['redirect' => $here]);
    $chatUrl   = route('client.messages.index', $product);
    $offerUrl  = auth()->check() ? $chatUrl . '?offre=1' : route('register', ['redirect' => $chatUrl . '?offre=1', 'role' => 'client']);
    $writeUrl  = auth()->check() ? $chatUrl : route('register', ['redirect' => $chatUrl, 'role' => 'client']);
    $shopUrl   = route('public.shops.products', $shop);
    $catUrl    = $product->category ? url('/') . '?category=' . urlencode($product->category) : null;
    $initials  = collect(preg_split('/\s+/', trim($shop->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

    // Communes et villes proposées à la livraison (boutiques en Guinée) ; sinon, champ libre
    $isGuinea = ($shop->country ?? 'GN') === 'GN';
    $communes = ['Conakry' => ['Kaloum', 'Dixinn', 'Matam', 'Matoto', 'Ratoma'],
                 'Autres villes' => ['Coyah', 'Dubréka', 'Kindia', 'Boké', 'Kamsar', 'Mamou', 'Labé', 'Faranah', 'Kankan', 'Siguiri', 'Kissidougou', 'Guéckédou', 'N\'Zérékoré']];

    $authUser  = auth()->user();
    // Carnet d'adresses : l'adresse principale pré-remplit le formulaire
    $savedAddresses = $authUser ? \App\Models\ClientAddress::forUser($authUser->id) : collect();
    $oldDest   = old('delivery_destination', $savedAddresses->firstWhere('is_default', true)?->address ?? $authUser->address ?? '');

    // Aperçu du lien partagé (WhatsApp, Facebook, Messenger) : photo, nom, prix et boutique
    $ogPrice = $fmt($nowPrice) . ' ' . $devise . ($oldPrice ? ' au lieu de ' . $fmt($oldPrice) . ' ' . $devise : '');
    $ogDesc  = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $product->description)));
    $ogDesc  = \Illuminate\Support\Str::limit($ogPrice . ' · ' . $shop->name . ($ogDesc !== '' ? ' — ' . $ogDesc : ''), 200);
    $ogImg   = $photos[0] ?? ($shop->image ? ImageOptimizer::url($shop->image, 'medium') : asset('images/shopio-logo-192.png'));
@endphp

@section('description', $ogDesc)
@push('meta')
<meta property="og:type" content="product">
<meta property="og:title" content="{{ $product->name }} — {{ $fmt($nowPrice) }} {{ $devise }}">
<meta property="og:description" content="{{ $ogDesc }}">
<meta property="og:image" content="{{ $ogImg }}">
<meta property="og:url" content="{{ $here }}">
<meta property="og:site_name" content="Shopio">
<meta property="product:price:amount" content="{{ number_format($nowPrice, 2, '.', '') }}">
<meta property="product:price:currency" content="{{ $devise }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $product->name }} — {{ $fmt($nowPrice) }} {{ $devise }}">
<meta name="twitter:description" content="{{ $ogDesc }}">
<meta name="twitter:image" content="{{ $ogImg }}">
@endpush

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* Fiche produit Shopio : colonne galerie + colonne achat, puis cartes d'information ; tunnel en 3 pages */
:root {
    --pp-blue: #1F7CF6;      --pp-btn: #1668D8;      --pp-btn-hover: #1260C9;
    --pp-blue-50: #EAF2FF;   --pp-blue-100: #D6E6FF; --pp-navy: #0B1E3D;
    --pp-amber: #F59E0B;     --pp-amber-ink: #16162A;
    --pp-bg: #F6F9FF;        --pp-surface: #FFFFFF;  --pp-surface-2: #F1F5FD;
    --pp-ink: #16162A;       --pp-ink-2: #45455E;    --pp-ink-3: #62627C;
    --pp-line: #E3E8F2;
    --pp-ok: #15803D;        --pp-warn: #B45309;     --pp-err: #B91C1C;
    --pp-font: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    --pp-r-lg: 28px; --pp-r: 20px; --pp-r-sm: 14px;
    --pp-shadow: 0 1px 2px rgba(11,30,61,.04), 0 8px 28px rgba(11,30,61,.06);
    --pp-ease-in: cubic-bezier(.22,1,.36,1);   --pp-ease-out: cubic-bezier(.55,0,1,.45);
    --pp-ease-move: cubic-bezier(.65,0,.35,1); --pp-ease-pop: cubic-bezier(.34,1.56,.64,1);
}
html[data-theme="dark"] {
    --pp-blue: #4C95FA;      --pp-btn: #1668D8;      --pp-btn-hover: #2A78E4;
    --pp-blue-50: #12284A;   --pp-blue-100: #1A3560;
    --pp-bg: #0A1426;        --pp-surface: #101D35;  --pp-surface-2: #15253F;
    --pp-ink: #EEF3FC;       --pp-ink-2: #C3CCDD;    --pp-ink-3: #9AA6BC;
    --pp-line: #22324F;
    --pp-ok: #4ADE80;        --pp-warn: #FBBF24;     --pp-err: #F87171;
    --pp-shadow: 0 1px 2px rgba(0,0,0,.3), 0 10px 30px rgba(0,0,0,.25);
    color-scheme: dark;
}
body { background: var(--pp-bg); color: var(--pp-ink); margin: 0; }
.pp, .pp *, .pp *::before, .pp *::after { box-sizing: border-box; }
.pp { font-family: var(--pp-font); font-size: 15px; line-height: 1.55; color: var(--pp-ink); -webkit-font-smoothing: antialiased; }
.pp .ico { display: inline-block; flex-shrink: 0; vertical-align: -3px; }
.pp a { color: inherit; }
.pp button { font-family: inherit; }
.pp :focus-visible { outline: 3px solid var(--pp-blue); outline-offset: 2px; border-radius: 10px; }
.pp-num { font-variant-numeric: tabular-nums; white-space: nowrap; }
.pp-wrap { max-width: 1200px; margin: 0 auto; padding-left: max(16px, env(safe-area-inset-left, 0px)); padding-right: max(16px, env(safe-area-inset-right, 0px)); }

/* ── En-tête ── */
.pp-head { position: sticky; top: env(safe-area-inset-top, 0px); z-index: 50; background: color-mix(in srgb, var(--pp-surface) 88%, transparent); backdrop-filter: blur(12px); border-bottom: 1px solid var(--pp-line); }
.pp-head-in { display: flex; align-items: center; gap: 12px; min-height: 68px; }
.pp-logo { display: inline-flex; align-items: center; gap: 10px; font-weight: 800; font-size: 20px; text-decoration: none; color: var(--pp-ink); letter-spacing: -.2px; }
.pp-logo img { width: 38px; height: 38px; border-radius: 11px; }
.pp-head-search { margin-left: auto; display: flex; align-items: center; gap: 6px; background: var(--pp-surface); border: 1px solid var(--pp-line); border-radius: 999px; padding: 4px 4px 4px 14px; }
.pp-head-search input { border: 0; outline: 0; background: transparent; color: var(--pp-ink); font: inherit; font-size: 15px; width: 220px; min-width: 0; }
.pp-head-search button { border: 0; background: var(--pp-blue-50); color: var(--pp-btn); font-weight: 700; font-size: 14px; border-radius: 999px; padding: 8px 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
.pp-icon-btn { position: relative; width: 44px; height: 44px; border-radius: 999px; border: 1px solid var(--pp-line); background: var(--pp-surface); color: var(--pp-ink); display: inline-flex; align-items: center; justify-content: center; text-decoration: none; cursor: pointer; flex-shrink: 0; transition: border-color .18s, transform .18s var(--pp-ease-pop); }
.pp-icon-btn:hover { border-color: var(--pp-blue); }
.pp-badge { position: absolute; top: -5px; right: -5px; min-width: 20px; height: 20px; padding: 0 5px; border-radius: 999px; background: var(--pp-amber); color: var(--pp-amber-ink); font-size: 11px; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 2px solid var(--pp-surface); }
.pp-badge.is-pop { animation: ppPop .45s var(--pp-ease-pop); }
.pp-head-search-m { display: none; }

/* ── Fil d'Ariane ── */
.pp-crumbs { display: flex; flex-wrap: wrap; gap: 6px; font-size: 13.5px; color: var(--pp-ink-3); padding: 18px 0 14px; }
.pp-crumbs a { color: var(--pp-ink-2); text-underline-offset: 3px; }
.pp-crumbs [aria-current] { color: var(--pp-ink); font-weight: 600; }

.pp-flash { display: flex; align-items: center; gap: 8px; padding: 12px 16px; border-radius: var(--pp-r-sm); font-weight: 600; margin-bottom: 14px; }
.pp-flash-ok { background: color-mix(in srgb, var(--pp-ok) 12%, var(--pp-surface)); color: var(--pp-ok); }
.pp-flash-err { background: color-mix(in srgb, var(--pp-err) 10%, var(--pp-surface)); color: var(--pp-err); }

/* ── Produit : galerie + achat ── */
.pp-top { display: grid; grid-template-columns: minmax(0, 1.08fr) minmax(0, 1fr); gap: 48px; align-items: start; }
.pp-gallery { position: sticky; top: 92px; }
.pp-vacation { margin: 0; padding: 12px 14px; border-radius: var(--pp-r-sm); background: #FEF3E0; color: #92400E; font-weight: 700; font-size: 14.5px; }
.pp-stage { position: relative; aspect-ratio: 1 / 1; border-radius: var(--pp-r-lg); background: radial-gradient(120% 90% at 30% 20%, var(--pp-surface) 0%, var(--pp-blue-50) 70%); overflow: hidden; cursor: zoom-in; border: 1px solid var(--pp-line); }
.pp-stage img { width: 100%; height: 100%; object-fit: cover; display: block; transition: opacity .2s ease, transform .5s var(--pp-ease-in); }
.pp-stage:hover img { transform: scale(1.03); }
.pp-stage-ph { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: var(--pp-blue); opacity: .5; }
.pp-stage-count { position: absolute; right: 16px; bottom: 16px; background: rgba(11,30,61,.78); color: #fff; font-size: 13px; font-weight: 700; padding: 5px 12px; border-radius: 999px; }
.pp-stage-nav { position: absolute; top: 50%; translate: 0 -50%; width: 44px; height: 44px; border-radius: 50%; border: 0; background: rgba(255,255,255,.92); color: #16162A; font-size: 24px; line-height: 1; cursor: pointer; box-shadow: 0 4px 14px rgba(11,30,61,.18); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity .2s; }
.pp-stage:hover .pp-stage-nav, .pp-stage-nav:focus-visible { opacity: 1; }
.pp-stage-prev { left: 14px; } .pp-stage-next { right: 14px; }
.pp-stage-flag { position: absolute; left: 16px; top: 16px; display: inline-flex; align-items: center; gap: 5px; background: var(--pp-amber); color: var(--pp-amber-ink); font-weight: 800; font-size: 13px; padding: 6px 12px; border-radius: 999px; }
.pp-thumbs { display: flex; gap: 12px; margin-top: 14px; overflow-x: auto; padding: 4px 2px; scrollbar-width: none; }
.pp-thumbs::-webkit-scrollbar { display: none; }
.pp-thumb { flex: 0 0 84px; height: 84px; border-radius: 18px; border: 2px solid transparent; padding: 0; background: var(--pp-surface-2); cursor: pointer; overflow: hidden; transition: border-color .18s, transform .18s var(--pp-ease-pop); }
.pp-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pp-thumb:hover { transform: translateY(-2px); }
.pp-thumb.is-active { border-color: var(--pp-btn); }

.pp-shopline { display: inline-flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 600; color: var(--pp-ink-2); text-decoration: none; }
.pp-shopline .ico { color: var(--pp-ok); }
.pp-title { font-size: clamp(28px, 3.4vw, 40px); line-height: 1.12; font-weight: 800; letter-spacing: -.6px; margin: 10px 0 12px; text-wrap: balance; }
.pp-rating { display: flex; align-items: center; gap: 8px; font-size: 14.5px; color: var(--pp-ink-2); }
.pp-rating .star-ico.is-filled { color: var(--pp-amber); }
.pp-rating .star-ico.is-empty { color: var(--pp-line); }
.pp-rating a { color: var(--pp-ink-2); text-underline-offset: 3px; }
.pp-rating strong { color: var(--pp-ink); }
.pp-price-row { display: flex; align-items: baseline; flex-wrap: wrap; gap: 10px 14px; margin: 18px 0 8px; }
.pp-price { font-size: clamp(30px, 3.6vw, 40px); font-weight: 800; letter-spacing: -.8px; }
.pp-old { font-size: 17px; color: var(--pp-ink-3); text-decoration: line-through; }
.pp-off { background: var(--pp-amber); color: var(--pp-amber-ink); font-weight: 800; font-size: 13px; padding: 4px 10px; border-radius: 999px; }
.pp-countdown { display: inline-flex; align-items: center; gap: 8px; background: color-mix(in srgb, var(--pp-amber) 18%, var(--pp-surface)); color: var(--pp-ink); border-radius: 999px; padding: 7px 14px; font-weight: 700; font-size: 14px; margin-bottom: 8px; }
.pp-countdown .ico { color: var(--pp-warn); }
.pp-stock { display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 15px; margin: 6px 0 20px; }
.pp-stock::before { content: ''; width: 9px; height: 9px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
.pp-stock.is-ok { color: var(--pp-ok); } .pp-stock.is-low { color: var(--pp-warn); } .pp-stock.is-out { color: var(--pp-err); }
.pp-stock span { font-weight: 500; color: var(--pp-ink-2); }

.pp-promo { width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 10px; border: 1.5px dashed var(--pp-blue); background: var(--pp-blue-50); color: var(--pp-ink); border-radius: var(--pp-r-sm); padding: 10px 14px; font-size: 14px; font-weight: 600; margin-bottom: 14px; cursor: pointer; text-align: left; }
.pp-promo strong { letter-spacing: .5px; }
.pp-promo-cta { flex-shrink: 0; background: var(--pp-btn); color: #fff; border-radius: 999px; padding: 6px 12px; font-size: 13px; font-weight: 700; }

/* Boîte d'achat */
.pp-buy { background: var(--pp-surface); border: 1px solid var(--pp-line); border-radius: var(--pp-r); padding: 18px; box-shadow: var(--pp-shadow); display: grid; gap: 14px; }
.pp-variants-lbl { font-size: 14px; font-weight: 700; margin-bottom: 8px; }
.pp-variants { display: flex; flex-wrap: wrap; gap: 8px; }
.pp-pill { min-height: 44px; padding: 0 16px; border-radius: 999px; border: 1.5px solid var(--pp-line); background: var(--pp-surface); color: var(--pp-ink); font-size: 14px; font-weight: 600; cursor: pointer; transition: border-color .18s, background .18s, transform .2s var(--pp-ease-pop); }
.pp-pill:hover:not(:disabled) { border-color: var(--pp-blue); }
.pp-pill.is-selected { background: var(--pp-btn); border-color: var(--pp-btn); color: #fff; transform: translateY(-1px); }
.pp-pill:disabled { opacity: .5; text-decoration: line-through; cursor: not-allowed; }
.pp-field-err { color: var(--pp-err); font-size: 14px; font-weight: 600; margin-top: 8px; }
.pp-qty-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.pp-qty-row label { font-weight: 700; font-size: 15px; }
.pp-stepper { display: inline-flex; align-items: center; border: 1px solid var(--pp-line); border-radius: 14px; overflow: hidden; background: var(--pp-surface); }
.pp-stepper button { width: 46px; height: 46px; border: 0; background: transparent; color: var(--pp-ink); font-size: 20px; font-weight: 600; cursor: pointer; }
.pp-stepper button:hover { background: var(--pp-blue-50); }
.pp-stepper button:active { transform: scale(.9); }
.pp-stepper input { width: 52px; height: 46px; border: 0; text-align: center; font: inherit; font-weight: 800; font-size: 16px; color: var(--pp-ink); background: transparent; -moz-appearance: textfield; }
.pp-stepper input::-webkit-outer-spin-button, .pp-stepper input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.pp-buy-main { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 10px; }
.pp-buy-alt { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.pp-note { margin: 0; font-size: 13.5px; color: var(--pp-ink-3); line-height: 1.5; }

/* Boutons */
.pp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 48px; padding: 0 20px; border-radius: 999px; font-weight: 700; font-size: 15px; text-decoration: none; cursor: pointer; border: 1.5px solid transparent; transition: background .18s, border-color .18s, color .18s, transform .2s var(--pp-ease-pop), box-shadow .18s; white-space: nowrap; }
.pp-btn:active:not(:disabled) { transform: scale(.97); }
.pp-btn-primary { background: var(--pp-btn); color: #fff; box-shadow: 0 8px 20px -6px rgba(22,104,216,.55); }
.pp-btn-primary:hover { background: var(--pp-btn-hover); color: #fff; }
.pp-btn-primary:disabled { opacity: .6; cursor: progress; }
.pp-btn-ghost { background: var(--pp-surface); color: var(--pp-ink); border-color: var(--pp-line); }
.pp-btn-ghost:hover { border-color: var(--pp-blue); color: var(--pp-ink); }
.pp-btn-ghost.is-done { border-color: var(--pp-ok); color: var(--pp-ok); }
.pp-btn-lg { min-height: 54px; font-size: 16px; }
.pp-fav { width: 54px; height: 54px; }
.pp-fav.is-fav { color: #E11D48; border-color: #FBCFE8; }
.pp-fav.is-fav svg { fill: currentColor; }
.pp-fav.is-pop svg { animation: ppPop .45s var(--pp-ease-pop); }

/* Garanties */
.pp-assure { list-style: none; margin: 22px 0 0; padding: 0; display: grid; gap: 14px; }
.pp-assure li { display: flex; align-items: center; gap: 14px; font-size: 15px; color: var(--pp-ink-2); }
.pp-assure strong { color: var(--pp-ink); }
.pp-assure-ico { width: 40px; height: 40px; border-radius: 12px; background: var(--pp-blue-50); color: var(--pp-btn); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

/* ── Cartes d'information ── */
.pp-section { margin-top: 56px; }
.pp-h2 { font-size: clamp(24px, 2.6vw, 30px); font-weight: 800; letter-spacing: -.4px; margin: 0 0 18px; display: flex; align-items: baseline; flex-wrap: wrap; gap: 10px; }
.pp-h2 small { font-size: 17px; font-weight: 700; color: var(--pp-ink-3); }
.pp-info { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start; }
.pp-card { background: var(--pp-surface); border: 1px solid var(--pp-line); border-radius: var(--pp-r); padding: 24px; box-shadow: var(--pp-shadow); }
.pp-card h2 { font-size: 22px; font-weight: 800; margin: 0 0 12px; }
.pp-desc { color: var(--pp-ink-2); margin: 0 0 18px; white-space: pre-line; overflow-wrap: anywhere; }
.pp-specs { display: grid; grid-template-columns: max-content 1fr; gap: 10px 28px; margin: 0; font-size: 14.5px; }
.pp-specs dt { color: var(--pp-ink-3); }
.pp-specs dd { margin: 0; font-weight: 600; }
.pp-seller { display: flex; align-items: center; gap: 14px; }
.pp-seller-logo { width: 54px; height: 54px; border-radius: 16px; background: var(--pp-btn); color: #fff; font-weight: 800; font-size: 19px; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; }
.pp-seller-logo img { width: 100%; height: 100%; object-fit: cover; }
.pp-seller-name { font-size: 19px; font-weight: 800; margin: 0; }
.pp-verified { display: inline-flex; align-items: center; gap: 5px; color: var(--pp-ok); font-size: 13.5px; font-weight: 700; }
.pp-seller-txt { color: var(--pp-ink-2); margin: 14px 0 0; }
.pp-seller-meta { display: flex; flex-wrap: wrap; gap: 8px 16px; margin-top: 10px; font-size: 14px; color: var(--pp-ink-3); }
.pp-seller-meta .star-ico.is-filled { color: var(--pp-amber); }
.pp-seller-meta .star-ico.is-empty { color: var(--pp-line); }
.pp-seller-cta { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }

/* Avis */
.pp-reviews { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.pp-review { background: var(--pp-surface); border: 1px solid var(--pp-line); border-radius: var(--pp-r); padding: 20px; }
.pp-review .star-ico.is-filled { color: var(--pp-amber); }
.pp-review .star-ico.is-empty { color: var(--pp-line); }
.pp-review p { margin: 8px 0 10px; color: var(--pp-ink); overflow-wrap: anywhere; }
.pp-review-meta { font-size: 13.5px; color: var(--pp-ink-3); }
.pp-empty { background: var(--pp-surface); border: 1px dashed var(--pp-line); border-radius: var(--pp-r); padding: 22px; color: var(--pp-ink-2); }

/* Produits liés : cartes inclinables avec reflet */
.pp-related { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; }
.pp-rcard { --rx: 0deg; --ry: 0deg; --mx: 50%; --my: 50%; position: relative; display: block; text-decoration: none; background: var(--pp-surface); border: 1px solid var(--pp-line); border-radius: var(--pp-r); overflow: hidden; transform: perspective(900px) rotateX(var(--rx)) rotateY(var(--ry)); transition: transform .25s var(--pp-ease-in), box-shadow .25s, border-color .25s; }
.pp-rcard::after { content: ''; position: absolute; inset: 0; pointer-events: none; background: radial-gradient(260px circle at var(--mx) var(--my), rgba(255,255,255,.35), transparent 60%); opacity: 0; transition: opacity .25s; }
.pp-rcard:hover { box-shadow: 0 18px 40px -16px rgba(11,30,61,.3); border-color: var(--pp-blue-100); }
.pp-rcard:hover::after { opacity: 1; }
.pp-rcard-img { aspect-ratio: 1 / 1; background: var(--pp-blue-50); position: relative; }
.pp-rcard-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pp-rcard-img .pp-off { position: absolute; left: 12px; top: 12px; }
.pp-rcard-body { padding: 14px 16px 16px; }
.pp-rcard-shop { font-size: 13px; color: var(--pp-ink-3); }
.pp-rcard-name { font-weight: 700; color: var(--pp-ink); margin: 2px 0 6px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 3.1em; }
.pp-rcard-price { font-weight: 800; font-size: 16.5px; color: var(--pp-ink); }
.pp-rcard-price s { font-weight: 500; font-size: 13.5px; color: var(--pp-ink-3); margin-left: 6px; }

/* Barre d'achat mobile */
.pp-mbar { position: fixed; left: 0; right: 0; bottom: 0; z-index: 60; display: none; align-items: center; gap: 12px; background: var(--pp-surface); border-top: 1px solid var(--pp-line); box-shadow: 0 -10px 30px rgba(11,30,61,.1); padding: 10px max(16px, env(safe-area-inset-right, 0px)) calc(10px + env(safe-area-inset-bottom, 0px)) max(16px, env(safe-area-inset-left, 0px)); transform: translateY(110%); transition: transform .35s var(--pp-ease-in); }
.pp-mbar.is-visible { transform: none; }
.pp-mbar-price { display: flex; flex-direction: column; min-width: 0; }
.pp-mbar-price small { font-size: 12.5px; color: var(--pp-ink-3); font-weight: 600; }
.pp-mbar-price strong { font-size: 18px; font-weight: 800; }
.pp-mbar .pp-btn { margin-left: auto; }

/* ── Tunnel de commande ── */
.ck { padding-bottom: 60px; }
.ck-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; padding: 18px 0 6px; }
.ck-secure { display: inline-flex; align-items: center; gap: 8px; color: var(--pp-ok); font-weight: 700; font-size: 14px; }
.ck-steps { list-style: none; margin: 18px 0 30px; padding: 0; display: grid; grid-template-columns: repeat(3, 1fr); position: relative; }
.ck-steps::before, .ck-steps-fill { content: ''; position: absolute; left: 0; right: 0; bottom: -14px; height: 4px; border-radius: 4px; background: var(--pp-line); }
.ck-steps-fill { background: var(--pp-btn); transform-origin: left center; transform: scaleX(var(--p, .333)); transition: transform .6s var(--pp-ease-move); }
.ck-step { display: flex; align-items: center; gap: 10px; font-weight: 700; color: var(--pp-ink-3); }
.ck-step:nth-child(3) { justify-content: center; } .ck-step:nth-child(4) { justify-content: flex-end; }
.ck-step-dot { width: 34px; height: 34px; border-radius: 50%; background: var(--pp-surface-2); color: var(--pp-ink-2); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 800; transition: background .3s, color .3s, transform .4s var(--pp-ease-pop); }
.ck-step.is-current, .ck-step.is-done { color: var(--pp-ink); }
.ck-step.is-current .ck-step-dot { background: var(--pp-btn); color: #fff; transform: scale(1.06); }
.ck-step.is-done .ck-step-dot { background: var(--pp-blue-100); color: var(--pp-btn); }
.ck-grid { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr); gap: 32px; align-items: start; perspective: 1400px; }
.ck-panel { background: var(--pp-surface); border: 1px solid var(--pp-line); border-radius: var(--pp-r-lg); padding: 32px; box-shadow: var(--pp-shadow); transform-origin: left center; }
.ck-panel.is-in-fwd { animation: ckTurnFwd .6s var(--pp-ease-in) backwards; }
.ck-panel.is-in-back { animation: ckTurnBack .6s var(--pp-ease-in) backwards; transform-origin: right center; }
.ck-panel h2 { font-size: clamp(24px, 2.6vw, 32px); font-weight: 800; letter-spacing: -.5px; margin: 0 0 22px; }
.pp .ck-panel h2:focus, .pp .ck-panel h2:focus-visible { outline: none; } /* focus posé par le script pour les lecteurs d'écran */
.ck-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.ck-field { display: grid; gap: 7px; }
.ck-field.is-wide { grid-column: 1 / -1; }
/* Carnet d'adresses */
.ck-addr-chips { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin: 0 0 16px; }
.ck-addr-chip { display: inline-flex; align-items: center; gap: 6px; min-height: 40px; padding: 0 16px; border: none; border-radius: 999px; background: rgba(22,22,42,.07); color: var(--pp-ink-2, #45455E); font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; transition: background-color .18s, color .18s; }
.ck-addr-chip:hover { background: rgba(22,22,42,.12); }
.ck-addr-chip.is-active { background: var(--pp-btn, #1668D8); color: #fff; }
.ck-addr-manage { font-size: 13.5px; font-weight: 700; color: var(--pp-btn, #1668D8); }
.ck-field .ck-save-addr { display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 14px; color: var(--pp-ink-2, #45455E); cursor: pointer; margin-top: 2px; }
.ck-save-addr input { width: 18px; height: 18px; accent-color: var(--pp-btn, #1668D8); }
.ck-field label { font-weight: 700; font-size: 14.5px; }
.ck-input { width: 100%; min-height: 52px; border: 1.5px solid var(--pp-line); border-radius: var(--pp-r-sm); padding: 0 16px; font: inherit; font-size: 16px; color: var(--pp-ink); background: var(--pp-surface); outline: none; transition: border-color .18s, box-shadow .18s; }
select.ck-input { appearance: auto; }
.ck-input::placeholder { color: var(--pp-ink-3); }
.ck-input:focus { border-color: var(--pp-btn); box-shadow: 0 0 0 4px var(--pp-blue-50); }
.ck-input.is-invalid { border-color: var(--pp-err); }
.ck-input.is-valid { border-color: var(--pp-ok); }
.ck-hint { font-size: 13.5px; font-weight: 600; min-height: 0; }
.ck-hint.is-error { color: var(--pp-err); } .ck-hint.is-ok { color: var(--pp-ok); }
.ck-who { display: flex; align-items: center; gap: 10px; background: var(--pp-blue-50); border-radius: var(--pp-r-sm); padding: 12px 14px; margin-bottom: 18px; color: var(--pp-ink-2); }
.ck-who strong { color: var(--pp-ink); }
.ck-actions { display: flex; justify-content: space-between; gap: 12px; margin-top: 26px; padding-top: 20px; border-top: 1px solid var(--pp-line); }
.ck-actions .pp-btn-primary { min-width: 200px; }

.ck-pay { display: grid; gap: 12px; }
.ck-pay-opt { display: flex; align-items: center; gap: 14px; border: 1.5px solid var(--pp-line); border-radius: var(--pp-r); padding: 16px; cursor: pointer; background: var(--pp-surface); transition: border-color .2s, box-shadow .25s, transform .3s var(--pp-ease-pop); }
.ck-pay-opt input { position: absolute; opacity: 0; pointer-events: none; }
.ck-pay-opt:hover { border-color: var(--pp-blue); }
.ck-pay-opt.is-active { border-color: var(--pp-btn); transform: translateY(-4px); box-shadow: 0 16px 30px -14px rgba(22,104,216,.45); }
.ck-pay-opt:focus-within { outline: 3px solid var(--pp-blue); outline-offset: 2px; }
.ck-pay-ico { width: 48px; height: 48px; border-radius: 14px; background: var(--pp-blue-50); color: var(--pp-btn); display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: background .2s, color .2s; }
.ck-pay-opt.is-active .ck-pay-ico { background: var(--pp-btn); color: #fff; }
.ck-pay-ico.is-spin { animation: ckCoin .7s var(--pp-ease-move); }
.ck-pay-txt { display: grid; gap: 2px; }
.ck-pay-txt strong { font-size: 16px; }
.ck-pay-txt span { font-size: 14px; color: var(--pp-ink-3); }
.ck-pay-check { margin-left: auto; width: 24px; height: 24px; border-radius: 50%; border: 2px solid var(--pp-line); display: flex; align-items: center; justify-content: center; color: transparent; flex-shrink: 0; }
.ck-pay-opt.is-active .ck-pay-check { border-color: var(--pp-btn); background: var(--pp-btn); color: #fff; }
.ck-sub { font-size: 16px; font-weight: 800; margin: 26px 0 10px; }
.ck-promo { display: flex; gap: 8px; }
.ck-promo .ck-input { text-transform: uppercase; letter-spacing: .5px; }
.ck-msg { font-size: 14px; font-weight: 600; margin-top: 8px; }
.ck-msg.is-ok { color: var(--pp-ok); } .ck-msg.is-error { color: var(--pp-err); }
.ck-points { display: flex; align-items: center; gap: 10px; font-weight: 600; cursor: pointer; }
.ck-points input[type="checkbox"] { width: 20px; height: 20px; accent-color: var(--pp-btn); }
.ck-points-row { display: flex; gap: 8px; margin-top: 10px; }
.ck-points-row .ck-input { max-width: 160px; }

.ck-recap { display: grid; grid-template-columns: max-content 1fr; gap: 12px 24px; margin: 0; }
.ck-recap dt { color: var(--pp-ink-3); }
.ck-recap dd { margin: 0; font-weight: 600; overflow-wrap: anywhere; }
.ck-recap-edit { background: none; border: 0; color: var(--pp-btn); font-weight: 700; cursor: pointer; padding: 0; margin-left: 8px; text-decoration: underline; text-underline-offset: 3px; font-size: 14px; }

.ck-sum { background: var(--pp-surface); border: 1px solid var(--pp-line); border-radius: var(--pp-r-lg); padding: 26px; box-shadow: var(--pp-shadow); position: sticky; top: 92px; }
.ck-sum h2 { font-size: 22px; font-weight: 800; margin: 0 0 18px; }
.ck-sum-item { display: grid; grid-template-columns: 72px minmax(0, 1fr) auto; gap: 14px; align-items: center; padding-bottom: 18px; border-bottom: 1px solid var(--pp-line); }
.ck-sum-img { width: 72px; height: 72px; border-radius: 16px; background: var(--pp-blue-50); overflow: hidden; display: flex; align-items: center; justify-content: center; color: var(--pp-btn); }
.ck-sum-img img { width: 100%; height: 100%; object-fit: cover; }
.ck-sum-name { font-weight: 700; line-height: 1.35; }
.ck-sum-meta { font-size: 13.5px; color: var(--pp-ink-3); }
.ck-sum-var { font-size: 13.5px; color: var(--pp-btn); font-weight: 700; }
.ck-sum-rows { display: grid; gap: 10px; padding: 16px 0; border-bottom: 1px solid var(--pp-line); }
.ck-row { display: flex; justify-content: space-between; gap: 12px; color: var(--pp-ink-2); }
.ck-row .pp-num { color: var(--pp-ink); }
.ck-row.is-discount, .ck-row.is-discount .pp-num { color: var(--pp-ok); }
.ck-total { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; padding-top: 16px; font-size: 18px; font-weight: 800; }
.ck-total .pp-num { font-size: 20px; display: inline-block; }
.ck-total .pp-num.is-bump { animation: ppPop .4s var(--pp-ease-pop); }
.ck-sum-foot { margin: 12px 0 0; font-size: 13.5px; color: var(--pp-ink-3); }

/* ── Animations ── */
@keyframes ppPop { 0% { transform: scale(1); } 40% { transform: scale(1.18); } 100% { transform: scale(1); } }
@keyframes ckTurnFwd { from { opacity: 0; transform: rotateY(-16deg) translateX(40px); } }
@keyframes ckTurnBack { from { opacity: 0; transform: rotateY(16deg) translateX(-40px); } }
@keyframes ckCoin { from { transform: rotateY(0); } to { transform: rotateY(360deg); } }
@keyframes ppRise { from { opacity: 0; transform: translateY(24px); } }
.pp-enter { animation: ppRise .6s var(--pp-ease-in) backwards; }
.pp-enter-2 { animation-delay: .08s; }
@supports (animation-timeline: view()) {
    .pp-reveal { animation: ppRise linear backwards; animation-timeline: view(); animation-range: entry 0% entry 35%; }
}

/* ── Responsive ── */
@media (max-width: 960px) {
    .pp-top { grid-template-columns: 1fr; gap: 26px; }
    .pp-gallery { position: static; }
    .pp-info { grid-template-columns: 1fr; }
    .pp-related { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ck-grid { grid-template-columns: 1fr; }
    .ck-sum { position: static; }
}
@media (max-width: 640px) {
    .pp { font-size: 16px; }
    .pp-head-search { display: none; }
    .pp-head-search-m { display: inline-flex; margin-left: auto; }
    .pp-head-in { min-height: 60px; }
    .pp-crumbs { padding-top: 12px; }
    .pp-stage { border-radius: 22px; margin-inline: -4px; }
    .pp-stage-nav { opacity: 1; width: 40px; height: 40px; }
    .pp-thumb { flex-basis: 68px; height: 68px; border-radius: 14px; }
    .pp-buy { padding: 16px; }
    .pp-buy-alt { grid-template-columns: 1fr; }
    .pp-section { margin-top: 40px; }
    .pp-card { padding: 20px; }
    .pp-reviews { grid-template-columns: 1fr; }
    .pp-related { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; gap: 12px; margin-inline: -16px; padding: 4px 16px 10px; scrollbar-width: none; }
    .pp-related::-webkit-scrollbar { display: none; }
    .pp-rcard { flex: 0 0 66%; scroll-snap-align: start; }
    .pp-mbar { display: flex; }
    .pp-main { padding-bottom: 96px; }
    .ck-steps { margin-top: 8px; }
    .ck-step { font-size: 14px; gap: 6px; }
    .ck-panel { padding: 22px 18px; border-radius: 22px; }
    .ck-fields { grid-template-columns: 1fr; }
    .ck-actions { flex-direction: column-reverse; }
    .ck-actions .pp-btn { width: 100%; }
    .ck-sum { padding: 18px; border-radius: 22px; }
    .ck-recap { grid-template-columns: 1fr; gap: 2px; }
    .ck-recap dd { margin-bottom: 10px; }
}
@media (max-width: 380px) {
    .ck-step-lbl { font-size: 13px; }
    .pp-buy-main .pp-btn-lg { font-size: 15px; padding: 0 14px; }
}
@media (prefers-reduced-motion: reduce) {
    .pp *, .pp *::before, .pp *::after { animation-duration: .01ms !important; animation-delay: 0s !important; transition-duration: .01ms !important; }
    .ck-panel.is-in-fwd, .ck-panel.is-in-back { animation: none; }
    .pp-rcard { transform: none !important; }
    .pp-reveal { animation: none; }
}

/* ── Visionneuse ── */
.pp-lb { position: fixed; inset: 0; z-index: 9000; background: rgba(6,14,28,.94); display: flex; align-items: center; justify-content: center; }
.pp-lb[hidden] { display: none; }
.pp-lb img { max-width: 92vw; max-height: 86vh; object-fit: contain; border-radius: 16px; }
.pp-lb-btn { position: absolute; width: 48px; height: 48px; border-radius: 50%; border: 1px solid rgba(255,255,255,.25); background: rgba(255,255,255,.1); color: #fff; font-size: 26px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.pp-lb-close { top: calc(16px + env(safe-area-inset-top, 0px)); right: 16px; }
.pp-lb-prev { left: 16px; top: 50%; translate: 0 -50%; } .pp-lb-next { right: 16px; top: 50%; translate: 0 -50%; }
.pp-lb-count { position: absolute; bottom: calc(18px + env(safe-area-inset-bottom, 0px)); left: 50%; translate: -50% 0; color: #fff; font-weight: 700; font-size: 14px; }
</style>
@endpush

@section('content')
<div class="pp">

{{-- ═══ EN-TÊTE ═══ --}}
<header class="pp-head">
    <div class="pp-wrap pp-head-in">
        <a href="{{ url('/') }}" class="pp-logo">
            <img src="/images/shopio-logo-192.png" alt="" width="38" height="38">
            Shopio
        </a>
        <form class="pp-head-search" action="{{ url('/') }}" method="GET" role="search">
            <label for="ppSearch" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)">Rechercher un produit</label>
            <input type="search" id="ppSearch" name="s" placeholder="Que cherchez-vous ?" autocomplete="off">
            <button type="submit">{!! Ico::svg('search', '', 16) !!} Rechercher</button>
        </form>
        <a href="{{ url('/') }}" class="pp-icon-btn pp-head-search-m" aria-label="Rechercher sur Shopio">{!! Ico::svg('search', '', 19) !!}</a>
        @auth
        <a href="{{ route('client.cart.index') }}" class="pp-icon-btn" aria-label="Panier ({{ $cartCount }} articles)" id="ppCartLink">
            {!! Ico::svg('bag', '', 19) !!}
            <span class="pp-badge" id="ppCartBadge" @if($cartCount < 1) hidden @endif>{{ $cartCount }}</span>
        </a>
        @else
        <a href="{{ $loginUrl }}" class="pp-icon-btn" aria-label="Se connecter">{!! Ico::svg('user', '', 19) !!}</a>
        @endauth
    </div>
</header>

{{-- ═══ FICHE PRODUIT ═══ --}}
<main class="pp-main" id="productView">
<div class="pp-wrap">

    <nav class="pp-crumbs" aria-label="Fil d'Ariane">
        <a href="{{ url('/') }}">Accueil</a> /
        @if($catUrl)<a href="{{ $catUrl }}">{{ $product->category }}</a> /@endif
        <span aria-current="page">{{ Str::limit($product->name, 60) }}</span>
    </nav>

    @if(session('success'))
    <div class="pp-flash pp-flash-ok" role="status">{!! Ico::svg('check', '', 16) !!} {{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="pp-flash pp-flash-err" role="alert">{!! Ico::svg('alert', '', 16) !!} {{ $errors->first() }}</div>
    @endif

    <div class="pp-top">
        {{-- Galerie --}}
        <div class="pp-gallery pp-enter">
            <div class="pp-stage" id="ppStage" role="button" tabindex="0" aria-label="Agrandir la photo">
                @if(count($photos))
                    <img src="{{ $photos[0] }}" alt="{{ $product->name }}" id="ppMainImg" width="800" height="800" fetchpriority="high">
                @else
                    <div class="pp-stage-ph">{!! Ico::categorySvg($product->category, '', 96) !!}</div>
                @endif
                @if($pct > 0)
                    <span class="pp-stage-flag">@if($isFlash){!! Ico::svg('zap', '', 14) !!} Vente flash @endif −{{ $pct }} %</span>
                @endif
                @if(count($photos) > 1)
                    <button type="button" class="pp-stage-nav pp-stage-prev" data-photo-step="-1" aria-label="Photo précédente">&#8249;</button>
                    <button type="button" class="pp-stage-nav pp-stage-next" data-photo-step="1" aria-label="Photo suivante">&#8250;</button>
                    <span class="pp-stage-count pp-num" id="ppPhotoCount">1 / {{ count($photos) }}</span>
                @endif
            </div>
            @if(count($photos) > 1)
            <div class="pp-thumbs" id="ppThumbs">
                @foreach($thumbs as $i => $t)
                <button type="button" class="pp-thumb {{ $i === 0 ? 'is-active' : '' }}" data-photo="{{ $i }}" aria-label="Photo {{ $i + 1 }} sur {{ count($thumbs) }}">
                    <img src="{{ $t }}" alt="" loading="lazy" width="84" height="84">
                </button>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Infos + achat --}}
        <div class="pp-enter pp-enter-2">
            <a href="{{ $shopUrl }}" class="pp-shopline">{!! Ico::svg('shield', '', 16) !!} {{ $shop->name }} · Boutique vérifiée</a>
            <h1 class="pp-title">{{ $product->name }}</h1>

            <div class="pp-rating">
                @if($productRating)
                    {!! Ico::stars($productRating, 15) !!}
                    <strong class="pp-num">{{ number_format($productRating, 1, ',', ' ') }}</strong>
                    <a href="#avis">{{ $productReviewCount }} avis</a>
                @else
                    {!! Ico::stars(0, 15) !!} <span>Pas encore d'avis</span>
                @endif
            </div>

            <div class="pp-price-row">
                <span class="pp-price pp-num"><span id="ppPrice">{{ $fmt($nowPrice) }}</span> {{ $devise }}</span>
                @if($oldPrice)<s class="pp-old pp-num">{{ $fmt($oldPrice) }} {{ $devise }}</s>@endif
                @if($pct > 0)<span class="pp-off">−{{ $pct }} %</span>@endif
            </div>

            @if($isFlash)
            <div class="pp-countdown" id="ppCountdown" data-ends="{{ $product->flash_ends_at->timestamp }}">
                {!! Ico::svg('clock', '', 15) !!} Vente flash : se termine dans <span class="pp-num" id="ppCountdownTxt">--:--:--</span>
            </div>
            @endif

            @if($variants->isEmpty())
                @if($stockOut)
                    <p class="pp-stock is-out">Rupture de stock</p>
                @elseif($stockLow)
                    <p class="pp-stock is-low">Plus que {{ $stockVal }} en stock <span>· commandez vite</span></p>
                @elseif($stockVal !== null)
                    <p class="pp-stock is-ok">En stock <span>· {{ $stockVal }} disponibles</span></p>
                @else
                    <p class="pp-stock is-ok">Disponible</p>
                @endif
            @else
                <p class="pp-stock is-ok" id="ppStockLine">{{ $variants->where('out_of_stock', false)->count() }} options disponibles</p>
            @endif

            @if($activePromo)
            <button type="button" class="pp-promo" id="ppPromoBanner">
                <span>{!! Ico::svg('gift', '', 15) !!} Code <strong>{{ $activePromo->code }}</strong> :
                    {{ $activePromo->type === 'percent' ? '−' . $activePromo->value . ' %' : '−' . $fmt($activePromo->value) . ' ' . $devise }}
                    @if($activePromo->min_purchase_amount) dès {{ $fmt($activePromo->min_purchase_amount) }} {{ $devise }} d'achat @endif
                </span>
                <span class="pp-promo-cta">Utiliser</span>
            </button>
            @endif

            <div class="pp-buy" id="ppBuy">
                @if($variants->isNotEmpty())
                <div id="ppVariantPicker">
                    <div class="pp-variants-lbl" id="ppVariantsLbl">Choisissez une option</div>
                    <div class="pp-variants" role="group" aria-labelledby="ppVariantsLbl">
                        <button type="button" class="pp-pill" id="ppDefaultPill" data-default="1">{{ Str::limit($product->name, 32) }}</button>
                        @foreach($variants as $v)
                        <button type="button" class="pp-pill {{ ($selectedVariantId ?? null) == $v->id ? 'is-selected' : '' }}"
                                data-id="{{ $v->id }}" data-name="{{ $v->name }}" data-price="{{ $v->effective_price }}"
                                data-stock="{{ $v->stock }}" data-image="{{ $v->image_url }}"
                                aria-pressed="{{ ($selectedVariantId ?? null) == $v->id ? 'true' : 'false' }}"
                                @disabled($v->out_of_stock)>
                            {{ $v->name }}{{ $v->out_of_stock ? ' (épuisé)' : '' }}
                        </button>
                        @endforeach
                    </div>
                    <div class="pp-field-err" id="ppVariantErr" hidden>Choisissez une option pour continuer.</div>
                </div>
                @endif

                @if($onVacation)
                    <p class="pp-vacation" role="status">{{ \App\Support\Storefront::vacationMessage($shop) }}</p>
                    <div class="pp-buy-main">
                        <a href="{{ $writeUrl }}" class="pp-btn pp-btn-primary pp-btn-lg">{!! Ico::svg('message', '', 18) !!} Écrire au vendeur</a>
                        @include('client.orders.partials.fav-button')
                    </div>
                @elseif($stockOut)
                    <p class="pp-note" style="font-size:15px;color:var(--pp-ink-2)">Ce produit n'est plus disponible pour le moment. Ajoutez-le à vos favoris ou écrivez au vendeur pour savoir quand il revient.</p>
                    <div class="pp-buy-main">
                        <a href="{{ $writeUrl }}" class="pp-btn pp-btn-primary pp-btn-lg">{!! Ico::svg('message', '', 18) !!} Écrire au vendeur</a>
                        @include('client.orders.partials.fav-button')
                    </div>
                @else
                    <div class="pp-qty-row">
                        <label for="qty">Quantité @if($product->unit && $product->unit !== 'pièce')<span style="font-weight:500;color:var(--pp-ink-3)">({{ $product->unit }})</span>@endif</label>
                        <div class="pp-stepper">
                            <button type="button" data-qty-step="-1" aria-label="Retirer un">−</button>
                            <input type="number" form="orderForm" name="quantity" id="qty" value="{{ old('quantity', 1) }}" min="1"
                                   max="{{ $variants->isNotEmpty() ? 999 : ($stockVal ?? 999) }}" inputmode="numeric" class="pp-num">
                            <button type="button" data-qty-step="1" aria-label="Ajouter un">+</button>
                        </div>
                    </div>
                    <div class="pp-buy-main">
                        <button type="button" class="pp-btn pp-btn-primary pp-btn-lg" id="ppBuyNow"@if($waMode) style="background:#15803D;box-shadow:none"@endif>
                            {{ $waMode ? 'Commander sur WhatsApp' : 'Commander' }} · <span class="pp-num"><span id="ppBuyTotal">{{ $fmt($nowPrice) }}</span> {{ $devise }}</span>
                        </button>
                        @include('client.orders.partials.fav-button')
                    </div>
                    <div class="pp-buy-alt">
                        @auth
                        <button type="button" class="pp-btn pp-btn-ghost" id="ppAddCart">{!! Ico::svg('cart', '', 17) !!} <span>Ajouter au panier</span></button>
                        @else
                        <a href="{{ $loginUrl }}" class="pp-btn pp-btn-ghost">{!! Ico::svg('cart', '', 17) !!} Ajouter au panier</a>
                        @endauth
                        <a href="{{ $offerUrl }}" class="pp-btn pp-btn-ghost">{!! Ico::svg('message', '', 17) !!} Faire une offre</a>
                    </div>
                    <p class="pp-note">Faites une offre : le vendeur peut accepter ou proposer un autre prix dans le chat. La commande se crée dès que vous êtes d'accord.</p>
                @endif
            </div>

            <ul class="pp-assure">
                <li><span class="pp-assure-ico">{!! Ico::svg('wallet', '', 19) !!}</span><span><strong>Paiement à la livraison.</strong> Vous payez à la réception du colis.</span></li>
                <li><span class="pp-assure-ico">{!! Ico::svg('truck', '', 19) !!}</span><span><strong>Livraison suivie.</strong> Vous suivez votre commande jusqu'à votre porte.</span></li>
                <li><span class="pp-assure-ico">{!! Ico::svg('message', '', 19) !!}</span><span><strong>Un vendeur joignable.</strong> Posez vos questions dans le chat avant d'acheter.</span></li>
            </ul>
        </div>
    </div>

    {{-- Description + boutique --}}
    <section class="pp-section pp-info pp-reveal" aria-label="Détails">
        <div class="pp-card">
            <h2>Description</h2>
            @if($product->description)
                <p class="pp-desc">{{ $product->description }}</p>
            @else
                <p class="pp-desc" style="color:var(--pp-ink-3)">Le vendeur n'a pas encore ajouté de description. Posez-lui vos questions dans le chat.</p>
            @endif
            <dl class="pp-specs">
                @if($product->category)<dt>Catégorie</dt><dd>{{ $product->category }}</dd>@endif
                @if($product->unit)<dt>Vendu à l'unité</dt><dd>{{ ucfirst($product->unit) }}</dd>@endif
                @if($product->preparation_time)<dt>Préparation</dt><dd>{{ $product->preparation_time }} min</dd>@endif
                @if($variants->isNotEmpty())<dt>Options</dt><dd>{{ $variants->pluck('name')->implode(', ') }}</dd>@endif
                <dt>Référence</dt><dd class="pp-num">#{{ $product->id }}</dd>
            </dl>
        </div>

        <div class="pp-card">
            <div class="pp-seller">
                <div class="pp-seller-logo">
                    @if($shop->image)<img src="{{ ImageOptimizer::url($shop->image, 'thumb') ?? asset('storage/' . $shop->image) }}" alt="" loading="lazy">@else{{ $initials }}@endif
                </div>
                <div>
                    <p class="pp-seller-name">{{ $shop->name }}</p>
                    <span class="pp-verified">{!! Ico::svg('shield', '', 14) !!} Boutique vérifiée</span>
                </div>
            </div>
            @if($shop->description)<p class="pp-seller-txt">{{ Str::limit($shop->description, 180) }}</p>@endif
            <div class="pp-seller-meta">
                @if($shopRating)<span>{!! Ico::stars($shopRating, 13) !!} <strong class="pp-num" style="color:var(--pp-ink)">{{ number_format($shopRating, 1, ',', ' ') }}</strong> · {{ $shopReviewCount }} avis</span>@endif
                @if($shop->type)<span>{{ $shop->type }}</span>@endif
                @if($shop->address)<span>{!! Ico::svg('map-pin', '', 14) !!} {{ $shop->address }}</span>@endif
            </div>
            <div class="pp-seller-cta">
                <a href="{{ $shopUrl }}" class="pp-btn pp-btn-ghost">{!! Ico::svg('store', '', 17) !!} Voir la boutique</a>
                <a href="{{ $writeUrl }}" class="pp-btn pp-btn-ghost">{!! Ico::svg('message', '', 17) !!} Écrire au vendeur</a>
            </div>
        </div>
    </section>

    {{-- Avis clients (uniquement de vrais avis laissés après une commande contenant ce produit) --}}
    <section class="pp-section pp-reveal" id="avis" aria-labelledby="ppReviewsTitle">
        <h2 class="pp-h2" id="ppReviewsTitle">Avis clients
            @if($productRating)<small class="pp-num">{{ number_format($productRating, 1, ',', ' ') }} · {{ $productReviewCount }} avis</small>@endif
        </h2>
        @if($productReviews->isNotEmpty())
        <div class="pp-reviews">
            @foreach($productReviews as $review)
            @php
                $parts = preg_split('/\s+/', trim($review->client->name ?? 'Client'));
                $who = $parts[0] . (isset($parts[1]) ? ' ' . mb_strtoupper(mb_substr($parts[1], 0, 1)) . '.' : '');
            @endphp
            <article class="pp-review">
                {!! Ico::stars((float) $review->rating, 14) !!}
                <p>{{ $review->comment }}</p>
                <div class="pp-review-meta">{{ $who }} · {{ $review->created_at->translatedFormat('j F Y') }}</div>
            </article>
            @endforeach
        </div>
        @else
        <div class="pp-empty">Pas encore d'avis écrit sur ce produit. Les avis sont laissés par les clients après la livraison de leur commande.</div>
        @endif
    </section>

    {{-- Autres produits --}}
    @if($relatedProducts->isNotEmpty())
    <section class="pp-section pp-reveal" aria-labelledby="ppRelatedTitle">
        <h2 class="pp-h2" id="ppRelatedTitle">Vous aimerez aussi</h2>
        <div class="pp-related">
            @foreach($relatedProducts->take(4) as $rp)
            @php
                $rpOld = $rp->is_flash_active ? $rp->price : (($rp->original_price && $rp->original_price > $rp->price) ? $rp->original_price : null);
                $rpPct = $rpOld ? (int) round((1 - $rp->current_price / $rpOld) * 100) : 0;
            @endphp
            <a href="{{ route('client.orders.createFromProduct', $rp) }}" class="pp-rcard" data-tilt>
                <div class="pp-rcard-img">
                    @if($rp->image)
                        <img src="{{ ImageOptimizer::url($rp->image, 'thumb') ?? asset('storage/' . $rp->image) }}"
                             srcset="{{ ImageOptimizer::url($rp->image, 'thumb') }} 300w, {{ ImageOptimizer::url($rp->image, 'medium') }} 800w"
                             sizes="(max-width: 640px) 66vw, 280px" alt="{{ $rp->name }}" loading="lazy" width="300" height="300">
                    @else
                        <div class="pp-stage-ph">{!! Ico::categorySvg($rp->category, '', 48) !!}</div>
                    @endif
                    @if($rpPct > 0)<span class="pp-off">−{{ $rpPct }} %</span>@endif
                </div>
                <div class="pp-rcard-body">
                    <div class="pp-rcard-shop">{{ $shop->name }}</div>
                    <div class="pp-rcard-name">{{ $rp->name }}</div>
                    <div class="pp-rcard-price pp-num">{{ $fmt($rp->current_price) }} {{ $devise }}@if($rpOld)<s>{{ $fmt($rpOld) }}</s>@endif</div>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

</div>
</main>

{{-- ═══ TUNNEL DE COMMANDE (3 étapes) ═══ --}}
@unless($stockOut || $onVacation)
<section class="ck" id="checkout" hidden aria-label="Commande">
<div class="pp-wrap">
    <div class="ck-top">
        <button type="button" class="pp-btn pp-btn-ghost" data-ck-close>{!! Ico::svg('arrow-left', '', 16) !!} Retour au produit</button>
        <span class="ck-secure">{!! Ico::svg('lock', '', 16) !!} Commande sécurisée · Paiement à la livraison</span>
    </div>

    <ol class="ck-steps" id="ckSteps" aria-label="Étapes de la commande">
        <li class="ck-steps-fill" aria-hidden="true"></li>
        @foreach(['Adresse', 'Paiement', 'Confirmation'] as $i => $label)
        <li class="ck-step" data-step-ind="{{ $i + 1 }}"><span class="ck-step-dot">{{ $i + 1 }}</span><span class="ck-step-lbl">{{ $label }}</span></li>
        @endforeach
    </ol>

    <div class="ck-grid">
        <form method="POST" action="{{ route('client.orders.storeProduct') }}" id="orderForm" novalidate>
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="variant_id" id="variantIdInput" value="{{ old('variant_id', $selectedVariantId ?? '') }}">
            <input type="hidden" name="payment_method" id="paymentMethodInput" value="cash">
            <input type="hidden" name="promo_code" id="promoCodeInput" value="">
            <input type="hidden" name="delivery_destination" id="delivery_destination" value="{{ $oldDest }}">

            {{-- Étape 1 : adresse --}}
            <div class="ck-panel" data-step="1">
                <h2 tabindex="-1">Où livrer votre commande ?</h2>
                @auth
                <div class="ck-who">{!! Ico::svg('user', '', 18) !!} <span>Commande au nom de <strong>{{ $authUser->name }}</strong></span></div>
                @if($savedAddresses->isNotEmpty())
                <div class="ck-addr-chips" role="group" aria-label="Mes adresses">
                    @foreach($savedAddresses as $addr)
                    <button type="button" class="ck-addr-chip" data-address="{{ $addr->address }}" title="{{ $addr->address }}">
                        {!! Ico::svg($addr->is_default ? 'home' : 'map-pin', '', 14) !!} {{ $addr->label }}
                    </button>
                    @endforeach
                    <a href="{{ route('client.addresses.index') }}" class="ck-addr-manage">Gérer mes adresses</a>
                </div>
                @endif
                @endauth
                <div class="ck-fields">
                    @guest
                    <div class="ck-field">
                        <label for="client_name">Nom complet</label>
                        <input type="text" class="ck-input" name="client_name" id="client_name" value="{{ old('client_name') }}" placeholder="Ex. Mariama Bah" autocomplete="name" required>
                        <div class="ck-hint" id="client_name_hint" aria-live="polite"></div>
                    </div>
                    @endguest
                    <div class="ck-field {{ auth()->check() ? 'is-wide' : '' }}">
                        <label for="client_phone">Téléphone</label>
                        <input type="tel" class="ck-input" name="client_phone" id="client_phone" value="{{ old('client_phone', $authUser->phone ?? '') }}" placeholder="Ex. 622 00 00 00" autocomplete="tel" inputmode="tel" required>
                        <div class="ck-hint" id="client_phone_hint" aria-live="polite"></div>
                    </div>
                    <div class="ck-field is-wide">
                        <label for="ckCommune">{{ $isGuinea ? 'Commune ou ville' : 'Ville' }}</label>
                        @if($isGuinea)
                        <select class="ck-input" id="ckCommune">
                            <option value="">Choisissez…</option>
                            @foreach($communes as $group => $list)
                            <optgroup label="{{ $group }}">
                                @foreach($list as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                            </optgroup>
                            @endforeach
                            <option value="__autre">Autre ville</option>
                        </select>
                        @else
                        <input type="text" class="ck-input" id="ckCommune" placeholder="Ex. Dakar" autocomplete="address-level2">
                        @endif
                        <div class="ck-hint" id="ckCommune_hint" aria-live="polite"></div>
                    </div>
                    <div class="ck-field is-wide">
                        <label for="ckQuartier">Quartier et point de repère</label>
                        <input type="text" class="ck-input" id="ckQuartier" value="{{ $oldDest }}" placeholder="Ex. Kipé, près de la pharmacie" autocomplete="street-address">
                        <div class="ck-hint" id="ckQuartier_hint" aria-live="polite"></div>
                        @auth
                        <label class="ck-save-addr">
                            <input type="checkbox" name="save_address" value="1" {{ $savedAddresses->isEmpty() ? 'checked' : '' }}>
                            Enregistrer dans mes adresses
                        </label>
                        @endauth
                    </div>
                    <div class="ck-field is-wide">
                        <label for="ckSlot">Quand souhaitez-vous être livré ?</label>
                        <select class="ck-input" name="delivery_slot" id="ckSlot">
                            @foreach(\App\Support\DeliverySlot::options() as $key => $label)
                            <option value="{{ $key }}" @selected(old('delivery_slot') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="ck-actions">
                    <button type="button" class="pp-btn pp-btn-ghost" data-ck-close>Retour au produit</button>
                    <button type="button" class="pp-btn pp-btn-primary" data-ck-next="2">Continuer</button>
                </div>
            </div>

            {{-- Étape 2 : paiement --}}
            <div class="ck-panel" data-step="2" hidden>
                <h2 tabindex="-1">Comment voulez-vous payer ?</h2>
                <div class="ck-pay" role="radiogroup" aria-label="Mode de paiement">
                    <label class="ck-pay-opt is-active">
                        <input type="radio" name="payment_method_choice" value="cash" checked>
                        <span class="ck-pay-ico">{!! Ico::svg('wallet', '', 22) !!}</span>
                        <span class="ck-pay-txt"><strong>Paiement à la livraison</strong><span>En espèces, quand vous recevez le colis</span></span>
                        <span class="ck-pay-check">{!! Ico::svg('check', '', 14) !!}</span>
                    </label>
                    <label class="ck-pay-opt">
                        <input type="radio" name="payment_method_choice" value="online">
                        <span class="ck-pay-ico">{!! Ico::svg('smartphone', '', 22) !!}</span>
                        <span class="ck-pay-txt"><strong>Payer maintenant par Mobile Money</strong><span>Paiement sécurisé, commission de {{ rtrim(rtrim(number_format((float) config('chapchappay.platform_fee_percent', 5), 1, ',', ''), '0'), ',') }} % ajoutée</span></span>
                        <span class="ck-pay-check">{!! Ico::svg('check', '', 14) !!}</span>
                    </label>
                </div>

                <div class="ck-sub"><label for="promoInput">Code promo</label></div>
                <div class="ck-promo">
                    <input type="text" class="ck-input" id="promoInput" placeholder="Ex. BIENVENUE" autocomplete="off">
                    <button type="button" class="pp-btn pp-btn-ghost" id="promoApply">Appliquer</button>
                </div>
                <div class="ck-msg" id="promoMsg" aria-live="polite" hidden></div>

                @auth
                @if(($loyaltyBalance ?? 0) > 0)
                <div class="ck-sub">Points fidélité</div>
                <label class="ck-points">
                    <input type="checkbox" id="usePointsChk">
                    Utiliser mes points (solde : <span class="pp-num">{{ $fmt($loyaltyBalance) }}</span>)
                </label>
                <div class="ck-points-row" id="pointsRow" hidden>
                    <input type="number" class="ck-input pp-num" id="pointsInput" name="points_to_use" value="0" min="0" aria-label="Nombre de points à utiliser">
                    <button type="button" class="pp-btn pp-btn-ghost" id="pointsMax">Maximum</button>
                </div>
                @endif
                @endauth

                <div class="ck-actions">
                    <button type="button" class="pp-btn pp-btn-ghost" data-ck-prev="1">Retour</button>
                    <button type="button" class="pp-btn pp-btn-primary" data-ck-next="3">Continuer</button>
                </div>
            </div>

            {{-- Étape 3 : confirmation --}}
            <div class="ck-panel" data-step="3" hidden>
                <h2 tabindex="-1">Vérifiez et confirmez</h2>
                <dl class="ck-recap">
                    <dt>Livraison à</dt>
                    <dd><span id="rcAddress"></span> <button type="button" class="ck-recap-edit" data-ck-prev="1">Modifier</button></dd>
                    <dt>Téléphone</dt><dd class="pp-num" id="rcPhone"></dd>
                    @guest<dt>Nom</dt><dd id="rcName"></dd>@endguest
                    <dt>Paiement</dt>
                    <dd><span id="rcPay"></span> <button type="button" class="ck-recap-edit" data-ck-prev="2">Modifier</button></dd>
                    <dt>Créneau</dt>
                    <dd><span id="rcSlot"></span> <button type="button" class="ck-recap-edit" data-ck-prev="1">Modifier</button></dd>
                </dl>
                <div class="ck-actions">
                    <button type="button" class="pp-btn pp-btn-ghost" data-ck-prev="2">Retour</button>
                    <button type="submit" class="pp-btn pp-btn-primary" id="submitBtn">{!! Ico::svg('check', '', 17) !!} Valider ma commande</button>
                    @if($waMode)<p style="flex-basis:100%;margin:6px 0 0;font-size:13px;color:#45455E">Votre commande est enregistrée sur Shopio, puis vous pourrez l’envoyer à la boutique sur WhatsApp en un clic.</p>@endif
                </div>
            </div>
        </form>

        {{-- Récapitulatif --}}
        <aside class="ck-sum" aria-label="Votre commande">
            <h2>Votre commande</h2>
            <div class="ck-sum-item">
                <div class="ck-sum-img">
                    @if($product->image)
                        <img src="{{ ImageOptimizer::url($product->image, 'thumb') ?? asset('storage/' . $product->image) }}" alt="" loading="lazy" id="ckSumImg">
                    @else
                        {!! Ico::categorySvg($product->category, '', 28) !!}
                    @endif
                </div>
                <div>
                    <div class="ck-sum-name">{{ $product->name }}</div>
                    @if($variants->isNotEmpty())<div class="ck-sum-var" id="summaryVariant"></div>@endif
                    <div class="ck-sum-meta">{{ $shop->name }} · Qté <span class="pp-num" id="summaryQty">1</span></div>
                </div>
                <div class="pp-num" style="font-weight:700"><span id="summaryLine">{{ $fmt($nowPrice) }}</span> {{ $devise }}</div>
            </div>
            <div class="ck-sum-rows">
                <div class="ck-row"><span>Sous-total</span><span class="pp-num"><span id="summarySub">{{ $fmt($nowPrice) }}</span> {{ $devise }}</span></div>
                <div class="ck-row is-discount" id="promoDiscountRow" hidden><span>Code promo</span><span class="pp-num">−<span id="promoDiscountVal">0</span> {{ $devise }}</span></div>
                <div class="ck-row is-discount" id="pointsDiscountRow" hidden><span>Points fidélité</span><span class="pp-num">−<span id="pointsDiscountVal">0</span> {{ $devise }}</span></div>
                <div class="ck-row" id="onlineFeeRow" hidden><span>Commission paiement en ligne</span><span class="pp-num">+<span id="onlineFeeVal">0</span> {{ $devise }}</span></div>
                <div class="ck-row"><span>Livraison</span><span>Non incluse</span></div>
            </div>
            <div class="ck-total">
                <span id="totalLabel">À payer à la livraison</span>
                <span class="pp-num" id="totalWrap"><span id="totalDisplay" aria-live="polite">{{ $fmt($nowPrice) }}</span> {{ $devise }}</span>
            </div>
            <p class="ck-sum-foot">Les frais de livraison ne sont pas compris dans ce total. Boutique vérifiée · vendeur joignable dans le chat.</p>
        </aside>
    </div>
</div>
</section>

{{-- Barre d'achat mobile : visible tant que la boîte d'achat n'est pas à l'écran --}}
<div class="pp-mbar" id="ppMobileBar" aria-hidden="true">
    <div class="pp-mbar-price">
        <small>{{ $product->name }}</small>
        <strong class="pp-num"><span id="ppMobileTotal">{{ $fmt($nowPrice) }}</span> {{ $devise }}</strong>
    </div>
    <button type="button" class="pp-btn pp-btn-primary" id="ppMobileBuy" tabindex="-1">Commander</button>
</div>
@endunless

{{-- Visionneuse photo --}}
<div class="pp-lb" id="ppLightbox" hidden role="dialog" aria-modal="true" aria-label="Photos du produit">
    <button type="button" class="pp-lb-btn pp-lb-close" data-lb-close aria-label="Fermer">&times;</button>
    <button type="button" class="pp-lb-btn pp-lb-prev" data-lb-step="-1" aria-label="Photo précédente">&#8249;</button>
    <img id="ppLbImg" src="" alt="{{ $product->name }}">
    <button type="button" class="pp-lb-btn pp-lb-next" data-lb-step="1" aria-label="Photo suivante">&#8250;</button>
    <div class="pp-lb-count pp-num" id="ppLbCount"></div>
</div>

</div>
@endsection

@if(\App\Support\MetaPixel::activeFor($shop))
@push('scripts')
{{-- Pixel Meta (Facebook) du vendeur : fiche produit vue --}}
@include('partials.meta-pixel', ['pixelId' => $shop->meta_pixel_id, 'events' => [['ViewContent', [
    'content_ids' => [(string) $product->id], 'content_name' => $product->name, 'content_type' => 'product',
    'content_category' => (string) $product->category, 'value' => (float) $nowPrice, 'currency' => $devise,
]]]])
@endpush
@endif

@push('scripts')
<script>
(function () {
'use strict';

const $ = id => document.getElementById(id);
const fmt = n => Math.round(n).toLocaleString('fr-FR');
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const HAS_VARIANTS = {{ $variants->isNotEmpty() ? 'true' : 'false' }};
const ONLINE_FEE_PERCENT = {{ (float) config('chapchappay.platform_fee_percent', 5) }};
const BASE_PRICE = {{ $nowPrice }};
const BASE_STOCK = {{ $variants->isNotEmpty() ? 999 : ($stockVal ?? 999) }};
const LOYALTY_BALANCE = {{ (int) ($loyaltyBalance ?? 0) }};
const MAX_REDEEM_RATIO = 0.5;
const PROMO_CHECK_URL = @json(route('client.orders.promo.check'));
const CSRF = @json(csrf_token());
const SHOP_ID = {{ (int) $shop->id }};
const ACTIVE_PROMO_CODE = @json($activePromo->code ?? null);
const CART_ADD_URL = @json(auth()->check() ? route('client.cart.add', $product) : null);
const PHOTOS = @json($photos);
const HAS_ERRORS = {{ $errors->any() ? 'true' : 'false' }};

let PRICE = BASE_PRICE, STOCK = BASE_STOCK, promoDiscount = 0, photoIdx = 0, checkoutTracked = false;

/* Pixel Meta du vendeur (s'il en a un) : événements produit, prix et devise toujours inclus */
const PIXEL_PRODUCT = { content_ids: [@json((string) $product->id)], content_type: 'product', currency: @json($devise) };
function pixelTrack(name, extra) {
    if (typeof window.shopioPixelTrack !== 'function') return;
    window.shopioPixelTrack(name, Object.assign({}, PIXEL_PRODUCT, { value: Math.round(PRICE * qtyVal()) }, extra || {}));
}

function bump(node) {
    if (!node || reduceMotion) return;
    node.classList.remove('is-bump'); void node.offsetWidth; node.classList.add('is-bump');
}

/* ══ Compte à rebours vente flash ══ */
(function () {
    const box = $('ppCountdown');
    if (!box) return;
    const end = parseInt(box.dataset.ends, 10) * 1000, txt = $('ppCountdownTxt');
    const pad = n => String(n).padStart(2, '0');
    function tick() {
        const d = end - Date.now();
        if (d <= 0) { txt.textContent = 'terminée'; clearInterval(t); return; }
        const days = Math.floor(d / 864e5), h = Math.floor(d % 864e5 / 36e5), m = Math.floor(d % 36e5 / 6e4), s = Math.floor(d % 6e4 / 1e3);
        txt.textContent = (days ? days + ' j ' : '') + pad(h) + ':' + pad(m) + ':' + pad(s);
    }
    tick(); const t = setInterval(tick, 1000);
})();

/* ══ Galerie + visionneuse ══ */
function showPhoto(i, src) {
    const img = $('ppMainImg');
    if (!img) return;
    if (!src) {
        if (!PHOTOS.length) return;
        photoIdx = (i + PHOTOS.length) % PHOTOS.length;
        src = PHOTOS[photoIdx];
        document.querySelectorAll('.pp-thumb').forEach((t, k) => t.classList.toggle('is-active', k === photoIdx));
        const c = $('ppPhotoCount'); if (c) c.textContent = (photoIdx + 1) + ' / ' + PHOTOS.length;
    }
    img.style.opacity = '0';
    const pre = new Image();
    pre.onload = pre.onerror = () => { img.src = src; img.style.opacity = '1'; };
    pre.src = src;
}
document.querySelectorAll('[data-photo]').forEach(b => b.addEventListener('click', () => showPhoto(+b.dataset.photo)));
document.querySelectorAll('[data-photo-step]').forEach(b => b.addEventListener('click', e => { e.stopPropagation(); showPhoto(photoIdx + +b.dataset.photoStep); }));

const stage = $('ppStage');
if (stage) {
    let sx = 0, sy = 0;
    stage.addEventListener('touchstart', e => { sx = e.touches[0].clientX; sy = e.touches[0].clientY; }, { passive: true });
    stage.addEventListener('touchend', e => {
        const dx = e.changedTouches[0].clientX - sx, dy = e.changedTouches[0].clientY - sy;
        if (PHOTOS.length > 1 && Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) showPhoto(photoIdx + (dx < 0 ? 1 : -1));
    }, { passive: true });
    const open = () => openLb();
    stage.addEventListener('click', open);
    stage.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); } });
}

let lbIdx = 0, lbReturn = null;
function lbRender() {
    $('ppLbImg').src = PHOTOS[lbIdx];
    $('ppLbCount').textContent = PHOTOS.length > 1 ? (lbIdx + 1) + ' / ' + PHOTOS.length : '';
    document.querySelectorAll('[data-lb-step]').forEach(b => b.hidden = PHOTOS.length < 2);
}
function openLb() {
    if (!PHOTOS.length) return;
    lbIdx = photoIdx; lbReturn = document.activeElement; lbRender();
    $('ppLightbox').hidden = false;
    document.querySelector('[data-lb-close]').focus();
}
function closeLb() { $('ppLightbox').hidden = true; if (lbReturn) lbReturn.focus(); }
$('ppLightbox').addEventListener('click', e => { if (e.target.id === 'ppLightbox') closeLb(); });
document.querySelector('[data-lb-close]').addEventListener('click', closeLb);
document.querySelectorAll('[data-lb-step]').forEach(b => b.addEventListener('click', () => { lbIdx = (lbIdx + +b.dataset.lbStep + PHOTOS.length) % PHOTOS.length; lbRender(); }));
document.addEventListener('keydown', e => {
    if ($('ppLightbox').hidden) return;
    if (e.key === 'Escape') closeLb();
    if (e.key === 'ArrowLeft') { lbIdx = (lbIdx - 1 + PHOTOS.length) % PHOTOS.length; lbRender(); }
    if (e.key === 'ArrowRight') { lbIdx = (lbIdx + 1) % PHOTOS.length; lbRender(); }
});

/* ══ Variantes ══ */
const pills = document.querySelectorAll('.pp-pill');
function selectPill(btn) {
    if (btn.disabled) return;
    pills.forEach(p => { p.classList.toggle('is-selected', p === btn); p.setAttribute('aria-pressed', p === btn ? 'true' : 'false'); });
    const isDefault = !!btn.dataset.default;
    $('variantIdInput').value = isDefault ? '' : btn.dataset.id;
    PRICE = isDefault ? BASE_PRICE : parseFloat(btn.dataset.price);
    STOCK = isDefault ? BASE_STOCK : parseInt(btn.dataset.stock, 10);
    const qty = $('qty');
    if (qty) { qty.max = STOCK; if (+qty.value > STOCK) qty.value = Math.max(1, STOCK); }
    $('ppPrice').textContent = fmt(PRICE);
    const sv = $('summaryVariant'); if (sv) sv.textContent = isDefault ? 'Sans préférence d\'option' : btn.dataset.name;
    showPhoto(0, isDefault ? PHOTOS[0] : (btn.dataset.image || PHOTOS[0]));
    const err = $('ppVariantErr'); if (err) err.hidden = true;
    updateTotal();
}
pills.forEach(p => p.addEventListener('click', () => selectPill(p)));
function variantChosen() {
    return !HAS_VARIANTS || !!$('variantIdInput').value || !!document.querySelector('.pp-pill.is-selected[data-default]');
}
function requireVariant() {
    if (variantChosen()) return true;
    const err = $('ppVariantErr'); if (err) err.hidden = false;
    $('ppVariantPicker')?.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
    return false;
}
const preselected = document.querySelector('.pp-pill.is-selected');
if (preselected) selectPill(preselected);

/* ══ Quantité ══ */
document.querySelectorAll('[data-qty-step]').forEach(b => b.addEventListener('click', () => {
    const q = $('qty');
    const v = Math.max(1, Math.min(STOCK, (parseInt(q.value, 10) || 1) + +b.dataset.qtyStep));
    if (String(v) !== q.value) bump(q);
    q.value = v; updateTotal();
}));
$('qty')?.addEventListener('input', updateTotal);

/* ══ Totaux ══ */
function qtyVal() { return Math.max(1, parseInt($('qty')?.value, 10) || 1); }
function maxPoints(amount) { return Math.max(0, Math.min(LOYALTY_BALANCE, Math.floor(amount * MAX_REDEEM_RATIO))); }
function updateTotal() {
    const qty = qtyVal(), sub = Math.round(PRICE * qty);
    const set = (id, v) => { const n = $(id); if (n) n.textContent = v; };
    set('ppBuyTotal', fmt(sub)); set('ppMobileTotal', fmt(sub));
    set('summaryQty', qty); set('summaryLine', fmt(sub)); set('summarySub', fmt(sub));

    $('promoDiscountRow').hidden = !(promoDiscount > 0);
    set('promoDiscountVal', fmt(promoDiscount));
    const afterPromo = Math.max(0, sub - promoDiscount);

    let points = 0;
    const chk = $('usePointsChk');
    if (chk && chk.checked) {
        const inp = $('pointsInput');
        points = Math.max(0, Math.min(parseInt(inp.value, 10) || 0, maxPoints(afterPromo)));
        inp.value = points;
    }
    if ($('pointsDiscountRow')) { $('pointsDiscountRow').hidden = points <= 0; set('pointsDiscountVal', fmt(points)); }

    let total = Math.max(0, afterPromo - points);
    // Paiement en ligne : commission Shopio ajoutée en plus, à la charge du client
    const online = $('paymentMethodInput').value === 'online';
    const fee = online && total > 0 ? Math.round(total * ONLINE_FEE_PERCENT / 100) : 0;
    total += fee;
    $('onlineFeeRow').hidden = fee <= 0; set('onlineFeeVal', fmt(fee));
    set('totalLabel', online ? 'À payer maintenant' : 'À payer à la livraison');

    const el = $('totalDisplay');
    if (el && el.textContent !== fmt(total)) { el.textContent = fmt(total); bump($('totalWrap')); }
}

/* ══ Favori ══ */
$('ppFav')?.addEventListener('click', function () {
    const btn = this;
    if (btn.dataset.busy) return;
    btn.dataset.busy = '1';
    fetch(btn.dataset.url, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(d => {
            btn.classList.toggle('is-fav', !!d.favorited);
            btn.setAttribute('aria-pressed', d.favorited ? 'true' : 'false');
            btn.setAttribute('aria-label', d.favorited ? 'Retirer des favoris' : 'Ajouter aux favoris');
            btn.classList.remove('is-pop');
            if (d.favorited && !reduceMotion) { void btn.offsetWidth; btn.classList.add('is-pop'); }
        })
        .catch(() => {})
        .finally(() => { delete btn.dataset.busy; });
});

/* ══ Panier ══ */
$('ppAddCart')?.addEventListener('click', function () {
    const btn = this, label = btn.querySelector('span');
    if (!requireVariant() || btn.disabled) return;
    btn.disabled = true;
    fetch(CART_ADD_URL, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ quantity: Math.min(qtyVal(), 99), variant_id: $('variantIdInput').value || null })
    })
        .then(r => r.json().then(d => ({ ok: r.ok, d })))
        .then(({ ok, d }) => {
            if (!ok) throw new Error(d.message || 'Impossible d\'ajouter au panier.');
            btn.classList.add('is-done'); label.textContent = 'Ajouté au panier';
            pixelTrack('AddToCart', { quantity: Math.min(qtyVal(), 99) });
            const badge = $('ppCartBadge');
            if (badge) { badge.hidden = false; badge.textContent = d.count; badge.classList.remove('is-pop'); void badge.offsetWidth; badge.classList.add('is-pop'); }
            setTimeout(() => { btn.classList.remove('is-done'); label.textContent = 'Ajouter au panier'; }, 2200);
        })
        .catch(err => { label.textContent = err.message; setTimeout(() => { label.textContent = 'Ajouter au panier'; }, 2600); })
        .finally(() => { btn.disabled = false; });
});

/* ══ Validation (mêmes règles que le serveur : RealisticGuineaPhone / RealisticFullName) ══ */
function validateGuineaPhone(value) {
    const digits = (value || '').replace(/\D+/g, '').replace(/^224(\d{9})$/, '$1');
    if (!/^6\d{8}$/.test(digits)) return 'Numéro guinéen invalide (9 chiffres commençant par 6). Ex. 622 00 00 00.';
    if (new Set(digits.split('')).size <= 2) return 'Ce numéro ne semble pas valide.';
    const diffs = [...digits].slice(1).map((d, i) => +d - +digits[i]);
    if (new Set(diffs).size === 1 && Math.abs(diffs[0]) === 1) return 'Ce numéro ne semble pas valide.';
    return null;
}
function validateFullName(value) {
    const name = (value || '').trim(), words = name.split(/\s+/).filter(Boolean);
    if (words.length < 2) return 'Indiquez votre prénom et votre nom.';
    if (words.some(w => w.length < 2)) return 'Prénom et nom complets requis.';
    if (!/[aeiouyàâäéèêëîïôöùûü]/i.test(name)) return 'Ce nom ne semble pas valide.';
    if (/(.)\1{3,}/i.test(name)) return 'Ce nom ne semble pas valide.';
    return null;
}
function mark(input, error, okText) {
    const hint = $(input.id + '_hint');
    input.classList.toggle('is-invalid', !!error);
    input.classList.toggle('is-valid', !error && !!okText);
    input.setAttribute('aria-invalid', error ? 'true' : 'false');
    if (hint) { hint.textContent = error || okText || ''; hint.className = 'ck-hint' + (error ? ' is-error' : (okText ? ' is-ok' : '')); }
    return !error;
}
const IS_GUINEA = {{ $isGuinea ? 'true' : 'false' }};
$('client_name')?.addEventListener('input', e => e.target.value.trim() ? mark(e.target, validateFullName(e.target.value), 'Parfait') : mark(e.target, null));
$('client_phone')?.addEventListener('input', e => e.target.value.trim() && IS_GUINEA ? mark(e.target, validateGuineaPhone(e.target.value), 'Numéro valide') : mark(e.target, null));

/* Adresse enregistrée : un clic remplit le quartier (l'adresse complète) et vide la commune */
document.querySelectorAll('.ck-addr-chip').forEach(chip => chip.addEventListener('click', () => {
    $('ckCommune').value = '';
    $('ckQuartier').value = chip.dataset.address;
    mark($('ckCommune'), null); mark($('ckQuartier'), null);
    document.querySelectorAll('.ck-addr-chip').forEach(c => c.classList.toggle('is-active', c === chip));
    const save = document.querySelector('input[name="save_address"]');
    if (save) save.checked = false; // déjà enregistrée
}));

/* Adresse envoyée au serveur = commune + quartier (sans répéter la commune si elle y figure déjà) */
function composeAddress() {
    const c = $('ckCommune').value.trim(), q = $('ckQuartier').value.trim();
    const commune = c === '__autre' ? '' : c;
    const addr = commune && !q.toLowerCase().includes(commune.toLowerCase()) ? (q ? commune + ', ' + q : commune) : q;
    $('delivery_destination').value = addr;
    return addr;
}

function validateStep1() {
    let first = null;
    const check = (input, err) => { if (!mark(input, err) && !first) first = input; };
    const name = $('client_name');
    if (name) check(name, name.value.trim() ? validateFullName(name.value) : 'Indiquez votre nom complet.');
    const phone = $('client_phone');
    check(phone, !phone.value.trim() ? 'Indiquez un numéro pour que le livreur puisse vous joindre.' : (IS_GUINEA ? validateGuineaPhone(phone.value) : null));
    const commune = $('ckCommune'), quartier = $('ckQuartier');
    check(commune, commune.value || quartier.value.trim().length >= 2 ? null : 'Choisissez votre commune ou votre ville.');
    check(quartier, quartier.value.trim().length >= 2 ? null : 'Précisez le quartier et un point de repère.');
    if (first) { first.focus(); return false; }
    composeAddress();
    return true;
}

/* ══ Paiement ══ */
document.querySelectorAll('input[name="payment_method_choice"]').forEach(r => r.addEventListener('change', () => {
    $('paymentMethodInput').value = r.value;
    document.querySelectorAll('.ck-pay-opt').forEach(o => o.classList.toggle('is-active', o.contains(r)));
    const ico = r.closest('.ck-pay-opt').querySelector('.ck-pay-ico');
    if (!reduceMotion) { ico.classList.remove('is-spin'); void ico.offsetWidth; ico.classList.add('is-spin'); }
    updateTotal();
}));

function showPromoMsg(text, ok) { const m = $('promoMsg'); m.textContent = text; m.className = 'ck-msg ' + (ok ? 'is-ok' : 'is-error'); m.hidden = false; }
function applyPromoCode() {
    const code = $('promoInput').value.trim().toUpperCase();
    if (!code) return;
    $('promoInput').value = code;
    fetch(PROMO_CHECK_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ code, shop_id: SHOP_ID, subtotal: Math.round(PRICE * qtyVal()) })
    })
        .then(r => r.json())
        .then(d => {
            promoDiscount = d.valid ? d.discount : 0;
            $('promoCodeInput').value = d.valid ? code : '';
            showPromoMsg(d.valid ? 'Code appliqué : −' + fmt(d.discount) + ' {{ $devise }}' : (d.message || 'Code invalide.'), !!d.valid);
            updateTotal();
        })
        .catch(() => showPromoMsg('Vérification impossible. Réessayez.', false));
}
$('promoApply')?.addEventListener('click', applyPromoCode);
$('promoInput')?.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); applyPromoCode(); } });
$('usePointsChk')?.addEventListener('change', e => { $('pointsRow').hidden = !e.target.checked; if (!e.target.checked) $('pointsInput').value = 0; updateTotal(); });
$('pointsInput')?.addEventListener('input', updateTotal);
$('pointsMax')?.addEventListener('click', () => { $('pointsInput').value = maxPoints(Math.max(0, Math.round(PRICE * qtyVal()) - promoDiscount)); updateTotal(); });

/* ══ Tunnel : la fiche et le tunnel sont deux « pages » de la même URL (#commande) ══ */
const productView = $('productView'), checkout = $('checkout');
let step = 1;

function goStep(n) {
    if (!checkout) return;
    const dir = n >= step ? 'fwd' : 'back';
    step = n;
    checkout.querySelectorAll('.ck-panel').forEach(p => {
        const on = +p.dataset.step === n;
        p.hidden = !on;
        p.classList.remove('is-in-fwd', 'is-in-back');
        if (on && !reduceMotion) { void p.offsetWidth; p.classList.add('is-in-' + dir); }
    });
    checkout.querySelectorAll('.ck-step').forEach(s => {
        const i = +s.dataset.stepInd;
        s.classList.toggle('is-current', i === n);
        s.classList.toggle('is-done', i < n);
        if (i === n) s.setAttribute('aria-current', 'step'); else s.removeAttribute('aria-current');
    });
    checkout.querySelector('.ck-steps-fill').style.setProperty('--p', n / 3);
    if (n === 3) fillRecap();
    window.scrollTo({ top: 0, behavior: 'auto' });
    checkout.querySelector('.ck-panel[data-step="' + n + '"] h2').focus({ preventScroll: true });
}

function fillRecap() {
    $('rcAddress').textContent = composeAddress();
    $('rcSlot').textContent = $('ckSlot').selectedOptions[0]?.textContent.trim() || 'Dès que possible';
    $('rcPhone').textContent = $('client_phone').value.trim();
    if ($('rcName')) $('rcName').textContent = $('client_name').value.trim();
    $('rcPay').textContent = $('paymentMethodInput').value === 'online' ? 'Mobile Money, maintenant' : 'À la livraison, en espèces';
}

function openCheckout(push) {
    if (!checkout || !requireVariant()) return;
    productView.hidden = true;
    $('ppMobileBar')?.classList.remove('is-visible');
    checkout.hidden = false;
    if (push) history.pushState({ ck: 1 }, '', '#commande');
    step = 1; goStep(1);
    if (!checkoutTracked) { checkoutTracked = true; pixelTrack('InitiateCheckout', { num_items: qtyVal() }); }
}
function closeCheckout(fromPop) {
    if (!checkout || checkout.hidden) return;
    checkout.hidden = true;
    productView.hidden = false;
    if (!fromPop && location.hash === '#commande') history.back();
    $('ppBuyNow')?.focus({ preventScroll: true });
    $('ppBuy')?.scrollIntoView({ block: 'center' });
}

$('ppBuyNow')?.addEventListener('click', () => openCheckout(true));
$('ppMobileBuy')?.addEventListener('click', () => openCheckout(true));
document.querySelectorAll('[data-ck-close]').forEach(b => b.addEventListener('click', () => closeCheckout(false)));
document.querySelectorAll('[data-ck-next]').forEach(b => b.addEventListener('click', () => {
    if (step === 1 && !validateStep1()) return;
    goStep(+b.dataset.ckNext);
}));
document.querySelectorAll('[data-ck-prev]').forEach(b => b.addEventListener('click', () => goStep(+b.dataset.ckPrev)));
window.addEventListener('popstate', () => { if (location.hash === '#commande') openCheckout(false); else closeCheckout(true); });

$('ppPromoBanner')?.addEventListener('click', () => {
    if (!ACTIVE_PROMO_CODE || !checkout) return;
    openCheckout(true); goStep(2);
    $('promoInput').value = ACTIVE_PROMO_CODE; applyPromoCode();
});

/* Envoi : dernières vérifications, puis le serveur prend le relais (il revalide tout) */
$('orderForm')?.addEventListener('submit', e => {
    if (!variantChosen()) { e.preventDefault(); closeCheckout(false); requireVariant(); return; }
    if (!validateStep1()) { e.preventDefault(); goStep(1); validateStep1(); return; }
    const btn = $('submitBtn');
    if (btn.disabled) { e.preventDefault(); return; }
    btn.disabled = true;
    btn.textContent = 'Envoi de la commande…';
});

/* Retour du serveur avec une erreur, ou lien direct vers #commande : on rouvre le tunnel */
if (checkout && (HAS_ERRORS || location.hash === '#commande')) {
    if (location.hash !== '#commande') history.replaceState({ ck: 1 }, '', '#commande');
    openCheckout(false);
    if (HAS_ERRORS) validateStep1();
}

/* ══ Barre d'achat mobile ══ */
const bar = $('ppMobileBar'), buy = $('ppBuy');
if (bar && buy && 'IntersectionObserver' in window) {
    new IntersectionObserver(([en]) => {
        bar.classList.toggle('is-visible', !en.isIntersecting && en.boundingClientRect.top < 0 && !productView.hidden);
    }, { threshold: 0 }).observe(buy);
}

/* ══ Cartes produits : inclinaison ±8° qui suit la souris, avec reflet ══ */
if (!reduceMotion && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    document.querySelectorAll('[data-tilt]').forEach(card => {
        card.addEventListener('pointermove', e => {
            const r = card.getBoundingClientRect(), x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
            card.style.setProperty('--ry', ((x - .5) * 16).toFixed(2) + 'deg');
            card.style.setProperty('--rx', ((.5 - y) * 16).toFixed(2) + 'deg');
            card.style.setProperty('--mx', (x * 100).toFixed(1) + '%');
            card.style.setProperty('--my', (y * 100).toFixed(1) + '%');
        });
        card.addEventListener('pointerleave', () => { card.style.setProperty('--rx', '0deg'); card.style.setProperty('--ry', '0deg'); });
    });
}

updateTotal();
})();
</script>
@endpush
