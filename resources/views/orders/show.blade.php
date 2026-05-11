{{-- resources/views/orders/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Suivi · Commande #' . str_pad($order->id, 5, '0', STR_PAD_LEFT))
@php $groupOrders ??= collect([$order]); @endphp
@php $bodyClass = 'is-dashboard'; @endphp


@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css"/>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --orange:    #f06a0f;
    --orange-dk: #d45a00;
    --orange-lt: #fff4ec;
    --orange-bd: #fcd9b6;
    --orange-2:  #fb923c;
    --green:     #10b981;
    --green-lt:  #ecfdf5;
    --green-dk:  #065f46;
    --blue:      #3b82f6;
    --blue-lt:   #eff6ff;
    --yellow:    #f59e0b;
    --yellow-lt: #fffbeb;
    --red:       #ef4444;
    --red-lt:    #fef2f2;
    --text:      #0f172a;
    --text-2:    #475569;
    --muted:     #94a3b8;
    --border:    #e2e8f0;
    --surface:   #ffffff;
    --bg:        #f8f9fc;
    --font:      'Plus Jakarta Sans', system-ui, sans-serif;
    --mono:      'JetBrains Mono', monospace;
    --r:         16px;
    --r-sm:      10px;
    --r-xs:      7px;
    --shadow:    0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.07);
    --shadow-lg: 0 8px 40px rgba(0,0,0,.12);
}

html, body {
    font-family: var(--font);
    background: var(--bg);
    color: var(--text);
    -webkit-font-smoothing: antialiased;
    min-height: 100vh;
}

/* ── Page ── */
.page { max-width: 860px; margin: 0 auto; padding: 24px 16px 80px; }

/* ── Topbar ── */
.topbar {
    background: linear-gradient(135deg, var(--orange) 0%, var(--orange-dk) 60%, #b84e00 100%);
    margin: -24px -16px 24px;
    padding: 0 20px;
    height: 64px;
    display: flex; align-items: center; gap: 14px;
    box-shadow: 0 4px 20px rgba(240,106,15,.4);
    position: relative; overflow: hidden;
}
.topbar::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(circle at 80% 50%, rgba(255,255,255,.09) 0%, transparent 60%);
    pointer-events: none;
}
.topbar::after {
    content: '';
    position: absolute; top: -50px; right: -50px;
    width: 160px; height: 160px; border-radius: 50%;
    background: rgba(255,255,255,.06); pointer-events: none;
}
.btn-back {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px;
    background: rgba(255,255,255,.16);
    border: 1.5px solid rgba(255,255,255,.32);
    border-radius: 30px;
    color: #fff; font-size: 13px; font-weight: 700;
    text-decoration: none; white-space: nowrap;
    transition: all .18s; flex-shrink: 0; position: relative; z-index: 1;
}
.btn-back:hover { background: rgba(255,255,255,.28); border-color: rgba(255,255,255,.55); transform: translateX(-2px); }
.topbar-info { flex: 1; position: relative; z-index: 1; min-width: 0; }
.topbar-info h1 { font-size: 16px; font-weight: 800; color: #fff; letter-spacing: -.3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.topbar-info p  { font-size: 11.5px; color: rgba(255,255,255,.75); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.topbar-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 13px; border-radius: 20px;
    background: rgba(255,255,255,.18); border: 1.5px solid rgba(255,255,255,.32);
    color: #fff; font-size: 12px; font-weight: 700;
    white-space: nowrap; flex-shrink: 0; position: relative; z-index: 1;
}

/* ── Carte GPS HERO ── */
.map-hero {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
    margin-bottom: 20px;
    position: relative;
}
.map-topstrip {
    height: 4px;
    background: linear-gradient(90deg, var(--orange), var(--orange-2), var(--yellow));
}
.map-header {
    padding: 14px 20px;
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    border-bottom: 1px solid var(--border);
    background: var(--surface);
}
.map-header-left { display: flex; align-items: center; gap: 10px; }
.map-title { font-size: 14px; font-weight: 800; color: var(--text); }
.live-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 20px;
    background: #f0fdf4; border: 1px solid #86efac;
    font-size: 11px; font-weight: 700; color: #15803d;
}
.live-pill.offline { background: #f8fafc; border-color: var(--border); color: var(--muted); }
.live-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #22c55e; flex-shrink: 0;
    animation: gps-pulse 1.8s ease-in-out infinite;
    box-shadow: 0 0 5px #22c55e;
}
.live-dot.offline { background: var(--muted); animation: none; box-shadow: none; }
@keyframes gps-pulse { 0%,100%{opacity:1} 50%{opacity:.35} }
.last-update-txt { font-size: 11.5px; color: var(--muted); font-weight: 500; }

/* ── La carte elle-même ── */
#gpsMap { height: 440px; width: 100%; }
@media (max-width: 600px) { #gpsMap { height: 320px; } }

/* ── Overlay info livreur sur la carte ── */
.map-livreur-overlay {
    position: absolute;
    bottom: 20px; left: 50%; transform: translateX(-50%);
    background: rgba(255,255,255,.96);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(240,106,15,.2);
    border-radius: 40px;
    padding: 8px 18px 8px 10px;
    display: flex; align-items: center; gap: 10px;
    box-shadow: 0 4px 24px rgba(0,0,0,.18);
    z-index: 500;
    white-space: nowrap;
    pointer-events: none;
}
.mlo-av {
    width: 34px; height: 34px; border-radius: 50%;
    background: linear-gradient(135deg, var(--orange), var(--orange-dk));
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 800; color: #fff; flex-shrink: 0;
    box-shadow: 0 0 0 2px rgba(240,106,15,.25);
}
.mlo-name { font-size: 13px; font-weight: 800; color: var(--text); }
.mlo-status { font-size: 11px; color: var(--muted); margin-top: 1px; display: flex; align-items: center; gap: 4px; }
.mlo-dot { width: 6px; height: 6px; border-radius: 50%; background: #22c55e; animation: gps-pulse 2s infinite; }

/* ── Progress bar ── */
.progress-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 20px 24px;
    box-shadow: var(--shadow);
    margin-bottom: 20px;
}
.prog-steps { display: flex; align-items: flex-start; position: relative; }
.prog-track {
    position: absolute; top: 15px; left: 14px; right: 14px;
    height: 3px; background: var(--border); z-index: 0;
}
.prog-fill {
    position: absolute; top: 15px; left: 14px;
    height: 3px;
    background: linear-gradient(90deg, var(--orange), var(--orange-2));
    z-index: 1; transition: width .6s cubic-bezier(.4,0,.2,1);
}
.prog-step { flex: 1; display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2; gap: 6px; }
.prog-dot {
    width: 30px; height: 30px; border-radius: 50%;
    border: 2.5px solid var(--border); background: var(--surface);
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; transition: all .3s;
}
.prog-dot.done {
    background: var(--orange); border-color: var(--orange-dk);
    color: #fff; font-size: 12px;
    box-shadow: 0 0 0 4px var(--orange-lt);
}
.prog-dot.current {
    background: var(--surface); border-color: var(--orange);
    color: var(--orange); border-width: 2.5px;
    box-shadow: 0 0 0 4px var(--orange-lt);
    animation: step-pulse 1.6s infinite;
}
@keyframes step-pulse {
    0%,100%{ box-shadow:0 0 0 4px var(--orange-lt); }
    50%     { box-shadow:0 0 0 8px rgba(240,106,15,.1); }
}
.prog-lbl { font-size: 10.5px; font-weight: 600; color: var(--muted); text-align: center; }
.prog-lbl.active { color: var(--orange); font-weight: 700; }
.prog-lbl.done-lbl { color: var(--green-dk); }

/* ── Info cards grid ── */
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px; }
.info-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 18px 20px;
    box-shadow: var(--shadow);
}
.ic-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: var(--muted); margin-bottom: 10px; display: flex; align-items: center; gap: 5px; }
.ic-main { font-size: 22px; font-weight: 900; color: var(--text); font-family: var(--mono); letter-spacing: -1px; line-height: 1; }
.ic-sub { font-size: 11.5px; color: var(--muted); margin-top: 4px; }

/* ── Livreur card ── */
.livreur-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow);
    margin-bottom: 20px;
}
.lc-header {
    padding: 14px 20px;
    background: linear-gradient(135deg, var(--orange-lt), #fff8f3);
    border-bottom: 1px solid var(--orange-bd);
    display: flex; align-items: center; gap: 10px;
}
.lc-title { font-size: 13px; font-weight: 800; color: var(--orange-dk); }
.lc-body { padding: 16px 20px; display: flex; align-items: center; gap: 14px; }
.lc-av {
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, var(--orange), var(--orange-dk));
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; font-weight: 900; color: #fff; flex-shrink: 0;
    box-shadow: 0 0 0 3px var(--orange-lt), 0 4px 12px rgba(240,106,15,.25);
}
.lc-name { font-size: 15px; font-weight: 800; color: var(--text); letter-spacing: -.3px; }
.lc-info { font-size: 12px; color: var(--muted); margin-top: 3px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.lc-status {
    display: inline-flex; align-items: center; gap: 4px;
    background: #f0fdf4; border: 1px solid #86efac;
    color: #15803d; border-radius: 20px;
    padding: 2px 9px; font-size: 11px; font-weight: 700;
}

/* ── Etat special ── */
.state-banner {
    border-radius: var(--r);
    padding: 28px 24px;
    text-align: center;
    margin-bottom: 20px;
    box-shadow: var(--shadow);
}
.state-banner.delivered {
    background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
    border: 1.5px solid #86efac;
}
.state-banner.cancelled {
    background: var(--red-lt);
    border: 1.5px solid #fca5a5;
}
.state-banner.waiting {
    background: var(--orange-lt);
    border: 1.5px solid var(--orange-bd);
}
.sb-ico { font-size: 48px; display: block; margin-bottom: 12px; }
.sb-title { font-size: 20px; font-weight: 900; margin-bottom: 6px; letter-spacing: -.4px; }
.sb-sub { font-size: 13.5px; line-height: 1.6; }

/* ── Infos commande ── */
.order-meta-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow);
    margin-bottom: 20px;
}
.omc-head { padding: 13px 20px; border-bottom: 1px solid var(--border); background: var(--bg); }
.omc-title { font-size: 12.5px; font-weight: 800; color: var(--text); display: flex; align-items: center; gap: 7px; }
.omc-row { display: flex; align-items: flex-start; gap: 12px; padding: 12px 20px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
.omc-row:last-child { border-bottom: none; }
.omc-lbl { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--muted); min-width: 80px; flex-shrink: 0; padding-top: 1px; }
.omc-val { font-weight: 600; color: var(--text); flex: 1; word-break: break-word; }
.omc-val.mono { font-family: var(--mono); }

/* ── Badge statut ── */
.badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 11px; border-radius: 20px; font-size: 11.5px; font-weight: 700; border: 1.5px solid; }
.badge-attente  { background: var(--yellow-lt); color: #92400e; border-color: #fde68a; }
.badge-confirm  { background: var(--blue-lt);   color: #1e40af; border-color: #bfdbfe; }
.badge-livraison{ background: var(--orange-lt); color: var(--orange-dk); border-color: var(--orange-bd); }
.badge-livree   { background: var(--green-lt);  color: var(--green-dk); border-color: #a7f3d0; }
.badge-annulee  { background: var(--red-lt);    color: #991b1b; border-color: #fca5a5; }

/* ── Flash ── */
.flash { padding: 12px 16px; border-radius: var(--r-xs); border: 1.5px solid; font-size: 13px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
.flash-success { background: var(--green-lt); border-color: #86efac; color: var(--green-dk); }

/* ── Responsive ── */
@media (max-width: 640px) {
    .info-grid { grid-template-columns: 1fr; }
    .topbar { margin: -24px -12px 20px; padding: 0 14px; height: 58px; }
    .topbar-info h1 { font-size: 14.5px; }
    .topbar-badge { display: none; }
    .page { padding: 20px 12px 60px; }
    .map-livreur-overlay { bottom: 12px; padding: 7px 14px 7px 8px; }
    .mlo-name { font-size: 12px; }
    .progress-card { padding: 16px; }
}

/* Bootstrap impose max-width:100%;height:auto sur toutes les <img> — ça casse les tuiles Leaflet */
.leaflet-container img { max-width: none !important; height: auto !important; }
.leaflet-tile          { max-width: none !important; }
</style>
@endpush

@section('content')
@php
use App\Models\Order;

$s           = $order->status;
$isCancelled = $s === Order::STATUS_ANNULEE;
$isDelivered = $s === Order::STATUS_LIVREE;
$isOngoing   = $s === Order::STATUS_EN_LIVRAISON;
$hasGPS      = !is_null($order->current_lat) && !is_null($order->current_lng);
$devise      = $order->shop?->currency ?? 'GNF';
$isGroup     = isset($groupOrders) && $groupOrders->count() > 1;
$groupTotal  = $isGroup ? $groupOrders->sum('total') : $order->total;
// Nom du livreur/chauffeur quel que soit le système
$gpsDriverName = $order->livreur?->name
    ?? ($order->driver_id ? optional(\App\Models\Driver::find($order->driver_id))->name : null);
$gpsDriverPhone = $order->livreur?->phone
    ?? ($order->driver_id ? optional(\App\Models\Driver::find($order->driver_id))->phone : null);
$hasAnyDriver      = (bool)($order->livreur_id || $order->driver_id);
$isCompanyDriver   = (bool)$order->driver_id;

$statusMap = [
    Order::STATUS_EN_ATTENTE   => ['label'=>'En attente',   'ico'=>'⏳', 'badge'=>'badge-attente',  'step'=>0],
    Order::STATUS_CONFIRMEE    => ['label'=>'Confirmée',    'ico'=>'📦', 'badge'=>'badge-confirm',  'step'=>1],
    Order::STATUS_EN_LIVRAISON => ['label'=>'En livraison', 'ico'=>'🚴', 'badge'=>'badge-livraison','step'=>2],
    Order::STATUS_LIVREE       => ['label'=>'Livrée',       'ico'=>'✅', 'badge'=>'badge-livree',   'step'=>3],
    Order::STATUS_ANNULEE      => ['label'=>'Annulée',      'ico'=>'❌', 'badge'=>'badge-annulee',  'step'=>-1],
];
$sInfo      = $statusMap[$s] ?? $statusMap[Order::STATUS_EN_ATTENTE];
$curStep    = $sInfo['step'];
$steps      = [
    ['ico'=>'🕐','lbl'=>'Reçue'],
    ['ico'=>'📦','lbl'=>'Confirmée'],
    ['ico'=>'🚴','lbl'=>'En route'],
    ['ico'=>'🏠','lbl'=>'Livrée'],
];
$fillPct = $curStep >= 0 ? min(($curStep / 3) * 100, 100) : 0;

$init = fn(string $n): string =>
    strtoupper(substr(explode(' ',$n)[0],0,1)).
    strtoupper(substr(explode(' ',$n)[1] ?? substr($n,1,1),0,1));
@endphp

<div class="page">

    {{-- ── Topbar ── --}}
    <div class="topbar">
        <a href="{{ route('client.orders.index') }}" class="btn-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Mes commandes
        </a>
        <div class="topbar-info">
            @if($isGroup)
            <h1>{{ $groupOrders->count() }} commandes · 1 trajet</h1>
            <p>{{ $order->shop?->name ?? '—' }} · #{{ $groupOrders->pluck('id')->map(fn($id)=>str_pad($id,5,'0',STR_PAD_LEFT))->implode(' · #') }}</p>
            @else
            <h1>Commande #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</h1>
            <p>{{ $order->shop?->name ?? '—' }} · {{ $order->created_at->format('d/m/Y à H:i') }}</p>
            @endif
        </div>
        <span class="topbar-badge" id="uiBadgeTop">{{ $sInfo['ico'] }} {{ $sInfo['label'] }}</span>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
    @endif

    {{-- ── Banners statut (mis à jour automatiquement) ── --}}
    <div id="uiStateBanner">
    @if($isDelivered)
    <div class="state-banner delivered">
        <span class="sb-ico">🎉</span>
        <div class="sb-title" style="color:var(--green-dk)">Commande livrée !</div>
        <div class="sb-sub" style="color:#047857">Votre colis a bien été remis. Merci de votre confiance.</div>
        @if(!$order->review)
        <a href="{{ route('client.reviews.create', $order) }}" style="display:inline-flex;align-items:center;gap:6px;margin-top:14px;padding:10px 22px;background:var(--orange);color:#fff;border-radius:30px;font-size:13px;font-weight:700;text-decoration:none;box-shadow:0 4px 14px rgba(240,106,15,.35)">
            ⭐ Laisser un avis
        </a>
        @endif
    </div>
    @endif

    {{-- ── Annulée ── --}}
    @if($isCancelled)
    <div class="state-banner cancelled">
        <span class="sb-ico">❌</span>
        <div class="sb-title" style="color:#991b1b">Commande annulée</div>
        <div class="sb-sub" style="color:#b91c1c">Cette commande a été annulée. Aucun suivi disponible.</div>
        <a href="{{ route('client.dashboard') }}" style="display:inline-flex;align-items:center;gap:6px;margin-top:14px;padding:10px 22px;background:var(--orange);color:#fff;border-radius:30px;font-size:13px;font-weight:700;text-decoration:none;box-shadow:0 4px 14px rgba(240,106,15,.35)">
            🏪 Découvrir les boutiques
        </a>
    </div>
    @else

    {{-- ── En attente d'assignation ── --}}
    @if($s === Order::STATUS_EN_ATTENTE || $s === Order::STATUS_CONFIRMEE)
    <div class="state-banner waiting">
        <span class="sb-ico">{{ $sInfo['ico'] }}</span>
        <div class="sb-title" style="color:var(--orange-dk)">{{ $sInfo['label'] }}</div>
        <div class="sb-sub" style="color:#9a3412">
            @if($s === Order::STATUS_EN_ATTENTE)
                Votre commande est en attente de confirmation par la boutique.
            @else
                Votre commande est confirmée. Un livreur va être assigné.
            @endif
        </div>
    </div>
    @endif

    {{-- ══ CARTE GPS HERO ══ --}}
    <div class="map-hero">
        <div class="map-topstrip"></div>
        <div class="map-header">
            <div class="map-header-left">
                <div id="livePill" class="live-pill {{ $isDelivered || !$isOngoing ? 'offline' : '' }}">
                    <span class="live-dot {{ $isDelivered || !$isOngoing ? 'offline' : '' }}" id="liveDot"></span>
                    <span id="liveLabel">{{ $isDelivered ? 'Terminé' : ($isOngoing ? 'En direct' : 'En attente') }}</span>
                </div>
                <span class="map-title">📍 Suivi GPS du livreur</span>
            </div>
            <span class="last-update-txt" id="lastUpdate">
                @if($hasGPS)
                    {{ $order->last_ping_at?->diffForHumans() ?? '—' }}
                @elseif($isOngoing)
                    GPS en chargement…
                @else
                    En attente…
                @endif
            </span>
        </div>

        {{-- La carte --}}
        <div style="position:relative">
            <div id="gpsMap" style="height:440px;width:100%;display:block;background:#e8e0d8"></div>

            {{-- Overlay livreur flottant au bas de la carte --}}
            @if($hasAnyDriver && $gpsDriverName)
            <div class="map-livreur-overlay" id="livreurOverlay">
                <div class="mlo-av">{{ $init($gpsDriverName) }}</div>
                <div>
                    <div class="mlo-name">{{ $gpsDriverName }}</div>
                    <div class="mlo-status">
                        @if($isOngoing)
                            <span class="mlo-dot"></span> En route vers vous
                        @elseif($isDelivered)
                            ✅ Livraison terminée
                        @else
                            🚴 Prépare la livraison
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Progress ── --}}
    <div class="progress-card">
        <div class="prog-steps">
            <div class="prog-track"></div>
            <div class="prog-fill" id="uiProgFill" style="width:{{ $fillPct }}%"></div>
            @foreach($steps as $i => $step)
            <div class="prog-step">
                <div class="prog-dot {{ $i < $curStep ? 'done' : ($i === $curStep ? 'current' : '') }}" id="uiDot{{ $i }}">
                    @if($i < $curStep) ✓ @else {{ $step['ico'] }} @endif
                </div>
                <div class="prog-lbl {{ $i === $curStep ? 'active' : ($i < $curStep ? 'done-lbl' : '') }}" id="uiLbl{{ $i }}">{{ $step['lbl'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    @endif {{-- /not cancelled --}}
    </div>{{-- /uiStateBanner --}}

    {{-- ── Infos commande ── --}}
    <div class="info-grid">
        <div class="info-card">
            <div class="ic-label">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Total à payer
            </div>
            <div class="ic-main">{{ number_format($groupTotal, 0, ',', ' ') }}</div>
            @php $totalItems = $isGroup ? $groupOrders->sum(fn($o) => $o->items->count()) : $order->items->count(); @endphp
            <div class="ic-sub">{{ $devise }} · {{ $totalItems }} article{{ $totalItems > 1 ? 's' : '' }}</div>
        </div>
        <div class="info-card">
            <div class="ic-label">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Statut
            </div>
            <div style="margin-top:4px"><span class="badge {{ $sInfo['badge'] }}" id="uiBadgeInfo" style="font-size:13px;padding:6px 14px">{{ $sInfo['ico'] }} {{ $sInfo['label'] }}</span></div>
            <div class="ic-sub">{{ $order->created_at->format('d/m/Y à H:i') }}</div>
        </div>
    </div>

    {{-- ── Livreur (mis à jour automatiquement) ── --}}
    <div id="uiLivreurWrap">
    @if($hasAnyDriver && $gpsDriverName)
    <div class="livreur-card">
        <div class="lc-header">
            <span style="font-size:16px">🛵</span>
            <span class="lc-title">Votre livreur</span>
        </div>
        <div class="lc-body">
            <div class="lc-av">{{ $init($gpsDriverName) }}</div>
            <div style="flex:1;min-width:0">
                <div class="lc-name">{{ $gpsDriverName }}</div>
                <div class="lc-info">
                    @if($gpsDriverPhone)
                    <a href="tel:{{ $gpsDriverPhone }}" style="color:var(--orange);font-weight:700;text-decoration:none">📞 {{ $gpsDriverPhone }}</a>
                    @endif
                    <span class="lc-status">
                        @if($isDelivered) ✅ Terminé @elseif($isOngoing) 🟢 En route @else 🟡 Assigné @endif
                    </span>
                </div>
            </div>
            @if($order->delivery_fee && !$isCompanyDriver)
            <div style="text-align:right;flex-shrink:0">
                <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.4px">Livraison</div>
                <div style="font-size:16px;font-weight:800;color:var(--orange);font-family:var(--mono)">{{ number_format($order->delivery_fee,0,',',' ') }}</div>
                <div style="font-size:10px;color:var(--muted)">{{ $devise }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif
    </div>{{-- /uiLivreurWrap --}}

    {{-- ── Détails commande ── --}}
    <div class="order-meta-card">
        <div class="omc-head">
            <div class="omc-title">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                Détails de la commande
            </div>
        </div>
        <div class="omc-row">
            <span class="omc-lbl">N°</span>
            <span class="omc-val mono">
                @if($isGroup)
                    @foreach($groupOrders as $go)
                    <div>#{{ str_pad($go->id, 5, '0', STR_PAD_LEFT) }}</div>
                    @endforeach
                @else
                    #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                @endif
            </span>
        </div>
        <div class="omc-row">
            <span class="omc-lbl">Boutique</span>
            <span class="omc-val">🏪 {{ $order->shop?->name ?? '—' }}</span>
        </div>
        @if($order->delivery_destination)
        <div class="omc-row">
            <span class="omc-lbl">Livraison</span>
            <span class="omc-val">📍 {{ $order->delivery_destination }}</span>
        </div>
        @endif
        <div class="omc-row">
            <span class="omc-lbl">Articles</span>
            <span class="omc-val">
                @php
                    $allItems = $isGroup
                        ? $groupOrders->flatMap(fn($o) => $o->items->all())
                        : $order->items;
                    $shownItems = $allItems->take(4);
                    $remaining  = $allItems->count() - $shownItems->count();
                @endphp
                @foreach($shownItems as $item)
                <div style="font-size:12.5px;margin-bottom:2px">· {{ $item->product?->name ?? 'Produit' }} <span style="color:var(--muted)">×{{ $item->quantity }}</span></div>
                @endforeach
                @if($remaining > 0)
                <div style="font-size:12px;color:var(--muted)">+ {{ $remaining }} autre(s)</div>
                @endif
            </span>
        </div>
        <div class="omc-row">
            <span class="omc-lbl">Total</span>
            <span class="omc-val mono" style="color:var(--orange);font-size:15px;font-weight:800">{{ number_format($groupTotal,0,',',' ') }} {{ $devise }}</span>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
<style>
@keyframes moto-search {
    0%,100% { transform: scale(1);   opacity: 1; }
    50%      { transform: scale(.88); opacity: .7; }
}
@keyframes halo-pulse {
    0%   { transform: scale(1);   opacity: .35; }
    100% { transform: scale(2.8); opacity: 0; }
}
.moto-searching { animation: moto-search 1.6s ease-in-out infinite; }
.halo-ring {
    position: absolute; inset: -5px; border-radius: 50%;
    border: 2px solid rgba(240,106,15,.6);
    animation: halo-pulse 1.8s ease-out infinite;
}
.halo-ring2 {
    position: absolute; inset: -5px; border-radius: 50%;
    border: 1.5px solid rgba(240,106,15,.35);
    animation: halo-pulse 1.8s ease-out .6s infinite;
}
</style>
<script>
(function () {
    @php
        $lat0 = $order->current_lat ?? 9.6412;
        $lng0 = $order->current_lng ?? -13.5784;
    @endphp

    /* ── Constantes Blade → JS ── */
    const DATA_URL     = '{{ route('suivi.data', $order) }}';
    const IS_DELIVERED = {{ $isDelivered ? 'true' : 'false' }};
    const IS_CANCELLED = {{ $isCancelled ? 'true' : 'false' }};
    const HAS_GPS      = {{ $hasGPS ? 'true' : 'false' }};
    const REVIEW_URL   = {!! json_encode($order->review ? null : route('client.reviews.create', $order)) !!};
    const DEV_FEE          = {{ $order->delivery_fee ?? 0 }};
    const DEVISE           = {!! json_encode($devise) !!};
    const IS_COMPANY_DRIVER = {{ $isCompanyDriver ? 'true' : 'false' }};

    /* ── État mutable ── */
    let lat           = {{ $lat0 }};
    let lng           = {{ $lng0 }};
    let gpsFound      = HAS_GPS;
    let currentStatus = {!! json_encode($order->status) !!};
    let lname         = {!! json_encode($gpsDriverName ?? '') !!};
    let pollId        = null;

    /* ── Carte Leaflet ── */
    const map = L.map('gpsMap', { zoomControl: true, attributionControl: true })
        .setView([lat, lng], HAS_GPS ? 16 : 13);

    const TILE_PROVIDERS = [
        { url:'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
          opts:{ attribution:'© OpenStreetMap', maxZoom:19, subdomains:'abc', crossOrigin:'' } },
        { url:'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png',
          opts:{ attribution:'© CartoDB', subdomains:'abcd', maxZoom:20, crossOrigin:'' } },
        { url:'https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}',
          opts:{ attribution:'© Esri', maxZoom:19, crossOrigin:'' } },
    ];
    let _pIdx = 0, _layer = null, _fails = 0;
    function _loadTiles(i) {
        if (i >= TILE_PROVIDERS.length) return;
        if (_layer) map.removeLayer(_layer);
        _fails = 0;
        const p = TILE_PROVIDERS[i];
        _layer = L.tileLayer(p.url, p.opts).addTo(map);
        _layer.on('tileerror', () => { if (++_fails >= 3) _loadTiles(++_pIdx); });
    }
    _loadTiles(0);
    setTimeout(() => map.invalidateSize(), 150);
    setTimeout(() => map.invalidateSize(), 700);
    window.addEventListener('load', () => map.invalidateSize());

    /* ── Icône moto ── */
    function makeMotoIcon(searching) {
        return L.divIcon({
            html: `<div style="position:relative;width:32px;height:32px">
                ${searching ? '<div class="halo-ring"></div><div class="halo-ring2"></div>' : ''}
                <div class="${searching ? 'moto-searching' : ''}" style="
                    width:32px;height:32px;border-radius:50%;
                    background:linear-gradient(135deg,#f06a0f 0%,#d45a00 100%);
                    border:2px solid #fff;
                    box-shadow:0 2px 10px rgba(240,106,15,.55),0 0 0 3px rgba(240,106,15,.15);
                    display:flex;align-items:center;justify-content:center;
                    font-size:15px;line-height:1;cursor:pointer">🛵</div>
            </div>`,
            iconSize:[32,32], iconAnchor:[16,16], popupAnchor:[0,-18], className:''
        });
    }

    /* ── Objets Leaflet ── */
    const marker = L.marker([lat, lng], { icon: makeMotoIcon(!gpsFound) }).addTo(map);

    function buildPopup() {
        return lname
            ? `<div style="font-family:system-ui;padding:2px 4px;min-width:120px">
                <div style="font-size:14px;font-weight:800;color:#0f172a;margin-bottom:3px">${lname}</div>
                <div style="font-size:11px;color:#94a3b8">${gpsFound ? '🟢 En route' : '⏳ En attente du signal…'}</div>
               </div>`
            : '';
    }
    marker.bindPopup(buildPopup(), { offset:[0,-28], minWidth:140 });
    if (lname) marker.openPopup();

    const halo = L.circle([lat, lng], {
        color:'#f06a0f', fillColor:'#f06a0f',
        fillOpacity: gpsFound ? .08 : .04,
        radius: gpsFound ? 65 : 200,
        weight: gpsFound ? 1.5 : 1,
        dashArray: gpsFound ? null : '5 5'
    }).addTo(map);

    const trailShadow = L.polyline([[lat,lng]], {
        color:'rgba(0,0,0,.15)', weight:7, opacity:1, lineCap:'round', lineJoin:'round'
    }).addTo(map);

    const trail = L.polyline([[lat,lng]], {
        color:'#f06a0f', weight:4.5, opacity:.85, lineCap:'round', lineJoin:'round',
        dashArray: IS_DELIVERED ? null : '1 0'
    }).addTo(map);

    let startDot = null;
    if (gpsFound) {
        startDot = L.circleMarker([lat,lng], {
            radius:7, color:'#fff', weight:2.5, fillColor:'#f06a0f', fillOpacity:1
        }).addTo(map).bindTooltip('Départ', { permanent:false, direction:'top' });
    }

    function animateTo(from, to, cb, ms = 800) {
        const t0 = performance.now();
        (function tick(now) {
            const p = Math.min(1, (now - t0) / ms);
            const e = 1 - Math.pow(1 - p, 3);
            cb([from[0] + (to[0] - from[0]) * e, from[1] + (to[1] - from[1]) * e]);
            if (p < 1) requestAnimationFrame(tick);
        })(performance.now());
    }

    /* ── DOM refs ── */
    const lastUpdateEl = document.getElementById('lastUpdate');
    const liveDotEl    = document.getElementById('liveDot');
    const livePillEl   = document.getElementById('livePill');
    const liveLabelEl  = document.getElementById('liveLabel');

    /* ── Helpers DOM ── */
    function setOnline(on) {
        liveDotEl?.classList.toggle('offline', !on);
        livePillEl?.classList.toggle('offline', !on);
        if (liveLabelEl) liveLabelEl.textContent = on ? 'En direct'
            : (currentStatus === 'livrée' ? 'Terminé' : 'Hors ligne');
    }

    const STEP_ICOS = ['🕐','📦','🚴','🏠'];
    function updateProgress(step) {
        const fill = document.getElementById('uiProgFill');
        if (fill) fill.style.width = (step >= 0 ? Math.min((step / 3) * 100, 100) : 0) + '%';
        for (let i = 0; i < 4; i++) {
            const dot = document.getElementById('uiDot' + i);
            const lbl = document.getElementById('uiLbl' + i);
            if (!dot || !lbl) continue;
            dot.className = 'prog-dot' + (i < step ? ' done' : i === step ? ' current' : '');
            dot.textContent = i < step ? '✓' : STEP_ICOS[i];
            lbl.className  = 'prog-lbl' + (i === step ? ' active' : i < step ? ' done-lbl' : '');
        }
    }

    function updateBadge(ico, label, badge) {
        const bTop  = document.getElementById('uiBadgeTop');
        const bInfo = document.getElementById('uiBadgeInfo');
        if (bTop)  bTop.textContent = ico + ' ' + label;
        if (bInfo) { bInfo.textContent = ico + ' ' + label; bInfo.className = 'badge ' + badge; }
    }

    function getInitials(name) {
        const p = name.trim().split(/\s+/);
        return ((p[0]?.[0] ?? '').toUpperCase() + (p[1]?.[0] ?? p[0]?.[1] ?? '').toUpperCase());
    }

    function updateLivreurCard(livreur, d) {
        const wrap = document.getElementById('uiLivreurWrap');
        if (!wrap) return;
        if (!livreur) return;

        lname = livreur.name;
        const ini = getInitials(livreur.name);
        const statusTxt = d.is_delivered ? '✅ Terminé' : d.is_ongoing ? '🟢 En route' : '🟡 Assigné';

        wrap.innerHTML = `
        <div class="livreur-card">
            <div class="lc-header">
                <span style="font-size:16px">🛵</span>
                <span class="lc-title">Votre livreur</span>
            </div>
            <div class="lc-body">
                <div class="lc-av">${ini}</div>
                <div style="flex:1;min-width:0">
                    <div class="lc-name">${livreur.name}</div>
                    <div class="lc-info">
                        ${livreur.phone ? `<a href="tel:${livreur.phone}" style="color:var(--orange);font-weight:700;text-decoration:none">📞 ${livreur.phone}</a>` : ''}
                        <span class="lc-status">${statusTxt}</span>
                    </div>
                </div>
                ${DEV_FEE > 0 && !IS_COMPANY_DRIVER ? `
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.4px">Livraison</div>
                    <div style="font-size:16px;font-weight:800;color:var(--orange);font-family:var(--mono)">${Number(DEV_FEE).toLocaleString('fr-FR')}</div>
                    <div style="font-size:10px;color:var(--muted)">${DEVISE}</div>
                </div>` : ''}
            </div>
        </div>`;

        /* Overlay flottant sur la carte */
        const ov = document.getElementById('livreurOverlay');
        if (ov) {
            const n = ov.querySelector('.mlo-name');
            const a = ov.querySelector('.mlo-av');
            const s = ov.querySelector('.mlo-status');
            if (n) n.textContent = livreur.name;
            if (a) a.textContent = ini;
            if (s) s.innerHTML = d.is_ongoing
                ? `<span class="mlo-dot"></span> En route vers vous`
                : d.is_delivered ? '✅ Livraison terminée' : '🚴 Prépare la livraison';
        } else {
            /* Créer l'overlay si le livreur vient d'être assigné */
            const mapPos = document.querySelector('#gpsMap')?.parentElement;
            if (mapPos) {
                const el = document.createElement('div');
                el.className = 'map-livreur-overlay'; el.id = 'livreurOverlay';
                el.innerHTML = `<div class="mlo-av">${ini}</div><div>
                    <div class="mlo-name">${livreur.name}</div>
                    <div class="mlo-status"><span class="mlo-dot"></span> En route vers vous</div>
                </div>`;
                mapPos.appendChild(el);
            }
        }
    }

    function updateStateBanner(d) {
        const wrap = document.getElementById('uiStateBanner');
        if (!wrap) return;
        /* Supprimer les banners ajoutés par JS lors des rafraîchissements précédents */
        wrap.querySelectorAll('.js-banner').forEach(el => el.remove());

        if (d.is_delivered) {
            const b = document.createElement('div');
            b.className = 'state-banner delivered js-banner';
            b.innerHTML = `<span class="sb-ico">🎉</span>
                <div class="sb-title" style="color:var(--green-dk)">Commande livrée !</div>
                <div class="sb-sub" style="color:#047857">Votre colis a bien été remis. Merci de votre confiance.</div>
                ${REVIEW_URL ? `<a href="${REVIEW_URL}" style="display:inline-flex;align-items:center;gap:6px;margin-top:14px;padding:10px 22px;background:var(--orange);color:#fff;border-radius:30px;font-size:13px;font-weight:700;text-decoration:none;box-shadow:0 4px 14px rgba(240,106,15,.35)">⭐ Laisser un avis</a>` : ''}`;
            wrap.insertBefore(b, wrap.firstChild);

        } else if (d.is_cancelled && !wrap.querySelector('.state-banner.cancelled')) {
            const b = document.createElement('div');
            b.className = 'state-banner cancelled js-banner';
            b.innerHTML = `<span class="sb-ico">❌</span>
                <div class="sb-title" style="color:#991b1b">Commande annulée</div>
                <div class="sb-sub" style="color:#b91c1c">Cette commande a été annulée. Aucun suivi disponible.</div>`;
            wrap.insertBefore(b, wrap.firstChild);

        } else if (!d.is_cancelled && d.step <= 1) {
            const msgs = {
                0: 'Votre commande est en attente de confirmation par la boutique.',
                1: 'Votre commande est confirmée. Un livreur va être assigné.',
            };
            const b = document.createElement('div');
            b.className = 'state-banner waiting js-banner';
            b.innerHTML = `<span class="sb-ico">${d.status_ico}</span>
                <div class="sb-title" style="color:var(--orange-dk)">${d.status_label}</div>
                <div class="sb-sub" style="color:#9a3412">${msgs[d.step] ?? ''}</div>`;
            const mapHero = wrap.querySelector('.map-hero');
            wrap.insertBefore(b, mapHero ?? wrap.firstChild);
        }
    }

    /* ── Rafraîchissement unifié ── */
    async function refreshAll() {
        try {
            const r = await fetch(DATA_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!r.ok) { setOnline(false); return; }
            const d = await r.json();

            /* Changement de statut → mise à jour UI complète */
            if (d.status !== currentStatus) {
                currentStatus = d.status;
                updateBadge(d.status_ico, d.status_label, d.status_badge);
                updateProgress(d.step);
                updateStateBanner(d);
                if (d.livreur) updateLivreurCard(d.livreur, d);

                if (d.is_delivered || d.is_cancelled) {
                    clearInterval(pollId); pollId = null;
                    setOnline(false);
                    trail.setStyle({ dashArray: null, opacity: 1 });
                    if (lastUpdateEl) lastUpdateEl.textContent = d.is_delivered ? '✅ Livraison terminée' : '❌ Annulée';
                    if (d.is_delivered && d.lat != null && d.lng != null) {
                        L.circleMarker([parseFloat(d.lat), parseFloat(d.lng)], {
                            radius:9, color:'#fff', weight:2.5, fillColor:'#10b981', fillOpacity:1
                        }).addTo(map).bindTooltip('Arrivée ✅', { permanent:true, direction:'top' });
                    }
                    return;
                }
            } else if (d.livreur && d.livreur.name !== lname) {
                /* Livreur nouvellement assigné sans changement de statut */
                updateLivreurCard(d.livreur, d);
            }

            /* Mise à jour GPS */
            if (d.lat != null && d.lng != null) {
                const nLat  = parseFloat(d.lat);
                const nLng  = parseFloat(d.lng);
                const delta = Math.abs(nLat - lat) + Math.abs(nLng - lng);

                if (!gpsFound) {
                    gpsFound = true;
                    marker.setIcon(makeMotoIcon(false));
                    marker.setPopupContent(buildPopup());
                    halo.setStyle({ fillOpacity:.08, radius:65, dashArray:null });
                    startDot = L.circleMarker([nLat,nLng], {
                        radius:7, color:'#fff', weight:2.5, fillColor:'#f06a0f', fillOpacity:1
                    }).addTo(map).bindTooltip('Départ', { permanent:false, direction:'top' });
                    map.setView([nLat, nLng], 16, { animate:true });
                }

                if (delta > 0.00013) {
                    const from = [lat, lng];
                    animateTo(from, [nLat, nLng], pt => {
                        marker.setLatLng(pt);
                        halo.setLatLng(pt);
                    });
                    trail.addLatLng([nLat, nLng]);
                    trailShadow.addLatLng([nLat, nLng]);
                    if (delta > 0.0004) map.panTo([nLat, nLng], { animate:true, duration:.9 });
                    lat = nLat; lng = nLng;
                }
            }

            /* Indicateur de fraîcheur */
            if (d.updated) {
                const dt    = new Date(d.updated);
                const diffS = Math.round((Date.now() - dt.getTime()) / 1000);
                setOnline(diffS < 45);
                if (lastUpdateEl) lastUpdateEl.textContent =
                    diffS < 60 ? `Mis à jour il y a ${diffS}s`
                               : dt.toLocaleTimeString('fr-FR', { hour:'2-digit', minute:'2-digit' });
            } else if (d.is_ongoing) {
                /* Livreur a commencé mais n'a pas encore envoyé sa position GPS — ne pas marquer hors ligne */
                liveDotEl?.classList.remove('offline');
                livePillEl?.classList.remove('offline');
                if (liveLabelEl) liveLabelEl.textContent = 'En route…';
                if (lastUpdateEl) lastUpdateEl.textContent = 'GPS en chargement…';
            } else {
                setOnline(false);
                if (lastUpdateEl) lastUpdateEl.textContent = 'En attente du signal…';
            }

        } catch (e) { setOnline(false); }
    }

    /* ── Démarrage ── */
    if (IS_DELIVERED) {
        setOnline(false);
        if (lastUpdateEl) lastUpdateEl.textContent = '✅ Livraison terminée';
        trail.setStyle({ dashArray: null });
    } else if (!IS_CANCELLED) {
        pollId = setInterval(refreshAll, 5000);
        refreshAll();
    }

})();
</script>
@endpush
