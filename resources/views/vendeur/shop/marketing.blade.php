{{--
    Marketing du vendeur : provenance des visites et des commandes (Facebook, Instagram, WhatsApp, QR…),
    liens de suivi à partager, catalogue produits pour Facebook / Instagram et test du pixel Meta.
    Données : Vendeur\MarketingController@index — sources : App\Support\TrafficSource.
--}}
@extends('layouts.app')

@section('title', 'Marketing · ' . $shop->name)

@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
html, body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; min-height: 100vh; -webkit-font-smoothing: antialiased; }

.mk-top { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; position: sticky; top: 0; z-index: 30; padding: 14px 28px !important; }
.mk-top .tb-info { flex: 1 1 240px; min-width: 0; }
.mk-title { display: flex; align-items: center; gap: 10px; margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -.02em; color: var(--text); }
.mk-title svg { color: var(--sv-blue); }
.mk-period { display: inline-flex; gap: 4px; padding: 4px; border-radius: 999px; background: var(--surface); box-shadow: inset 0 0 0 1.5px var(--border); }
.mk-period a { min-height: 36px; display: inline-flex; align-items: center; padding: 0 14px; border-radius: 999px; font-weight: 700; font-size: 13px; color: var(--text-2); text-decoration: none; }
.mk-period a[aria-current="true"] { background: var(--sv-btn); color: #fff; }
.mk-period a:not([aria-current="true"]):hover { color: var(--sv-btn); }

.mk-content { padding: 24px 28px 56px; display: flex; flex-direction: column; gap: 20px; max-width: 1180px; min-width: 0; width: 100%; }
.mk-content > * { min-width: 0; }
.mk-flash { border-radius: 18px; padding: 14px 18px; font-size: 14px; font-weight: 700; }
.mk-flash-ok { background: var(--sv-ok50); color: var(--sv-ok); }
.mk-flash-err { background: var(--sv-err50); color: var(--sv-err); }

.mk-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 18px; border-radius: 999px; border: 0; font: 700 14px/1 var(--font); text-decoration: none; cursor: pointer; white-space: nowrap; transition: transform 180ms var(--sv-e-out), background-color 180ms, box-shadow 180ms, color 180ms; }
.mk-btn:active { transform: scale(.97); }
.mk-btn-blue { background: var(--sv-btn); color: #fff; }
.mk-btn-blue:hover { background: var(--sv-btn-h); color: #fff; transform: translateY(-1px); box-shadow: 0 8px 20px rgba(22,104,216,.28); }
.mk-btn-line { background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1.5px var(--border); }
.mk-btn-line:hover { box-shadow: inset 0 0 0 1.5px var(--sv-blue); color: var(--sv-btn); }
.mk-btn-sm { min-height: 40px; padding: 0 14px; font-size: 13px; }
.mk-btn:focus-visible, .mk-period a:focus-visible { outline: 3px solid var(--sv-blue); outline-offset: 2px; }

/* ── Résumé bleu nuit + courbe des visites ── */
.mk-hero { background: var(--sv-night); color: #E6EEFB; border-radius: 28px; padding: 26px 28px; display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.25fr); gap: 28px; }
.mk-hero-eyebrow { font-size: 12px; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; color: var(--sv-sky); margin: 0 0 14px; }
.mk-kpis { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px 22px; }
.mk-kpi-val { font-size: 30px; font-weight: 800; letter-spacing: -.02em; color: #fff; line-height: 1.1; font-variant-numeric: tabular-nums; }
.mk-kpi-lbl { font-size: 13px; color: #9FB3D3; margin-top: 4px; }
.mk-chart { display: flex; flex-direction: column; gap: 8px; min-width: 0; }
.mk-chart-head { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; font-size: 13px; color: #9FB3D3; }
.mk-chart-head strong { color: #fff; font-weight: 700; }
.mk-bars { display: flex; align-items: flex-end; gap: 3px; height: 150px; padding-top: 8px; border-bottom: 1px solid rgba(255,255,255,.14); background-image: linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px); background-size: 100% 50%; }
.mk-bar { flex: 1 1 0; min-width: 2px; border-radius: 4px 4px 0 0; background: var(--sv-blue); height: var(--h); min-height: 2px; transform-origin: bottom; animation: mkGrow .7s var(--sv-e-out) both; animation-delay: calc(var(--i) * 12ms); }
.mk-bar.is-zero { background: rgba(255,255,255,.12); }
.mk-bar.is-today { background: var(--sv-amber); }
.mk-axis { display: flex; justify-content: space-between; font-size: 11px; color: #8EA3C4; font-variant-numeric: tabular-nums; }
@keyframes mkGrow { from { transform: scaleY(0); } }

/* ── Cartes ── */
.mk-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; align-items: start; }
.mk-card { background: var(--surface); border-radius: 24px; box-shadow: 0 0 0 1px var(--border); padding: 22px 24px; display: flex; flex-direction: column; gap: 16px; min-width: 0; scroll-margin-top: 96px; }
.mk-card-wide { grid-column: 1 / -1; }
.mk-card-head { display: flex; align-items: flex-start; gap: 14px; }
.mk-card-ico { width: 44px; height: 44px; border-radius: 14px; background: var(--sv-b50); color: var(--sv-btn); display: flex; align-items: center; justify-content: center; flex: none; }
.mk-card-title { margin: 0; font-size: 18px; font-weight: 800; letter-spacing: -.01em; color: var(--text); text-wrap: balance; }
.mk-card-sub { margin: 4px 0 0; font-size: 14px; color: var(--muted); line-height: 1.5; max-width: 64ch; }
.mk-note { font-size: 13px; color: var(--muted); margin: 0; line-height: 1.5; }

/* Tableau des sources */
.mk-table-wrap { overflow-x: auto; margin: 0 -4px; }
.mk-table { width: 100%; border-collapse: collapse; font-size: 14px; min-width: 560px; }
.mk-table th { text-align: left; padding: 0 10px 10px; font-size: 11px; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: var(--muted); white-space: nowrap; }
.mk-table td { padding: 12px 10px; border-top: 1px solid var(--border); vertical-align: middle; font-variant-numeric: tabular-nums; }
.mk-table .num { text-align: right; white-space: nowrap; }
.mk-src { display: flex; align-items: center; gap: 10px; font-weight: 700; color: var(--text); white-space: nowrap; }
.mk-dot { width: 10px; height: 10px; border-radius: 50%; flex: none; background: var(--c); }
.mk-share { display: flex; align-items: center; gap: 10px; min-width: 140px; }
.mk-share-bar { flex: 1; height: 8px; border-radius: 999px; background: var(--sv-b50); overflow: hidden; }
.mk-share-bar i { display: block; height: 100%; width: var(--w); background: var(--c); border-radius: 999px; }
.mk-share span { width: 36px; text-align: right; color: var(--muted); font-size: 12px; }
.mk-conv { display: inline-flex; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 800; background: var(--sv-b50); color: var(--sv-btn); }
.mk-conv.is-good { background: var(--sv-ok50); color: var(--sv-ok); }
.mk-empty { text-align: center; padding: 26px 16px; border-radius: 18px; background: var(--sv-b50); color: var(--text-2); font-size: 14px; line-height: 1.6; }
.mk-empty strong { display: block; color: var(--text); font-size: 15px; margin-bottom: 4px; }

/* Liens de suivi */
.mk-field-lbl { font-size: 13px; font-weight: 700; color: var(--text-2); }
.mk-select, .mk-url { width: 100%; min-height: 44px; border-radius: 14px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font: 500 14px var(--font); padding: 0 14px; }
.mk-url { font-size: 13px; color: var(--text-2); text-overflow: ellipsis; }
.mk-links { display: flex; flex-direction: column; gap: 12px; margin: 0; padding: 0; list-style: none; }
.mk-link { display: grid; grid-template-columns: 150px minmax(0, 1fr) auto; gap: 10px; align-items: center; }
.mk-link-name { display: flex; flex-direction: column; gap: 2px; }
.mk-link-name b { display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--text); }
.mk-link-name small { font-size: 12px; color: var(--muted); }
.mk-link-actions { display: flex; gap: 6px; }
.mk-qr { display: flex; gap: 16px; align-items: center; padding: 14px; border-radius: 18px; background: var(--sv-b50); }
.mk-qr-img { width: 104px; height: 104px; background: #fff; border-radius: 12px; padding: 6px; flex: none; }
.mk-qr-img svg { width: 100%; height: 100%; display: block; }
.mk-qr p { margin: 0 0 10px; font-size: 13px; color: var(--text-2); line-height: 1.5; }

/* Catalogue */
.mk-stats { display: flex; gap: 10px; flex-wrap: wrap; }
.mk-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 13px; font-weight: 700; background: var(--sv-ok50); color: var(--sv-ok); }
.mk-chip.is-warn { background: var(--sv-amb50); color: var(--sv-amb-ink); text-decoration: none; }
.mk-steps { margin: 0; padding: 0; list-style: none; counter-reset: st; display: flex; flex-direction: column; gap: 10px; }
.mk-steps li { counter-increment: st; display: grid; grid-template-columns: 28px 1fr; gap: 10px; font-size: 14px; color: var(--text-2); line-height: 1.5; }
.mk-steps li::before { content: counter(st); width: 28px; height: 28px; border-radius: 50%; background: var(--sv-b100); color: var(--sv-btn); font-weight: 800; font-size: 13px; display: flex; align-items: center; justify-content: center; }
.mk-steps b { color: var(--text); }
.mk-copyrow { display: flex; gap: 8px; }
.mk-copyrow .mk-url { flex: 1; min-width: 0; }

/* Pixel */
.mk-status { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 18px; background: var(--sv-b50); }
.mk-status .mk-dot { width: 12px; height: 12px; }
.mk-status b { display: block; color: var(--text); font-size: 14px; }
.mk-status small { color: var(--muted); font-size: 13px; font-variant-numeric: tabular-nums; }
.mk-status.is-test { background: var(--sv-amb50); }
.mk-events { display: flex; flex-wrap: wrap; gap: 8px; margin: 0; padding: 0; list-style: none; }
.mk-events li { padding: 6px 12px; border-radius: 999px; background: var(--surface); box-shadow: inset 0 0 0 1.5px var(--border); font-size: 13px; color: var(--text-2); }
.mk-events b { color: var(--text); }
.mk-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.mk-row form { margin: 0; }

.mk-toast { position: fixed; left: 50%; bottom: calc(24px + env(safe-area-inset-bottom, 0px)); transform: translate(-50%, 20px); opacity: 0; pointer-events: none; background: var(--sv-night); color: #fff; padding: 12px 20px; border-radius: 999px; font-weight: 700; font-size: 14px; transition: opacity .2s, transform .2s var(--sv-e-out); z-index: 60; }
.mk-toast.show { opacity: 1; transform: translate(-50%, 0); }

@media (max-width: 1100px) { .mk-grid { grid-template-columns: minmax(0, 1fr); } }
@media (max-width: 900px) {
    .mk-hero { grid-template-columns: minmax(0, 1fr); padding: 22px; }
    .mk-top { padding: 12px 16px !important; }
    .mk-content { padding: 18px 16px 48px; }
}
/* Campagnes */
.mk-btn-ghost { background: transparent; color: var(--muted); }
.mk-btn-ghost:hover { color: var(--sv-err); background: var(--sv-err50); }
.mk-camp-form { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr) minmax(0, 1fr) auto; gap: 12px; align-items: end; }
.mk-camp-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.mk-form-err { margin: 0; color: var(--sv-err); font-size: 13px; font-weight: 700; }
.mk-camps { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.mk-camp { border-radius: 18px; box-shadow: inset 0 0 0 1.5px var(--border); padding: 16px; display: flex; flex-direction: column; gap: 12px; min-width: 0; }
.mk-camp-top { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 10px; }
.mk-camp-top .mk-src { white-space: normal; }
.mk-camp-meta { font-size: 12px; color: var(--muted); }
.mk-chip.is-code { background: var(--sv-b50); color: var(--sv-btn); margin-left: auto; letter-spacing: .03em; }
.mk-camp-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; margin: 0; }
.mk-camp-stats div { background: var(--bg); border-radius: 12px; padding: 8px 10px; min-width: 0; }
.mk-camp-stats dt { font-size: 11px; font-weight: 700; color: var(--muted); }
.mk-camp-stats dd { margin: 2px 0 0; font-weight: 800; font-size: 15px; color: var(--text); font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
.mk-camp .mk-copyrow form { margin: 0; display: flex; }

/* Produits */
.mk-prod { display: flex; align-items: center; gap: 12px; min-width: 220px; }
.mk-prod img, .mk-prod-ph { width: 40px; height: 40px; border-radius: 10px; object-fit: cover; flex: none; background: var(--sv-b50); }
.mk-prod-name { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 8px; }
.mk-prod-name a { color: var(--text); font-weight: 700; text-decoration: none; }
.mk-prod-name a:hover { color: var(--sv-btn); text-decoration: underline; }
.mk-prod-name .mk-chip { padding: 2px 8px; font-size: 11px; }

@media (max-width: 1100px) { .mk-camps { grid-template-columns: minmax(0, 1fr); } }
@media (max-width: 760px) { .mk-camp-form { grid-template-columns: minmax(0, 1fr); } }
@media (max-width: 560px) {
    .mk-camp-stats { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
    .mk-camp-stats div:last-child { grid-column: 1 / -1; }
    .mk-link { grid-template-columns: minmax(0, 1fr) auto; }
    .mk-link-name { grid-column: 1 / -1; }
    .mk-kpi-val { font-size: 24px; }
    .mk-qr { flex-direction: column; align-items: flex-start; }
    .mk-card { padding: 18px; }
}
@media (prefers-reduced-motion: reduce) {
    .mk-bar { animation: none; }
    .mk-btn, .mk-toast { transition: none; }
    .mk-btn-blue:hover { transform: none; }
}
</style>
@endpush

@section('content')
@php
    $cur = $shop->currency ?: 'GNF';
    $money = fn ($v) => number_format((float) $v, 0, ',', ' ') . ' ' . $cur;
    $srcColors = ['facebook' => '#1F7CF6', 'instagram' => '#D6336C', 'tiktok' => '#0B1E3D', 'whatsapp' => '#16A34A', 'qr' => '#F59E0B', 'google' => '#7C3AED', 'shopio' => '#7DB4FF', 'autre' => '#94A3B8', 'direct' => '#64748B'];
    $maxVisits = max(1, $rows->max('visits') ?? 0);
    $maxDay = max(1, $series->max('visits'));
    $conv = $totals['visits'] > 0 ? round($totals['orders'] / $totals['visits'] * 100, 1) : null;
    $channels = [
        'facebook'  => ['Facebook', 'Publications, pages, groupes'],
        'instagram' => ['Instagram', 'Bio et stories'],
        'tiktok'    => ['TikTok', 'Bio et descriptions'],
        'whatsapp'  => ['WhatsApp', 'Statut et discussions'],
        'qr'        => ['QR code', 'Affiches, emballages'],
    ];
    $hasPixel = (bool) $shop->meta_pixel_id;
    $ico = fn ($d, $s = 22) => '<svg width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="'.$d.'"/></svg>';
@endphp
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'marketing'])

    <main class="main vs-main">
        <div class="topbar mk-top">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <div class="tb-info">
                <h1 class="mk-title">{!! $ico('M4 10v4h3l6 4V6l-6 4zM16 9a4 4 0 0 1 0 6M18.5 6.5a8 8 0 0 1 0 11') !!} Marketing</h1>
                <div class="tb-sub">{{ $shop->name }} · d'où viennent vos clients</div>
            </div>
            <nav class="mk-period" aria-label="Période">
                @foreach(\App\Http\Controllers\Vendeur\MarketingController::PERIODS as $p)
                    <a href="{{ route('boutique.marketing', ['jours' => $p]) }}" @if($p === $days) aria-current="true" @endif>{{ $p }} jours</a>
                @endforeach
            </nav>
        </div>

        <div class="mk-content">
            @if(session('success'))<div class="mk-flash mk-flash-ok" role="status">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="mk-flash mk-flash-err" role="alert">{{ session('error') }}</div>@endif

            {{-- ══ Résumé de la période ══ --}}
            <section class="mk-hero" aria-label="Résumé des {{ $days }} derniers jours">
                <div>
                    <p class="mk-hero-eyebrow">{{ $days }} derniers jours</p>
                    <div class="mk-kpis">
                        <div><div class="mk-kpi-val">{{ number_format($totals['visits'], 0, ',', ' ') }}</div><div class="mk-kpi-lbl">Visiteurs</div></div>
                        <div><div class="mk-kpi-val">{{ number_format($totals['orders'], 0, ',', ' ') }}</div><div class="mk-kpi-lbl">Commandes</div></div>
                        <div><div class="mk-kpi-val">{{ $money($totals['revenue']) }}</div><div class="mk-kpi-lbl">Chiffre d'affaires (hors annulées)</div></div>
                        <div><div class="mk-kpi-val">{{ $conv !== null ? str_replace('.', ',', $conv) . ' %' : '—' }}</div><div class="mk-kpi-lbl">Visiteurs qui commandent</div></div>
                    </div>
                </div>
                <div class="mk-chart">
                    <div class="mk-chart-head"><span>Visiteurs par jour</span><span>Maximum : <strong>{{ $series->max('visits') }}</strong></span></div>
                    <div class="mk-bars" role="img" aria-label="Visiteurs par jour sur {{ $days }} jours, maximum {{ $series->max('visits') }}">
                        @foreach($series as $i => $d)
                            <span class="mk-bar {{ $d['visits'] === 0 ? 'is-zero' : '' }} {{ $loop->last ? 'is-today' : '' }}" style="--h:{{ round($d['visits'] / $maxDay * 100, 1) }}%;--i:{{ $i }}" title="{{ $d['label'] }} : {{ $d['visits'] }} visiteur{{ $d['visits'] > 1 ? 's' : '' }}"></span>
                        @endforeach
                    </div>
                    <div class="mk-axis"><span>{{ $series->first()['label'] }}</span><span>{{ $series[intdiv($days, 2)]['label'] }}</span><span>Aujourd'hui</span></div>
                </div>
            </section>

            {{-- ══ Sources ══ --}}
            <section class="mk-card" aria-labelledby="mkSrcTitle">
                <div class="mk-card-head">
                    <span class="mk-card-ico">{!! $ico('M4 19h16M7 16V9M12 16V5M17 16v-4') !!}</span>
                    <div>
                        <h2 class="mk-card-title" id="mkSrcTitle">D'où viennent vos clients</h2>
                        <p class="mk-card-sub">Chaque visiteur est compté une fois par jour. Une commande est attribuée au dernier réseau par lequel le client est arrivé, jusqu'à 7 jours avant.</p>
                    </div>
                </div>

                @if($rows->isEmpty())
                    <div class="mk-empty">
                        <strong>Aucune visite enregistrée sur cette période</strong>
                        Partagez vos liens de suivi ci-dessous sur Facebook, Instagram ou WhatsApp : chaque visite s'affichera ici avec son réseau d'origine.
                    </div>
                @else
                    <div class="mk-table-wrap">
                        <table class="mk-table">
                            <thead><tr><th scope="col">Source</th><th scope="col">Visiteurs</th><th scope="col" class="num">Commandes</th><th scope="col" class="num">Chiffre d'affaires</th><th scope="col" class="num">Taux de commande</th></tr></thead>
                            <tbody>
                            @foreach($rows as $r)
                                @php $c = $srcColors[$r['source']] ?? '#94A3B8'; $rate = $r['visits'] > 0 ? round($r['orders'] / $r['visits'] * 100, 1) : null; @endphp
                                <tr>
                                    <td><span class="mk-src"><span class="mk-dot" style="--c:{{ $c }}"></span>{{ $r['label'] }}</span></td>
                                    <td>
                                        <div class="mk-share" style="--c:{{ $c }}">
                                            <div class="mk-share-bar"><i style="--w:{{ round($r['visits'] / $maxVisits * 100, 1) }}%"></i></div>
                                            <span>{{ number_format($r['visits'], 0, ',', ' ') }}</span>
                                        </div>
                                    </td>
                                    <td class="num">{{ $r['orders'] }}</td>
                                    <td class="num">{{ $money($r['revenue']) }}</td>
                                    <td class="num">
                                        @if($rate !== null)<span class="mk-conv {{ $conv !== null && $rate > $conv ? 'is-good' : '' }}">{{ str_replace('.', ',', $rate) }} %</span>@else — @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                @if($unknownOrders > 0)
                    <p class="mk-note">{{ $unknownOrders }} commande{{ $unknownOrders > 1 ? 's' : '' }} sans source connue sur cette période (passée{{ $unknownOrders > 1 ? 's' : '' }} avant l'activation du suivi ou sans visite enregistrée).</p>
                @endif
            </section>

            {{-- ══ Campagnes nommées ══ --}}
            <section class="mk-card" id="campagnes" aria-labelledby="mkCampTitle">
                <div class="mk-card-head">
                    <span class="mk-card-ico">{!! $ico('M5 21V4M5 4h11l-2 4 2 4H5') !!}</span>
                    <div>
                        <h2 class="mk-card-title" id="mkCampTitle">Campagnes</h2>
                        <p class="mk-card-sub">Un lien à part pour un influenceur, une promotion ou une affiche. Si vous y liez un code promo, il est proposé au client arrivé par ce lien, et les commandes passées avec ce code comptent aussi pour la campagne.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('boutique.marketing.campaigns.store') }}" class="mk-camp-form">
                    @csrf
                    <div class="mk-camp-field mk-camp-name">
                        <label class="mk-field-lbl" for="mkCampName">Nom de la campagne</label>
                        <input type="text" class="mk-select" id="mkCampName" name="name" maxlength="60" required value="{{ old('name') }}" placeholder="Ex. Influenceuse Awa, Promo Tabaski" @error('name') aria-invalid="true" @enderror>
                    </div>
                    <div class="mk-camp-field">
                        <label class="mk-field-lbl" for="mkCampChannel">Partagée sur</label>
                        <select class="mk-select" id="mkCampChannel" name="channel">
                            @foreach($channels as $key => [$name, $hint])
                                <option value="{{ $key }}" @selected(old('channel', 'instagram') === $key)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mk-camp-field">
                        <label class="mk-field-lbl" for="mkCampPromo">Code promo (facultatif)</label>
                        <select class="mk-select" id="mkCampPromo" name="promo_code_id">
                            <option value="">Aucun</option>
                            @foreach($promoCodes as $pc)
                                <option value="{{ $pc->id }}" @selected((string) old('promo_code_id') === (string) $pc->id)>{{ $pc->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="mk-btn mk-btn-blue">Créer le lien</button>
                </form>
                @if($errors->any())
                    <p class="mk-form-err" role="alert">{{ $errors->first() }}</p>
                @endif
                @if($promoCodes->isEmpty())
                    <p class="mk-note">Pour offrir une réduction avec une campagne, créez d'abord un code dans <a href="{{ route('boutique.promo-codes.index') }}" style="color:var(--sv-btn);font-weight:700">Codes promo</a>.</p>
                @endif

                @if($campaigns->isNotEmpty())
                    <ul class="mk-camps">
                        @foreach($campaigns as $c)
                            <li class="mk-camp">
                                <div class="mk-camp-top">
                                    <span class="mk-src"><span class="mk-dot" style="--c:{{ $srcColors[$c['channel']] ?? '#94A3B8' }}"></span>{{ $c['name'] }}</span>
                                    <span class="mk-camp-meta">{{ \App\Support\TrafficSource::label($c['channel']) }} · depuis le {{ $c['since']->format('d/m/Y') }}</span>
                                    @if($c['promo'])<span class="mk-chip is-code">Code {{ $c['promo'] }}</span>@endif
                                </div>
                                <dl class="mk-camp-stats">
                                    <div><dt>Visiteurs</dt><dd>{{ number_format($c['visits'], 0, ',', ' ') }}</dd></div>
                                    <div><dt>Commandes</dt><dd>{{ $c['orders'] }}</dd></div>
                                    <div><dt>Chiffre d'affaires</dt><dd>{{ $money($c['revenue']) }}</dd></div>
                                </dl>
                                <div class="mk-copyrow">
                                    <input type="text" class="mk-url" id="mkCamp-{{ $c['id'] }}" value="{{ $c['url'] }}" readonly aria-label="Lien de la campagne {{ $c['name'] }}">
                                    <button type="button" class="mk-btn mk-btn-line mk-btn-sm" data-copy="#mkCamp-{{ $c['id'] }}">Copier</button>
                                    <form method="POST" action="{{ route('boutique.marketing.campaigns.archive', $c['id']) }}" data-confirm="Archiver « {{ $c['name'] }} » ? Le lien mènera toujours à votre boutique, mais ses visites ne seront plus comptées à part.">
                                        @csrf
                                        <button type="submit" class="mk-btn mk-btn-ghost mk-btn-sm">Archiver</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- ══ Statistiques par produit ══ --}}
            <section class="mk-card" id="produits" aria-labelledby="mkProdTitle">
                <div class="mk-card-head">
                    <span class="mk-card-ico">{!! $ico('M3 12 12 3h8v8l-9 9zM15.5 8.5h.01') !!}</span>
                    <div>
                        <h2 class="mk-card-title" id="mkProdTitle">Vos produits</h2>
                        <p class="mk-card-sub">Les fiches les plus consultées sur {{ $days }} jours et ce qu'elles rapportent. Une fiche « à revoir » est beaucoup vue mais peu commandée : vérifiez le prix, les photos et la description.</p>
                    </div>
                </div>

                @if($productStats->isEmpty())
                    <div class="mk-empty">
                        <strong>Pas encore de vues sur vos fiches produit</strong>
                        Les vues sont comptées depuis aujourd'hui. Partagez un produit avec les liens ci-dessous pour voir lesquels attirent le plus.
                    </div>
                @else
                    <div class="mk-table-wrap">
                        <table class="mk-table">
                            <thead><tr><th scope="col">Produit</th><th scope="col" class="num">Vues</th><th scope="col" class="num">Commandes</th><th scope="col" class="num">Chiffre d'affaires</th><th scope="col" class="num">Taux de commande</th></tr></thead>
                            <tbody>
                            @foreach($productStats as $p)
                                <tr>
                                    <td>
                                        <div class="mk-prod">
                                            @if($p['thumb'])<img src="{{ $p['thumb'] }}" alt="" width="40" height="40" loading="lazy">@else<span class="mk-prod-ph" aria-hidden="true"></span>@endif
                                            <span class="mk-prod-name">
                                                <a href="{{ route('products.edit', $p['id']) }}">{{ $p['name'] }}</a>
                                                @if($p['review'])<span class="mk-chip is-warn">À revoir</span>@endif
                                            </span>
                                        </div>
                                    </td>
                                    <td class="num">{{ number_format($p['views'], 0, ',', ' ') }}</td>
                                    <td class="num">{{ $p['orders'] }}</td>
                                    <td class="num">{{ $money($p['revenue']) }}</td>
                                    <td class="num">@if($p['rate'] !== null){{ str_replace('.', ',', $p['rate']) }} %@else — @endif</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <div class="mk-grid">
                {{-- ══ Liens de suivi ══ --}}
                <section class="mk-card" id="liens" aria-labelledby="mkLinksTitle">
                    <div class="mk-card-head">
                        <span class="mk-card-ico">{!! $ico('M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1') !!}</span>
                        <div>
                            <h2 class="mk-card-title" id="mkLinksTitle">Liens de suivi</h2>
                            <p class="mk-card-sub">Utilisez un lien différent sur chaque réseau. Ils mènent tous à la même page, mais vous saurez lequel vous amène des clients.</p>
                        </div>
                    </div>

                    <label class="mk-field-lbl" for="mkTarget">Page à partager</label>
                    <select class="mk-select" id="mkTarget">
                        <option value="{{ $publicUrl }}">Ma vitrine (tous les produits)</option>
                        @if($products->isNotEmpty())
                        <optgroup label="Un produit">
                            @foreach($products as $p)<option value="{{ $p['url'] }}">{{ $p['name'] }}</option>@endforeach
                        </optgroup>
                        @endif
                    </select>

                    <ul class="mk-links">
                        @foreach($channels as $key => [$name, $hint])
                            <li class="mk-link">
                                <span class="mk-link-name"><b><span class="mk-dot" style="--c:{{ $srcColors[$key] }}"></span>{{ $name }}</b><small>{{ $hint }}</small></span>
                                <input type="text" class="mk-url" id="mkLink-{{ $key }}" data-channel="{{ $key }}" data-medium="{{ $key === 'qr' ? 'print' : 'social' }}" value="{{ $links[$key] }}" readonly aria-label="Lien de suivi {{ $name }}">
                                <span class="mk-link-actions">
                                    <button type="button" class="mk-btn mk-btn-line mk-btn-sm" data-copy="#mkLink-{{ $key }}">Copier</button>
                                    @if($key === 'whatsapp')
                                        <a class="mk-btn mk-btn-blue mk-btn-sm" id="mkWa" target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode('Découvrez ma boutique ' . $shop->name . ' sur Shopio : ' . $links['whatsapp']) }}">Envoyer</a>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    @if($qrSvg)
                    <div class="mk-qr">
                        <div class="mk-qr-img" role="img" aria-label="QR code menant à votre vitrine">{!! $qrSvg !!}</div>
                        <div>
                            <p>Ce QR code mène à votre vitrine. Les visites qui en viennent apparaissent sous « QR code ». Imprimez-le sur vos affiches, sacs ou cartes de visite.</p>
                            <a href="{{ route('boutique.storefront.qr') }}" class="mk-btn mk-btn-line mk-btn-sm">Télécharger le QR code</a>
                        </div>
                    </div>
                    @endif
                </section>

                <div style="display:flex;flex-direction:column;gap:20px;min-width:0">
                    {{-- ══ Catalogue Facebook / Instagram ══ --}}
                    <section class="mk-card" id="catalogue" aria-labelledby="mkCatTitle">
                        <div class="mk-card-head">
                            <span class="mk-card-ico">{!! $ico('M4 5h16v4H4zM5 9v10h14V9M9 13h6') !!}</span>
                            <div>
                                <h2 class="mk-card-title" id="mkCatTitle">Catalogue Facebook et Instagram</h2>
                                <p class="mk-card-sub">Vos produits dans la boutique Facebook, les publications Instagram identifiées et les publicités catalogue. Prix, stock et photos se mettent à jour tout seuls.</p>
                            </div>
                        </div>

                        <div class="mk-stats">
                            <span class="mk-chip">{{ $catalogCount }} produit{{ $catalogCount > 1 ? 's' : '' }} inclus</span>
                            @if($withoutPhoto > 0)
                                <a class="mk-chip is-warn" href="{{ route('products.index') }}">{{ $withoutPhoto }} sans photo, non inclus</a>
                            @endif
                        </div>

                        <div class="mk-copyrow">
                            <input type="text" class="mk-url" id="mkCatalog" value="{{ $catalogUrl }}" readonly aria-label="Adresse du catalogue">
                            <button type="button" class="mk-btn mk-btn-blue mk-btn-sm" data-copy="#mkCatalog">Copier</button>
                        </div>

                        <ol class="mk-steps">
                            <li><span>Ouvrez <b>Meta Commerce Manager</b> (business.facebook.com/commerce) et créez un catalogue « E-commerce » si vous n'en avez pas.</span></li>
                            <li><span>Dans <b>Catalogue → Sources de données</b>, choisissez <b>Ajouter des articles → Flux de données</b>.</span></li>
                            <li><span>Choisissez un <b>flux programmé</b>, collez l'adresse ci-dessus et réglez la mise à jour sur <b>chaque jour</b>.</span></li>
                            <li><span>Reliez ce catalogue à votre pixel pour les publicités catalogue : les identifiants des produits sont les mêmes.</span></li>
                        </ol>
                        <p class="mk-note"><a href="{{ $catalogUrl }}" style="color:var(--sv-btn);font-weight:700">Télécharger le fichier</a> pour vérifier son contenu.</p>
                    </section>

                    {{-- ══ Pixel Meta ══ --}}
                    <section class="mk-card" id="pixel" aria-labelledby="mkPixTitle">
                        <div class="mk-card-head">
                            <span class="mk-card-ico">{!! $ico('M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM12 12h.01') !!}</span>
                            <div>
                                <h2 class="mk-card-title" id="mkPixTitle">Pixel Meta</h2>
                                <p class="mk-card-sub">Mesure les visites et les achats venus de vos publicités Facebook et Instagram. Vos propres visites ne sont pas comptées.</p>
                            </div>
                        </div>

                        @if(!$hasPixel)
                            <div class="mk-status">
                                <span class="mk-dot" style="--c:var(--muted)"></span>
                                <span><b>Aucun pixel configuré</b><small>Collez l'identifiant de votre pixel (Gestionnaire d'événements Meta) dans les réglages.</small></span>
                            </div>
                            <div class="mk-row"><a href="{{ route('shop.edit', $shop) }}?etape=3" class="mk-btn mk-btn-blue">Ajouter mon pixel</a></div>
                        @else
                            <div class="mk-status {{ $pixelTest ? 'is-test' : '' }}">
                                <span class="mk-dot" style="--c:{{ $pixelTest ? 'var(--sv-amber)' : 'var(--sv-ok)' }}"></span>
                                <span>
                                    <b>{{ $pixelTest ? 'Mode test actif jusqu\'à ' . \Carbon\Carbon::createFromTimestamp($pixelTest['until'])->timezone(config('app.timezone'))->format('H:i') : 'Pixel actif' }}</b>
                                    <small>Identifiant {{ $shop->meta_pixel_id }}</small>
                                </span>
                            </div>
                            <ul class="mk-events" aria-label="Événements envoyés">
                                <li><b>PageView</b> · page vue</li>
                                <li><b>ViewContent</b> · produit consulté</li>
                                <li><b>AddToCart</b> · ajout au panier</li>
                                <li><b>InitiateCheckout</b> · début de commande</li>
                                <li><b>Purchase</b> · commande validée</li>
                            </ul>
                            <p class="mk-note">Le test ouvre votre vitrine avec le pixel actif pour vous pendant 30 minutes. Un panneau montre chaque événement envoyé à Facebook ; vous pouvez aussi les voir dans « Tester les événements » du Gestionnaire d'événements Meta.</p>
                            <div class="mk-row">
                                <form method="POST" action="{{ route('boutique.marketing.pixel-test') }}" target="_blank">
                                    @csrf
                                    <button type="submit" class="mk-btn mk-btn-blue">Tester mon pixel</button>
                                </form>
                                @if($pixelTest)
                                <form method="POST" action="{{ route('boutique.marketing.pixel-test.stop') }}">
                                    @csrf
                                    <button type="submit" class="mk-btn mk-btn-line">Arrêter le test</button>
                                </form>
                                @endif
                                <a href="{{ route('shop.edit', $shop) }}?etape=3" class="mk-btn mk-btn-line">Modifier</a>
                            </div>
                        @endif
                    </section>
                </div>
            </div>
        </div>
    </main>
</div>
<div class="mk-toast" id="mkToast" role="status" aria-live="polite"></div>
@endsection

@push('scripts')
<script>
(function () {
    var toast = document.getElementById('mkToast'), timer;
    function say(msg) {
        toast.textContent = msg; toast.classList.add('show');
        clearTimeout(timer); timer = setTimeout(function () { toast.classList.remove('show'); }, 2200);
    }

    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.querySelector(btn.getAttribute('data-copy'));
            var done = function () { say('Lien copié'); };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(input.value).then(done, function () { input.select(); document.execCommand('copy'); done(); });
            } else { input.select(); document.execCommand('copy'); done(); }
        });
    });

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) { if (!window.confirm(form.dataset.confirm)) e.preventDefault(); });
    });

    /* Changer la page à partager met à jour tous les liens (le QR code reste celui de la vitrine) */
    var target = document.getElementById('mkTarget'), wa = document.getElementById('mkWa');
    var shopName = @json($shop->name);
    target.addEventListener('change', function () {
        var base = target.value, isShop = target.selectedIndex === 0;
        document.querySelectorAll('.mk-url[data-channel]').forEach(function (input) {
            var sep = base.indexOf('?') === -1 ? '?' : '&';
            input.value = base + sep + 'utm_source=' + input.dataset.channel + '&utm_medium=' + input.dataset.medium;
        });
        if (wa) {
            var label = isShop ? 'Découvrez ma boutique ' + shopName + ' sur Shopio : ' : target.options[target.selectedIndex].text + ' chez ' + shopName + ' : ';
            wa.href = 'https://wa.me/?text=' + encodeURIComponent(label + document.getElementById('mkLink-whatsapp').value);
        }
    });

})();
</script>
@endpush

@push('styles')
@include('boutique._partials.sv-theme')
@endpush
