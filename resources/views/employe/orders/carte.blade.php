@extends('layouts.app')
@section('title', 'Carte en direct · ' . ($shop->name ?? 'Boutique'))
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
*,*::before,*::after{box-sizing:border-box}
:root{
    --brand:#6366f1;--brand-dk:#4f46e5;
    --bg:#f8fafc;--surface:#fff;--border:#e2e8f0;
    --text:#0f172a;--text-2:#475569;--muted:#94a3b8;
    --green:#10b981;--amber:#f59e0b;--red:#ef4444;--blue:#3b82f6;
    --top-h:54px;--panel-w:290px;--r:12px;
}
html,body{margin:0;font-family:'Segoe UI',system-ui,sans-serif;background:var(--bg);color:var(--text);height:100%;overflow:hidden}
a{text-decoration:none;color:inherit}

/* ── WRAP ── */
.crt-wrap{display:flex;flex-direction:column;height:100vh;height:100dvh}

/* ── TOPBAR ── */
.crt-top{
    height:var(--top-h);background:var(--surface);
    border-bottom:1px solid var(--border);
    display:flex;align-items:center;gap:10px;padding:0 16px;
    flex-shrink:0;z-index:100;box-shadow:0 1px 4px rgba(0,0,0,.06);
}
.crt-back{display:flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);color:var(--brand-dk);font-size:12.5px;font-weight:700;cursor:pointer;transition:background .14s;white-space:nowrap;flex-shrink:0}
.crt-back:hover{background:rgba(99,102,241,.15)}
.crt-title{font-size:14px;font-weight:800;color:var(--text)}
.crt-sub{font-size:12px;color:var(--muted);margin-left:2px}
.crt-right{margin-left:auto;display:flex;align-items:center;gap:8px;flex-shrink:0}
.crt-pulse{display:flex;align-items:center;gap:6px;font-size:11.5px;color:var(--muted)}
.crt-dot{width:7px;height:7px;border-radius:50%;background:var(--green);animation:blink 2s ease-in-out infinite;flex-shrink:0}
.crt-count{background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.25);color:var(--brand-dk);font-size:11.5px;font-weight:700;padding:3px 10px;border-radius:20px;white-space:nowrap}
.crt-panel-btn{
    display:none;height:30px;padding:0 10px;border-radius:8px;
    background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.25);
    color:var(--brand-dk);font-size:12px;font-weight:700;cursor:pointer;
    align-items:center;gap:5px;white-space:nowrap;flex-shrink:0;
}
.crt-panel-btn:hover{background:rgba(99,102,241,.18)}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.25}}

/* ── BODY ── */
.crt-body{display:flex;flex:1;overflow:hidden;position:relative}

/* ── PANEL ── */
.crt-panel{
    width:var(--panel-w);flex-shrink:0;
    background:var(--surface);border-right:1px solid var(--border);
    display:flex;flex-direction:column;overflow:hidden;
    transition:transform .3s cubic-bezier(.23,1,.32,1);
}
.crt-panel-drag{display:none;width:38px;height:4px;background:rgba(0,0,0,.15);border-radius:2px;margin:10px auto 0;flex-shrink:0}
.crt-panel-hd{padding:12px 14px 8px;border-bottom:1px solid var(--border);flex-shrink:0}
.crt-panel-title{font-size:13px;font-weight:800;color:var(--text);margin-bottom:2px}
.crt-panel-sub{font-size:11px;color:var(--muted)}
.crt-panel-list{flex:1;overflow-y:auto;scrollbar-width:thin;scrollbar-color:rgba(99,102,241,.2) transparent}
.crt-panel-list::-webkit-scrollbar{width:3px}
.crt-panel-list::-webkit-scrollbar-thumb{background:rgba(99,102,241,.2);border-radius:3px}
.crt-panel-close{
    display:none;position:absolute;top:10px;right:10px;
    width:26px;height:26px;border-radius:7px;
    background:rgba(0,0,0,.05);border:1px solid var(--border);
    color:var(--muted);font-size:13px;cursor:pointer;
    align-items:center;justify-content:center;z-index:1;transition:background .14s;flex-shrink:0;
}
.crt-panel-close:hover{background:rgba(239,68,68,.1);color:#dc2626}

/* ── CARDS PANNEAU ── */
.cp-card{padding:11px 13px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .12s;position:relative}
.cp-card:hover{background:#f8fafc}
.cp-card.selected{background:rgba(99,102,241,.06);border-left:3px solid var(--brand)}
.cp-card-top{display:flex;align-items:center;gap:7px;margin-bottom:5px}
.cp-color{width:9px;height:9px;border-radius:50%;flex-shrink:0}
.cp-id{font-size:10.5px;font-weight:800;color:var(--brand)}
.cp-name{font-size:12.5px;font-weight:700;color:var(--text);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.cp-badge{font-size:9.5px;font-weight:700;padding:2px 7px;border-radius:20px;flex-shrink:0}
.cp-badge-liv{background:rgba(245,158,11,.12);color:#b45309;border:1px solid rgba(245,158,11,.25)}
.cp-badge-conf{background:rgba(59,130,246,.1);color:#1d4ed8;border:1px solid rgba(59,130,246,.2)}
.cp-type{font-size:10px;color:var(--muted);margin-bottom:3px}
.cp-dest{font-size:11px;color:var(--muted);margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.cp-ping{font-size:10px;color:var(--muted);margin-top:3px;display:flex;align-items:center;gap:3px}
.cp-ping-ok{color:var(--green)}
.cp-ping-stale{color:var(--amber)}
.cp-no-gps{font-size:10px;color:var(--muted);font-style:italic;margin-top:3px}
.cp-empty{padding:36px 18px;text-align:center;color:var(--muted)}
.cp-empty-ico{font-size:34px;margin-bottom:8px}
.cp-empty-title{font-size:13.5px;font-weight:700;color:var(--text);margin-bottom:3px}

/* ── MAP ── */
.crt-map-wrap{flex:1;position:relative;min-width:0}
#map{width:100%;height:100%}
#map img,.leaflet-container img,.leaflet-tile{max-width:none!important;height:auto}

/* ── FAB mobile ── */
.crt-fab{
    display:none;
    position:absolute;bottom:18px;left:50%;transform:translateX(-50%);
    z-index:450;
    background:var(--brand);color:#fff;border:none;border-radius:28px;
    padding:9px 18px;font-size:12.5px;font-weight:800;
    cursor:pointer;gap:7px;align-items:center;
    box-shadow:0 4px 18px rgba(99,102,241,.45);
    white-space:nowrap;transition:background .14s,opacity .2s,transform .1s;
}
.crt-fab.hidden{opacity:0;pointer-events:none}
.crt-fab:active{transform:translateX(-50%) scale(.96)}
.crt-fab:hover{background:var(--brand-dk)}
.crt-fab-count{background:rgba(255,255,255,.25);border-radius:20px;padding:1px 7px;font-size:10.5px}

/* ── Overlay panel mobile ── */
.crt-panel-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:395}
.crt-panel-overlay.open{display:block}

/* ── No signal ── */
.crt-no-signal{position:absolute;top:12px;left:50%;transform:translateX(-50%);z-index:500;background:rgba(0,0,0,.72);color:#fff;padding:7px 16px;border-radius:20px;font-size:12px;font-weight:700;display:none;gap:6px;align-items:center;backdrop-filter:blur(6px);white-space:nowrap}
.crt-no-signal.show{display:flex}

/* ── Popup Leaflet ── */
.leaflet-popup-content-wrapper{background:#fff;color:#111;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 6px 24px rgba(0,0,0,.12)}
.leaflet-popup-tip{background:#fff}
.leaflet-popup-content{margin:10px 13px;font-size:12px;line-height:1.6}
.lp-title{font-weight:800;font-size:13px;margin-bottom:4px}
.lp-row{display:flex;gap:5px;color:#475569;font-size:11.5px}
.lp-row strong{color:#0f172a}

/* ═══ RESPONSIVE ═══ */
@media(max-width:900px){
    .crt-panel{
        position:absolute;top:0;left:0;bottom:0;
        width:270px;z-index:400;
        transform:translateX(-100%);
        box-shadow:6px 0 24px rgba(0,0,0,.18);
    }
    .crt-panel.show{transform:translateX(0)}
    .crt-panel-close{display:flex}
    .crt-panel-btn{display:flex}
    .crt-sub{display:none}
}
@media(max-width:640px){
    :root{--top-h:50px}
    .crt-top{padding:0 10px;gap:7px}
    .crt-title{font-size:13px}
    .crt-pulse{display:none}
    .crt-panel-btn{display:none}
    .crt-panel{
        position:fixed;top:auto;left:0;right:0;bottom:0;
        width:100%;max-height:60vh;
        border-right:none;border-top:1px solid var(--border);
        border-radius:16px 16px 0 0;
        box-shadow:0 -6px 32px rgba(0,0,0,.15);
        transform:translateY(100%);
        z-index:410;
    }
    .crt-panel.show{transform:translateY(0)}
    .crt-panel-drag{display:flex}
    .crt-fab{display:flex}
    .crt-panel-close{display:flex;top:8px;right:8px}
    .crt-count{display:none}
}
</style>
@endpush

@section('content')

<div class="crt-panel-overlay" id="crtPanelOv"></div>

<div class="crt-wrap">

    {{-- Topbar --}}
    <div class="crt-top">
        <a href="{{ route('employe.orders.index') }}" class="crt-back">← Commandes</a>
        <span class="crt-title">🗺️ Carte en direct</span>
        <span class="crt-sub">· {{ $shop->name ?? 'Boutique' }}</span>
        <div class="crt-right">
            <div class="crt-pulse">
                <span class="crt-dot"></span>
                Temps réel
            </div>
            <span class="crt-count" id="crtCount">0 en cours</span>
            <button class="crt-panel-btn" id="crtPanelBtn" onclick="togglePanel()">
                🚴 <span id="crtPanelLbl">Livreurs</span>
            </button>
        </div>
    </div>

    <div class="crt-body">

        {{-- Panneau livreurs --}}
        <div class="crt-panel" id="crtPanel">
            <div class="crt-panel-drag" id="crtDrag"></div>
            <button class="crt-panel-close" onclick="togglePanel()">✕</button>
            <div class="crt-panel-hd">
                <div class="crt-panel-title">🚴 Livreurs en mission</div>
                <div class="crt-panel-sub" id="crtPanelSub">Chargement…</div>
            </div>
            <div class="crt-panel-list" id="crtPanelList">
                <div class="cp-empty">
                    <div class="cp-empty-ico">🗺️</div>
                    <div class="cp-empty-title">Chargement…</div>
                </div>
            </div>
        </div>

        {{-- Carte --}}
        <div class="crt-map-wrap">
            <div id="map"></div>

            <button class="crt-fab" id="crtFab" onclick="togglePanel()">
                🚴 <span id="crtFabLbl">Livreurs</span>
                <span class="crt-fab-count" id="crtFabCnt">0</span>
            </button>

            <div class="crt-no-signal" id="crtNoSignal">
                📡 Aucun livreur avec GPS actif pour le moment
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
/* ── Panneau (tablette/mobile) ── */
const panel   = document.getElementById('crtPanel');
const panelOv = document.getElementById('crtPanelOv');
const panelLbl= document.getElementById('crtPanelLbl');
let   panelOpen = false;
function togglePanel() {
    panelOpen = !panelOpen;
    panel?.classList.toggle('show', panelOpen);
    panelOv?.classList.toggle('open', panelOpen);
    if (panelLbl) panelLbl.textContent = panelOpen ? 'Masquer' : 'Livreurs';
    document.getElementById('crtFab')?.classList.toggle('hidden', panelOpen);
    setTimeout(() => map.invalidateSize(), 320);
}
panelOv?.addEventListener('click', () => { panelOpen = true; togglePanel(); });

/* Drag handle (bottom sheet) */
const drag = document.getElementById('crtDrag');
if (drag) {
    let sy = 0;
    drag.addEventListener('touchstart', e => { sy = e.touches[0].clientY; }, {passive:true});
    drag.addEventListener('touchend',   e => { if (e.changedTouches[0].clientY - sy > 40) { panelOpen = true; togglePanel(); }}, {passive:true});
}

/* ── Couleurs ── */
const COLORS = ['#6366f1','#10b981','#f59e0b','#3b82f6','#ec4899','#8b5cf6','#06b6d4','#f97316','#ef4444','#84cc16','#14b8a6','#f43f5e'];
let colorMap = {}, colorIdx = 0;
function getColor(key) {
    if (colorMap[key] === undefined) colorMap[key] = colorIdx++;
    return COLORS[colorMap[key] % COLORS.length];
}

/* ── Icône livreur ── */
function driverIcon(color, isMoving) {
    const pulse = isMoving
        ? `<circle cx="16" cy="16" r="14" fill="${color}" opacity=".13"><animate attributeName="r" from="11" to="18" dur="1.5s" repeatCount="indefinite"/><animate attributeName="opacity" from=".25" to="0" dur="1.5s" repeatCount="indefinite"/></circle>`
        : '';
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
        ${pulse}
        <circle cx="16" cy="16" r="11" fill="${color}" stroke="#fff" stroke-width="2.5"/>
        <text x="16" y="20.5" font-size="11" text-anchor="middle" fill="#fff" font-weight="900" font-family="Segoe UI,sans-serif">🚴</text>
    </svg>`;
    return L.divIcon({html:svg, iconSize:[32,32], iconAnchor:[16,16], popupAnchor:[0,-18], className:''});
}

/* ── Leaflet ── */
const map = L.map('map', {center:[9.537,-13.677], zoom:13, zoomControl:true});
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution:'© OpenStreetMap', maxZoom:19}).addTo(map);
map.zoomControl.setPosition('bottomright');
window.addEventListener('resize', () => map.invalidateSize());

/* ── State ── */
const markers  = {};
const traces   = {};
const polylines= {};
let   selectedKey = null;

/* ── Rendu panneau ── */
function renderPanel(drivers) {
    const list  = document.getElementById('crtPanelList');
    const sub   = document.getElementById('crtPanelSub');
    const cnt   = document.getElementById('crtCount');
    const fab   = document.getElementById('crtFabCnt');
    const fabLbl= document.getElementById('crtFabLbl');
    const n = drivers.length;
    if (cnt)    cnt.textContent  = n + ' en cours';
    if (fab)    fab.textContent  = n;
    if (fabLbl) fabLbl.textContent = n ? n + ' livreur' + (n > 1 ? 's' : '') : 'Aucun actif';

    if (!n) {
        list.innerHTML = `<div class="cp-empty"><div class="cp-empty-ico">🚚</div><div class="cp-empty-title">Aucune livraison en cours</div><div style="font-size:11.5px;color:var(--muted);margin-top:3px">Les livreurs apparaîtront ici dès qu'une commande est confirmée.</div></div>`;
        if (sub) sub.textContent = 'Aucune livraison active';
        return;
    }
    if (sub) sub.textContent = n + ' livreur' + (n > 1 ? 's' : '') + ' actif' + (n > 1 ? 's' : '');

    list.innerHTML = drivers.map(o => {
        const color  = getColor(o.key);
        const isLiv  = o.status === 'en_livraison';
        const badge  = isLiv
            ? `<span class="cp-badge cp-badge-liv">En route</span>`
            : `<span class="cp-badge cp-badge-conf">Assigné</span>`;
        const hasGps = o.lat && o.lng;
        const pingAge = hasGps ? Date.now()/1000 - new Date(o.ping).getTime()/1000 : null;
        const pingCls = pingAge !== null && pingAge < 30 ? 'cp-ping-ok' : 'cp-ping-stale';
        const gpsLine = hasGps
            ? `<div class="cp-ping"><span class="${pingCls}">📡 ${esc(o.ping_ago)}</span></div>`
            : `<div class="cp-no-gps">📡 GPS non encore reçu</div>`;
        const typeLabel = o.type === 'company' ? '🏢 Entreprise' : '🏪 Boutique';
        // Résumé en tête de carte : total commandes
        const orderLabel = o.order_count > 1
            ? `<span class="cp-id">${o.order_count} cde${o.order_count > 1 ? 's' : ''}</span>`
            : `<span class="cp-id">#${String((o.trips?.[0]?.orders?.[0] || o.orders?.[0] || {}).id || '').padStart(5,'0')}</span>`;
        // Lignes de trajets (batch groupé vs individuel)
        const tripsHtml = (o.trips && o.trips.length)
            ? o.trips.map(t => {
                if (t.type === 'batch') {
                    const dest = t.destination ? ` → ${esc(t.destination)}` : '';
                    return `<div class="cp-dest">📦 ${t.count} cdes · 1 trajet${dest}</div>`;
                } else {
                    const ord = t.orders[0] || {};
                    const num = String(ord.id || '').padStart(5,'0');
                    const dest = ord.destination ? ` · ${esc(ord.destination)}` : '';
                    return `<div class="cp-dest">📌 #${num}${dest}</div>`;
                }
            }).join('')
            : (o.destination ? `<div class="cp-dest">📍 ${esc(o.destination)}</div>` : '');
        const sel = selectedKey === o.key ? ' selected' : '';
        return `<div class="cp-card${sel}" onclick="focusDriver('${o.key}')" data-key="${o.key}">
            <div class="cp-card-top">
                <span class="cp-color" style="background:${color}"></span>
                ${orderLabel}
                <span class="cp-name">${esc(o.driver)}</span>
                ${badge}
            </div>
            <div class="cp-type">${typeLabel}${o.driver_phone ? ' · ' + esc(o.driver_phone) : ''}</div>
            ${tripsHtml}
            ${gpsLine}
        </div>`;
    }).join('');
}

/* ── Mise à jour carte ── */
function updateMap(drivers) {
    const activeKeys = new Set(drivers.map(o => o.key));
    Object.keys(markers).forEach(k => {
        if (!activeKeys.has(k)) {
            map.removeLayer(markers[k]); delete markers[k];
            if (polylines[k]) { map.removeLayer(polylines[k]); delete polylines[k]; }
            delete traces[k];
        }
    });

    let hasGps = false;
    const bounds = [];

    drivers.forEach(o => {
        if (!o.lat || !o.lng) return;
        hasGps = true;
        const color    = getColor(o.key);
        const isMoving = o.status === 'en_livraison';
        const pos      = [parseFloat(o.lat), parseFloat(o.lng)];
        const k        = o.key;

        if (!traces[k]) traces[k] = [];
        const last = traces[k].at(-1);
        if (!last || last[0] !== pos[0] || last[1] !== pos[1]) traces[k].push(pos);

        if (traces[k].length > 1) {
            if (polylines[k]) { polylines[k].setLatLngs(traces[k]); }
            else { polylines[k] = L.polyline(traces[k], {color, weight:3, opacity:.7, dashArray: isMoving ? null : '5,5'}).addTo(map); }
        }

        if (markers[k]) {
            markers[k].setLatLng(pos);
            markers[k].setIcon(driverIcon(color, isMoving));
        } else {
            markers[k] = L.marker(pos, {icon: driverIcon(color, isMoving)}).addTo(map).bindPopup(() => buildPopup(o));
        }
        if (markers[k].isPopupOpen()) markers[k].setPopupContent(buildPopup(o));
        bounds.push(pos);
    });

    document.getElementById('crtNoSignal')?.classList.toggle('show', !hasGps && drivers.length > 0);

    if (bounds.length > 0 && selectedKey === null) {
        bounds.length === 1
            ? map.setView(bounds[0], Math.max(map.getZoom(), 15))
            : map.fitBounds(bounds, {padding:[40,40], maxZoom:16});
    }
}

/* ── Popup ── */
function buildPopup(o) {
    const color = getColor(o.key);
    const typeLabel = o.type === 'company' ? '🏢 Entreprise' : '🏪 Boutique';
    let ordersHtml = '';
    if (o.trips && o.trips.length) {
        ordersHtml = o.trips.map((t, i) => {
            const sep = i > 0 ? '<hr style="margin:5px 0;border:none;border-top:1px solid #e2e8f0">' : '';
            if (t.type === 'batch') {
                const lines = t.orders.map(ord =>
                    `<div class="lp-row"><span>Cde&nbsp;</span><strong>#${String(ord.id).padStart(5,'0')} · ${esc(ord.client)}</strong>${ord.destination ? `<span style="color:var(--muted)"> · ${esc(ord.destination)}</span>` : ''}</div>`
                ).join('');
                return `${sep}<div class="lp-row" style="margin-bottom:2px"><strong>📦 Lot · ${t.count} cdes</strong></div>${lines}`;
            } else {
                const ord = t.orders[0] || {};
                return `${sep}<div class="lp-row"><span>Commande&nbsp;</span><strong>#${String(ord.id||'').padStart(5,'0')}</strong></div>
                    <div class="lp-row"><span>Client&nbsp;</span><strong>${esc(ord.client)}</strong></div>${ord.destination ? `<div class="lp-row"><span>Dest.&nbsp;</span><strong>${esc(ord.destination)}</strong></div>` : ''}`;
            }
        }).join('');
    } else if (o.orders && o.orders.length > 1) {
        ordersHtml = o.orders.map(ord =>
            `<div class="lp-row"><span>Cde&nbsp;</span><strong>#${ord.id} · ${esc(ord.client)}</strong>${ord.destination ? `<span style="color:var(--muted)"> · ${esc(ord.destination)}</span>` : ''}</div>`
        ).join('');
    } else if (o.orders && o.orders.length === 1) {
        const ord = o.orders[0];
        ordersHtml = `<div class="lp-row"><span>Commande&nbsp;</span><strong>#${ord.id}</strong></div>
            <div class="lp-row"><span>Client&nbsp;</span><strong>${esc(ord.client)}</strong></div>`;
    }
    return `<div class="lp-title" style="color:${color}">🚴 ${esc(o.driver)}</div>
        <div class="lp-row" style="margin-bottom:3px"><span style="font-size:10.5px;color:var(--muted)">${typeLabel}</span></div>
        ${ordersHtml}
        <div class="lp-row" style="margin-top:4px"><span>GPS&nbsp;</span><strong>${esc(o.ping_ago)}</strong></div>`;
}

/* ── Focus livreur ── */
function focusDriver(key) {
    selectedKey = (selectedKey === key) ? null : key;
    document.querySelectorAll('.cp-card').forEach(c => c.classList.toggle('selected', c.dataset.key === selectedKey));
    if (selectedKey && markers[selectedKey]) {
        map.setView(markers[selectedKey].getLatLng(), 16, {animate:true});
        markers[selectedKey].openPopup();
        if (window.innerWidth <= 640 && panelOpen) { panelOpen = true; togglePanel(); }
    }
}

/* ── Polling ── */
async function poll() {
    try {
        const res = await fetch('{{ route('employe.orders.carte.data') }}', {credentials:'same-origin', headers:{'Accept':'application/json'}});
        if (!res.ok) return;
        const d = await res.json();
        if (!d.ok) return;
        renderPanel(d.drivers);
        updateMap(d.drivers);
    } catch(e) {}
}

function esc(s){ return String(s??'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

poll();
setInterval(poll, 4000);
</script>
@endpush
