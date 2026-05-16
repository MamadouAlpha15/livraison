@extends('layouts.app')
@section('title', 'Ticket #' . $ticket->id . ' · Support')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --brand:#6366f1;--brand-dk:#4f46e5;--brand-lt:#e0e7ff;--brand-mlt:#eef2ff;
    --sb-border:rgba(255,255,255,.08);--sb-act:rgba(99,102,241,.52);
    --sb-hov:rgba(255,255,255,.07);--sb-txt:rgba(255,255,255,.62);
    --bg:#f8fafc;--surface:#ffffff;--border:#e2e8f0;--border-dk:#cbd5e1;
    --text:#0f172a;--text-2:#475569;--muted:#94a3b8;
    --green:#10b981;
    --font:'Plus Jakarta Sans',sans-serif;--mono:'JetBrains Mono',monospace;
    --r:14px;--r-sm:9px;--shadow-sm:0 1px 3px rgba(0,0,0,.06);
    --sb-w:232px;--top-h:58px;
}
html{font-family:var(--font);}
body{background:var(--bg);margin:0;color:var(--text);-webkit-font-smoothing:antialiased;}
.dash-wrap{display:flex;min-height:100vh;}
.dash-wrap .main{margin-left:var(--sb-w);flex:1;min-width:0;}

/* ─── SIDEBAR ─── */
.sidebar{background:linear-gradient(180deg,#0f0f59 0%,#0e0e16 40%,#10103a 100%);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;width:var(--sb-w);overflow-y:scroll;scrollbar-width:thin;scrollbar-color:rgba(99,102,241,.35) transparent;z-index:40;border-right:1px solid rgba(99,102,241,.15);box-shadow:6px 0 30px rgba(0,0,0,.35);}
.sidebar::-webkit-scrollbar{width:4px;}
.sidebar::-webkit-scrollbar-track{background:rgba(255,255,255,.04);}
.sidebar::-webkit-scrollbar-thumb{background:rgba(99,102,241,.4);border-radius:4px;}
.sidebar::-webkit-scrollbar-thumb:hover{background:rgba(99,102,241,.7);}
.sb-brand{padding:18px 16px 14px;border-bottom:1px solid var(--sb-border);flex-shrink:0;position:relative;}
.sb-close{display:none;position:absolute;top:14px;right:12px;width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.6);font-size:18px;line-height:1;cursor:pointer;align-items:center;justify-content:center;transition:background .15s,color .15s;flex-shrink:0;}
.sb-close:hover{background:rgba(239,68,68,.18);border-color:rgba(239,68,68,.3);color:#fca5a5;}
@media(max-width:900px){.sb-close{display:flex;}}
.sb-logo{display:flex;align-items:center;gap:10px;text-decoration:none;color:#fff;}
.sb-logo-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;}
.sb-shop-name{font-size:14.5px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:148px;letter-spacing:-.3px;color:#fff;}
.sb-status{display:flex;align-items:center;gap:6px;margin-top:9px;font-size:10.5px;color:var(--sb-txt);font-weight:500;}
.pulse{width:6px;height:6px;border-radius:50%;background:var(--brand);flex-shrink:0;animation:blink 2.2s ease-in-out infinite;box-shadow:0 0 5px var(--brand);}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.35}}
.sb-nav{padding:10px 10px 32px;flex:1;display:flex;flex-direction:column;gap:1px;overflow:visible;}
.sb-section{font-size:9.5px;text-transform:uppercase;letter-spacing:1.8px;color:rgba(255,255,255,.48);padding:16px 10px 5px;font-weight:800;}
.sb-item{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--r-sm);font-size:13.5px;font-weight:600;color:rgba(255,255,255,.78);text-decoration:none;transition:background .15s,color .15s;position:relative;}
.sb-item:hover{background:var(--sb-hov);color:rgba(255,255,255,.96);}
.sb-item.active{background:var(--sb-act);color:#fff;box-shadow:0 2px 12px rgba(99,102,241,.25);}
.sb-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:20px;background:#a5b4fc;border-radius:0 3px 3px 0;box-shadow:2px 0 8px rgba(165,180,252,.5);}
.sb-item .ico{font-size:13px;width:26px;height:26px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border-radius:7px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.06);transition:background .15s;}
.sb-item:hover .ico{background:rgba(255,255,255,.09);}
.sb-item.active .ico{background:rgba(255,255,255,.18);border-color:rgba(255,255,255,.2);}
.sb-group{display:flex;flex-direction:column;}
.sb-group-toggle{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--r-sm);font-size:13.5px;font-weight:600;color:rgba(255,255,255,.78);cursor:pointer;transition:background .15s,color .15s;user-select:none;border:none;background:none;width:100%;text-align:left;font-family:var(--font);}
.sb-group-toggle:hover{background:var(--sb-hov);color:rgba(255,255,255,.96);}
.sb-group-toggle.open{color:#fff;background:rgba(255,255,255,.05);}
.sb-group-toggle .ico{font-size:13px;width:26px;height:26px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border-radius:7px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.06);}
.sb-group-toggle .sb-arrow{margin-left:auto;font-size:10px;color:rgba(255,255,255,.32);transition:transform .2s;flex-shrink:0;}
.sb-group-toggle.open .sb-arrow{transform:rotate(90deg);color:rgba(255,255,255,.6);}
.sb-sub{display:none;flex-direction:column;gap:1px;margin-left:12px;padding-left:14px;border-left:1px solid rgba(255,255,255,.1);margin-top:2px;margin-bottom:4px;overflow:visible;}
.sb-sub.open{display:flex;}
.sb-sub .sb-item{font-size:13px;font-weight:500;padding:6px 10px;color:rgba(255,255,255,.62);}
.sb-sub .sb-item:hover{color:rgba(255,255,255,.92);}
.sb-sub .sb-item.active{color:#fff;background:var(--sb-act);font-weight:600;}
.sb-scroll-hint{position:sticky;top:auto;bottom:72px;width:100%;height:40px;background:linear-gradient(to bottom,transparent,rgba(17,17,24,.95));pointer-events:none;z-index:2;display:flex;align-items:flex-end;justify-content:center;padding-bottom:6px;transition:opacity .3s;margin-top:-40px;align-self:flex-end;}
.sb-scroll-hint.hidden{opacity:0;pointer-events:none;}
.sb-scroll-hint-arrow{display:flex;flex-direction:column;align-items:center;gap:2px;animation:bounceDown 1.5s ease-in-out infinite;}
.sb-scroll-hint-dot{width:4px;height:4px;border-radius:50%;background:rgba(99,102,241,.6);}
.sb-scroll-hint-dot:nth-child(2){opacity:.5;margin-top:-2px;}
.sb-scroll-hint-dot:nth-child(3){opacity:.25;margin-top:-2px;}
@keyframes bounceDown{0%,100%{transform:translateY(0)}50%{transform:translateY(4px)}}
.sb-footer{padding:12px 10px;border-top:1px solid rgba(255,255,255,.08);flex-shrink:0;display:flex;flex-direction:column;gap:6px;position:sticky;bottom:0;background:linear-gradient(180deg,transparent 0%,#0b0b12 25%);z-index:1;}
.sb-user{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--r-sm);text-decoration:none;transition:background .15s,border-color .15s;border:1px solid transparent;}
.sb-user:hover{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.07);}
.sb-av{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#4338ca);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0;box-shadow:0 0 0 2px rgba(99,102,241,.45),0 2px 8px rgba(99,102,241,.3);letter-spacing:-.5px;}
.sb-uname{font-size:13px;font-weight:700;color:#fff;letter-spacing:-.2px;}
.sb-urole{font-size:10.5px;color:rgba(255,255,255,.52);margin-top:1px;font-weight:500;}
.sb-logout{display:flex;align-items:center;gap:8px;width:100%;padding:8px 10px;border-radius:var(--r-sm);background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.15);color:rgba(252,165,165,.92);font-size:12.5px;font-weight:600;font-family:var(--font);cursor:pointer;text-decoration:none;transition:background .15s,color .15s,border-color .15s;text-align:left;}
.sb-logout:hover{background:rgba(220,38,38,.18);border-color:rgba(220,38,38,.35);color:#fca5a5;}
.sb-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:39;}

/* ─── TOPBAR & MAIN ─── */
.main{display:flex;flex-direction:column;min-width:0;height:100vh;}
.topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 22px;height:var(--top-h);display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:30;box-shadow:var(--shadow-sm);flex-shrink:0;}
.btn-hamburger{display:none;background:none;border:none;cursor:pointer;padding:6px;color:var(--text);font-size:20px;}
.tb-bc{font-size:12.5px;color:var(--muted);display:flex;align-items:center;gap:6px;flex:1;overflow:hidden;}
.tb-bc a{color:var(--muted);text-decoration:none}.tb-bc a:hover{color:var(--brand)}
.tb-bc span.cur{color:var(--text);font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}

/* ─── TICKET HEADER ─── */
.tk-hd{padding:16px 22px;background:var(--surface);border-bottom:1px solid var(--border);display:flex;align-items:flex-start;gap:14px;flex-shrink:0;flex-wrap:wrap;}
.tk-info{flex:1;min-width:0;}
.tk-subject{font-size:16px;font-weight:800;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text);}
.tk-meta{font-size:12px;color:var(--muted);margin-top:4px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
.badge-open{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:rgba(16,185,129,.1);color:#047857;}
.badge-closed{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:#f1f5f9;color:var(--muted);}
.badge-dot{width:5px;height:5px;border-radius:50%;}
.badge-open .badge-dot{background:#10b981;}
.badge-closed .badge-dot{background:var(--muted);}
.btn-close-ticket{display:inline-flex;align-items:center;gap:7px;padding:7px 16px;border-radius:var(--r-sm);border:1.5px solid #fca5a5;background:rgba(239,68,68,.06);color:#ef4444;font-size:12.5px;font-weight:700;cursor:pointer;font-family:var(--font);transition:all .14s;flex-shrink:0;white-space:nowrap;}
.btn-close-ticket:hover{background:#ef4444;color:#fff;border-color:#ef4444;}

/* ─── CHAT ─── */
.chat-area{flex:1;overflow:hidden;display:flex;flex-direction:column;min-height:0;}
.messages{flex:1;overflow-y:auto;padding:20px 22px;display:flex;flex-direction:column;gap:14px;scroll-behavior:smooth;}
.messages::-webkit-scrollbar{width:4px;}
.messages::-webkit-scrollbar-thumb{background:var(--border-dk);border-radius:4px;}
.msg-row{display:flex;gap:10px;max-width:78%;}
.msg-row.mine{align-self:flex-end;flex-direction:row-reverse;}
.msg-row.theirs{align-self:flex-start;}
.msg-av{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0;margin-top:2px;}
.msg-av.shop{background:linear-gradient(135deg,#6366f1,#4f46e5);}
.msg-av.admin{background:linear-gradient(135deg,#1e1b4b,#312e81);}
.msg-body{}
.msg-name{font-size:10.5px;font-weight:700;color:var(--muted);margin-bottom:4px;}
.msg-row.mine .msg-name{text-align:right;}
.msg-bubble{padding:10px 14px;border-radius:14px;font-size:13.5px;line-height:1.55;word-break:break-word;}
.msg-row.mine .msg-bubble{background:var(--brand);color:#fff;border-bottom-right-radius:4px;}
.msg-row.theirs .msg-bubble{background:var(--surface);border:1px solid var(--border);color:var(--text);border-bottom-left-radius:4px;box-shadow:var(--shadow-sm);}
.msg-time{font-size:10px;color:var(--muted);margin-top:4px;}
.msg-row.mine .msg-time{text-align:right;}

/* ─── INPUT ─── */
.chat-input{padding:14px 22px;background:var(--surface);border-top:1px solid var(--border);flex-shrink:0;}
.input-row{display:flex;gap:10px;align-items:flex-end;}
.chat-textarea{flex:1;padding:10px 13px;border:1.5px solid var(--border-dk);border-radius:12px;font-size:13.5px;font-family:var(--font);color:var(--text);background:var(--bg);outline:none;resize:none;min-height:44px;max-height:120px;transition:border-color .15s,box-shadow .15s;line-height:1.5;}
.chat-textarea:focus{border-color:var(--brand);box-shadow:0 0 0 3px rgba(99,102,241,.1);background:#fff;}
.btn-send{width:44px;height:44px;border-radius:12px;background:var(--brand);border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .14s;}
.btn-send:hover{background:var(--brand-dk);}
.send-hint{font-size:11px;color:var(--muted);margin-top:6px;}
.closed-banner{display:flex;align-items:center;gap:10px;padding:12px 22px;background:var(--bg);border-top:1px solid var(--border);font-size:13px;color:var(--muted);font-weight:600;flex-shrink:0;}
.flash-ok{display:flex;align-items:center;gap:10px;padding:12px 22px;background:rgba(16,185,129,.08);border-bottom:1px solid rgba(16,185,129,.2);color:#065f46;font-size:13px;font-weight:600;flex-shrink:0;}

/* ─── MODAL FERMETURE ─── */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:200;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(3px);}
.modal-overlay.open{display:flex;}
.modal-box{background:var(--surface);border-radius:var(--r);padding:28px 26px;max-width:420px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.22);animation:modalIn .2s ease;}
@keyframes modalIn{from{opacity:0;transform:scale(.95) translateY(-8px)}to{opacity:1;transform:scale(1) translateY(0)}}
.modal-icon{font-size:44px;text-align:center;margin-bottom:14px;}
.modal-title{font-size:17px;font-weight:800;color:var(--text);text-align:center;margin-bottom:10px;}
.modal-body{font-size:13.5px;color:var(--text-2);text-align:center;line-height:1.65;margin-bottom:20px;}
.modal-warns{display:flex;flex-direction:column;gap:8px;margin-bottom:22px;background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:14px 16px;}
.warn-row{display:flex;align-items:flex-start;gap:10px;font-size:12.5px;color:#92400e;font-weight:600;}
.warn-ico{font-size:16px;flex-shrink:0;margin-top:1px;}
.modal-actions{display:flex;gap:10px;}
.btn-modal-cancel{flex:1;padding:11px;border-radius:var(--r-sm);border:1.5px solid var(--border-dk);background:var(--surface);color:var(--text-2);font-size:13.5px;font-weight:700;font-family:var(--font);cursor:pointer;transition:all .14s;}
.btn-modal-cancel:hover{border-color:var(--brand);color:var(--brand);}
.btn-modal-close{flex:1;padding:11px;border-radius:var(--r-sm);border:none;background:#dc2626;color:#fff;font-size:13.5px;font-weight:800;font-family:var(--font);cursor:pointer;transition:background .14s;}
.btn-modal-close:hover{background:#b91c1c;}

/* ─── RESPONSIVE ─── */
@media(max-width:900px){
    :root{--sb-w:230px;}
    .dash-wrap .main{margin-left:0;}
    .sidebar{transform:translateX(-100%);transition:transform .25s cubic-bezier(.23,1,.32,1);}
    .sidebar.open{transform:translateX(0);}
    .sb-overlay.open{display:block;}
    .btn-hamburger{display:flex;}
    .messages{padding:14px;}
    .chat-input{padding:10px 14px;}
    .tk-hd{padding:12px 14px;}
}
@media(max-width:640px){
    .topbar{padding:0 12px;gap:8px;}
    .msg-row{max-width:92%;}
    .tk-subject{font-size:14px;}
    .messages{padding:10px;}
}
@media(max-width:480px){
    .msg-bubble{font-size:13px;padding:8px 12px;}
    .chat-input{padding:8px 10px;}
    .tk-hd{flex-direction:column;gap:10px;}
    .btn-close-ticket{width:100%;justify-content:center;}
}
@media(max-width:360px){
    .topbar{padding:0 8px;}
    .msg-row{max-width:100%;}
}
</style>
@endpush

@section('content')
@php
    $u        = Auth::user();
    $parts    = explode(' ', $u->name ?? 'U');
    $initials = strtoupper(substr($parts[0],0,1)).strtoupper(substr($parts[1] ?? 'X',0,1));
    $isSuperAdmin = $u->role === 'superadmin';
@endphp

<div class="dash-wrap">

    {{-- ════ SIDEBAR (boutique seulement) ════ --}}
    @if(!$isSuperAdmin)
    <aside class="sidebar" id="sidebar">
        <div class="sb-brand">
            <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
                <div class="sb-logo-icon"><img src="/images/Shopio3.jpeg" alt="Shopio" style="width:100%;height:100%;object-fit:cover;border-radius:9px"></div>
                <span class="sb-shop-name">{{ $shop->name ?? 'Boutique' }}</span>
            </a>
            <button class="sb-close" id="btnCloseSidebar" aria-label="Fermer le menu">✕</button>
            <div class="sb-status">
                <span class="pulse"></span>
                Boutique active &nbsp;·&nbsp; {{ ucfirst($u->role_in_shop ?? $u->role) }}
            </div>
        </div>
        <div class="sb-scroll-hint" id="sbScrollHint">
            <div class="sb-scroll-hint-arrow">
                <div class="sb-scroll-hint-dot"></div>
                <div class="sb-scroll-hint-dot"></div>
                <div class="sb-scroll-hint-dot"></div>
            </div>
        </div>
        <nav class="sb-nav">
            <a href="{{ route('boutique.dashboard') }}" class="sb-item" style="margin-bottom:4px"><span class="ico">⊞</span> Tableau de bord</a>
            <div class="sb-section">Boutique</div>
            <a href="{{ route('boutique.messages.hub') }}" class="sb-item"><span class="ico">💬</span> Messages</a>
            <a href="{{ route('boutique.orders.index') }}" class="sb-item"><span class="ico">📦</span> Commandes</a>
            <a href="{{ route('products.index') }}" class="sb-item"><span class="ico">🏷️</span> Produits</a>
            <a href="{{ route('boutique.clients.index') }}" class="sb-item"><span class="ico">👥</span> Clients</a>
            <a href="{{ route('boutique.employees.index') }}" class="sb-item"><span class="ico">🧑‍💼</span> Équipe</a>
            <div class="sb-section">Livraison</div>
            <a href="{{ route('boutique.livreurs.index') }}" class="sb-item"><span class="ico">🚴</span> Livreurs</a>
            <a href="{{ route('delivery.companies.index') }}" class="sb-item"><span class="ico">🏢</span> Partenaires</a>
            <div class="sb-section">Finances</div>
            <div class="sb-group">
                <button class="sb-group-toggle" onclick="toggleGroup(this)" type="button">
                    <span class="ico">💰</span> Finances &amp; Rapports <span class="sb-arrow">▶</span>
                </button>
                <div class="sb-sub">
                    <a href="{{ route('boutique.payments.index') }}" class="sb-item"><span class="ico">💳</span> Paiements</a>
                    <a href="{{ route('boutique.commissions.index') }}" class="sb-item"><span class="ico">📊</span> Commissions</a>
                    <a href="{{ route('boutique.reports.index') }}" class="sb-item"><span class="ico">📋</span> Rapports</a>
                    @if($u->role === 'admin')
                    <a href="{{ route('shop.edit', $shop) }}" class="sb-item"><span class="ico">⚙️</span> Paramètres</a>
                    @endif
                </div>
            </div>
            <div class="sb-section">Aide</div>
            <a href="{{ route('support.index') }}" class="sb-item active"><span class="ico">🎧</span> Support</a>
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
                <button type="submit" class="sb-logout"><span class="ico">⎋</span> Se déconnecter</button>
            </form>
        </div>
    </aside>
    <div class="sb-overlay" id="sbOverlay"></div>
    @endif

    {{-- ════ MAIN ════ --}}
    <main class="main" style="{{ $isSuperAdmin ? 'margin-left:0' : '' }}">

        <div class="topbar">
            @if(!$isSuperAdmin)
            <button class="btn-hamburger" id="btnMenu">☰</button>
            @endif
            <div class="tb-bc">
                @if($isSuperAdmin)
                    <a href="{{ route('admin.dashboard') }}">⚡ Admin</a>
                @else
                    <a href="{{ route('boutique.dashboard') }}">🏪 {{ $shop->name ?? 'Boutique' }}</a>
                @endif
                <span style="color:var(--muted)">›</span>
                <a href="{{ $isSuperAdmin ? route('admin.support.index') : route('support.index') }}">🎧 Support</a>
                <span style="color:var(--muted)">›</span>
                <span class="cur">Ticket #{{ $ticket->id }}</span>
            </div>
        </div>

        @if(session('success'))
        <div class="flash-ok">✅ {{ session('success') }}</div>
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
                    @if($ticket->shop)<span>🏪 {{ $ticket->shop->name }}</span>@endif
                    <span>Par <strong>{{ $ticket->creator->name ?? '—' }}</strong></span>
                    <span>{{ $ticket->created_at->format('d/m/Y à H:i') }}</span>
                </div>
            </div>
            @if($ticket->status === 'open')
            <button type="button" class="btn-close-ticket" onclick="openCloseModal()">
                🔒 Fermer le ticket
            </button>
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
                    <div class="msg-body">
                        <div class="msg-name">
                            {{ $m->author->name ?? '—' }}
                            @if($isAdmin)
                                <span style="font-size:10px;background:rgba(99,102,241,.12);color:#4338ca;padding:1px 7px;border-radius:10px;margin-left:4px;font-weight:800">SuperAdmin</span>
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
                                  placeholder="Écrivez votre message…" required
                                  onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();document.getElementById('msgForm').submit();}"></textarea>
                        <button type="submit" class="btn-send" title="Envoyer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        </button>
                    </div>
                    <div class="send-hint">Entrée pour envoyer · Maj+Entrée pour un saut de ligne</div>
                </form>
            </div>
            @else
            <div class="closed-banner">
                ✅ Ce ticket est fermé — aucun nouveau message ne peut être ajouté.
                <a href="{{ route('support.create') }}" style="color:var(--brand);font-weight:700;margin-left:auto;text-decoration:none;white-space:nowrap">
                    Ouvrir un nouveau ticket →
                </a>
            </div>
            @endif
        </div>

    </main>
</div>

{{-- ════ MODAL FERMETURE TICKET ════ --}}
@if($ticket->status === 'open')
<div class="modal-overlay" id="closeModal">
    <div class="modal-box">
        <div class="modal-icon">🔒</div>
        <div class="modal-title">Fermer ce ticket ?</div>
        <div class="modal-body">
            Vous êtes sur le point de fermer le ticket&nbsp;:<br>
            <strong style="color:var(--text)">"{{ Str::limit($ticket->subject, 60) }}"</strong>
        </div>
        <div class="modal-warns">
            <div class="warn-row">
                <span class="warn-ico">⛔</span>
                <span>Une fois fermé, <strong>vous ne pourrez plus rouvrir ce ticket</strong>. Il sera archivé définitivement.</span>
            </div>
            <div class="warn-row">
                <span class="warn-ico">🚫</span>
                <span>Le SuperAdmin <strong>ne pourra plus répondre</strong> à cette conversation.</span>
            </div>
            <div class="warn-row">
                <span class="warn-ico">💡</span>
                <span>Si vous avez encore besoin d'aide, <strong>créez un nouveau ticket</strong> après la fermeture.</span>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-modal-cancel" onclick="closeCloseModal()">← Annuler</button>
            <button type="button" class="btn-modal-close" onclick="confirmClose()">🔒 Oui, fermer définitivement</button>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('support.close', $ticket) }}" id="closeForm" style="display:none">
    @csrf
</form>
@endif
@endsection

@push('scripts')
<script>
// Modal fermeture
function openCloseModal()  { document.getElementById('closeModal').classList.add('open'); }
function closeCloseModal() { document.getElementById('closeModal').classList.remove('open'); }
function confirmClose()    { document.getElementById('closeForm').submit(); }
document.getElementById('closeModal')?.addEventListener('click', function(e){ if(e.target===this) closeCloseModal(); });

// Sidebar mobile
const sidebar  = document.getElementById('sidebar');
const overlay  = document.getElementById('sbOverlay');
const btnMenu  = document.getElementById('btnMenu');
const btnClose = document.getElementById('btnCloseSidebar');

function openSb()  { if(sidebar){sidebar.classList.add('open');} if(overlay){overlay.classList.add('open');} }
function closeSb() { if(sidebar){sidebar.classList.remove('open');} if(overlay){overlay.classList.remove('open');} }
if (btnMenu)  btnMenu.addEventListener('click', openSb);
if (btnClose) btnClose.addEventListener('click', closeSb);
if (overlay)  overlay.addEventListener('click', closeSb);

function toggleGroup(btn) {
    btn.classList.toggle('open');
    const sub = btn.nextElementSibling;
    if (sub) sub.classList.toggle('open');
}

function updateScrollHint() {
    const sb = sidebar;
    const hint = document.getElementById('sbScrollHint');
    if (!sb || !hint) return;
    const atBottom = sb.scrollHeight - sb.scrollTop - sb.clientHeight < 30;
    hint.classList.toggle('hidden', atBottom);
}
if (sidebar) { sidebar.addEventListener('scroll', updateScrollHint); updateScrollHint(); }

// Chat
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
            const isMine   = m.user_id === currentUid;
            const isAdmin  = m.author && m.author.role === 'superadmin';
            const init     = (m.author?.name || '?')[0].toUpperCase();
            const adminBadge = isAdmin
                ? `<span style="font-size:10px;background:rgba(99,102,241,.12);color:#4338ca;padding:1px 7px;border-radius:10px;margin-left:4px;font-weight:800">SuperAdmin</span>`
                : '';
            const row = document.createElement('div');
            row.className = `msg-row ${isMine ? 'mine' : 'theirs'}`;
            row.dataset.id = m.id;
            row.innerHTML = `
                <div class="msg-av ${isAdmin ? 'admin' : 'shop'}">${escHtml(init)}</div>
                <div class="msg-body">
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
</script>
@endpush
