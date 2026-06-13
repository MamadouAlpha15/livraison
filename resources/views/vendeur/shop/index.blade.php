@extends('layouts.app')

@section('title', $shop ? $shop->name . ' · Ma boutique' : 'Ma boutique')

@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=block" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    --brand:     #6366f1;
    --brand-dk:  #4f46e5;
    --brand-lt:  #e0e7ff;
    --brand-mlt: #eef2ff;
    --bg:        #f1f5f9;
    --surface:   #ffffff;
    --border:    #e2e8f0;
    --text:      #0f172a;
    --text-2:    #475569;
    --muted:     #94a3b8;
    --success:   #10b981;
    --success-lt:#ecfdf5;
    --warn:      #f59e0b;
    --warn-lt:   #fffbeb;
    --danger:    #ef4444;
    --font:      'Plus Jakarta Sans', sans-serif;
    --r:         16px;
    --r-sm:      10px;
    --shadow-sm: 0 1px 4px rgba(0,0,0,.06);
    --shadow:    0 8px 32px rgba(0,0,0,.08);
    --shadow-lg: 0 20px 60px rgba(0,0,0,.12);
}

html, body {
    font-family: var(--font);
    background: var(--bg);
    color: var(--text);
    margin: 0;
    -webkit-font-smoothing: antialiased;
}

/* ── Page wrapper ── */
.shop-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 32px 20px 60px;
}

/* ══════════════════════════════════════════════
   HERO CARD
══════════════════════════════════════════════ */
.hero-card {
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
    margin-bottom: 24px;
    position: relative;
}

/* Cover photo / gradient background */
.hero-cover {
    height: 180px;
    position: relative;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    overflow: hidden;
}
.hero-cover-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #111168 0%, #1e1b4b 45%, #312e81 100%);
}
.hero-cover-img {
    position: absolute;
    inset: 0;
    width: 100%; height: 100%;
    object-fit: cover;
    filter: brightness(.45) blur(2px);
    transform: scale(1.04);
}
.hero-cover-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,.6) 0%, rgba(0,0,0,0) 60%);
}

/* Avatar */
.hero-avatar-wrap {
    position: absolute;
    bottom: -44px;
    left: 32px;
    z-index: 2;
}
.hero-avatar {
    width: 88px; height: 88px;
    border-radius: 22px;
    border: 4px solid #fff;
    box-shadow: 0 8px 28px rgba(0,0,0,.18);
    object-fit: cover;
    display: block;
}
.hero-avatar-initials {
    width: 88px; height: 88px;
    border-radius: 22px;
    border: 4px solid #fff;
    box-shadow: 0 8px 28px rgba(0,0,0,.18);
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; font-weight: 800; color: #fff;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    letter-spacing: -1px;
    position: relative;
    overflow: hidden;
}
.hero-avatar-initials::after {
    content: '';
    position: absolute;
    top: -50%; left: -50%;
    width: 60%; height: 100%;
    background: rgba(255,255,255,.18);
    transform: rotate(25deg);
}

/* Hero body */
.hero-body {
    background: var(--surface);
    padding: 58px 32px 28px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.hero-info { flex: 1; min-width: 0; }
.hero-name {
    font-size: 24px; font-weight: 800;
    color: var(--text); letter-spacing: -.5px;
    margin: 0 0 8px;
    line-height: 1.2;
}
.hero-badges { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 12px; border-radius: 50px;
    font-size: 11.5px; font-weight: 700;
    border: 1.5px solid transparent;
}
.badge-approved {
    background: var(--success-lt); color: #065f46;
    border-color: #6ee7b7;
}
.badge-pending {
    background: var(--warn-lt); color: #92400e;
    border-color: #fcd34d;
    animation: pulse-badge 2s ease-in-out infinite;
}
@keyframes pulse-badge {
    0%,100% { opacity:1; } 50% { opacity:.7; }
}
.badge-type {
    background: var(--brand-mlt); color: var(--brand-dk);
    border-color: var(--brand-lt);
}
.badge-currency {
    background: #f8fafc; color: var(--text-2);
    border-color: var(--border); font-family: monospace;
}
.hero-actions {
    display: flex; gap: 10px; flex-shrink: 0; align-items: center;
}
.btn-edit {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 20px; border-radius: var(--r-sm);
    font-size: 13px; font-weight: 700; font-family: var(--font);
    background: var(--brand); color: #fff;
    border: 1.5px solid var(--brand-dk);
    text-decoration: none; transition: all .15s;
    box-shadow: 0 4px 14px rgba(99,102,241,.3);
    cursor: pointer;
}
.btn-edit:hover { background: var(--brand-dk); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,.4); }
.btn-dashboard {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 18px; border-radius: var(--r-sm);
    font-size: 13px; font-weight: 600; font-family: var(--font);
    background: var(--surface); color: var(--text-2);
    border: 1.5px solid var(--border);
    text-decoration: none; transition: all .15s;
}
.btn-dashboard:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-mlt); }

/* ══════════════════════════════════════════════
   STATS BAR
══════════════════════════════════════════════ */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}
.stat-card {
    background: var(--surface);
    border-radius: var(--r);
    padding: 20px 18px;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border);
    display: flex; align-items: center; gap: 14px;
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
.stat-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
}
.stat-icon-indigo { background: var(--brand-mlt); }
.stat-icon-green  { background: var(--success-lt); }
.stat-icon-amber  { background: var(--warn-lt); }
.stat-icon-blue   { background: #eff6ff; }
.stat-val {
    font-size: 22px; font-weight: 800; color: var(--text);
    line-height: 1; margin-bottom: 3px;
    letter-spacing: -.5px;
}
.stat-lbl {
    font-size: 11.5px; color: var(--muted); font-weight: 600;
    text-transform: uppercase; letter-spacing: .4px;
}

/* ══════════════════════════════════════════════
   DETAIL GRID
══════════════════════════════════════════════ */
.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.detail-card {
    background: var(--surface);
    border-radius: var(--r);
    padding: 20px 22px;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border);
}
.detail-card.full { grid-column: 1 / -1; }
.detail-card-hd {
    display: flex; align-items: center; gap: 9px;
    margin-bottom: 12px;
}
.detail-icon {
    width: 32px; height: 32px; border-radius: 9px;
    background: var(--brand-mlt); color: var(--brand-dk);
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; flex-shrink: 0;
}
.detail-label {
    font-size: 11px; font-weight: 700;
    color: var(--muted); text-transform: uppercase; letter-spacing: .5px;
}
.detail-value {
    font-size: 14.5px; font-weight: 600; color: var(--text);
    line-height: 1.5;
}
.detail-value.muted { color: var(--muted); font-style: italic; font-weight: 400; }
.detail-value a { color: var(--brand-dk); text-decoration: none; }
.detail-value a:hover { text-decoration: underline; }

/* Commission detail */
.commission-bar-wrap {
    margin-top: 10px;
    background: var(--bg); border-radius: 50px; height: 8px; overflow: hidden;
}
.commission-bar-fill {
    height: 100%; border-radius: 50px;
    background: linear-gradient(90deg, var(--brand), var(--brand-dk));
    transition: width .6s cubic-bezier(.23,1,.32,1);
}
.commission-pct-row {
    display: flex; justify-content: space-between;
    font-size: 11px; color: var(--muted); margin-top: 5px;
}

/* ══════════════════════════════════════════════
   EMPTY STATE (pas de boutique)
══════════════════════════════════════════════ */
.empty-page {
    min-height: calc(100vh - 80px);
    display: flex; align-items: center; justify-content: center;
    padding: 40px 20px;
}
.empty-card {
    background: var(--surface);
    border-radius: 24px;
    padding: 56px 48px;
    box-shadow: var(--shadow-lg);
    max-width: 520px; width: 100%;
    text-align: center;
    border: 1px solid var(--border);
    position: relative; overflow: hidden;
}
.empty-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 4px;
    background: linear-gradient(90deg, #6366f1, #a855f7, #ec4899);
}
.empty-emoji {
    font-size: 64px; line-height: 1;
    margin-bottom: 20px;
    display: block;
    filter: drop-shadow(0 4px 12px rgba(99,102,241,.2));
    animation: float 3s ease-in-out infinite;
}
@keyframes float {
    0%,100% { transform: translateY(0); }
    50%      { transform: translateY(-8px); }
}
.empty-title {
    font-size: 26px; font-weight: 800;
    color: var(--text); letter-spacing: -.5px;
    margin-bottom: 12px;
}
.empty-sub {
    font-size: 14px; color: var(--muted); line-height: 1.7;
    margin-bottom: 32px;
}
.empty-features {
    display: flex; flex-direction: column; gap: 10px;
    margin-bottom: 36px; text-align: left;
}
.empty-feature {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px;
    background: var(--bg); border-radius: var(--r-sm);
    font-size: 13px; color: var(--text-2); font-weight: 500;
}
.empty-feature-icon {
    font-size: 20px; flex-shrink: 0;
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    background: var(--brand-mlt); border-radius: 9px;
}
.btn-create {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 15px 36px; border-radius: var(--r-sm);
    font-size: 15px; font-weight: 800; font-family: var(--font);
    background: linear-gradient(135deg, var(--brand), var(--brand-dk));
    color: #fff; text-decoration: none;
    box-shadow: 0 8px 24px rgba(99,102,241,.4);
    transition: all .2s;
    border: none; cursor: pointer;
    width: 100%; justify-content: center;
}
.btn-create:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(99,102,241,.5);
}

/* Flash */
.flash {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 12px 16px; border-radius: var(--r-sm);
    font-size: 13.5px; font-weight: 500;
    margin-bottom: 20px; border: 1px solid;
}
.flash-success { background: var(--success-lt); border-color: #6ee7b7; color: #065f46; }
.flash-danger  { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }

/* ── Responsive ── */
@media (max-width: 700px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .detail-grid { grid-template-columns: 1fr; }
    .detail-card.full { grid-column: 1; }
}
@media (max-width: 560px) {
    .shop-page { padding: 16px 12px 48px; }
    .hero-cover { height: 140px; }
    .hero-body { padding: 52px 18px 22px; }
    .hero-name { font-size: 20px; }
    .hero-avatar-wrap { left: 18px; bottom: -40px; }
    .hero-avatar, .hero-avatar-initials { width: 78px; height: 78px; border-radius: 18px; font-size: 24px; }
    .hero-actions { width: 100%; }
    .btn-edit, .btn-dashboard { flex: 1; justify-content: center; }
    .stat-card { padding: 14px 12px; gap: 10px; }
    .stat-icon { width: 38px; height: 38px; border-radius: 10px; font-size: 17px; }
    .stat-val { font-size: 18px; }
    .empty-card { padding: 40px 24px; }
    .empty-title { font-size: 22px; }
}
@media (max-width: 400px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .hero-badges { gap: 5px; }
    .badge { font-size: 10.5px; padding: 3px 10px; }
}
</style>
@endpush

@section('content')

{{-- ════════════════════════════════════════
     CAS 1 : La boutique existe
════════════════════════════════════════ --}}
@if($shop)

@php
    $initials    = collect(explode(' ', $shop->name))->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');
    $productsCount = $shop->products()->where('is_active', true)->count();
    $ordersCount   = $shop->orders()->count();
    $commPct       = $shop->commission_rate ? round($shop->commission_rate * 100, 1) : 0;
    $currency      = $shop->currency ?? 'GNF';
@endphp

<div class="shop-page">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="flash flash-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="flash flash-danger">⚠️ {{ session('error') }}</div>
    @endif

    {{-- ── Hero card ── --}}
    <div class="hero-card">

        {{-- Cover photo --}}
        <div class="hero-cover">
            <div class="hero-cover-bg"></div>
            @if($shop->image)
                <img class="hero-cover-img" src="{{ asset('storage/' . $shop->image) }}" alt="">
            @endif
            <div class="hero-cover-overlay"></div>

            {{-- Avatar --}}
            <div class="hero-avatar-wrap">
                @if($shop->image)
                    <img class="hero-avatar" src="{{ asset('storage/' . $shop->image) }}" alt="{{ $shop->name }}">
                @else
                    <div class="hero-avatar-initials">{{ $initials }}</div>
                @endif
            </div>
        </div>

        {{-- Body --}}
        <div class="hero-body">
            <div class="hero-info">
                <h1 class="hero-name">{{ $shop->name }}</h1>
                <div class="hero-badges">
                    @if($shop->is_approved)
                        <span class="badge badge-approved">✅ Boutique active</span>
                    @else
                        <span class="badge badge-pending">⛔ Boutique suspendue</span>
                    @endif
                    @if($shop->type)
                        <span class="badge badge-type">🏷️ {{ $shop->type }}</span>
                    @endif
                    <span class="badge badge-currency">{{ $currency }}</span>
                </div>
            </div>
            <div class="hero-actions">
                <a href="{{ route('boutique.dashboard') }}" class="btn-dashboard">📊 Dashboard</a>
                <a href="{{ route('shop.edit', $shop->id) }}" class="btn-edit">✏️ Modifier</a>
            </div>
        </div>
    </div>

    {{-- ── Stats bar ── --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon-indigo">📦</div>
            <div>
                <div class="stat-val">{{ $productsCount }}</div>
                <div class="stat-lbl">Produits actifs</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-green">🛒</div>
            <div>
                <div class="stat-val">{{ $ordersCount }}</div>
                <div class="stat-lbl">Commandes</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-blue">💱</div>
            <div>
                <div class="stat-val" style="font-size:17px;letter-spacing:0">{{ $currency }}</div>
                <div class="stat-lbl">Devise</div>
            </div>
        </div>
    </div>

    {{-- ── Detail cards ── --}}
    <div class="detail-grid">

        {{-- Adresse --}}
        <div class="detail-card">
            <div class="detail-card-hd">
                <div class="detail-icon">📍</div>
                <span class="detail-label">Adresse</span>
            </div>
            @if($shop->address)
                <div class="detail-value">{{ $shop->address }}</div>
            @else
                <div class="detail-value muted">Non renseignée</div>
            @endif
        </div>

        {{-- Téléphone --}}
        <div class="detail-card">
            <div class="detail-card-hd">
                <div class="detail-icon">📞</div>
                <span class="detail-label">Téléphone</span>
            </div>
            @if($shop->phone)
                <div class="detail-value"><a href="tel:{{ $shop->phone }}">{{ $shop->phone }}</a></div>
            @else
                <div class="detail-value muted">Non renseigné</div>
            @endif
        </div>

        {{-- Email --}}
        <div class="detail-card">
            <div class="detail-card-hd">
                <div class="detail-icon">✉️</div>
                <span class="detail-label">Email boutique</span>
            </div>
            @if($shop->email)
                <div class="detail-value"><a href="mailto:{{ $shop->email }}">{{ $shop->email }}</a></div>
            @else
                <div class="detail-value muted">Non renseigné</div>
            @endif
        </div>


        {{-- Description --}}
        <div class="detail-card full">
            <div class="detail-card-hd">
                <div class="detail-icon">📝</div>
                <span class="detail-label">Description</span>
            </div>
            @if($shop->description)
                <div class="detail-value" style="font-weight:400;color:var(--text-2);line-height:1.7">
                    {{ $shop->description }}
                </div>
            @else
                <div class="detail-value muted">Aucune description. <a href="{{ route('shop.edit', $shop->id) }}">Ajouter une description →</a></div>
            @endif
        </div>

    </div>
</div>

{{-- ════════════════════════════════════════
     CAS 2 : Pas de boutique
════════════════════════════════════════ --}}
@else

<div class="empty-page">
    <div class="empty-card">
        <span class="empty-emoji">🛍️</span>
        <div class="empty-title">Vous n'avez pas encore de boutique</div>
        <div class="empty-sub">
            Créez votre boutique en quelques minutes et commencez à vendre dès aujourd'hui.
        </div>

        <div class="empty-features">
            <div class="empty-feature">
                <div class="empty-feature-icon">📦</div>
                <span>Gérez vos produits facilement</span>
            </div>
            <div class="empty-feature">
                <div class="empty-feature-icon">🚀</div>
                <span>Recevez des commandes en temps réel</span>
            </div>
            <div class="empty-feature">
                <div class="empty-feature-icon">🚚</div>
                <span>Réseau de livreurs intégré</span>
            </div>
            <div class="empty-feature">
                <div class="empty-feature-icon">📊</div>
                <span>Tableau de bord & statistiques</span>
            </div>
        </div>

        <a href="{{ route('shop.create') }}" class="btn-create">
            ✨ Créer ma boutique
        </a>
    </div>
</div>

@endif

@endsection
