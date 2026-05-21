@extends('layouts.app')
@section('title', 'Support · ' . ($shop->name ?? 'Boutique'))
@php $bodyClass = 'is-dashboard'; @endphp

@php
$_s  = 'stroke-width="1.75"';
$_s2 = 'stroke-width="2"';
$I   = [];
$I['dash_nav']  = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>';
$I['msg_nav']   = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';
$I['box_nav']   = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>';
$I['tag_nav']   = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>';
$I['users_nav'] = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
$I['team_nav']  = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>';
$I['bike_nav']  = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 0 0-1-1h-1l-5 8h8"/><path d="M12 6l2.5 5"/></svg>';
$I['bldg_nav']  = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>';
$I['coin_nav']  = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="9" y1="10" x2="15" y2="10"/><line x1="9" y1="14" x2="15" y2="14"/></svg>';
$I['card_nav']  = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>';
$I['chart_nav'] = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>';
$I['list_nav']  = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><path d="M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2"/><path d="M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/><polyline points="9 12 11 14 15 10"/></svg>';
$I['gear_nav']  = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>';
$I['hdp_nav']   = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>';
$I['hdp_tb']    = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>';
@endphp

@push('styles')
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --brand:#6366f1;--brand-dk:#4f46e5;--brand-lt:#e0e7ff;
    --sb-border:rgba(255,255,255,.08);--sb-act:rgba(99,102,241,.52);
    --sb-hov:rgba(255,255,255,.07);--sb-txt:rgba(255,255,255,.62);--sb-txt-act:#fff;
    --bg:#f8fafc;--surface:#ffffff;--border:#e2e8f0;
    --text:#0f172a;--text-2:#475569;--muted:#94a3b8;
    --font:'Segoe UI',sans-serif;
    --r:14px;--r-sm:9px;--shadow-sm:0 1px 3px rgba(0,0,0,.06);
    --sb-w:232px;--top-h:58px;
}
html { font-family:var(--font); }
body { background:var(--bg); margin:0; color:var(--text); -webkit-font-smoothing:antialiased; }
.dash-wrap { display:flex; min-height:100vh; }
.dash-wrap .main { margin-left:var(--sb-w); flex:1; min-width:0; }

/* ── SIDEBAR ── */
.sidebar{background:linear-gradient(180deg,#0f0f59 0%,#0e0e16 40%,#10103a 100%);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;width:var(--sb-w);overflow-y:scroll;scrollbar-width:thin;scrollbar-color:rgba(99,102,241,.35) transparent;z-index:40;border-right:1px solid rgba(99,102,241,.15);box-shadow:6px 0 30px rgba(0,0,0,.35);}
.sidebar::-webkit-scrollbar{width:4px;}
.sidebar::-webkit-scrollbar-thumb{background:rgba(99,102,241,.4);border-radius:4px;}
.sb-brand{padding:18px 16px 14px;border-bottom:1px solid var(--sb-border);flex-shrink:0;position:relative;}
.sb-close{display:none;position:absolute;top:14px;right:12px;width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.6);font-size:18px;line-height:1;cursor:pointer;align-items:center;justify-content:center;transition:background .15s,color .15s;flex-shrink:0;}
.sb-close:hover{background:rgba(239,68,68,.18);color:#fca5a5;}
@media(max-width:900px){.sb-close{display:flex;}}
.sb-logo{display:flex;align-items:center;gap:10px;text-decoration:none;color:#fff;}
.sb-logo-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;}
.sb-shop-name{font-size:14.5px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:148px;color:#fff;}
.sb-status{display:flex;align-items:center;gap:6px;margin-top:9px;font-size:10.5px;color:var(--sb-txt);font-weight:500;}
.pulse{width:6px;height:6px;border-radius:50%;background:var(--brand);flex-shrink:0;animation:blink 2.2s ease-in-out infinite;box-shadow:0 0 5px var(--brand);}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.35}}
.sb-nav{padding:10px 10px 32px;flex:1;display:flex;flex-direction:column;gap:1px;}
.sb-section{font-size:9.5px;text-transform:uppercase;letter-spacing:1.8px;color:rgba(255,255,255,.48);padding:16px 10px 5px;font-weight:800;}
.sb-item{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--r-sm);font-size:13.5px;font-weight:600;color:rgba(255,255,255,.78);text-decoration:none;transition:background .15s,color .15s;position:relative;}
.sb-item:hover{background:var(--sb-hov);color:rgba(255,255,255,.96);}
.sb-item.active{background:var(--sb-act);color:#fff;box-shadow:0 2px 12px rgba(99,102,241,.25);}
.sb-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:20px;background:#a5b4fc;border-radius:0 3px 3px 0;}
.sb-item .ico{font-size:13px;width:26px;height:26px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border-radius:7px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.06);}
.sb-item.active .ico{background:rgba(255,255,255,.18);}
.sb-badge{margin-left:auto;background:var(--brand);color:#fff;font-size:10px;font-weight:700;border-radius:20px;padding:1px 7px;min-width:20px;text-align:center;}
.sb-group{display:flex;flex-direction:column;}
.sb-group-toggle{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--r-sm);font-size:13.5px;font-weight:600;color:rgba(255,255,255,.78);cursor:pointer;transition:background .15s;border:none;background:none;width:100%;text-align:left;font-family:var(--font);}
.sb-group-toggle:hover{background:var(--sb-hov);}
.sb-group-toggle .ico{font-size:13px;width:26px;height:26px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border-radius:7px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.06);}
.sb-group-toggle .sb-arrow{margin-left:auto;font-size:10px;color:rgba(255,255,255,.32);transition:transform .2s;}
.sb-group-toggle.open .sb-arrow{transform:rotate(90deg);}
.sb-sub{display:none;flex-direction:column;gap:1px;margin-left:12px;padding-left:14px;border-left:1px solid rgba(255,255,255,.1);margin-top:2px;}
.sb-sub.open{display:flex;}
.sb-sub .sb-item{font-size:13px;font-weight:500;padding:6px 10px;}
.sb-footer{padding:12px 10px;border-top:1px solid rgba(255,255,255,.08);flex-shrink:0;display:flex;flex-direction:column;gap:6px;position:sticky;bottom:0;background:linear-gradient(180deg,transparent 0%,#0b0b12 25%);z-index:1;}
.sb-user{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--r-sm);text-decoration:none;transition:background .15s;border:1px solid transparent;}
.sb-user:hover{background:rgba(255,255,255,.06);}
.sb-av{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#4338ca);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0;}
.sb-uname{font-size:13px;font-weight:700;color:#fff;}
.sb-urole{font-size:10.5px;color:rgba(255,255,255,.52);margin-top:1px;}
.sb-logout{display:flex;align-items:center;gap:8px;width:100%;padding:8px 10px;border-radius:var(--r-sm);background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.15);color:rgba(252,165,165,.92);font-size:12.5px;font-weight:600;font-family:var(--font);cursor:pointer;text-decoration:none;transition:background .15s;}
.sb-logout:hover{background:rgba(220,38,38,.18);}
.sb-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:39;}

/* ── MAIN ── */
.main { display:flex; flex-direction:column; height:100vh; overflow:hidden; }
.topbar { background:var(--surface); border-bottom:1px solid var(--border); padding:0 22px; height:var(--top-h); display:flex; align-items:center; gap:12px; flex-shrink:0; box-shadow:var(--shadow-sm); }
.btn-hamburger { display:none; background:none; border:none; cursor:pointer; padding:6px; color:var(--text); font-size:20px; }
.tb-title { font-size:14px; font-weight:700; color:var(--text); display:flex; align-items:center; gap:6px; }
.tb-sub { font-size:11px; color:var(--muted); margin-top:1px; }
.sa-online { display:flex; align-items:center; gap:5px; font-size:12px; font-weight:600; color:#10b981; margin-left:auto; }
.sa-dot { width:8px; height:8px; border-radius:50%; background:#10b981; animation:blink 2s ease-in-out infinite; }

/* ── CHAT ── */
.chat-thread {
    flex: 1; overflow-y: auto; padding: 16px 20px;
    display: flex; flex-direction: column; gap: 6px;
    background: #efeae2 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80'%3E%3Ccircle cx='40' cy='40' r='1.5' fill='%23c8b89a' opacity='.18'/%3E%3C/svg%3E");
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
}
.chat-thread::-webkit-scrollbar { width: 5px; }
.chat-thread::-webkit-scrollbar-thumb { background: #ccc; border-radius: 5px; }

/* Message row */
.chat-row { display:flex; align-items:flex-end; gap:8px; max-width:78%; animation:msgIn .18s ease both; }
@keyframes msgIn { from{opacity:0;transform:translateY(4px)} to{opacity:1;transform:translateY(0)} }
.chat-row.mine { margin-left:auto; flex-direction:row-reverse; }

/* Avatar SuperAdmin */
.chat-av {
    width:32px; height:32px; border-radius:50%; flex-shrink:0;
    background:linear-gradient(135deg,#6366f1,#4338ca);
    color:#fff; font-size:11px; font-weight:800;
    display:flex; align-items:center; justify-content:center;
}

/* Bubble */
.chat-wrap { display:flex; flex-direction:column; max-width:100%; }
.chat-name { font-size:11px; font-weight:700; color:#6366f1; margin-bottom:2px; padding-left:2px; }
.chat-bubble {
    padding: 9px 13px; border-radius: 8px; font-size: 13.5px;
    line-height: 1.55; word-break: break-word;
    box-shadow: 0 1px 2px rgba(0,0,0,.12);
}
.chat-row.mine   .chat-bubble { background:#dcf8c6; border-bottom-right-radius:2px; }
.chat-row.theirs .chat-bubble { background:#fff; border-bottom-left-radius:2px; }
.chat-meta { font-size:10.5px; color:#667781; margin-top:2px; text-align:right; padding-right:2px; }
.chat-row.theirs .chat-meta { text-align:left; padding-left:2px; }

/* Date separator */
.chat-date-sep { text-align:center; margin:10px 0; }
.chat-date-sep span { background:#fff; color:#667781; font-size:11.5px; font-weight:600; padding:4px 12px; border-radius:20px; box-shadow:0 1px 3px rgba(0,0,0,.1); }

/* Welcome empty */
.chat-welcome { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px; padding:40px 20px; }
.chat-welcome .w-ico { font-size:56px; opacity:.35; }
.chat-welcome p { font-size:14px; color:#667781; text-align:center; margin:0; }

/* Input zone */
.chat-input-zone {
    background:#f0f2f5; border-top:1px solid var(--border);
    padding:10px 16px; display:flex; gap:10px; align-items:flex-end;
    flex-shrink:0;
    padding-bottom: calc(10px + env(safe-area-inset-bottom,0px));
}
.chat-textarea {
    flex:1; padding:10px 16px; border-radius:24px; border:none;
    background:#fff; font-size:14px; font-family:var(--font);
    outline:none; resize:none; min-height:44px; max-height:120px;
    line-height:1.5; box-shadow:0 1px 2px rgba(0,0,0,.08);
}
.chat-send-btn {
    width:44px; height:44px; border-radius:50%;
    background:var(--brand); color:#fff; border:none;
    cursor:pointer; font-size:18px;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; transition:background .15s, transform .1s;
}
.chat-send-btn:hover { background:var(--brand-dk,#4f46e5); transform:scale(1.06); }
.chat-send-btn:disabled { opacity:.5; cursor:not-allowed; transform:none; }

/* Typing indicator */
.chat-typing { display:none; align-items:center; gap:4px; padding:6px 12px; background:#fff; border-radius:20px; width:fit-content; box-shadow:0 1px 2px rgba(0,0,0,.1); }
.chat-typing span { width:7px; height:7px; border-radius:50%; background:#aaa; animation:typing .9s infinite; }
.chat-typing span:nth-child(2) { animation-delay:.2s; }
.chat-typing span:nth-child(3) { animation-delay:.4s; }
@keyframes typing { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-6px)} }

/* ── Responsive ── */
@media(max-width:900px) {
    .dash-wrap .main { margin-left:0; }
    .sidebar { transform:translateX(-100%); transition:transform .25s cubic-bezier(.23,1,.32,1); }
    .sidebar.open { transform:translateX(0); }
    .sb-overlay.open { display:block; }
    .btn-hamburger { display:flex; }
    .topbar { padding:0 10px; }
    /* position:fixed empêche iOS de scroller la page quand le clavier s'ouvre */
    .main { position:fixed; top:0; left:0; right:0; height:100dvh; z-index:1; }
}
@media(max-width:640px) {
    .topbar { height:52px; gap:8px; }
    .tb-sub { display:none; }
    .sa-online { gap:4px; font-size:11px; }
    .sa-online span { display:none; }
    .chat-thread { padding:10px 6px; }
    .chat-row { max-width:90%; }
    .chat-bubble { font-size:13px; padding:8px 11px; }
    .chat-input-zone { padding:8px; padding-bottom:calc(8px + env(safe-area-inset-bottom,0px)); gap:6px; }
    .chat-textarea { font-size:16px; min-height:40px; padding:9px 14px; }
    .chat-send-btn { width:40px; height:40px; font-size:16px; }
    .chat-av { width:26px; height:26px; font-size:9px; }
}
@media(max-width:400px) {
    .chat-row { max-width:94%; }
    .chat-bubble { font-size:12.5px; }
}
* { scrollbar-width:thin; scrollbar-color:rgba(99,102,241,.2) transparent; }
</style>
@endpush

@section('content')
@php
    $u        = Auth::user();
    $parts    = explode(' ', $u->name ?? 'U X');
    $initials = strtoupper(substr($parts[0],0,1)).strtoupper(substr($parts[1] ?? 'X',0,1));
    $myId     = $u->id;
@endphp

<div class="dash-wrap">

{{-- SIDEBAR --}}
@if($shop)
<aside class="sidebar" id="sidebar">
    <div class="sb-brand">
        <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
            <div class="sb-logo-icon"><img src="/images/Shopio3.jpeg" alt="Shopio" style="width:100%;height:100%;object-fit:cover;border-radius:9px"></div>
            <span class="sb-shop-name">{{ $shop->name ?? 'Boutique' }}</span>
        </a>
        <button class="sb-close" id="btnCloseSidebar">✕</button>
        <div class="sb-status">
            <span class="pulse"></span>
            {{ ($shop->is_approved ?? true) ? 'Boutique active' : 'En attente' }}
        </div>
    </div>
    <nav class="sb-nav">
        <a href="{{ route('boutique.dashboard') }}" class="sb-item"><span class="ico">{!! $I['dash_nav'] !!}</span> Tableau de bord</a>
        <div class="sb-section">Boutique</div>
        <a href="{{ route('boutique.messages.hub') }}" class="sb-item"><span class="ico">{!! $I['msg_nav'] !!}</span> Messages</a>
        <a href="{{ route('boutique.orders.index') }}" class="sb-item"><span class="ico">{!! $I['box_nav'] !!}</span> Commandes</a>
        <a href="{{ route('products.index') }}" class="sb-item"><span class="ico">{!! $I['tag_nav'] !!}</span> Produits</a>
        <a href="{{ route('boutique.clients.index') }}" class="sb-item"><span class="ico">{!! $I['users_nav'] !!}</span> Clients</a>
        <a href="{{ route('boutique.employees.index') }}" class="sb-item"><span class="ico">{!! $I['team_nav'] !!}</span> Équipe</a>
        <div class="sb-section">Livraison</div>
        <a href="{{ route('boutique.livreurs.index') }}" class="sb-item"><span class="ico">{!! $I['bike_nav'] !!}</span> Livreurs</a>
        <a href="{{ route('delivery.companies.index') }}" class="sb-item"><span class="ico">{!! $I['bldg_nav'] !!}</span> Partenaires</a>
        <div class="sb-section">Finances</div>
        <div class="sb-group">
            <button class="sb-group-toggle" onclick="toggleGroup(this)" type="button">
                <span class="ico">{!! $I['coin_nav'] !!}</span> Finances &amp; Rapports <span class="sb-arrow">▶</span>
            </button>
            <div class="sb-sub">
                <a href="{{ route('boutique.payments.index') }}" class="sb-item"><span class="ico">{!! $I['card_nav'] !!}</span> Paiements</a>
                <a href="{{ route('boutique.commissions.index') }}" class="sb-item"><span class="ico">{!! $I['chart_nav'] !!}</span> Commissions</a>
                <a href="{{ route('boutique.reports.index') }}" class="sb-item"><span class="ico">{!! $I['list_nav'] !!}</span> Rapports</a>
                @if($u->role === 'admin')
                <a href="{{ route('shop.edit', $shop) }}" class="sb-item"><span class="ico">{!! $I['gear_nav'] !!}</span> Paramètres</a>
                @endif
            </div>
        </div>
        <div class="sb-section">Aide</div>
        <a href="{{ route('support.index') }}" class="sb-item active"><span class="ico">{!! $I['hdp_nav'] !!}</span> Support</a>
    </nav>
    <div class="sb-footer">
        <a href="{{ route('profile.edit') }}" class="sb-user">
            <div class="sb-av">{{ $initials }}</div>
            <div style="flex:1;min-width:0">
                <div class="sb-uname">{{ Str::limit($u->name, 20) }}</div>
                <div class="sb-urole">{{ $u->role === 'admin' ? 'Administrateur' : ucfirst($u->role) }}</div>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sb-logout">⎋ Se déconnecter</button>
        </form>
    </div>
</aside>
<div class="sb-overlay" id="sbOverlay"></div>
@endif

{{-- MAIN --}}
<main class="main">
    {{-- Topbar --}}
    <div class="topbar">
        @if($shop)<button class="btn-hamburger" id="btnMenu">☰</button>@endif
        <div>
            <div class="tb-title">{!! $I['hdp_tb'] !!} Support</div>
            <div class="tb-sub">{{ $shop->name ?? '' }} · Chat avec le SuperAdmin</div>
        </div>
        <div class="sa-online">
            <span class="sa-dot"></span>
            <span>SuperAdmin disponible</span>
        </div>
    </div>

    {{-- Thread --}}
    <div class="chat-thread" id="chatThread">

        @if($messages->isEmpty())
        <div class="chat-welcome">
            <div class="w-ico">🎧</div>
            <p style="font-size:16px;font-weight:700;color:#374151">Bienvenue dans le chat support</p>
            <p>Écrivez votre message ci-dessous.<br>Le SuperAdmin vous répondra dès que possible.</p>
        </div>
        @else

        @php $lastDate = null; @endphp
        @foreach($messages as $msg)
            @php
                $date = \Carbon\Carbon::parse($msg->created_at)->locale('fr')->isoFormat('D MMMM YYYY');
                $mine = $msg->user_id === $myId;
            @endphp
            @if($date !== $lastDate)
            <div class="chat-date-sep"><span>{{ $date }}</span></div>
            @php $lastDate = $date; @endphp
            @endif

            <div class="chat-row {{ $mine ? 'mine' : 'theirs' }}" data-id="{{ $msg->id }}">
                @if(!$mine)
                <div class="chat-av">SA</div>
                @endif
                <div class="chat-wrap">
                    @if(!$mine)
                    <div class="chat-name">SuperAdmin</div>
                    @endif
                    <div class="chat-bubble">{{ $msg->body }}</div>
                    <div class="chat-meta">{{ \Carbon\Carbon::parse($msg->created_at)->format('H:i') }}</div>
                </div>
            </div>
        @endforeach
        @endif

        <div class="chat-typing" id="chatTyping">
            <span></span><span></span><span></span>
        </div>
    </div>

    {{-- Input --}}
    <div class="chat-input-zone">
        <textarea id="chatInput" class="chat-textarea" placeholder="Écrire au support…" rows="1"
            onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMsg()}"
            oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,120)+'px'"></textarea>
        <button class="chat-send-btn" id="sendBtn" onclick="sendMsg()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>
    </div>
</main>
</div>
@endsection

@push('scripts')
<script>
const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const TICKET_ID = {{ $ticket->id }};
const MY_ID     = {{ $myId }};
let lastMsgId   = {{ $messages->last()?->id ?? 0 }};

function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function fmtTime(iso) {
    return iso ? iso.substring(11, 16) : '';
}

/* Ajouter un message dans le thread */
function appendMsg(m) {
    const mine  = m.author?.id === MY_ID || m.user_id === MY_ID;
    const thread = document.getElementById('chatThread');

    /* Supprimer le welcome si présent */
    thread.querySelector('.chat-welcome')?.remove();

    const row = document.createElement('div');
    row.className = 'chat-row ' + (mine ? 'mine' : 'theirs');
    row.dataset.id = m.id;
    row.innerHTML = `
        ${!mine ? '<div class="chat-av">SA</div>' : ''}
        <div class="chat-wrap">
            ${!mine ? '<div class="chat-name">SuperAdmin</div>' : ''}
            <div class="chat-bubble">${escHtml(m.body)}</div>
            <div class="chat-meta">${fmtTime(m.created_at)}</div>
        </div>`;

    /* Insérer avant l'indicateur de frappe */
    const typing = document.getElementById('chatTyping');
    thread.insertBefore(row, typing);
    setTimeout(scrollBottom, 30);
}

/* Envoyer un message */
async function sendMsg() {
    const input = document.getElementById('chatInput');
    const btn   = document.getElementById('sendBtn');
    const body  = input.value.trim();
    if (!body) return;

    input.value = '';
    input.style.height = 'auto';
    btn.disabled = true;

    try {
        const res = await fetch(`/support/${TICKET_ID}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ body }),
        });
        const data = await res.json();
        if (data.ok && data.msg) {
            appendMsg(data.msg);
            lastMsgId = data.msg.id;
        }
    } catch(e) {}

    btn.disabled = false;
    input.focus();
}

/* Polling — messages de l'autre côté (SuperAdmin) */
async function poll() {
    try {
        const res  = await fetch(`/support/${TICKET_ID}/messages.json`);
        const msgs = await res.json();
        const news = msgs.filter(m => m.id > lastMsgId);
        news.forEach(m => { appendMsg(m); lastMsgId = m.id; });
    } catch(e) {}
}
setInterval(poll, 5000);

/* Scroll vers le dernier message — plusieurs tentatives pour couvrir iOS/Android */
function scrollBottom() {
    const thread = document.getElementById('chatThread');
    if (!thread) return;
    thread.scrollTop = thread.scrollHeight;
    const last = thread.querySelector('.chat-row:last-of-type');
    if (last) last.scrollIntoView({ block: 'end' });
}

/* Init */
document.addEventListener('DOMContentLoaded', () => {
    scrollBottom();
    requestAnimationFrame(scrollBottom);
    setTimeout(scrollBottom, 150);

    /* Sidebar mobile */
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sbOverlay');
    document.getElementById('btnMenu')?.addEventListener('click', () => {
        sidebar?.classList.add('open'); overlay?.classList.add('open');
    });
    overlay?.addEventListener('click', () => {
        sidebar?.classList.remove('open'); overlay?.classList.remove('open');
    });
    document.getElementById('btnCloseSidebar')?.addEventListener('click', () => {
        sidebar?.classList.remove('open'); overlay?.classList.remove('open');
    });

    /* Sur mobile : ajuste la hauteur de .main au viewport visible (sans espace clavier) */
    const mainEl = document.querySelector('.main');
    const isMobile = () => window.innerWidth <= 900;
    if (mainEl) {
        const fitToViewport = () => {
            if (!isMobile()) return;
            const vv = window.visualViewport;
            if (vv) {
                mainEl.style.height = vv.height + 'px';
                mainEl.style.top    = vv.offsetTop + 'px';
            } else {
                mainEl.style.height = window.innerHeight + 'px';
            }
            scrollBottom();
        };
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', fitToViewport);
            window.visualViewport.addEventListener('scroll', fitToViewport);
        }
        window.addEventListener('resize', fitToViewport);
        fitToViewport();
    }
});

function toggleGroup(btn) {
    const sub = btn.nextElementSibling;
    const isOpen = sub.classList.contains('open');
    document.querySelectorAll('.sb-sub.open').forEach(s => {
        s.classList.remove('open'); s.previousElementSibling?.classList.remove('open');
    });
    if (!isOpen) { sub.classList.add('open'); btn.classList.add('open'); }
}
</script>
@endpush
