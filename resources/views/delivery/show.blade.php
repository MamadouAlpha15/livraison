@extends('layouts.app')
@section('title', $company->name . ' · ShipXpress')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box}

:root{
    --brand:     #6366f1;
    --brand-dk:  #4f46e5;
    --brand-dkk: #4338ca;
    --brand-lt:  #e0e7ff;
    --brand-mlt: #eef2ff;
    --bg:        #f8fafc;
    --surface:   #ffffff;
    --surface2:  #f1f5f9;
    --border:    #e2e8f0;
    --border-md: #cbd5e1;
    --text:      #0f172a;
    --text2:     #475569;
    --muted:     #94a3b8;
    --green:     #10b981;
    --gold:      #f59e0b;
    --red:       #ef4444;
    --sh-sm: 0 1px 3px rgba(0,0,0,.05),0 1px 2px rgba(0,0,0,.03);
    --sh:    0 4px 20px rgba(0,0,0,.07),0 1px 4px rgba(0,0,0,.04);
    --sh-lg: 0 12px 40px rgba(0,0,0,.10),0 2px 8px rgba(0,0,0,.05);
    --sh-xl: 0 24px 64px rgba(0,0,0,.12),0 4px 12px rgba(0,0,0,.06);
    --r:    16px;
    --r-sm: 10px;
    --r-xs: 7px;
}

body{font-family:'Segoe UI',system-ui,sans-serif;background:var(--bg);color:var(--text);margin:0;-webkit-font-smoothing:antialiased;}
a{text-decoration:none;color:inherit;}

/* ════════════════════════════════════
   BARRE SUPÉRIEURE — identique index
════════════════════════════════════ */
.page-banner{
    width:100%;
    background:linear-gradient(135deg,#3730a3 0%,#4f46e5 40%,#6366f1 75%,#818cf8 100%);
    padding:0;position:relative;overflow:hidden;
}
.page-banner::before{
    content:'';position:absolute;top:-80px;right:-100px;
    width:380px;height:380px;border-radius:50%;
    background:radial-gradient(circle,rgba(255,255,255,.07) 0%,transparent 65%);
    pointer-events:none;
}
.page-banner::after{
    content:'';position:absolute;bottom:-80px;left:-50px;
    width:260px;height:260px;border-radius:50%;
    background:radial-gradient(circle,rgba(124,58,237,.2) 0%,transparent 65%);
    pointer-events:none;
}
.banner-grid{
    position:absolute;inset:0;pointer-events:none;
    background-image:
        linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),
        linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);
    background-size:40px 40px;
}

.banner-inner{
    max-width:1180px;margin:0 auto;padding:32px 24px 28px;
    position:relative;z-index:1;
}

/* Breadcrumb */
.breadcrumb{
    display:flex;align-items:center;gap:6px;
    font-size:12px;font-weight:600;color:rgba(255,255,255,.55);
    margin-bottom:20px;
}
.breadcrumb a{color:rgba(255,255,255,.7);transition:color .14s;}
.breadcrumb a:hover{color:#fff;}
.breadcrumb-sep{color:rgba(255,255,255,.3);}

/* Banner content */
.banner-body{display:flex;align-items:flex-start;justify-content:space-between;gap:24px;flex-wrap:wrap;}
.banner-left{display:flex;align-items:flex-start;gap:18px;flex:1;min-width:0;}

.banner-logo{
    width:72px;height:72px;border-radius:18px;flex-shrink:0;overflow:hidden;
    background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.22);
    backdrop-filter:blur(4px);box-shadow:0 4px 20px rgba(0,0,0,.2);
    display:flex;align-items:center;justify-content:center;font-size:32px;
}
.banner-logo img{width:100%;height:100%;object-fit:cover;}

.banner-name{
    font-size:28px;font-weight:900;color:#fff;
    letter-spacing:-.6px;margin:0 0 6px;
    text-shadow:0 2px 8px rgba(0,0,0,.15);
}
.banner-addr{
    font-size:13px;color:rgba(255,255,255,.68);
    display:flex;align-items:center;gap:5px;margin-bottom:12px;
}
.banner-chips{display:flex;gap:7px;flex-wrap:wrap;}
.b-chip{
    display:inline-flex;align-items:center;gap:5px;
    background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);
    color:rgba(255,255,255,.9);font-size:11.5px;font-weight:600;
    padding:4px 12px;border-radius:30px;backdrop-filter:blur(4px);
    white-space:nowrap;
}
.b-chip-dot{width:6px;height:6px;border-radius:50%;background:#6ee7b7;box-shadow:0 0 5px #6ee7b7;flex-shrink:0;}

/* Rating in banner */
.banner-rating{
    display:flex;flex-direction:column;align-items:flex-end;gap:10px;flex-shrink:0;
}
.rating-big{
    display:flex;align-items:center;gap:8px;
    background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);
    backdrop-filter:blur(4px);border-radius:var(--r-sm);padding:10px 16px;
}
.rating-num{font-size:28px;font-weight:900;color:#fff;letter-spacing:-.5px;line-height:1;}
.rating-detail{display:flex;flex-direction:column;gap:3px;}
.stars{display:flex;gap:2px;}
.star{font-size:13px;color:rgba(255,255,255,.35);}
.star.on{color:var(--gold);}
.rating-sub{font-size:11px;color:rgba(255,255,255,.55);font-weight:500;}

/* CTA button in banner */
.btn-banner-cta{
    display:inline-flex;align-items:center;gap:8px;
    background:#fff;color:var(--brand-dk);
    font-size:13.5px;font-weight:800;font-family:inherit;
    padding:12px 22px;border-radius:var(--r-sm);border:none;
    box-shadow:0 4px 16px rgba(0,0,0,.2);cursor:pointer;
    transition:all .18s;white-space:nowrap;
}
.btn-banner-cta:hover{background:var(--brand-lt);transform:translateY(-1px);box-shadow:0 6px 22px rgba(0,0,0,.25);}
.btn-banner-login{
    display:inline-flex;align-items:center;gap:7px;
    background:rgba(255,255,255,.14);color:#fff;
    font-size:13px;font-weight:700;font-family:inherit;
    padding:11px 20px;border-radius:var(--r-sm);
    border:1px solid rgba(255,255,255,.22);backdrop-filter:blur(4px);
    cursor:pointer;transition:all .16s;
}
.btn-banner-login:hover{background:rgba(255,255,255,.2);}

/* ════ PAGE BODY ════ */
.pw{max-width:1180px;margin:0 auto;padding:28px 24px 80px;}

.page-layout{display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;}

/* ════ MAIN COLUMN ════ */
.main-col{display:flex;flex-direction:column;gap:20px;}

/* Hero image */
.company-img-wrap{
    border-radius:var(--r);overflow:hidden;
    box-shadow:var(--sh-lg);position:relative;
}
.company-img-wrap img{
    width:100%;height:320px;object-fit:cover;display:block;
}
.company-img-overlay{
    position:absolute;inset:0;
    background:linear-gradient(to top,rgba(15,23,42,.5) 0%,transparent 55%);
}
.img-no-photo{
    width:100%;height:220px;
    background:linear-gradient(135deg,var(--brand-mlt),var(--brand-lt));
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    gap:10px;border-radius:var(--r);border:1px solid var(--border);
}
.img-no-photo-ico{font-size:56px;opacity:.3;}
.img-no-photo-txt{font-size:13px;font-weight:700;color:var(--brand);opacity:.5;letter-spacing:.5px;text-transform:uppercase;}

/* Card base */
.card{
    background:var(--surface);border:1px solid var(--border);
    border-radius:var(--r);box-shadow:var(--sh-sm);overflow:hidden;
}

/* Card header */
.card-hd{
    padding:16px 22px;border-bottom:1px solid var(--border);
    display:flex;align-items:center;justify-content:space-between;gap:10px;
}
.card-title{
    font-size:14px;font-weight:800;color:var(--text);
    display:flex;align-items:center;gap:8px;letter-spacing:-.2px;
}
.card-title-ico{
    width:28px;height:28px;border-radius:7px;
    background:var(--brand-mlt);border:1px solid var(--brand-lt);
    display:flex;align-items:center;justify-content:center;font-size:13px;
    flex-shrink:0;
}
.card-body{padding:22px;}

/* Description */
.desc-text{
    font-size:14px;color:var(--text2);line-height:1.75;margin:0;
}

/* Stats grid */
.stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;}
.stat-box{
    background:var(--surface2);border:1px solid var(--border);
    border-radius:var(--r-sm);padding:16px;text-align:center;
    position:relative;overflow:hidden;
}
.stat-box::before{
    content:'';position:absolute;top:0;left:0;right:0;height:2px;
    background:var(--s-color,var(--brand));
}
.stat-box-ico{font-size:20px;margin-bottom:6px;}
.stat-box-val{
    font-size:22px;font-weight:900;color:var(--text);
    letter-spacing:-.5px;line-height:1;margin-bottom:4px;
}
.stat-box-lbl{font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;}

/* Drivers grid */
.drivers-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(190px,1fr));
    gap:12px;padding:18px 22px;
}
.driver-card{
    display:flex;align-items:center;gap:10px;
    padding:12px;border-radius:var(--r-sm);
    background:var(--surface2);border:1px solid var(--border);
    transition:border-color .15s,box-shadow .15s;
}
.driver-card:hover{border-color:#c7d2fe;box-shadow:0 2px 12px rgba(99,102,241,.1);}
.driver-av{
    width:44px;height:44px;border-radius:var(--r-xs);flex-shrink:0;overflow:hidden;
    background:linear-gradient(135deg,var(--brand),var(--brand-dkk));
    display:flex;align-items:center;justify-content:center;
}
.driver-av img{width:100%;height:100%;object-fit:cover;}
.driver-av-ini{font-size:13px;font-weight:800;color:#fff;letter-spacing:-.3px;}
.driver-name{font-size:12.5px;font-weight:700;color:var(--text);margin-bottom:2px;}
.driver-phone{font-size:11px;color:var(--muted);}
.driver-status{
    display:inline-flex;align-items:center;gap:3px;
    font-size:9.5px;font-weight:700;padding:2px 7px;border-radius:20px;margin-top:3px;
}
.ds-avail{background:rgba(16,185,129,.1);color:#059669;border:1px solid rgba(16,185,129,.2);}
.ds-busy {background:rgba(245,158,11,.1);color:#d97706;border:1px solid rgba(245,158,11,.2);}
.ds-off  {background:rgba(100,116,139,.1);color:var(--muted);border:1px solid var(--border);}

/* Empty drivers */
.drivers-empty{
    padding:36px 22px;text-align:center;
    display:flex;flex-direction:column;align-items:center;gap:10px;
}
.drivers-empty-ico{
    width:56px;height:56px;border-radius:14px;
    background:var(--brand-mlt);border:1px solid var(--brand-lt);
    display:flex;align-items:center;justify-content:center;font-size:24px;
}
.drivers-empty-txt{font-size:13.5px;font-weight:700;color:var(--text);}
.drivers-empty-sub{font-size:12px;color:var(--muted);}

/* ════ SIDEBAR ════ */
.sidebar{display:flex;flex-direction:column;gap:18px;position:sticky;top:20px;}

/* Contact card */
.contact-card{
    background:linear-gradient(135deg,var(--brand-dk) 0%,var(--brand) 100%);
    border-radius:var(--r);box-shadow:0 8px 32px rgba(99,102,241,.3);
    overflow:hidden;position:relative;
}
.contact-card::before{
    content:'';position:absolute;top:-40px;right:-40px;
    width:160px;height:160px;border-radius:50%;
    background:rgba(255,255,255,.07);pointer-events:none;
}
.contact-card-body{padding:22px;position:relative;z-index:1;}
.contact-card-title{
    font-size:16px;font-weight:800;color:#fff;
    margin:0 0 4px;letter-spacing:-.3px;
}
.contact-card-sub{font-size:12.5px;color:rgba(255,255,255,.7);margin:0 0 20px;}
.contact-info-list{display:flex;flex-direction:column;gap:8px;margin-bottom:18px;}
.contact-info-item{
    display:flex;align-items:center;gap:10px;
    background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.16);
    border-radius:var(--r-xs);padding:9px 12px;
    backdrop-filter:blur(4px);
}
.ci-ico{font-size:14px;flex-shrink:0;}
.ci-val{font-size:12.5px;font-weight:600;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.ci-empty{color:rgba(255,255,255,.45);font-style:italic;}
.btn-cta-main{
    width:100%;display:flex;align-items:center;justify-content:center;gap:8px;
    background:#fff;color:var(--brand-dk);
    font-size:14px;font-weight:800;font-family:inherit;
    padding:13px;border-radius:var(--r-sm);border:none;cursor:pointer;
    box-shadow:0 4px 16px rgba(0,0,0,.18);
    transition:all .18s;
}
.btn-cta-main:hover{background:var(--brand-lt);transform:translateY(-1px);box-shadow:0 6px 22px rgba(0,0,0,.22);}
.btn-cta-login{
    width:100%;display:flex;align-items:center;justify-content:center;gap:8px;
    background:rgba(255,255,255,.13);color:#fff;
    font-size:13.5px;font-weight:700;font-family:inherit;
    padding:12px;border-radius:var(--r-sm);border:1px solid rgba(255,255,255,.22);
    cursor:pointer;transition:background .16s;
}
.btn-cta-login:hover{background:rgba(255,255,255,.2);}

/* Info card */
.info-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--sh-sm);}
.info-card-hd{
    padding:14px 18px;border-bottom:1px solid var(--border);
    font-size:13px;font-weight:800;color:var(--text);
    display:flex;align-items:center;gap:7px;
}
.info-card-ico-wrap{
    width:26px;height:26px;border-radius:6px;
    background:var(--brand-mlt);border:1px solid var(--brand-lt);
    display:flex;align-items:center;justify-content:center;font-size:12px;
    flex-shrink:0;
}
.info-list{padding:4px 0;}
.info-row{
    display:flex;align-items:center;gap:10px;
    padding:11px 18px;border-bottom:1px solid var(--border);
}
.info-row:last-child{border-bottom:none;}
.info-ico{
    width:32px;height:32px;border-radius:var(--r-xs);flex-shrink:0;
    display:flex;align-items:center;justify-content:center;font-size:14px;
    background:var(--surface2);
}
.info-lbl{font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;}
.info-val{font-size:13px;font-weight:700;color:var(--text);}

/* Status badge */
.status-approved{
    display:inline-flex;align-items:center;gap:5px;
    background:rgba(16,185,129,.1);color:#059669;
    border:1px solid rgba(16,185,129,.2);
    font-size:12px;font-weight:700;
    padding:3px 10px;border-radius:20px;
}
.status-dot{width:6px;height:6px;border-radius:50%;background:#10b981;flex-shrink:0;}
.status-pending{
    display:inline-flex;align-items:center;gap:5px;
    background:rgba(245,158,11,.1);color:#d97706;
    border:1px solid rgba(245,158,11,.2);
    font-size:12px;font-weight:700;
    padding:3px 10px;border-radius:20px;
}

/* Flash */
.flash{display:flex;align-items:flex-start;gap:10px;margin-bottom:16px;padding:12px 16px;border-radius:var(--r-sm);border:1px solid;font-size:13px;font-weight:500;}
.flash-success{background:#f0fdf4;border-color:#bbf7d0;color:#166534;}
.flash-danger {background:#fef2f2;border-color:#fecaca;color:#991b1b;}
.flash-info   {background:var(--brand-mlt);border-color:var(--brand-lt);color:var(--brand-dk);}
.flash-warning{background:#fffbeb;border-color:#fde68a;color:#92400e;}

/* Back */
.back{
    display:inline-flex;align-items:center;gap:6px;
    font-size:12.5px;font-weight:600;color:var(--muted);
    padding:6px 12px 6px 8px;border-radius:var(--r-xs);
    border:1px solid transparent;margin-bottom:20px;transition:all .15s;
}
.back:hover{background:var(--surface);border-color:var(--border);color:var(--brand);}
.back svg{flex-shrink:0;transition:transform .15s;}
.back:hover svg{transform:translateX(-2px);}

/* Responsive */
@media(max-width:900px){
    .page-layout{grid-template-columns:1fr;}
    .sidebar{position:static;}
    .stats-grid{grid-template-columns:repeat(3,1fr);}
    .banner-body{flex-direction:column;}
    .banner-rating{align-items:flex-start;}
}
@media(max-width:600px){
    .pw{padding:16px 14px 60px;}
    .stats-grid{grid-template-columns:1fr 1fr;}
    .banner-inner{padding:22px 16px 20px;}
    .banner-name{font-size:22px;}
    .banner-logo{width:54px;height:54px;font-size:24px;}
    .company-img-wrap img{height:220px;}
    .drivers-grid{grid-template-columns:1fr;}
}
</style>
@endpush

@section('content')

{{-- ══════════════════════════
     BANNIÈRE SUPÉRIEURE
══════════════════════════ --}}
<div class="page-banner">
    <div class="banner-grid"></div>
    <div class="banner-inner">

        {{-- Breadcrumb --}}
        <div class="breadcrumb">
            <a href="{{ route('delivery.companies.index') }}">Entreprises de livraison</a>
            <span class="breadcrumb-sep">/</span>
            <span style="color:rgba(255,255,255,.8)">{{ Str::limit($company->name, 32) }}</span>
        </div>

        <div class="banner-body">
            <div class="banner-left">
                {{-- Logo/icone --}}
                <div class="banner-logo">
                    @if($company->image)
                        <img src="{{ asset('storage/'.$company->image) }}" alt="{{ $company->name }}">
                    @else
                        🚚
                    @endif
                </div>

                <div style="min-width:0;flex:1">
                    <h1 class="banner-name">{{ $company->name }}</h1>
                    @if($company->address)
                    <div class="banner-addr">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $company->address }}
                    </div>
                    @endif
                    <div class="banner-chips">
                        <span class="b-chip">
                            <span class="b-chip-dot"></span>
                            Approuvée
                        </span>
                        <span class="b-chip">
                            🚴 {{ $company->drivers()->count() }} livreur{{ $company->drivers()->count() > 1 ? 's' : '' }}
                        </span>
                       
                        @if($company->email)
                        <span class="b-chip">✉️ {{ $company->email }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Rating + CTA --}}
            <div class="banner-rating">
                @php
                    $rating = round($avgRating, 1);
                    $fullS  = (int) floor($rating);
                @endphp
                <div class="rating-big">
                    <div class="rating-num">{{ number_format($rating, 1) }}</div>
                    <div class="rating-detail">
                        <div class="stars">
                            @for($s = 1; $s <= 5; $s++)
                                <span class="star {{ $s <= $fullS ? 'on' : '' }}">★</span>
                            @endfor
                        </div>
                        <div class="rating-sub">Note moyenne / 5</div>
                    </div>
                </div>
                @auth
                    @if(in_array(auth()->user()->role, ['vendeur','admin']))
                    <a href="{{ route('company.chat.show', [$company]) }}?shop_id={{ auth()->user()->shop_id ?? auth()->user()->currentShopId() }}"
                       class="btn-banner-cta">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        Contacter
                    </a>
                    @endif
                @else
                <a href="{{ route('login') }}" class="btn-banner-login">
                    🔑 Se connecter pour contacter
                </a>
                @endauth
            </div>
        </div>
    </div>
</div>

{{-- ══ PAGE BODY ══ --}}
<div class="pw">

    {{-- Back --}}
    <a href="{{ route('delivery.companies.index') }}" class="back">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la liste
    </a>

    {{-- Flash --}}
    @foreach(['success'=>'flash-success','danger'=>'flash-danger','info'=>'flash-info','warning'=>'flash-warning'] as $k=>$cls)
        @if(session($k))
        <div class="flash {{ $cls }}">
            <span>{{ $k==='success'?'✅':($k==='danger'?'❌':($k==='info'?'ℹ️':'⚠️')) }}</span>
            {{ session($k) }}
        </div>
        @endif
    @endforeach

    <div class="page-layout">

        {{-- ════ COLONNE PRINCIPALE ════ --}}
        <div class="main-col">

            {{-- Image principale --}}
            @if($company->image)
            <div class="company-img-wrap">
                <img src="{{ asset('storage/'.$company->image) }}" alt="{{ $company->name }}">
                <div class="company-img-overlay"></div>
            </div>
            @else
            <div class="img-no-photo">
                <span class="img-no-photo-ico">🚚</span>
                <span class="img-no-photo-txt">{{ Str::limit($company->name, 20) }}</span>
            </div>
            @endif

            {{-- Stats rapides --}}
            <div class="card">
                <div class="card-body" style="padding:18px 22px">
                    <div class="stats-grid">
                        <div class="stat-box" style="--s-color:#6366f1">
                            <div class="stat-box-ico">🚴</div>
                            <div class="stat-box-val">{{ $company->drivers()->count() }}</div>
                            <div class="stat-box-lbl">Livreurs</div>
                        </div>
                        <div class="stat-box" style="--s-color:#f59e0b">
                            <div class="stat-box-ico">⭐</div>
                            <div class="stat-box-val">{{ number_format($rating, 1) }}</div>
                            <div class="stat-box-lbl">Note /5</div>
                        </div>
                       
                    </div>
                </div>
            </div>

            {{-- Description --}}
            @if($company->description)
            <div class="card">
                <div class="card-hd">
                    <div class="card-title">
                        <div class="card-title-ico">📄</div>
                        À propos de {{ $company->name }}
                    </div>
                </div>
                <div class="card-body">
                    <p class="desc-text">{{ $company->description }}</p>
                </div>
            </div>
            @endif

            {{-- Livreurs --}}
            <div class="card">
                <div class="card-hd">
                    <div class="card-title">
                        <div class="card-title-ico">🚴</div>
                        Équipe de livreurs
                    </div>
                    @if($company->drivers->count() > 0)
                    <span style="font-size:11.5px;font-weight:700;background:var(--brand-mlt);color:var(--brand-dk);border:1px solid var(--brand-lt);padding:3px 10px;border-radius:20px;">
                        {{ $company->drivers->count() }} au total
                    </span>
                    @endif
                </div>

                @forelse($company->drivers as $driver)
                @if($loop->first)
                <div class="drivers-grid">
                @endif

                    <div class="driver-card">
                        <div class="driver-av">
                            @if($driver->photo)
                                <img src="{{ asset('storage/'.$driver->photo) }}" alt="{{ $driver->name }}">
                            @else
                                <span class="driver-av-ini">{{ strtoupper(substr($driver->name, 0, 2)) }}</span>
                            @endif
                        </div>
                        <div style="min-width:0;flex:1">
                            <div class="driver-name">{{ $driver->name }}</div>
                            <div class="driver-phone">{{ $driver->phone ?? 'Aucun téléphone' }}</div>
                            @php
                                $dStatus = $driver->status ?? 'available';
                                $dCls    = match($dStatus) {
                                    'available' => 'ds-avail',
                                    'busy'      => 'ds-busy',
                                    default     => 'ds-off',
                                };
                                $dLbl = match($dStatus) {
                                    'available' => '● Disponible',
                                    'busy'      => '● En livraison',
                                    default     => '● Hors ligne',
                                };
                            @endphp
                            <span class="driver-status {{ $dCls }}">{{ $dLbl }}</span>
                        </div>
                    </div>

                @if($loop->last)
                </div>
                @endif

                @empty
                <div class="drivers-empty">
                    <div class="drivers-empty-ico">🚴</div>
                    <div class="drivers-empty-txt">Aucun livreur enregistré</div>
                    <div class="drivers-empty-sub">Cette entreprise n'a pas encore publié son équipe.</div>
                </div>
                @endforelse
            </div>

            {{-- ════ AVIS DES VENDEURS ════ --}}
            <div class="card">
                <div class="card-hd">
                    <div class="card-title">
                        <div class="card-title-ico">⭐</div>
                        Avis des vendeurs
                        @if($reviews->count() > 0)
                        <span style="font-size:11px;font-weight:700;background:var(--brand-mlt);color:var(--brand-dk);border:1px solid var(--brand-lt);padding:2px 9px;border-radius:20px;margin-left:4px;">
                            {{ $reviews->count() }} avis
                        </span>
                        @endif
                    </div>
                    @if($reviews->count() > 0)
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-size:22px;font-weight:900;color:var(--brand);">{{ number_format($avgRating,1) }}</span>
                        <div>
                            @for($s=1;$s<=5;$s++)<span style="color:{{ $s<=round($avgRating)?'#f59e0b':'#e2e8f0' }};font-size:14px;">★</span>@endfor
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-body" style="padding:0;">

                    {{-- Formulaire laisser un avis --}}
                    @auth
                        @if(in_array(auth()->user()->role, ['vendeur','admin']))
                        <div style="padding:20px 22px;border-bottom:1px solid var(--border);background:var(--surface2);">
                            <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:12px;">
                                {{ $userReview ? '✏️ Modifier votre avis' : '✍️ Laisser un avis' }}
                            </div>
                            <form method="POST" action="{{ route('delivery.companies.review', $company) }}">
                                @csrf
                                {{-- Étoiles cliquables --}}
                                <div style="display:flex;gap:6px;margin-bottom:14px;" id="starPicker">
                                    @for($s=1;$s<=5;$s++)
                                    <label style="cursor:pointer;font-size:28px;color:{{ ($userReview && $userReview->rating>=$s)?'#f59e0b':'#cbd5e1' }};transition:color .15s;" data-star="{{ $s }}">
                                        ★
                                        <input type="radio" name="rating" value="{{ $s }}" style="display:none;" {{ ($userReview && $userReview->rating==$s)?'checked':'' }} required>
                                    </label>
                                    @endfor
                                </div>
                                <textarea name="comment" rows="3" placeholder="Décrivez votre expérience avec cette entreprise…"
                                    style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:var(--r-sm);font-size:13px;font-family:inherit;resize:vertical;background:var(--surface);color:var(--text);outline:none;transition:border-color .15s;"
                                    onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">{{ $userReview?->comment }}</textarea>
                                <button type="submit"
                                    style="margin-top:10px;padding:10px 22px;background:linear-gradient(135deg,var(--brand-dk),var(--brand));color:#fff;border:none;border-radius:var(--r-sm);font-size:13px;font-weight:700;font-family:inherit;cursor:pointer;box-shadow:0 4px 14px rgba(99,102,241,.35);">
                                    {{ $userReview ? 'Mettre à jour' : 'Publier mon avis' }}
                                </button>
                            </form>
                        </div>
                        @endif
                    @else
                    <div style="padding:16px 22px;background:var(--surface2);border-bottom:1px solid var(--border);font-size:13px;color:var(--text2);">
                        <a href="{{ route('login') }}" style="color:var(--brand);font-weight:700;">Connectez-vous</a> pour laisser un avis.
                    </div>
                    @endauth

                    {{-- Liste des avis --}}
                    @forelse($reviews as $review)
                    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;gap:14px;align-items:flex-start;">
                        <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--brand),var(--brand-dkk));display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0;">
                            {{ strtoupper(substr($review->user->name??'?',0,1)) }}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;margin-bottom:4px;">
                                <span style="font-size:13px;font-weight:700;color:var(--text);">{{ $review->user->name ?? 'Vendeur' }}</span>
                                <span style="font-size:11px;color:var(--muted);">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            <div style="margin-bottom:6px;">
                                @for($s=1;$s<=5;$s++)<span style="color:{{ $s<=$review->rating?'#f59e0b':'#e2e8f0' }};font-size:13px;">★</span>@endfor
                            </div>
                            @if($review->comment)
                            <p style="font-size:13px;color:var(--text2);margin:0;line-height:1.65;">{{ $review->comment }}</p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div style="padding:36px 22px;text-align:center;">
                        <div style="font-size:36px;margin-bottom:10px;">⭐</div>
                        <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px;">Aucun avis pour le moment</div>
                        <div style="font-size:12px;color:var(--muted);">Soyez le premier à noter cette entreprise.</div>
                    </div>
                    @endforelse

                </div>
            </div>

        </div>{{-- /main-col --}}

        {{-- ════ SIDEBAR ════ --}}
        <aside class="sidebar">

            {{-- Contact card --}}
            <div class="contact-card">
                <div class="contact-card-body">
                    <div class="contact-card-title">Contacter l'entreprise</div>
                    <div class="contact-card-sub">Démarrez une conversation pour discuter de vos besoins de livraison.</div>

                    <div class="contact-info-list">
                        <div class="contact-info-item">
                            <span class="ci-ico">📞</span>
                            <span class="ci-val {{ !$company->phone ? 'ci-empty' : '' }}">
                                {{ $company->phone ?? 'Téléphone non renseigné' }}
                            </span>
                        </div>
                        <div class="contact-info-item">
                            <span class="ci-ico">✉️</span>
                            <span class="ci-val {{ !$company->email ? 'ci-empty' : '' }}">
                                {{ $company->email ?? 'Email non renseigné' }}
                            </span>
                        </div>
                        @if($company->address)
                        <div class="contact-info-item">
                            <span class="ci-ico">📍</span>
                            <span class="ci-val">{{ Str::limit($company->address, 38) }}</span>
                        </div>
                        @endif
                    </div>

                    @auth
                        @if(in_array(auth()->user()->role, ['vendeur','admin']))
                        <a href="{{ route('company.chat.show', [$company]) }}?shop_id={{ auth()->user()->shop_id ?? auth()->user()->currentShopId() }}"
                           class="btn-cta-main">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            Ouvrir la conversation
                        </a>
                        @else
                        <div class="btn-cta-login" style="pointer-events:none;opacity:.6;cursor:default">
                            💬 Contacter (accès restreint)
                        </div>
                        @endif
                    @else
                    <a href="{{ route('login') }}" class="btn-cta-login">
                        🔑 Se connecter pour contacter
                    </a>
                    @endauth
                </div>
            </div>

            {{-- Fiche entreprise --}}
            <div class="info-card">
                <div class="info-card-hd">
                    <div class="info-card-ico-wrap">ℹ️</div>
                    Fiche entreprise
                </div>
                <div class="info-list">
                    <div class="info-row">
                        <div class="info-ico">📋</div>
                        <div>
                            <div class="info-lbl">Statut</div>
                            @if($company->approved)
                                <span class="status-approved"><span class="status-dot"></span>Approuvée</span>
                            @else
                                <span class="status-pending">⏳ En attente</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-ico">📅</div>
                        <div>
                            <div class="info-lbl">Membre depuis</div>
                            <div class="info-val">{{ \Carbon\Carbon::parse($company->created_at)->format('d/m/Y') }}</div>
                        </div>
                    </div>
                    @if($company->approved_at)
                    <div class="info-row">
                        <div class="info-ico">✅</div>
                        <div>
                            <div class="info-lbl">Date d'approbation</div>
                            <div class="info-val">{{ \Carbon\Carbon::parse($company->approved_at)->format('d/m/Y') }}</div>
                        </div>
                    </div>
                    @endif
                   
                    <div class="info-row">
                        <div class="info-ico">🚴</div>
                        <div>
                            <div class="info-lbl">Livreurs enregistrés</div>
                            <div class="info-val">{{ $company->drivers()->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Zones & Tarifs --}}
            @if($zones->isNotEmpty())
            <div class="info-card">
                <div class="info-card-hd">
                    <div class="info-card-ico-wrap">📍</div>
                    Zones & Tarifs de livraison
                </div>
                <div class="info-list">
                    @foreach($zones as $zone)
                    <div class="info-row" style="gap:12px;padding:12px 18px;">
                        <div style="width:10px;height:10px;border-radius:50%;background:{{ $zone->color }};flex-shrink:0;box-shadow:0 0 6px {{ $zone->color }}88;"></div>
                        <div style="flex:1;min-width:0;">
                            <div class="info-val" style="font-size:13px;">{{ $zone->name }}</div>
                            @if($zone->description)
                            <div class="info-lbl" style="text-transform:none;letter-spacing:0;font-size:11px;margin-top:1px;">{{ $zone->description }}</div>
                            @endif
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            <div style="font-size:14px;font-weight:900;color:var(--brand);">{{ number_format($zone->price, 0, ',', ' ') }} <span style="font-size:10px;font-weight:600;color:var(--muted);">GNF</span></div>
                            <div style="font-size:10px;color:var(--muted);margin-top:1px;">~{{ $zone->estimated_minutes }} min</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Retour liste --}}
            <a href="{{ route('delivery.companies.index') }}"
               style="display:flex;align-items:center;justify-content:center;gap:7px;padding:11px 18px;border-radius:var(--r-sm);background:var(--surface);border:1px solid var(--border);font-size:13px;font-weight:700;color:var(--text2);transition:all .15s;box-shadow:var(--sh-sm);"
               onmouseover="this.style.borderColor='var(--brand)';this.style.color='var(--brand)'"
               onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text2)'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                Voir toutes les entreprises
            </a>

        </aside>

    </div>{{-- /page-layout --}}
</div>{{-- /pw --}}

@endsection

@push('scripts')
<script>
(function () {
    const picker = document.getElementById('starPicker');
    if (!picker) return;
    const labels = picker.querySelectorAll('label');

    function highlight(n) {
        labels.forEach((l, i) => {
            l.style.color = i < n ? '#f59e0b' : '#cbd5e1';
        });
    }

    labels.forEach((label, idx) => {
        label.addEventListener('mouseenter', () => highlight(idx + 1));
        label.addEventListener('mouseleave', () => {
            const checked = picker.querySelector('input:checked');
            highlight(checked ? parseInt(checked.value) : 0);
        });
        label.addEventListener('click', () => highlight(idx + 1));
    });
})();
</script>
@endpush
