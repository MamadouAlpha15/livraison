{{--
  orders/nav.blade.php
  Carte de navigation du livreur.
  Phase 1 (avant en_livraison) : trajet livreur → boutique (ramassage)
  Phase 2 (en_livraison)       : trajet livreur → client (livraison)
--}}
@php
    use App\Support\IconLibrary as Ico;
    use App\Models\Payment;

    $orderNum     = '#' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
    $hasVendor    = $order->vendor_lat && $order->vendor_lng;
    $destination  = $order->delivery_destination ?? ($order->client?->address ?? 'Destination inconnue');
    $shopName     = $order->shop?->name ?? 'Boutique';
    $shopAddress  = $order->shop?->address ?? '';
    $shopPhone    = $order->shop?->phone;
    $clientName   = $order->client?->name ?? $order->client_name ?? 'Client';
    $clientPhone  = $order->client_phone ?: $order->client?->phone;
    $shopWa       = preg_replace('/\D/', '', $shopPhone ?? '');
    $clientWa     = preg_replace('/\D/', '', $clientPhone ?? '');
    $initialPhase = $phase; // passé depuis le contrôleur
    $devise       = $order->shop?->currency ?? 'GNF';
    $isPaid       = $order->payment
        && $order->payment->method !== Payment::METHOD_CASH
        && $order->payment->status === Payment::STATUS_PAYE;
    $fmt          = fn ($n) => number_format($n ?? 0, 0, ',', "\u{00A0}") . "\u{00A0}" . $devise;
    $waIco = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#0B1E3D">
<title>Navigation · Commande {{ $orderNum }}</title>
<link rel="preconnect" href="https://api.mapbox.com" crossorigin>
<link rel="dns-prefetch" href="https://api.mapbox.com">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css"/>
<link href="{{ \App\Support\Assets::v('fonts/dashboard-fonts.css') }}" rel="stylesheet">
<style>
/* Navigation livreur — carte plein écran, barre bleu nuit, panneau blanc lisible en plein soleil */
:root {
    --blue: #1F7CF6; --blue-2: #1668D8; --blue-3: #1260C9; --blue-lt: #EAF2FF;
    --navy: #0B1E3D; --navy-2: #132B52; --amber: #F59E0B; --amber-lt: #FFF7E6;
    --green: #059669; --green-2: #047857; --green-lt: #ECFDF5;
    --ink: #16162A; --ink-2: #45455E; --ink-3: #62627C; --line: #E3E8F2; --bg: #F6F9FF;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; }
body {
    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
    background: var(--navy); color: var(--ink);
    display: flex; flex-direction: column; overflow: hidden;
}
.num { font-variant-numeric: tabular-nums; }
button { font-family: inherit; }
:focus-visible { outline: 3px solid rgba(31,124,246,.55); outline-offset: 2px; }

/* ── Barre du haut ── */
.nav-bar {
    flex-shrink: 0; position: relative; z-index: 1000;
    display: flex; align-items: center; gap: 10px;
    padding: max(10px, env(safe-area-inset-top, 0px)) max(12px, env(safe-area-inset-right, 0px)) 10px max(12px, env(safe-area-inset-left, 0px));
    background: radial-gradient(120% 220% at 100% 0%, rgba(31,124,246,.4), transparent 60%), linear-gradient(135deg, var(--navy), var(--navy-2));
    color: #fff;
}
.nav-back { width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,.1); color: #fff; text-decoration: none; transition: background-color .18s; }
.nav-back:hover { background: rgba(255,255,255,.2); }
.nav-order { flex: 1; min-width: 0; }
.nav-order-lbl { font-size: 11.5px; font-weight: 600; color: #C9D6EA; }
.nav-order-num { font-size: 15px; font-weight: 800; }
.phase-badge { display: inline-flex; align-items: center; gap: 7px; padding: 7px 13px; border-radius: 999px; font-size: 12.5px; font-weight: 800; white-space: nowrap; }
.phase-1 { background: var(--amber-lt); color: #92400E; }
.phase-2 { background: var(--blue-lt); color: var(--blue-3); }
.phase-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; background: currentColor; animation: pulse 1.6s ease-in-out infinite; }
@keyframes pulse { 0%, 100% { transform: scale(1); opacity: 1 } 50% { transform: scale(1.5); opacity: .55 } }

/* ── Carte ── */
.map-wrap { flex: 1; position: relative; min-height: 200px; }
#navMap { position: absolute; inset: 0; background: #E8EEF7; }
.no-vendor-banner {
    position: absolute; top: 12px; left: 12px; right: 60px; z-index: 1100; width: fit-content;
    display: none; align-items: center; gap: 8px;
    padding: 9px 14px; border-radius: 14px; background: #fff; color: #92400E;
    font-size: 12.5px; font-weight: 700; box-shadow: 0 8px 24px rgba(11,30,61,.25);
}
.no-vendor-banner svg { color: var(--amber); flex-shrink: 0; }

/* ── Panneau du bas ── */
.info-panel {
    flex-shrink: 0; position: relative; z-index: 1000;
    background: #fff; border-radius: 24px 24px 0 0; margin-top: -20px;
    box-shadow: 0 -12px 32px rgba(11,30,61,.18);
    padding: 10px max(16px, env(safe-area-inset-right, 0px)) max(16px, env(safe-area-inset-bottom, 0px)) max(16px, env(safe-area-inset-left, 0px));
    display: grid; gap: 12px; max-height: 62vh; overflow-y: auto;
}
.panel-grip { width: 40px; height: 4px; border-radius: 999px; background: var(--line); margin: 0 auto 2px; }
.steps { display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 8px; }
.step { display: flex; align-items: center; gap: 7px; font-size: 12.5px; font-weight: 700; color: var(--ink-3); }
.step:last-child { justify-content: flex-end; }
.step-n { width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11.5px; font-weight: 800; background: #F1F3F8; color: var(--ink-3); }
.step.is-on { color: var(--ink); }
.step.is-on .step-n { background: var(--blue-2); color: #fff; }
.step.is-done .step-n { background: var(--green-lt); color: var(--green); }
.step-bar { width: 40px; height: 2px; border-radius: 2px; background: var(--line); }

.dest { display: flex; align-items: center; gap: 12px; }
.dest-ico { width: 44px; height: 44px; border-radius: 14px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
.ico-vendor { background: var(--amber-lt); color: #B45309; }
.ico-client { background: var(--blue-lt); color: var(--blue-2); }
.dest-txt { min-width: 0; flex: 1; }
.dest-lbl { font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: var(--ink-3); }
.dest-val { font-size: 16px; font-weight: 800; margin-top: 2px; overflow-wrap: anywhere; }
.dest-sub { font-size: 13px; color: var(--ink-2); margin-top: 2px; overflow-wrap: anywhere; }
.vendor-wait { display: inline-flex; align-items: center; gap: 6px; margin-top: 6px; padding: 3px 10px; border-radius: 999px; background: var(--amber-lt); color: #92400E; font-size: 12px; font-weight: 700; }
.vendor-wait span { width: 6px; height: 6px; border-radius: 50%; background: var(--amber); animation: pulse 1.2s ease-in-out infinite; }

.contact { display: flex; gap: 8px; }
.contact a { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 42px; border-radius: 999px; font-size: 13.5px; font-weight: 700; text-decoration: none; transition: background-color .18s, color .18s; }
.c-call { background: var(--blue-lt); color: var(--blue-3); }
.c-call:hover { background: #CFE1FF; }
.c-wa { background: #E8F8EE; color: #128C4A; }
.c-wa:hover { background: #25D366; color: #fff; }

.facts { display: flex; flex-wrap: wrap; gap: 8px; }
.fact { display: inline-flex; align-items: center; gap: 6px; padding: 7px 11px; border-radius: 12px; background: var(--bg); font-size: 12.5px; font-weight: 600; color: var(--ink-2); }
.fact strong { color: var(--ink); }
.fact.cash { background: var(--amber-lt); color: #92400E; }
.fact.cash strong { color: #78350F; }
.fact.paid { background: var(--green-lt); color: #065F46; }

.btn-phase, .btn-gps { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; border-radius: 999px; border: none; cursor: pointer; font-weight: 800; transition: background-color .18s, transform .18s; }
.btn-phase { min-height: 54px; font-size: 16px; color: #fff; }
.btn-phase:active, .btn-gps:active { transform: scale(.98); }
.btn-phase:disabled { opacity: .55; cursor: not-allowed; }
.btn-start { background: var(--blue-2); }
.btn-start:hover:not(:disabled) { background: var(--blue-3); }
.btn-done { background: var(--green); }
.btn-done:hover:not(:disabled) { background: var(--green-2); }
.btn-gps { min-height: 46px; font-size: 14px; background: rgba(22,22,42,.06); color: var(--ink); }
.btn-gps:hover { background: rgba(22,22,42,.1); }
.btn-dev { width: 100%; min-height: 40px; border-radius: 999px; border: 1.5px dashed #F2C26B; background: var(--amber-lt); color: #92400E; font-size: 12.5px; font-weight: 700; cursor: pointer; }

/* ── Chargement GPS ── */
.gps-loading { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 2000; background: #fff; border-radius: 20px; padding: 24px 28px; text-align: center; box-shadow: 0 20px 50px rgba(11,30,61,.3); }
.gps-loading-spinner { width: 34px; height: 34px; margin: 0 auto 12px; border-radius: 50%; border: 3px solid var(--blue-lt); border-top-color: var(--blue-2); animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.gps-loading p { font-size: 13.5px; font-weight: 600; color: var(--ink-2); }

/* ── Fenêtres (colis récupéré, preuve de livraison) ── */
.pickup-overlay { display: none; position: fixed; inset: 0; z-index: 8000; background: rgba(11,30,61,.55); backdrop-filter: blur(3px); align-items: flex-end; justify-content: center; }
.pickup-overlay.open { display: flex; }
.pickup-modal {
    width: 100%; max-width: 480px; max-height: 92vh; overflow-y: auto;
    background: #fff; border-radius: 26px 26px 0 0; text-align: center;
    padding: 26px 22px max(24px, env(safe-area-inset-bottom, 0px));
    animation: slideUp .25s ease;
}
@keyframes slideUp { from { transform: translateY(60px); opacity: 0 } to { transform: translateY(0); opacity: 1 } }
.pickup-modal-ico { width: 64px; height: 64px; border-radius: 50%; margin: 0 auto 14px; display: flex; align-items: center; justify-content: center; background: var(--blue-lt); color: var(--blue-2); }
.pickup-modal-ico.green { background: var(--green-lt); color: var(--green); }
.pickup-modal-title { font-size: 19px; font-weight: 800; margin-bottom: 6px; }
.pickup-modal-desc { font-size: 14px; color: var(--ink-2); line-height: 1.55; margin-bottom: 18px; }
.pickup-modal-dest { display: flex; align-items: center; gap: 12px; padding: 12px 14px; margin-bottom: 18px; border-radius: 16px; background: var(--bg); text-align: left; }
.pickup-modal-dest-ico { width: 38px; height: 38px; border-radius: 12px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: var(--blue-lt); color: var(--blue-2); }
.pickup-modal-dest-name { font-size: 13.5px; font-weight: 800; }
.pickup-modal-dest-addr { font-size: 12.5px; color: var(--ink-2); margin-top: 2px; overflow-wrap: anywhere; }
.pickup-modal-slot { font-size: 12.5px; font-weight: 700; color: var(--blue-3); margin-top: 4px; }
.code-lbl { display: block; text-align: left; font-size: 13px; font-weight: 700; margin: 4px 0 6px; }
.code-input { width: 100%; padding: 12px; border-radius: 16px; border: 1.5px solid var(--line); background: #fff; color: var(--ink); font-size: 26px; font-weight: 800; letter-spacing: .5em; text-align: center; font-family: inherit; font-variant-numeric: tabular-nums; }
.code-input:focus { outline: none; border-color: var(--blue); box-shadow: 0 0 0 4px rgba(31,124,246,.15); }
.code-err { display: none; text-align: left; color: #B91C1C; font-size: 13px; font-weight: 600; margin-top: 6px; }
.code-help { text-align: left; font-size: 12px; color: var(--ink-3); margin-top: 6px; }
.photo-pick { display: flex; align-items: center; justify-content: center; gap: 8px; margin: 14px 0; padding: 14px; border-radius: 16px; border: 1.5px dashed #B9C6DA; background: var(--bg); color: var(--ink); font-size: 14px; font-weight: 700; cursor: pointer; }
.photo-pick:hover { border-color: var(--blue); color: var(--blue-3); }
.photo-preview { display: none; margin: 12px 0; }
.photo-preview img { width: 100%; max-height: 220px; object-fit: cover; border-radius: 16px; display: block; }
.pickup-modal-btns { display: flex; gap: 10px; }
.btn-pickup-cancel { flex: 1; min-height: 50px; border-radius: 999px; border: none; background: rgba(22,22,42,.07); color: var(--ink); font-size: 14px; font-weight: 700; cursor: pointer; }
.btn-pickup-cancel:hover { background: rgba(22,22,42,.12); }
.btn-pickup-confirm { flex: 2; min-height: 50px; border-radius: 999px; border: none; background: var(--green); color: #fff; font-size: 15px; font-weight: 800; cursor: pointer; transition: background-color .18s; }
.btn-pickup-confirm:hover:not(:disabled) { background: var(--green-2); }
.btn-pickup-confirm.blue { background: var(--blue-2); }
.btn-pickup-confirm.blue:hover:not(:disabled) { background: var(--blue-3); }
.btn-pickup-confirm:disabled { opacity: .6; cursor: not-allowed; }

/* ── Écran de fin ── */
.nav-done-screen { display: none; position: fixed; inset: 0; z-index: 9000; flex-direction: column; align-items: center; justify-content: center; gap: 10px; padding: 32px; text-align: center; color: #fff; background: radial-gradient(120% 90% at 50% 0%, rgba(31,124,246,.4), transparent 60%), linear-gradient(160deg, var(--navy), var(--navy-2)); }
.nav-done-ico { width: 84px; height: 84px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #D1FAE5; color: var(--green); margin-bottom: 8px; box-shadow: 0 0 0 10px rgba(209,250,229,.15); }
.nav-done-title { font-size: 24px; font-weight: 800; }
.nav-done-sub { font-size: 14.5px; color: #C9D6EA; }
.nav-done-cd { font-size: 12.5px; color: #93A7C6; }
.nav-done-btn { margin-top: 14px; min-height: 50px; padding: 0 28px; border-radius: 999px; border: none; background: #fff; color: var(--navy); font-size: 15px; font-weight: 800; cursor: pointer; }

/* Marqueurs de la carte */
.map-pin { display: flex; align-items: center; justify-content: center; border-radius: 50%; color: #fff; border: 3px solid #fff; box-shadow: 0 6px 18px rgba(11,30,61,.45); }

@media (min-width: 720px) {
    .info-panel { width: 440px; position: absolute; left: 16px; bottom: 16px; top: auto; margin: 0; border-radius: 24px; max-height: calc(100% - 100px); }
    .panel-grip { display: none; }
}
@media (prefers-reduced-motion: reduce) {
    .phase-dot, .vendor-wait span, .gps-loading-spinner { animation: none; }
    .pickup-modal { animation: none; }
}
</style>
</head>
<body>

{{-- Barre du haut --}}
<header class="nav-bar">
    <a href="{{ route('livreur.orders.index') }}" class="nav-back" aria-label="Retour à mes livraisons">{!! Ico::svg('arrow-left', '', 18) !!}</a>
    <div class="nav-order">
        <div class="nav-order-lbl">Commande</div>
        <div class="nav-order-num num">{{ $orderNum }}</div>
    </div>
    <div class="phase-badge {{ $initialPhase === 1 ? 'phase-1' : 'phase-2' }}" id="phaseBadge" role="status">
        <span class="phase-dot" aria-hidden="true"></span>
        <span id="phaseLabel">{{ $initialPhase === 1 ? 'Ramassage' : 'Livraison' }}</span>
    </div>
</header>

{{-- Carte --}}
<div class="map-wrap">
    <div id="navMap" aria-label="Carte de l'itinéraire"></div>
    @if($initialPhase === 1 && !$hasVendor)
    <div class="no-vendor-banner" id="noVendorBanner" style="display:flex">
        {!! Ico::svg('alert', '', 15) !!} La boutique n'a pas encore partagé sa position
    </div>
    @endif
</div>

{{-- Panneau du bas --}}
<section class="info-panel" aria-label="Course en cours">
    <div class="panel-grip" aria-hidden="true"></div>

    <div class="steps" aria-hidden="true">
        <div class="step {{ $initialPhase === 1 ? 'is-on' : 'is-done' }}" id="step1"><span class="step-n">1</span> Ramassage</div>
        <span class="step-bar"></span>
        <div class="step {{ $initialPhase === 2 ? 'is-on' : '' }}" id="step2"><span class="step-n">2</span> Livraison</div>
    </div>

    <div class="dest" id="destPanel">
        <div class="dest-ico {{ $initialPhase === 1 ? 'ico-vendor' : 'ico-client' }}" id="destIco">
            {!! Ico::svg($initialPhase === 1 ? 'store' : 'map-pin', '', 21) !!}
        </div>
        <div class="dest-txt">
            <div class="dest-lbl" id="destLbl">{{ $initialPhase === 1 ? 'Récupérer le colis chez' : 'Livrer à' }}</div>
            <div class="dest-val" id="destVal">{{ $initialPhase === 1 ? $shopName : $destination }}</div>
            <div class="dest-sub" id="destSub">{{ $initialPhase === 1 ? $shopAddress : $clientName }}</div>
            @if($initialPhase === 1 && !$hasVendor)
            <div class="vendor-wait" id="vendorStatusBadge"><span></span> En attente de la position de la boutique…</div>
            @endif
        </div>
    </div>

    {{-- Contact : la boutique pendant le ramassage, le client pendant la livraison --}}
    <div class="contact" id="contactPickup" style="{{ $initialPhase !== 1 || (!$shopPhone && !$shopWa) ? 'display:none' : '' }}">
        @if($shopPhone)<a href="tel:{{ $shopPhone }}" class="c-call">{!! Ico::svg('phone', '', 16) !!} Appeler la boutique</a>@endif
        @if($shopWa)<a href="https://wa.me/{{ $shopWa }}" target="_blank" rel="noopener" class="c-wa">{!! $waIco !!} WhatsApp</a>@endif
    </div>
    <div class="contact" id="contactClient" style="{{ $initialPhase !== 2 || (!$clientPhone && !$clientWa) ? 'display:none' : '' }}">
        @if($clientPhone)<a href="tel:{{ $clientPhone }}" class="c-call">{!! Ico::svg('phone', '', 16) !!} Appeler le client</a>@endif
        @if($clientWa)<a href="https://wa.me/{{ $clientWa }}" target="_blank" rel="noopener" class="c-wa">{!! $waIco !!} WhatsApp</a>@endif
    </div>

    <div class="facts">
        @if($isPaid)
        <span class="fact paid">{!! Ico::svg('check', '', 14) !!} Déjà payée en ligne</span>
        @else
        <span class="fact cash">{!! Ico::svg('wallet', '', 14) !!} À encaisser <strong class="num">{{ $fmt($order->total) }}</strong></span>
        @endif
        @if($order->delivery_slot)
        <span class="fact">{!! Ico::svg('clock', '', 14) !!} <strong>{{ $order->delivery_slot }}</strong></span>
        @endif
        @if(filled($order->delivery_code))
        <span class="fact">{!! Ico::svg('lock', '', 14) !!} Code de remise à demander</span>
        @endif
    </div>

    {{-- Action principale selon la phase --}}
    <button class="btn-phase btn-start" id="btnStart" style="{{ $initialPhase !== 1 ? 'display:none' : '' }}" onclick="startDelivery()">
        {!! Ico::svg('package', '', 19) !!} Colis récupéré
    </button>
    <button class="btn-phase btn-done" id="btnDone" style="{{ $initialPhase !== 2 ? 'display:none' : '' }}" onclick="markDelivered()">
        {!! Ico::svg('check', '', 19) !!} Marquer comme livrée
    </button>
    <button class="btn-gps" onclick="openGoogleMaps()">
        {!! Ico::svg('navigation', '', 17) !!} Ouvrir dans Google Maps
    </button>

    @if(app()->isLocal())
    <form method="POST" action="{{ route('orders.simulate.gps', $order) }}">
        @csrf
        <button type="submit" class="btn-dev">Test local : simuler les positions boutique, client et livreur</button>
    </form>
    @endif
</section>

{{-- Fenêtre : colis récupéré --}}
<div class="pickup-overlay" id="pickupOverlay" role="dialog" aria-modal="true" aria-labelledby="pickupTitle">
    <div class="pickup-modal">
        <div class="pickup-modal-ico">{!! Ico::svg('package', '', 30) !!}</div>
        <div class="pickup-modal-title" id="pickupTitle">Colis récupéré ?</div>
        <div class="pickup-modal-desc">En confirmant, vous commencez la livraison. L'itinéraire passe vers l'adresse du client.</div>
        <div class="pickup-modal-dest">
            <div class="pickup-modal-dest-ico">{!! Ico::svg('map-pin', '', 18) !!}</div>
            <div style="min-width:0">
                <div class="pickup-modal-dest-name">Livraison chez {{ $clientName }}</div>
                <div class="pickup-modal-dest-addr" id="pickupDestAddr">{{ $destination }}</div>
                @if($order->delivery_slot)<div class="pickup-modal-slot">Créneau : {{ $order->delivery_slot }}</div>@endif
            </div>
        </div>
        <div class="pickup-modal-btns">
            <button class="btn-pickup-cancel" onclick="closePickupModal()">Annuler</button>
            <button class="btn-pickup-confirm blue" id="btnPickupConfirm" onclick="confirmPickup()">Oui, commencer la livraison</button>
        </div>
    </div>
</div>

{{-- Fenêtre : preuve de livraison --}}
<div class="pickup-overlay" id="proofOverlay" role="dialog" aria-modal="true" aria-labelledby="proofTitle">
    <div class="pickup-modal">
        <div class="pickup-modal-ico green">{!! Ico::svg('camera', '', 30) !!}</div>
        <div class="pickup-modal-title" id="proofTitle">Remise du colis</div>
        <div class="pickup-modal-desc">Une photo du colis remis évite les litiges. Elle est facultative.</div>

        @if(filled($order->delivery_code))
        <label for="deliveryCodeInput" class="code-lbl">Code de remise donné par le client</label>
        <input id="deliveryCodeInput" class="code-input" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="4" autocomplete="off" placeholder="0000">
        <div id="deliveryCodeErr" class="code-err" role="alert"></div>
        <div class="code-help">Le client le voit dans son suivi de commande.</div>
        @endif

        <div id="proofPreviewWrap" class="photo-preview">
            <img id="proofPreviewImg" alt="Aperçu de la photo">
        </div>
        <label for="proofPhotoInput" id="proofPickBtn" class="photo-pick">
            {!! Ico::svg('camera', '', 18) !!} <span id="proofPickLbl">Prendre une photo</span>
        </label>
        <input type="file" id="proofPhotoInput" accept="image/*" capture="environment" style="display:none">

        <div class="pickup-modal-btns">
            <button class="btn-pickup-cancel" onclick="closeProofModal()">Annuler</button>
            <button class="btn-pickup-confirm" id="btnProofConfirm" onclick="confirmDelivered()">Confirmer la livraison</button>
        </div>
    </div>
</div>

{{-- Écran de fin --}}
<div class="nav-done-screen" id="navDoneScreen" role="status">
    <div class="nav-done-ico">{!! Ico::svg('check', '', 40) !!}</div>
    <div class="nav-done-title">Livraison terminée</div>
    <div class="nav-done-sub">La commande {{ $orderNum }} est marquée comme livrée.</div>
    <div class="nav-done-cd" id="navDoneCountdown"></div>
    <button class="nav-done-btn" onclick="goToOrders()">Voir mes livraisons</button>
</div>

{{-- Chargement GPS --}}
<div class="gps-loading" id="gpsLoading" role="status">
    <div class="gps-loading-spinner" aria-hidden="true"></div>
    <p>Localisation en cours…</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
<script>
const ORDER_ID   = {{ $order->id }};
const DATA_URL   = '{{ route('suivi.data', $order) }}';
const POS_URL    = '{{ route('orders.position.update', $order) }}';
const STATUS_URL = '{{ route('orders.driver.status', $order) }}';
const CSRF       = '{{ csrf_token() }}';
const SHOP_NAME    = {!! json_encode($shopName) !!};
const SHOP_ADDRESS = {!! json_encode($shopAddress) !!};
const DESTINATION  = {!! json_encode($destination) !!};
const CLIENT_NAME  = {!! json_encode($clientName) !!};
const ICO = {!! json_encode([
    'store'  => Ico::svg('store', '', 21),
    'pin'    => Ico::svg('map-pin', '', 21),
    'mStore' => Ico::svg('store', '', 18),
    'mPin'   => Ico::svg('map-pin', '', 18),
    'mMe'    => Ico::svg('navigation', '', 20),
]) !!};
const HAS_CONTACT = { 1: {{ $shopPhone || $shopWa ? 'true' : 'false' }}, 2: {{ $clientPhone || $clientWa ? 'true' : 'false' }} };

// Les noms et adresses viennent d'autres utilisateurs : toujours échappés avant d'entrer dans du HTML
const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

let currentPhase = {{ $initialPhase }};
let driverLat = null, driverLng = null;
let destLat   = null, destLng   = null;
let gpsWatcher    = null;
let mapInitialized = false;

const VENDOR = { lat: @json($order->vendor_lat), lng: @json($order->vendor_lng) };
const CLIENT = { lat: @json($order->client_lat), lng: @json($order->client_lng) };
const MAPBOX_TOKEN = '{{ config('services.mapbox.token') }}';

// ─────────────────────────────────────────────
// CARTE
// ─────────────────────────────────────────────
const _initLat = VENDOR.lat || CLIENT.lat || 9.641;
const _initLng = VENDOR.lng || CLIENT.lng || -13.578;
const map = L.map('navMap', { zoomControl: false, attributionControl: false })
    .setView([_initLat, _initLng], 13);
L.control.zoom({ position: 'topright' }).addTo(map);
/* Fournisseurs de tuiles avec repli automatique : Mapbox (si un jeton est configuré), puis OpenStreetMap.
   CartoDB a été retiré : ses tuiles exigent désormais une clé et affichaient « API KEY REQUIRED ». */
const TILE_PROVIDERS = [
    ...(MAPBOX_TOKEN ? [{ url:`https://api.mapbox.com/styles/v1/mapbox/streets-v12/tiles/256/{z}/{x}/{y}?access_token=${MAPBOX_TOKEN}`,
      opts:{ attribution:'© <a href="https://www.mapbox.com/">Mapbox</a>', maxZoom:19, crossOrigin:'' } }] : []),
    { url:'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
      opts:{ attribution:'© OpenStreetMap', maxZoom:19, subdomains:'abc', crossOrigin:'' } },
];
let _tileIdx = 0, _tileLayer = null, _tileFails = 0, _tileTimeout = null;
function _loadTiles(i) {
    if (i >= TILE_PROVIDERS.length) return;
    if (_tileLayer) map.removeLayer(_tileLayer);
    if (_tileTimeout) clearTimeout(_tileTimeout);
    _tileFails = 0;
    const p = TILE_PROVIDERS[i];
    _tileLayer = L.tileLayer(p.url, p.opts).addTo(map);
    _tileLayer.on('tileerror', () => { if (++_tileFails >= 3) _loadTiles(++_tileIdx); });
    let _tilesLoaded = false;
    _tileLayer.on('load', () => { _tilesLoaded = true; });
    _tileTimeout = setTimeout(() => {
        if (!_tilesLoaded && i < TILE_PROVIDERS.length - 1) _loadTiles(++_tileIdx);
    }, 5000);
}
_loadTiles(0);
// La carte occupe l'espace laissé par le panneau : sa taille est recalculée dès que cet espace change
// (chargement de la police, rotation du téléphone, fenêtre redimensionnée)
if ('ResizeObserver' in window) new ResizeObserver(() => map.invalidateSize()).observe(document.querySelector('.map-wrap'));
window.addEventListener('load', () => map.invalidateSize());

function makeIcon(svg, color, size = 42) {
    return L.divIcon({
        className: '',
        html: `<div class="map-pin" style="width:${size}px;height:${size}px;background:${color}">${svg}</div>`,
        iconSize: [size, size], iconAnchor: [size/2, size/2],
    });
}
const driverIcon = makeIcon(ICO.mMe, '#0B1E3D', 46);
const vendorIcon = makeIcon(ICO.mStore, '#F59E0B', 40);
const clientIcon = makeIcon(ICO.mPin, '#1668D8', 40);

let driverMarker = null, destMarker = null;
let routeShadow  = null, routeLine  = null;

// ─────────────────────────────────────────────
// ANIMATION DOUCE DU MARQUEUR (effet Google Maps)
// ─────────────────────────────────────────────
let _aniFrom = null, _aniTo = null, _aniStart = null, _aniRAF = null;

function animateDriverTo(toLat, toLng) {
    if (!driverMarker) return;
    const cur = driverMarker.getLatLng();
    // Si le livreur n'a pas bougé → pas d'animation
    if (Math.abs(cur.lat - toLat) < 1e-7 && Math.abs(cur.lng - toLng) < 1e-7) return;
    _aniFrom  = [cur.lat, cur.lng];
    _aniTo    = [toLat, toLng];
    _aniStart = null;
    if (_aniRAF) cancelAnimationFrame(_aniRAF);

    function step(ts) {
        if (!_aniStart) _aniStart = ts;
        const raw  = Math.min(1, (ts - _aniStart) / 700);       // 700 ms
        const ease = raw < .5 ? 2*raw*raw : -1+(4-2*raw)*raw;   // easeInOut
        driverMarker.setLatLng([
            _aniFrom[0] + (_aniTo[0] - _aniFrom[0]) * ease,
            _aniFrom[1] + (_aniTo[1] - _aniFrom[1]) * ease,
        ]);
        if (raw < 1) _aniRAF = requestAnimationFrame(step);
        else _aniRAF = null;
    }
    _aniRAF = requestAnimationFrame(step);
}

// ─────────────────────────────────────────────
// ROUTAGE — vraies routes
// ─────────────────────────────────────────────
let routePoints    = [];   // Tous les waypoints calculés
let routeRemaining = [];   // Waypoints non encore dépassés (portion restante)
let lastDestKey    = '';   // Clé "lat,lng" de la dernière destination → détecte un changement
let isFetchingRoute = false;
let offRouteStreak  = 0;   // Positions consécutives hors-route

async function fetchOSRMRoute(fLat, fLng, tLat, tLng) {
    try {
        const url = `https://api.mapbox.com/directions/v5/mapbox/driving/`
                  + `${fLng},${fLat};${tLng},${tLat}`
                  + `?geometries=geojson&overview=full&access_token=${MAPBOX_TOKEN}`;
        const r = await fetch(url);
        const d = await r.json();
        if (d.routes?.[0]) {
            return d.routes[0].geometry.coordinates.map(([lng, lat]) => [lat, lng]);
        }
    } catch(e) {}
    return [[fLat, fLng], [tLat, tLng]];
}

function redrawRemaining() {
    if (routeShadow) { map.removeLayer(routeShadow); routeShadow = null; }
    if (routeLine)   { map.removeLayer(routeLine);   routeLine   = null; }
    if (routeRemaining.length < 2) return;

    routeShadow = L.polyline(routeRemaining, {
        color: 'rgba(11,30,61,.25)', weight: 10, lineCap: 'round', lineJoin: 'round'
    }).addTo(map);
    routeLine = L.polyline(routeRemaining, {
        color:     currentPhase === 1 ? '#F59E0B' : '#1668D8',
        weight:    6, lineCap: 'round', lineJoin: 'round', opacity: .97,
    }).addTo(map);
    // Remettre la ligne au dessus
    if (routeLine) routeLine.bringToFront();
}

// Trouver l'indice du waypoint le plus proche de la position actuelle
function closestIdx(lat, lng, pts) {
    let minD = Infinity, minI = 0;
    for (let i = 0; i < pts.length; i++) {
        const d = (pts[i][0]-lat)**2 + (pts[i][1]-lng)**2;
        if (d < minD) { minD = d; minI = i; }
    }
    return minI;
}

// Distance minimale entre (lat,lng) et tous les points du trajet
function distToRoute(lat, lng, pts) {
    if (!pts.length) return Infinity;
    let minD = Infinity;
    for (const [pLat, pLng] of pts) {
        const d = Math.sqrt((pLat-lat)**2 + (pLng-lng)**2);
        if (d < minD) minD = d;
    }
    return minD;
}

// Consommer la portion déjà parcourue : la ligne se raccourcit à mesure qu'on avance
function consumeRoute(lat, lng) {
    if (routeRemaining.length < 2) return;
    const idx = closestIdx(lat, lng, routeRemaining);
    if (idx > 0) routeRemaining = routeRemaining.slice(idx);
    // Le premier point devient la position exacte du livreur
    routeRemaining[0] = [lat, lng];
    redrawRemaining();
}

// Calculer un nouveau trajet
async function planRoute(fLat, fLng, tLat, tLng) {
    if (isFetchingRoute) return;
    isFetchingRoute = true;
    const pts = await fetchOSRMRoute(fLat, fLng, tLat, tLng);
    routePoints    = pts;
    routeRemaining = [...pts];
    lastDestKey    = `${tLat},${tLng}`;
    offRouteStreak = 0;
    redrawRemaining();

    if (!mapInitialized && pts.length > 1) {
        map.fitBounds(L.latLngBounds(pts), { padding: [70, 70], maxZoom: 16 });
        mapInitialized = true;
    }
    isFetchingRoute = false;
}

// ─────────────────────────────────────────────
// PHASE — mise à jour de l'interface + destination
// ─────────────────────────────────────────────
function applyPhase(phase) {
    if (phase !== currentPhase) {
        mapInitialized = false;
        routePoints = []; routeRemaining = []; lastDestKey = '';
    }
    currentPhase = phase;

    const badge = document.getElementById('phaseBadge');
    const label = document.getElementById('phaseLabel');
    const ico   = document.getElementById('destIco');
    const lbl   = document.getElementById('destLbl');
    const val   = document.getElementById('destVal');
    const sub   = document.getElementById('destSub');

    document.getElementById('step1').className = 'step ' + (phase === 1 ? 'is-on' : 'is-done');
    document.getElementById('step2').className = 'step ' + (phase === 2 ? 'is-on' : '');
    document.getElementById('contactPickup').style.display = phase === 1 && HAS_CONTACT[1] ? '' : 'none';
    document.getElementById('contactClient').style.display = phase === 2 && HAS_CONTACT[2] ? '' : 'none';

    if (phase === 1) {
        badge.className  = 'phase-badge phase-1';
        label.textContent = 'Ramassage';
        ico.className    = 'dest-ico ico-vendor'; ico.innerHTML = ICO.store;
        lbl.textContent  = 'Récupérer le colis chez';
        val.textContent  = SHOP_NAME;
        sub.textContent  = SHOP_ADDRESS;
        destLat = VENDOR.lat; destLng = VENDOR.lng;
        if (destMarker) { map.removeLayer(destMarker); destMarker = null; }
        if (VENDOR.lat && VENDOR.lng) {
            destMarker = L.marker([VENDOR.lat, VENDOR.lng], { icon: vendorIcon })
                .bindPopup(`<b>${esc(SHOP_NAME)}</b><br>Point de ramassage`).addTo(map);
            if (!driverLat) map.setView([VENDOR.lat, VENDOR.lng], 15);
        }
        const banner = document.getElementById('noVendorBanner');
        if (banner) banner.style.display = (VENDOR.lat && VENDOR.lng) ? 'none' : 'flex';
    } else {
        badge.className  = 'phase-badge phase-2';
        label.textContent = 'Livraison';
        ico.className    = 'dest-ico ico-client'; ico.innerHTML = ICO.pin;
        lbl.textContent  = 'Livrer à';
        val.textContent  = DESTINATION;
        sub.textContent  = CLIENT_NAME;
        const wait = document.getElementById('vendorStatusBadge');
        if (wait) wait.style.display = 'none';
        const banner = document.getElementById('noVendorBanner');
        if (banner) banner.style.display = 'none';
        destLat = CLIENT.lat; destLng = CLIENT.lng;
        if (destMarker) { map.removeLayer(destMarker); destMarker = null; }
        if (CLIENT.lat && CLIENT.lng) {
            destMarker = L.marker([CLIENT.lat, CLIENT.lng], { icon: clientIcon })
                .bindPopup(`<b>${esc(CLIENT_NAME)}</b><br>${esc(DESTINATION)}`).addTo(map);
            if (!driverLat) map.setView([CLIENT.lat, CLIENT.lng], 15);
        }
    }

    document.getElementById('btnStart').style.display = phase === 1 ? '' : 'none';
    document.getElementById('btnDone').style.display  = phase === 2 ? '' : 'none';

    if (driverLat && destLat) planRoute(driverLat, driverLng, destLat, destLng);
}

// ─────────────────────────────────────────────
// GPS DU LIVREUR
// ─────────────────────────────────────────────
function startTracking() {
    if (!navigator.geolocation) {
        document.getElementById('gpsLoading').style.display = 'none';
        map.setView([9.641, -13.578], 13);
        applyPhase(currentPhase);
        return;
    }

    gpsWatcher = navigator.geolocation.watchPosition(
        async pos => {
            document.getElementById('gpsLoading').style.display = 'none';
            const newLat = pos.coords.latitude;
            const newLng = pos.coords.longitude;

            // ── Créer ou animer le marqueur ──
            if (!driverMarker) {
                driverMarker = L.marker([newLat, newLng], { icon: driverIcon })
                    .bindPopup('Ma position').addTo(map);
            } else {
                animateDriverTo(newLat, newLng);
            }
            driverLat = newLat; driverLng = newLng;

            // ── La carte suit immédiatement le livreur (avant tout calcul d'itinéraire) ──
            map.panTo([newLat, newLng], { animate: true, duration: 0.4 });

            // ── Envoyer la position au serveur (au plus toutes les 3 s) ──
            const _now = Date.now();
            if (!window._lastPosSent || _now - window._lastPosSent > 3000) {
                window._lastPosSent = _now;
                fetch(POS_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ lat: newLat, lng: newLng }),
                }).catch(() => {});
            }

            if (!destLat) return;

            const destKey = `${destLat},${destLng}`;

            if (destKey !== lastDestKey || routePoints.length === 0) {
                // Nouvelle destination → nouveau trajet
                await planRoute(newLat, newLng, destLat, destLng);
            } else {
                const dist = distToRoute(newLat, newLng, routeRemaining);
                if (dist > 0.004) {          // hors trajet à ~400 m
                    offRouteStreak++;
                    if (offRouteStreak >= 2) planRoute(newLat, newLng, destLat, destLng);
                } else {
                    offRouteStreak = 0;
                    consumeRoute(newLat, newLng);
                }
            }
        },
        () => {
            document.getElementById('gpsLoading').style.display = 'none';
            map.setView([9.641, -13.578], 13);
        },
        { enableHighAccuracy: true, maximumAge: 3000, timeout: 10000 }
    );
}

// ─────────────────────────────────────────────
// SUIVI (statut + positions)
// ─────────────────────────────────────────────
function pollOrderData() {
    fetch(DATA_URL).then(r => r.json()).then(data => {
        const prevVLat = VENDOR.lat, prevCLat = CLIENT.lat, prevCLng = CLIENT.lng;
        if (data.vendor_lat && data.vendor_lng) { VENDOR.lat = data.vendor_lat; VENDOR.lng = data.vendor_lng; }
        if (data.client_lat && data.client_lng) { CLIENT.lat = data.client_lat; CLIENT.lng = data.client_lng; }

        const newPhase = data.status === 'en_livraison' ? 2 : 1;
        if (newPhase !== currentPhase) {
            applyPhase(newPhase);
        } else if (currentPhase === 1 && VENDOR.lat && !prevVLat) {
            mapInitialized = false; applyPhase(1);
            const b = document.getElementById('vendorStatusBadge');
            if (b) b.style.display = 'none';
        } else if (currentPhase === 2 && CLIENT.lat) {
            if (!prevCLat) {
                applyPhase(2);
            } else if (CLIENT.lat !== prevCLat || CLIENT.lng !== prevCLng) {
                destLat = CLIENT.lat; destLng = CLIENT.lng;
                if (destMarker) destMarker.setLatLng([CLIENT.lat, CLIENT.lng]);
                if (driverLat)  planRoute(driverLat, driverLng, CLIENT.lat, CLIENT.lng);
            }
        }
    }).catch(() => {});
}

// ─────────────────────────────────────────────
// Compresse une photo côté navigateur avant envoi — une photo prise directement
// avec l'appareil d'un téléphone peut peser 8-15 Mo, ce qui échoue souvent sur
// une connexion mobile instable. On la redimensionne et compresse en JPEG léger.
// ─────────────────────────────────────────────
function compressImage(file, maxDim = 1600, quality = 0.75) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        const url = URL.createObjectURL(file);
        img.onload = () => {
            URL.revokeObjectURL(url);
            let { width, height } = img;
            if (width > height && width > maxDim) {
                height = Math.round(height * (maxDim / width));
                width = maxDim;
            } else if (height >= width && height > maxDim) {
                width = Math.round(width * (maxDim / height));
                height = maxDim;
            }
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            canvas.getContext('2d').drawImage(img, 0, 0, width, height);
            canvas.toBlob((blob) => {
                if (!blob) { reject(new Error('Échec de la compression de la photo.')); return; }
                resolve(new File([blob], 'proof.jpg', { type: 'image/jpeg' }));
            }, 'image/jpeg', quality);
        };
        img.onerror = () => { URL.revokeObjectURL(url); reject(new Error('Photo invalide.')); };
        img.src = url;
    });
}

// ─────────────────────────────────────────────
// ACTIONS STATUT
// ─────────────────────────────────────────────
async function postStatus(status, photoFile, extra = {}) {
    let opts;
    if (photoFile) {
        const fd = new FormData();
        fd.append('status', status);
        fd.append('proof_photo', photoFile);
        Object.entries(extra).forEach(([k, v]) => fd.append(k, v));
        opts = { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: fd };
    } else {
        opts = {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ status, ...extra }),
        };
    }
    const r = await fetch(STATUS_URL, opts);
    if (!r.ok) { const d = await r.json().catch(()=>({})); throw new Error(d.message || 'Erreur serveur.'); }
    return true;
}

function startDelivery()  { document.getElementById('pickupOverlay').classList.add('open'); }
function closePickupModal(){ document.getElementById('pickupOverlay').classList.remove('open'); }

async function confirmPickup() {
    const btn = document.getElementById('btnPickupConfirm');
    btn.disabled = true; btn.textContent = 'En cours…';
    try   { await postStatus('en_livraison'); closePickupModal(); applyPhase(2); }
    catch(e) { alert(e.message); btn.disabled = false; btn.textContent = 'Oui, commencer la livraison'; }
}

const ORDERS_URL = '{{ route('livreur.orders.index') }}';
function goToOrders() { window.location.href = ORDERS_URL; }

/* "Marquer comme livrée" → ouvre la fenêtre de remise (code + photo facultative) */
function markDelivered() {
    document.getElementById('proofOverlay').classList.add('open');
}
function closeProofModal() {
    document.getElementById('proofOverlay').classList.remove('open');
}

document.getElementById('proofPhotoInput')?.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById('proofPreviewImg').src = e.target.result;
        document.getElementById('proofPreviewWrap').style.display = 'block';
        document.getElementById('proofPickLbl').textContent = 'Reprendre la photo';
    };
    reader.readAsDataURL(file);
});

async function confirmDelivered() {
    const btn = document.getElementById('btnProofConfirm');
    btn.disabled = true; btn.textContent = 'Envoi en cours…';
    let photoFile = document.getElementById('proofPhotoInput').files[0] || null;
    try {
        if (photoFile) {
            btn.textContent = 'Compression de la photo…';
            try {
                photoFile = await compressImage(photoFile);
            } catch (e) {
                // Si la compression échoue pour une raison quelconque, on tente quand même
                // l'envoi avec le fichier d'origine plutôt que de bloquer la livraison.
            }
            btn.textContent = 'Envoi en cours…';
        }
        const codeInput = document.getElementById('deliveryCodeInput');
        const codeErr   = document.getElementById('deliveryCodeErr');
        if (codeErr) codeErr.style.display = 'none';
        try {
            await postStatus('livrée', photoFile, codeInput ? { delivery_code: codeInput.value } : {});
        } catch (err) {
            // Code de remise manquant ou faux : on l'affiche sous le champ plutôt qu'en alerte
            if (codeErr) {
                codeErr.textContent = err.message; codeErr.style.display = 'block'; codeInput.focus();
                btn.disabled = false; btn.textContent = 'Confirmer la livraison';
                return;
            }
            throw err;
        }
        closeProofModal();
        if (gpsWatcher) navigator.geolocation.clearWatch(gpsWatcher);
        if (_aniRAF)    cancelAnimationFrame(_aniRAF);
        document.getElementById('navDoneScreen').style.display = 'flex';
        let t = 3;
        const cd = document.getElementById('navDoneCountdown');
        cd.textContent = `Retour à vos livraisons dans ${t} s…`;
        const iv = setInterval(() => { t--; if (t<=0){clearInterval(iv);goToOrders();return;} cd.textContent=`Retour à vos livraisons dans ${t} s…`; }, 1000);
    } catch(e) {
        alert(e.message);
        btn.disabled = false; btn.textContent = 'Confirmer la livraison';
    }
}

function openGoogleMaps() {
    const origin = driverLat ? `${driverLat},${driverLng}` : '';
    let dest;
    if      (destLat && destLng)              dest = `${destLat},${destLng}`;
    else if (currentPhase===1 && SHOP_ADDRESS) dest = encodeURIComponent(SHOP_ADDRESS);
    else if (currentPhase===2 && DESTINATION)  dest = encodeURIComponent(DESTINATION);
    else { alert('Destination non disponible.'); return; }
    window.open(`https://www.google.com/maps/dir/${origin}/${dest}`, '_blank');
}

// Fermer une fenêtre en touchant le fond ou avec Échap
document.querySelectorAll('.pickup-overlay').forEach(o => o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); }));
document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.pickup-overlay.open').forEach(o => o.classList.remove('open')); });

// ─────────────────────────────────────────────
// INIT
// ─────────────────────────────────────────────
applyPhase(currentPhase);
startTracking();
setInterval(pollOrderData, 5000);
</script>
</body>
</html>
