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
// ──────────────────────────────────────────────────────────────────
// Données initiales depuis Blade
// ──────────────────────────────────────────────────────────────────
const ORDER_ID     = {{ $order->id }};
const DATA_URL     = '{{ route('suivi.data', $order) }}';
const POS_URL      = '{{ route('orders.position.update', $order) }}';
const STATUS_URL   = '{{ route('orders.driver.status', $order) }}';
const CSRF         = '{{ csrf_token() }}';
const SHOP_NAME    = @json($shopName);
const SHOP_ADDRESS = @json($shopAddress);
const DESTINATION  = @json($destination);

let currentPhase = {{ $initialPhase }};
let driverLat = null, driverLng = null;
let destLat   = null, destLng   = null;
let gpsWatcher = null;

// Données initiales du vendeur / client
const VENDOR = {
    lat: @json($order->vendor_lat),
    lng: @json($order->vendor_lng),
};
const CLIENT = {
    lat: @json($order->client_lat),
    lng: @json($order->client_lng),
};

// ──────────────────────────────────────────────────────────────────
// Carte Leaflet
// ──────────────────────────────────────────────────────────────────
const map = L.map('navMap', { zoomControl: true, attributionControl: false });
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19, subdomains: 'abc'
}).addTo(map);

// Icônes personnalisées
function makeIcon(emoji, color) {
    return L.divIcon({
        className: '',
        html: `<div style="width:36px;height:36px;border-radius:50%;background:${color};display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 3px 14px rgba(0,0,0,.5);border:2px solid rgba(255,255,255,.3)">${emoji}</div>`,
        iconSize: [36, 36], iconAnchor: [18, 18],
    });
}
const driverIcon = makeIcon('🛵', '#4f46e5');
const vendorIcon = makeIcon('🏪', '#d97706');
const clientIcon = makeIcon('📍', '#059669');

let driverMarker   = null;
let destMarker     = null;
let routeLine      = null;
let routeShadow    = null;
let mapInitialized = false; // true après le premier fitBounds

// ──────────────────────────────────────────────────────────────────
// Dessiner le trajet
// ──────────────────────────────────────────────────────────────────
function drawRoute(fromLat, fromLng, toLat, toLng) {
    if (routeShadow) map.removeLayer(routeShadow);
    if (routeLine)   map.removeLayer(routeLine);
    if (!toLat || !toLng) return;

    const pts = [[fromLat, fromLng], [toLat, toLng]];
    routeShadow = L.polyline(pts, { color: 'rgba(0,0,0,.25)', weight: 6 }).addTo(map);
    routeLine   = L.polyline(pts, {
        color: currentPhase === 1 ? '#f59e0b' : '#10b981',
        weight: 4, dashArray: '10 6', lineCap: 'round'
    }).addTo(map);

    if (!mapInitialized) {
        // Premier fix GPS : cadrer pour voir driver + destination
        map.fitBounds(L.latLngBounds(pts), { padding: [60, 60], maxZoom: 16 });
        mapInitialized = true;
    } else {
        // Ensuite : suivre le livreur en douceur sans changer le zoom
        map.panTo([fromLat, fromLng], { animate: true, duration: 0.5 });
    }
}

// ──────────────────────────────────────────────────────────────────
// Mettre à jour la destination selon la phase
// ──────────────────────────────────────────────────────────────────
function applyPhase(phase) {
    if (phase !== currentPhase) mapInitialized = false; // recadrer sur la nouvelle destination
    currentPhase = phase;

    const badge = document.getElementById('phaseBadge');
    const label = document.getElementById('phaseLabel');
    const ico   = document.getElementById('destIco');
    const lbl   = document.getElementById('destLbl');
    const val   = document.getElementById('destVal');

    if (phase === 1) {
        badge.className = 'phase-badge phase-1';
        label.textContent = 'Phase 1 · Ramassage';
        ico.className = 'info-dest-ico ico-vendor';
        ico.textContent = '🏪';
        lbl.textContent = 'Destination · Ramassage';
        val.textContent = SHOP_NAME;
        destLat = VENDOR.lat; destLng = VENDOR.lng;
        if (destMarker) { map.removeLayer(destMarker); destMarker = null; }
        if (VENDOR.lat && VENDOR.lng) {
            destMarker = L.marker([VENDOR.lat, VENDOR.lng], { icon: vendorIcon })
                .bindPopup(`<b>🏪 ${SHOP_NAME}</b><br>Point de ramassage`)
                .addTo(map);
            // Centrer la carte sur le vendeur en attendant le GPS du livreur
            if (!driverLat) map.setView([VENDOR.lat, VENDOR.lng], 15);
        }
        // Afficher/cacher le banner "vendeur n'a pas partagé"
        const banner = document.getElementById('noVendorBanner');
        if (banner) banner.style.display = (VENDOR.lat && VENDOR.lng) ? 'none' : 'flex';
    } else {
        badge.className = 'phase-badge phase-2';
        label.textContent = 'Phase 2 · Livraison';
        ico.className = 'info-dest-ico ico-client';
        ico.textContent = '📍';
        lbl.textContent = 'Destination · Livraison';
        val.textContent = DESTINATION;
        destLat = CLIENT.lat; destLng = CLIENT.lng;
        if (destMarker) { map.removeLayer(destMarker); destMarker = null; }
        if (CLIENT.lat && CLIENT.lng) {
            destMarker = L.marker([CLIENT.lat, CLIENT.lng], { icon: clientIcon })
                .bindPopup(`<b>📍 Client</b><br>${DESTINATION}`)
                .addTo(map);
            // Centrer sur le client en attendant le GPS du livreur
            if (!driverLat) map.setView([CLIENT.lat, CLIENT.lng], 15);
        }
    }

    // Basculer boutons d'action
    const btnStart = document.getElementById('btnStart');
    const btnDone  = document.getElementById('btnDone');
    if (btnStart) btnStart.style.display = phase === 1 ? '' : 'none';
    if (btnDone)  btnDone.style.display  = phase === 2 ? '' : 'none';

    if (driverLat && destLat) drawRoute(driverLat, driverLng, destLat, destLng);
}

// ──────────────────────────────────────────────────────────────────
// Changement de statut
// ──────────────────────────────────────────────────────────────────
async function postStatus(status) {
    const r = await fetch(STATUS_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ status }),
    });
    if (!r.ok) {
        const d = await r.json().catch(() => ({}));
        throw new Error(d.message || 'Erreur serveur.');
    }
    return true;
}

function startDelivery() {
    document.getElementById('pickupOverlay').classList.add('open');
}

function closePickupModal() {
    document.getElementById('pickupOverlay').classList.remove('open');
}

async function confirmPickup() {
    const btn = document.getElementById('btnPickupConfirm');
    btn.disabled = true; btn.textContent = 'En cours…';
    try {
        await postStatus('en_livraison');
        closePickupModal();
        applyPhase(2);
    } catch(e) {
        alert(e.message);
        btn.disabled = false; btn.textContent = '✅ Oui, commencer la livraison';
    }
}

const ORDERS_URL = '{{ route('livreur.orders.index') }}';

function goToOrders() {
    window.location.href = ORDERS_URL;
}

async function markDelivered() {
    if (!confirm('Confirmer la livraison de cette commande ?')) return;
    const btn = document.getElementById('btnDone');
    btn.disabled = true; btn.style.opacity = '.6';
    try {
        await postStatus('livrée');
        if (gpsWatcher) navigator.geolocation.clearWatch(gpsWatcher);
        document.getElementById('navDoneScreen').style.display = 'flex';
        // Compte à rebours auto-redirect 3s
        let t = 3;
        const cd = document.getElementById('navDoneCountdown');
        cd.textContent = `Redirection dans ${t}s…`;
        const iv = setInterval(() => {
            t--;
            if (t <= 0) { clearInterval(iv); goToOrders(); return; }
            cd.textContent = `Redirection dans ${t}s…`;
        }, 1000);
    } catch(e) {
        alert(e.message);
        btn.disabled = false; btn.style.opacity = '';
    }
}

// ──────────────────────────────────────────────────────────────────
// GPS du livreur (navigateur)
// ──────────────────────────────────────────────────────────────────
function startTracking() {
    if (!navigator.geolocation) {
        document.getElementById('gpsLoading').style.display = 'none';
        map.setView([9.641, -13.578], 13);
        applyPhase(currentPhase);
        return;
    }
    gpsWatcher = navigator.geolocation.watchPosition(
        pos => {
            document.getElementById('gpsLoading').style.display = 'none';
            driverLat = pos.coords.latitude;
            driverLng = pos.coords.longitude;

            // Mise à jour marqueur driver
            if (!driverMarker) {
                driverMarker = L.marker([driverLat, driverLng], { icon: driverIcon })
                    .bindPopup('📍 Ma position')
                    .addTo(map);
            } else {
                driverMarker.setLatLng([driverLat, driverLng]);
            }

            // Envoyer position au serveur
            fetch(POS_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ lat: driverLat, lng: driverLng }),
            }).catch(() => {});

            if (destLat) drawRoute(driverLat, driverLng, destLat, destLng);
            else map.setView([driverLat, driverLng], 15);
        },
        () => {
            document.getElementById('gpsLoading').style.display = 'none';
            map.setView([9.641, -13.578], 13);
        },
        { enableHighAccuracy: true, maximumAge: 5000, timeout: 10000 }
    );
}

// ──────────────────────────────────────────────────────────────────
// Polling : vérifier si le statut a changé (phase switch auto)
// et récupérer les nouvelles positions vendeur/client
// ──────────────────────────────────────────────────────────────────
function pollOrderData() {
    fetch(DATA_URL)
        .then(r => r.json())
        .then(data => {
            const prevVendorLat = VENDOR.lat;
            const prevClientLat = CLIENT.lat;
            const prevClientLng = CLIENT.lng;

            if (data.vendor_lat && data.vendor_lng) {
                VENDOR.lat = data.vendor_lat;
                VENDOR.lng = data.vendor_lng;
            }
            if (data.client_lat && data.client_lng) {
                CLIENT.lat = data.client_lat;
                CLIENT.lng = data.client_lng;
            }

            const newPhase = data.status === 'en_livraison' ? 2 : 1;
            if (newPhase !== currentPhase) {
                applyPhase(newPhase);
            } else if (currentPhase === 1 && VENDOR.lat && !prevVendorLat) {
                mapInitialized = false; // recadrer pour voir driver + boutique ensemble
                applyPhase(1);
                const badge = document.getElementById('vendorStatusBadge');
                if (badge) badge.style.display = 'none';
            } else if (currentPhase === 2 && CLIENT.lat) {
                if (!prevClientLat) {
                    // Client vient de partager pour la première fois → recréer marqueur + route
                    applyPhase(2);
                } else if (CLIENT.lat !== prevClientLat || CLIENT.lng !== prevClientLng) {
                    // Client a bougé → déplacer le marqueur + mettre à jour destLat/destLng
                    destLat = CLIENT.lat; destLng = CLIENT.lng;
                    if (destMarker) destMarker.setLatLng([CLIENT.lat, CLIENT.lng]);
                    if (driverLat) drawRoute(driverLat, driverLng, CLIENT.lat, CLIENT.lng);
                }
            }
        })
        .catch(() => {});
}

// ──────────────────────────────────────────────────────────────────
// Google Maps fallback
// ──────────────────────────────────────────────────────────────────
function openGoogleMaps() {
    const origin = driverLat ? `${driverLat},${driverLng}` : '';
    let dest;
    if (destLat && destLng) {
        dest = `${destLat},${destLng}`;
    } else if (currentPhase === 1 && SHOP_ADDRESS) {
        dest = encodeURIComponent(SHOP_ADDRESS);
    } else if (currentPhase === 2 && DESTINATION) {
        // Client n'a pas partagé sa position GPS → utiliser l'adresse texte
        dest = encodeURIComponent(DESTINATION);
    } else {
        alert('Destination non disponible.');
        return;
    }
    window.open(`https://www.google.com/maps/dir/${origin}/${dest}`, '_blank');
}

// ──────────────────────────────────────────────────────────────────
// Init
// ──────────────────────────────────────────────────────────────────
applyPhase(currentPhase);
startTracking();
setInterval(pollOrderData, 5000); // Vérifier toutes les 5s
</script>
</body>
</html>
