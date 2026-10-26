@extends('layouts.app')
@section('title', 'Mon tableau de bord')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
/* Tableau de bord livreur — en-tête bleu nuit, chiffres du jour, la prochaine course en premier */
.lv {
    --blue: #1F7CF6; --blue-2: #1668D8; --blue-3: #1260C9; --blue-lt: #EAF2FF; --blue-bd: #CFE1FF;
    --navy: #0B1E3D; --navy-2: #132B52; --amber: #F59E0B; --amber-lt: #FFF7E6;
    --green: #059669; --green-lt: #ECFDF5; --red: #B91C1C;
    --ink: #16162A; --ink-2: #45455E; --ink-3: #62627C; --line: #E3E8F2; --bg: #F6F9FF;
    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
    color: var(--ink); background: var(--bg); min-height: 100vh; padding-bottom: 48px;
}
.lv *, .lv *::before, .lv *::after { box-sizing: border-box; }
.lv-num { font-variant-numeric: tabular-nums; }

/* ── En-tête ── */
.lv-head {
    background: radial-gradient(110% 170% at 100% 0%, rgba(31,124,246,.45), transparent 60%), linear-gradient(135deg, var(--navy), var(--navy-2));
    color: #fff;
    padding: 22px max(16px, env(safe-area-inset-right, 0px)) 78px max(16px, env(safe-area-inset-left, 0px));
}
.lv-head-in { max-width: 1160px; margin: 0 auto; display: grid; gap: 18px; }
.lv-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.lv-user { display: flex; align-items: center; gap: 12px; min-width: 0; }
.lv-av { width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: var(--blue-2); font-weight: 800; font-size: 16px; box-shadow: 0 0 0 3px rgba(255,255,255,.14); }
.lv-name { font-weight: 800; font-size: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.lv-org { font-size: 12.5px; color: #C9D6EA; margin-top: 2px; }
.lv-top-actions { display: flex; align-items: center; gap: 8px; }
.lv-avail { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; padding: 0 16px; border-radius: 999px; border: none; cursor: pointer; font-family: inherit; font-size: 13.5px; font-weight: 800; transition: background-color .18s, color .18s; }
.lv-avail.is-on  { background: #D1FAE5; color: #065F46; }
.lv-avail.is-off { background: rgba(255,255,255,.12); color: #fff; }
.lv-avail.is-off:hover { background: rgba(255,255,255,.2); }
.lv-dot { width: 9px; height: 9px; border-radius: 50%; background: currentColor; }
.lv-avail.is-on .lv-dot { background: #10B981; box-shadow: 0 0 0 3px rgba(16,185,129,.25); }
.lv-logout { width: 40px; height: 40px; border-radius: 50%; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,.1); color: #fff; transition: background-color .18s; }
.lv-logout:hover { background: rgba(239,68,68,.3); }
.lv-hello h1 { margin: 0; font-size: clamp(22px, 4vw, 30px); font-weight: 800; letter-spacing: -.02em; text-wrap: balance; }
.lv-hello p { margin: 6px 0 0; font-size: 14px; color: #C9D6EA; }

/* ── Chiffres du jour ── */
.lv-main { max-width: 1160px; margin: -56px auto 0; padding: 0 16px; display: grid; gap: 18px; position: relative; }
.lv-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.lv-kpi { display: flex; align-items: center; gap: 12px; padding: 16px; background: #fff; border-radius: 20px; box-shadow: 0 0 0 1px var(--line), 0 14px 30px -24px rgba(11,30,61,.5); text-decoration: none; color: inherit; transition: transform .2s cubic-bezier(.22,1,.36,1), box-shadow .2s; min-width: 0; }
.lv-kpi:hover { transform: translateY(-2px); box-shadow: 0 0 0 1px var(--blue-bd), 0 18px 34px -22px rgba(11,30,61,.5); }
.lv-kpi-ico { width: 42px; height: 42px; border-radius: 14px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
.lv-kpi-ico.amber { background: var(--amber-lt); color: #B45309; }
.lv-kpi-ico.blue  { background: var(--blue-lt); color: var(--blue-2); }
.lv-kpi-ico.green { background: var(--green-lt); color: var(--green); }
.lv-kpi-ico.navy  { background: var(--navy); color: #fff; }
.lv-kpi-val { font-size: 22px; font-weight: 800; line-height: 1.1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.lv-kpi-lbl { font-size: 12.5px; color: var(--ink-3); font-weight: 600; margin-top: 2px; }

/* ── Grille ── */
.lv-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 18px; align-items: start; }
.lv-col { display: grid; gap: 18px; min-width: 0; }
.lv-card { background: #fff; border-radius: 22px; box-shadow: 0 0 0 1px var(--line); overflow: hidden; }
.lv-card-hd { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 16px 20px; border-bottom: 1px solid var(--line); }
.lv-card-title { margin: 0; font-size: 15px; font-weight: 800; display: flex; align-items: center; gap: 8px; }
.lv-link { display: inline-flex; align-items: center; gap: 4px; font-size: 13px; font-weight: 700; color: var(--blue-3); text-decoration: none; }
.lv-link:hover { text-decoration: underline; text-underline-offset: 3px; }

/* ── Prochaine course ── */
.lv-next { padding: 20px; display: grid; gap: 16px; }
.lv-next-top { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.lv-chip { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 999px; font-size: 12.5px; font-weight: 800; }
.lv-chip.pickup { background: var(--amber-lt); color: #92400E; }
.lv-chip.route  { background: var(--blue-lt); color: var(--blue-3); }
.lv-next-num { font-size: 13px; font-weight: 700; color: var(--ink-3); }
.lv-route { display: grid; gap: 0; }
.lv-stop { display: grid; grid-template-columns: 34px 1fr; gap: 12px; align-items: start; }
.lv-stop-ico { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: var(--bg); color: var(--ink-2); box-shadow: inset 0 0 0 1.5px var(--line); }
.lv-stop.is-current .lv-stop-ico { background: var(--blue-2); color: #fff; box-shadow: 0 0 0 4px rgba(31,124,246,.15); }
.lv-stop.is-done .lv-stop-ico { background: var(--green-lt); color: var(--green); box-shadow: none; }
.lv-stop-line { width: 2px; height: 18px; margin: 2px 0 2px 16px; background: var(--line); }
.lv-stop-lbl { font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: var(--ink-3); }
.lv-stop-val { font-size: 15px; font-weight: 700; margin-top: 2px; overflow-wrap: anywhere; }
.lv-stop-sub { font-size: 13px; color: var(--ink-2); margin-top: 2px; overflow-wrap: anywhere; }
.lv-facts { display: flex; flex-wrap: wrap; gap: 8px; }
.lv-fact { display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 12px; background: var(--bg); font-size: 13px; font-weight: 600; color: var(--ink-2); }
.lv-fact strong { color: var(--ink); }
.lv-fact.cash { background: var(--amber-lt); color: #92400E; }
.lv-fact.cash strong { color: #78350F; }
.lv-fact.paid { background: var(--green-lt); color: #065F46; }
.lv-next-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.lv-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 48px; padding: 0 22px; border-radius: 999px; font-family: inherit; font-size: 15px; font-weight: 800; text-decoration: none; border: none; cursor: pointer; transition: background-color .18s, transform .18s; }
.lv-btn-primary { background: var(--blue-2); color: #fff; flex: 1; min-width: 220px; }
.lv-btn-primary:hover { background: var(--blue-3); color: #fff; }
.lv-btn-ghost { background: rgba(22,22,42,.07); color: var(--ink); }
.lv-btn-ghost:hover { background: rgba(22,22,42,.12); color: var(--ink); }
.lv-idle { padding: 28px 20px; text-align: center; display: grid; gap: 8px; justify-items: center; }
.lv-idle-ico { width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: var(--blue-lt); color: var(--blue-2); }
.lv-idle-title { font-size: 16px; font-weight: 800; }
.lv-idle-sub { font-size: 13.5px; color: var(--ink-2); max-width: 44ch; }

/* ── Commandes récentes ── */
.lv-row { display: flex; align-items: center; gap: 12px; padding: 13px 20px; border-bottom: 1px solid #F0F3F9; text-decoration: none; color: inherit; transition: background-color .15s; }
.lv-row:last-child { border-bottom: none; }
.lv-row:hover { background: #F8FAFE; }
.lv-row-ico { width: 38px; height: 38px; border-radius: 12px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: var(--bg); color: var(--ink-2); }
.lv-row-txt { flex: 1; min-width: 0; }
.lv-row-num { font-size: 14px; font-weight: 700; }
.lv-row-sub { font-size: 12.5px; color: var(--ink-3); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.lv-row-amt { font-size: 14px; font-weight: 800; white-space: nowrap; }
.lv-st { font-size: 11.5px; font-weight: 800; padding: 4px 10px; border-radius: 999px; white-space: nowrap; flex-shrink: 0; }
.lv-st.s-ready { background: var(--amber-lt); color: #92400E; }
.lv-st.s-route { background: var(--blue-lt); color: var(--blue-3); }
.lv-st.s-done  { background: var(--green-lt); color: #065F46; }
.lv-st.s-other { background: #F1F3F8; color: var(--ink-3); }
.lv-empty { padding: 28px 20px; text-align: center; color: var(--ink-3); font-size: 13.5px; }

/* ── Objectif du jour ── */
.lv-goal { padding: 18px 20px; display: grid; gap: 10px; }
.lv-goal-top { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; }
.lv-goal-count { font-size: 22px; font-weight: 800; }
.lv-goal-count small { font-size: 14px; color: var(--ink-3); font-weight: 700; }
.lv-goal-bar { height: 10px; border-radius: 999px; background: #F1F3F8; overflow: hidden; }
.lv-goal-bar span { display: block; height: 100%; border-radius: 999px; background: var(--amber); transition: width .6s cubic-bezier(.22,1,.36,1); }
.lv-goal-sub { font-size: 13px; color: var(--ink-2); line-height: 1.45; }
.lv-goal-sub.reached { color: #065F46; font-weight: 700; }

/* ── Gains ── */
.lv-earn { padding: 20px; display: grid; gap: 4px; background: radial-gradient(120% 160% at 100% 0%, rgba(31,124,246,.35), transparent 60%), linear-gradient(135deg, var(--navy), var(--navy-2)); color: #fff; }
.lv-earn-lbl { font-size: 12.5px; color: #C9D6EA; font-weight: 600; }
.lv-earn-val { font-size: 28px; font-weight: 800; letter-spacing: -.01em; }
.lv-earn-foot { padding: 12px 20px; }

/* ── Classement ── */
.lv-rank { display: flex; align-items: center; gap: 12px; padding: 11px 20px; border-bottom: 1px solid #F0F3F9; }
.lv-rank:last-child { border-bottom: none; }
.lv-rank.me { background: var(--blue-lt); }
.lv-rank-pos { width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 12.5px; font-weight: 800; background: #F1F3F8; color: var(--ink-3); }
.lv-rank-pos.r1 { background: #FDE68A; color: #78350F; }
.lv-rank-pos.r2 { background: #E2E8F0; color: #334155; }
.lv-rank-pos.r3 { background: #FED7AA; color: #7C2D12; }
.lv-rank-name { flex: 1; min-width: 0; font-size: 13.5px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.lv-rank.me .lv-rank-name { color: var(--blue-3); }
.lv-rank-count { font-size: 13px; font-weight: 800; color: var(--ink-2); white-space: nowrap; }

/* ── Notifications ── */
.lv-notif { padding: 18px 20px; display: grid; gap: 12px; }
.lv-notif-top { display: flex; gap: 12px; align-items: flex-start; }
.lv-notif-ico { width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: var(--amber-lt); color: #B45309; }
.lv-notif-title { font-size: 14.5px; font-weight: 800; }
.lv-notif-sub { font-size: 13px; color: var(--ink-2); margin-top: 2px; line-height: 1.45; }

/* ── Responsive ── */
@media (max-width: 1024px) {
    .lv-grid { grid-template-columns: minmax(0, 1fr); }
    .lv-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 520px) {
    .lv-head { padding-bottom: 70px; }
    .lv-kpi { padding: 13px; gap: 8px; flex-direction: column; align-items: flex-start; }
    .lv-kpi > span:last-child { width: 100%; }
    .lv-kpi-ico { width: 34px; height: 34px; border-radius: 11px; }
    .lv-kpi-val { font-size: 20px; }
    .lv-row { padding: 12px 16px; }
    .lv-row-amt { display: none; }
    .lv-next-actions { flex-direction: column; }
    .lv-next-actions .lv-btn { width: 100%; min-width: 0; }
    .lv-avail { padding: 0 13px; font-size: 13px; }
}
@media (prefers-reduced-motion: reduce) {
    .lv-kpi, .lv-goal-bar span { transition: none; }
}
</style>
@endpush

@section('content')
@php
    use App\Support\IconLibrary as Ico;

    $first    = explode(' ', trim($livreur->name))[0];
    $parts    = preg_split('/\s+/', trim($livreur->name));
    $initials = mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));
    // Espaces insécables : un montant ne se coupe jamais en fin de ligne (« 5 000 GNF »)
    $fmt      = fn ($n) => number_format($n ?? 0, 0, ',', "\u{00A0}") . "\u{00A0}" . $devise;
    $org      = $isCompanyDriver ? (optional(\App\Models\Driver::where('user_id', $livreur->id)->first()?->company)->name ?? 'Société de livraison') : ($shop?->name ?? 'Livreur Shopio');

    // Statut → libellé, classe, icône et destination du lien
    $statusInfo = function (string $status) {
        return match (true) {
            in_array($status, ['en_livraison', 'delivering', 'shipped'], true) => ['En route', 's-route', 'truck'],
            in_array($status, ['livrée', 'delivered', 'completed'], true)      => ['Livrée', 's-done', 'check'],
            $status === 'annulée'                                              => ['Annulée', 's-other', 'x'],
            $status === 'en_attente'                                           => ['En attente', 's-ready', 'clock'],
            default                                                            => ['À récupérer', 's-ready', 'package'],
        };
    };
    $rowUrl = fn ($status, $order) => in_array($status, ['en_livraison', 'delivering', 'shipped', 'confirmée'], true)
        ? route('orders.nav', $order)
        : route('livreur.orders.index', ['status' => in_array($status, ['livrée', 'delivered', 'completed', 'annulée'], true) ? 'delivered' : 'confirmed']);

    $isRoute  = $nextOrder && $nextOrder->status === \App\Models\Order::STATUS_EN_LIVRAISON;
    $nextPaid = $nextOrder && $nextOrder->payment
        && $nextOrder->payment->method !== \App\Models\Payment::METHOD_CASH
        && $nextOrder->payment->status === \App\Models\Payment::STATUS_PAYE;
@endphp

<div class="lv">
    {{-- ═══ En-tête ═══ --}}
    <header class="lv-head">
        <div class="lv-head-in">
            <div class="lv-top">
                <div class="lv-user">
                    <div class="lv-av" aria-hidden="true">{{ $initials }}</div>
                    <div style="min-width:0">
                        <div class="lv-name">{{ $livreur->name }}</div>
                        <div class="lv-org">{{ $org }}</div>
                    </div>
                </div>
                <div class="lv-top-actions">
                    <form action="{{ route('livreur.availability.toggle') }}" method="POST">
                        @csrf @method('PUT')
                        <button type="submit" class="lv-avail {{ $livreur->is_available ? 'is-on' : 'is-off' }}"
                                aria-label="{{ $livreur->is_available ? 'Vous êtes en ligne. Passer hors ligne' : 'Vous êtes hors ligne. Passer en ligne' }}">
                            <span class="lv-dot" aria-hidden="true"></span>
                            {{ $livreur->is_available ? 'En ligne' : 'Hors ligne' }}
                        </button>
                    </form>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="lv-logout" aria-label="Se déconnecter" title="Se déconnecter">{!! Ico::svg('logout', '', 18) !!}</button>
                    </form>
                </div>
            </div>
            <div class="lv-hello">
                <h1>Bonjour {{ $first }}</h1>
                <p>
                    {{ ucfirst(now()->translatedFormat('l j F')) }} ·
                    {{ $livreur->is_available ? 'Vous êtes en ligne : les nouvelles courses vous arrivent.' : 'Vous êtes hors ligne : passez en ligne pour recevoir des courses.' }}
                </p>
            </div>
        </div>
    </header>

    <main class="lv-main">
        {{-- ═══ Chiffres du jour ═══ --}}
        <div class="lv-kpis">
            <a href="{{ route('livreur.orders.index', ['status' => 'confirmed']) }}" class="lv-kpi">
                <span class="lv-kpi-ico amber">{!! Ico::svg('package', '', 20) !!}</span>
                <span style="min-width:0"><div class="lv-kpi-val lv-num" id="kpi-attente">{{ $enAttente }}</div><div class="lv-kpi-lbl">À récupérer</div></span>
            </a>
            <a href="{{ route('livreur.orders.index', ['status' => 'delivering']) }}" class="lv-kpi">
                <span class="lv-kpi-ico blue">{!! Ico::svg('truck', '', 20) !!}</span>
                <span style="min-width:0"><div class="lv-kpi-val lv-num" id="kpi-encours">{{ $enCours }}</div><div class="lv-kpi-lbl">En route</div></span>
            </a>
            <a href="{{ route('livreur.orders.index', ['status' => 'delivered']) }}" class="lv-kpi">
                <span class="lv-kpi-ico green">{!! Ico::svg('check', '', 20) !!}</span>
                <span style="min-width:0"><div class="lv-kpi-val lv-num" id="kpi-today">{{ $dailyProgress['count'] }}</div><div class="lv-kpi-lbl">Livrées aujourd'hui</div></span>
            </a>
            <a href="{{ route('livreur.commissions.index') }}" class="lv-kpi">
                <span class="lv-kpi-ico navy">{!! Ico::svg('wallet', '', 20) !!}</span>
                <span style="min-width:0"><div class="lv-kpi-val lv-num" id="kpi-earned">{{ $fmt($todayCommission) }}</div><div class="lv-kpi-lbl">{{ $isCompanyDriver ? 'Frais collectés aujourd\'hui' : 'Gagné aujourd\'hui' }}</div></span>
            </a>
        </div>

        <div class="lv-grid">
            <div class="lv-col">
                {{-- ═══ Prochaine course ═══ --}}
                <section class="lv-card" id="nextCard" data-next-key="{{ $nextOrder ? $nextOrder->id . '|' . $nextOrder->status : '' }}" aria-labelledby="nextTitle">
                    <div class="lv-card-hd">
                        <h2 class="lv-card-title" id="nextTitle">{!! Ico::svg('navigation', '', 17) !!} Prochaine course</h2>
                        @if($enAttente + $enCours > 1)
                        <a href="{{ route('livreur.orders.index') }}" class="lv-link">{{ $enAttente + $enCours }} courses actives {!! Ico::svg('chevron-right', '', 14) !!}</a>
                        @endif
                    </div>

                    @if($nextOrder)
                    <div class="lv-next">
                        <div class="lv-next-top">
                            <span class="lv-chip {{ $isRoute ? 'route' : 'pickup' }}">
                                {!! Ico::svg($isRoute ? 'truck' : 'package', '', 14) !!} {{ $isRoute ? 'En route vers le client' : 'Colis à récupérer' }}
                            </span>
                            <span class="lv-next-num lv-num">Commande #{{ str_pad($nextOrder->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>

                        <div class="lv-route">
                            <div class="lv-stop {{ $isRoute ? 'is-done' : 'is-current' }}">
                                <span class="lv-stop-ico">{!! Ico::svg($isRoute ? 'check' : 'store', '', 16) !!}</span>
                                <div>
                                    <div class="lv-stop-lbl">Récupération</div>
                                    <div class="lv-stop-val">{{ $nextOrder->shop?->name ?? 'Boutique' }}</div>
                                    @if($nextOrder->shop?->address)<div class="lv-stop-sub">{{ $nextOrder->shop->address }}</div>@endif
                                </div>
                            </div>
                            <div class="lv-stop-line" aria-hidden="true"></div>
                            <div class="lv-stop {{ $isRoute ? 'is-current' : '' }}">
                                <span class="lv-stop-ico">{!! Ico::svg('map-pin', '', 16) !!}</span>
                                <div>
                                    <div class="lv-stop-lbl">Livraison</div>
                                    <div class="lv-stop-val">{{ $nextOrder->display_name }}</div>
                                    <div class="lv-stop-sub">{{ $nextOrder->delivery_destination ?: ($nextOrder->client?->address ?? 'Adresse à confirmer avec le client') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="lv-facts">
                            @if($nextPaid)
                            <span class="lv-fact paid">{!! Ico::svg('check', '', 14) !!} Déjà payée en ligne</span>
                            @else
                            <span class="lv-fact cash">{!! Ico::svg('wallet', '', 14) !!} À encaisser <strong class="lv-num">{{ $fmt($nextOrder->total) }}</strong></span>
                            @endif
                            @if($nextOrder->delivery_slot)
                            <span class="lv-fact">{!! Ico::svg('clock', '', 14) !!} <strong>{{ $nextOrder->delivery_slot }}</strong></span>
                            @endif
                            @if(filled($nextOrder->delivery_code))
                            <span class="lv-fact">{!! Ico::svg('lock', '', 14) !!} Code de remise à demander au client</span>
                            @endif
                        </div>

                        <div class="lv-next-actions">
                            <a href="{{ route('orders.nav', $nextOrder) }}" class="lv-btn lv-btn-primary">
                                {!! Ico::svg('navigation', '', 18) !!} {{ $isRoute ? 'Reprendre la navigation' : 'Aller récupérer le colis' }}
                            </a>
                            @if($nextOrder->display_phone)
                            <a href="tel:{{ $nextOrder->display_phone }}" class="lv-btn lv-btn-ghost">{!! Ico::svg('phone', '', 17) !!} Appeler le client</a>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="lv-idle">
                        <span class="lv-idle-ico">{!! Ico::svg('truck', '', 26) !!}</span>
                        <div class="lv-idle-title">Aucune course pour l'instant</div>
                        <div class="lv-idle-sub">
                            {{ $livreur->is_available
                                ? 'Restez en ligne : vous serez prévenu dès qu\'une commande vous est confiée.'
                                : 'Passez en ligne pour que les boutiques puissent vous confier des commandes.' }}
                        </div>
                    </div>
                    @endif
                </section>

                {{-- ═══ Commandes récentes ═══ --}}
                <section class="lv-card" aria-labelledby="recentTitle">
                    <div class="lv-card-hd">
                        <h2 class="lv-card-title" id="recentTitle">Commandes récentes</h2>
                        <a href="{{ route('livreur.orders.index') }}" class="lv-link">Tout voir {!! Ico::svg('chevron-right', '', 14) !!}</a>
                    </div>
                    <div id="recent-orders-list">
                        @forelse($recentOrders as $group)
                        @php
                            $order = $group['order'];
                            [$stLabel, $stCls, $stIco] = $statusInfo($group['status']);
                            $who   = $group['client']?->name ?? $order->client_name ?? 'Client';
                        @endphp
                        <a href="{{ $rowUrl($group['status'], $order) }}" class="lv-row">
                            <span class="lv-row-ico">{!! Ico::svg($stIco, '', 17) !!}</span>
                            <span class="lv-row-txt">
                                <div class="lv-row-num lv-num">{{ $group['count'] > 1 ? $group['count'] . ' commandes · 1 trajet' : 'Commande #' . str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div class="lv-row-sub">{{ $who }}@if($order->shop) · {{ $order->shop->name }}@endif</div>
                            </span>
                            <span class="lv-row-amt lv-num">{{ $fmt($group['total']) }}</span>
                            <span class="lv-st {{ $stCls }}">{{ $stLabel }}</span>
                        </a>
                        @empty
                        <div class="lv-empty">Aucune commande ne vous a encore été confiée.</div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="lv-col">
                {{-- ═══ Objectif du jour ═══ --}}
                <section class="lv-card" aria-labelledby="goalTitle">
                    <div class="lv-goal">
                        <div class="lv-goal-top">
                            <h2 class="lv-card-title" id="goalTitle">{!! Ico::svg('trophy', '', 17) !!} Objectif du jour</h2>
                            <span class="lv-goal-count lv-num" id="goal-count">{{ $dailyProgress['count'] }}<small>/{{ $dailyProgress['goal'] }}</small></span>
                        </div>
                        <div class="lv-goal-bar" role="progressbar" aria-label="Livraisons du jour" aria-valuemin="0" aria-valuemax="{{ $dailyProgress['goal'] }}" aria-valuenow="{{ $dailyProgress['count'] }}">
                            <span id="goal-bar" style="width:{{ $dailyProgress['percent'] }}%"></span>
                        </div>
                        <div class="lv-goal-sub {{ $dailyProgress['rewarded'] ? 'reached' : '' }}" id="goal-sub">
                            @if($dailyProgress['rewarded'])
                                Prime de {{ $fmt($dailyProgress['bonus']) }} débloquée aujourd'hui.
                            @else
                                Encore {{ max(0, $dailyProgress['goal'] - $dailyProgress['count']) }} livraison{{ $dailyProgress['goal'] - $dailyProgress['count'] > 1 ? 's' : '' }} pour débloquer une prime de {{ $fmt($dailyProgress['bonus']) }}.
                            @endif
                        </div>
                    </div>
                </section>

                {{-- ═══ Gains ═══ --}}
                <section class="lv-card" aria-label="{{ $isCompanyDriver ? 'Frais de livraison' : 'Mes gains' }}">
                    <div class="lv-earn">
                        <span class="lv-earn-lbl">{{ $isCompanyDriver ? 'Frais de livraison collectés pour l\'entreprise' : 'Total de mes commissions' }}</span>
                        <span class="lv-earn-val lv-num">{{ $fmt($totalCommission) }}</span>
                    </div>
                    <div class="lv-earn-foot">
                        <a href="{{ route('livreur.commissions.index') }}" class="lv-link">{{ $isCompanyDriver ? 'Voir le détail des frais' : 'Voir l\'historique des commissions' }} {!! Ico::svg('chevron-right', '', 14) !!}</a>
                    </div>
                </section>

                {{-- ═══ Classement de la semaine ═══ --}}
                @if(count($leaderboard))
                <section class="lv-card" aria-labelledby="rankTitle">
                    <div class="lv-card-hd">
                        <h2 class="lv-card-title" id="rankTitle">Classement de la semaine</h2>
                    </div>
                    @foreach(array_slice($leaderboard, 0, 5) as $row)
                    <div class="lv-rank {{ $row['is_me'] ? 'me' : '' }}">
                        <span class="lv-rank-pos {{ $row['rank'] <= 3 ? 'r' . $row['rank'] : '' }}">{{ $row['rank'] }}</span>
                        <span class="lv-rank-name">{{ $row['is_me'] ? 'Vous' : $row['name'] }}</span>
                        <span class="lv-rank-count lv-num">{{ $row['count'] }} livraison{{ $row['count'] > 1 ? 's' : '' }}</span>
                    </div>
                    @endforeach
                    @php $myRow = collect($leaderboard)->firstWhere('is_me', true); @endphp
                    @if($myRow && $myRow['rank'] > 5)
                    <div class="lv-rank me">
                        <span class="lv-rank-pos">{{ $myRow['rank'] }}</span>
                        <span class="lv-rank-name">Vous</span>
                        <span class="lv-rank-count lv-num">{{ $myRow['count'] }} livraison{{ $myRow['count'] > 1 ? 's' : '' }}</span>
                    </div>
                    @endif
                </section>
                @endif

                {{-- ═══ Notifications (masqué une fois activées) ═══ --}}
                <section class="lv-card" id="notifSettingsCard" aria-labelledby="notifTitle">
                    <div class="lv-notif">
                        <div class="lv-notif-top">
                            <span class="lv-notif-ico">{!! Ico::svg('bell', '', 19) !!}</span>
                            <div>
                                <div class="lv-notif-title" id="notifTitle">Notifications</div>
                                <div class="lv-notif-sub" id="notifSettingsSub">Soyez prévenu d'une nouvelle course, même l'application fermée.</div>
                            </div>
                        </div>
                        <button type="button" id="notifSettingsBtn" class="lv-btn lv-btn-primary" style="min-height:44px;font-size:14px">Activer les notifications</button>
                    </div>
                </section>
            </div>
        </div>
    </main>
</div>

<script>
(function () {
    var card = document.getElementById('notifSettingsCard');
    var btn  = document.getElementById('notifSettingsBtn');
    var sub  = document.getElementById('notifSettingsSub');
    if (!card) return;
    if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) { card.hidden = true; return; }

    function refresh() {
        if (Notification.permission === 'granted' && localStorage.getItem('push_subscribed')) {
            card.hidden = true; // déjà activées : rien à faire ici
        } else if (Notification.permission === 'denied') {
            sub.textContent = 'Bloquées par votre navigateur. Autorisez les notifications pour ce site dans ses réglages.';
            btn.hidden = true;
        }
    }
    btn.addEventListener('click', function () {
        btn.disabled = true;
        btn.textContent = 'Activation…';
        Promise.resolve(window.enablePushNotifications ? window.enablePushNotifications() : null)
            .finally(function () { btn.disabled = false; btn.textContent = 'Activer les notifications'; refresh(); });
    });
    refresh();
})();
</script>
@endsection

@push('scripts')
<script>
const DASH_DATA_URL = '{{ route('livreur.dashboard.data') }}';
const NAV_BASE      = '{{ url('/orders') }}';
const ORDERS_URL    = '{{ route('livreur.orders.index') }}';
const LV_ICO = {!! json_encode(collect(['truck', 'check', 'x', 'clock', 'package'])->mapWithKeys(fn ($n) => [$n => \App\Support\IconLibrary::svg($n, '', 17)])) !!};
const BONUS_TXT = {!! json_encode($fmt($dailyProgress['bonus'])) !!};

/* Le nom du client vient d'une saisie libre : on l'échappe avant de l'insérer dans la page */
const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

function statusInfo(s) {
    if (['en_livraison', 'delivering', 'shipped'].includes(s)) return ['En route', 's-route', 'truck'];
    if (['livrée', 'delivered', 'completed'].includes(s))      return ['Livrée', 's-done', 'check'];
    if (s === 'annulée')                                        return ['Annulée', 's-other', 'x'];
    if (s === 'en_attente')                                     return ['En attente', 's-ready', 'clock'];
    return ['À récupérer', 's-ready', 'package'];
}
function rowUrl(o) {
    if (['en_livraison', 'delivering', 'shipped', 'confirmée'].includes(o.status)) return `${NAV_BASE}/${o.order_id}/nav`;
    if (['livrée', 'delivered', 'completed', 'annulée'].includes(o.status)) return `${ORDERS_URL}?status=delivered`;
    return `${ORDERS_URL}?status=confirmed`;
}
function renderOrders(orders) {
    if (!orders.length) return '<div class="lv-empty">Aucune commande ne vous a encore été confiée.</div>';
    return orders.map(o => {
        const [label, cls, ico] = statusInfo(o.status);
        const title = o.count > 1 ? `${o.count} commandes · 1 trajet` : `Commande #${String(o.order_id).padStart(5, '0')}`;
        return `<a href="${rowUrl(o)}" class="lv-row">
            <span class="lv-row-ico">${LV_ICO[ico] || ''}</span>
            <span class="lv-row-txt">
                <div class="lv-row-num lv-num">${title}</div>
                <div class="lv-row-sub">${esc(o.client)}${o.shop ? ' · ' + esc(o.shop) : ''}</div>
            </span>
            <span class="lv-row-amt lv-num">${esc(o.total)}</span>
            <span class="lv-st ${cls}">${label}</span>
        </a>`;
    }).join('');
}

async function pollDashboard() {
    if (document.hidden) return;
    try {
        const r = await fetch(DASH_DATA_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
        if (!r.ok) return;
        const d = await r.json();

        // Nouvelle course ou changement d'étape : on recharge pour afficher la bonne carte
        const next = document.getElementById('nextCard');
        if (next && (next.dataset.nextKey || '') !== (d.nextKey || '')) { location.reload(); return; }

        document.getElementById('kpi-attente').textContent = d.enAttente;
        document.getElementById('kpi-encours').textContent = d.enCours;
        document.getElementById('kpi-earned').textContent  = d.earnedToday;
        document.getElementById('recent-orders-list').innerHTML = renderOrders(d.recentOrders);

        if (d.dailyProgress) {
            const gp = d.dailyProgress;
            document.getElementById('kpi-today').textContent = gp.count;
            document.getElementById('goal-count').innerHTML = `${gp.count}<small>/${gp.goal}</small>`;
            document.getElementById('goal-bar').style.width = gp.percent + '%';
            const sub = document.getElementById('goal-sub');
            const left = Math.max(0, gp.goal - gp.count);
            sub.textContent = gp.rewarded
                ? `Prime de ${BONUS_TXT} débloquée aujourd'hui.`
                : `Encore ${left} livraison${left > 1 ? 's' : ''} pour débloquer une prime de ${BONUS_TXT}.`;
            sub.classList.toggle('reached', !!gp.rewarded);
        }
    } catch (_) {}
}
setInterval(pollDashboard, 15000);
</script>
@endpush
