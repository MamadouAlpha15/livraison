@extends('layouts.app')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box}
:root{
    --brand:#7c3aed;--sb:#1e1b4b;--sb-text:rgba(255,255,255,.88);--sb-w:268px;
    --bg:#f1f5f9;--card:#fff;--bd:rgba(0,0,0,.07);
    --text:#0f172a;--muted:#64748b;
    --green:#10b981;--amber:#f59e0b;--red:#ef4444;--blue:#3b82f6;
    --font:'Segoe UI',system-ui,sans-serif;
}
body{font-family:var(--font);background:var(--bg);color:var(--text);margin:0;-webkit-font-smoothing:antialiased}
.sa{display:flex;min-height:100vh}
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:200;overflow-y:auto;overflow-x:hidden;transition:transform .28s cubic-bezier(.4,0,.2,1);scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.15) transparent}
.sb::-webkit-scrollbar{width:3px}.sb::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
.sb-logo{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:11px;flex-shrink:0}
.sb-ico-wrap{width:40px;height:40px;border-radius:11px;background:linear-gradient(135deg,#a78bfa,#7c3aed);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(167,139,250,.45);flex-shrink:0}
.sb-appname{font-size:14.5px;font-weight:900;color:#fff;letter-spacing:-.2px}
.sb-apptag{font-size:10px;font-weight:700;color:#c4b5fd;text-transform:uppercase;letter-spacing:.9px;margin-top:2px}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb-me{display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.08)}
.sb-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#a78bfa,#6d28d9);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:#fff;flex-shrink:0}
.sb-name{font-size:13px;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sb-badge{font-size:10px;color:#a78bfa;font-weight:600;margin-top:1px}
.sb-nav{padding:10px 0;flex:1}
.sb-sec{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.3px;padding:14px 18px 5px;color:rgba(255,255,255,.35)}
.sb-sec.shop{color:#6ee7b7}.sb-sec.livr{color:#93c5fd}.sb-sec.fin{color:#fcd34d}.sb-sec.plat{color:#c4b5fd}
.sb-a{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--sb-text);text-decoration:none;font-size:13px;font-weight:600;border-left:3px solid transparent;transition:all .15s}
.sb-a:hover{background:rgba(255,255,255,.09);color:#fff}
.sb-a.on{background:rgba(167,139,250,.22);border-left-color:#a78bfa;color:#fff;font-weight:700;box-shadow:inset 0 0 20px rgba(124,58,237,.08)}
.sb-i{width:20px;height:20px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.85}
.sb-ft-ico{width:16px;height:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.7}
.sb-ft{border-top:1px solid rgba(255,255,255,.1);padding:10px 0}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 18px;color:rgba(255,255,255,.6);text-decoration:none;font-size:12.5px;font-weight:600;cursor:pointer;background:none;border:none;width:100%;transition:all .15s}
.sb-ft-row:hover{color:#fff;background:rgba(255,255,255,.07)}
.live-dot{width:7px;height:7px;border-radius:50%;background:#10b981;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.mn{margin-left:var(--sb-w);flex:1;display:flex;flex-direction:column;min-width:0}
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:center;padding:0 24px;gap:16px;position:sticky;top:0;z-index:100}
.ham{width:34px;height:34px;border-radius:8px;background:none;border:1px solid var(--bd);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text);flex-shrink:0;transition:background .15s}.ham:hover{background:#f1f5f9}
.tb-bc{font-size:12.5px;color:var(--muted);display:flex;align-items:center;gap:6px;flex:1}
.tb-bc a{color:var(--muted);text-decoration:none}.tb-bc a:hover{color:var(--brand)}
.tb-ua{display:flex;align-items:center;gap:10px;margin-left:auto}
.tb-uav{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#a78bfa,#6d28d9);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff}
.tb-uname{font-size:13px;font-weight:700;color:var(--text)}.tb-urole{font-size:10.5px;color:var(--muted);font-weight:600}
.pw{padding:24px;max-width:1100px;margin:0 auto;width:100%}

.hero{background:linear-gradient(135deg,#1e1b4b 0%,#312e81 50%,#4338ca 100%);border-radius:16px;padding:28px 32px;display:flex;align-items:center;gap:24px;margin-bottom:24px;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;top:-30px;right:-30px;width:200px;height:200px;background:radial-gradient(circle,rgba(167,139,250,.2) 0%,transparent 70%);pointer-events:none}
.hero-ico{display:flex;align-items:center;justify-content:center;width:64px;height:64px;background:rgba(255,255,255,.12);border-radius:16px;color:#fff;filter:drop-shadow(0 4px 12px rgba(0,0,0,.3));flex-shrink:0}
.hero-title{font-size:22px;font-weight:900;color:#fff;letter-spacing:-.3px}
.hero-sub{font-size:13px;color:#c4b5fd;margin-top:3px}
.hero-stats{display:flex;gap:12px;margin-left:auto;flex-wrap:wrap}
.hs{background:rgba(255,255,255,.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);border-radius:12px;padding:12px 18px;text-align:center;min-width:90px}
.hs-v{font-size:22px;font-weight:900;color:#fff;line-height:1}
.hs-l{font-size:10px;font-weight:700;color:#c4b5fd;text-transform:uppercase;letter-spacing:.6px;margin-top:4px}

.flash{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:12.5px;font-weight:600}
.flash.ok{background:rgba(16,185,129,.1);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.flash.err{background:rgba(239,68,68,.1);color:#7f1d1d;border:1px solid rgba(239,68,68,.2)}

.card{background:var(--card);border:1px solid var(--bd);border-radius:14px;margin-bottom:22px;overflow:hidden}
.card-h{padding:16px 22px;border-bottom:1px solid var(--bd);font-size:14px;font-weight:800;display:flex;align-items:center;gap:8px}
.card-b{padding:22px}

.f-group{margin-bottom:16px}
.f-label{font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:7px}
.target-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px}
.target-card{border:1.5px solid var(--bd);border-radius:10px;padding:14px;cursor:pointer;transition:all .15s;text-align:center}
.target-card:hover{border-color:var(--brand)}
.target-card.on{border-color:var(--brand);background:rgba(124,58,237,.06);box-shadow:0 0 0 3px rgba(124,58,237,.1)}
.target-card-n{font-size:13px;font-weight:700;color:var(--text)}
.target-card-c{font-size:20px;font-weight:900;color:var(--brand);margin-top:4px}
.target-card-l{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-top:2px}
.f-input,.f-textarea{width:100%;border:1.5px solid var(--bd);border-radius:9px;padding:11px 13px;font-size:13.5px;font-family:inherit;outline:none;transition:border-color .15s;box-sizing:border-box}
.f-input:focus,.f-textarea:focus{border-color:var(--brand)}
.f-textarea{min-height:150px;resize:vertical;line-height:1.6}
.f-hint{font-size:11px;color:var(--muted);margin-top:5px}
.btn-send{display:inline-flex;align-items:center;gap:8px;padding:11px 22px;border-radius:9px;background:linear-gradient(135deg,#7c3aed,#5b21b6);color:#fff;border:none;font-size:13.5px;font-weight:700;cursor:pointer;transition:all .15s}
.btn-send:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(124,58,237,.35)}
.btn-send:disabled{opacity:.5;cursor:not-allowed;transform:none;box-shadow:none}

table{width:100%;border-collapse:collapse}
th{padding:11px 18px;text-align:left;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);background:#f8fafc;border-bottom:1px solid var(--bd);white-space:nowrap}
td{padding:13px 18px;border-bottom:1px solid var(--bd);font-size:13px;vertical-align:middle}
tr:last-child td{border-bottom:none}
.muted{color:var(--muted);font-size:12px}
.badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700}
.badge.client{background:rgba(16,185,129,.12);color:#047857}
.badge.admin{background:rgba(59,130,246,.12);color:#1d4ed8}
.badge.company{background:rgba(139,92,246,.12);color:#6d28d9}
.pbar-wrap{width:140px}
.pbar{height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;margin-bottom:4px}
.pbar-f{height:100%;background:linear-gradient(90deg,#7c3aed,#a78bfa);border-radius:3px;transition:width .5s}
.pbar-t{font-size:11px;color:var(--muted)}
.status-pill{font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:20px}
.status-pill.queued{background:rgba(245,158,11,.12);color:#b45309}
.status-pill.done{background:rgba(16,185,129,.12);color:#047857}
.empty-state{padding:50px 20px;text-align:center;color:var(--muted);font-size:13px}

@media(max-width:768px){
    .sb{transform:translateX(-100%)}.sb.open{transform:translateX(0)}
    .sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:199}.sb-ov.show{display:block}
    .mn{margin-left:0}.ham{display:flex}
    .hero{flex-direction:column;align-items:flex-start;padding:20px}
    .hero-stats{margin-left:0;width:100%}.hs{flex:1;min-width:70px}
}
</style>
@endpush

@section('content')
@php
$s = 'stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"';
$I = [
'brand_lg'=> '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h9l-1 8L21 10h-9l1-8z"/></svg>',
'home'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/><path d="M9 21V12h6v9"/></svg>',
'store'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M3 9h18v1a4 4 0 0 1-8 0 4 4 0 0 1-8 0V9z"/><path d="M4 10v9a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-9"/><path d="M9 21v-7h6v7"/><path d="M3 9l2.5-5h13L21 9"/></svg>',
'bag'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
'box'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>',
'brief'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="12.01"/></svg>',
'user'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
'users'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
'truck'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
'bike'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6h-3l-2 5H5.5"/><path d="M9 11l3-5h5l2 6h-4.5"/></svg>',
'map'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>',
'pin'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
'card'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
'trend'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>',
'dollar' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
'megaphone'=>'<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>',
'megaphone_lg'=>'<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>',
'star'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
'ticket' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2z"/></svg>',
'image_ico' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>',
'cog'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
'logout' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
'profile'=> '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
'close'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
'menu'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
'send'   => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>',
];
$me     = auth()->user();
$meName = $me->name ?? 'Admin';
$meInit = strtoupper(substr($meName,0,1));
@endphp

<div class="sa">
<aside class="sb" id="sb">
    <div class="sb-logo">
        <div class="sb-ico-wrap" style="color:#fff">{!! $I['brand_lg'] !!}</div>
        <div><div class="sb-appname">{{ config('app.name','Shopio') }}</div><div class="sb-apptag">Plateforme · Super Admin</div></div>
        <button class="sb-close" onclick="closeSb()">{!! $I['close'] !!}</button>
    </div>
    <div class="sb-me">
        <div class="sb-av">{{ $meInit }}</div>
        <div style="min-width:0"><div class="sb-name">{{ Str::limit($meName,22) }}</div><div class="sb-badge">{{ $me->role === 'superadmin' ? 'Fondateur & Développeur' : 'Personnel plateforme' }}</div></div>
    </div>
    <nav class="sb-nav">
        <a href="{{ route('admin.dashboard') }}" class="sb-a"><span class="sb-i">{!! $I['home'] !!}</span><span>Vue d'ensemble</span></a>
        <div class="sb-sec shop">── SaaS Boutiques</div>
        <a href="{{ route('admin.shops.index') }}" class="sb-a"><span class="sb-i">{!! $I['store'] !!}</span><span>Boutiques</span></a>
        <a href="{{ route('admin.products.index') }}" class="sb-a"><span class="sb-i">{!! $I['bag'] !!}</span><span>Produits</span></a>
        <a href="{{ route('admin.orders.index') }}" class="sb-a"><span class="sb-i">{!! $I['box'] !!}</span><span>Commandes boutiques</span></a>
        <a href="{{ route('admin.vendeurs.index') }}" class="sb-a"><span class="sb-i">{!! $I['brief'] !!}</span><span>Vendeurs &amp; Employés</span></a>
        <a href="{{ route('admin.clients.index') }}" class="sb-a"><span class="sb-i">{!! $I['user'] !!}</span><span>Clients boutiques</span></a>
        <a href="{{ route('admin.image-health.index') }}" class="sb-a"><span class="sb-i">{!! $I['image_ico'] !!}</span><span>Santé des images</span></a>
        <div class="sb-sec livr">── SaaS Livraison</div>
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a"><span class="sb-i">{!! $I['truck'] !!}</span><span>Entreprises livraison</span></a>
        <a href="{{ route('admin.livreurs.index') }}" class="sb-a"><span class="sb-i">{!! $I['bike'] !!}</span><span>Livreurs</span></a>
        <a href="{{ route('admin.zones.index') }}" class="sb-a"><span class="sb-i">{!! $I['map'] !!}</span><span>Zones de livraison</span></a>
        <a href="{{ route('admin.suivi.index') }}" class="sb-a"><span class="sb-i">{!! $I['pin'] !!}</span><span>Suivi en temps réel</span></a>
        <div class="sb-sec fin">── Finance</div>
        <a href="{{ route('admin.paiements.index') }}" class="sb-a"><span class="sb-i">{!! $I['card'] !!}</span><span>Paiements</span></a>
        <a href="{{ route('admin.payouts.index') }}" class="sb-a"><span class="sb-i">{!! $I['dollar'] !!}</span><span>Règlements boutiques</span></a>
        <a href="{{ route('admin.commissions.index') }}" class="sb-a"><span class="sb-i">{!! $I['trend'] !!}</span><span>Commissions</span></a>
        <a href="{{ route('admin.revenus.index') }}" class="sb-a"><span class="sb-i">{!! $I['dollar'] !!}</span><span>Revenus plateforme</span></a>
        <div class="sb-sec plat">── Plateforme</div>
        <a href="{{ route('admin.users.index') }}" class="sb-a"><span class="sb-i">{!! $I['users'] !!}</span><span>Tous les utilisateurs</span></a>
        <a href="{{ route('admin.avis.index') }}" class="sb-a"><span class="sb-i">{!! $I['star'] !!}</span><span>Avis &amp; Notation</span></a>
        <a href="{{ route('admin.support.index') }}" class="sb-a"><span class="sb-i">{!! $I['ticket'] !!}</span><span>Support conversations</span></a>
        <a href="{{ route('admin.broadcasts.index') }}" class="sb-a on"><span class="sb-i">{!! $I['megaphone'] !!}</span><span>Annonces par email</span></a>
        <a href="{{ route('admin.plans.index') }}" class="sb-a"><span class="sb-i">{!! $I['cog'] !!}</span><span>Paramètres système</span></a>
    </nav>
    <div class="sb-ft">
        <div style="display:flex;align-items:center;gap:6px;padding:6px 18px;font-size:11.5px;color:rgba(255,255,255,.6);font-weight:600"><div class="live-dot"></div>Système opérationnel</div>
        <a href="{{ route('profile.edit') }}" class="sb-ft-row"><span class="sb-ft-ico">{!! $I['profile'] !!}</span>Mon profil</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">@csrf<button type="submit" class="sb-ft-row"><span class="sb-ft-ico">{!! $I['logout'] !!}</span>Déconnexion</button></form>
    </div>
</aside>
<div class="sb-ov" id="sbOv" onclick="closeSb()"></div>

<div class="mn">
<header class="tb">
    <button class="ham" onclick="toggleSb()">{!! $I['menu'] !!}</button>
    <div class="tb-bc">
        <a href="{{ route('admin.dashboard') }}" style="display:inline-flex;align-items:center;gap:5px">{!! $I['home'] !!}Accueil</a>
        <span>›</span><span>Plateforme</span>
        <span>›</span><span style="color:var(--text);font-weight:700">Annonces par email</span>
    </div>
    <div class="tb-ua">
        <div class="tb-uav">{{ $meInit }}</div>
        <div><div class="tb-uname">{{ Str::limit($meName,14) }}</div><div class="tb-urole">{{ $me->role === 'superadmin' ? 'Super Admin' : 'Personnel' }}</div></div>
    </div>
</header>

<div class="pw">

    <div class="hero">
        <div class="hero-ico">{!! $I['megaphone_lg'] !!}</div>
        <div>
            <div class="hero-title">Annonces par email</div>
            <div class="hero-sub">Envoie un message ciblé à tous les comptes d'un groupe</div>
        </div>
        <div class="hero-stats">
            @foreach($targets as $role => $label)
            <div class="hs"><div class="hs-v">{{ number_format($counts[$role]) }}</div><div class="hs-l">{{ $label }}</div></div>
            @endforeach
        </div>
    </div>

    @if(session('success'))<div class="flash ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="flash err">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="flash err">{{ $errors->first() }}</div>@endif

    {{-- COMPOSE --}}
    <div class="card">
        <div class="card-h">{!! $I['megaphone'] !!} Nouvelle annonce</div>
        <div class="card-b">
            <form method="POST" action="{{ route('admin.broadcasts.store') }}" id="broadcastForm" onsubmit="return confirmSend()">
                @csrf
                <div class="f-group">
                    <label class="f-label">À qui envoyer ?</label>
                    <input type="hidden" name="target_role" id="targetRoleInput" value="{{ old('target_role', 'client') }}" required>
                    <div class="target-cards">
                        @foreach($targets as $role => $label)
                        <div class="target-card {{ old('target_role','client') === $role ? 'on' : '' }}" onclick="selectTarget('{{ $role }}', this)">
                            <div class="target-card-c">{{ number_format($counts[$role]) }}</div>
                            <div class="target-card-n">{{ $label }}</div>
                            <div class="target-card-l">destinataires</div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="f-group">
                    <label class="f-label" for="subject">Objet de l'email</label>
                    <input type="text" name="subject" id="subject" class="f-input" maxlength="200" required
                           placeholder="Ex : Nouveauté sur Shopio 🎉" value="{{ old('subject') }}">
                </div>

                <div class="f-group">
                    <label class="f-label" for="body">Message</label>
                    <textarea name="body" id="body" class="f-textarea" maxlength="5000" required
                              placeholder="Écris ton message ici — il sera envoyé tel quel dans l'email, avec la mise en page Shopio autour.">{{ old('body') }}</textarea>
                    <div class="f-hint">Le destinataire verra "Bonjour [Prénom]," automatiquement ajouté avant ton message.</div>
                </div>

                <button type="submit" class="btn-send" id="sendBtn">{!! $I['send'] !!} Envoyer l'annonce</button>
            </form>
        </div>
    </div>

    {{-- HISTORIQUE --}}
    <div class="card">
        <div class="card-h">Historique des annonces</div>
        @if($broadcasts->isEmpty())
        <div class="empty-state">Aucune annonce envoyée pour l'instant.</div>
        @else
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>Objet</th>
                        <th>Cible</th>
                        <th>Progression</th>
                        <th>Statut</th>
                        <th>Envoyé par</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="broadcastRows">
                    @foreach($broadcasts as $b)
                    <tr data-broadcast-id="{{ $b->id }}">
                        <td style="font-weight:700;max-width:260px">{{ Str::limit($b->subject, 50) }}</td>
                        <td><span class="badge {{ $b->target_role }}">{{ $targets[$b->target_role] ?? $b->target_role }}</span></td>
                        <td>
                            <div class="pbar-wrap">
                                <div class="pbar"><div class="pbar-f" data-sent="{{ $b->sent_count }}" data-failed="{{ $b->failed_count }}" data-total="{{ $b->recipient_count }}"
                                     style="width:{{ $b->recipient_count > 0 ? round((($b->sent_count+$b->failed_count)/$b->recipient_count)*100) : 0 }}%"></div></div>
                                <div class="pbar-t" data-progress-text>{{ $b->sent_count + $b->failed_count }}/{{ $b->recipient_count }}{{ $b->failed_count > 0 ? ' ('.$b->failed_count.' échec)' : '' }}</div>
                            </div>
                        </td>
                        <td><span class="status-pill {{ $b->status }}" data-status-pill>{{ $b->status === 'done' ? 'Terminé' : 'En cours' }}</span></td>
                        <td class="muted">{{ $b->sender?->name ?? '—' }}</td>
                        <td class="muted">{{ $b->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($broadcasts->hasPages())
        <div style="padding:14px 20px;border-top:1px solid var(--bd)">{{ $broadcasts->links() }}</div>
        @endif
        @endif
    </div>

</div>
</div>
</div>

@endsection

@push('scripts')
<script>
function toggleSb(){document.getElementById('sb').classList.toggle('open');document.getElementById('sbOv').classList.toggle('show')}
function closeSb(){document.getElementById('sb').classList.remove('open');document.getElementById('sbOv').classList.remove('show')}

function selectTarget(role, el){
    document.getElementById('targetRoleInput').value = role;
    document.querySelectorAll('.target-card').forEach(c => c.classList.remove('on'));
    el.classList.add('on');
}

function confirmSend(){
    var role = document.getElementById('targetRoleInput').value;
    var count = document.querySelector('.target-card.on .target-card-c')?.textContent.trim() || '?';
    return confirm('Envoyer cet email à ' + count + ' destinataire(s) ? Cette action ne peut pas être annulée une fois lancée.');
}

document.getElementById('broadcastForm').addEventListener('submit', function(){
    var btn = document.getElementById('sendBtn');
    setTimeout(function(){ btn.disabled = true; btn.textContent = 'Envoi en cours…'; }, 10);
});

/* Progression en direct pour les annonces "en cours" */
function pollBroadcastProgress(){
    var rows = document.querySelectorAll('tr[data-broadcast-id]');
    var hasQueued = false;
    rows.forEach(function(r){
        var pill = r.querySelector('[data-status-pill]');
        if (pill && pill.textContent.trim() === 'En cours') hasQueued = true;
    });
    if (!hasQueued) return;

    fetch('{{ route("admin.broadcasts.progress") }}', {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r => r.json())
        .then(list => {
            list.forEach(function(b){
                var row = document.querySelector('tr[data-broadcast-id="' + b.id + '"]');
                if (!row) return;
                var done = b.sent_count + b.failed_count;
                var pct = b.recipient_count > 0 ? Math.round((done / b.recipient_count) * 100) : 0;
                var fill = row.querySelector('.pbar-f');
                var text = row.querySelector('[data-progress-text]');
                if (fill) fill.style.width = pct + '%';
                if (text) text.textContent = done + '/' + b.recipient_count + (b.failed_count > 0 ? ' (' + b.failed_count + ' échec)' : '');
                if (b.status === 'done') {
                    var pill = row.querySelector('[data-status-pill]');
                    if (pill) { pill.textContent = 'Terminé'; pill.className = 'status-pill done'; }
                }
            });
        })
        .catch(() => {});
}
setInterval(pollBroadcastProgress, 8000);
</script>
@endpush
