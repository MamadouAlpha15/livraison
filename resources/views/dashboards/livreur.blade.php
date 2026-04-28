@extends('layouts.app')
@section('title', 'Mon tableau de bord')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
:root {
    --navy:#0f172a; --navy2:#1e1b4b; --brand:#6366f1; --brand-dk:#4f46e5;
    --orange:#f90; --orange-dk:#e47911;
    --green:#10b981; --green-lt:#d1fae5; --yellow:#f59e0b; --yellow-lt:#fef3c7;
    --red:#ef4444; --red-lt:#fee2e2; --blue:#3b82f6; --blue-lt:#dbeafe;
    --purple:#8b5cf6; --purple-lt:#ede9fe;
    --border:#e9edef; --muted:#64748b; --text:#0f172a; --bg:#f1f5f9;
    --font:'Segoe UI',sans-serif;
}
*,*::before,*::after{box-sizing:border-box}
body{margin:0;font-family:var(--font);background:var(--bg);color:var(--text)}

/* ─── HERO ─── */
.lv-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy2) 60%, #1a4a7a 100%);
    padding: 28px 24px 80px;
    position: relative;
    overflow: hidden;
}
.lv-hero::before {
    content:'';position:absolute;inset:0;
    background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Ccircle cx='30' cy='30' r='1.5' fill='%23ffffff' opacity='.06'/%3E%3C/svg%3E");
}
.lv-hero-top { display:flex; align-items:center; justify-content:space-between; position:relative; }
.lv-hero-user { display:flex; align-items:center; gap:14px; }
.lv-hero-av {
    width:52px; height:52px; border-radius:50%;
    background:linear-gradient(135deg,var(--orange),var(--orange-dk));
    color:#fff; font-size:18px; font-weight:800;
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 4px 16px rgba(255,153,0,.4); flex-shrink:0;
}
.lv-hero-name { font-size:16px; font-weight:800; color:#fff; }
.lv-hero-shop { font-size:12px; color:rgba(255,255,255,.6); margin-top:2px; }
.lv-toggle-form { position:relative; }
.lv-toggle-btn {
    display:flex; align-items:center; gap:8px;
    padding:10px 18px; border-radius:12px; border:none; cursor:pointer;
    font-size:13px; font-weight:700; font-family:var(--font);
    transition:all .2s;
}
.lv-toggle-btn.online  { background:#d1fae5; color:#065f46; }
.lv-toggle-btn.offline { background:rgba(255,255,255,.12); color:#fff; border:1.5px solid rgba(255,255,255,.25); }
.lv-toggle-btn:hover { transform:scale(1.04); }
.lv-hero-title { font-size:22px; font-weight:900; color:#fff; margin-top:18px; position:relative; }
.lv-hero-sub { font-size:13px; color:rgba(255,255,255,.55); margin-top:4px; position:relative; }

/* ─── KPI CARDS flottantes ─── */
.lv-kpi-wrap {
    display:grid; grid-template-columns:repeat(4,1fr); gap:14px;
    padding:0 24px; margin-top:-48px; position:relative; z-index:2;
}
.lv-kpi {
    background:#fff; border-radius:16px; padding:16px;
    box-shadow:0 4px 20px rgba(0,0,0,.08);
    display:flex; flex-direction:column; gap:6px;
    border-top:3px solid transparent;
    transition:transform .15s, box-shadow .15s;
}
.lv-kpi:hover { transform:translateY(-3px); box-shadow:0 8px 28px rgba(0,0,0,.12); }
.lv-kpi.blue   { border-top-color:var(--blue); }
.lv-kpi.orange { border-top-color:var(--orange); }
.lv-kpi.green  { border-top-color:var(--green); }
.lv-kpi.purple { border-top-color:var(--purple); }
.lv-kpi-ico { font-size:22px; }
.lv-kpi-val { font-size:26px; font-weight:900; color:var(--text); line-height:1; }
.lv-kpi-lbl { font-size:11.5px; font-weight:600; color:var(--muted); }

/* ─── LAYOUT ─── */
.lv-body { padding:24px; display:grid; grid-template-columns:1fr 340px; gap:20px; }

/* ─── CARD ─── */
.lv-card {
    background:#fff; border-radius:16px;
    box-shadow:0 2px 12px rgba(0,0,0,.06);
    overflow:hidden;
}
.lv-card-hd {
    padding:16px 20px; display:flex; align-items:center;
    justify-content:space-between; border-bottom:1px solid var(--border);
}
.lv-card-title { font-size:14px; font-weight:800; color:var(--text); }
.lv-card-link { font-size:12px; font-weight:700; color:var(--brand); text-decoration:none; }
.lv-card-link:hover { text-decoration:underline; }

/* ─── COMMANDES RECENTES ─── */
.lv-order-row {
    display:flex; align-items:center; gap:12px;
    padding:12px 20px; border-bottom:1px solid #f8fafc;
    transition:background .12s;
}
.lv-order-row:last-child { border-bottom:none; }
.lv-order-row:hover { background:#f8fafc; }
.lv-order-num { font-size:13px; font-weight:700; color:var(--text); }
.lv-order-client { font-size:11.5px; color:var(--muted); margin-top:2px; }
.lv-order-amount { font-size:13px; font-weight:800; color:var(--navy); font-family:monospace; margin-left:auto; }
.lv-order-status {
    font-size:10.5px; font-weight:700; padding:3px 10px;
    border-radius:20px; white-space:nowrap; flex-shrink:0;
}
.s-delivering { background:#dbeafe; color:#1d4ed8; }
.s-delivered  { background:#d1fae5; color:#065f46; }
.s-ready      { background:#fef3c7; color:#92400e; }
.s-other      { background:#f3f4f6; color:#6b7280; }
.lv-order-ico { width:36px; height:36px; border-radius:10px; background:var(--bg); display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; }

/* ─── ACTIONS RAPIDES ─── */
.lv-actions { display:flex; flex-direction:column; gap:10px; padding:16px; }
.lv-action-btn {
    display:flex; align-items:center; gap:14px;
    padding:14px 16px; border-radius:12px; text-decoration:none;
    transition:all .15s; border:1.5px solid transparent;
}
.lv-action-btn:hover { transform:translateX(4px); }
.lv-action-btn .action-ico { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
.lv-action-btn .action-lbl { font-size:13.5px; font-weight:700; }
.lv-action-btn .action-sub { font-size:11.5px; margin-top:1px; }
.ab-orange { background:#fff8f0; border-color:#fed7aa; }
.ab-orange .action-ico { background:#fff3e0; }
.ab-orange .action-lbl { color:#c2410c; }
.ab-orange .action-sub { color:#ea580c; }
.ab-orange:hover { background:#fff3e0; border-color:var(--orange); }
.ab-blue { background:#eff6ff; border-color:#bfdbfe; }
.ab-blue .action-ico { background:#dbeafe; }
.ab-blue .action-lbl { color:#1d4ed8; }
.ab-blue .action-sub { color:#3b82f6; }
.ab-blue:hover { background:#dbeafe; border-color:var(--blue); }
.ab-green { background:#f0fdf4; border-color:#bbf7d0; }
.ab-green .action-ico { background:#d1fae5; }
.ab-green .action-lbl { color:#065f46; }
.ab-green .action-sub { color:#10b981; }
.ab-green:hover { background:#d1fae5; border-color:var(--green); }
.ab-purple { background:#faf5ff; border-color:#ddd6fe; }
.ab-purple .action-ico { background:#ede9fe; }
.ab-purple .action-lbl { color:#5b21b6; }
.ab-purple .action-sub { color:#8b5cf6; }
.ab-purple:hover { background:#ede9fe; border-color:var(--purple); }

/* ─── STAT COMMISSION ─── */
.lv-commission-hero {
    background:linear-gradient(135deg,#1e1b4b,#3730a3);
    padding:20px; text-align:center;
}
.lv-commission-hero .amount { font-size:28px; font-weight:900; color:#fff; font-family:monospace; }
.lv-commission-hero .label  { font-size:12px; color:rgba(255,255,255,.7); margin-top:4px; }

/* ─── EMPTY ─── */
.lv-empty { padding:32px; text-align:center; color:var(--muted); font-size:13px; }
.lv-empty-ico { font-size:36px; display:block; margin-bottom:10px; opacity:.4; }

/* ─── RESPONSIVE ─── */
@media(max-width:1024px) {
    .lv-kpi-wrap { grid-template-columns:repeat(2,1fr); margin-top:-36px; }
    .lv-body { grid-template-columns:1fr; }
}
@media(max-width:640px) {
    .lv-hero { padding:20px 16px 70px; }
    .lv-hero-name { font-size:14px; }
    .lv-hero-title { font-size:18px; }
    .lv-kpi-wrap { grid-template-columns:repeat(2,1fr); gap:10px; padding:0 16px; margin-top:-40px; }
    .lv-kpi-val { font-size:22px; }
    .lv-body { padding:16px; gap:14px; }
    .lv-toggle-btn { padding:8px 13px; font-size:12px; }
}
@media(max-width:400px) {
    .lv-kpi-wrap { grid-template-columns:repeat(2,1fr); gap:8px; padding:0 12px; }
    .lv-kpi { padding:12px; }
}
</style>
@endpush

@section('content')
@php
    $parts   = explode(' ', $livreur->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
    $fmt = fn($n) => number_format($n ?? 0, 0, ',', ' ') . ' ' . $devise;

    $statusMap = [
        'en_attente'   => ['label'=>'En attente',   'cls'=>'s-ready'],
        'confirmée'    => ['label'=>'À récupérer',  'cls'=>'s-ready'],
        'confirmed'    => ['label'=>'À récupérer',  'cls'=>'s-ready'],
        'delivering'   => ['label'=>'En livraison', 'cls'=>'s-delivering'],
        'en_livraison' => ['label'=>'En livraison', 'cls'=>'s-delivering'],
        'shipped'      => ['label'=>'Expédiée',     'cls'=>'s-delivering'],
        'delivered'    => ['label'=>'Livrée',        'cls'=>'s-delivered'],
        'livrée'       => ['label'=>'Livrée',        'cls'=>'s-delivered'],
        'completed'    => ['label'=>'Terminée',      'cls'=>'s-delivered'],
        'ready'        => ['label'=>'Prête',          'cls'=>'s-ready'],
        'prête'        => ['label'=>'Prête',          'cls'=>'s-ready'],
        'assigned'     => ['label'=>'Assignée',      'cls'=>'s-ready'],
        'annulée'      => ['label'=>'Annulée',       'cls'=>'s-other'],
    ];
@endphp

{{-- ═══ HERO ═══ --}}
<div class="lv-hero">
    <div class="lv-hero-top">
        <div class="lv-hero-user">
            <div class="lv-hero-av">{{ $initials }}</div>
            <div>
                <div class="lv-hero-name">{{ $livreur->name }}</div>
                <div class="lv-hero-shop">{{ $shop?->name ?? 'Boutique' }} · {{ $devise }}</div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <form action="{{ route('livreur.availability.toggle') }}" method="POST">
                @csrf @method('PUT')
                <button type="submit" class="lv-toggle-btn {{ $livreur->is_available ? 'online' : 'offline' }}">
                    @if($livreur->is_available) 🟢 En ligne @else ⚫ Hors ligne @endif
                </button>
            </form>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="lv-toggle-btn" style="background:rgba(239,68,68,.15);color:#fca5a5;border:1.5px solid rgba(239,68,68,.3);">
                    🚪 Déconnexion
                </button>
            </form>
        </div>
    </div>
    <div class="lv-hero-title">Tableau de bord Livreur 🚴</div>
    <div class="lv-hero-sub">Bonjour {{ explode(' ', $livreur->name)[0] }} — voici votre activité du jour</div>
</div>

{{-- ═══ KPI ═══ --}}
<div class="lv-kpi-wrap">
    <div class="lv-kpi blue">
        <span class="lv-kpi-ico">📦</span>
        <div class="lv-kpi-val">{{ $totalAssigned }}</div>
        <div class="lv-kpi-lbl">Total assignées</div>
    </div>
    <div class="lv-kpi orange">
        <span class="lv-kpi-ico">🚴</span>
        <div class="lv-kpi-val">{{ $enCours }}</div>
        <div class="lv-kpi-lbl">En cours</div>
    </div>
    <div class="lv-kpi green">
        <span class="lv-kpi-ico">✅</span>
        <div class="lv-kpi-val">{{ $terminees }}</div>
        <div class="lv-kpi-lbl">Livrées</div>
    </div>
    <div class="lv-kpi purple">
        <span class="lv-kpi-ico">⏳</span>
        <div class="lv-kpi-val">{{ $enAttente }}</div>
        <div class="lv-kpi-lbl">En attente</div>
    </div>
</div>

{{-- ═══ BODY ═══ --}}
<div class="lv-body">

    {{-- Commandes récentes --}}
    <div class="lv-card">
        <div class="lv-card-hd">
            <span class="lv-card-title">🕐 Commandes récentes</span>
            <a href="{{ route('livreur.orders.index') }}" class="lv-card-link">Voir toutes →</a>
        </div>
        @if($recentOrders->isEmpty())
        <div class="lv-empty">
            <span class="lv-empty-ico">📭</span>
            Aucune commande assignée pour le moment.
        </div>
        @else
        @foreach($recentOrders as $order)
        @php
            $st = $statusMap[$order->status] ?? ['label'=>ucfirst($order->status),'cls'=>'s-other'];
        @endphp
        <div class="lv-order-row">
            <div class="lv-order-ico">📦</div>
            <div style="flex:1;min-width:0">
                <div class="lv-order-num">Commande #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div class="lv-order-client">{{ $order->client?->name ?? $order->user?->name ?? 'Client' }}</div>
            </div>
            <div class="lv-order-amount">{{ $fmt($order->total) }}</div>
            <span class="lv-order-status {{ $st['cls'] }}">{{ $st['label'] }}</span>
        </div>
        @endforeach
        @endif
    </div>

    {{-- Colonne droite --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Commission --}}
        <div class="lv-card">
            <div class="lv-commission-hero">
                <div class="amount">{{ $fmt($totalCommission) }}</div>
                <div class="label">💸 Total commissions gagnées</div>
            </div>
            <div style="padding:14px 16px">
                <a href="{{ route('livreur.commissions.index') }}" class="lv-action-btn ab-green" style="border-radius:10px;">
                    <div class="action-ico">💰</div>
                    <div>
                        <div class="action-lbl">Voir mes commissions</div>
                        <div class="action-sub">Historique détaillé</div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Actions rapides --}}
        <div class="lv-card">
            <div class="lv-card-hd">
                <span class="lv-card-title">⚡ Actions rapides</span>
            </div>
            <div class="lv-actions">
                <a href="{{ route('livreur.orders.index') }}" class="lv-action-btn ab-orange">
                    <div class="action-ico">📦</div>
                    <div>
                        <div class="action-lbl">Toutes mes livraisons</div>
                        <div class="action-sub">{{ $totalAssigned }} commande(s) assignée(s)</div>
                    </div>
                </a>
                <a href="{{ route('livreur.orders.index', ['status'=>'delivering']) }}" class="lv-action-btn ab-blue">
                    <div class="action-ico">🚴</div>
                    <div>
                        <div class="action-lbl">En cours de livraison</div>
                        <div class="action-sub">{{ $enCours }} en route</div>
                    </div>
                </a>
                <a href="{{ route('livreur.orders.index', ['status'=>'delivered']) }}" class="lv-action-btn ab-green">
                    <div class="action-ico">✅</div>
                    <div>
                        <div class="action-lbl">Historique livrées</div>
                        <div class="action-sub">{{ $terminees }} livraison(s) terminée(s)</div>
                    </div>
                </a>
                <a href="{{ route('livreur.commissions.index') }}" class="lv-action-btn ab-purple">
                    <div class="action-ico">💸</div>
                    <div>
                        <div class="action-lbl">Mes commissions</div>
                        <div class="action-sub">{{ $fmt($totalCommission) }} au total</div>
                    </div>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
