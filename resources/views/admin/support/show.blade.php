@extends('layouts.app')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box}
:root{
    --brand:#7c3aed;--blt:#8b5cf6;--bdk:#5b21b6;--glow:rgba(124,58,237,.22);
    --sb:#1e1b4b;--sb-text:rgba(255,255,255,.88);--sb-w:268px;
    --bg:#f1f5f9;--card:#fff;--bd:rgba(0,0,0,.07);
    --text:#0f172a;--muted:#64748b;
    --green:#10b981;--gbg:rgba(16,185,129,.1);
    --red:#ef4444;--rbg:rgba(239,68,68,.1);
    --indigo:#6366f1;--ibg:rgba(99,102,241,.1);
    --font:'Segoe UI',system-ui,sans-serif;
}
body{font-family:var(--font);background:var(--bg);color:var(--text);margin:0;-webkit-font-smoothing:antialiased}
.sa{display:flex;min-height:100vh}

/* ─── SIDEBAR ─── */
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:200;overflow-y:auto;overflow-x:hidden;transition:transform .28s cubic-bezier(.4,0,.2,1);scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.15) transparent}
.sb::-webkit-scrollbar{width:3px}.sb::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
.sb-logo{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:11px;flex-shrink:0}
.sb-ico-wrap{width:40px;height:40px;border-radius:11px;background:linear-gradient(135deg,#a78bfa,#7c3aed);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(167,139,250,.45);flex-shrink:0}
.sb-appname{font-size:14.5px;font-weight:900;color:#fff;letter-spacing:-.2px}
.sb-apptag{font-size:10px;font-weight:700;color:#c4b5fd;text-transform:uppercase;letter-spacing:.9px;margin-top:2px}
.sb-me{padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:10px;flex-shrink:0;background:rgba(255,255,255,.05)}
.sb-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#a78bfa,#6d28d9);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;color:#fff;flex-shrink:0;border:2px solid rgba(196,181,253,.5)}
.sb-name{font-size:13px;font-weight:800;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:172px}
.sb-badge{display:inline-block;font-size:9.5px;font-weight:700;color:#e9d5ff;background:rgba(167,139,250,.22);border:1px solid rgba(196,181,253,.4);padding:2px 8px;border-radius:20px;margin-top:3px;text-transform:uppercase;letter-spacing:.6px}
.sb-nav{padding:8px 0;flex:1}
.sb-sec{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.3px;padding:16px 18px 6px;display:flex;align-items:center;gap:6px}
.sb-sec.shop{color:#6ee7b7}.sb-sec.livr{color:#93c5fd}.sb-sec.fin{color:#fcd34d}.sb-sec.plat{color:#c4b5fd}
.sb-a{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--sb-text);text-decoration:none;font-size:13px;font-weight:600;border-left:3px solid transparent;transition:all .15s;cursor:pointer}
.sb-a:hover{background:rgba(255,255,255,.09);color:#fff}
.sb-a.on{background:rgba(167,139,250,.22);border-left-color:#a78bfa;color:#fff;font-weight:700}
.sb-i{width:20px;height:20px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.85}
.sb-ft-ico{width:16px;height:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.7}
.sb-ft{padding:12px 18px;border-top:1px solid rgba(255,255,255,.12);flex-shrink:0;background:rgba(0,0,0,.12)}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:8px;color:rgba(255,255,255,.7);font-size:12.5px;font-weight:600;background:none;border:none;cursor:pointer;font-family:var(--font);width:100%;text-align:left;text-decoration:none;transition:all .15s}
.sb-ft-row:hover{background:rgba(255,255,255,.1);color:#fff}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--green);animation:blink 2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:199;backdrop-filter:blur(2px)}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb.closed{transform:translateX(-100%)}

/* ─── MAIN ─── */
.mn{flex:1;margin-left:var(--sb-w);display:flex;flex-direction:column;height:100vh;min-width:0;transition:margin-left .28s}
.mn.sb-closed{margin-left:0}
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:center;padding:0 22px;gap:12px;position:sticky;top:0;z-index:100;flex-shrink:0}
.ham{display:flex;width:32px;height:32px;background:none;border:none;cursor:pointer;border-radius:7px;align-items:center;justify-content:center;color:var(--muted);transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
.tb-bc{font-size:12.5px;color:var(--muted);display:flex;align-items:center;gap:6px;flex:1;overflow:hidden}
.tb-bc a{color:var(--muted);text-decoration:none}.tb-bc a:hover{color:var(--brand)}
.tb-bc span.cur{color:var(--text);font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

/* ─── TICKET HEADER ─── */
.tk-hd{padding:18px 24px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:flex-start;gap:16px;flex-shrink:0;flex-wrap:wrap}
.tk-info{flex:1;min-width:0}
.tk-subject{font-size:17px;font-weight:800;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.tk-meta{font-size:12.5px;color:var(--muted);margin-top:5px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.badge-open{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:700;background:rgba(16,185,129,.1);color:#047857}
.badge-closed{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:700;background:#f1f5f9;color:var(--muted)}
.badge-dot{width:6px;height:6px;border-radius:50%}
.badge-open .badge-dot{background:#10b981}
.badge-closed .badge-dot{background:var(--muted)}
.btn-close-ticket{display:inline-flex;align-items:center;gap:7px;padding:8px 18px;border-radius:8px;border:1.5px solid #fca5a5;background:rgba(239,68,68,.06);color:#ef4444;font-size:13px;font-weight:700;cursor:pointer;font-family:var(--font);transition:all .14s;flex-shrink:0}
.btn-close-ticket:hover{background:#ef4444;color:#fff;border-color:#ef4444}

/* ─── CHAT ─── */
.chat-area{flex:1;overflow:hidden;display:flex;flex-direction:column}
.messages{flex:1;overflow-y:auto;padding:24px;display:flex;flex-direction:column;gap:16px;scroll-behavior:smooth}
.messages::-webkit-scrollbar{width:4px}.messages::-webkit-scrollbar-thumb{background:var(--bd);border-radius:4px}
.msg-row{display:flex;gap:10px;max-width:75%}
.msg-row.mine{align-self:flex-end;flex-direction:row-reverse}
.msg-row.theirs{align-self:flex-start}
.msg-av{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0;margin-top:2px}
.msg-av.shop{background:linear-gradient(135deg,#6366f1,#4f46e5)}
.msg-av.admin{background:linear-gradient(135deg,#1e1b4b,#4c1d95)}
.msg-name{font-size:11px;font-weight:700;color:var(--muted);margin-bottom:4px}
.msg-row.mine .msg-name{text-align:right}
.msg-bubble{padding:11px 15px;border-radius:14px;font-size:13.5px;line-height:1.55;word-break:break-word}
.msg-row.mine .msg-bubble{background:var(--brand);color:#fff;border-bottom-right-radius:4px}
.msg-row.theirs .msg-bubble{background:var(--card);border:1px solid var(--bd);color:var(--text);border-bottom-left-radius:4px;box-shadow:0 1px 4px rgba(0,0,0,.05)}
.msg-time{font-size:10.5px;color:var(--muted);margin-top:4px}
.msg-row.mine .msg-time{text-align:right}

/* ─── INPUT ─── */
.chat-input{padding:16px 24px;background:var(--card);border-top:1px solid var(--bd);flex-shrink:0}
.input-row{display:flex;gap:10px;align-items:flex-end}
.chat-textarea{flex:1;padding:11px 14px;border:1.5px solid var(--bd);border-radius:12px;font-size:13.5px;font-family:var(--font);color:var(--text);background:var(--bg);outline:none;resize:none;min-height:44px;max-height:120px;transition:border-color .15s,box-shadow .15s;line-height:1.5}
.chat-textarea:focus{border-color:var(--brand);box-shadow:0 0 0 3px rgba(124,58,237,.12);background:#fff}
.btn-send{width:44px;height:44px;border-radius:12px;background:var(--brand);border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .14s}
.btn-send:hover{background:var(--bdk)}
.closed-banner{display:flex;align-items:center;gap:10px;padding:14px 24px;background:#f8fafc;border-top:1px solid var(--bd);font-size:13px;color:var(--muted);font-weight:600;flex-shrink:0}
.flash-ok{display:flex;align-items:center;gap:10px;padding:12px 24px;background:rgba(16,185,129,.08);border-bottom:1px solid rgba(16,185,129,.2);color:#065f46;font-size:13px;font-weight:600;flex-shrink:0}

@media(max-width:900px){
    .sb{transform:translateX(-100%)}.sb.open{transform:none}
    .mn{margin-left:0}.sb-ov.open{display:block}
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
'star'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
'ticket' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2z"/></svg>',
'cog'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
'logout' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
'profile'=> '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
'close'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
'menu'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
'check'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="20 6 9 17 4 12"/></svg>',
'store_sm'=> '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M3 9h18v1a4 4 0 0 1-8 0 4 4 0 0 1-8 0V9z"/><path d="M4 10v9a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-9"/><path d="M9 21v-7h6v7"/><path d="M3 9l2.5-5h13L21 9"/></svg>',
];
    $u        = Auth::user();
    $meInit   = strtoupper(substr($u->name ?? 'S', 0, 1));
    $meName   = $u->name ?? 'SuperAdmin';
@endphp

<div class="sa">

{{-- ════════ SIDEBAR ════════ --}}
<aside class="sb" id="sb">
    <div class="sb-logo">
        <div class="sb-ico-wrap" style="color:#fff">{!! $I['brand_lg'] !!}</div>
        <div>
            <div class="sb-appname">{{ config('app.name','Shopio') }}</div>
            <div class="sb-apptag">Plateforme · Super Admin</div>
        </div>
        <button class="sb-close" onclick="closeSb()" title="Fermer la sidebar">{!! $I['close'] !!}</button>
    </div>
    <div class="sb-me">
        <div class="sb-av">{{ $meInit }}</div>
        <div style="min-width:0">
            <div class="sb-name">{{ Str::limit($meName,22) }}</div>
            <div class="sb-badge">Fondateur &amp; Développeur</div>
        </div>
    </div>
    <nav class="sb-nav">
        <a href="{{ route('admin.dashboard') }}" class="sb-a"><span class="sb-i">{!! $I['home'] !!}</span><span>Vue d'ensemble</span></a>
        <div class="sb-sec shop">── SaaS Boutiques</div>
        <a href="{{ route('admin.shops.index') }}" class="sb-a"><span class="sb-i">{!! $I['store'] !!}</span><span>Boutiques</span></a>
        <a href="{{ route('admin.products.index') }}" class="sb-a"><span class="sb-i">{!! $I['bag'] !!}</span><span>Produits</span></a>
        <a href="{{ route('admin.orders.index') }}" class="sb-a"><span class="sb-i">{!! $I['box'] !!}</span><span>Commandes boutiques</span></a>
        <a href="{{ route('admin.vendeurs.index') }}" class="sb-a"><span class="sb-i">{!! $I['brief'] !!}</span><span>Vendeurs &amp; Employés</span></a>
        <a href="{{ route('admin.clients.index') }}" class="sb-a"><span class="sb-i">{!! $I['user'] !!}</span><span>Clients boutiques</span></a>
        <div class="sb-sec livr">── SaaS Livraison</div>
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a"><span class="sb-i">{!! $I['truck'] !!}</span><span>Entreprises livraison</span></a>
        <a href="{{ route('admin.livreurs.index') }}" class="sb-a"><span class="sb-i">{!! $I['bike'] !!}</span><span>Livreurs</span></a>
        <a href="{{ route('admin.zones.index') }}" class="sb-a"><span class="sb-i">{!! $I['map'] !!}</span><span>Zones de livraison</span></a>
        <a href="{{ route('admin.suivi.index') }}" class="sb-a"><span class="sb-i">{!! $I['pin'] !!}</span><span>Suivi en temps réel</span></a>
        <div class="sb-sec fin">── Finance</div>
        <a href="{{ route('admin.paiements.index') }}" class="sb-a"><span class="sb-i">{!! $I['card'] !!}</span><span>Paiements</span></a>
        <a href="{{ route('admin.commissions.index') }}" class="sb-a"><span class="sb-i">{!! $I['trend'] !!}</span><span>Commissions</span></a>
        <a href="{{ route('admin.revenus-boutiques.index') }}" class="sb-a"><span class="sb-i">{!! $I['store'] !!}</span><span>Revenus boutiques</span></a>
        <a href="{{ route('admin.revenus-entreprises.index') }}" class="sb-a"><span class="sb-i">{!! $I['truck'] !!}</span><span>Revenus entreprises</span></a>
        <a href="{{ route('admin.revenus.index') }}" class="sb-a"><span class="sb-i">{!! $I['dollar'] !!}</span><span>Revenus plateforme</span></a>
        <div class="sb-sec plat">── Plateforme</div>
        <a href="{{ route('admin.users.index') }}" class="sb-a"><span class="sb-i">{!! $I['users'] !!}</span><span>Tous les utilisateurs</span></a>
        <a href="{{ route('admin.avis.index') }}" class="sb-a"><span class="sb-i">{!! $I['star'] !!}</span><span>Avis &amp; Notation</span></a>
        <a href="{{ route('admin.support.index') }}" class="sb-a on"><span class="sb-i">{!! $I['ticket'] !!}</span><span>Tickets support</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">{!! $I['cog'] !!}</span><span>Paramètres système</span></a>
    </nav>
    <div class="sb-ft">
        <div style="display:flex;align-items:center;gap:6px;padding:6px 10px;font-size:11.5px;color:rgba(255,255,255,.6);font-weight:600">
            <div class="live-dot"></div>Système opérationnel
        </div>
        <a href="{{ route('profile.edit') }}" class="sb-ft-row"><span class="sb-ft-ico">{!! $I['profile'] !!}</span>Mon profil</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf<button type="submit" class="sb-ft-row"><span class="sb-ft-ico">{!! $I['logout'] !!}</span>Déconnexion</button>
        </form>
    </div>
</aside>
<div class="sb-ov" id="sbOv" onclick="closeSb()"></div>

{{-- ════════ MAIN ════════ --}}
<div class="mn" id="mn">

    {{-- Topbar --}}
    <div class="tb">
        <button class="ham" onclick="toggleSb()">{!! $I['menu'] !!}</button>
        <div class="tb-bc">
            <a href="{{ route('admin.dashboard') }}" style="display:inline-flex;align-items:center;gap:4px">{!! $I['brand_lg'] !!} Admin</a>
            <span>›</span>
            <a href="{{ route('admin.support.index') }}" style="display:inline-flex;align-items:center;gap:4px">{!! $I['ticket'] !!} Tickets support</a>
            <span>›</span>
            <span class="cur">Ticket #{{ $ticket->id }}</span>
        </div>
    </div>

    @if(session('success'))
    <div class="flash-ok">{!! $I['check'] !!} {{ session('success') }}</div>
    @endif

    {{-- Ticket header --}}
    <div class="tk-hd">
        <div class="tk-info">
            <div class="tk-subject">{{ $ticket->subject }}</div>
            <div class="tk-meta">
                <span class="badge-{{ $ticket->status }}">
                    <span class="badge-dot"></span>
                    {{ $ticket->status === 'open' ? 'Ouvert' : 'Fermé' }}
                </span>
                @if($ticket->shop)
                    <span style="display:inline-flex;align-items:center;gap:4px">{!! $I['store_sm'] !!} {{ $ticket->shop->name }}</span>
                @endif
                <span>Ouvert par <strong>{{ $ticket->creator->name ?? '—' }}</strong></span>
                <span>le {{ $ticket->created_at->format('d/m/Y à H:i') }}</span>
            </div>
        </div>
        @if($ticket->status === 'open')
        <form method="POST" action="{{ route('support.close', $ticket) }}">
            @csrf
            <button type="submit" class="btn-close-ticket" onclick="return confirm('Fermer ce ticket ?')">{!! $I['check'] !!} Fermer le ticket</button>
        </form>
        @endif
    </div>

    {{-- Chat --}}
    <div class="chat-area">
        <div class="messages" id="chatMessages">
            @foreach($ticket->messages as $m)
            @php
                $isMine  = $m->user_id === $u->id;
                $isAdmin = $m->author && $m->author->role === 'superadmin';
                $init    = strtoupper(substr($m->author->name ?? '?', 0, 1));
            @endphp
            <div class="msg-row {{ $isMine ? 'mine' : 'theirs' }}" data-id="{{ $m->id }}">
                <div class="msg-av {{ $isAdmin ? 'admin' : 'shop' }}">{{ $init }}</div>
                <div>
                    <div class="msg-name">
                        {{ $m->author->name ?? '—' }}
                        @if($isAdmin)
                            <span style="font-size:10px;background:rgba(124,58,237,.12);color:#5b21b6;padding:1px 7px;border-radius:10px;margin-left:5px;font-weight:800">SuperAdmin</span>
                        @endif
                    </div>
                    <div class="msg-bubble">{{ $m->body }}</div>
                    <div class="msg-time">{{ $m->created_at->format('d/m/Y à H:i') }}</div>
                </div>
            </div>
            @endforeach
        </div>

        @if($ticket->status === 'open')
        <div class="chat-input">
            <form method="POST" action="{{ route('support.messages.store', $ticket) }}" id="msgForm">
                @csrf
                <div class="input-row">
                    <textarea name="body" id="msgBody" class="chat-textarea" rows="1"
                              placeholder="Répondez à la boutique…" required
                              onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();document.getElementById('msgForm').submit();}"></textarea>
                    <button type="submit" class="btn-send" title="Envoyer">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
                <div style="font-size:11px;color:var(--muted);margin-top:6px">Entrée pour envoyer · Maj+Entrée pour un saut de ligne</div>
            </form>
        </div>
        @else
        <div class="closed-banner">
            {!! $I['check'] !!} Ce ticket est fermé.
            <a href="{{ route('admin.support.index') }}" style="color:var(--brand);font-weight:700;margin-left:auto;text-decoration:none">← Retour aux tickets</a>
        </div>
        @endif
    </div>

</div>{{-- /mn --}}
</div>{{-- /sa --}}
@endsection

@push('scripts')
<script>
const chatEl     = document.getElementById('chatMessages');
const currentUid = {{ auth()->id() }};
let   lastId     = {{ $ticket->messages->last()?->id ?? 0 }};
let   firstLoad  = true;

function scrollBottom(force = false) {
    if (!chatEl) return;
    const near = chatEl.scrollHeight - chatEl.scrollTop - chatEl.clientHeight < 150;
    if (force || near) chatEl.scrollTop = chatEl.scrollHeight;
}
function escHtml(s) { const d = document.createElement('div'); d.innerText = s; return d.innerHTML; }

async function pollMessages() {
    try {
        const r = await fetch("{{ route('support.messages.json', $ticket) }}", {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        });
        if (!r.ok) return;
        const msgs = await r.json();
        if (firstLoad) { firstLoad = false; scrollBottom(true); return; }
        const newMsgs = msgs.filter(m => m.id > lastId);
        if (!newMsgs.length) return;
        newMsgs.forEach(m => {
            lastId = Math.max(lastId, m.id);
            const isMine  = m.user_id === currentUid;
            const isAdmin = m.author && m.author.role === 'superadmin';
            const init    = (m.author?.name || '?')[0].toUpperCase();
            const adminBadge = isAdmin
                ? `<span style="font-size:10px;background:rgba(124,58,237,.12);color:#5b21b6;padding:1px 7px;border-radius:10px;margin-left:5px;font-weight:800">SuperAdmin</span>` : '';
            const row = document.createElement('div');
            row.className = `msg-row ${isMine ? 'mine' : 'theirs'}`;
            row.dataset.id = m.id;
            row.innerHTML = `
                <div class="msg-av ${isAdmin ? 'admin' : 'shop'}">${escHtml(init)}</div>
                <div>
                    <div class="msg-name">${escHtml(m.author?.name || '—')}${adminBadge}</div>
                    <div class="msg-bubble">${escHtml(m.body)}</div>
                    <div class="msg-time">${new Date(m.created_at).toLocaleString('fr-FR',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'})}</div>
                </div>`;
            chatEl.appendChild(row);
        });
        scrollBottom();
    } catch(e) { console.warn(e); }
}

document.addEventListener('DOMContentLoaded', () => scrollBottom(true));
@if($ticket->status === 'open')
setInterval(pollMessages, 5000);
@endif

const ta = document.getElementById('msgBody');
if (ta) {
    ta.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
}

function toggleSb(){const s=document.getElementById('sb'),m=document.getElementById('mn'),o=document.getElementById('sbOv');s.classList.toggle('open');s.classList.toggle('closed');m.classList.toggle('sb-closed');o.classList.toggle('open')}
function closeSb(){const s=document.getElementById('sb'),m=document.getElementById('mn'),o=document.getElementById('sbOv');s.classList.remove('open');s.classList.add('closed');m.classList.add('sb-closed');o.classList.remove('open')}
function nt(){alert('Bientôt disponible !');}
document.addEventListener('click',e=>{});
</script>
@endpush
