@extends('layouts.app')
@section('title', 'Top ventes du mois · ' . $shop->name)
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
:root {
    --brand:#6366f1; --brand-dk:#4f46e5; --brand-lt:#e0e7ff; --brand-mlt:#eef2ff;
    --navy:#0f172a; --navy2:#1e3a5f;
    --orange:#f59e0b; --orange-lt:#fef3c7; --orange-dk:#92400e;
    --bg:#f1f5f9; --surface:#fff; --border:#e9edef; --muted:#64748b; --text:#0f172a;
    --font:'Segoe UI',sans-serif;
}
*,*::before,*::after{box-sizing:border-box}
body{margin:0;font-family:var(--font);background:var(--bg);color:var(--text)}

/* HERO */
.tp-hero {
    background:linear-gradient(135deg,var(--navy) 0%,var(--navy2) 60%,#0d3b6e 100%);
    padding:28px 28px 80px; position:relative; overflow:hidden;
}
.tp-hero::before {
    content:''; position:absolute; inset:0;
    background:radial-gradient(circle at 80% 50%, rgba(99,102,241,.12) 0%, transparent 60%);
}
.tp-hero-top { display:flex; align-items:center; justify-content:space-between; position:relative; gap:12px; flex-wrap:wrap; }
.tp-back { display:inline-flex; align-items:center; gap:8px; padding:9px 18px; background:rgba(255,255,255,.12); color:#fff; border:1.5px solid rgba(255,255,255,.2); border-radius:10px; font-size:13px; font-weight:700; text-decoration:none; transition:all .15s; }
.tp-back:hover { background:rgba(255,255,255,.22); transform:translateX(-2px); }
.tp-hero-title { font-size:26px; font-weight:900; color:#fff; margin-top:20px; position:relative; letter-spacing:-.5px; }
.tp-hero-sub   { font-size:13px; color:rgba(255,255,255,.55); margin-top:5px; position:relative; }
.tp-period-badge {
    display:inline-flex; align-items:center; gap:6px;
    background:rgba(99,102,241,.2); border:1px solid rgba(99,102,241,.35);
    color:#a5b4fc; font-size:12px; font-weight:700; padding:5px 12px;
    border-radius:20px; margin-top:10px; position:relative;
}

/* KPI FLOTTANTS */
.tp-kpi-row {
    display:flex; gap:14px; padding:0 28px;
    margin-top:-48px; position:relative; z-index:2; flex-wrap:wrap;
}
.tp-kpi {
    flex:1; min-width:140px;
    background:#fff; border-radius:14px;
    box-shadow:0 4px 20px rgba(0,0,0,.1);
    padding:16px 20px;
    display:flex; align-items:center; gap:14px;
}
.tp-kpi-ico { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
.tp-kpi-lbl { font-size:10.5px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.4px; margin-bottom:3px; }
.tp-kpi-val { font-size:20px; font-weight:900; color:var(--text); font-family:monospace; line-height:1; }
.tp-kpi-unit { font-size:10px; color:var(--muted); margin-top:2px; }

/* BODY */
.tp-body { padding:28px 28px 60px; }

/* PODIUM TOP 3 */
.tp-podium { display:flex; align-items:flex-end; justify-content:center; gap:16px; margin-bottom:32px; }
.tp-pod-item { display:flex; flex-direction:column; align-items:center; gap:10px; flex:1; max-width:200px; }
.tp-pod-img-wrap { position:relative; }
.tp-pod-img { width:80px; height:80px; border-radius:16px; object-fit:cover; border:3px solid; }
.tp-pod-ph  { width:80px; height:80px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:30px; border:3px solid; }
.tp-pod-crown { position:absolute; top:-14px; left:50%; transform:translateX(-50%); font-size:22px; }
.tp-pod-rank { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:900; color:#fff; position:absolute; bottom:-8px; right:-8px; }
.tp-pod-name { font-size:13px; font-weight:700; color:var(--text); text-align:center; max-width:160px; line-height:1.3; }
.tp-pod-sales { font-size:12px; font-weight:600; color:var(--muted); }
.tp-pod-revenue { font-size:14px; font-weight:900; color:var(--brand-dk); font-family:monospace; }
.tp-pod-bar { width:100%; border-radius:10px 10px 0 0; }

.tp-pod-item.rank-1 .tp-pod-img, .tp-pod-item.rank-1 .tp-pod-ph { border-color:#f59e0b; width:96px; height:96px; }
.tp-pod-item.rank-1 .tp-pod-rank { background:#f59e0b; }
.tp-pod-item.rank-1 .tp-pod-bar { background:linear-gradient(180deg,#fef3c7,#fde68a); height:100px; }
.tp-pod-item.rank-2 .tp-pod-img, .tp-pod-item.rank-2 .tp-pod-ph { border-color:#94a3b8; }
.tp-pod-item.rank-2 .tp-pod-rank { background:#94a3b8; }
.tp-pod-item.rank-2 .tp-pod-bar { background:linear-gradient(180deg,#f1f5f9,#e2e8f0); height:75px; }
.tp-pod-item.rank-3 .tp-pod-img, .tp-pod-item.rank-3 .tp-pod-ph { border-color:#cd7c3a; }
.tp-pod-item.rank-3 .tp-pod-rank { background:#cd7c3a; }
.tp-pod-item.rank-3 .tp-pod-bar { background:linear-gradient(180deg,#fef9ec,#fde8c8); height:55px; }

/* LISTE COMPLETE */
.tp-list-card { background:#fff; border-radius:16px; box-shadow:0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
.tp-list-hd { padding:16px 22px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
.tp-list-title { font-size:15px; font-weight:800; color:var(--text); }

.tp-row {
    display:flex; align-items:center; gap:16px;
    padding:14px 22px; border-bottom:1px solid #f8fafc;
    transition:background .12s;
}
.tp-row:last-child { border-bottom:none; }
.tp-row:hover { background:#f8fafc; }
.tp-rank-num {
    width:28px; height:28px; border-radius:8px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:12px; font-weight:900;
}
.tp-rank-num.gold   { background:#fef3c7; color:#92400e; }
.tp-rank-num.silver { background:#f1f5f9; color:#475569; }
.tp-rank-num.bronze { background:#fef9ec; color:#9a4b1a; }
.tp-rank-num.other  { background:#f8fafc; color:var(--muted); }
.tp-prod-img { width:52px; height:52px; border-radius:10px; object-fit:cover; border:1.5px solid var(--border); flex-shrink:0; }
.tp-prod-ph  { width:52px; height:52px; border-radius:10px; background:var(--bg); border:1.5px solid var(--border); display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
.tp-prod-info { flex:1; min-width:0; }
.tp-prod-name { font-size:13px; font-weight:700; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.tp-prod-cat  { font-size:11px; color:var(--muted); margin-top:2px; }
.tp-bar-wrap { flex:1; min-width:80px; max-width:200px; }
.tp-bar-track { height:8px; background:#f1f5f9; border-radius:4px; overflow:hidden; }
.tp-bar-fill  { height:100%; border-radius:4px; background:linear-gradient(90deg,var(--brand),var(--brand-dk)); transition:width .6s cubic-bezier(.23,1,.32,1); width:0; }
.tp-sales-num { font-size:13px; font-weight:800; color:var(--text); font-family:monospace; white-space:nowrap; text-align:right; }
.tp-sales-lbl { font-size:10px; color:var(--muted); margin-top:1px; text-align:right; }
.tp-revenue   { font-size:13px; font-weight:700; color:var(--brand-dk); font-family:monospace; white-space:nowrap; text-align:right; }

/* EMPTY */
.tp-empty { padding:60px 20px; text-align:center; }
.tp-empty-ico { font-size:52px; display:block; margin-bottom:14px; opacity:.3; }
.tp-empty-title { font-size:16px; font-weight:700; color:var(--text); margin-bottom:6px; }

/* RESPONSIVE */
@media(max-width:1024px) {
    .tp-kpi-val { font-size:18px; }
    .tp-bar-wrap { max-width:140px; }
}
@media(max-width:768px) {
    .tp-hero { padding:18px 16px 72px; }
    .tp-hero-title { font-size:20px; }
    .tp-kpi-row { padding:0 16px; gap:10px; flex-wrap:wrap; }
    .tp-kpi { flex:0 0 calc(50% - 5px); min-width:0; }
    .tp-body { padding:20px 16px 40px; }
    .tp-podium { gap:8px; }
    .tp-pod-item.rank-1 .tp-pod-img,
    .tp-pod-item.rank-1 .tp-pod-ph { width:72px; height:72px; }
    .tp-pod-img, .tp-pod-ph { width:60px!important; height:60px!important; }
    .tp-bar-wrap { display:none; }
    .tp-row { gap:10px; padding:12px 16px; }
    .tp-list-hd { flex-wrap:wrap; gap:4px; }
    .tp-list-title { font-size:13px; }
}
@media(max-width:480px) {
    .tp-hero { padding:16px 12px 68px; }
    .tp-hero-top { gap:8px; }
    .tp-back { padding:7px 11px; font-size:12px; }
    .tp-hero-title { font-size:17px; }
    .tp-hero-sub { font-size:12px; }
    .tp-kpi-row { padding:0 12px; gap:8px; margin-top:-40px; }
    .tp-kpi { flex:0 0 calc(50% - 4px); min-width:0; padding:12px 14px; gap:10px; }
    .tp-kpi-ico { width:36px; height:36px; font-size:16px; }
    .tp-kpi-val { font-size:16px; }
    .tp-body { padding:16px 12px 40px; }
    .tp-podium { flex-direction:column; align-items:center; gap:8px; }
    .tp-pod-bar { display:none; }
    .tp-pod-item { max-width:280px; width:100%; }
    .tp-pod-item.rank-1 .tp-pod-img,
    .tp-pod-item.rank-1 .tp-pod-ph { width:64px!important; height:64px!important; }
    .tp-prod-img, .tp-prod-ph { width:42px!important; height:42px!important; border-radius:8px!important; }
    .tp-row { gap:8px; padding:10px 12px; }
    .tp-revenue-col { display:none; }
    .tp-list-hd { padding:12px 16px; }
}
@media(max-width:360px) {
    .tp-hero-top { flex-direction:column; align-items:flex-start; }
    .tp-kpi { flex:0 0 100%; }
    .tp-kpi-val { font-size:15px; }
    .tp-prod-name { font-size:12px; }
    .tp-prod-img, .tp-prod-ph { width:36px!important; height:36px!important; }
    .tp-rank-num { width:24px; height:24px; font-size:11px; }
    .tp-back { font-size:11px; }
}
</style>
@endpush

@section('content')
@php
    $fmt = fn($n) => number_format($n ?? 0, 0, ',', ' ') . ' ' . $devise;
    $totalVentes  = $topProducts->sum('monthly_sales');
    $totalRevenue = $topProducts->sum('monthly_revenue');
    $top3 = $topProducts->take(3);
    $rest = $topProducts->skip(3);
    $mois = ucfirst($now->isoFormat('MMMM YYYY'));
@endphp

{{-- HERO --}}
<div class="tp-hero">
    <div class="tp-hero-top">
        <a href="{{ route('boutique.dashboard') }}" class="tp-back">← Tableau de bord</a>
        <a href="{{ route('products.index') }}" class="tp-back" style="background:rgba(99,102,241,.2);border-color:rgba(99,102,241,.4);color:#a5b4fc;">
            🏷️ Tous les produits
        </a>
    </div>
    <div class="tp-hero-title">🏆 Top ventes du mois</div>
    <div class="tp-hero-sub">{{ $shop->name }} — classement des meilleures ventes</div>
    <div class="tp-period-badge">📅 {{ $mois }}</div>
</div>

{{-- KPI FLOTTANTS --}}
<div class="tp-kpi-row">
    <div class="tp-kpi">
        <div class="tp-kpi-ico" style="background:#eef2ff;">🏆</div>
        <div>
            <div class="tp-kpi-lbl">Produits classés</div>
            <div class="tp-kpi-val">{{ $topProducts->count() }}</div>
            <div class="tp-kpi-unit">produits ce mois</div>
        </div>
    </div>
    <div class="tp-kpi">
        <div class="tp-kpi-ico" style="background:#eff6ff;">📦</div>
        <div>
            <div class="tp-kpi-lbl">Total vendus</div>
            <div class="tp-kpi-val">{{ number_format($totalVentes, 0, ',', ' ') }}</div>
            <div class="tp-kpi-unit">articles ce mois</div>
        </div>
    </div>
    <div class="tp-kpi">
        <div class="tp-kpi-ico" style="background:#fef3c7;">💰</div>
        <div>
            <div class="tp-kpi-lbl">Revenu total</div>
            <div class="tp-kpi-val" style="font-size:15px;">{{ number_format($totalRevenue, 0, ',', ' ') }}</div>
            <div class="tp-kpi-unit">{{ $devise }}</div>
        </div>
    </div>
    @if($topProducts->first())
    <div class="tp-kpi">
        <div class="tp-kpi-ico" style="background:#fef3c7;">🥇</div>
        <div>
            <div class="tp-kpi-lbl">N°1 du mois</div>
            <div class="tp-kpi-val" style="font-size:13px;letter-spacing:0;">{{ Str::limit($topProducts->first()->name, 16) }}</div>
            <div class="tp-kpi-unit">{{ $topProducts->first()->monthly_sales }} ventes</div>
        </div>
    </div>
    @endif
</div>

<div class="tp-body">

@if($topProducts->isEmpty())
    <div class="tp-empty">
        <span class="tp-empty-ico">📭</span>
        <div class="tp-empty-title">Aucune vente ce mois</div>
        <div style="font-size:13px;color:var(--muted);">Aucun produit n'a été vendu ce mois-ci.</div>
    </div>
@else

    {{-- PODIUM TOP 3 --}}
    @if($top3->count() >= 2)
    <div style="margin-bottom:28px;">
        <div style="font-size:13px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;text-align:center;">🏅 Podium du mois</div>
        <div class="tp-podium">
            {{-- Ordre visuel : 2ème — 1er — 3ème --}}
            @php $podOrder = [$top3->get(1), $top3->get(0), $top3->get(2)]; $podRanks = [2,1,3]; @endphp
            @foreach($podOrder as $k => $p)
            @if($p)
            @php $rank = $podRanks[$k]; $crowns = ['','👑','🥈','🥉']; @endphp
            <div class="tp-pod-item rank-{{ $rank }}">
                <div class="tp-pod-img-wrap">
                    <span class="tp-pod-crown">{{ $crowns[$rank] }}</span>
                    @if($p->image)
                        <img src="{{ asset('storage/'.$p->image) }}" alt="{{ $p->name }}" class="tp-pod-img">
                    @else
                        <div class="tp-pod-ph">🛒</div>
                    @endif
                    <span class="tp-pod-rank">{{ $rank }}</span>
                </div>
                <div class="tp-pod-name">{{ Str::limit($p->name, 22) }}</div>
                <div class="tp-pod-sales">{{ $p->monthly_sales }} vente{{ $p->monthly_sales > 1 ? 's' : '' }}</div>
                @if($p->monthly_revenue)
                <div class="tp-pod-revenue">{{ number_format($p->monthly_revenue, 0, ',', ' ') }} {{ $devise }}</div>
                @endif
                <div class="tp-pod-bar"></div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- LISTE COMPLÈTE --}}
    <div class="tp-list-card">
        <div class="tp-list-hd">
            <span class="tp-list-title">📊 Classement complet — {{ $topProducts->count() }} produits</span>
            <span style="font-size:12px;color:var(--muted);">{{ $mois }}</span>
        </div>

        @foreach($topProducts as $i => $product)
        @php
            $rank   = $i + 1;
            $rankCls = $rank === 1 ? 'gold' : ($rank === 2 ? 'silver' : ($rank === 3 ? 'bronze' : 'other'));
            $pct    = $maxSales > 0 ? round(($product->monthly_sales / $maxSales) * 100) : 0;
        @endphp
        <div class="tp-row">
            {{-- Rang --}}
            <div class="tp-rank-num {{ $rankCls }}">{{ $rank }}</div>

            {{-- Image --}}
            @if($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="tp-prod-img">
            @else
                <div class="tp-prod-ph">🛒</div>
            @endif

            {{-- Infos produit --}}
            <div class="tp-prod-info">
                <div class="tp-prod-name">{{ $product->name }}</div>
                <div class="tp-prod-cat">
                    @if($product->category) {{ $product->category }} · @endif
                    {{ $fmt($product->price) }}
                </div>
            </div>

            {{-- Barre progression --}}
            <div class="tp-bar-wrap">
                <div class="tp-bar-track">
                    <div class="tp-bar-fill" data-pct="{{ $pct }}"></div>
                </div>
            </div>

            {{-- Ventes --}}
            <div style="text-align:right;flex-shrink:0;">
                <div class="tp-sales-num">{{ $product->monthly_sales }}</div>
                <div class="tp-sales-lbl">vente{{ $product->monthly_sales > 1 ? 's' : '' }}</div>
            </div>

            {{-- Revenu --}}
            @if($product->monthly_revenue)
            <div class="tp-revenue-col" style="text-align:right;flex-shrink:0;min-width:90px;">
                <div class="tp-revenue">{{ number_format($product->monthly_revenue, 0, ',', ' ') }}</div>
                <div class="tp-sales-lbl">{{ $devise }}</div>
            </div>
            @endif
        </div>
        @endforeach
    </div>

@endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.tp-bar-fill').forEach((el, i) => {
        setTimeout(() => { el.style.width = el.dataset.pct + '%'; }, 100 + i * 60);
    });
});
</script>
@endpush
