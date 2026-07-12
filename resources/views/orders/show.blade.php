{{-- resources/views/orders/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Suivi · Commande #' . str_pad($order->id, 5, '0', STR_PAD_LEFT))
@php $groupOrders ??= collect([$order]); @endphp
@php $bodyClass = 'is-dashboard'; @endphp



@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=block" rel="stylesheet">
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
    .progress-card { padding: 16px; }

    /* Header carte : réduit pour tenir sur petit écran */
    .map-header { padding: 10px 14px; gap: 8px; flex-wrap: wrap; }
    .map-title { font-size: 12.5px; }
    .last-update-txt { font-size: 10.5px; }
    .live-pill { padding: 2px 8px; font-size: 10.5px; }

    /* Overlay livreur : en bas à gauche sur mobile pour ne pas cacher l'attribution Leaflet */
    .map-livreur-overlay {
        bottom: 10px; left: 12px; transform: none;
        padding: 6px 12px 6px 8px;
        max-width: calc(100% - 24px);
        white-space: normal;
    }
    .mlo-name { font-size: 12px; }
    .mlo-av   { width: 28px; height: 28px; font-size: 12px; }
}

/* Bootstrap impose max-width:100%;height:auto sur toutes les <img> — ça casse les tuiles Leaflet */
.leaflet-container img { max-width: none !important; height: auto !important; }
.leaflet-tile          { max-width: none !important; }

/* ── Partage de position client ── */
.share-loc-strip {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 12px 20px;
    border-top: 1px solid var(--border);
    background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);
}
.share-loc-info  { display: flex; align-items: center; gap: 10px; min-width: 0; }
.share-loc-text  { min-width: 0; }
.share-loc-title { font-size: 13px; font-weight: 700; color: var(--text); }
.share-loc-sub   { font-size: 11px; color: var(--muted); margin-top: 1px; display:flex; align-items:center; gap:4px; }
.share-loc-btn {
    flex-shrink: 0;
    padding: 8px 18px; border-radius: 30px; border: none; cursor: pointer;
    background: var(--blue); color: #fff;
    font-size: 13px; font-weight: 700; font-family: var(--font);
    display: inline-flex; align-items: center; gap: 6px;
    transition: background .2s, transform .15s; white-space: nowrap;
    box-shadow: 0 2px 8px rgba(59,130,246,.35);
}
.share-loc-btn:hover  { opacity: .9; transform: scale(1.03); }
.share-loc-btn.active { background: var(--green); box-shadow: 0 2px 8px rgba(16,185,129,.35); }
@media (max-width: 600px) {
    .share-loc-strip  { padding: 10px 14px; gap: 8px; }
    .share-loc-title  { font-size: 12px; }
    .share-loc-sub    { font-size: 10.5px; }
    .share-loc-btn    { padding: 7px 14px; font-size: 12px; }
}

/* ── MODAL INSCRIPTION (commande invité) ── */
.signup-overlay {
    display: none; position: fixed; inset: 0; z-index: 3000;
    background: rgba(15,23,42,.55); backdrop-filter: blur(3px);
    align-items: flex-end; justify-content: center;
}
.signup-overlay.open { display: flex; }
@keyframes signup-slide-up { from { transform: translateY(24px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.signup-modal {
    position: relative; width: 100%; max-width: 420px;
    background: var(--surface); border-radius: 20px 20px 0 0;
    padding: 28px 24px 30px; box-shadow: var(--shadow-lg);
    animation: signup-slide-up .28s ease-out;
    text-align: center;
}
@media (min-width: 640px) {
    .signup-overlay { align-items: center; }
    .signup-modal   { border-radius: var(--r); }
}
.signup-close {
    position: absolute; top: 12px; right: 12px; width: 30px; height: 30px;
    border-radius: 50%; border: none; background: var(--bg); color: var(--muted);
    font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center;
}
.signup-close:hover { background: var(--border); color: var(--text); }
.signup-ico   { font-size: 40px; margin-bottom: 6px; }
.signup-title { font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 8px; }
.signup-text  { font-size: 13.5px; color: var(--text-2); line-height: 1.55; margin-bottom: 20px; }
.signup-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 13px; border-radius: 50px; font-size: 14px; font-weight: 700;
    text-decoration: none; margin-bottom: 10px; transition: all .15s;
}
.signup-btn-google  { background: var(--surface); border: 1.5px solid var(--border); color: var(--text); }
.signup-btn-google:hover { background: var(--bg); }
.signup-btn-primary { background: var(--orange); color: #fff; box-shadow: 0 4px 14px rgba(240,106,15,.35); }
.signup-btn-primary:hover { background: var(--orange-dk); transform: translateY(-1px); }
.signup-link  { display: block; font-size: 13px; color: var(--text-2); font-weight: 600; text-decoration: underline; margin-bottom: 14px; }
.signup-later { display: block; width: 100%; background: none; border: none; color: var(--muted); font-size: 12.5px; font-weight: 600; cursor: pointer; padding: 4px; }
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
        <a href="{{ auth()->check() ? route('client.orders.index') : url('/') }}" class="btn-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            {{ auth()->check() ? 'Mes commandes' : 'Accueil' }}
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

        {{-- Strip partage de position client --}}
        @if(!$isDelivered && !$isCancelled)
        @php $hadPosition = !is_null($order->client_lat) && !is_null($order->client_lng); @endphp
        <div class="share-loc-strip" id="shareLocStrip">
            <div class="share-loc-info">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="{{ $hadPosition ? '#10b981' : '#3b82f6' }}" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="10" r="3"/><path d="M12 2C8.134 2 5 5.134 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.866-3.134-7-7-7z"/></svg>
                <div class="share-loc-text">
                    <div class="share-loc-title">Partager ma position</div>
                    @if($hadPosition)
                    <div class="share-loc-sub" id="shareSub" style="color:#10b981;display:flex;align-items:center;gap:4px">
                        <span style="width:6px;height:6px;border-radius:50%;background:#10b981;flex-shrink:0;display:inline-block"></span>
                        Votre dernière position est encore visible par votre livreur
                    </div>
                    @else
                    <div class="share-loc-sub" id="shareSub">Guidez votre livreur directement chez vous</div>
                    @endif
                </div>
            </div>
            <button class="share-loc-btn" id="shareLocBtn" type="button">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                Partager
            </button>
        </div>
        @endif
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

@if(!auth()->check() && is_null($order->user_id))
{{-- ── Modal invitation à créer un compte (commande passée sans compte) ── --}}
@php
    $suiviUrl    = route('suivi.show', $order);
    $registerUrl = route('register', ['redirect' => $suiviUrl, 'order_id' => $order->id, 'role' => 'client']);
    $loginUrl    = route('login',    ['redirect' => $suiviUrl, 'order_id' => $order->id]);
    $googleUrl   = route('google.redirect', ['redirect' => $suiviUrl, 'order_id' => $order->id]);
@endphp
<div class="signup-overlay" id="signupOverlay">
    <div class="signup-modal">
        <button class="signup-close" onclick="dismissSignupModal()">✕</button>
        <div class="signup-ico">🎉</div>
        <h2 class="signup-title">Suivez toutes vos commandes</h2>
        <p class="signup-text">Créez votre compte pour retrouver cette commande, discuter avec le vendeur et commander plus vite la prochaine fois.</p>
        <a href="{{ $googleUrl }}" class="signup-btn signup-btn-google">
            <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.4 29.3 35 24 35c-6.1 0-11-4.9-11-11s4.9-11 11-11c2.8 0 5.3 1 7.3 2.8l6-6C33.7 6.5 29.1 4.5 24 4.5 13.2 4.5 4.5 13.2 4.5 24S13.2 43.5 24 43.5 43.5 34.8 43.5 24c0-1.2-.1-2.4-.4-3.5z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.6 15.9 18.9 13 24 13c2.8 0 5.3 1 7.3 2.8l6-6C33.7 6.5 29.1 4.5 24 4.5c-7.5 0-14 4.2-17.3 10.2z"/><path fill="#4CAF50" d="M24 43.5c5 0 9.6-1.9 13-5.1l-6-4.9C29.1 35.4 26.7 36.2 24 36.2c-5.3 0-9.7-3.4-11.3-8.1l-6.5 5C9.9 39.3 16.4 43.5 24 43.5z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.1-4.1 5.4l6 4.9c-.4.4 6.3-4.6 6.3-14.3 0-1.2-.1-2.4-.4-3.5z"/></svg>
            Continuer avec Google
        </a>
        <a href="{{ $registerUrl }}" class="signup-btn signup-btn-primary">✨ Créer mon compte</a>
        <a href="{{ $loginUrl }}" class="signup-link">J'ai déjà un compte</a>
        <button class="signup-later" onclick="dismissSignupModal()">Plus tard</button>
    </div>
</div>
@endif

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
@keyframes gps-spin { to { transform: rotate(360deg); } }
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
    const IS_ONGOING        = {{ $isOngoing ? 'true' : 'false' }}; // Phase 2 uniquement

    /* ── État mutable ── */
    let lat           = {{ $lat0 }};
    let lng           = {{ $lng0 }};
    let gpsFound      = HAS_GPS && IS_ONGOING; // GPS visible seulement en Phase 2
    let currentStatus = {!! json_encode($order->status) !!};
    let lname         = {!! json_encode($gpsDriverName ?? '') !!};
    let pollId        = null;

    const MAPBOX_TOKEN    = '{{ config('services.mapbox.token') }}';

    /* ── Carte Leaflet ── */
    const map = L.map('gpsMap', { zoomControl: true, attributionControl: true })
        .setView([lat, lng], HAS_GPS ? 16 : 13);

    const TILE_PROVIDERS = [
        { url:`https://api.mapbox.com/styles/v1/mapbox/streets-v12/tiles/256/{z}/{x}/{y}?access_token=${MAPBOX_TOKEN}`,
          opts:{ attribution:'© <a href="https://www.mapbox.com/">Mapbox</a>', maxZoom:19, crossOrigin:'' } },
        { url:'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
          opts:{ attribution:'© OpenStreetMap', maxZoom:19, subdomains:'abc', crossOrigin:'' } },
        { url:'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png',
          opts:{ attribution:'© CartoDB', subdomains:'abcd', maxZoom:20, crossOrigin:'' } },
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
    /* Recalcul taille carte quand l'onglet redevient actif (retour arrière-plan mobile) */
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') map.invalidateSize();
    });

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
    // Le marker livreur n'est ajouté à la carte qu'en Phase 2 (en_livraison)
    const marker = L.marker([lat, lng], { icon: makeMotoIcon(!gpsFound) });
    if (IS_ONGOING) marker.addTo(map);

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
    });
    if (IS_ONGOING) halo.addTo(map); // halo visible seulement en Phase 2

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

            /* Mise à jour GPS — Phase 2 uniquement (livreur en route vers le client) */
            if (d.lat != null && d.lng != null && d.is_ongoing) {
                const nLat  = parseFloat(d.lat);
                const nLng  = parseFloat(d.lng);
                const delta = Math.abs(nLat - lat) + Math.abs(nLng - lng);

                if (!gpsFound) {
                    gpsFound = true;
                    if (!map.hasLayer(marker)) marker.addTo(map);
                    if (!map.hasLayer(halo))   halo.addTo(map);
                    marker.setIcon(makeMotoIcon(false));
                    marker.setPopupContent(buildPopup());
                    halo.setStyle({ fillOpacity:.08, radius:65, dashArray:null });
                    /* Centrer sur les 2 points si client a partagé, sinon sur le livreur */
                    if (clientLat !== null) {
                        map.fitBounds([[nLat, nLng], [clientLat, clientLng]], { padding:[60,60], maxZoom:16, animate:true });
                        scheduleRouteUpdate();
                    } else {
                        map.setView([nLat, nLng], 16, { animate:true });
                    }
                }

                if (delta > 0.00013) {
                    animateTo([lat, lng], [nLat, nLng], pt => {
                        marker.setLatLng(pt);
                        halo.setLatLng(pt);
                    });
                    lat = nLat; lng = nLng;

                    /* Ajuster la vue pour garder livreur + client visibles en même temps */
                    if (clientLat !== null) {
                        map.fitBounds(
                            [[nLat, nLng], [clientLat, clientLng]],
                            { padding: [60, 60], maxZoom: 16, animate: true, duration: 0.6 }
                        );
                        /* Route complète livreur → client (recalcul si changement de position) */
                        scheduleRouteUpdate();
                    } else {
                        /* Pas de position client : on suit juste le livreur */
                        if (delta > 0.0004) map.panTo([nLat, nLng], { animate:true, duration:.9 });
                    }
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

    /* ── Hint mobile "2 doigts pour défiler" ── */
    if (window.innerWidth <= 640) {
        const _hintWrap = document.createElement('div');
        _hintWrap.style.cssText = 'position:absolute;inset:0;z-index:600;pointer-events:none;display:flex;align-items:center;justify-content:center';
        const _hintBubble = document.createElement('div');
        _hintBubble.style.cssText = 'background:rgba(0,0,0,.62);color:#fff;padding:8px 18px;border-radius:20px;font-size:12px;font-weight:600;font-family:var(--font);opacity:0;transition:opacity .3s;white-space:nowrap;pointer-events:none';
        _hintBubble.textContent = '☝️ 2 doigts pour défiler la page';
        _hintWrap.appendChild(_hintBubble);
        document.getElementById('gpsMap').parentElement.appendChild(_hintWrap);
        let _hintTimer = null;
        map.on('dragstart', () => {
            _hintBubble.style.opacity = '1';
            clearTimeout(_hintTimer);
            _hintTimer = setTimeout(() => { _hintBubble.style.opacity = '0'; }, 1800);
        });
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

    /* ══════════════════════════════════════════════════════════════════
     *  PARTAGE DE POSITION CLIENT
     * ══════════════════════════════════════════════════════════════════ */
    const CLIENT_LOC_URL  = '{{ route('suivi.client-location', $order) }}';
    const CSRF_TOKEN      = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let clientLat    = {{ $order->client_lat ?? 'null' }};
    let clientLng    = {{ $order->client_lng ?? 'null' }};
    let clientMarker = null;
    let routeLine    = null;
    let routeShadow  = null;
    let watchId      = null;
    let lastSentTs   = 0;
    let shareActive  = false;
    let routeTimer    = null;
    let routePoints   = [];
    let routeRemaining= [];
    let routeDestKey  = '';
    let routeIsFetching = false;
    let routeOffStreak  = 0;

    /* ── Icône position client (bleu) ── */
    function makeClientIcon() {
        return L.divIcon({
            html: `<div style="width:20px;height:20px;border-radius:50%;
                        background:#3b82f6;border:2.5px solid #fff;
                        box-shadow:0 0 0 3px rgba(59,130,246,.3),0 2px 8px rgba(59,130,246,.45)"></div>`,
            iconSize:[20,20], iconAnchor:[10,10], className:''
        });
    }

    /* ── Envoie la position au serveur (throttle 8 s) ── */
    async function sendClientLocation(clat, clng) {
        const now = Date.now();
        if (now - lastSentTs < 8000) return;
        lastSentTs = now;
        try {
            await fetch(CLIENT_LOC_URL, {
                method: 'POST',
                headers: {
                    'Content-Type':    'application/json',
                    'X-CSRF-TOKEN':    CSRF_TOKEN,
                    'X-Requested-With':'XMLHttpRequest'
                },
                body: JSON.stringify({ lat: clat, lng: clng })
            });
        } catch {}
    }

    /* ── Trace OSRM du livreur vers le client ── */
    async function fetchRoute(dLat, dLng, cLat, cLng) {
        try {
            const url = `https://api.mapbox.com/directions/v5/mapbox/driving/${dLng},${dLat};${cLng},${cLat}?geometries=geojson&overview=full&access_token=${MAPBOX_TOKEN}`;
            const res = await fetch(url);
            const data = await res.json();
            if (!data.routes?.length) return null;
            return data.routes[0].geometry.coordinates.map(([lo, la]) => [la, lo]);
        } catch { return null; }
    }

    /* ── Helpers consommation de route ── */
    function closestRouteIdx(eLat, eLng, pts) {
        let minD = Infinity, minI = 0;
        for (let i = 0; i < pts.length; i++) {
            const d = (pts[i][0]-eLat)**2 + (pts[i][1]-eLng)**2;
            if (d < minD) { minD = d; minI = i; }
        }
        return minI;
    }

    function distToRoutePoints(eLat, eLng, pts) {
        let minD = Infinity;
        for (const [pLat, pLng] of pts) {
            const d = Math.sqrt((pLat-eLat)**2 + (pLng-eLng)**2);
            if (d < minD) minD = d;
        }
        return minD;
    }

    function redrawRoute() {
        if (routeRemaining.length < 2) {
            if (routeLine)   { map.removeLayer(routeLine);   routeLine   = null; }
            if (routeShadow) { map.removeLayer(routeShadow); routeShadow = null; }
            return;
        }
        if (routeShadow) routeShadow.setLatLngs(routeRemaining);
        else routeShadow = L.polyline(routeRemaining, { color:'rgba(0,0,0,.12)', weight:7, lineCap:'round', lineJoin:'round' }).addTo(map);
        if (routeLine)  routeLine.setLatLngs(routeRemaining);
        else routeLine  = L.polyline(routeRemaining, { color:'#3b82f6', weight:3.5, opacity:.82, lineCap:'round', lineJoin:'round' }).addTo(map);
        routeShadow.bringToBack(); routeLine.bringToBack();
        if (trailShadow) trailShadow.bringToFront();
        if (trail) trail.bringToFront();
    }

    /* GTA 5 : mange la route au fur et à mesure que le livreur avance */
    function consumeOSRMRoute(dLat, dLng) {
        if (routeRemaining.length < 2) return;
        const idx = closestRouteIdx(dLat, dLng, routeRemaining);
        if (idx > 0) routeRemaining = routeRemaining.slice(idx);
        routeRemaining[0] = [dLat, dLng];
        redrawRoute();
    }

    async function planOSRMRoute(dLat, dLng, cLat, cLng) {
        if (routeIsFetching) return;
        routeIsFetching = true;
        const pts = await fetchRoute(dLat, dLng, cLat, cLng);
        if (pts) {
            routePoints = pts; routeRemaining = [...pts];
            routeDestKey = `${cLat},${cLng}`; routeOffStreak = 0;
            redrawRoute();
        }
        routeIsFetching = false;
    }

    async function updateRoute() {
        if (!gpsFound || clientLat === null) {
            if (routeLine)   { map.removeLayer(routeLine);   routeLine   = null; }
            if (routeShadow) { map.removeLayer(routeShadow); routeShadow = null; }
            routePoints = []; routeRemaining = []; routeDestKey = '';
            return;
        }
        await planOSRMRoute(lat, lng, clientLat, clientLng);
    }

    function scheduleRouteUpdate() {
        clearTimeout(routeTimer);
        routeTimer = setTimeout(updateRoute, 1800);
    }

    /* ── Marker client sur la carte ── */
    function updateClientMarker(clat, clng) {
        if (clientMarker) {
            clientMarker.setLatLng([clat, clng]);
        } else {
            clientMarker = L.marker([clat, clng], { icon: makeClientIcon() })
                .addTo(map)
                .bindPopup('<div style="font-family:system-ui;padding:2px 4px"><b style="font-size:13px">Votre position</b><br><span style="font-size:11px;color:#94a3b8">Partagée en direct</span></div>');
        }
    }

    /* ── UI bouton ── */
    const shareBtn = document.getElementById('shareLocBtn');
    const shareSubEl = document.getElementById('shareSub');

    function setShareUI(active) {
        shareActive = active;
        if (!shareBtn) return;
        shareBtn.innerHTML = active
            ? `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Arrêter`
            : `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> Partager`;
        shareBtn.classList.toggle('active', active);

        /* Icône pin : verte si position connue, bleue sinon */
        const pinIcon = document.querySelector('#shareLocStrip svg');
        if (pinIcon) pinIcon.setAttribute('stroke', active || clientLat !== null ? '#10b981' : '#3b82f6');

        if (shareSubEl) {
            if (active) {
                shareSubEl.style.color = '#10b981';
                shareSubEl.innerHTML = `<span style="width:6px;height:6px;border-radius:50%;background:#10b981;flex-shrink:0;display:inline-block;animation:gps-pulse 1.8s ease-in-out infinite"></span> Position partagée · votre livreur vous voit`;
            } else if (clientLat !== null) {
                shareSubEl.style.color = '#10b981';
                shareSubEl.innerHTML = `<span style="width:6px;height:6px;border-radius:50%;background:#10b981;flex-shrink:0;display:inline-block"></span> Votre dernière position est encore visible par votre livreur`;
            } else {
                shareSubEl.style.color = '';
                shareSubEl.textContent = 'Guidez votre livreur directement chez vous';
            }
        }
    }

    /* ── Démarrage partage ── */
    function startSharing() {
        /* Géolocalisation indisponible sur cet appareil/navigateur */
        if (!('geolocation' in navigator)) {
            if (shareSubEl) {
                shareSubEl.style.color = 'var(--red)';
                shareSubEl.textContent = '❌ Géolocalisation non disponible sur cet appareil';
            }
            return;
        }

        /* État "en cours de localisation" pendant que le GPS se fixe */
        if (shareBtn) {
            shareBtn.disabled = true;
            shareBtn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="animation:gps-spin .8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Localisation…`;
        }
        if (shareSubEl) {
            shareSubEl.style.color = 'var(--muted)';
            shareSubEl.textContent = '🔍 Recherche du signal GPS…';
        }

        let _firstFix = true;
        watchId = navigator.geolocation.watchPosition(
            pos => {
                const { latitude: clat, longitude: clng, accuracy } = pos.coords;
                clientLat = clat; clientLng = clng;
                if (_firstFix) {
                    _firstFix = false;
                    if (shareBtn) shareBtn.disabled = false;
                    setShareUI(true);
                }
                /* Avertir si précision mauvaise (PC sans GPS) */
                if (shareSubEl) {
                    if (accuracy > 500) {
                        shareSubEl.style.color = '#f59e0b';
                        shareSubEl.innerHTML = `<span style="width:6px;height:6px;border-radius:50%;background:#f59e0b;flex-shrink:0;display:inline-block"></span> Précision faible (${Math.round(accuracy)}m) — utilisez un téléphone pour un meilleur résultat`;
                    } else {
                        shareSubEl.style.color = '#10b981';
                        shareSubEl.innerHTML = `<span style="width:6px;height:6px;border-radius:50%;background:#10b981;flex-shrink:0;display:inline-block;animation:gps-pulse 1.8s ease-in-out infinite"></span> Position partagée · précision ~${Math.round(accuracy)}m`;
                    }
                }
                sendClientLocation(clat, clng);
                updateClientMarker(clat, clng);
                /* Si le livreur est déjà visible → ajuster la vue pour montrer les 2 */
                if (gpsFound) {
                    map.fitBounds(
                        [[lat, lng], [clat, clng]],
                        { padding:[60,60], maxZoom:16, animate:true, duration:0.6 }
                    );
                }
                scheduleRouteUpdate();
            },
            err => {
                if (shareBtn) shareBtn.disabled = false;
                stopSharing(false);
                let msg;
                if (err.code === 1) {
                    /* Permission refusée : donner des instructions claires selon l'OS */
                    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
                    msg = isIOS
                        ? '❌ Bloqué — Réglages → Confidentialité → Service de localisation → Safari → Autoriser'
                        : '❌ Bloqué — Paramètres du navigateur → Autorisations → Localisation → Autoriser ce site';
                } else {
                    msg = '❌ Signal GPS indisponible — réessayez en extérieur';
                }
                if (shareSubEl) {
                    shareSubEl.style.color = 'var(--red)';
                    shareSubEl.textContent = msg;
                }
            },
            { enableHighAccuracy: true, maximumAge: 10000, timeout: 15000 }
        );
    }

    /* ── Arrêt partage ── */
    function stopSharing(clearMarker = true) {
        if (watchId !== null) { navigator.geolocation.clearWatch(watchId); watchId = null; }
        clearTimeout(routeTimer); routeTimer = null;
        setShareUI(false);
        if (clearMarker && clientMarker) { map.removeLayer(clientMarker); clientMarker = null; }
        if (clearMarker) { clientLat = null; clientLng = null; }
        if (routeLine)   { map.removeLayer(routeLine);   routeLine   = null; }
        if (routeShadow) { map.removeLayer(routeShadow); routeShadow = null; }
        routePoints = []; routeRemaining = []; routeDestKey = '';
    }

    if (shareBtn && !IS_DELIVERED && !IS_CANCELLED) {
        shareBtn.addEventListener('click', () => {
            if (shareActive) stopSharing(true); else startSharing();
        });
    }

    /* ── Restaurer le marqueur client si position déjà en base ── */
    if (clientLat !== null && clientLng !== null && !IS_DELIVERED && !IS_CANCELLED) {
        updateClientMarker(clientLat, clientLng);
        if (gpsFound) scheduleRouteUpdate();
    }

})();

/* ── Modal invitation à créer un compte (commande invité) ── */
(function () {
    const overlay = document.getElementById('signupOverlay');
    if (!overlay) return;

    const key = 'shopio_hide_signup_modal_{{ $order->id }}';
    if (localStorage.getItem(key)) return;

    setTimeout(() => overlay.classList.add('open'), 1200);

    window.dismissSignupModal = function () {
        overlay.classList.remove('open');
        localStorage.setItem(key, '1');
    };

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) window.dismissSignupModal();
    });
})();
</script>
@endpush
