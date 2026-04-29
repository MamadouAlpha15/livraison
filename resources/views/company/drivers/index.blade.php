@extends('layouts.app')
@section('title', 'Chauffeurs · ' . $company->name)
@php
    $bodyClass = 'cx-dashboard';
    $u   = auth()->user();
    $ini = strtoupper(substr($u->name ?? 'A', 0, 1)) . strtoupper(substr(explode(' ', $u->name ?? 'A ')[1] ?? 'X', 0, 1));
@endphp

@push('styles')
{{-- Anti-flash --}}
<script>
(function(){
    var t = localStorage.getItem('cx-theme');
    if (t === 'light') document.documentElement.classList.add('cx-prelight');
})();
</script>
<style>
html.cx-prelight body { background:#F5F7FA !important; }
</style>
<style>
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
    --cx-text:      #e2e8f0;
    --cx-text2:     #94a3b8;
    --cx-muted:     #475569;
    --cx-green:     #10b981;
    --cx-amber:     #f59e0b;
    --cx-red:       #ef4444;
    --cx-sb-w:      220px;
    --r:            16px;
    --r-sm:         10px;
    --r-xs:         7px;
}

html, body {
    margin:0; font-family:'Segoe UI',system-ui,sans-serif;
    background:var(--cx-bg) !important;
    color:var(--cx-text); -webkit-font-smoothing:antialiased;
}
a { text-decoration:none; color:inherit; }

/* Cacher navbar/footer du layout */
body.cx-dashboard > nav,
body.cx-dashboard > header,
body.cx-dashboard .navbar,
body.cx-dashboard > .topbar-global,
body.cx-dashboard .app-footer,
body.cx-dashboard .app-flash { display:none !important; }
body.cx-dashboard > main.app-main {
    padding:0 !important; margin:0 !important;
    max-width:100% !important; width:100% !important;
    background:var(--cx-bg) !important; min-height:100vh;
}

/* ══ LAYOUT ══ */
.cx-wrap { display:flex; min-height:100vh; padding-left:var(--cx-sb-w); background:var(--cx-bg); }

/* ══ SIDEBAR ══ */
.cx-sidebar {
    position:fixed; top:0; left:0; bottom:0; width:var(--cx-sb-w);
    background:linear-gradient(180deg,#0f0f59 0%,#0e0e16 40%,#10103a 100%);
    display:flex; flex-direction:column;
    border-right:1px solid rgba(99,102,241,.15);
    box-shadow:6px 0 30px rgba(0,0,0,.35);
    z-index:1200; overflow-y:auto;
    scrollbar-width:thin; scrollbar-color:rgba(124,58,237,.3) transparent;
    transition:transform .25s cubic-bezier(.23,1,.32,1);
}
.cx-sidebar::-webkit-scrollbar { width:3px; }
.cx-sidebar::-webkit-scrollbar-thumb { background:rgba(124,58,237,.3); border-radius:3px; }
.cx-sidebar a { color:#c7d2fe; transition:all .2s; }
.cx-sidebar a:hover { background:rgba(99,102,241,.15); color:#fff; }
.cx-sidebar a.active { background:linear-gradient(90deg,#6366f1,#4f46e5); color:#fff; }

/* Brand */
.cx-brand-hd { padding:14px 14px 10px; border-bottom:1px solid rgba(255,255,255,.06); flex-shrink:0; }
.cx-brand-top { display:flex; align-items:center; justify-content:space-between; }
.cx-logo { display:flex; align-items:center; gap:9px; color:#fff; font-size:16px; font-weight:800; }
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
    margin-top:8px;
}
.cx-sys-dot { width:6px; height:6px; border-radius:50%; background:var(--cx-green); animation:blink 2.2s ease-in-out infinite; flex-shrink:0; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.25} }
.cx-close-btn { display:none; background:none; border:none; color:rgba(255,255,255,.45); font-size:18px; cursor:pointer; padding:2px 6px; border-radius:6px; line-height:1; }
.cx-close-btn:hover { color:#fff; }

/* Nav */
.cx-nav { padding:8px 8px 12px; flex:1; }
.cx-nav-sec { font-size:9px; font-weight:800; letter-spacing:1.8px; color:rgba(255,255,255,.3); padding:14px 10px 4px; text-transform:uppercase; }
.cx-nav-item {
    display:flex; align-items:center; gap:9px;
    padding:7px 10px; border-radius:var(--r-xs);
    color:rgba(255,255,255,.68); font-size:13px; font-weight:500;
    transition:background .14s,color .14s; position:relative;
    cursor:pointer; margin-bottom:1px;
}
.cx-nav-item:hover { background:rgba(255,255,255,.06); color:#fff; }
.cx-nav-item.active { background:rgba(124,58,237,.22); color:#fff; font-weight:700; }
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
.cx-user-foot { padding:10px 10px 12px; border-top:1px solid rgba(255,255,255,.07); flex-shrink:0; }
.cx-user-row {
    display:flex; align-items:center; gap:9px;
    padding:7px 8px; border-radius:var(--r-xs);
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
    color:#f87171; cursor:pointer; transition:background .15s,color .15s; padding:0;
}
.cx-logout-btn:hover { background:rgba(239,68,68,.22); color:#fff; }
.cx-dark-row { display:flex; align-items:center; justify-content:space-between; padding:4px 8px; cursor:pointer; }
.cx-dark-lbl { font-size:11.5px; color:var(--cx-text2); }
.cx-toggle { width:34px; height:18px; background:#475569; border-radius:9px; position:relative; transition:background .25s; flex-shrink:0; }
.cx-toggle::after { content:''; position:absolute; top:3px; left:3px; width:12px; height:12px; background:#fff; border-radius:50%; transition:left .25s; }
.cx-toggle.on { background:var(--cx-brand); }
.cx-toggle.on::after { left:19px; }

/* Overlay mobile */
.cx-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:1100; }
.cx-overlay.open { display:block; }

/* ══ MAIN ══ */
.cx-main { flex:1; min-width:0; display:flex; flex-direction:column; min-height:100vh; }

/* Topbar */
.cx-topbar {
    height:60px; background:var(--cx-surface); border-bottom:1px solid var(--cx-border);
    display:flex; align-items:center; gap:12px; padding:0 20px;
    position:sticky; top:0; z-index:100;
}
.cx-hamburger {
    display:none; background:none; border:none;
    color:var(--cx-text2); font-size:18px; cursor:pointer; padding:4px 8px; border-radius:6px;
}
.cx-topbar-title { font-size:15px; font-weight:800; color:var(--cx-text); }
.cx-topbar-sub { font-size:12px; color:var(--cx-muted); margin-left:2px; }
.cx-tb-right { margin-left:auto; display:flex; align-items:center; gap:8px; }
.cx-tb-btn {
    height:34px; padding:0 10px; border-radius:8px;
    background:rgba(255,255,255,.06); border:1px solid var(--cx-border);
    color:var(--cx-text2); font-size:16px; cursor:pointer;
    transition:background .14s,color .14s; display:flex; align-items:center; gap:6px;
    position:relative;
}
.cx-tb-btn:hover { background:rgba(255,255,255,.1); color:#fff; }

/* ══ MODE CLAIR ══ */
body.cx-light {
    --cx-bg:       #F5F7FA;
    --cx-surface:  #ffffff;
    --cx-surface2: #eef1f7;
    --cx-border:   rgba(0,0,0,.08);
    --cx-text:     #111827;
    --cx-text2:    #4b5563;
    --cx-muted:    #9ca3af;
}
body.cx-light, html.cx-light, html.cx-light body { background:#F5F7FA !important; }
body.cx-light .cx-topbar { background:#fff; border-bottom-color:rgba(0,0,0,.07); box-shadow:0 1px 4px rgba(0,0,0,.06); }
body.cx-light .cx-tb-btn { background:rgba(0,0,0,.05); border-color:rgba(0,0,0,.08); color:#374151; }
body.cx-light .cx-tb-btn:hover { background:rgba(0,0,0,.1); color:#111; }

/* ══ PAGE CONTENT ══ */
.cx-content { padding:0; flex:1; }

/* Banner */
.page-banner {
    background:linear-gradient(135deg,#1e1b4b 0%,#2d2470 35%,#3d1fa5 65%,#5b21b6 100%);
    padding:28px 32px 24px; position:relative; overflow:hidden;
}
.banner-grid {
    position:absolute; inset:0; pointer-events:none;
    background-image: linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px), linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);
    background-size:44px 44px;
}
.banner-glow  { position:absolute; top:-100px; right:-60px; width:400px; height:400px; border-radius:50%; background:radial-gradient(circle,rgba(124,58,237,.2) 0%,transparent 65%); pointer-events:none; }
.banner-glow2 { position:absolute; bottom:-80px; left:-40px; width:280px; height:280px; border-radius:50%; background:radial-gradient(circle,rgba(59,130,246,.1) 0%,transparent 65%); pointer-events:none; }
.banner-inner { max-width:1200px; margin:0 auto; position:relative; z-index:1; }
.banner-top { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:20px; }
.banner-title-group { display:flex; align-items:center; gap:14px; }
.banner-icon { width:48px; height:48px; border-radius:14px; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2); backdrop-filter:blur(6px); display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; }
.banner-title { font-size:22px; font-weight:900; color:#fff; letter-spacing:-.5px; margin:0; }
.banner-sub { font-size:12.5px; color:rgba(255,255,255,.55); margin-top:3px; }
.btn-add-driver { display:inline-flex; align-items:center; gap:8px; background:#fff; color:var(--cx-brand); font-size:13.5px; font-weight:800; font-family:inherit; padding:11px 20px; border-radius:var(--r-sm); border:none; box-shadow:0 4px 20px rgba(0,0,0,.25); cursor:pointer; transition:all .18s; white-space:nowrap; }
.btn-add-driver:hover { background:#f0e7ff; transform:translateY(-1px); }

/* Stats bar */
.stats-bar { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; }
.stat-pill { background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.11); border-radius:var(--r-sm); padding:14px 16px; backdrop-filter:blur(4px); position:relative; overflow:hidden; transition:background .15s; }
.stat-pill:hover { background:rgba(255,255,255,.1); }
.stat-pill::before { content:''; position:absolute; top:0; left:0; right:0; height:2px; background:var(--s-accent,#a78bfa); }
.stat-pill-ico { font-size:16px; margin-bottom:5px; }
.stat-pill-val { font-size:22px; font-weight:900; color:#fff; line-height:1; letter-spacing:-.6px; }
.stat-pill-lbl { font-size:10px; font-weight:600; color:rgba(255,255,255,.48); text-transform:uppercase; letter-spacing:.5px; margin-top:4px; }

/* ── BODY ── */
.pw { max-width:1200px; margin:0 auto; padding:24px 28px 60px; }

/* Filter bar */
.filter-bar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; background:var(--cx-surface); border:1px solid var(--cx-border); border-radius:var(--r); padding:14px 16px; margin-bottom:20px; }
.tab-btn { padding:7px 18px; border-radius:30px; font-size:12.5px; font-weight:700; border:1px solid transparent; cursor:pointer; transition:all .15s; background:transparent; color:var(--cx-text2); font-family:inherit; }
.tab-btn:hover { background:var(--cx-surface2); color:var(--cx-text); }
.tab-btn.active       { background:var(--cx-brand); color:#fff; border-color:var(--cx-brand); }
.tab-btn.active-green { background:rgba(16,185,129,.15); color:#34d399; border-color:rgba(16,185,129,.3); }
.tab-btn.active-amber { background:rgba(245,158,11,.15); color:#fbbf24; border-color:rgba(245,158,11,.3); }
.tab-btn.active-gray  { background:rgba(100,116,139,.15); color:var(--cx-text2); border-color:var(--cx-border); }
.tab-sep { width:1px; height:22px; background:var(--cx-border); flex-shrink:0; }
.search-wrap { position:relative; flex:1; min-width:180px; }
.search-ico { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--cx-muted); pointer-events:none; }
.search-input { width:100%; padding:9px 12px 9px 35px; background:var(--cx-surface2); border:1px solid var(--cx-border); border-radius:var(--r-xs); color:var(--cx-text); font-size:13px; font-family:inherit; outline:none; transition:border-color .15s; }
.search-input::placeholder { color:var(--cx-muted); }
.search-input:focus { border-color:var(--cx-brand); box-shadow:0 0 0 3px rgba(124,58,237,.12); }

/* ── DRIVER GRID ── */
.drivers-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(290px,1fr)); gap:18px; }

/* Driver card */
.driver-card { background:var(--cx-surface); border:1px solid var(--cx-border); border-radius:var(--r); overflow:hidden; transition:border-color .18s,box-shadow .18s,transform .18s; position:relative; }
.driver-card:hover { border-color:rgba(124,58,237,.35); box-shadow:0 8px 32px rgba(124,58,237,.15),0 2px 8px rgba(0,0,0,.3); transform:translateY(-2px); }
.driver-card-accent { height:3px; width:100%; }

/* Card header */
.dc-header { padding:20px 18px 0; display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
.dc-avatar-wrap { position:relative; flex-shrink:0; }
.dc-avatar { width:64px; height:64px; border-radius:16px; overflow:hidden; border:2px solid rgba(255,255,255,.1); display:flex; align-items:center; justify-content:center; font-size:20px; font-weight:900; color:#fff; letter-spacing:-.5px; }
.dc-avatar img { width:100%; height:100%; object-fit:cover; }
.dc-status-dot { position:absolute; bottom:-3px; right:-3px; width:16px; height:16px; border-radius:50%; border:3px solid var(--cx-surface); }
.dot-avail { background:#10b981; box-shadow:0 0 8px rgba(16,185,129,.6); }
.dot-busy  { background:#f59e0b; box-shadow:0 0 8px rgba(245,158,11,.5); }
.dot-off   { background:#475569; }
.dc-actions { display:flex; gap:6px; flex-shrink:0; }
.dc-action-btn { width:32px; height:32px; border-radius:var(--r-xs); display:flex; align-items:center; justify-content:center; border:1px solid var(--cx-border); background:var(--cx-surface2); color:var(--cx-text2); cursor:pointer; transition:all .14s; font-size:14px; }
.dc-action-btn:hover { background:rgba(124,58,237,.15); border-color:rgba(124,58,237,.3); color:#c4b5fd; }
.dc-action-btn.del:hover { background:rgba(239,68,68,.1); border-color:rgba(239,68,68,.3); color:#f87171; }

/* Card body */
.dc-body { padding:14px 18px 18px; }
.dc-name { font-size:16px; font-weight:800; color:var(--cx-text); letter-spacing:-.3px; margin-bottom:3px; }
.dc-phone { display:flex; align-items:center; gap:5px; font-size:12.5px; color:var(--cx-text2); margin-bottom:14px; }
.dc-status { display:inline-flex; align-items:center; gap:6px; font-size:11.5px; font-weight:700; padding:4px 12px; border-radius:20px; margin-bottom:16px; }
.status-avail { background:rgba(16,185,129,.12); color:#34d399; border:1px solid rgba(16,185,129,.2); }
.status-busy  { background:rgba(245,158,11,.12); color:#fbbf24; border:1px solid rgba(245,158,11,.2); }
.status-off   { background:rgba(100,116,139,.1); color:var(--cx-muted); border:1px solid var(--cx-border); }
.status-dot-sm { width:6px; height:6px; border-radius:50%; flex-shrink:0; }
.dc-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; background:var(--cx-surface2); border:1px solid var(--cx-border); border-radius:var(--r-sm); padding:12px; margin-bottom:16px; }
.dc-stat { text-align:center; }
.dc-stat-val { font-size:18px; font-weight:900; color:var(--cx-text); letter-spacing:-.3px; line-height:1; }
.dc-stat-lbl { font-size:10px; font-weight:600; color:var(--cx-muted); text-transform:uppercase; letter-spacing:.4px; margin-top:3px; }
.dc-status-info { display:flex; align-items:center; justify-content:space-between; background:var(--cx-surface2); border:1px solid var(--cx-border); border-radius:var(--r-xs); padding:9px 12px; font-size:11.5px; }
.dc-status-info-label { color:var(--cx-muted); font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
.dc-status-pill { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:700; padding:3px 10px; border-radius:99px; }
.pill-available { background:rgba(16,185,129,.12); color:#34d399; border:1px solid rgba(16,185,129,.25); }
.pill-busy      { background:rgba(245,158,11,.12); color:#fbbf24; border:1px solid rgba(245,158,11,.25); }
.pill-offline   { background:rgba(100,116,139,.1); color:#94a3b8; border:1px solid rgba(100,116,139,.2); }
.dc-status-dot-sm { width:6px; height:6px; border-radius:50%; flex-shrink:0; }
.dot-sm-green { background:#10b981; box-shadow:0 0 5px rgba(16,185,129,.5); }
.dot-sm-amber { background:#f59e0b; box-shadow:0 0 5px rgba(245,158,11,.4); }
.dot-sm-gray  { background:#64748b; }

/* Empty state */
.empty-state { grid-column:1/-1; text-align:center; padding:64px 24px; display:flex; flex-direction:column; align-items:center; gap:14px; }
.empty-ico-wrap { width:72px; height:72px; border-radius:20px; background:rgba(124,58,237,.1); border:1px solid rgba(124,58,237,.2); display:flex; align-items:center; justify-content:center; font-size:32px; }
.empty-title { font-size:17px; font-weight:800; color:var(--cx-text); }
.empty-sub { font-size:13px; color:var(--cx-muted); max-width:320px; line-height:1.6; }

/* ── MODAL ── */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.7); z-index:2000; display:flex; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(5px); opacity:0; pointer-events:none; transition:opacity .22s; }
.modal-overlay.open { opacity:1; pointer-events:all; }
.modal { background:var(--cx-surface); border:1px solid rgba(255,255,255,.13); border-radius:var(--r); width:100%; max-width:480px; max-height:92vh; overflow-y:auto; box-shadow:0 32px 80px rgba(0,0,0,.6); transform:translateY(18px) scale(.98); transition:transform .22s; }
.modal-overlay.open .modal { transform:translateY(0) scale(1); }
.modal-hd { padding:20px 24px; border-bottom:1px solid var(--cx-border); display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:var(--cx-surface); z-index:1; }
.modal-title { font-size:15px; font-weight:900; color:var(--cx-text); display:flex; align-items:center; gap:9px; }
.modal-title-ico { width:30px; height:30px; border-radius:8px; background:rgba(124,58,237,.2); border:1px solid rgba(124,58,237,.3); display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }
.modal-close { width:30px; height:30px; border-radius:var(--r-xs); background:var(--cx-surface2); border:1px solid var(--cx-border); display:flex; align-items:center; justify-content:center; color:var(--cx-text2); cursor:pointer; font-size:16px; transition:all .14s; }
.modal-close:hover { background:rgba(239,68,68,.12); border-color:rgba(239,68,68,.3); color:#f87171; }
.modal-body { padding:22px 24px; }

/* Avatar upload */
.avatar-upload { display:flex; flex-direction:column; align-items:center; gap:12px; margin-bottom:22px; }
.avatar-preview { width:90px; height:90px; border-radius:22px; background:linear-gradient(135deg,var(--cx-brand),var(--cx-brand2)); border:2px solid rgba(124,58,237,.3); display:flex; align-items:center; justify-content:center; font-size:36px; font-weight:900; color:#fff; overflow:hidden; cursor:pointer; position:relative; transition:all .18s; }
.avatar-preview:hover { border-color:var(--cx-brand); box-shadow:0 0 0 4px rgba(124,58,237,.15); }
.avatar-preview img { width:100%; height:100%; object-fit:cover; }
.avatar-preview-overlay { position:absolute; inset:0; background:rgba(0,0,0,.5); display:flex; align-items:center; justify-content:center; font-size:22px; opacity:0; transition:opacity .15s; border-radius:20px; }
.avatar-preview:hover .avatar-preview-overlay { opacity:1; }
.avatar-hint { font-size:11.5px; color:var(--cx-muted); text-align:center; }
.avatar-file-input { display:none; }

/* Form */
.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.form-group { display:flex; flex-direction:column; gap:6px; }
.form-group.full { grid-column:1/-1; }
.form-label { font-size:11.5px; font-weight:700; color:var(--cx-text2); text-transform:uppercase; letter-spacing:.5px; }
.form-input { padding:11px 14px; background:var(--cx-surface2); border:1.5px solid var(--cx-border); border-radius:var(--r-sm); color:var(--cx-text); font-size:13.5px; font-family:inherit; outline:none; transition:border-color .15s,box-shadow .15s; }
.form-input:focus { border-color:var(--cx-brand); box-shadow:0 0 0 3px rgba(124,58,237,.15); }
.form-input::placeholder { color:var(--cx-muted); }
select.form-input { cursor:pointer; }
select.form-input option { background:#1e1b4b; color:#e2e8f0; }
.btn-submit { width:100%; padding:14px; margin-top:18px; background:linear-gradient(135deg,var(--cx-brand),var(--cx-brand2)); color:#fff; border:none; border-radius:var(--r-sm); font-size:14px; font-weight:800; font-family:inherit; cursor:pointer; box-shadow:0 4px 16px rgba(124,58,237,.4); transition:all .18s; display:flex; align-items:center; justify-content:center; gap:9px; }
.btn-submit:hover { transform:translateY(-1px); box-shadow:0 6px 22px rgba(124,58,237,.5); }
.btn-submit:disabled { opacity:.6; cursor:not-allowed; transform:none; }

/* Delete modal */
.del-modal .modal-body { text-align:center; padding:28px 24px; }
.del-icon { font-size:44px; margin-bottom:12px; }
.del-title { font-size:16px; font-weight:800; color:var(--cx-text); margin-bottom:8px; }
.del-sub { font-size:13px; color:var(--cx-text2); line-height:1.65; margin-bottom:24px; }
.del-actions { display:flex; gap:10px; }
.btn-cancel-del { flex:1; padding:12px; border-radius:var(--r-sm); background:var(--cx-surface2); border:1px solid var(--cx-border); color:var(--cx-text2); font-size:13px; font-weight:700; font-family:inherit; cursor:pointer; transition:all .15s; }
.btn-cancel-del:hover { background:var(--cx-surface); color:var(--cx-text); }
.btn-confirm-del { flex:1; padding:12px; border-radius:var(--r-sm); background:linear-gradient(135deg,#dc2626,#ef4444); border:none; color:#fff; font-size:13px; font-weight:800; font-family:inherit; cursor:pointer; box-shadow:0 4px 12px rgba(239,68,68,.3); transition:all .15s; }
.btn-confirm-del:hover { transform:translateY(-1px); }

/* Toast */
.toast { position:fixed; bottom:28px; right:28px; z-index:9999; padding:13px 22px; border-radius:var(--r-sm); font-size:13px; font-weight:700; color:#fff; box-shadow:0 10px 30px rgba(0,0,0,.4); transform:translateY(24px); opacity:0; transition:all .28s cubic-bezier(.23,1,.32,1); pointer-events:none; max-width:340px; }
.toast.show { transform:translateY(0); opacity:1; }
.toast-success { background:linear-gradient(135deg,#059669,#10b981); }
.toast-error   { background:linear-gradient(135deg,#dc2626,#ef4444); }

/* Flash */
.flash { display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:var(--r-sm); border:1px solid; font-size:13px; font-weight:600; margin-bottom:18px; }
.flash-success { background:rgba(16,185,129,.08); border-color:rgba(16,185,129,.2); color:#34d399; }
.flash-error   { background:rgba(239,68,68,.08); border-color:rgba(239,68,68,.2); color:#f87171; }

/* ── MODE CLAIR overrides ── */
body.cx-light .filter-bar,
body.cx-light .modal,
body.cx-light .modal-hd { background:#fff; border-color:rgba(0,0,0,.08); }
body.cx-light .driver-card { background:#fff; border-color:rgba(0,0,0,.07); }
body.cx-light .driver-card:hover { border-color:rgba(124,58,237,.3); }
body.cx-light .dc-stats,
body.cx-light .dc-status-info,
body.cx-light .search-input,
body.cx-light .form-input { background:#f3f4f6; border-color:rgba(0,0,0,.08); }
body.cx-light .dc-status-dot { border-color:#fff; }
body.cx-light .tab-btn:hover { background:#f3f4f6; }
body.cx-light select.form-input option { background:#fff; color:#111; }
body.cx-light .status-off { background:rgba(100,116,139,.06); color:#64748b; }

@media(max-width:1024px) { .stats-bar{grid-template-columns:repeat(3,1fr);} }
@media(max-width:900px) {
    .cx-sidebar { transform:translateX(-100%); }
    .cx-sidebar.open { transform:translateX(0); }
    .cx-wrap { padding-left:0; }
    .cx-hamburger { display:block; }
}
@media(max-width:768px) {
    .page-banner { padding:20px 16px 18px; }
    .pw { padding:16px 14px 48px; }
    .stats-bar { grid-template-columns:1fr 1fr; }
    .drivers-grid { grid-template-columns:1fr; }
    .form-grid { grid-template-columns:1fr; }
}
</style>
@endpush

@section('content')
{{-- ══ SIDEBAR ══ --}}
<aside class="cx-sidebar" id="cxSidebar">
    <div class="cx-brand-hd">
        <div class="cx-brand-top">
           <a href="{{ route('company.dashboard') }}" class="cx-logo">
                 <div class="cx-logo-icon"><img src="/images/Shopio3.jpeg" alt="Shopio" style="width: 40px;;height: 40px;object-fit:cover;border-radius:9px"></div>
                <span class="sb-shop-name">{{ $company->name }}</span>
            </a>
            <button class="cx-close-btn" id="cxCloseBtn">✕</button>
        </div>
        <div class="cx-sys-badge">
            <span class="cx-sys-dot"></span> Système actif
        </div>
    </div>

    <nav class="cx-nav">
        <div class="cx-nav-sec">Principal</div>
        <a href="{{ route('company.dashboard') }}" class="cx-nav-item">
            <span class="cx-nav-ico">⊞</span> Tableau de bord
        </a>
        <a href="{{ route('company.chat.inbox') }}" class="cx-nav-item">
            <span class="cx-nav-ico">💬</span> Demandes (Chat)
        </a>
        <a href="{{ route('company.orders.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">📦</span> Commandes
        </a>
        <a href="{{ route('company.drivers.index') }}" class="cx-nav-item active">
            <span class="cx-nav-ico">🚴</span> Chauffeurs
        </a>
        <a href="{{ route('company.livraisons.index') }}" class="cx-nav-item"><span class="cx-nav-ico">🚚</span> Livraisons</a>
        <a href="{{ route('company.carte.index') }}" class="cx-nav-item"><span class="cx-nav-ico">🗺️</span> Carte en direct</a>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">🏪</span> Boutiques</a>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">👥</span> Clients</a>

        <div class="cx-nav-sec">Gestion</div>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">📍</span> Zone de livraison</a>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">💲</span> Tarification</a>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">🔔</span> Notifications</a>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">📊</span> Rapports</a>

        <div class="cx-nav-sec">Configuration</div>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">⚙️</span> Paramètres</a>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">👤</span> Utilisateurs</a>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">🔌</span> Intégrations</a>
    </nav>

    <div class="cx-user-foot">
        <div class="cx-user-row">
            <div class="cx-user-av">{{ $ini }}</div>
            <div style="flex:1;min-width:0">
                <div class="cx-user-name">{{ Str::limit($u->name ?? 'Admin', 16) }}</div>
                <div class="cx-user-role">{{ $company->name }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="cx-logout-btn" title="Déconnexion">⏻</button>
            </form>
        </div>
        <div class="cx-dark-row" id="cxDarkToggle">
            <span class="cx-dark-lbl" id="cxDarkLbl">Mode sombre</span>
            <div class="cx-toggle" id="cxDarkSwitch"></div>
        </div>
    </div>
</aside>

<div class="cx-overlay" id="cxOverlay"></div>

{{-- ══ MAIN ══ --}}
<div class="cx-wrap">
<main class="cx-main">

  

    <div class="cx-content">

        {{-- Banner --}}
        <div class="page-banner">
            <div class="banner-grid"></div>
            <div class="banner-glow"></div>
            <div class="banner-glow2"></div>
            <div class="banner-inner">
                <div class="banner-top">
                    <div class="banner-title-group">
                        <div class="banner-icon">🚴</div>
                        <div>
                            <h1 class="banner-title">Mes chauffeurs</h1>
                            <div class="banner-sub">{{ $company->name }} · Gestion de l'équipe de livraison</div>
                        </div>
                    </div>
                    <a href="{{ route('company.drivers.create') }}" class="btn-add-driver">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Ajouter un chauffeur
                    </a>
                </div>

                <div class="stats-bar">
                    <div class="stat-pill" style="--s-accent:#a78bfa">
                        <div class="stat-pill-ico">👥</div>
                        <div class="stat-pill-val">{{ $stats['total'] }}</div>
                        <div class="stat-pill-lbl">Total</div>
                    </div>
                    <div class="stat-pill" style="--s-accent:#34d399">
                        <div class="stat-pill-ico">🟢</div>
                        <div class="stat-pill-val">{{ $stats['available'] }}</div>
                        <div class="stat-pill-lbl">Disponibles</div>
                    </div>
                    <div class="stat-pill" style="--s-accent:#fbbf24">
                        <div class="stat-pill-ico">🔄</div>
                        <div class="stat-pill-val">{{ $stats['busy'] }}</div>
                        <div class="stat-pill-lbl">En mission</div>
                    </div>
                    <div class="stat-pill" style="--s-accent:#94a3b8">
                        <div class="stat-pill-ico">⚫</div>
                        <div class="stat-pill-val">{{ $stats['offline'] }}</div>
                        <div class="stat-pill-lbl">Hors ligne</div>
                    </div>
                    <div class="stat-pill" style="--s-accent:#60a5fa">
                        <div class="stat-pill-ico">✅</div>
                        <div class="stat-pill-val">{{ $stats['livrees'] }}</div>
                        <div class="stat-pill-lbl">Livraisons</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="pw">

            @if(session('success'))
            <div class="flash flash-success">✅ {{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="flash flash-error">❌ {{ session('error') }}</div>
            @endif

            {{-- Filter bar --}}
            <div class="filter-bar">
                <button class="tab-btn active" id="tab-all"       onclick="filterStatus('all')">Tous ({{ $stats['total'] }})</button>
                <button class="tab-btn"        id="tab-available" onclick="filterStatus('available')">🟢 Disponibles ({{ $stats['available'] }})</button>
                <button class="tab-btn"        id="tab-busy"      onclick="filterStatus('busy')">🟡 En mission ({{ $stats['busy'] }})</button>
                <button class="tab-btn"        id="tab-offline"   onclick="filterStatus('offline')">⚫ Hors ligne ({{ $stats['offline'] }})</button>
                <div class="tab-sep"></div>
                <div class="search-wrap">
                    <svg class="search-ico" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input class="search-input" type="text" id="searchInput" placeholder="Rechercher un chauffeur…" oninput="filterCards()">
                </div>
            </div>

            {{-- Grid --}}
            <div class="drivers-grid" id="driversGrid">
                @forelse($drivers as $driver)
                @php
                    $statusCfg = match($driver->status) {
                        'available' => ['cls'=>'status-avail','dot'=>'dot-avail','lbl'=>'Disponible','accent'=>'linear-gradient(90deg,#10b981,#059669)'],
                        'busy'      => ['cls'=>'status-busy', 'dot'=>'dot-busy', 'lbl'=>'En mission', 'accent'=>'linear-gradient(90deg,#f59e0b,#d97706)'],
                        default     => ['cls'=>'status-off',  'dot'=>'dot-off',  'lbl'=>'Hors ligne', 'accent'=>'linear-gradient(90deg,#475569,#334155)'],
                    };
                    $ini2 = strtoupper(substr($driver->name,0,1)) . strtoupper(substr(explode(' ',$driver->name)[1] ?? 'X',0,1));
                @endphp
                <div class="driver-card" data-status="{{ $driver->status }}" data-name="{{ strtolower($driver->name) }}">
                    <div class="driver-card-accent" style="background:{{ $statusCfg['accent'] }}"></div>
                    <div class="dc-header">
                        <div class="dc-avatar-wrap">
                            <div class="dc-avatar" style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
                                @if($driver->photo)
                                    <img src="{{ asset('storage/'.$driver->photo) }}" alt="{{ $driver->name }}">
                                @else
                                    {{ $ini2 }}
                                @endif
                            </div>
                            <div class="dc-status-dot {{ $statusCfg['dot'] }}"></div>
                        </div>
                        <div class="dc-actions">
                            <a href="{{ route('company.drivers.edit', $driver) }}" class="dc-action-btn" title="Modifier">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <button class="dc-action-btn del" onclick="openDeleteModal({{ $driver->id }}, '{{ e($driver->name) }}')" title="Supprimer">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="dc-body">
                        <div class="dc-name">{{ $driver->name }}</div>
                        <div class="dc-phone">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.77 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            {{ $driver->phone ?? 'Aucun numéro' }}
                        </div>
                        <div class="dc-status {{ $statusCfg['cls'] }}">
                            <span class="status-dot-sm" style="background:{{ $driver->status==='available'?'#10b981':($driver->status==='busy'?'#f59e0b':'#475569') }}"></span>
                            {{ $statusCfg['lbl'] }}
                        </div>
                        <div class="dc-stats">
                            <div class="dc-stat">
                                <div class="dc-stat-val">{{ $driver->orders_count ?? 0 }}</div>
                                <div class="dc-stat-lbl">Total</div>
                            </div>
                            <div class="dc-stat">
                                <div class="dc-stat-val" style="color:#34d399">{{ $driver->livrees_count ?? 0 }}</div>
                                <div class="dc-stat-lbl">Livrées</div>
                            </div>
                            <div class="dc-stat">
                                <div class="dc-stat-val" style="color:#fbbf24">{{ $driver->en_cours_count ?? 0 }}</div>
                                <div class="dc-stat-lbl">En cours</div>
                            </div>
                        </div>
                        @php
                            $pillCfg = match($driver->status) {
                                'available' => ['pill-available','dot-sm-green','Disponible'],
                                'busy'      => ['pill-busy',     'dot-sm-amber','En mission'],
                                default     => ['pill-offline',  'dot-sm-gray', 'Hors ligne'],
                            };
                        @endphp
                        <div class="dc-status-info">
                            <span class="dc-status-info-label">Statut</span>
                            <span class="dc-status-pill {{ $pillCfg[0] }}">
                                <span class="dc-status-dot-sm {{ $pillCfg[1] }}"></span>
                                {{ $pillCfg[2] }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-ico-wrap">🚴</div>
                    <div class="empty-title">Aucun chauffeur enregistré</div>
                    <div class="empty-sub">Commencez par ajouter votre premier chauffeur pour assigner des livraisons.</div>
                    <a href="{{ route('company.drivers.create') }}" class="btn-add-driver" style="font-size:13px;padding:11px 20px;">+ Ajouter le premier chauffeur</a>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</main>
</div>

{{-- ═══════ MODAL AJOUTER ═══════ --}}
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-hd">
            <div class="modal-title"><div class="modal-title-ico">🚴</div> Ajouter un chauffeur</div>
            <button class="modal-close" onclick="closeModal('addModal')">✕</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ route('company.drivers.store') }}" enctype="multipart/form-data" id="addForm">
                @csrf
                <div class="avatar-upload">
                    <div class="avatar-preview" id="addAvatarPreview" onclick="document.getElementById('addPhotoInput').click()">
                        🚴<div class="avatar-preview-overlay">📷</div>
                    </div>
                    <div class="avatar-hint">Cliquez pour ajouter une photo (optionnel)</div>
                    <input type="file" name="photo" id="addPhotoInput" class="avatar-file-input" accept="image/*" onchange="previewAvatar(this,'addAvatarPreview')">
                </div>
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Nom complet *</label>
                        <input type="text" name="name" class="form-input" placeholder="Ex : Alpha Diallo" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="phone" class="form-input" placeholder="Ex : +224 622…">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Statut initial</label>
                        <select name="status" class="form-input">
                            <option value="available">🟢 Disponible</option>
                            <option value="offline">⚫ Hors ligne</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-submit">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Ajouter le chauffeur
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ═══════ MODAL MODIFIER ═══════ --}}
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-hd">
            <div class="modal-title"><div class="modal-title-ico">✏️</div> Modifier le chauffeur</div>
            <button class="modal-close" onclick="closeModal('editModal')">✕</button>
        </div>
        <div class="modal-body">
            <form method="POST" id="editForm" action="" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="avatar-upload">
                    <div class="avatar-preview" id="editAvatarPreview" onclick="document.getElementById('editPhotoInput').click()">
                        ✏️<div class="avatar-preview-overlay">📷</div>
                    </div>
                    <div class="avatar-hint">Cliquez pour changer la photo</div>
                    <input type="file" name="photo" id="editPhotoInput" class="avatar-file-input" accept="image/*" onchange="previewAvatar(this,'editAvatarPreview')">
                </div>
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Nom complet *</label>
                        <input type="text" name="name" id="editName" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="phone" id="editPhone" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Statut</label>
                        <select name="status" id="editStatus" class="form-input">
                            <option value="available">🟢 Disponible</option>
                            <option value="busy">🟡 En mission</option>
                            <option value="offline">⚫ Hors ligne</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-submit">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Enregistrer les modifications
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ═══════ MODAL SUPPRESSION ═══════ --}}
<div class="modal-overlay del-modal" id="deleteModal">
    <div class="modal" style="max-width:400px">
        <div class="modal-body">
            <div class="del-icon">🗑️</div>
            <div class="del-title">Supprimer ce chauffeur ?</div>
            <div class="del-sub">
                Vous allez supprimer <strong id="delDriverName" style="color:var(--cx-text)"></strong>.<br>
                Cette action est <strong style="color:#f87171">irréversible</strong>.
            </div>
            <div class="del-actions">
                <button class="btn-cancel-del" onclick="closeModal('deleteModal')">← Annuler</button>
                <form id="deleteForm" method="POST" action="" style="flex:1">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-confirm-del" style="width:100%">🗑️ Confirmer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>
@endsection

@push('scripts')
<script>
/* ── Thème ── */
(function() {
    const body   = document.body;
    const sw     = document.getElementById('cxDarkSwitch');
    const lbl    = document.getElementById('cxDarkLbl');
    const toggle = document.getElementById('cxDarkToggle');
    const saved  = localStorage.getItem('cx-theme') || 'dark';

    function apply(theme) {
        if (theme === 'light') {
            body.classList.remove('cx-dark'); body.classList.add('cx-light');
            if (sw)  sw.classList.remove('on');
            if (lbl) lbl.textContent = 'Mode clair';
        } else {
            body.classList.remove('cx-light'); body.classList.add('cx-dark');
            if (sw)  sw.classList.add('on');
            if (lbl) lbl.textContent = 'Mode sombre';
        }
    }
    apply(saved);
    document.documentElement.classList.remove('cx-prelight');

    toggle?.addEventListener('click', () => {
        const isLight = body.classList.contains('cx-light');
        const next = isLight ? 'dark' : 'light';
        apply(next);
        localStorage.setItem('cx-theme', next);
    });
})();

/* ── Sidebar mobile ── */
const ham     = document.getElementById('cxHamburger');
const sidebar = document.getElementById('cxSidebar');
const overlay = document.getElementById('cxOverlay');
const closeBtn = document.getElementById('cxCloseBtn');
function openSb()  { sidebar?.classList.add('open'); overlay?.classList.add('open'); closeBtn && (closeBtn.style.display='flex'); }
function closeSb() { sidebar?.classList.remove('open'); overlay?.classList.remove('open'); }
ham?.addEventListener('click', openSb);
overlay?.addEventListener('click', closeSb);
closeBtn?.addEventListener('click', closeSb);

/* ── Modals ── */
function openModal(id)  { document.getElementById(id).classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow=''; }
document.querySelectorAll('.modal-overlay').forEach(el =>
    el.addEventListener('click', e => { if (e.target === el) closeModal(el.id); })
);
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') ['addModal','editModal','deleteModal'].forEach(closeModal);
});

function openEditModal(id, name, phone, status) {
    document.getElementById('editForm').action = `/company/drivers/${id}`;
    document.getElementById('editName').value   = name;
    document.getElementById('editPhone').value  = phone;
    document.getElementById('editStatus').value = status;
    const prev = document.getElementById('editAvatarPreview');
    prev.innerHTML = `✏️<div class="avatar-preview-overlay">📷</div>`;
    openModal('editModal');
}

function openDeleteModal(id, name) {
    document.getElementById('deleteForm').action = `/company/drivers/${id}`;
    document.getElementById('delDriverName').textContent = name;
    openModal('deleteModal');
}

/* ── Avatar preview ── */
function previewAvatar(input, previewId) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const prev = document.getElementById(previewId);
        prev.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:20px"><div class="avatar-preview-overlay">📷</div>`;
    };
    reader.readAsDataURL(file);
}

/* ── Filtres ── */
let currentFilter = 'all';

function filterStatus(status) {
    currentFilter = status;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active','active-green','active-amber','active-gray'));
    const btn = document.getElementById('tab-' + status);
    if (status === 'available') btn.classList.add('active-green');
    else if (status === 'busy') btn.classList.add('active-amber');
    else if (status === 'offline') btn.classList.add('active-gray');
    else btn.classList.add('active');
    filterCards();
}

function filterCards() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const cards  = document.querySelectorAll('.driver-card');
    let visible  = 0;
    cards.forEach(card => {
        const ok = (currentFilter === 'all' || card.dataset.status === currentFilter) &&
                   (!search || card.dataset.name.includes(search));
        card.style.display = ok ? '' : 'none';
        if (ok) visible++;
    });
    let empty = document.getElementById('dynamicEmpty');
    if (visible === 0 && cards.length > 0) {
        if (!empty) {
            empty = document.createElement('div');
            empty.id = 'dynamicEmpty';
            empty.className = 'empty-state';
            empty.innerHTML = '<div class="empty-ico-wrap">🔍</div><div class="empty-title">Aucun résultat</div><div class="empty-sub">Essayez un autre filtre ou une autre recherche.</div>';
            document.getElementById('driversGrid').appendChild(empty);
        }
        empty.style.display = '';
    } else if (empty) {
        empty.style.display = 'none';
    }
}

/* ── Toast flash ── */
function toast(msg, type = 'success') {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = `toast toast-${type} show`;
    setTimeout(() => el.classList.remove('show'), 4000);
}
@if(session('success'))
    setTimeout(() => toast('✅ {{ session('success') }}', 'success'), 200);
@endif
@if(session('error'))
    setTimeout(() => toast('❌ {{ session('error') }}', 'error'), 200);
@endif
</script>
@endpush
