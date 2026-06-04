{{-- resources/views/livreur/orders/carte.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>GPS · Livraison #{{ str_pad($order->id,5,'0',STR_PAD_LEFT) }}</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{width:100%;height:100%;overflow:hidden;font-family:'Segoe UI',system-ui,sans-serif;background:#0f172a}
:root{
    --blue:#3b82f6;--blue-lt:#dbeafe;
    --green:#10b981;--green-lt:#d1fae5;
    --orange:#f06a0f;
    --text:#0f172a;--muted:#64748b;--border:#e2e8f0;
}

/* ── Topbar ── */
#topbar{
    position:fixed;top:0;left:0;right:0;z-index:900;
    height:54px;
    background:rgba(15,23,42,.95);
    backdrop-filter:blur(12px);
    border-bottom:1px solid rgba(255,255,255,.08);
    display:flex;align-items:center;gap:10px;padding:0 14px;
}
#topbar a{
    display:inline-flex;align-items:center;gap:5px;
    padding:7px 12px;border-radius:8px;
    background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);
    color:#fff;font-size:12.5px;font-weight:700;text-decoration:none;
    white-space:nowrap;flex-shrink:0;
}
#topbar a:hover{background:rgba(255,255,255,.18)}
.tb-info{flex:1;min-width:0}
.tb-title{font-size:13.5px;font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.tb-sub{font-size:11px;color:rgba(255,255,255,.55);margin-top:1px}
.tb-pill{
    flex-shrink:0;
    display:inline-flex;align-items:center;gap:5px;
    padding:4px 11px;border-radius:20px;
    background:rgba(16,185,129,.18);border:1px solid rgba(16,185,129,.35);
    color:#34d399;font-size:11px;font-weight:700;white-space:nowrap;
}
.tb-pill.off{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.12);color:rgba(255,255,255,.4)}
.tb-dot{width:6px;height:6px;border-radius:50%;background:#22c55e;animation:blink 2s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}

/* ── Map ── */
#map{position:fixed;inset:0;top:54px;bottom:100px}
#map img,.leaflet-container img,.leaflet-tile{max-width:none!important;height:auto!important}

/* ── Bottom panel ── */
#bottomPanel{
    position:fixed;bottom:0;left:0;right:0;z-index:900;
    height:100px;
    background:rgba(15,23,42,.97);
    border-top:1px solid rgba(255,255,255,.08);
    display:flex;align-items:center;gap:12px;padding:12px 16px;
}
.bp-info{flex:1;min-width:0}
.bp-client{font-size:14px;font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.bp-dest{font-size:11.5px;color:rgba(255,255,255,.5);margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.bp-gps-status{font-size:11px;margin-top:5px;display:flex;align-items:center;gap:5px}
#clientPosLabel{color:rgba(255,255,255,.4)}
.bp-actions{display:flex;flex-direction:column;gap:6px;flex-shrink:0}
.bp-call{
    display:inline-flex;align-items:center;justify-content:center;gap:5px;
    padding:7px 14px;border-radius:8px;
    background:rgba(37,211,102,.15);border:1px solid rgba(37,211,102,.3);
    color:#25d366;font-size:12px;font-weight:700;text-decoration:none;white-space:nowrap;
}
.bp-complete{
    display:inline-flex;align-items:center;justify-content:center;gap:5px;
    padding:7px 14px;border-radius:8px;
    background:rgba(16,185,129,.2);border:1px solid rgba(16,185,129,.4);
    color:#34d399;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;
    font-family:inherit;
}
.bp-complete:active{opacity:.8}
</style>
</head>
<body>
@php
    $client     = $order->client;
    $clientPhone= $order->client_phone ?: $client?->phone;
    $dest       = $order->delivery_destination ?? $client?->address ?? '';
    $hasClient  = !is_null($order->client_lat) && !is_null($order->client_lng);
    $clientName = $client?->name ?? 'Client';
@endphp

{{-- Topbar --}}
<div id="topbar">
    <a href="{{ route('livreur.orders.index') }}">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Retour
    </a>
    <div class="tb-info">
        <div class="tb-title">Commande #{{ str_pad($order->id,5,'0',STR_PAD_LEFT) }}</div>
        <div class="tb-sub">{{ $clientName }}</div>
    </div>
    <div class="tb-pill" id="gpsPill">
        <span class="tb-dot" id="gpsTopDot"></span>
        <span id="gpsTopLabel">GPS…</span>
    </div>
</div>

{{-- Map --}}
<div id="map"></div>

{{-- Bottom panel --}}
<div id="bottomPanel">
    <div class="bp-info">
        <div class="bp-client">{{ $clientName }}</div>
        <div class="bp-dest">{{ $dest ?: 'Destination non renseignée' }}</div>
        <div class="bp-gps-status">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span id="clientPosLabel">{{ $hasClient ? 'Position client disponible' : 'En attente de la position client…' }}</span>
        </div>
    </div>
    <div class="bp-actions">
        @if($clientPhone)
        <a href="tel:{{ $clientPhone }}" class="bp-call">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.18 1.22 2 2 0 012.17 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.09a16 16 0 006 6l.46-.46a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
            Appeler
        </a>
        @endif
        <button class="bp-complete" id="completeBtn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Livré
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
<script>
(function () {
    const DATA_URL      = '{{ route('suivi.data', $order) }}';
    const GPS_URL       = '/orders/{{ $order->id }}/position';
    const COMPLETE_URL  = '{{ route('livreur.orders.complete', $order) }}';
    const CSRF          = document.querySelector('meta[name="csrf-token"]').content;

    @php
        $initLat = $order->client_lat ?? $order->current_lat ?? 9.6412;
        $initLng = $order->client_lng ?? $order->current_lng ?? -13.5784;
    @endphp

    const MAPBOX_TOKEN = '{{ config('services.mapbox.token') }}';

    /* ── Leaflet init ── */
    const map = L.map('map', { zoomControl: true, attributionControl: false })
        .setView([{{ $initLat }}, {{ $initLng }}], 15);

    L.tileLayer(`https://api.mapbox.com/styles/v1/mapbox/streets-v12/tiles/256/{z}/{x}/{y}?access_token=${MAPBOX_TOKEN}`, {
        attribution:'© Mapbox', maxZoom:19
    }).addTo(map);
    map.zoomControl.setPosition('topright');
    setTimeout(() => map.invalidateSize(), 200);

    /* ── State ── */
    let driverLat = null, driverLng = null;
    let clientLat = {{ $hasClient ? $order->client_lat : 'null' }};
    let clientLng = {{ $hasClient ? $order->client_lng : 'null' }};
    let driverMarker = null, clientMarker = null;
    let routeLine = null, routeShadow = null;
    let routeTimer = null;

    /* ── Driver marker (orange) ── */
    function makeDriverIcon() {
        return L.divIcon({
            html: `<div style="width:32px;height:32px;border-radius:50%;
                        background:linear-gradient(135deg,#f06a0f,#d45a00);
                        border:2.5px solid #fff;
                        box-shadow:0 2px 10px rgba(240,106,15,.55);
                        display:flex;align-items:center;justify-content:center;
                        font-size:15px;line-height:1">🛵</div>`,
            iconSize: [32,32], iconAnchor: [16,16], className: ''
        });
    }

    /* ── Client marker (blue) ── */
    function makeClientIcon() {
        return L.divIcon({
            html: `<div style="width:22px;height:22px;border-radius:50%;
                        background:#3b82f6;border:2.5px solid #fff;
                        box-shadow:0 0 0 4px rgba(59,130,246,.25),0 2px 8px rgba(59,130,246,.5)"></div>`,
            iconSize: [22,22], iconAnchor: [11,11], className: ''
        });
    }

    function updateDriverMarker(lat, lng) {
        if (driverMarker) driverMarker.setLatLng([lat, lng]);
        else {
            driverMarker = L.marker([lat, lng], { icon: makeDriverIcon() })
                .addTo(map)
                .bindPopup('<b style="font-size:13px">Votre position</b>');
        }
    }

    function updateClientMarker(lat, lng) {
        const el = document.getElementById('clientPosLabel');
        if (el) el.textContent = 'Position client reçue · en direct';
        if (clientMarker) clientMarker.setLatLng([lat, lng]);
        else {
            clientMarker = L.marker([lat, lng], { icon: makeClientIcon() })
                .addTo(map)
                .bindPopup(`<b style="font-size:13px">{{ $clientName }}</b><br><span style="font-size:11px;color:#94a3b8">Position partagée</span>`);
        }
    }

    /* ── Mapbox Directions route ── */
    async function fetchRoute(dLat, dLng, cLat, cLng) {
        try {
            const url = `https://api.mapbox.com/directions/v5/mapbox/driving/${dLng},${dLat};${cLng},${cLat}?geometries=geojson&overview=full&access_token=${MAPBOX_TOKEN}`;
            const res = await fetch(url);
            const data = await res.json();
            if (!data.routes?.length) return null;
            return data.routes[0].geometry.coordinates.map(([lo, la]) => [la, lo]);
        } catch { return null; }
    }

    async function updateRoute() {
        if (!driverLat || !clientLat) return;
        const pts = await fetchRoute(driverLat, driverLng, clientLat, clientLng);
        if (!pts) return;
        if (routeShadow) routeShadow.setLatLngs(pts);
        else routeShadow = L.polyline(pts, { color: 'rgba(0,0,0,.15)', weight: 8, lineCap: 'round' }).addTo(map);
        if (routeLine)  routeLine.setLatLngs(pts);
        else routeLine  = L.polyline(pts, { color: '#3b82f6', weight: 4, opacity: .85, lineCap: 'round', dashArray: '10 6' }).addTo(map);
        /* Keep route lines on top */
        if (routeLine) routeLine.bringToFront();
    }

    function scheduleRoute() {
        clearTimeout(routeTimer);
        routeTimer = setTimeout(updateRoute, 1500);
    }

    /* ── Driver GPS (watchPosition → send to server) ── */
    let lastSentTs = 0;
    const GPS_OPTS = { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 };

    function onDriverPosition(pos) {
        driverLat = pos.coords.latitude;
        driverLng = pos.coords.longitude;
        updateDriverMarker(driverLat, driverLng);
        scheduleRoute();

        const gpsPill  = document.getElementById('gpsPill');
        const gpsLabel = document.getElementById('gpsTopLabel');
        if (gpsPill)  gpsPill.classList.remove('off');
        if (gpsLabel) gpsLabel.textContent = 'GPS actif';

        /* Throttle server sends to every 5s */
        const now = Date.now();
        if (now - lastSentTs < 5000) return;
        lastSentTs = now;
        fetch(GPS_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ lat: driverLat, lng: driverLng })
        }).catch(() => {});
    }

    function onGpsError(err) {
        const gpsPill  = document.getElementById('gpsPill');
        const gpsLabel = document.getElementById('gpsTopLabel');
        if (gpsPill)  gpsPill.classList.add('off');
        if (gpsLabel) gpsLabel.textContent = 'GPS erreur';
    }

    /* Auto-start GPS */
    if ('geolocation' in navigator) {
        navigator.geolocation.watchPosition(onDriverPosition, onGpsError, GPS_OPTS);
    } else {
        document.getElementById('gpsTopLabel').textContent = 'GPS non dispo';
    }

    /* WakeLock (mobile) */
    if ('wakeLock' in navigator) {
        navigator.wakeLock.request('screen').catch(() => {});
    }

    /* ── Poll client + order status ── */
    async function pollData() {
        try {
            const res = await fetch(DATA_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const d = await res.json();

            /* Client position update */
            if (d.client_lat != null && d.client_lng != null) {
                const newLat = parseFloat(d.client_lat);
                const newLng = parseFloat(d.client_lng);
                const changed = newLat !== clientLat || newLng !== clientLng;
                clientLat = newLat; clientLng = newLng;
                updateClientMarker(clientLat, clientLng);
                if (changed && driverLat) scheduleRoute();
            }

            /* Order delivered / cancelled → redirect back */
            if (d.is_delivered || d.is_cancelled) {
                window.location.href = '{{ route('livreur.orders.index') }}';
            }
        } catch {}
    }

    /* Show initial client marker if position already set */
    if (clientLat !== null && clientLng !== null) {
        updateClientMarker(clientLat, clientLng);
    }

    pollData();
    setInterval(pollData, 5000);

    /* ── "Livré" button → POST complete form ── */
    document.getElementById('completeBtn')?.addEventListener('click', () => {
        if (!confirm('Confirmer la livraison de cette commande ?')) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = COMPLETE_URL;
        form.innerHTML = `<input type="hidden" name="_token" value="${CSRF}">
                          <input type="hidden" name="_method" value="PUT">`;
        document.body.appendChild(form);
        form.submit();
    });

})();
</script>
</body>
</html>
