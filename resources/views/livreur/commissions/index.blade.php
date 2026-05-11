@extends('layouts.app')
@section('title', $isCompanyDriver ? 'Commissions de livraison' : 'Mes commissions')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
:root {
    --navy:#0f172a; --navy2:#1e3a5f; --green:#10b981; --green-lt:#d1fae5;
    --green-dk:#065f46; --yellow:#f59e0b; --yellow-lt:#fef3c7; --yellow-dk:#92400e;
    --border:#e9edef; --muted:#64748b; --text:#0f172a; --bg:#f1f5f9;
    --font:'Segoe UI',sans-serif;
}
*,*::before,*::after{box-sizing:border-box}
body{margin:0;font-family:var(--font);background:var(--bg);color:var(--text)}

/* ── HERO ── */
.cm-hero {
    background:linear-gradient(135deg,var(--navy) 0%,var(--navy2) 60%,#1a4a7a 100%);
    padding:24px 24px 72px; position:relative; overflow:hidden;
}
.cm-hero::before {
    content:'';position:absolute;inset:0;
    background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Ccircle cx='30' cy='30' r='1.5' fill='%23ffffff' opacity='.06'/%3E%3C/svg%3E");
}
.cm-hero-top { display:flex; align-items:center; justify-content:space-between; position:relative; }
.cm-hero-back { display:inline-flex; align-items:center; gap:8px; padding:9px 16px; background:rgba(255,255,255,.12); color:#fff; border:1.5px solid rgba(255,255,255,.2); border-radius:10px; font-size:13px; font-weight:700; text-decoration:none; transition:all .15s; }
.cm-hero-back:hover { background:rgba(255,255,255,.22); color:#fff; transform:translateX(-2px); }
.cm-hero-title { font-size:22px; font-weight:900; color:#fff; margin-top:18px; position:relative; }
.cm-hero-sub   { font-size:13px; color:rgba(255,255,255,.55); margin-top:4px; position:relative; }

/* ── KPI ── */
.cm-kpi-wrap { display:grid; grid-template-columns:1fr 1fr; gap:16px; padding:0 24px; margin-top:-48px; position:relative; z-index:2; }
.cm-kpi { background:#fff; border-radius:16px; padding:20px; box-shadow:0 4px 20px rgba(0,0,0,.09); display:flex; align-items:center; gap:16px; transition:transform .15s; }
.cm-kpi:hover { transform:translateY(-3px); }
.cm-kpi-ico { width:52px; height:52px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:24px; flex-shrink:0; }
.cm-kpi.yellow .cm-kpi-ico { background:var(--yellow-lt); }
.cm-kpi.green  .cm-kpi-ico { background:var(--green-lt); }
.cm-kpi-val { font-size:20px; font-weight:900; font-family:monospace; line-height:1; }
.cm-kpi.yellow .cm-kpi-val { color:var(--yellow-dk); }
.cm-kpi.green  .cm-kpi-val { color:var(--green-dk); }
.cm-kpi-lbl { font-size:12px; font-weight:600; color:var(--muted); margin-top:4px; }

/* ── BODY ── */
.cm-body { padding:24px; display:flex; flex-direction:column; gap:20px; }

/* ── CARD ── */
.cm-card { background:#fff; border-radius:16px; box-shadow:0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
.cm-card-hd { padding:16px 20px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid var(--border); }
.cm-card-title { font-size:14px; font-weight:800; color:var(--text); display:flex; align-items:center; gap:8px; }
.cm-card-badge { font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; }
.badge-yellow { background:var(--yellow-lt); color:var(--yellow-dk); }
.badge-green  { background:var(--green-lt);  color:var(--green-dk); }

/* ── LIGNES ── */
.cm-row { display:flex; align-items:center; gap:12px; padding:14px 20px; border-bottom:1px solid #f8fafc; transition:background .12s; }
.cm-row:last-child { border-bottom:none; }
.cm-row:hover { background:#f8fafc; }
.cm-row-ico { width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
.cm-row-ico.pending { background:var(--yellow-lt); }
.cm-row-ico.paid    { background:var(--green-lt); }
.cm-row-info { flex:1; min-width:0; }
.cm-row-order { font-size:13px; font-weight:700; color:var(--text); }
.cm-row-date  { font-size:11.5px; color:var(--muted); margin-top:2px; }
.cm-row-right { text-align:right; flex-shrink:0; }
.cm-row-amount { font-size:14px; font-weight:900; font-family:monospace; }
.cm-row-amount.pending { color:var(--yellow-dk); }
.cm-row-amount.paid    { color:var(--green-dk); }
.cm-row-rate { font-size:11px; color:var(--muted); margin-top:2px; }
.cm-row-ref  { font-size:11px; color:var(--green); margin-top:2px; font-weight:600; }

/* ── BANNIÈRE INFO ENTREPRISE ── */
.cm-info-banner {
    padding:14px 16px; border-radius:12px;
    background:#eff6ff; border:1.5px solid #bfdbfe;
    display:flex; gap:12px; align-items:flex-start;
}
.cm-info-banner-ico { font-size:20px; flex-shrink:0; margin-top:1px; }
.cm-info-banner-text { font-size:12.5px; color:#1e40af; line-height:1.55; }
.cm-info-banner-text strong { font-weight:800; color:#1d4ed8; }

/* ── EMPTY ── */
.cm-empty { padding:36px 20px; text-align:center; color:var(--muted); font-size:13px; }
.cm-empty-ico { font-size:40px; display:block; margin-bottom:10px; opacity:.35; }

/* ── PAGINATION ── */
.cm-pagination { padding:12px 20px; border-top:1px solid var(--border); }
.cm-pagination .pagination { margin:0; gap:4px; display:flex; flex-wrap:wrap; }

/* ── RESPONSIVE ── */
@media(max-width:640px) {
    .cm-hero { padding:18px 16px 64px; }
    .cm-hero-title { font-size:18px; }
    .cm-kpi-wrap { padding:0 16px; gap:12px; }
    .cm-kpi { padding:14px; gap:12px; }
    .cm-kpi-ico { width:42px; height:42px; font-size:20px; }
    .cm-kpi-val { font-size:17px; }
    .cm-body { padding:16px; }
    .cm-row { padding:12px 16px; }
}
@media(max-width:400px) {
    .cm-kpi-wrap { grid-template-columns:1fr; }
}
</style>
@endpush

@section('content')
@php
    $fmt = fn($n) => number_format($n ?? 0, 0, ',', ' ') . ' ' . $devise;
@endphp

{{-- HERO --}}
<div class="cm-hero">
    <div class="cm-hero-top">
        <a href="{{ route('livreur.dashboard') }}" class="cm-hero-back">← Retour</a>
    </div>
    @if($isCompanyDriver)
    <div class="cm-hero-title">📊 Commissions de livraison</div>
    <div class="cm-hero-sub">Montants payés par les boutiques à {{ $companyName }}</div>
    @else
    <div class="cm-hero-title">💸 Mes commissions</div>
    <div class="cm-hero-sub">Suivi de vos gains sur chaque livraison</div>
    @endif
</div>

{{-- KPI --}}
<div class="cm-kpi-wrap">
    <div class="cm-kpi yellow">
        <div class="cm-kpi-ico">⏳</div>
        <div>
            <div class="cm-kpi-val">{{ $fmt($pendingTotal) }}</div>
            <div class="cm-kpi-lbl">{{ $isCompanyDriver ? 'En attente (boutiques → entreprise)' : 'En attente de paiement' }}</div>
        </div>
    </div>
    <div class="cm-kpi green">
        <div class="cm-kpi-ico">✅</div>
        <div>
            <div class="cm-kpi-val">{{ $fmt($paidTotal) }}</div>
            <div class="cm-kpi-lbl">{{ $isCompanyDriver ? 'Total encaissé par l\'entreprise' : 'Total payé' }}</div>
        </div>
    </div>
</div>

{{-- BODY --}}
<div class="cm-body">

    @if($isCompanyDriver)
    <div class="cm-info-banner">
        <div class="cm-info-banner-ico">ℹ️</div>
        <div class="cm-info-banner-text">
            <strong>Ces montants ne sont pas vos gains personnels.</strong><br>
            Il s'agit des commissions versées par les boutiques à <strong>{{ $companyName }}</strong> pour chaque livraison effectuée.
            Votre rémunération est gérée directement par votre entreprise.
        </div>
    </div>
    @endif

    {{-- En attente --}}
    <div class="cm-card">
        <div class="cm-card-hd">
            <span class="cm-card-title">
                ⏳ {{ $isCompanyDriver ? 'En attente (boutiques → entreprise)' : 'En attente' }}
                <span class="cm-card-badge badge-yellow">{{ $pending->total() }}</span>
            </span>
        </div>
        @forelse($pending as $c)
        <div class="cm-row">
            <div class="cm-row-ico pending">💰</div>
            <div class="cm-row-info">
                <div class="cm-row-order">Commande #{{ $c->order_id }}</div>
                <div class="cm-row-date">{{ $c->created_at->format('d/m/Y à H:i') }}</div>
            </div>
            <div class="cm-row-right">
                <div class="cm-row-amount pending">{{ $fmt($c->amount) }}</div>
                <div class="cm-row-rate">Taux {{ number_format($c->rate * 100, 1) }}% · Cmd {{ $fmt($c->order_total) }}</div>
            </div>
        </div>
        @empty
        <div class="cm-empty">
            <span class="cm-empty-ico">💤</span>
            {{ $isCompanyDriver ? 'Aucun montant en attente.' : 'Aucune commission en attente.' }}
        </div>
        @endforelse
        @if($pending->hasPages())
        <div class="cm-pagination">{{ $pending->links() }}</div>
        @endif
    </div>

    {{-- Payées --}}
    <div class="cm-card">
        <div class="cm-card-hd">
            <span class="cm-card-title">
                ✅ {{ $isCompanyDriver ? 'Encaissées par l\'entreprise' : 'Payées' }}
                <span class="cm-card-badge badge-green">{{ $paid->total() }}</span>
            </span>
        </div>
        @forelse($paid as $c)
        <div class="cm-row">
            <div class="cm-row-ico paid">✅</div>
            <div class="cm-row-info">
                <div class="cm-row-order">Commande #{{ $c->order_id }}</div>
                <div class="cm-row-date">{{ optional($c->paid_at)->format('d/m/Y à H:i') ?? $c->created_at->format('d/m/Y') }}</div>
            </div>
            <div class="cm-row-right">
                <div class="cm-row-amount paid">{{ $fmt($c->amount) }}</div>
                @if($c->payout_ref)
                <div class="cm-row-ref">Réf: {{ $c->payout_ref }}</div>
                @endif
            </div>
        </div>
        @empty
        <div class="cm-empty">
            <span class="cm-empty-ico">🏦</span>
            {{ $isCompanyDriver ? 'Aucun montant encaissé pour l\'instant.' : 'Pas encore de commission payée.' }}
        </div>
        @endforelse
        @if($paid->hasPages())
        <div class="cm-pagination">{{ $paid->links() }}</div>
        @endif
    </div>

</div>
@endsection
