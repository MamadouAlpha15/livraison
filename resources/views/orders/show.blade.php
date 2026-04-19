{{-- resources/views/orders/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Suivi commande #' . str_pad($order->id, 5, '0', STR_PAD_LEFT))
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --orange: #f06a0f; --orange-dk: #d45a00; --orange-lt: #fff4ec;
    --green: #10b981; --green-lt: #ecfdf5;
    --blue: #3b82f6;  --blue-lt: #eff6ff;
    --yellow: #f59e0b; --yellow-lt: #fffbeb;
    --red: #ef4444;
    --text: #0f172a; --text-2: #475569; --muted: #94a3b8;
    --border: #e2e8f0; --surface: #fff; --bg: #f8f9fc;
    --r: 14px; --r-sm: 9px;
    --shadow: 0 4px 20px rgba(0,0,0,.08);
    --font: system-ui,-apple-system,'Segoe UI',sans-serif;
}
body { font-family: var(--font); background: var(--bg); color: var(--text); }

.suivi-wrap { max-width: 780px; margin: 0 auto; padding: 24px 16px 60px; }

/* ── Top bar ── */
.top-bar {
    background: linear-gradient(135deg, var(--orange), var(--orange-dk));
    border-radius: var(--r);
    padding: 16px 20px;
    display: flex; align-items: center; gap: 14px;
    margin-bottom: 20px;
    box-shadow: 0 4px 18px rgba(240,106,15,.3);
    position: relative; overflow: hidden;
}
.top-bar::after {
    content:''; position:absolute; top:-40px; right:-40px;
    width:130px; height:130px; border-radius:50%;
    background:rgba(255,255,255,.08); pointer-events:none;
}
.top-bar-ico { font-size: 30px; flex-shrink: 0; position: relative; z-index:1; }
.top-bar-info { flex:1; position: relative; z-index:1; }
.top-bar-info h1 { font-size:17px; font-weight:800; color:#fff; margin:0 0 3px; letter-spacing:-.3px; }
.top-bar-info p  { font-size:12px; color:rgba(255,255,255,.78); margin:0; font-weight:500; }
.status-pill {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 12px; border-radius:20px;
    font-size:12px; font-weight:700;
    background:rgba(255,255,255,.2); color:#fff;
    border:1.5px solid rgba(255,255,255,.35);
    position: relative; z-index:1;
    white-space: nowrap;
}

/* ── Info card ── */
.info-card {
    background: var(--surface); border:1px solid var(--border);
    border-radius: var(--r); padding: 18px 20px;
    margin-bottom: 16px; box-shadow: var(--shadow);
}
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 20px; }
.info-item label { font-size:11px; color:var(--muted); font-weight:600; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:3px; }
.info-item span  { font-size:14px; font-weight:700; color:var(--text); }

/* ── Barre de progression ── */
.progress-section { margin-bottom: 16px; }
.prog-steps { display:flex; align-items:center; position:relative; }
.prog-track {
    position:absolute; top:50%; left:0; right:0; height:3px;
    background:var(--border); transform:translateY(-50%); z-index:0;
}
.prog-fill {
    position:absolute; top:50%; left:0; height:3px;
    background:linear-gradient(90deg,var(--orange),var(--orange-dk));
    transform:translateY(-50%); transition:width .6s ease; z-index:1;
}
.prog-step { flex:1; display:flex; flex-direction:column; align-items:center; position:relative; z-index:2; }
.prog-dot {
    width:32px; height:32px; border-radius:50%;
    border:2.5px solid var(--border); background:var(--surface);
    display:flex; align-items:center; justify-content:center;
    font-size:14px; transition:all .3s;
}
.prog-dot.done    { background:var(--green); border-color:var(--green); color:#fff; font-size:13px; }
.prog-dot.current { background:var(--orange); border-color:var(--orange-dk); color:#fff; box-shadow:0 0 0 4px rgba(240,106,15,.2); }
.prog-lbl { font-size:10.5px; color:var(--muted); margin-top:5px; font-weight:600; text-align:center; }
.prog-lbl.active { color:var(--orange); }

/* ── Carte Leaflet ── */
.map-card {
    background: var(--surface); border:1px solid var(--border);
    border-radius: var(--r); overflow: hidden;
    box-shadow: var(--shadow); margin-bottom: 16px;
}
.map-header {
    padding: 12px 16px; border-bottom:1px solid var(--border);
    display: flex; align-items:center; justify-content:space-between; gap:10px;
}
.map-header-left { display:flex; align-items:center; gap:8px; }
.map-header-left strong { font-size:14px; font-weight:700; }
.live-dot {
    width:9px; height:9px; border-radius:50%;
    background:var(--green); display:inline-block;
    animation:pulse 1.6s ease-in-out infinite;
}
.live-dot.offline { background:var(--muted); animation:none; }
@keyframes pulse {
    0%,100% { box-shadow:0 0 0 0 rgba(16,185,129,.5); }
    50%      { box-shadow:0 0 0 6px rgba(16,185,129,0); }
}
.last-update { font-size:11.5px; color:var(--muted); }
#map { height: 420px; width:100%; }
@media (max-width:600px) { #map { height: 300px; } }

/* ── Annulée état ── */
.cancelled-state {
    text-align:center; padding:48px 20px;
    background:var(--surface); border:1px solid var(--border);
    border-radius:var(--r); box-shadow:var(--shadow);
}
.cancelled-state .ico { font-size:48px; margin-bottom:12px; }
.cancelled-state p    { color:var(--muted); font-size:14px; }

/* ── Livrée état ── */
.delivered-banner {
    background:var(--green-lt); border:1.5px solid #a7f3d0;
    border-radius:var(--r); padding:20px 24px;
    text-align:center; margin-bottom:16px;
}
.delivered-banner .ico { font-size:42px; margin-bottom:8px; }
.delivered-banner h3   { font-size:18px; font-weight:800; color:#065f46; margin:0 0 4px; }
.delivered-banner p    { font-size:13px; color:#047857; margin:0; }
</style>
@endpush

@section('content')
@php
use App\Models\Order;
$s = $order->status;
$isCancelled = $s === Order::STATUS_ANNULEE;
$isDelivered = $s === Order::STATUS_LIVREE;
$isOngoing   = $s === Order::STATUS_EN_LIVRAISON;

$statusLabels = [
    Order::STATUS_EN_ATTENTE   => ['En attente',   '⏳'],
    Order::STATUS_CONFIRMEE    => ['Confirmée',    '📦'],
    Order::STATUS_EN_LIVRAISON => ['En livraison', '🚚'],
    Order::STATUS_LIVREE       => ['Livrée',       '✅'],
    Order::STATUS_ANNULEE      => ['Annulée',      '❌'],
];
[$statusLabel, $statusIco] = $statusLabels[$s] ?? ['—', '?'];

$steps = [
    ['ico'=>'🕐','lbl'=>'Reçue',    'key'=> Order::STATUS_EN_ATTENTE],
    ['ico'=>'📦','lbl'=>'Confirmée','key'=> Order::STATUS_CONFIRMEE],
    ['ico'=>'🚚','lbl'=>'En route', 'key'=> Order::STATUS_EN_LIVRAISON],
    ['ico'=>'🏠','lbl'=>'Livrée',   'key'=> Order::STATUS_LIVREE],
];
$stepIndex = [
    Order::STATUS_EN_ATTENTE   => 0,
    Order::STATUS_CONFIRMEE    => 1,
    Order::STATUS_EN_LIVRAISON => 2,
    Order::STATUS_LIVREE       => 3,
];
$currentStep = $stepIndex[$s] ?? 0;
$fillPct     = min(($currentStep / (count($steps) - 1)) * 100, 100);
@endphp

<div class="suivi-wrap">

    {{-- ── Top bar ── --}}
    <div class="top-bar">
        <div class="top-bar-ico">📦</div>
        <div class="top-bar-info">
            <h1>Commande #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</h1>
            <p>{{ $order->shop?->name ?? '—' }} · {{ $order->created_at->format('d/m/Y à H:i') }}</p>
        </div>
        <span class="status-pill">{{ $statusIco }} {{ $statusLabel }}</span>
    </div>

    {{-- ── Infos ── --}}
    <div class="info-card">
        <div class="info-grid">
            <div class="info-item">
                <label>Client</label>
                <span>{{ $order->client?->name ?? '—' }}</span>
            </div>
            <div class="info-item">
                <label>Boutique</label>
                <span>{{ $order->shop?->name ?? '—' }}</span>
            </div>
            <div class="info-item">
                <label>Total</label>
                <span>{{ number_format($order->total, 0, ',', ' ') }} {{ $order->shop?->currency ?? 'GNF' }}</span>
            </div>
            <div class="info-item">
                <label>Livreur</label>
                <span>{{ $order->livreur?->name ?? '—' }}</span>
            </div>
        </div>
    </div>

    {{-- ── Progression (sauf annulée) ── --}}
    @if(!$isCancelled)
    <div class="info-card progress-section">
        <div class="prog-steps">
            <div class="prog-track"></div>
            <div class="prog-fill" style="width:{{ $fillPct }}%"></div>
            @foreach($steps as $i => $step)
            <div class="prog-step">
                <div class="prog-dot {{ $i < $currentStep ? 'done' : ($i === $currentStep ? 'current' : '') }}">
                    @if($i < $currentStep) ✓ @else {{ $step['ico'] }} @endif
                </div>
                <div class="prog-lbl {{ $i === $currentStep ? 'active' : '' }}">{{ $step['lbl'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Livrée : message de confirmation ── --}}
    @if($isDelivered)
    <div class="delivered-banner">
        <div class="ico">🎉</div>
        <h3>Commande livrée avec succès !</h3>
        <p>Votre colis a bien été remis. Merci de votre confiance.</p>
    </div>
    @endif

    {{-- ── Carte GPS ── --}}
    @if($isCancelled)
    <div class="cancelled-state">
        <div class="ico">❌</div>
        <h3 style="font-weight:800;color:var(--text);margin:0 0 8px">Commande annulée</h3>
        <p>Cette commande a été annulée. Aucun suivi disponible.</p>
    </div>
    @else
    <div class="map-card">
        <div class="map-header">
            <div class="map-header-left">
                <span class="live-dot {{ $isDelivered ? 'offline' : '' }}" id="liveDot"></span>
                <strong>📍 Suivi GPS en direct</strong>
            </div>
            <span class="last-update" id="lastUpdate">—</span>
        </div>
        <div id="map"></div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

@if(!$isCancelled)
<script>
(function () {
    const ORDER_STATUS = @json($order->status);
    const IS_DELIVERED = ORDER_STATUS === 'livrée';

    let lat = {{ $order->current_lat ?? '9.6412' }};
    let lng = {{ $order->current_lng ?? '-13.5784' }};
    const hasPosition = {{ $order->current_lat ? 'true' : 'false' }};

    const lastEl  = document.getElementById('lastUpdate');
    const liveDot = document.getElementById('liveDot');

    // ── Carte ──
    const map = L.map('map').setView([lat, lng], hasPosition ? 15 : 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // ── Icône livreur personnalisée ──
    const deliveryIcon = L.divIcon({
        html: '<div style="font-size:28px;line-height:1;filter:drop-shadow(0 2px 4px rgba(0,0,0,.3))">🚚</div>',
        iconSize: [34, 34], iconAnchor: [17, 17], className: ''
    });

    const marker  = L.marker([lat, lng], { icon: deliveryIcon }).addTo(map);
    const pathCoords = [[lat, lng]];
    const pathLine = L.polyline(pathCoords, {
        color: '#f06a0f', weight: 4, opacity: 0.7,
        dashArray: IS_DELIVERED ? null : '8 6'
    }).addTo(map);

    // ── Animation smooth ──
    function animateTo(from, to, cb, duration = 800) {
        const start = performance.now();
        (function frame(now) {
            const t = Math.min(1, (now - start) / duration);
            cb([from[0] + (to[0]-from[0])*t, from[1] + (to[1]-from[1])*t]);
            if (t < 1) requestAnimationFrame(frame);
        })(performance.now());
    }

    let pollInterval = null;
    let lastPingMs   = null;
    let offlineTimer = null;

    // ── Indicateur online/offline ──
    function setOnline(online) {
        if (!liveDot) return;
        liveDot.classList.toggle('offline', !online);
    }

    // ── Polling position ──
    async function refreshPosition() {
        if (IS_DELIVERED) return; // livraison terminée, pas besoin de poller

        try {
            const resp = await fetch("{{ route('orders.position.show', $order) }}", {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!resp.ok) {
                // 401 = pas connecté, on affiche un message discret
                if (resp.status === 401) {
                    lastEl.textContent = 'Connectez-vous pour voir la position en direct.';
                }
                setOnline(false);
                return;
            }

            const data = await resp.json();

            if (data.lat != null && data.lng != null) {
                const newLat = parseFloat(data.lat);
                const newLng = parseFloat(data.lng);
                const dLat   = newLat - lat;
                const dLng   = newLng - lng;

                if (Math.abs(dLat) + Math.abs(dLng) > 0.00015) { // ~15m
                    animateTo([lat, lng], [newLat, newLng], pt => marker.setLatLng(pt));
                    pathCoords.push([newLat, newLng]);
                    pathLine.setLatLngs(pathCoords);
                    if (Math.abs(dLat)+Math.abs(dLng) > 0.0005) {
                        map.panTo([newLat, newLng], { animate: true, duration: 0.8 });
                    }
                    lat = newLat; lng = newLng;
                }
            }

            // ── Statut online (livreur actif si ping < 30s) ──
            if (data.updated) {
                const d = new Date(data.updated);
                lastPingMs = d.getTime();
                const diffSec = Math.round((Date.now() - lastPingMs) / 1000);
                const isOnline = diffSec < 30;
                setOnline(isOnline);

                if (diffSec < 60) lastEl.textContent = 'Mise à jour il y a ' + diffSec + 's';
                else              lastEl.textContent = 'Dernière position : ' + d.toLocaleTimeString();
            } else {
                lastEl.textContent = 'En attente de la position du livreur…';
                setOnline(false);
            }

            // ── Stop si livraison terminée ──
            if (data.status === 'livrée' || data.status === 'livree' || data.status === 'delivered') {
                clearInterval(pollInterval);
                setOnline(false);
                lastEl.textContent = '✅ Livraison terminée.';
                pathLine.setStyle({ dashArray: null }); // trait plein
            }

        } catch (e) {
            console.warn('GPS poll error:', e);
            setOnline(false);
        }
    }

    // ── Start ──
    refreshPosition();
    if (!IS_DELIVERED) {
        pollInterval = setInterval(refreshPosition, 3000);
    } else {
        setOnline(false);
        lastEl.textContent = '✅ Livraison terminée.';
    }

})();
</script>
@endif
@endpush
