{{--
    resources/views/boutique/clients/show.blade.php — fiche d'un client
    Route  : GET /boutique/clients/{user} → Boutique\ClientController@show → name('boutique.clients.show')
    Variables :
      $user      → User (le client)
      $commandes → LengthAwarePaginator<Order>
      $stats     → object (total_depense, nb_commandes, derniere_cmd, premiere_cmd) — chiffres calculés
      $isTop     → bool
      $shop      → Shop
      $devise    → string
      $latestPhone, $latestAddress → dernières coordonnées données à la commande
    Même style que la liste des clients : menu vendeur, cartes blanches, icônes au trait, une seule couleur d'accent.
--}}

@extends('layouts.app')
@section('title', 'Fiche client · ' . $user->name)
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*, *::before, *::after { box-sizing: border-box; }
html, body { margin: 0; min-height: 100vh; background: var(--bg); color: var(--text); font-family: var(--font); -webkit-font-smoothing: antialiased; }

/* Barre du haut */
.cf-top { display: flex; align-items: center; gap: 12px; padding: 14px 28px !important; }
.cf-back { display: inline-flex; align-items: center; gap: 6px; min-height: 40px; padding: 0 14px 0 10px; border-radius: 999px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); color: var(--text-2); font-size: 14px; font-weight: 700; text-decoration: none; }
.cf-back:hover { color: #1668D8; box-shadow: inset 0 0 0 1px #1F7CF6; }
.cf-top-title { font-size: 15px; font-weight: 700; color: var(--muted); }

.cf-content { padding: 24px 28px 48px; display: flex; flex-direction: column; gap: 18px; max-width: 1200px; }
.cf-card { background: var(--surface); border-radius: 18px; box-shadow: inset 0 0 0 1px var(--border); }

/* Icônes au trait dans une tuile bleu clair */
.cf-ico { width: 44px; height: 44px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; background: var(--sv-b50, #EAF2FF); color: #1668D8; }
.cf-ico svg { width: 22px; height: 22px; }
.cf-i { width: 16px; height: 16px; flex-shrink: 0; color: var(--muted); }

/* Profil */
.cf-profile { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; padding: 22px 24px; }
.cf-avatar { width: 64px; height: 64px; border-radius: 18px; background: #1668D8; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 800; flex-shrink: 0; }
.cf-id { flex: 1 1 280px; min-width: 0; }
.cf-name-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.cf-name { margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -.02em; color: var(--text); overflow-wrap: anywhere; }
.cf-badge { display: inline-flex; align-items: center; gap: 5px; height: 26px; padding: 0 10px; border-radius: 999px; background: var(--sv-b50, #EAF2FF); color: #1668D8; font-size: 12.5px; font-weight: 800; }
.cf-badge svg { width: 14px; height: 14px; }
.cf-contacts { display: flex; flex-wrap: wrap; gap: 8px 18px; margin-top: 8px; }
.cf-contact { display: inline-flex; align-items: center; gap: 6px; font-size: 14px; color: var(--text-2); text-decoration: none; min-width: 0; overflow-wrap: anywhere; }
a.cf-contact:hover { color: #1668D8; }
.cf-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.cf-btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 42px; padding: 0 16px; border-radius: 999px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); color: var(--text); font-size: 14px; font-weight: 700; text-decoration: none; white-space: nowrap; }
.cf-btn:hover { box-shadow: inset 0 0 0 1px #1F7CF6; color: #1668D8; }
.cf-btn svg { width: 17px; height: 17px; }

/* Chiffres (calculés à partir des commandes) */
.cf-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.cf-kpi { display: flex; align-items: center; gap: 14px; padding: 18px; min-width: 0; }
.cf-kpi-lbl { font-size: 12px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--muted); }
.cf-kpi-val { margin-top: 3px; font-size: 22px; font-weight: 800; letter-spacing: -.02em; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cf-kpi-val small { font-size: 13px; font-weight: 700; color: var(--muted); letter-spacing: 0; }
.cf-kpi-val.is-txt { font-size: 16px; }

/* Commandes */
.cf-orders { padding: 6px 0 4px; }
.cf-orders-hd { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; padding: 14px 20px 12px; }
.cf-orders-hd h2 { margin: 0; font-size: 17px; font-weight: 800; color: var(--text); flex: 1 1 auto; }
.cf-orders-hd span { font-size: 13px; color: var(--muted); }
.cf-list { list-style: none; margin: 0; padding: 0 12px 8px; display: flex; flex-direction: column; gap: 8px; }
.cf-order { display: flex; align-items: center; gap: 14px; padding: 14px 16px; border-radius: 14px; box-shadow: inset 0 0 0 1px var(--border); color: inherit; text-decoration: none; transition: box-shadow 160ms; }
.cf-order:hover { box-shadow: inset 0 0 0 1px #9DB4D6, 0 6px 18px -10px rgba(11,30,61,.25); }
.cf-order:focus-visible { outline: 3px solid #7DB4FF; outline-offset: 2px; }
.cf-order-main { flex: 1; min-width: 0; }
.cf-order-ref { font-size: 13px; color: var(--muted); }
.cf-order-ref b { color: var(--text); font-weight: 800; }
.cf-order-prods { margin-top: 3px; font-size: 15px; font-weight: 700; color: var(--text); overflow-wrap: anywhere; }
.cf-order-prods em { font-style: normal; color: var(--muted); font-weight: 600; }
.cf-order-dest { display: flex; align-items: center; gap: 6px; margin-top: 4px; font-size: 13px; color: var(--text-2); overflow-wrap: anywhere; }
.cf-order-side { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex-shrink: 0; }
.cf-amount { font-size: 16px; font-weight: 800; color: var(--text); white-space: nowrap; }
.cf-amount small { font-size: 12px; color: var(--muted); font-weight: 700; }
.cf-chev { width: 18px; height: 18px; color: var(--muted); flex-shrink: 0; }

/* Étape de la commande : mêmes noms que la page Commandes, teintes douces */
.cf-pill { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 999px; font-size: 12.5px; font-weight: 800; white-space: nowrap; background: #F1F4F9; color: #45455E; }
.cf-pill.is-amber  { background: #FEF3E0; color: #92400E; }
.cf-pill.is-blue, .cf-pill.is-sky { background: #EAF2FF; color: #1260C9; }
.cf-pill.is-green  { background: #E7F6EC; color: #15803D; }
.cf-pill.is-red, .cf-pill.is-brown { background: #FDECEC; color: #B91C1C; }
.cf-pill.is-violet { background: #F1EDFE; color: #5B3FC4; }
[data-theme="dark"] .cf-pill { background: #1B2A45; color: #C3CEE0; }
[data-theme="dark"] .cf-pill.is-amber  { background: #2E2208; color: #FBBF24; }
[data-theme="dark"] .cf-pill.is-blue, [data-theme="dark"] .cf-pill.is-sky { background: #13284B; color: #7DB4FF; }
[data-theme="dark"] .cf-pill.is-green  { background: #0F2A1C; color: #4ADE80; }
[data-theme="dark"] .cf-pill.is-red, [data-theme="dark"] .cf-pill.is-brown { background: #2A1214; color: #F87171; }
[data-theme="dark"] .cf-pill.is-violet { background: #231A45; color: #B9A6FF; }
[data-theme="dark"] .cf-ico, [data-theme="dark"] .cf-badge { color: #7DB4FF; }

.cf-empty { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 36px 20px; text-align: center; color: var(--muted); font-size: 14px; }
.cf-empty b { color: var(--text); font-size: 15px; }
.cf-pages { padding: 6px 20px 14px; }

@media (max-width: 1100px) { .cf-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 640px) {
    .cf-top { padding: 10px 16px !important; }
    .cf-top-title { display: none; }
    .cf-content { padding: 16px 16px 40px; gap: 14px; }
    .cf-profile { padding: 18px 16px; gap: 14px; }
    .cf-avatar { width: 52px; height: 52px; font-size: 18px; border-radius: 14px; }
    .cf-name { font-size: 20px; }
    .cf-actions { width: 100%; }
    .cf-actions .cf-btn { flex: 1 1 0; }
    .cf-kpis { gap: 10px; }
    .cf-kpi { flex-direction: column; align-items: flex-start; gap: 10px; padding: 14px; }
    .cf-ico { width: 38px; height: 38px; }
    .cf-kpi-val { font-size: 18px; }
    .cf-list { padding: 0 8px 6px; }
    .cf-orders-hd { padding: 12px 14px 10px; }
    .cf-order { padding: 12px; gap: 10px; align-items: flex-start; }
    .cf-chev { display: none; }
}
</style>
@endpush

@section('content')
@php
    $fmt = fn ($n) => number_format((float) ($n ?? 0), 0, ',', ' ');
    $initials = collect(preg_split('/\s+/', trim((string) $user->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: 'C';
    $nb = (int) ($stats->nb_commandes ?? 0);
    $panierMoyen = $nb > 0 ? round($stats->total_depense / $nb) : 0;
    $bestPhone   = $latestPhone ?: $user->phone;
    $bestAddress = $latestAddress ?: $user->address;
    $wa = \App\Support\OrderBoard::intl($bestPhone);
    $stages = \App\Support\OrderBoard::STAGES;

    /* Icônes au trait (même famille que le menu vendeur) */
    $svg = fn (string $d, string $cls = 'cf-i') => '<svg class="' . $cls . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $d . '"/></svg>';
    $I = [
        'mail'   => 'M4 6h16v12H4zM4 7l8 6 8-6',
        'phone'  => 'M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2',
        'pin'    => 'M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21zM12 12a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5',
        'cal'    => 'M4 6h16v14H4zM4 10h16M8 3v4M16 3v4',
        'trophy' => 'M8 4h8v5a4 4 0 0 1-8 0zM8 6H5a3 3 0 0 0 3 4M16 6h3a3 3 0 0 1-3 4M12 13v4M8 20h8M10 17h4',
        'wallet' => 'M4 7h14a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4zM4 7l11-3v3M16 13.5h.01',
        'box'    => 'M12 3 4 7v10l8 4 8-4V7zM4 7l8 4 8-4M12 11v10',
        'cart'   => 'M3 4h2l2.4 11h10.2L20 7H6.2M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2M17 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2',
        'clock'  => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM12 7v5l3 2',
        'chat'   => 'M4 20l1.4-4A8 8 0 1 1 8 19zM9 10h6M9 13h4',
        'chev'   => 'm9 6 6 6-6 6',
        'back'   => 'M15 6l-6 6 6 6',
    ];
@endphp
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'clients'])

    <main class="main vs-main">
        <div class="topbar cf-top">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <a href="{{ route('boutique.clients.index') }}" class="cf-back">{!! $svg($I['back']) !!} Clients</a>
            <span class="cf-top-title">Fiche client</span>
        </div>

        <div class="cf-content">
            {{-- ══ Profil ══ --}}
            <section class="cf-card cf-profile" aria-label="Client">
                <div class="cf-avatar" aria-hidden="true">{{ $initials }}</div>
                <div class="cf-id">
                    <div class="cf-name-row">
                        <h1 class="cf-name">{{ $user->name }}</h1>
                        @if($isTop)<span class="cf-badge">{!! $svg($I['trophy'], '') !!} Top client ce mois</span>@endif
                    </div>
                    <div class="cf-contacts">
                        @if($bestPhone)<a href="tel:{{ $bestPhone }}" class="cf-contact">{!! $svg($I['phone']) !!} {{ $bestPhone }}</a>@endif
                        @if($user->email)<span class="cf-contact">{!! $svg($I['mail']) !!} {{ $user->email }}</span>@endif
                        @if($bestAddress)<span class="cf-contact">{!! $svg($I['pin']) !!} {{ $bestAddress }}</span>@endif
                        @if($stats->premiere_cmd)<span class="cf-contact">{!! $svg($I['cal']) !!} Client depuis {{ \Carbon\Carbon::parse($stats->premiere_cmd)->translatedFormat('F Y') }}</span>@endif
                    </div>
                </div>
                @if($bestPhone)
                <div class="cf-actions">
                    <a href="tel:{{ $bestPhone }}" class="cf-btn">{!! $svg($I['phone'], '') !!} Appeler</a>
                    @if($wa)<a href="https://wa.me/{{ $wa }}" class="cf-btn" target="_blank" rel="noopener">{!! $svg($I['chat'], '') !!} WhatsApp</a>@endif
                </div>
                @endif
            </section>

            {{-- ══ Chiffres du client dans cette boutique (calculés) ══ --}}
            <section class="cf-kpis" aria-label="Chiffres du client">
                <div class="cf-card cf-kpi">
                    <span class="cf-ico">{!! $svg($I['wallet'], '') !!}</span>
                    <div style="min-width:0"><div class="cf-kpi-lbl">Total dépensé</div><div class="cf-kpi-val">{{ $fmt($stats->total_depense) }} <small>{{ $devise }}</small></div></div>
                </div>
                <div class="cf-card cf-kpi">
                    <span class="cf-ico">{!! $svg($I['box'], '') !!}</span>
                    <div style="min-width:0"><div class="cf-kpi-lbl">Commandes</div><div class="cf-kpi-val">{{ $nb }}</div></div>
                </div>
                <div class="cf-card cf-kpi">
                    <span class="cf-ico">{!! $svg($I['cart'], '') !!}</span>
                    <div style="min-width:0"><div class="cf-kpi-lbl">Panier moyen</div><div class="cf-kpi-val">{{ $fmt($panierMoyen) }} <small>{{ $devise }}</small></div></div>
                </div>
                <div class="cf-card cf-kpi">
                    <span class="cf-ico">{!! $svg($I['clock'], '') !!}</span>
                    <div style="min-width:0"><div class="cf-kpi-lbl">Dernière commande</div><div class="cf-kpi-val is-txt">{{ $stats->derniere_cmd ? \Carbon\Carbon::parse($stats->derniere_cmd)->diffForHumans() : '—' }}</div></div>
                </div>
            </section>

            {{-- ══ Commandes du client, comme sur la page Commandes ══ --}}
            <section class="cf-card cf-orders" aria-labelledby="cfOrdersT">
                <div class="cf-orders-hd">
                    <h2 id="cfOrdersT">Ses commandes</h2>
                    <span>{{ $nb }} commande{{ $nb > 1 ? 's' : '' }} · {{ $shop->name }}</span>
                </div>

                @if($commandes->isEmpty())
                <div class="cf-empty">
                    {!! $svg($I['box'], 'cf-i') !!}
                    <b>Aucune commande</b>
                    <span>Ce client n'a pas encore commandé dans votre boutique.</span>
                </div>
                @else
                <ul class="cf-list">
                    @foreach($commandes as $order)
                    @php
                        $stage = $stages[\App\Support\OrderBoard::stageOf($order)] ?? ['—', null, null, 'grey'];
                        $items = $order->items ?? collect();
                        $names = $items->take(2)->map(fn ($it) => e($it->product->name ?? 'Produit supprimé') . ' <em>×' . (int) $it->quantity . '</em>')->implode(', ');
                    @endphp
                    <li>
                        <a class="cf-order" href="{{ route('boutique.orders.index', ['q' => $order->id, 'commande' => $order->id]) }}" aria-label="Ouvrir la commande n° {{ $order->id }}">
                            <div class="cf-order-main">
                                <div class="cf-order-ref"><b>#{{ $order->id }}</b> · {{ $order->created_at->translatedFormat('j M Y') }} à {{ $order->created_at->format('H:i') }}</div>
                                <div class="cf-order-prods">
                                    @if($items->isNotEmpty()){!! $names !!}@if($items->count() > 2) <em>+{{ $items->count() - 2 }} autre{{ $items->count() - 2 > 1 ? 's' : '' }}</em>@endif
                                    @else<em>Aucun article</em>@endif
                                </div>
                                @if($order->delivery_destination)<div class="cf-order-dest">{!! $svg($I['pin']) !!} {{ $order->delivery_destination }}</div>@endif
                            </div>
                            <div class="cf-order-side">
                                <span class="cf-amount">{{ $fmt($order->total) }} <small>{{ $devise }}</small></span>
                                <span class="cf-pill is-{{ $stage[3] }}">{{ $stage[0] }}</span>
                            </div>
                            {!! $svg($I['chev'], 'cf-chev') !!}
                        </a>
                    </li>
                    @endforeach
                </ul>
                @if($commandes->hasPages())
                <div class="cf-pages">{{ $commandes->links() }}</div>
                @endif
                @endif
            </section>
        </div>
    </main>
</div>
@endsection

{{-- Thème bleu « espace vendeur », chargé après les styles de la page --}}
@push('styles')
@include('boutique._partials.sv-theme')
@endpush
