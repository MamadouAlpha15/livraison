{{--
    resources/views/boutique/clients/show.blade.php
    Route  : GET /boutique/clients/{user} → Boutique\ClientController@show → name('boutique.clients.show')
    Variables :
      $user      → User (le client)
      $commandes → LengthAwarePaginator<Order>
      $stats     → object (total_depense, nb_commandes, derniere_cmd, premiere_cmd)
      $isTop     → bool
      $shop      → Shop
      $devise    → string
--}}

@extends('layouts.app')
@section('title', 'Fiche client · ' . $user->name)
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*,*::before,*::after { box-sizing:border-box; }

:root {
    --brand:#6366f1; --brand-dk:#4f46e5; --brand-lt:#e0e7ff;
    --navy:#0f172a;  --navy2:#1e3a5f;
    --bg:#f1f5f9;    --surface:#fff;
    --border:#e2e8f0; --muted:#64748b; --text:#0f172a;
    --font:'Segoe UI',sans-serif; --mono:'JetBrains Mono',monospace;
    --r:14px; --r-sm:9px;
}

body { margin:0; font-family:var(--font); background:var(--bg); color:var(--text); }

/* ── HERO ── */
.cl-hero {
    background:linear-gradient(135deg,var(--navy) 0%,var(--navy2) 60%,#0d3b6e 100%);
    padding:24px 28px 80px; position:relative; overflow:hidden;
}
.cl-hero::before {
    content:''; position:absolute; inset:0;
    background:radial-gradient(circle at 75% 40%, rgba(99,102,241,.15) 0%, transparent 60%);
    pointer-events:none;
}
.cl-back {
    display:inline-flex; align-items:center; gap:7px;
    padding:8px 16px; background:rgba(255,255,255,.12);
    color:#fff; border:1.5px solid rgba(255,255,255,.2);
    border-radius:10px; font-size:12.5px; font-weight:700;
    text-decoration:none; transition:all .15s; position:relative;
}
.cl-back:hover { background:rgba(255,255,255,.22); transform:translateX(-2px); }

.cl-hero-body {
    display:flex; align-items:center; gap:20px;
    margin-top:20px; position:relative; flex-wrap:wrap;
}
.cl-avatar {
    width:72px; height:72px; border-radius:50%; flex-shrink:0;
    background:linear-gradient(135deg,#818cf8,#4f46e5);
    display:flex; align-items:center; justify-content:center;
    font-size:24px; font-weight:800; color:#fff; letter-spacing:-.5px;
    box-shadow:0 0 0 4px rgba(255,255,255,.2);
}
.cl-hero-info { flex:1; min-width:0; }
.cl-name {
    font-size:22px; font-weight:900; color:#fff;
    letter-spacing:-.4px; margin:0 0 8px;
}
.cl-contacts {
    display:flex; flex-wrap:wrap; gap:14px;
    font-size:12.5px; color:rgba(255,255,255,.65);
}
.cl-contact { display:flex; align-items:center; gap:5px; }
.top-badge {
    display:inline-flex; align-items:center; gap:5px;
    background:linear-gradient(135deg,#fef3c7,#fde68a);
    color:#92400e; border:1px solid #fcd34d;
    font-size:11px; font-weight:700;
    padding:4px 12px; border-radius:20px; margin-top:10px;
}

/* ── KPI FLOTTANTS ── */
.cl-kpi-row {
    display:flex; gap:14px;
    padding:0 28px; margin-top:-52px;
    position:relative; z-index:2; flex-wrap:wrap;
}
.cl-kpi {
    flex:1; min-width:160px;
    background:#fff; border-radius:14px;
    box-shadow:0 4px 20px rgba(0,0,0,.1);
    padding:16px 20px;
    display:flex; align-items:center; gap:14px;
}
.cl-kpi-ico {
    width:46px; height:46px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    font-size:20px; flex-shrink:0;
}
.cl-kpi-lbl {
    font-size:10.5px; font-weight:700; color:var(--muted);
    text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px;
}
.cl-kpi-val {
    font-size:16px; font-weight:900; color:var(--text);
    font-family:var(--mono); line-height:1.1; word-break:break-all;
}
.cl-kpi-dev {
    display:inline-block; margin-top:3px;
    font-size:10px; font-weight:700; color:var(--muted);
    background:var(--bg); border-radius:4px;
    padding:1px 6px; letter-spacing:.3px;
}

/* ── BODY ── */
.cl-body { padding:28px 28px 60px; }

/* ── CARTE COMMANDES ── */
.orders-card {
    background:#fff; border-radius:var(--r);
    box-shadow:0 2px 12px rgba(0,0,0,.06); overflow:hidden;
}
.card-hd {
    padding:16px 22px; border-bottom:1px solid var(--border);
    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;
}
.card-title { font-size:14px; font-weight:800; color:var(--text); }
.card-sub   { font-size:11.5px; color:var(--muted); }

/* Table */
.tbl { width:100%; border-collapse:collapse; font-size:13px; }
.tbl th {
    padding:11px 18px; text-align:left;
    font-size:10px; font-weight:700; color:var(--muted);
    text-transform:uppercase; letter-spacing:.6px;
    background:#f8fafc; border-bottom:1.5px solid var(--border);
    white-space:nowrap;
}
.tbl th.right, .tbl td.right { text-align:right; }
.tbl td { padding:14px 18px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.tbl tbody tr:last-child td { border-bottom:none; }
.tbl tbody tr:hover td { background:#fafcff; }

/* Ref */
.oid {
    font-family:var(--mono); font-size:12px; font-weight:700;
    color:var(--brand-dk); background:var(--brand-lt);
    padding:3px 8px; border-radius:6px;
}

/* Produits */
.prod-line { font-size:12.5px; font-weight:600; color:var(--text); line-height:1.4; }
.prod-qty  { color:var(--muted); font-weight:400; }
.prod-more { font-size:11px; color:var(--muted); margin-top:2px; }

/* Montant */
.amt-num {
    font-family:var(--mono); font-size:14px; font-weight:800;
    color:var(--text); white-space:nowrap;
}
.amt-dev {
    font-size:10px; font-weight:700; color:var(--muted);
    background:var(--bg); border-radius:4px;
    padding:1px 5px; letter-spacing:.3px; margin-top:3px;
    display:inline-block;
}

/* Pills statut */
.pill { display:inline-block; font-size:10.5px; font-weight:700; padding:4px 10px; border-radius:20px; white-space:nowrap; }
.p-success { background:#dcfce7; color:#15803d; }
.p-warning { background:#fef3c7; color:#92400e; }
.p-info    { background:#dbeafe; color:#1e40af; }
.p-danger  { background:#fee2e2; color:#991b1b; }
.p-muted   { background:#f1f5f9; color:#64748b; }

/* Date */
.dt-main { font-size:12.5px; font-weight:600; color:var(--text); }
.dt-hour { font-size:11px; color:var(--muted); margin-top:2px; }

/* Vide */
.tbl-empty { padding:40px; text-align:center; font-size:13px; color:var(--muted); }

/* Pagination */
.pagination-wrap { display:flex; justify-content:center; padding:16px; }

/* ── RESPONSIVE ── */

/* Tablette large */
@media(max-width:1024px) {
    .cl-kpi-val { font-size:15px; }
    .tbl th, .tbl td { padding:12px 14px; }
}

/* Tablette */
@media(max-width:768px) {
    .cl-hero    { padding:18px 16px 76px; }
    .cl-kpi-row { padding:0 16px; gap:10px; }
    .cl-kpi     { flex:0 0 calc(50% - 5px); min-width:0; }
    .cl-body    { padding:20px 16px 40px; }
    .tbl th, .tbl td { padding:11px 12px; font-size:12px; }
    .card-hd    { padding:12px 16px; }
}

/* Mobile */
@media(max-width:480px) {
    .cl-hero        { padding:14px 12px 70px; }
    .cl-hero-body   { gap:14px; }
    .cl-avatar      { width:56px; height:56px; font-size:18px; }
    .cl-name        { font-size:17px; }
    .cl-contacts    { font-size:11.5px; gap:6px; }
    .cl-back        { font-size:12px; padding:7px 12px; }

    .cl-kpi-row { padding:0 12px; gap:8px; margin-top:-44px; }
    .cl-kpi     { flex:0 0 calc(50% - 4px); min-width:0; padding:12px 14px; gap:10px; }
    .cl-kpi-ico { width:38px; height:38px; font-size:16px; border-radius:10px; }
    .cl-kpi-val { font-size:14px; }
    .cl-kpi-lbl { font-size:10px; }

    .cl-body    { padding:14px 0 40px; }

    /* La carte commandes prend toute la largeur */
    .orders-card { border-radius:0; box-shadow:none; }
    .card-hd     { padding:12px 16px; }

    /* Table → liste de cartes verticales */
    .tbl, .tbl tbody { display:block; width:100%; }
    .tbl thead { display:none; }

    .tbl tbody tr {
        display:block;
        padding:12px 16px;
        border-bottom:1px solid var(--border);
    }
    .tbl tbody tr:last-child { border-bottom:none; }
    .tbl tbody tr:hover { background:#fafcff; }

    .tbl td {
        display:flex; align-items:flex-start;
        justify-content:space-between;
        padding:5px 0; border:none; font-size:12.5px;
    }
    /* Labels auto générés par nth-child */
    .tbl td::before {
        font-size:10px; font-weight:700; color:var(--muted);
        text-transform:uppercase; letter-spacing:.4px;
        flex-shrink:0; margin-right:10px;
        padding-top:3px; min-width:62px;
    }
    .tbl td:nth-child(1)::before { content:'Réf'; }
    .tbl td:nth-child(2)::before { content:'Produits'; }
    .tbl td:nth-child(3)::before { content:'Livraison'; }
    .tbl td:nth-child(4)::before { content:'Montant'; }
    .tbl td:nth-child(5)::before { content:'Statut'; }
    .tbl td:nth-child(6)::before { content:'Date'; }
    .tbl td.right { text-align:right; }

    .amt-num  { font-size:13px; }
    .oid      { font-size:11px; }
}

/* Petit mobile */
@media(max-width:360px) {
    .cl-avatar  { width:48px; height:48px; font-size:15px; }
    .cl-name    { font-size:15px; }
    .cl-contacts { font-size:11px; }
    .cl-kpi     { flex:0 0 100%; }
    .cl-kpi-val { font-size:13px; }
    .cl-kpi-ico { width:34px; height:34px; font-size:14px; }
}
</style>
@endpush

@section('content')
@php
    $fmt = fn($n) => number_format($n ?? 0, 0, ',', ' ');
    $nameParts = explode(' ', $user->name ?? 'C L');
    $initials  = strtoupper(substr($nameParts[0],0,1)) . strtoupper(substr($nameParts[1] ?? 'X',0,1));
    $panierMoyen = ($stats->nb_commandes > 0)
        ? round($stats->total_depense / $stats->nb_commandes)
        : 0;
    $statusMap = [
        'livrée'       => ['label'=>'Livré',        'cls'=>'p-success'],
        'pending'      => ['label'=>'En attente',   'cls'=>'p-warning'],
        'en attente'   => ['label'=>'En attente',   'cls'=>'p-warning'],
        'confirmée'    => ['label'=>'Confirmée',    'cls'=>'p-info'],
        'en_livraison' => ['label'=>'En livraison', 'cls'=>'p-info'],
        'annulée'      => ['label'=>'Annulée',      'cls'=>'p-danger'],
    ];
@endphp

{{-- ═══════════ HERO ═══════════ --}}
<div class="cl-hero">
    <a href="{{ route('boutique.clients.index') }}" class="cl-back">← Retour aux clients</a>

    <div class="cl-hero-body">
        <div class="cl-avatar">{{ $initials }}</div>
        <div class="cl-hero-info">
            <h1 class="cl-name">{{ $user->name }}</h1>
            <div class="cl-contacts">
                @if($user->email)
                    <span class="cl-contact">✉️ {{ $user->email }}</span>
                @endif
                @php $bestPhone = $latestPhone ?: $user->phone; @endphp
                @if($bestPhone)
                    <a href="tel:{{ $bestPhone }}" class="cl-contact" style="color:rgba(255,255,255,.65);text-decoration:none">📞 {{ $bestPhone }}</a>
                @endif
                @php $bestAddress = $latestAddress ?: $user->address; @endphp
                @if($bestAddress)
                    <span class="cl-contact">📍 {{ $bestAddress }}</span>
                @endif
                <span class="cl-contact">
                    🗓️ Client depuis {{ \Carbon\Carbon::parse($stats->premiere_cmd ?? now())->translatedFormat('M Y') }}
                </span>
            </div>
            @if($isTop)
            <div class="top-badge">🏆 Top client ce mois</div>
            @endif
        </div>
    </div>
</div>

{{-- ═══════════ KPI FLOTTANTS ═══════════ --}}
<div class="cl-kpi-row">

    {{-- Total dépensé --}}
    <div class="cl-kpi">
        <div class="cl-kpi-ico" style="background:#eef2ff;">💰</div>
        <div>
            <div class="cl-kpi-lbl">Total dépensé</div>
            <div class="cl-kpi-val">{{ $fmt($stats->total_depense ?? 0) }}</div>
            <span class="cl-kpi-dev">{{ $devise }}</span>
        </div>
    </div>

    {{-- Nb commandes --}}
    <div class="cl-kpi">
        <div class="cl-kpi-ico" style="background:#eff6ff;">📦</div>
        <div>
            <div class="cl-kpi-lbl">Commandes</div>
            <div class="cl-kpi-val">{{ $stats->nb_commandes ?? 0 }}</div>
            <span class="cl-kpi-dev">au total</span>
        </div>
    </div>

    {{-- Panier moyen --}}
    <div class="cl-kpi">
        <div class="cl-kpi-ico" style="background:#fef3c7;">🛒</div>
        <div>
            <div class="cl-kpi-lbl">Panier moyen</div>
            <div class="cl-kpi-val">{{ $fmt($panierMoyen) }}</div>
            <span class="cl-kpi-dev">{{ $devise }}</span>
        </div>
    </div>

    {{-- Dernière commande --}}
    <div class="cl-kpi">
        <div class="cl-kpi-ico" style="background:#f3e8ff;">🕐</div>
        <div>
            <div class="cl-kpi-lbl">Dernière commande</div>
            <div class="cl-kpi-val" style="font-size:13px;letter-spacing:0;line-height:1.3;">
                {{ $stats->derniere_cmd ? \Carbon\Carbon::parse($stats->derniere_cmd)->diffForHumans() : '—' }}
            </div>
        </div>
    </div>

</div>

{{-- ═══════════ HISTORIQUE ═══════════ --}}
<div class="cl-body">
    <div class="orders-card">
        <div class="card-hd">
            <span class="card-title">📋 Historique des commandes</span>
            <span class="card-sub">{{ $stats->nb_commandes ?? 0 }} commande(s) · {{ $shop->name }}</span>
        </div>

        @if($commandes->isEmpty())
            <div class="tbl-empty">Aucune commande trouvée pour ce client.</div>
        @else
        <table class="tbl">
            <thead>
                <tr>
                    <th>Réf</th>
                    <th>Produits</th>
                    <th>Livraison</th>
                    <th class="right">Montant</th>
                    <th>Statut</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($commandes as $order)
                @php $st = $statusMap[$order->status] ?? ['label'=>ucfirst($order->status),'cls'=>'p-muted']; @endphp
                <tr>
                    {{-- Référence --}}
                    <td><span class="oid">#{{ $order->id }}</span></td>

                    {{-- Produits --}}
                    <td>
                        @if($order->items && $order->items->count() > 0)
                            @foreach($order->items->take(2) as $item)
                            <div class="prod-line">
                                {{ $item->product->name ?? 'Produit supprimé' }}
                                <span class="prod-qty">×{{ $item->quantity }}</span>
                            </div>
                            @endforeach
                            @if($order->items->count() > 2)
                            <div class="prod-more">+{{ $order->items->count()-2 }} autre(s)</div>
                            @endif
                        @else
                            <span style="color:var(--muted)">—</span>
                        @endif
                    </td>

                    {{-- Livraison --}}
                    <td>
                        @if($order->delivery_destination)
                            <div style="font-size:12px;color:var(--text);font-weight:600;max-width:180px;line-height:1.3">📍 {{ $order->delivery_destination }}</div>
                        @endif
                        @if($order->client_phone)
                            <a href="tel:{{ $order->client_phone }}" style="font-size:11.5px;color:var(--muted);text-decoration:none;display:block;margin-top:3px">📞 {{ $order->client_phone }}</a>
                        @endif
                        @if(!$order->delivery_destination && !$order->client_phone)
                            <span style="color:var(--muted);font-size:12px">—</span>
                        @endif
                    </td>

                    {{-- Montant --}}
                    <td class="right">
                        <div class="amt-num">{{ $fmt($order->total) }}</div>
                        <span class="amt-dev">{{ $devise }}</span>
                    </td>

                    {{-- Statut --}}
                    <td><span class="pill {{ $st['cls'] }}">{{ $st['label'] }}</span></td>

                    {{-- Date --}}
                    <td>
                        <div class="dt-main">{{ $order->created_at->format('d/m/Y') }}</div>
                        <div class="dt-hour">{{ $order->created_at->format('H:i') }}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($commandes->hasPages())
        <div class="pagination-wrap">{{ $commandes->links() }}</div>
        @endif
        @endif
    </div>
</div>

@endsection
