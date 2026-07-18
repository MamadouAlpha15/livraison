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

/* ─── NOTIFICATIONS PUSH ─── */
.lv-notif-card {
    display:flex; align-items:center; gap:14px;
    background:#fff; border-radius:16px;
    padding:16px 20px; margin:16px 24px;
    box-shadow:0 2px 12px rgba(0,0,0,.06);
    border-left:4px solid var(--blue);
}
.lv-notif-ico {
    width:44px; height:44px; border-radius:12px; flex-shrink:0;
    background:var(--blue-lt); display:flex; align-items:center; justify-content:center;
    font-size:21px;
}
.lv-notif-title { font-weight:800; font-size:14px; color:var(--text); }
.lv-notif-sub { font-size:12.5px; color:var(--muted); margin-top:2px; line-height:1.4; }
.lv-notif-btn {
    background:linear-gradient(135deg,var(--green),#059669); color:#fff; border:none;
    border-radius:10px; padding:10px 18px; font-size:13px; font-weight:700; font-family:var(--font);
    cursor:pointer; white-space:nowrap; box-shadow:0 3px 10px rgba(16,185,129,.3);
    transition:transform .15s, opacity .15s;
}
.lv-notif-btn:hover { transform:scale(1.03); }
@media(max-width:640px) {
    .lv-notif-card { margin:14px 16px; padding:14px 16px; flex-wrap:wrap; }
    .lv-notif-btn { width:100%; }
}

/* ─── STAT COMMISSION ─── */
.lv-commission-hero {
    background:linear-gradient(135deg,#1e1b4b,#3730a3);
    padding:20px; text-align:center;
}
.lv-commission-hero .amount { font-size:28px; font-weight:900; color:#fff; font-family:monospace; }
.lv-commission-hero .label  { font-size:12px; color:rgba(255,255,255,.7); margin-top:4px; }

/* ─── OBJECTIF DU JOUR (gamification) ─── */
.lv-goal-card {
    background:linear-gradient(135deg,#f59e0b,#fbbf24);
    border-radius:16px; padding:18px 20px; margin:0 24px 20px;
    box-shadow:0 4px 20px rgba(245,158,11,.25);
}
.lv-goal-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
.lv-goal-title { font-size:14px; font-weight:800; color:#78350f; display:flex; align-items:center; gap:6px; }
.lv-goal-count { font-size:13px; font-weight:700; color:#78350f; font-family:monospace; }
.lv-goal-bar-wrap { height:10px; background:rgba(120,53,15,.15); border-radius:20px; overflow:hidden; margin-bottom:8px; }
.lv-goal-bar { height:100%; background:#78350f; border-radius:20px; transition:width .5s ease; }
.lv-goal-sub { font-size:12px; color:#92400e; font-weight:600; }
.lv-goal-sub.reached { color:#065f46; }

/* ─── CLASSEMENT (gamification) ─── */
.lv-rank-row {
    display:flex; align-items:center; gap:10px;
    padding:10px 16px; border-bottom:1px solid #f8fafc;
}
.lv-rank-row:last-child { border-bottom:none; }
.lv-rank-row.me { background:#eef2ff; }
.lv-rank-pos { width:22px; text-align:center; font-weight:900; font-size:13px; color:var(--muted); flex-shrink:0; }
.lv-rank-pos.gold   { color:#f59e0b; }
.lv-rank-pos.silver { color:#94a3b8; }
.lv-rank-pos.bronze { color:#cd7c3c; }
.lv-rank-name { flex:1; min-width:0; font-size:12.5px; font-weight:700; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.lv-rank-row.me .lv-rank-name { color:var(--brand); }
.lv-rank-count { font-size:12px; font-weight:800; color:var(--muted); font-family:monospace; flex-shrink:0; }

/* ─── EMPTY ─── */
.lv-empty { padding:32px; text-align:center; color:var(--muted); font-size:13px; }
.lv-empty-ico { font-size:36px; display:block; margin-bottom:10px; opacity:.4; }

/* ─── PULSE INDICATOR ─── */
@keyframes lvpulse {
    0%,100% { opacity:1; transform:scale(1); }
    50%      { opacity:.4; transform:scale(1.4); }
}

/* ─── CLICKABLE ROWS ─── */
.lv-order-row { cursor:pointer; }

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

{{-- Notifications push — activation manuelle si la bannière automatique n'est pas apparue --}}
<div id="notifSettingsCard" class="lv-notif-card">
    <div class="lv-notif-ico">🔔</div>
    <div style="flex:1;min-width:160px">
        <div class="lv-notif-title">Notifications push</div>
        <div id="notifSettingsSub" class="lv-notif-sub">Recevez vos commandes en temps réel, même application fermée.</div>
    </div>
    <button type="button" id="notifSettingsBtn" class="lv-notif-btn">
        Activer les notifications
    </button>
</div>
<script>
(function() {
    var card = document.getElementById('notifSettingsCard');
    var btn  = document.getElementById('notifSettingsBtn');
    var sub  = document.getElementById('notifSettingsSub');
    if (!card) return;

    if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) {
        card.style.display = 'none';
        return;
    }

    function refresh() {
        if (Notification.permission === 'granted' && localStorage.getItem('push_subscribed')) {
            sub.textContent = 'Notifications activées ✓';
            btn.textContent = 'Déjà activées';
            btn.disabled = true;
            btn.style.opacity = '.6';
            btn.style.cursor = 'default';
        } else if (Notification.permission === 'denied') {
            sub.textContent = 'Bloquées dans les réglages de votre navigateur — autorisez les notifications pour ce site pour les activer.';
            btn.style.display = 'none';
        }
    }

    btn.addEventListener('click', function() {
        btn.disabled = true;
        btn.textContent = 'Activation…';
        Promise.resolve(window.enablePushNotifications ? window.enablePushNotifications() : null)
            .finally(function() {
                btn.disabled = false;
                btn.textContent = 'Activer les notifications';
                refresh();
            });
    });

    refresh();
})();
</script>

{{-- ═══ KPI ═══ --}}
<div class="lv-kpi-wrap">
    <div class="lv-kpi blue">
        <span class="lv-kpi-ico">📦</span>
        <div class="lv-kpi-val" id="kpi-total">{{ $totalAssigned }}</div>
        <div class="lv-kpi-lbl">Total assignées</div>
    </div>
    <div class="lv-kpi orange">
        <span class="lv-kpi-ico">🚴</span>
        <div class="lv-kpi-val" id="kpi-encours">{{ $enCours }}</div>
        <div class="lv-kpi-lbl">En cours</div>
    </div>
    <div class="lv-kpi green">
        <span class="lv-kpi-ico">✅</span>
        <div class="lv-kpi-val" id="kpi-terminees">{{ $terminees }}</div>
        <div class="lv-kpi-lbl">Livrées</div>
    </div>
    <div class="lv-kpi purple">
        <span class="lv-kpi-ico">⏳</span>
        <div class="lv-kpi-val" id="kpi-attente">{{ $enAttente }}</div>
        <div class="lv-kpi-lbl">En attente</div>
    </div>
</div>

{{-- ═══ OBJECTIF DU JOUR (gamification) ═══ --}}
<div class="lv-goal-card">
    <div class="lv-goal-top">
        <div class="lv-goal-title">🎯 Objectif du jour</div>
        <div class="lv-goal-count" id="goal-count">{{ $dailyProgress['count'] }}/{{ $dailyProgress['goal'] }}</div>
    </div>
    <div class="lv-goal-bar-wrap">
        <div class="lv-goal-bar" id="goal-bar" style="width:{{ $dailyProgress['percent'] }}%"></div>
    </div>
    <div class="lv-goal-sub {{ $dailyProgress['rewarded'] ? 'reached' : '' }}" id="goal-sub">
        @if($dailyProgress['rewarded'])
            ✅ Prime de {{ number_format($dailyProgress['bonus'], 0, ',', ' ') }} débloquée aujourd'hui !
        @else
            Livrez {{ $dailyProgress['goal'] }} commandes aujourd'hui pour débloquer une prime de {{ number_format($dailyProgress['bonus'], 0, ',', ' ') }}
        @endif
    </div>
</div>

{{-- ═══ BODY ═══ --}}
<div class="lv-body">

    {{-- Commandes récentes --}}
    <div class="lv-card">
        <div class="lv-card-hd">
            <span class="lv-card-title">🕐 Commandes récentes</span>
            <span style="display:flex;align-items:center;gap:10px;">
                <span id="lv-pulse" style="width:7px;height:7px;border-radius:50%;background:#10b981;display:inline-block;animation:lvpulse 2s infinite;"></span>
                <a href="{{ route('livreur.orders.index') }}" class="lv-card-link">Voir toutes →</a>
            </span>
        </div>
        <div id="recent-orders-list">
        @if($recentOrders->isEmpty())
        <div class="lv-empty">
            <span class="lv-empty-ico">📭</span>
            Aucune commande assignée pour le moment.
        </div>
        @else
        @foreach($recentOrders as $group)
        @php
            $order  = $group['order'];
            $st     = $statusMap[$group['status']] ?? ['label'=>ucfirst($group['status']),'cls'=>'s-other'];
            $rowUrl = in_array($group['status'], ['delivering','en_livraison','shipped'])
                ? route('orders.nav', $order)
                : (in_array($group['status'], ['livrée','delivered','completed','annulée'])
                    ? route('livreur.orders.index', ['status'=>'delivered'])
                    : route('livreur.orders.index', ['status'=>'confirmed']));
        @endphp
        <a href="{{ $rowUrl }}" class="lv-order-row" style="text-decoration:none;color:inherit;display:flex;">
            <div class="lv-order-ico">📦</div>
            <div style="flex:1;min-width:0">
                @if($group['count'] > 1)
                    <div class="lv-order-num">{{ $group['count'] }} commandes · 1 trajet</div>
                @else
                    <div class="lv-order-num">Commande #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
                @endif
                <div class="lv-order-client">{{ $group['client']?->name ?? 'Client' }}</div>
            </div>
            <div class="lv-order-amount">{{ $fmt($group['total']) }}</div>
            <span class="lv-order-status {{ $st['cls'] }}">{{ $st['label'] }}</span>
        </a>
        @endforeach
        @endif
        </div>
    </div>

    {{-- Colonne droite --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Commission / Frais de livraison --}}
        <div class="lv-card">
            <div class="lv-commission-hero">
                <div class="amount">{{ $fmt($totalCommission) }}</div>
                @if($isCompanyDriver)
                    <div class="label">🚚 Frais de livraison collectés pour l'entreprise</div>
                @else
                    <div class="label">💸 Total commissions gagnées</div>
                @endif
            </div>
            <div style="padding:14px 16px">
                <a href="{{ route('livreur.commissions.index') }}" class="lv-action-btn ab-green" style="border-radius:10px;">
                    <div class="action-ico">💰</div>
                    <div>
                        <div class="action-lbl">{{ $isCompanyDriver ? 'Voir les frais de livraison' : 'Voir mes commissions' }}</div>
                        <div class="action-sub">Historique détaillé</div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Classement (gamification) --}}
        @if(count($leaderboard))
        <div class="lv-card">
            <div class="lv-card-hd">
                <span class="lv-card-title">🏆 Classement de la semaine</span>
            </div>
            <div>
                @foreach(array_slice($leaderboard, 0, 5) as $row)
                <div class="lv-rank-row {{ $row['is_me'] ? 'me' : '' }}">
                    <div class="lv-rank-pos {{ $row['rank']===1?'gold':($row['rank']===2?'silver':($row['rank']===3?'bronze':'')) }}">
                        {{ $row['rank']===1?'🥇':($row['rank']===2?'🥈':($row['rank']===3?'🥉':$row['rank'])) }}
                    </div>
                    <div class="lv-rank-name">{{ $row['is_me'] ? 'Vous' : $row['name'] }}</div>
                    <div class="lv-rank-count">{{ $row['count'] }} 📦</div>
                </div>
                @endforeach
                @php $myRow = collect($leaderboard)->firstWhere('is_me', true); @endphp
                @if($myRow && $myRow['rank'] > 5)
                <div class="lv-rank-row me" style="border-top:2px solid var(--border)">
                    <div class="lv-rank-pos">{{ $myRow['rank'] }}</div>
                    <div class="lv-rank-name">Vous</div>
                    <div class="lv-rank-count">{{ $myRow['count'] }} 📦</div>
                </div>
                @endif
            </div>
        </div>
        @endif

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

@push('scripts')
<script>
const DASH_DATA_URL  = '{{ route('livreur.dashboard.data') }}';
const NAV_BASE       = '{{ url('/orders') }}';
const ORDERS_URL     = '{{ route('livreur.orders.index') }}';

const STATUS_MAP = {
    'en_attente':   { label: 'En attente',   cls: 's-ready' },
    'confirmée':    { label: 'À récupérer',  cls: 's-ready' },
    'confirmed':    { label: 'À récupérer',  cls: 's-ready' },
    'delivering':   { label: 'En livraison', cls: 's-delivering' },
    'en_livraison': { label: 'En livraison', cls: 's-delivering' },
    'shipped':      { label: 'Expédiée',     cls: 's-delivering' },
    'delivered':    { label: 'Livrée',       cls: 's-delivered' },
    'livrée':       { label: 'Livrée',       cls: 's-delivered' },
    'completed':    { label: 'Terminée',     cls: 's-delivered' },
    'ready':        { label: 'Prête',        cls: 's-ready' },
    'prête':        { label: 'Prête',        cls: 's-ready' },
    'assigned':     { label: 'Assignée',     cls: 's-ready' },
    'annulée':      { label: 'Annulée',      cls: 's-other' },
};

function pad(id) { return '#' + String(id).padStart(5, '0'); }

function orderUrl(o) {
    const active  = ['delivering','en_livraison','shipped'];
    const done    = ['livrée','delivered','completed','annulée'];
    if (active.includes(o.status))  return `${NAV_BASE}/${o.order_id}/nav`;
    if (done.includes(o.status))    return `${ORDERS_URL}?status=delivered`;
    return `${ORDERS_URL}?status=confirmed`;
}

function renderOrders(orders) {
    if (!orders.length) {
        return `<div class="lv-empty"><span class="lv-empty-ico">📭</span>Aucune commande assignée pour le moment.</div>`;
    }
    return orders.map(o => {
        const st  = STATUS_MAP[o.status] ?? { label: o.status, cls: 's-other' };
        const lbl = o.count > 1 ? `${o.count} commandes · 1 trajet` : `Commande ${pad(o.order_id)}`;
        return `<a href="${orderUrl(o)}" class="lv-order-row" style="text-decoration:none;color:inherit;display:flex;">
            <div class="lv-order-ico">📦</div>
            <div style="flex:1;min-width:0">
                <div class="lv-order-num">${lbl}</div>
                <div class="lv-order-client">${o.client}</div>
            </div>
            <div class="lv-order-amount">${o.total}</div>
            <span class="lv-order-status ${st.cls}">${st.label}</span>
        </a>`;
    }).join('');
}

async function pollDashboard() {
    try {
        const r = await fetch(DASH_DATA_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!r.ok) return;
        const d = await r.json();

        document.getElementById('kpi-total').textContent    = d.totalAssigned;
        document.getElementById('kpi-encours').textContent  = d.enCours;
        document.getElementById('kpi-terminees').textContent = d.terminees;
        document.getElementById('kpi-attente').textContent  = d.enAttente;

        document.getElementById('recent-orders-list').innerHTML = renderOrders(d.recentOrders);

        if (d.dailyProgress) {
            const gp = d.dailyProgress;
            document.getElementById('goal-count').textContent = gp.count + '/' + gp.goal;
            document.getElementById('goal-bar').style.width = gp.percent + '%';
            const sub = document.getElementById('goal-sub');
            if (gp.rewarded) {
                sub.textContent = '✅ Prime de ' + new Intl.NumberFormat('fr').format(gp.bonus) + ' débloquée aujourd\'hui !';
                sub.classList.add('reached');
            } else {
                sub.textContent = 'Livrez ' + gp.goal + ' commandes aujourd\'hui pour débloquer une prime de ' + new Intl.NumberFormat('fr').format(gp.bonus);
                sub.classList.remove('reached');
            }
        }
    } catch (_) {}
}

setInterval(pollDashboard, 15000);
</script>
@endpush
