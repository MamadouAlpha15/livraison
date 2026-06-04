{{--
  orders/nav.blade.php
  Carte de navigation intelligente pour le livreur de l'entreprise.
  Phase 1 (avant en_livraison) : trajet driver → boutique/vendeur
  Phase 2 (en_livraison)       : trajet driver → client
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Navigation · Commande #{{ $order->id }}</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'Segoe UI',system-ui,sans-serif;background:#0f172a;color:#f8fafc}

/* ── TOPBAR ── */
.nav-bar{
    position:fixed;top:0;left:0;right:0;z-index:1000;
    background:linear-gradient(135deg,#1e1b4b,#312e81);
    border-bottom:1px solid rgba(99,102,241,.3);
    padding:12px 16px;
    display:flex;align-items:center;justify-content:space-between;gap:12px;
}
.nav-order{font-size:13px;font-weight:800;color:#fff;letter-spacing:-.3px}
.nav-order span{color:#a5b4fc}

/* ── PHASE BADGE ── */
.phase-badge{
    display:inline-flex;align-items:center;gap:7px;
    padding:6px 14px;border-radius:30px;font-size:12px;font-weight:700;
    letter-spacing:.3px;white-space:nowrap;
}
.phase-1{background:rgba(245,158,11,.18);border:1px solid rgba(245,158,11,.4);color:#fcd34d}
.phase-2{background:rgba(16,185,129,.18);border:1px solid rgba(16,185,129,.4);color:#6ee7b7}
.phase-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;animation:pulse 1.6s ease-in-out infinite}
.phase-1 .phase-dot{background:#f59e0b;box-shadow:0 0 6px #f59e0b}
.phase-2 .phase-dot{background:#10b981;box-shadow:0 0 6px #10b981}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.5)}}

/* ── MAP ── */
#navMap{position:fixed;top:56px;left:0;right:0;bottom:120px}

/* ── INFO PANEL ── */
.info-panel{
    position:fixed;bottom:0;left:0;right:0;z-index:1000;
    background:linear-gradient(180deg,#1e1b4b,#0f0f3a);
    border-top:1px solid rgba(99,102,241,.25);
    padding:14px 16px 20px;
}
.info-dest{
    display:flex;align-items:center;gap:10px;
    background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);
    border-radius:12px;padding:11px 14px;margin-bottom:10px;
}
.info-dest-ico{
    width:36px;height:36px;border-radius:10px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;font-size:18px;
}
.ico-vendor{background:rgba(245,158,11,.2)}
.ico-client{background:rgba(16,185,129,.2)}
.info-dest-txt .lbl{font-size:10px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.5px}
.info-dest-txt .val{font-size:13px;font-weight:600;color:#fff;margin-top:2px}

.panel-btns{display:flex;gap:8px;margin-bottom:10px;}
.btn-phase{
    flex:1;display:flex;align-items:center;justify-content:center;gap:7px;
    padding:13px;border-radius:12px;border:none;
    font-size:13.5px;font-weight:800;font-family:inherit;cursor:pointer;transition:all .15s;
}
.btn-phase:active{transform:scale(.97);}
.btn-phase:disabled{opacity:.5;cursor:not-allowed;}
.btn-start{background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;box-shadow:0 6px 18px rgba(245,158,11,.4);}
.btn-start:hover:not(:disabled){box-shadow:0 8px 24px rgba(245,158,11,.5);}
.btn-done{background:linear-gradient(135deg,#10b981,#059669);color:#fff;box-shadow:0 6px 18px rgba(16,185,129,.4);}
.btn-done:hover:not(:disabled){box-shadow:0 8px 24px rgba(16,185,129,.5);}
.btn-gps{
    display:flex;align-items:center;justify-content:center;gap:8px;
    width:100%;padding:11px;border-radius:12px;
    background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);
    color:rgba(255,255,255,.8);font-size:13px;font-weight:700;font-family:inherit;
    cursor:pointer;transition:opacity .15s;
}
.btn-gps:active{opacity:.8}
.nav-done-screen{
    display:none;position:fixed;inset:0;background:linear-gradient(180deg,#0f172a,#0a1220);
    z-index:9000;flex-direction:column;align-items:center;justify-content:center;gap:16px;padding:32px;text-align:center;
}
.nav-done-ico{font-size:64px;line-height:1;}
.nav-done-title{font-size:22px;font-weight:900;color:#fff;}
.nav-done-sub{font-size:14px;color:rgba(255,255,255,.5);}
.nav-done-btn{margin-top:16px;padding:14px 32px;border-radius:14px;border:none;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;font-size:15px;font-weight:800;font-family:inherit;cursor:pointer;}

/* ── LOADING ── */
.gps-loading{
    position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);
    background:rgba(15,23,42,.95);border:1px solid rgba(99,102,241,.3);
    border-radius:16px;padding:28px 32px;text-align:center;z-index:2000;
}
.gps-loading-spinner{
    width:36px;height:36px;border:3px solid rgba(99,102,241,.2);
    border-top-color:#6366f1;border-radius:50%;
    animation:spin .8s linear infinite;margin:0 auto 12px;
}
@keyframes spin{to{transform:rotate(360deg)}}
.gps-loading p{font-size:13px;color:rgba(255,255,255,.6)}

/* ── MODAL CONFIRMATION COLIS ── */
.pickup-overlay{
    display:none;position:fixed;inset:0;z-index:8000;
    background:rgba(0,0,0,.6);backdrop-filter:blur(4px);
    align-items:flex-end;justify-content:center;
}
.pickup-overlay.open{display:flex}
.pickup-modal{
    background:linear-gradient(180deg,#1e1b4b,#0f0f3a);
    border:1px solid rgba(99,102,241,.3);
    border-radius:24px 24px 0 0;
    padding:28px 24px 36px;
    width:100%;max-width:480px;
    text-align:center;
    animation:slideUp .25s ease;
}
@keyframes slideUp{from{transform:translateY(60px);opacity:0}to{transform:translateY(0);opacity:1}}
.pickup-modal-ico{font-size:52px;line-height:1;margin-bottom:14px}
.pickup-modal-title{font-size:18px;font-weight:900;color:#fff;margin-bottom:8px}
.pickup-modal-desc{font-size:13.5px;color:rgba(255,255,255,.6);line-height:1.6;margin-bottom:24px}
.pickup-modal-dest{
    display:flex;align-items:center;gap:10px;
    background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);
    border-radius:12px;padding:12px 16px;margin-bottom:24px;text-align:left;
}
.pickup-modal-dest-ico{font-size:22px;flex-shrink:0}
.pickup-modal-dest-name{font-size:13px;font-weight:800;color:#6ee7b7}
.pickup-modal-dest-addr{font-size:11.5px;color:rgba(255,255,255,.45);margin-top:2px}
.pickup-modal-btns{display:flex;gap:10px}
.btn-pickup-cancel{
    flex:1;padding:13px;border-radius:12px;border:1.5px solid rgba(255,255,255,.15);
    background:transparent;color:rgba(255,255,255,.6);font-size:13px;font-weight:700;
    font-family:inherit;cursor:pointer;
}
.btn-pickup-confirm{
    flex:2;padding:13px;border-radius:12px;border:none;
    background:linear-gradient(135deg,#10b981,#059669);color:#fff;
    font-size:14px;font-weight:800;font-family:inherit;cursor:pointer;
    box-shadow:0 6px 18px rgba(16,185,129,.4);
}
.btn-pickup-confirm:disabled{opacity:.6;cursor:not-allowed}

/* ── NO VENDOR ── */
.no-vendor-banner{
    position:fixed;top:64px;left:50%;transform:translateX(-50%);z-index:1100;
    background:linear-gradient(135deg,#92400e,#b45309);
    border:1px solid rgba(245,158,11,.4);
    border-radius:12px;padding:10px 18px;
    font-size:12px;font-weight:700;color:#fcd34d;
    white-space:nowrap;box-shadow:0 4px 20px rgba(0,0,0,.4);
    display:none;
}
</style>
</head>
<body>

@php
    $orderNum     = '#' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
    $hasVendor    = $order->vendor_lat && $order->vendor_lng;
    $hasClient    = $order->client_lat && $order->client_lng;
    $destination  = $order->delivery_destination ?? ($order->client?->address ?? 'Destination inconnue');
    $shopName     = $order->shop?->name ?? 'Boutique';
    $shopAddress  = $order->shop?->address ?? '';
    $initialPhase = $phase; // passé depuis le contrôleur
@endphp

{{-- TOPBAR --}}
<div class="nav-bar">
    <div class="nav-order">Commande <span>{{ $orderNum }}</span></div>
    <div class="phase-badge {{ $initialPhase === 1 ? 'phase-1' : 'phase-2' }}" id="phaseBadge">
        <span class="phase-dot"></span>
        <span id="phaseLabel">{{ $initialPhase === 1 ? 'Phase 1 · Ramassage' : 'Phase 2 · Livraison' }}</span>
    </div>
</div>

{{-- Banner si vendeur n'a pas partagé sa position --}}
@if($initialPhase === 1 && !$hasVendor)
<div class="no-vendor-banner" id="noVendorBanner" style="display:flex;align-items:center;gap:7px">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    Le vendeur n'a pas encore partagé sa position
</div>
@endif

{{-- CARTE --}}
<div id="navMap"></div>

{{-- PANEL BAS --}}
<div class="info-panel">
    <div class="info-dest" id="destPanel">
        <div class="info-dest-ico {{ $initialPhase === 1 ? 'ico-vendor' : 'ico-client' }}" id="destIco">
            {{ $initialPhase === 1 ? '🏪' : '📍' }}
        </div>
        <div class="info-dest-txt">
            <div class="lbl" id="destLbl">{{ $initialPhase === 1 ? 'Destination · Ramassage' : 'Destination · Livraison' }}</div>
            <div class="val" id="destVal">{{ $initialPhase === 1 ? $shopName : $destination }}</div>
            @if($initialPhase === 1 && $shopAddress)
            <div id="destAddr" style="font-size:11px;color:rgba(255,255,255,.45);margin-top:2px">📍 {{ $shopAddress }}</div>
            @endif
            @if($initialPhase === 1 && !$hasVendor)
            <div id="vendorStatusBadge" style="display:inline-flex;align-items:center;gap:5px;margin-top:5px;font-size:11px;font-weight:700;color:#fcd34d;background:rgba(245,158,11,.15);border:1px solid rgba(245,158,11,.3);padding:3px 10px;border-radius:20px">
                <span style="width:6px;height:6px;border-radius:50%;background:#f59e0b;animation:pulse 1.2s ease-in-out infinite;display:inline-block"></span>
                En attente de la position boutique…
            </div>
            @endif
        </div>
    </div>
    {{-- Boutons d'action selon la phase --}}
    <div class="panel-btns">
        <button class="btn-phase btn-start" id="btnStart"
                style="{{ $initialPhase !== 1 ? 'display:none' : '' }}"
                onclick="startDelivery()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            Colis récupéré
        </button>
        <button class="btn-phase btn-done" id="btnDone"
                style="{{ $initialPhase !== 2 ? 'display:none' : '' }}"
                onclick="markDelivered()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Marquer comme livrée
        </button>
    </div>
    <button class="btn-gps" onclick="openGoogleMaps()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8l4 4-4 4M8 12h8"/></svg>
        Ouvrir dans Google Maps
    </button>

    @if(app()->isLocal())
    <form method="POST" action="{{ route('orders.simulate.gps', $order) }}" style="margin-top:8px">
        @csrf
        <button type="submit" style="width:100%;padding:10px;border-radius:12px;border:1.5px dashed rgba(251,191,36,.5);background:rgba(251,191,36,.08);color:#fbbf24;font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;">
            🧪 [DEV] Simuler positions vendeur + client + livreur
        </button>
    </form>
    @endif
</div>

{{-- MODAL CONFIRMATION COLIS RÉCUPÉRÉ --}}
<div class="pickup-overlay" id="pickupOverlay">
    <div class="pickup-modal">
        <div class="pickup-modal-ico">📦</div>
        <div class="pickup-modal-title">Colis récupéré ?</div>
        <div class="pickup-modal-desc">En confirmant, vous commencez la livraison chez le client. La navigation va basculer vers sa destination.</div>
        <div class="pickup-modal-dest">
            <div class="pickup-modal-dest-ico">📍</div>
            <div>
                <div class="pickup-modal-dest-name">Livraison chez le client</div>
                <div class="pickup-modal-dest-addr" id="pickupDestAddr">{{ $destination }}</div>
            </div>
        </div>
        <div class="pickup-modal-btns">
            <button class="btn-pickup-cancel" onclick="closePickupModal()">Annuler</button>
            <button class="btn-pickup-confirm" id="btnPickupConfirm" onclick="confirmPickup()">
                ✅ Oui, commencer la livraison
            </button>
        </div>
    </div>
</div>

{{-- Écran de fin --}}
<div class="nav-done-screen" id="navDoneScreen">
    <div class="nav-done-ico">✅</div>
    <div class="nav-done-title">Livraison terminée !</div>
    <div class="nav-done-sub">La commande #{{ $order->id }} a été marquée comme livrée.</div>
    <div style="font-size:12px;color:rgba(255,255,255,.35);margin-top:4px" id="navDoneCountdown"></div>
    <button class="nav-done-btn" onclick="goToOrders()">Voir mes livraisons</button>
</div>

{{-- LOADING GPS --}}
<div class="gps-loading" id="gpsLoading">
    <div class="gps-loading-spinner"></div>
    <p>Localisation en cours…</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
<script>
const ORDER_ID   = {{ $order->id }};
const DATA_URL   = '{{ route('suivi.data', $order) }}';
const POS_URL    = '{{ route('orders.position.update', $order) }}';
const STATUS_URL = '{{ route('orders.driver.status', $order) }}';
const CSRF       = '{{ csrf_token() }}';
const SHOP_NAME    = @json($shopName);
const SHOP_ADDRESS = @json($shopAddress);
const DESTINATION  = @json($destination);

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
const map = L.map('navMap', { zoomControl: true, attributionControl: false })
    .setView([_initLat, _initLng], 13);
L.tileLayer(`https://api.mapbox.com/styles/v1/mapbox/streets-v12/tiles/256/{z}/{x}/{y}?access_token=${MAPBOX_TOKEN}`, { attribution:'© Mapbox', maxZoom:19 }).addTo(map);

function makeIcon(emoji, color, size = 42) {
    return L.divIcon({
        className: '',
        html: `<div style="width:${size}px;height:${size}px;border-radius:50%;background:${color};display:flex;align-items:center;justify-content:center;font-size:${Math.round(size*.46)}px;box-shadow:0 4px 18px rgba(0,0,0,.55);border:3px solid rgba(255,255,255,.95)">${emoji}</div>`,
        iconSize: [size, size], iconAnchor: [size/2, size/2],
    });
}
const driverIcon = makeIcon('🛵', '#4f46e5', 46);
const vendorIcon = makeIcon('🏪', '#d97706', 40);
const clientIcon = makeIcon('📍', '#059669', 40);

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
// ROUTAGE OSRM — vraies routes
// ─────────────────────────────────────────────
let routePoints    = [];   // Tous les waypoints calculés par OSRM
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
        color: 'rgba(0,0,0,.28)', weight: 10, lineCap: 'round', lineJoin: 'round'
    }).addTo(map);
    routeLine = L.polyline(routeRemaining, {
        color:     currentPhase === 1 ? '#f59e0b' : '#10b981',
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

// Consommer la portion déjà parcourue → effet GTA 5
function consumeRoute(lat, lng) {
    if (routeRemaining.length < 2) return;
    const idx = closestIdx(lat, lng, routeRemaining);
    if (idx > 0) routeRemaining = routeRemaining.slice(idx);
    // Le premier point devient la position exacte du livreur
    routeRemaining[0] = [lat, lng];
    redrawRemaining();
}

// Calculer un nouveau trajet OSRM
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
// PHASE — mise à jour UI + destination
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

    if (phase === 1) {
        badge.className  = 'phase-badge phase-1';
        label.textContent = 'Phase 1 · Ramassage';
        ico.className    = 'info-dest-ico ico-vendor'; ico.textContent = '🏪';
        lbl.textContent  = 'Destination · Ramassage';
        val.textContent  = SHOP_NAME;
        destLat = VENDOR.lat; destLng = VENDOR.lng;
        if (destMarker) { map.removeLayer(destMarker); destMarker = null; }
        if (VENDOR.lat && VENDOR.lng) {
            destMarker = L.marker([VENDOR.lat, VENDOR.lng], { icon: vendorIcon })
                .bindPopup(`<b>🏪 ${SHOP_NAME}</b><br>Point de ramassage`).addTo(map);
            if (!driverLat) map.setView([VENDOR.lat, VENDOR.lng], 15);
        }
        const banner = document.getElementById('noVendorBanner');
        if (banner) banner.style.display = (VENDOR.lat && VENDOR.lng) ? 'none' : 'flex';
    } else {
        badge.className  = 'phase-badge phase-2';
        label.textContent = 'Phase 2 · Livraison';
        ico.className    = 'info-dest-ico ico-client'; ico.textContent = '📍';
        lbl.textContent  = 'Destination · Livraison';
        val.textContent  = DESTINATION;
        destLat = CLIENT.lat; destLng = CLIENT.lng;
        if (destMarker) { map.removeLayer(destMarker); destMarker = null; }
        if (CLIENT.lat && CLIENT.lng) {
            destMarker = L.marker([CLIENT.lat, CLIENT.lng], { icon: clientIcon })
                .bindPopup(`<b>📍 Client</b><br>${DESTINATION}`).addTo(map);
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
                    .bindPopup('🛵 Ma position').addTo(map);
            } else {
                animateDriverTo(newLat, newLng);
            }
            driverLat = newLat; driverLng = newLng;

            // ── Carte suit IMMÉDIATEMENT le livreur (avant tout calcul OSRM) ──
            map.panTo([newLat, newLng], { animate: true, duration: 0.4 });

            // ── Envoyer position au serveur (throttle 3s) ──
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
                // Nouvelle destination → nouveau trajet OSRM
                await planRoute(newLat, newLng, destLat, destLng);
            } else {
                const dist = distToRoute(newLat, newLng, routeRemaining);
                if (dist > 0.004) {          // hors-route à ~400 m
                    offRouteStreak++;
                    if (offRouteStreak >= 2) planRoute(newLat, newLng, destLat, destLng);
                } else {
                    offRouteStreak = 0;
                    consumeRoute(newLat, newLng);  // ← EFFET GTA 5 : mange la route
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
// POLLING (statut + positions)
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
// ACTIONS STATUT
// ─────────────────────────────────────────────
async function postStatus(status) {
    const r = await fetch(STATUS_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ status }),
    });
    if (!r.ok) { const d = await r.json().catch(()=>({})); throw new Error(d.message || 'Erreur serveur.'); }
    return true;
}

function startDelivery()  { document.getElementById('pickupOverlay').classList.add('open'); }
function closePickupModal(){ document.getElementById('pickupOverlay').classList.remove('open'); }

async function confirmPickup() {
    const btn = document.getElementById('btnPickupConfirm');
    btn.disabled = true; btn.textContent = 'En cours…';
    try   { await postStatus('en_livraison'); closePickupModal(); applyPhase(2); }
    catch(e) { alert(e.message); btn.disabled = false; btn.textContent = '✅ Oui, commencer la livraison'; }
}

const ORDERS_URL = '{{ route('livreur.orders.index') }}';
function goToOrders() { window.location.href = ORDERS_URL; }

async function markDelivered() {
    if (!confirm('Confirmer la livraison de cette commande ?')) return;
    const btn = document.getElementById('btnDone');
    btn.disabled = true; btn.style.opacity = '.6';
    try {
        await postStatus('livrée');
        if (gpsWatcher) navigator.geolocation.clearWatch(gpsWatcher);
        if (_aniRAF)    cancelAnimationFrame(_aniRAF);
        document.getElementById('navDoneScreen').style.display = 'flex';
        let t = 3;
        const cd = document.getElementById('navDoneCountdown');
        cd.textContent = `Redirection dans ${t}s…`;
        const iv = setInterval(() => { t--; if (t<=0){clearInterval(iv);goToOrders();return;} cd.textContent=`Redirection dans ${t}s…`; }, 1000);
    } catch(e) { alert(e.message); btn.disabled=false; btn.style.opacity=''; }
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

// ─────────────────────────────────────────────
// INIT
// ─────────────────────────────────────────────
applyPhase(currentPhase);
startTracking();
setInterval(pollOrderData, 5000);
</script>
</body>
</html>
