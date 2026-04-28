@extends('layouts.app')
@section('title', 'Dashboard · ShipXpress')
@php $bodyClass = 'cx-dashboard'; @endphp

@push('styles')
{{-- Anti-flash : applique le thème sauvegardé avant le premier paint --}}
<script>
(function(){
    if(localStorage.getItem('cx-theme')==='light')
        document.documentElement.classList.add('cx-prelight');
})();
</script>
<style>
html.cx-prelight body { background:#F5F7FA !important; }
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
/* ═══════════════════════════════════════════════════════════
   ShipXpress — Company Dashboard — Dark Theme
═══════════════════════════════════════════════════════════ */
*,*::before,*::after { box-sizing:border-box; }

:root {
    --cx-bg:        #0b0d22;
    --cx-surface:   #0d1226;
    --cx-surface2:  #111930;
    --cx-sb:        #06070f;
    --cx-border:    rgba(255,255,255,.07);
    --cx-brand:     #7c3aed;
    --cx-brand2:    #6d28d9;
    --cx-brand-lt:  #a78bfa;
    --cx-brand-mlt: rgba(124,58,237,.12);
    --cx-text:      #e2e8f0;
    --cx-text2:     #94a3b8;
    --cx-muted:     #475569;
    --cx-green:     #10b981;
    --cx-blue:      #3b82f6;
    --cx-orange:    #f59e0b;
    --cx-red:       #ef4444;
    --cx-sb-w:      220px;
    --sb-w:         220px;
    --cx-top-h:     60px;
    --r:            12px;
    --r-sm:         8px;
}

html,body {
    margin:0; font-family:'Segoe UI',sans-serif;
    background:var(--cx-bg) !important;
    color:var(--cx-text);
    -webkit-font-smoothing:antialiased;
}
a { text-decoration:none; color:inherit; }

/* Cacher la navbar du layout pour ce dashboard */
body.cx-dashboard > nav,
body.cx-dashboard > header,
body.cx-dashboard .navbar,
body.cx-dashboard > .topbar-global { display:none !important; }

/* Neutraliser le container Bootstrap du layout (container-xxl) */
body.cx-dashboard > main.app-main {
    padding: 0 !important;
    margin:  0 !important;
    max-width: 100% !important;
    width: 100% !important;
}

/* ══ STRUCTURE PRINCIPALE ══ */
.cx-wrap { display:flex; min-height:100vh; padding-left:var(--cx-sb-w); }

/* ══ SIDEBAR ══ */
.cx-sidebar {
    position:fixed; top:0; left:0; bottom:0; width:var(--cx-sb-w);
    background: linear-gradient(180deg, #0f0f59 0%, #0e0e16 40%, #10103a 100%); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; width: var(--sb-w); overflow-y: scroll; scrollbar-width: thin; scrollbar-color: rgba(99,102,241,.35) transparent; z-index: 40; border-right: 1px solid rgba(99,102,241,.15); box-shadow: 6px 0 30px rgba(0,0,0,.35); -webkit-font-smoothing: antialiased; text-rendering: optimizeLegibility; 
    border-right:1px solid rgba(99,102,241,.1);
    backdrop-filter: blur(6px); /* effet moderne */
    display:flex; flex-direction:column;
    z-index:1200; overflow-y:auto;
    scrollbar-width:thin; scrollbar-color:rgba(124,58,237,.3) transparent;
    transition:transform .25s cubic-bezier(.23,1,.32,1);
}

.cx-sidebar a {
     color: #c7d2fe;
    transition: all .2s ease;
}
.cx-sidebar a:hover {
     background: rgba(99,102,241,.15);
    color: #fff;
}
.cx-sidebar  a.active{
     background: linear-gradient(90deg, #6366f1, #4f46e5);
    color: #fff;
}
.cx-sidebar::-webkit-scrollbar { width:3px; }
.cx-sidebar::-webkit-scrollbar-thumb { background:rgba(124,58,237,.3); border-radius:3px; }

/* Brand */
.cx-brand-hd {
    padding:14px 14px 10px; border-bottom:1px solid rgba(255,255,255,.06);
    flex-shrink:0; display:flex; flex-direction:column; gap:8px;
}
.cx-brand-top { display:flex; align-items:center; justify-content:space-between; }
.cx-logo {
    display:flex; align-items:center; gap:9px;
    color:#fff; font-size:16px; font-weight:800;
}
.cx-logo-icon {
    width:34px; height:34px; border-radius:9px;
    background:linear-gradient(135deg,#7c3aed,#4f46e5);
    display:flex; align-items:center; justify-content:center;
    font-size:16px; flex-shrink:0;
}
.cx-sys-badge {
    display:inline-flex; align-items:center; gap:5px;
    font-size:10px; font-weight:600; color:var(--cx-green);
    padding:3px 8px; border-radius:20px;
    background:rgba(16,185,129,.1); border:1px solid rgba(16,185,129,.2);
}
.cx-sys-dot {
    width:6px; height:6px; border-radius:50%;
    background:var(--cx-green);
    animation:blink 2.2s ease-in-out infinite;
    flex-shrink:0;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.25} }

.cx-close-btn {
    display:none; background:none; border:none;
    color:rgba(255,255,255,.45); font-size:18px;
    cursor:pointer; padding:2px 6px; border-radius:6px;
    line-height:1; transition:color .15s;
}
.cx-close-btn:hover { color:#fff; }

/* Navigation */
.cx-nav { padding:8px 8px 12px; flex:1; }
.cx-nav-sec {
    font-size:9px; font-weight:800; letter-spacing:1.8px;
    color:rgba(255,255,255,.3); padding:14px 10px 4px;
    text-transform:uppercase;
}
.cx-nav-item {
    display:flex; align-items:center; gap:9px;
    padding:7px 10px; border-radius:var(--r-sm);
    color:rgba(255,255,255,.68); font-size:13px; font-weight:500;
    transition:background .14s, color .14s; position:relative;
    cursor:pointer; margin-bottom:1px;
}
.cx-nav-item:hover { background:rgba(255,255,255,.06); color:#fff; }
.cx-nav-item.active {
    background:rgba(124,58,237,.22); color:#fff; font-weight:700;
}
.cx-nav-item.active::before {
    content:''; position:absolute; left:0; top:50%; transform:translateY(-50%);
    width:3px; height:18px; background:#8b5cf6; border-radius:0 3px 3px 0;
    box-shadow:2px 0 8px rgba(139,92,246,.5);
}
.cx-nav-ico {
    width:24px; height:24px; border-radius:6px;
    background:rgba(255,255,255,.05);
    display:flex; align-items:center; justify-content:center;
    font-size:12px; flex-shrink:0;
}
.cx-nav-item.active .cx-nav-ico { background:rgba(139,92,246,.18); }
.cx-nav-badge {
    margin-left:auto; background:var(--cx-brand); color:#fff;
    font-size:9.5px; font-weight:700; padding:1px 6px;
    border-radius:20px; min-width:18px; text-align:center;
}

/* User foot */
.cx-user-foot {
    padding:10px 10px 12px; border-top:1px solid rgba(255,255,255,.07);
    flex-shrink:0;
}
.cx-user-row {
    display:flex; align-items:center; gap:9px;
    padding:7px 8px; border-radius:var(--r-sm);
    background:rgba(255,255,255,.04); cursor:pointer;
    transition:background .15s; margin-bottom:6px;
}
.cx-user-row:hover { background:rgba(255,255,255,.08); }
.cx-user-av {
    width:32px; height:32px; border-radius:50%;
    background:linear-gradient(135deg,#7c3aed,#4338ca);
    display:flex; align-items:center; justify-content:center;
    font-size:11px; font-weight:800; color:#fff; flex-shrink:0;
}
.cx-user-name { font-size:12px; font-weight:700; color:#fff; line-height:1.2; }
.cx-user-role { font-size:10px; color:var(--cx-text2); }
.cx-logout-btn {
    width:30px; height:30px; border-radius:8px; flex-shrink:0;
    background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.2);
    display:flex; align-items:center; justify-content:center;
    color:#f87171; cursor:pointer; transition:background .15s, color .15s;
    padding:0;
}
.cx-logout-btn:hover { background:rgba(239,68,68,.22); color:#fff; }
.cx-dark-row {
    display:flex; align-items:center; justify-content:space-between;
    padding:4px 8px; cursor:pointer;
}
.cx-dark-lbl { font-size:11.5px; color:var(--cx-text2); }
.cx-toggle {
    width:34px; height:18px; background:#475569;
    border-radius:9px; position:relative; transition:background .25s;
    flex-shrink:0;
}
.cx-toggle::after {
    content:''; position:absolute; top:3px; left:3px;
    width:12px; height:12px; background:#fff; border-radius:50%;
    transition:left .25s;
}
.cx-toggle.on { background:var(--cx-brand); }
.cx-toggle.on::after { left:19px; }

/* ══ MODE CLAIR ══ */
body.cx-light {
    --cx-bg:        #F5F7FA;
    --cx-surface:   #ffffff;
    --cx-surface2:  #eef1f7;
    --cx-border:    rgba(0,0,0,.08);
    --cx-text:      #111827;
    --cx-text2:     #4b5563;
    --cx-muted:     #9ca3af;
    --cx-brand-mlt: rgba(124,58,237,.07);
}
body.cx-light,
html.cx-light,
html.cx-light body { background:#F5F7FA !important; }

/* Topbar clair */
body.cx-light .cx-topbar {
    background:#fff;
    border-bottom-color:rgba(0,0,0,.07);
    box-shadow:0 1px 4px rgba(0,0,0,.06);
}
body.cx-light .cx-tb-btn {
    background:rgba(0,0,0,.05); border-color:rgba(0,0,0,.08); color:#374151;
}
body.cx-light .cx-tb-btn:hover { background:rgba(0,0,0,.1); color:#111; }
body.cx-light .cx-tb-uname  { color:#111827; }
body.cx-light .cx-tb-urole  { color:#6b7280; }
body.cx-light .cx-tb-status {
    background:rgba(16,185,129,.07); border-color:rgba(16,185,129,.2);
}
body.cx-light .cx-search {
    background:rgba(0,0,0,.04); border-color:rgba(0,0,0,.09);
}
body.cx-light .cx-search input { color:#111; }
body.cx-light .cx-search input::placeholder { color:#9ca3af; }
body.cx-light .cx-kbd {
    background:rgba(0,0,0,.06); border-color:rgba(0,0,0,.1); color:#6b7280;
}
body.cx-light .cx-date-badge {
    background:rgba(0,0,0,.05); border-color:rgba(0,0,0,.1); color:#374151;
}

/* Stats clairs */
body.cx-light .cx-stat {
    background:#fff;
    border-color:rgba(0,0,0,.07);
    box-shadow:0 1px 3px rgba(0,0,0,.07), 0 4px 12px rgba(0,0,0,.04);
}
body.cx-light .cx-stat:hover {
    box-shadow:0 4px 16px rgba(0,0,0,.1), 0 1px 4px rgba(0,0,0,.06);
}
body.cx-light .cx-stat-val { color:#111827; }

/* Panels clairs */
body.cx-light .cx-panel {
    background:#fff;
    border-color:rgba(0,0,0,.07);
    box-shadow:0 1px 3px rgba(0,0,0,.06), 0 4px 12px rgba(0,0,0,.03);
}
body.cx-light .cx-panel-hd { border-bottom-color:rgba(0,0,0,.06); }
body.cx-light .cx-panel-title { color:#111827; }
body.cx-light .cx-panel-link { color:var(--cx-brand); }

/* Chat items clairs */
body.cx-light .cx-chat-item { border-bottom-color:rgba(0,0,0,.05); }
body.cx-light .cx-chat-item:hover { background:rgba(0,0,0,.03); }
body.cx-light .cx-chat-name { color:#111827; }
body.cx-light .cx-chat-msg  { color:#4b5563; }
body.cx-light .cx-chat-time { color:#9ca3af; }
body.cx-light .cx-chat-foot { }
body.cx-light .cx-chat-cta  {
    background:rgba(124,58,237,.06); border-color:rgba(124,58,237,.15);
}

/* Pipeline clair */
body.cx-light .cx-pipe-fill { opacity:.75; }
body.cx-light .cx-pipe-val  { color:#111827; }

/* Livreurs clairs */
body.cx-light .cx-driv-card {
    background:var(--cx-surface2); border-color:rgba(0,0,0,.07);
}
body.cx-light .cx-driv-name { color:#111827; }

/* Charts clairs */
body.cx-light .cx-chart-wrap {
    background:#fff; border-color:rgba(0,0,0,.07);
    box-shadow:0 1px 3px rgba(0,0,0,.06), 0 4px 12px rgba(0,0,0,.03);
}
body.cx-light .cx-chart-title { color:#111827; }
body.cx-light .cx-rev-big     { color:#111827; }

/* Performances claires */
body.cx-light .cx-perf-card {
    background:#F5F7FA; border-color:rgba(0,0,0,.07);
    box-shadow:0 1px 2px rgba(0,0,0,.05);
}
body.cx-light .cx-perf-val { color:#111827; }
body.cx-light .cx-perf-lbl { color:#4b5563; }

/* Notification dropdown clair */
body.cx-light .cx-notif-panel {
    background:#fff; border-color:rgba(0,0,0,.1);
    box-shadow:0 12px 40px rgba(0,0,0,.12);
}
body.cx-light .cx-notif-panel-hd  { border-bottom-color:rgba(0,0,0,.07); }
body.cx-light .cx-notif-panel-title { color:#111827; }
body.cx-light .cx-notif-item { border-bottom-color:rgba(0,0,0,.05); }
body.cx-light .cx-notif-item:hover { background:rgba(0,0,0,.03); }
body.cx-light .cx-notif-name  { color:#111827; }
body.cx-light .cx-notif-msg   { color:#4b5563; }
body.cx-light .cx-notif-time  { color:#9ca3af; }
body.cx-light .cx-notif-footer { border-top-color:rgba(0,0,0,.07); }
body.cx-light .cx-notif-empty { color:#9ca3af; }

/* Label dark mode toggle en mode clair */
body.cx-light .cx-dark-lbl { color:#4b5563; }

/* Éléments avec rgba blancs à inverser en mode clair */
body.cx-light .cx-driver:hover { background:rgba(0,0,0,.03); }
body.cx-light .cx-pipe-track   { background:rgba(0,0,0,.07); }
body.cx-light .cx-pipe-lbl     { color:#4b5563; }
body.cx-light .cx-driv-loc     { color:#6b7280; }
body.cx-light .cx-driv-rating  { color:#d97706; }
body.cx-light .cx-driv-phone   { background:rgba(0,0,0,.05); border-color:rgba(0,0,0,.09); color:#374151; }
body.cx-light .cx-driv-phone:hover { background:rgba(16,185,129,.1); }
body.cx-light .cx-chart-sel    { background:#fff; border-color:rgba(0,0,0,.1); color:#374151; }
body.cx-light .cx-rev-sub      { color:#059669; }
body.cx-light .cx-map-legend   { border-top-color:rgba(0,0,0,.06); }
body.cx-light .cx-legend-item  { color:#4b5563; }
body.cx-light .cx-perf-trend   { }
body.cx-light .cx-stat-lbl     { color:#4b5563; }
body.cx-light .cx-stat-trend   { color:#059669; }
/* Fond Leaflet en mode clair */
body.cx-light .leaflet-container { background:#e8ecf0 !important; }
/* Topbar en mode clair : fond blanc net */
body.cx-light .cx-topbar { background:#fff; box-shadow:0 1px 0 rgba(0,0,0,.07); }
/* Wrap global en mode clair */
body.cx-light .cx-wrap { background:#F5F7FA; }

/* ══ OVERLAY ══ */
.cx-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.6); z-index:1100;
}
.cx-overlay.open { display:block; }

/* ══ MAIN ══ */
.cx-main {
    display:flex; flex-direction:column;
    min-height:100vh; min-width:0; flex:1;
    background:#0b0d22;
}
body.cx-light .cx-main { background:#F5F7FA; }

/* ══ TOPBAR ══ */
.cx-topbar {
    height:var(--cx-top-h);
    background:var(--cx-surface);
    border-bottom:1px solid var(--cx-border);
    display:flex; align-items:center; gap:12px;
    padding:0 20px; position:sticky; top:0; z-index:1050;
    flex-shrink:0;
}
.cx-hamburger {
    display:none; background:none; border:none;
    color:var(--cx-text); font-size:20px; cursor:pointer; padding:4px;
    line-height:1;
}
.cx-search {
    flex:1; max-width:420px;
    display:flex; align-items:center; gap:8px;
    background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.08);
    border-radius:var(--r-sm); padding:8px 14px; cursor:text;
    transition:border-color .15s;
}
.cx-search:focus-within { border-color:rgba(139,92,246,.4); }
.cx-search input {
    flex:1; background:none; border:none; outline:none;
    color:var(--cx-text); font-size:13px; font-family:inherit;
}
.cx-search input::placeholder { color:var(--cx-muted); }
.cx-kbd {
    background:rgba(255,255,255,.08); color:var(--cx-text2);
    font-size:10px; font-weight:600; padding:2px 6px;
    border-radius:4px; white-space:nowrap; flex-shrink:0;
}

.cx-tb-right { display:flex; align-items:center; gap:10px; margin-left:auto; }
.cx-tb-status {
    display:inline-flex; align-items:center; gap:5px;
    font-size:11px; font-weight:600; color:var(--cx-green);
    padding:4px 10px; border-radius:20px;
    background:rgba(16,185,129,.1); border:1px solid rgba(16,185,129,.2);
}
.cx-tb-btn {
    position:relative; width:36px; height:36px; border-radius:10px;
    background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.08);
    display:flex; align-items:center; justify-content:center;
    color:var(--cx-text2); font-size:16px; cursor:pointer;
    transition:background .14s, color .14s; flex-shrink:0;
}
.cx-tb-btn:hover { background:rgba(255,255,255,.1); color:#fff; }
.cx-notif-dot {
    position:absolute; top:-6px; right:-6px;
    background:var(--cx-red); color:#fff;
    font-size:10px; font-weight:800; line-height:1.3;
    padding:1px 5px; border-radius:20px;
    min-width:18px; text-align:center;
    border:2px solid var(--cx-surface);
    white-space:nowrap; pointer-events:none;
}
.cx-tb-user {
    display:flex; align-items:center; gap:8px; cursor:pointer;
    padding:5px 10px; border-radius:var(--r-sm);
    transition:background .14s; flex-shrink:0;
}
.cx-tb-user:hover { background:rgba(255,255,255,.06); }
.cx-tb-av {
    width:32px; height:32px; border-radius:50%;
    background:linear-gradient(135deg,#7c3aed,#4338ca);
    display:flex; align-items:center; justify-content:center;
    font-size:11px; font-weight:800; color:#fff; flex-shrink:0;
}
.cx-tb-uname { font-size:12.5px; font-weight:700; color:var(--cx-text); line-height:1.2; }
.cx-tb-urole { font-size:10px; color:var(--cx-text2); }
.cx-date-badge {
    display:flex; align-items:center; gap:6px; white-space:nowrap;
    font-size:11.5px; font-weight:600; color:var(--cx-text2);
    background:var(--cx-surface2); border:1px solid var(--cx-border);
    padding:5px 12px; border-radius:var(--r-sm); flex-shrink:0;
}

/* ══ CONTENT ══ */
.cx-content { padding:16px 16px 40px; display:flex; flex-direction:column; gap:14px; }

/* ══ STATS ══ */
.cx-stats { display:flex; gap:14px; }
.cx-stat {
    flex:1; min-width:0;
    background:var(--cx-surface); border:1px solid var(--cx-border);
    border-radius:var(--r); padding:18px 18px 16px;
    display:flex; align-items:center; gap:14px;
    transition:transform .18s, box-shadow .18s;
    position:relative; overflow:hidden; cursor:default;
}
.cx-stat::after {
    content:''; position:absolute; top:0; left:0; right:0; height:2px;
    background:var(--s-color, var(--cx-brand));
}
.cx-stat:hover { transform:translateY(-2px); box-shadow:0 10px 30px rgba(0,0,0,.35); }
.cx-stat-ico {
    width:46px; height:46px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    font-size:20px; flex-shrink:0;
    background:var(--s-ico-bg, rgba(124,58,237,.12));
}
.cx-stat-lbl { font-size:11px; font-weight:600; color:var(--cx-text2); margin-bottom:4px; }
.cx-stat-val  { font-size:26px; font-weight:900; color:#fff; line-height:1.1; letter-spacing:-.5px; }
.cx-stat-val.sm { font-size:18px; }
.cx-stat-trend { font-size:11px; color:var(--cx-green); font-weight:600; margin-top:4px; }

/* ══ MAIN GRID (carte large + colonne droite) ══ */
.cx-main-grid {
    display:grid;
    grid-template-columns: 1fr 340px;
    gap:14px; align-items:stretch;
}
.cx-map-panel { display:flex; flex-direction:column; }
.cx-right-stack { display:flex; flex-direction:column; gap:16px; }

/* Panel base */
.cx-panel {
    background:var(--cx-surface); border:1px solid var(--cx-border);
    border-radius:var(--r); overflow:hidden;
}
.cx-panel-hd {
    padding:13px 18px 11px; border-bottom:1px solid var(--cx-border);
    display:flex; align-items:center; justify-content:space-between; gap:8px;
}
.cx-panel-title {
    font-size:13px; font-weight:800; color:#fff;
    display:flex; align-items:center; gap:7px;
}
.cx-panel-link {
    font-size:11px; font-weight:600; color:var(--cx-brand-lt);
    padding:3px 8px; border-radius:5px; transition:background .14s;
}
.cx-panel-link:hover { background:var(--cx-brand-mlt); color:#fff; }

/* Chat list */
.cx-chat-item {
    display:flex; align-items:flex-start; gap:10px;
    padding:10px 16px; cursor:pointer;
    border-bottom:1px solid rgba(255,255,255,.04);
    transition:background .14s;
}
.cx-chat-item:last-child { border-bottom:none; }
.cx-chat-item:hover { background:rgba(255,255,255,.04); }
.cx-chat-av {
    width:38px; height:38px; border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-size:12px; font-weight:700; color:#fff; flex-shrink:0;
}
.cx-chat-body { flex:1; min-width:0; }
.cx-chat-row  { display:flex; align-items:center; justify-content:space-between; gap:6px; }
.cx-chat-name { font-size:12.5px; font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.cx-chat-time { font-size:10.5px; color:var(--cx-text2); flex-shrink:0; }
.cx-chat-msg  { font-size:11.5px; color:var(--cx-text2); margin-top:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.cx-chat-foot { margin-top:4px; }

/* Badges */
.cx-badge {
    display:inline-block; font-size:9.5px; font-weight:700;
    padding:2px 8px; border-radius:20px;
}
.b-wait   { background:rgba(234,179,8,.14); color:#fbbf24; border:1px solid rgba(234,179,8,.28); }
.b-new    { background:rgba(59,130,246,.14); color:#60a5fa; border:1px solid rgba(59,130,246,.28); }
.b-assign { background:rgba(124,58,237,.18); color:#a78bfa; border:1px solid rgba(124,58,237,.3); }
.b-done   { background:rgba(16,185,129,.14); color:#34d399; border:1px solid rgba(16,185,129,.28); }

/* Chat CTA */
.cx-chat-cta {
    margin:10px 14px 12px;
    display:flex; align-items:center; justify-content:center; gap:6px;
    padding:9px; background:var(--cx-brand-mlt);
    border:1px solid rgba(124,58,237,.22); border-radius:var(--r-sm);
    color:var(--cx-brand-lt); font-size:12.5px; font-weight:700;
    cursor:pointer; transition:background .14s;
}
.cx-chat-cta:hover { background:rgba(124,58,237,.2); }

/* Map */
#cxMap { flex:1; min-height:420px; }
.cx-map-legend {
    padding:9px 14px; border-top:1px solid var(--cx-border);
    display:flex; align-items:center; gap:14px; flex-wrap:wrap;
}
.cx-legend-item { display:flex; align-items:center; gap:5px; font-size:11px; color:var(--cx-text2); }
.cx-legend-dot  { width:9px; height:9px; border-radius:50%; flex-shrink:0; }

/* Leaflet popup override */
.leaflet-popup-content-wrapper { background:transparent!important; box-shadow:none!important; border:none!important; padding:0!important; }
.leaflet-popup-tip-container { display:none; }
.leaflet-container { background:#0d1424!important; }

/* Pipeline */
.cx-pipe-list { padding:12px 16px; display:flex; flex-direction:column; gap:10px; }
.cx-pipe-item { display:flex; flex-direction:column; gap:4px; }
.cx-pipe-row  { display:flex; align-items:center; justify-content:space-between; }
.cx-pipe-lbl  { font-size:11.5px; font-weight:600; color:var(--cx-text2); }
.cx-pipe-val  { font-size:13px; font-weight:800; color:#fff; }
.cx-pipe-track { height:5px; background:rgba(255,255,255,.07); border-radius:3px; overflow:hidden; }
.cx-pipe-fill  { height:100%; border-radius:3px; width:0; transition:width .9s cubic-bezier(.23,1,.32,1); }

/* Livreurs actifs */
.cx-driver {
    display:flex; align-items:center; gap:10px;
    padding:9px 16px; border-bottom:1px solid rgba(255,255,255,.04);
    transition:background .14s; cursor:pointer;
}
.cx-driver:last-child { border-bottom:none; }
.cx-driver:hover { background:rgba(255,255,255,.04); }
.cx-driv-av {
    width:36px; height:36px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:12px; font-weight:700; color:#fff; flex-shrink:0; position:relative;
}
.cx-driv-dot {
    position:absolute; bottom:0; right:0;
    width:9px; height:9px; border-radius:50%;
    border:2px solid var(--cx-surface);
}
.cx-driv-info { flex:1; min-width:0; }
.cx-driv-name { font-size:12.5px; font-weight:700; color:#fff; }
.cx-driv-loc  { font-size:10.5px; color:var(--cx-text2); margin-top:1px; }
.cx-driv-right{ display:flex; flex-direction:column; align-items:flex-end; gap:4px; }
.cx-driv-rating{ font-size:11px; font-weight:700; color:var(--cx-orange); }
.cx-driv-status{ font-size:9.5px; font-weight:700; padding:2px 7px; border-radius:20px; }
.ds-ok { background:rgba(16,185,129,.14); color:#34d399; border:1px solid rgba(16,185,129,.25); }
.ds-go { background:rgba(245,158,11,.14); color:#fbbf24; border:1px solid rgba(245,158,11,.25); }
.cx-driv-phone {
    width:28px; height:28px; border-radius:7px;
    background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.08);
    display:flex; align-items:center; justify-content:center; font-size:13px;
    cursor:pointer; flex-shrink:0; transition:background .14s;
}
.cx-driv-phone:hover { background:rgba(16,185,129,.14); }

/* ══ BOTTOM (analytique) ══ */
.cx-bottom { display:grid; grid-template-columns:1fr 1fr .85fr; gap:14px; }
.cx-chart-body { padding:14px 16px 12px; }
.cx-chart-hd {
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom:12px;
}
.cx-chart-title { font-size:13px; font-weight:800; color:#fff; }
.cx-chart-sel {
    padding:5px 10px; border-radius:var(--r-sm);
    background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.08);
    color:var(--cx-text2); font-size:11px; font-family:inherit;
    cursor:pointer; outline:none;
}
.cx-rev-big { font-size:24px; font-weight:900; color:#fff; letter-spacing:-.5px; margin-bottom:2px; }
.cx-rev-sub { font-size:11px; color:var(--cx-green); font-weight:600; margin-bottom:10px; }

/* Performance */
.cx-perf-list { padding:14px 14px; display:flex; flex-direction:column; gap:10px; }
.cx-perf-card {
    background:var(--cx-surface2); border:1px solid var(--cx-border);
    border-radius:var(--r-sm); padding:13px 15px;
    display:flex; align-items:center; gap:12px;
}
.cx-perf-ico  { font-size:22px; flex-shrink:0; }
.cx-perf-lbl  { font-size:10.5px; color:var(--cx-text2); font-weight:600; margin-bottom:2px; }
.cx-perf-val  { font-size:22px; font-weight:900; color:#fff; letter-spacing:-.5px; line-height:1; }
.cx-perf-val span { font-size:13px; font-weight:600; color:var(--cx-text2); }
.cx-perf-trend { font-size:10px; font-weight:600; margin-top:3px; }
.trend-up   { color:var(--cx-green); }
.trend-down { color:var(--cx-red); }

/* ══ RESPONSIVE ══ */

/* ── 1400px+ : colonne droite un peu plus large ── */
@media(min-width:1400px) {
    .cx-main-grid { grid-template-columns:1fr 380px; }
}

/* ── 1200px : colonne droite réduite, stats 3/ligne, bottom 2 cols ── */
@media(max-width:1200px) {
    .cx-main-grid { grid-template-columns:1fr 280px; }
    .cx-bottom { grid-template-columns:1fr 1fr; }
    .cx-bottom > :last-child { grid-column:1/-1; }
    .cx-stat { flex:0 0 calc(33.333% - 10px); }
}

/* ── 1024px : sidebar drawer, hamburger visible ── */
@media(max-width:1024px) {
    .cx-sidebar { transform:translateX(-100%); }
    .cx-sidebar.open { transform:translateX(0); }
    .cx-wrap { padding-left:0; }
    .cx-hamburger { display:flex; }
    .cx-close-btn { display:block; }
    .cx-main-grid { grid-template-columns:1fr 260px; }
}

/* ── 900px : grille principale → 1 colonne ── */
@media(max-width:900px) {
    .cx-main-grid { grid-template-columns:1fr; }
    #cxMap { min-height:300px; }
    .cx-stats { flex-wrap:wrap; }
    .cx-stat { flex:0 0 calc(50% - 7px); }
}

/* ── 768px : bottom 1 colonne, topbar simplifiée, padding réduit ── */
@media(max-width:768px) {
    .cx-bottom { grid-template-columns:1fr; }
    .cx-bottom > :last-child { grid-column:auto; }
    .cx-content { padding:12px; gap:12px; }
    .cx-tb-status { display:none; }
    .cx-date-badge { display:none; }
    #cxMap { min-height:250px; }
    .cx-toasts { left:10px; right:10px; top:auto; bottom:14px; }
    .cx-toast { min-width:0; max-width:100%; }
    .cx-notif-panel { width:calc(100vw - 24px); right:-60px; }
}

/* ── 600px : topbar compacte ── */
@media(max-width:600px) {
    .cx-stat { flex:0 0 calc(50% - 7px); }
    .cx-stat-val { font-size:22px; }
    .cx-stat-ico  { width:40px; height:40px; font-size:18px; }
    .cx-topbar { gap:8px; padding:0 12px; }
    .cx-tb-btn { width:32px; height:32px; font-size:14px; }
    .cx-tb-user { padding:4px 6px; gap:6px; }
    .cx-tb-uname { font-size:11.5px; }
    .cx-tb-urole { display:none; }
}

/* ── 480px : stats empilées, recherche masquée ── */
@media(max-width:480px) {
    .cx-stats { flex-direction:column; gap:10px; }
    .cx-stat  { flex:none; width:100%; }
    .cx-search { display:none; }
    .cx-content { padding:10px; gap:10px; }
    .cx-panel-hd  { padding:10px 14px 9px; }
    .cx-chat-item { padding:9px 13px; }
    .cx-driver    { padding:8px 13px; }
    .cx-pipe-list { padding:10px 13px; }
    .cx-perf-list { padding:10px 12px; }
    .cx-chart-body{ padding:12px 13px 10px; }
    #cxMap { min-height:200px; }
    .cx-toasts { left:8px; right:8px; }
    .cx-notif-panel { right:-40px; }
}

/* ── 375px : iPhone SE ── */
@media(max-width:375px) {
    :root { --cx-top-h:54px; }
    .cx-topbar { height:var(--cx-top-h); }
    .cx-tb-av { width:28px; height:28px; font-size:10px; }
    .cx-stat-val { font-size:21px; }
    #cxMap { min-height:180px; }
    .cx-notif-panel { width:calc(100vw - 16px); right:-50px; }
}

/* ══ NOTIFICATION DROPDOWN ══ */
.cx-notif-wrap { position:relative; }
/* .cx-notif-btn inherits all styles from .cx-tb-btn */
.cx-notif-panel {
    position:absolute; top:calc(100% + 12px); right:0;
    width:330px; background:var(--cx-surface);
    border:1px solid rgba(255,255,255,.11); border-radius:16px;
    box-shadow:0 24px 64px rgba(0,0,0,.7);
    z-index:9000; overflow:hidden;
    opacity:0; transform:translateY(-10px) scale(.97);
    transition:opacity .2s cubic-bezier(.23,1,.32,1), transform .2s cubic-bezier(.23,1,.32,1);
    pointer-events:none;
}
.cx-notif-panel.open { opacity:1; transform:translateY(0) scale(1); pointer-events:all; }
.cx-notif-panel-hd {
    padding:13px 16px 11px;
    border-bottom:1px solid rgba(255,255,255,.07);
    display:flex; align-items:center; justify-content:space-between;
}
.cx-notif-panel-title { font-size:13px; font-weight:800; color:#fff; }
.cx-notif-panel-cnt {
    background:var(--cx-brand); color:#fff;
    font-size:10px; font-weight:800;
    padding:1px 7px; border-radius:20px;
}
.cx-notif-list {
    max-height:350px; overflow-y:auto;
    scrollbar-width:thin; scrollbar-color:rgba(124,58,237,.25) transparent;
}
.cx-notif-list::-webkit-scrollbar { width:3px; }
.cx-notif-list::-webkit-scrollbar-thumb { background:rgba(124,58,237,.3); border-radius:3px; }
.cx-notif-item {
    display:flex; align-items:flex-start; gap:10px;
    padding:11px 16px; cursor:pointer;
    border-bottom:1px solid rgba(255,255,255,.04);
    transition:background .14s;
}
.cx-notif-item:hover { background:rgba(255,255,255,.05); }
.cx-notif-item:last-child { border-bottom:none; }
.cx-notif-av {
    width:36px; height:36px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:12px; font-weight:800; color:#fff;
}
.cx-notif-body { flex:1; min-width:0; }
.cx-notif-name { font-size:12.5px; font-weight:700; color:#fff; }
.cx-notif-msg { font-size:11.5px; color:var(--cx-text2); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin-top:2px; }
.cx-notif-meta { display:flex; align-items:center; justify-content:space-between; margin-top:4px; }
.cx-notif-time { font-size:10px; color:var(--cx-muted); }
.cx-notif-unread { background:var(--cx-brand); color:#fff; font-size:10px; font-weight:800; padding:1px 6px; border-radius:20px; }
.cx-notif-empty { padding:32px 16px; text-align:center; color:var(--cx-muted); font-size:12.5px; }
.cx-notif-footer { padding:10px 16px; border-top:1px solid rgba(255,255,255,.07); text-align:center; }
.cx-notif-footer a { font-size:12px; color:var(--cx-brand-lt); font-weight:600; }
.cx-notif-footer a:hover { color:#fff; }

/* ══ TOAST NOTIFICATIONS ══ */
.cx-toasts {
    position:fixed; top:18px; right:18px; z-index:9999;
    display:flex; flex-direction:column; gap:10px;
    pointer-events:none;
}
.cx-toast {
    background:var(--cx-surface);
    border:1px solid rgba(124,58,237,.25);
    border-left:3px solid var(--cx-brand);
    border-radius:14px;
    padding:13px 16px;
    min-width:290px; max-width:360px;
    box-shadow:0 12px 32px rgba(0,0,0,.5);
    display:flex; align-items:flex-start; gap:11px;
    pointer-events:all; cursor:pointer;
    animation:toastIn .35s cubic-bezier(.23,1,.32,1) forwards;
}
.cx-toast.hiding {
    animation:toastOut .3s ease forwards;
}
@keyframes toastIn  { from{transform:translateX(120%);opacity:0} to{transform:translateX(0);opacity:1} }
@keyframes toastOut { from{transform:translateX(0);opacity:1}    to{transform:translateX(120%);opacity:0} }
.cx-toast-ico  { font-size:22px; flex-shrink:0; line-height:1; }
.cx-toast-body { flex:1; min-width:0; }
.cx-toast-shop { font-size:12.5px; font-weight:800; color:#fff; }
.cx-toast-msg  { font-size:11.5px; color:var(--cx-text2); margin-top:3px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.cx-toast-time { font-size:10px; color:var(--cx-muted); margin-top:3px; }
.cx-toast-close{
    color:var(--cx-muted); font-size:14px; flex-shrink:0;
    padding:2px 4px; border-radius:4px; transition:color .14s;
    line-height:1; align-self:flex-start;
}
.cx-toast-close:hover { color:#fff; }
</style>
@endpush

@section('content')
@php
    $u = auth()->user();
    $parts = explode(' ', $u->name ?? 'Admin ShipXpress');
    $ini = strtoupper(substr($parts[0],0,1)) . (isset($parts[1]) ? strtoupper(substr($parts[1],0,1)) : '');
@endphp

<div class="cx-wrap">

{{-- ═══════════════════════ SIDEBAR ═══════════════════════ --}}
<aside class="cx-sidebar" id="cxSidebar">

    <div class="cx-brand-hd">
        <div class="cx-brand-top">
            <a href="{{ route('company.dashboard') }}" class="cx-logo">
                <div class="cx-logo-icon">🚚</div>
                ShipXpress
            </a>
            <button class="cx-close-btn" id="cxClose">✕</button>
        </div>
        <div class="cx-sys-badge">
            <span class="cx-sys-dot"></span> Système actif
        </div>
    </div>

    <nav class="cx-nav">
        <div class="cx-nav-sec">Principal</div>
        <a href="{{ route('company.dashboard') }}" class="cx-nav-item active">
            <span class="cx-nav-ico">⊞</span> Tableau de bord
        </a>
        <a href="{{ route('company.chat.inbox') }}" class="cx-nav-item">
            <span class="cx-nav-ico">💬</span> Demandes (Chat)
            <span class="cx-nav-badge" id="navChatBadge" style="display:none"></span>
        </a>
        <a href="{{ route('company.orders.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">📦</span> Commandes
            <span class="cx-nav-badge" id="navOrderBadge" style="display:none"></span>
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">🚚</span> Livraisons
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">🗺️</span> Carte en direct
        </a>
        <a href="{{ route('company.drivers.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">🚴</span> Chauffeurs
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">🏪</span> Boutiques
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">👥</span> Clients
        </a>

        <div class="cx-nav-sec">Gestion</div>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">📍</span> Zone de livraison
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">💲</span> Tarification
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">🔔</span> Notifications
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">📊</span> Rapports
        </a>

        <div class="cx-nav-sec">Configuration</div>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">⚙️</span> Paramètres
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">👤</span> Utilisateurs
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">🔌</span> Intégrations
        </a>
    </nav>

    <div class="cx-user-foot">
        <div class="cx-user-row">
            <div class="cx-user-av">{{ $ini }}</div>
            <div style="flex:1;min-width:0">
                <div class="cx-user-name">{{ Str::limit($u->name ?? 'Admin ShipXpress', 18) }}</div>
                <div class="cx-user-role">Super Administrateur</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="flex-shrink:0">
                @csrf
                <button type="submit" class="cx-logout-btn" title="Se déconnecter">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>
        <div class="cx-dark-row" id="cxDarkToggle">
            <span class="cx-dark-lbl">Mode sombre</span>
            <div class="cx-toggle" id="cxDarkSwitch"></div>
        </div>
    </div>
</aside>

<div class="cx-overlay" id="cxOverlay"></div>

{{-- ═══════════════════════ MAIN ═══════════════════════ --}}
<main class="cx-main">

    {{-- TOPBAR --}}
    <div class="cx-topbar">
        <button class="cx-hamburger" id="cxHamburger">☰</button>

        <div class="cx-search">
            <span style="font-size:15px;color:var(--cx-muted);flex-shrink:0">🔍</span>
            <input type="text" id="globalSearch" placeholder="Rechercher (commandes, livreurs, boutiques...)">
            <span class="cx-kbd">Ctrl+K</span>
        </div>

        <div class="cx-tb-right">
            <div class="cx-tb-status">
                <span class="cx-sys-dot"></span> Système actif
            </div>
            {{-- Bell : dropdown notifications --}}
            <div class="cx-notif-wrap" id="cxNotifWrap">
                <button class="cx-tb-btn cx-notif-btn" id="cxNotifBtn" title="Notifications">
                    🔔
                    <span class="cx-notif-dot" id="topbarNotifBadge" style="display:none"></span>
                </button>
                <div class="cx-notif-panel" id="cxNotifPanel">
                    <div class="cx-notif-panel-hd">
                        <div class="cx-notif-panel-title">🔔 Notifications</div>
                        <span class="cx-notif-panel-cnt" id="notifPanelCnt" style="display:none"></span>
                    </div>
                    <div class="cx-notif-list" id="cxNotifList">
                        <div class="cx-notif-empty">Aucune notification pour le moment</div>
                    </div>
                    <div class="cx-notif-footer">
                        <a href="{{ route('company.chat.inbox') }}">Voir toutes les conversations →</a>
                    </div>
                </div>
            </div>
            <a href="{{ route('company.chat.inbox') }}" class="cx-tb-btn" title="Messages" style="text-decoration:none">
                💬
            </a>
            <div class="cx-tb-user">
                <div class="cx-tb-av">{{ $ini }}</div>
                <div>
                    <div class="cx-tb-uname">{{ Str::limit($u->name ?? 'Admin ShipXpress', 16) }}</div>
                    <div class="cx-tb-urole">Super Admin</div>
                </div>
                <span style="color:var(--cx-muted);font-size:11px;margin-left:2px">▾</span>
            </div>
            <div class="cx-date-badge">
                📅 {{ now()->translatedFormat('d M Y') }}
            </div>
        </div>
    </div>

    {{-- CONTENT --}}
    <div class="cx-content">

        {{-- ══ STATS ══ --}}
        <div class="cx-stats">
            <div class="cx-stat" style="--s-color:#f59e0b;--s-ico-bg:rgba(245,158,11,.12)">
                <div class="cx-stat-ico">📋</div>
                <div>
                    <div class="cx-stat-lbl">Commandes en attente</div>
                    <div class="cx-stat-val">24</div>
                    <div class="cx-stat-trend">↑ +18% vs hier</div>
                </div>
            </div>
            <div class="cx-stat" style="--s-color:#8b5cf6;--s-ico-bg:rgba(139,92,246,.12)">
                <div class="cx-stat-ico">🚴</div>
                <div>
                    <div class="cx-stat-lbl">Livreurs disponibles</div>
                    <div class="cx-stat-val">32</div>
                    <div class="cx-stat-trend">↑ +12% vs hier</div>
                </div>
            </div>
            <div class="cx-stat" style="--s-color:#3b82f6;--s-ico-bg:rgba(59,130,246,.12)">
                <div class="cx-stat-ico">🚚</div>
                <div>
                    <div class="cx-stat-lbl">Livraisons en cours</div>
                    <div class="cx-stat-val">56</div>
                    <div class="cx-stat-trend">↑ +8% vs hier</div>
                </div>
            </div>
            <div class="cx-stat" style="--s-color:#10b981;--s-ico-bg:rgba(16,185,129,.12)">
                <div class="cx-stat-ico">✅</div>
                <div>
                    <div class="cx-stat-lbl">Livraisons terminées</div>
                    <div class="cx-stat-val">152</div>
                    <div class="cx-stat-trend">↑ +15% vs hier</div>
                </div>
            </div>
            <div class="cx-stat" style="--s-color:#7c3aed;--s-ico-bg:rgba(124,58,237,.12)">
                <div class="cx-stat-ico">💳</div>
                <div>
                    <div class="cx-stat-lbl">Revenus livraison</div>
                    <div class="cx-stat-val sm">2 350 000</div>
                    <div class="cx-stat-trend">↑ +22% vs hier · FCFA</div>
                </div>
            </div>
        </div>

        {{-- ══ MAIN GRID : carte (gauche large) + colonne droite ══ --}}
        <div class="cx-main-grid">

            {{-- ── GAUCHE : CARTE EN TEMPS RÉEL ── --}}
            <div class="cx-panel cx-map-panel">
                <div class="cx-panel-hd">
                    <div class="cx-panel-title">🗺️ Carte en temps réel</div>
                    <div style="display:flex;align-items:center;gap:6px">
                        <span class="cx-sys-dot"></span>
                        <span style="font-size:11px;color:var(--cx-green);font-weight:600">Live</span>
                    </div>
                </div>
                <div id="cxMap"></div>
                <div class="cx-map-legend">
                    <span class="cx-legend-item"><span class="cx-legend-dot" style="background:#10b981"></span>Disponibles</span>
                    <span class="cx-legend-item"><span class="cx-legend-dot" style="background:#f59e0b"></span>En livraison</span>
                    <span class="cx-legend-item"><span class="cx-legend-dot" style="background:#3b82f6"></span>Boutiques</span>
                    <span class="cx-legend-item"><span class="cx-legend-dot" style="background:#ef4444"></span>Clients</span>
                </div>
            </div>

            {{-- ── DROITE : DEMANDES + PIPELINE + LIVREURS ── --}}
            <div class="cx-right-stack">

                {{-- Demandes de livraison (Chat) --}}
                <div class="cx-panel">
                    <div class="cx-panel-hd">
                        <div class="cx-panel-title">
                            💬 Demandes (Chat)
                            <span id="chatPanelBadge" style="display:none;background:rgba(239,68,68,.18);color:#f87171;border:1px solid rgba(239,68,68,.28);font-size:9.5px;font-weight:700;padding:1px 6px;border-radius:20px"></span>
                        </div>
                        <a href="{{ route('company.chat.inbox') }}" class="cx-panel-link">Voir tout</a>
                    </div>
                    <div id="chatPanelList">
                        <div style="padding:28px 16px;text-align:center;color:var(--cx-muted);font-size:12.5px">Chargement…</div>
                    </div>
                    <a href="{{ route('company.chat.inbox') }}" class="cx-chat-cta">Voir toutes les demandes →</a>
                </div>

                {{-- Pipeline des livraisons --}}
                <div class="cx-panel">
                    <div class="cx-panel-hd">
                        <div class="cx-panel-title">📊 Pipeline des livraisons</div>
                        <a href="{{ route('company.orders.index') }}" class="cx-panel-link">Commandes</a>
                    </div>
                    <div class="cx-pipe-list">
                        @php
                        $pipe = [
                            ['En attente de livreur', 24,  15.8, '#eab308'],
                            ['Assignees',             18,  11.8, '#3b82f6'],
                            ['En livraison',          56,  36.8, '#f59e0b'],
                            ['Livrees',              152, 100.0, '#10b981'],
                            ['Annulees',               6,   3.9, '#ef4444'],
                        ];
                        @endphp
                        @foreach($pipe as [$lbl,$val,$pct,$color])
                        <div class="cx-pipe-item">
                            <div class="cx-pipe-row">
                                <span class="cx-pipe-lbl">{{ $lbl }}</span>
                                <span class="cx-pipe-val">{{ $val }}</span>
                            </div>
                            <div class="cx-pipe-track">
                                <div class="cx-pipe-fill" data-w="{{ $pct }}" style="background:{{ $color }}"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Livreurs actifs --}}
                <div class="cx-panel">
                    <div class="cx-panel-hd">
                        <div class="cx-panel-title">🚴 Livreurs actifs</div>
                        <a href="{{ route('company.drivers.index') }}" class="cx-panel-link">Gerer</a>
                    </div>
                    @php
                    $driversDemo = [
                        ['JD','Jean Dupont',   'Plateau, Abidjan',  '4.8','Disponible',  'ok','linear-gradient(135deg,#6366f1,#4338ca)'],
                        ['MD','Mariam Diarra', 'Cocody, Abidjan',   '4.7','En livraison','go','linear-gradient(135deg,#10b981,#059669)'],
                        ['IK','Ibrahim Kone',  'Yopougon, Abidjan', '4.6','Disponible',  'ok','linear-gradient(135deg,#f59e0b,#d97706)'],
                        ['AT','Awa Traore',    'Marcory, Abidjan',  '4.9','En livraison','go','linear-gradient(135deg,#ec4899,#be185d)'],
                        ['MS','Mohamed Sy',    'Bingerville',        '4.5','Disponible',  'ok','linear-gradient(135deg,#8b5cf6,#7c3aed)'],
                    ];
                    @endphp
                    @foreach($driversDemo as [$ini2,$name,$loc,$rate,$stat,$type,$bg])
                    <div class="cx-driver">
                        <div class="cx-driv-av" style="background:{{ $bg }}">
                            {{ $ini2 }}
                            <span class="cx-driv-dot" style="background:{{ $type==='ok'?'#10b981':'#f59e0b' }}"></span>
                        </div>
                        <div class="cx-driv-info">
                            <div class="cx-driv-name">{{ $name }}</div>
                            <div class="cx-driv-loc">📍 {{ $loc }}</div>
                        </div>
                        <div class="cx-driv-right">
                            <span class="cx-driv-rating">★ {{ $rate }}</span>
                            <span class="cx-driv-status {{ $type==='ok'?'ds-ok':'ds-go' }}">● {{ $stat }}</span>
                        </div>
                        <div class="cx-driv-phone">📞</div>
                    </div>
                    @endforeach
                </div>

            </div>{{-- /cx-right-stack --}}
        </div>{{-- /cx-main-grid --}}

        {{-- ══ BOTTOM : analytique (charts + performance) ══ --}}
        <div class="cx-bottom">

            {{-- Commandes par jour --}}
            <div class="cx-panel">
                <div class="cx-chart-body">
                    <div class="cx-chart-hd">
                        <span class="cx-chart-title">📈 Commandes par jour</span>
                        <select class="cx-chart-sel">
                            <option>7 derniers jours</option>
                            <option>30 derniers jours</option>
                        </select>
                    </div>
                    <canvas id="chartOrders" height="150"></canvas>
                </div>
            </div>

            {{-- Revenus --}}
            <div class="cx-panel">
                <div class="cx-chart-body">
                    <div class="cx-chart-hd">
                        <span class="cx-chart-title">💰 Revenus (Livraison)</span>
                        <select class="cx-chart-sel">
                            <option>30 derniers jours</option>
                            <option>7 derniers jours</option>
                        </select>
                    </div>
                    <div class="cx-rev-big">2 350 000 <span style="font-size:14px;font-weight:600;color:var(--cx-text2)">FCFA</span></div>
                    <div class="cx-rev-sub">↑ +22% vs periode precedente</div>
                    <canvas id="chartRevenue" height="110"></canvas>
                </div>
            </div>

            {{-- Performance --}}
            <div class="cx-panel">
                <div class="cx-panel-hd">
                    <div class="cx-panel-title">⚡ Performance moyenne</div>
                </div>
                <div class="cx-perf-list">
                    <div class="cx-perf-card">
                        <div class="cx-perf-ico">🏍️</div>
                        <div>
                            <div class="cx-perf-lbl">Temps moyen livraison</div>
                            <div class="cx-perf-val">28<span> min</span></div>
                            <div class="cx-perf-trend trend-down">↓ -5% vs mois dernier</div>
                        </div>
                    </div>
                    <div class="cx-perf-card">
                        <div class="cx-perf-ico">✅</div>
                        <div>
                            <div class="cx-perf-lbl">Taux de reussite</div>
                            <div class="cx-perf-val">96.5<span>%</span></div>
                            <div class="cx-perf-trend trend-up">↑ +2% vs mois dernier</div>
                        </div>
                    </div>
                    <div class="cx-perf-card">
                        <div class="cx-perf-ico">⭐</div>
                        <div>
                            <div class="cx-perf-lbl">Note moyenne</div>
                            <div class="cx-perf-val">4.7<span>/5</span></div>
                            <div class="cx-perf-trend trend-up">↑ +0.3 vs mois dernier</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /cx-bottom --}}
    </div>{{-- /cx-content --}}
</main>
</div>{{-- /cx-wrap --}}

{{-- Conteneur des toasts de notification --}}
<div class="cx-toasts" id="cxToasts"></div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ── Sidebar ── */
    const sidebar  = document.getElementById('cxSidebar');
    const overlay  = document.getElementById('cxOverlay');
    document.getElementById('cxHamburger')?.addEventListener('click', () => {
        sidebar.classList.add('open'); overlay.classList.add('open');
    });
    [overlay, document.getElementById('cxClose')].forEach(el =>
        el?.addEventListener('click', () => {
            sidebar.classList.remove('open'); overlay.classList.remove('open');
        })
    );

    /* ── Dark / Light mode toggle ── */
    (function initTheme() {
        const sw   = document.getElementById('cxDarkSwitch');
        const row  = document.getElementById('cxDarkToggle');
        const lbl  = row?.querySelector('.cx-dark-lbl');
        const body = document.body;

        // Restaurer depuis localStorage (dark = défaut)
        const saved = localStorage.getItem('cx-theme') || 'dark';
        const applyTheme = (theme) => {
            if (theme === 'light') {
                body.classList.add('cx-light');
                sw?.classList.remove('on');
                if (lbl) lbl.textContent = 'Mode clair';
            } else {
                body.classList.remove('cx-light');
                sw?.classList.add('on');
                if (lbl) lbl.textContent = 'Mode sombre';
            }
        };

        applyTheme(saved);
        document.documentElement.classList.remove('cx-prelight');
        // Sync html element pour que son background suive aussi
        document.documentElement.classList.toggle('cx-light', saved === 'light');

        row?.addEventListener('click', () => {
            const isLight = body.classList.toggle('cx-light');
            document.documentElement.classList.toggle('cx-light', isLight);
            sw?.classList.toggle('on', !isLight);
            if (lbl) lbl.textContent = isLight ? 'Mode clair' : 'Mode sombre';
            localStorage.setItem('cx-theme', isLight ? 'light' : 'dark');
        });
    })();

    /* ── Ctrl+K focus search ── */
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey||e.metaKey) && e.key==='k') {
            e.preventDefault();
            document.getElementById('globalSearch')?.focus();
        }
    });

    /* ── Carte Leaflet (dark tile, Abidjan) ── */
    const map = L.map('cxMap', { zoomControl:false }).setView([5.3599,-4.0082], 13);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution:'&copy; CartoDB', maxZoom:19, subdomains:'abcd'
    }).addTo(map);
    L.control.zoom({ position:'bottomright' }).addTo(map);

    const pin = (color, icon) => L.divIcon({
        html:`<div style="width:28px;height:28px;border-radius:50%;background:${color};display:flex;align-items:center;justify-content:center;font-size:13px;border:2px solid rgba(255,255,255,.25);box-shadow:0 2px 8px rgba(0,0,0,.5)">${icon}</div>`,
        className:'', iconSize:[28,28], iconAnchor:[14,14]
    });

    [[5.3530,-4.0010],[5.3680,-4.0180],[5.3450,-4.0250]].forEach(p =>
        L.marker(p, {icon:pin('#10b981','🚴')}).addTo(map));
    [[5.3600,-4.0082],[5.3720,-3.9950],[5.3480,-4.0120]].forEach(p =>
        L.marker(p, {icon:pin('#f59e0b','🚚')}).addTo(map));
    [[5.3550,-4.0050],[5.3650,-4.0200]].forEach(p =>
        L.marker(p, {icon:pin('#3b82f6','🏪')}).addTo(map));
    [[5.3490,-4.0150],[5.3750,-3.9980]].forEach(p =>
        L.marker(p, {icon:pin('#ef4444','📍')}).addTo(map));

    const isLightNow = () => document.documentElement.classList.contains('cx-light');
    const popupBg  = () => isLightNow() ? '#ffffff' : '#0d1424';
    const popupBdr = () => isLightNow() ? 'rgba(0,0,0,.1)' : 'rgba(99,102,241,.22)';
    const popupTxt = () => isLightNow() ? '#111827'  : '#e2e8f0';
    const popupSub = () => isLightNow() ? '#6b7280'  : '#94a3b8';

    const buildPopup = () =>
        `<div style="background:${popupBg()};border:1px solid ${popupBdr()};border-radius:9px;padding:10px 14px;color:${popupTxt()};font-size:12px;min-width:185px;box-shadow:0 6px 20px rgba(0,0,0,.18)">
            <div style="font-weight:800;margin-bottom:4px">🚚 Jean Dupont</div>
            <div style="color:${popupSub()};font-size:11px">Commande #CMD-2156</div>
            <div style="color:#10b981;margin-top:5px;font-weight:700;font-size:11.5px">⏱ Arrivee dans 15 min</div>
        </div>`;

    L.popup({className:'cx-map-popup', closeButton:false, offset:[0,-8]})
        .setLatLng([5.3600,-4.0082])
        .setContent(buildPopup())
        .openOn(map);

    /* ── Chart Commandes ── */
    new Chart(document.getElementById('chartOrders'), {
        type: 'line',
        data: {
            labels: ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'],
            datasets: [{
                data: [45,62,58,80,72,95,68],
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139,92,246,.08)',
                borderWidth: 2, pointRadius: 4,
                pointBackgroundColor: '#8b5cf6',
                fill: true, tension: .4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend:{display:false} },
            scales: {
                x: { grid:{color:'rgba(255,255,255,.05)'}, ticks:{color:'#64748b',font:{size:11}} },
                y: { grid:{color:'rgba(255,255,255,.05)'}, ticks:{color:'#64748b',font:{size:11}}, beginAtZero:true }
            }
        }
    });

    /* ── Chart Revenus ── */
    const revData = [120,95,180,210,160,240,195,175,220,260,200,185,
                     230,215,190,245,205,170,235,255,180,210,195,225,
                     240,215,260,285,240,310];
    new Chart(document.getElementById('chartRevenue'), {
        type: 'bar',
        data: {
            labels: Array.from({length:30},(_,i)=>`${i+1}`),
            datasets: [{
                data: revData,
                backgroundColor: revData.map((_,i)=> i===29 ? '#8b5cf6' : 'rgba(139,92,246,.35)'),
                borderRadius: 3, borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            plugins: { legend:{display:false} },
            scales: {
                x: { display:false },
                y: { grid:{color:'rgba(255,255,255,.04)'}, ticks:{color:'#64748b',font:{size:10}}, beginAtZero:true }
            }
        }
    });

    /* ── Pipeline bars animation ── */
    setTimeout(() => {
        document.querySelectorAll('.cx-pipe-fill').forEach(el => {
            el.style.width = el.dataset.w + '%';
        });
    }, 300);

    /* ══════════════════════════════════════════════════════
       NOTIFICATIONS CHAT EN TEMPS RÉEL
       Polling /company/chat/conversations toutes les 5s.
       Badge sidebar + topbar + cloche dropdown + panel chat.
    ══════════════════════════════════════════════════════ */
    let prevConvMap = {};
    let firstPoll   = true;

    const AV_COLORS = [
        'linear-gradient(135deg,#7c3aed,#5b21b6)',
        'linear-gradient(135deg,#10b981,#059669)',
        'linear-gradient(135deg,#3b82f6,#1d4ed8)',
        'linear-gradient(135deg,#ec4899,#be185d)',
        'linear-gradient(135deg,#f59e0b,#d97706)',
        'linear-gradient(135deg,#06b6d4,#0891b2)',
        'linear-gradient(135deg,#84cc16,#4d7c0f)',
        'linear-gradient(135deg,#f97316,#c2410c)',
    ];

    function esc(s) {
        return String(s ?? '').replace(/[&<>"']/g, c =>
            ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])
        );
    }
    function getIni(name) {
        const n = (name || '').trim();
        if (!n) return '?';
        const p = n.split(' ');
        const a = p[0]?.[0] || '';
        const b = p[1]?.[0] || '';
        return (a + b).toUpperCase() || n[0].toUpperCase();
    }
    function fmtTime(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr), now = new Date(), diff = now - d;
        if (diff < 60000) return 'À l\'instant';
        if (diff < 3600000) return Math.floor(diff / 60000) + ' min';
        if (d.toDateString() === now.toDateString())
            return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
    }

    function setBadge(id, count) {
        const el = document.getElementById(id);
        if (!el) return;
        if (count > 0) {
            el.textContent = count > 99 ? '99+' : count;
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    }

    /* ── Rendu panneau cloche ── */
    function renderNotifPanel(convs) {
        const list = document.getElementById('cxNotifList');
        const cnt  = document.getElementById('notifPanelCnt');
        if (!list) return;

        const unread = convs.filter(c => (c.unread || 0) > 0);
        const shown  = unread.length ? unread : convs.slice(0, 6);
        const total  = unread.length;

        if (cnt) { cnt.textContent = total; cnt.style.display = total > 0 ? '' : 'none'; }

        if (!shown.length) {
            list.innerHTML = '<div class="cx-notif-empty">Aucune notification pour le moment</div>';
            return;
        }
        list.innerHTML = shown.map((c, i) => `
            <div class="cx-notif-item" onclick="window.location.href='{{ route('company.chat.inbox') }}?shop_id=${encodeURIComponent(c.shop_id)}'">
                <div class="cx-notif-av" style="background:${AV_COLORS[i % AV_COLORS.length]}">${esc(getIni(c.shop_name))}</div>
                <div class="cx-notif-body">
                    <div class="cx-notif-name">${esc(c.shop_name)}</div>
                    <div class="cx-notif-msg">${esc(c.last_message || '')}</div>
                    <div class="cx-notif-meta">
                        <span class="cx-notif-time">${fmtTime(c.last_at)}</span>
                        ${(c.unread || 0) > 0 ? `<span class="cx-notif-unread">${c.unread > 99 ? '99+' : c.unread} non lu${c.unread > 1 ? 's' : ''}</span>` : ''}
                    </div>
                </div>
            </div>
        `).join('');
    }

    /* ── Rendu panel chat dashboard ── */
    function renderChatPanel(convs) {
        const list = document.getElementById('chatPanelList');
        if (!list) return;
        const items = convs.slice(0, 5);
        if (!items.length) {
            list.innerHTML = '<div style="padding:28px 16px;text-align:center;color:var(--cx-muted);font-size:12.5px">Aucune conversation pour l\'instant</div>';
            return;
        }
        list.innerHTML = items.map((c, i) => {
            const hasUnread = (c.unread || 0) > 0;
            const badge = hasUnread
                ? `<span class="cx-badge b-new">${c.unread > 99 ? '99+' : c.unread} non lu${c.unread > 1 ? 's' : ''}</span>`
                : `<span class="cx-badge b-done">Lu</span>`;
            return `
                <div class="cx-chat-item" onclick="window.location.href='{{ route('company.chat.inbox') }}?shop_id=${encodeURIComponent(c.shop_id)}'">
                    <div class="cx-chat-av" style="background:${AV_COLORS[i % AV_COLORS.length]}">${esc(getIni(c.shop_name))}</div>
                    <div class="cx-chat-body">
                        <div class="cx-chat-row">
                            <span class="cx-chat-name">${esc(c.shop_name)}</span>
                            <span class="cx-chat-time">${fmtTime(c.last_at)}</span>
                        </div>
                        <div class="cx-chat-msg">${esc(c.last_message || '')}</div>
                        <div class="cx-chat-foot">${badge}</div>
                    </div>
                </div>
            `;
        }).join('');
    }

    /* ── Toast ── */
    function showToast(shopName, lastMsg, shopId) {
        const container = document.getElementById('cxToasts');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = 'cx-toast';
        toast.innerHTML = `
            <div class="cx-toast-ico">💬</div>
            <div class="cx-toast-body">
                <div class="cx-toast-shop">${esc(shopName)}</div>
                <div class="cx-toast-msg">${esc(lastMsg)}</div>
                <div class="cx-toast-time">À l'instant · Cliquez pour répondre</div>
            </div>
            <div class="cx-toast-close" title="Fermer">✕</div>
        `;
        const url = '{{ route('company.chat.inbox') }}?shop_id=' + encodeURIComponent(shopId);
        toast.addEventListener('click', () => { window.location.href = url; });
        toast.querySelector('.cx-toast-close').addEventListener('click', e => {
            e.stopPropagation(); dismissToast(toast);
        });
        container.appendChild(toast);
        setTimeout(() => dismissToast(toast), 6000);
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator(), gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(440, ctx.currentTime + 0.15);
            gain.gain.setValueAtTime(0.12, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
            osc.start(ctx.currentTime); osc.stop(ctx.currentTime + 0.3);
        } catch(e) {}
    }
    function dismissToast(toast) {
        if (!toast.parentNode) return;
        toast.classList.add('hiding');
        setTimeout(() => toast.remove(), 320);
    }

    /* ── Polling principal ── */
    async function pollChatNotifs() {
        try {
            const res = await fetch('{{ route('company.chat.conversations') }}', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!data.ok || !Array.isArray(data.conversations)) return;

            const convs = data.conversations;
            const totalUnread = convs.reduce((s, c) => s + (c.unread || 0), 0);

            // Badges — même compteur partout
            setBadge('navChatBadge',     totalUnread);
            setBadge('topbarNotifBadge', totalUnread);
            setBadge('chatPanelBadge',   totalUnread);

            // Rendu live
            renderNotifPanel(convs);
            renderChatPanel(convs);

            // Toasts uniquement pour les nouveaux messages (pas au 1er chargement)
            if (!firstPoll) {
                convs.forEach(conv => {
                    const prev = prevConvMap[conv.shop_id] ?? 0;
                    if ((conv.unread || 0) > prev) {
                        showToast(conv.shop_name, conv.last_message, conv.shop_id);
                    }
                });
            }

            prevConvMap = {};
            convs.forEach(c => { prevConvMap[c.shop_id] = c.unread || 0; });
            firstPoll = false;

        } catch(e) { /* silencieux */ }
    }

    /* ── Toggle cloche dropdown ── */
    const notifBtn   = document.getElementById('cxNotifBtn');
    const notifPanel = document.getElementById('cxNotifPanel');
    const notifWrap  = document.getElementById('cxNotifWrap');
    if (notifBtn && notifPanel) {
        notifBtn.addEventListener('click', e => {
            e.stopPropagation();
            notifPanel.classList.toggle('open');
        });
        document.addEventListener('click', e => {
            if (notifWrap && !notifWrap.contains(e.target)) {
                notifPanel.classList.remove('open');
            }
        });
    }

    // Lancer immédiatement puis toutes les 5 secondes
    pollChatNotifs();
    setInterval(pollChatNotifs, 5000);
});
</script>
@endpush
