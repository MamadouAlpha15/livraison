{{--
    resources/views/boutique/profit.blade.php
    Route     : GET /boutique/benefices (boutique.profit.index), PUT /boutique/benefices (boutique.profit.update)
                → Boutique\ProfitController
    Rôle      : Bénéfice réel par produit sur un mois : chiffre d'affaires livré − prix d'achat − livraison − retours − pub.
                ?couts=1 : mode pour renseigner les prix d'achat, la pub du mois et les frais de livraison / retour.
    Variables : $shop, $month, $months, $report, $editing, $all, $ads, $devise
--}}
@extends('layouts.app')
@section('title', 'Bénéfices · ' . $shop->name)
@php
    $bodyClass = 'is-dashboard';
    $t = $report['total'];
    $n = fn ($v) => number_format((int) $v, 0, ',', ' ');
    $neg = fn ($v) => (int) $v ? '−' . $n($v) : '0'; // un coût : « −15 000 », ou « 0 » s'il n'y en a pas
    $thumb = fn ($img) => $img ? (\App\Services\ImageOptimizer::url($img, 'thumb') ?? asset('storage/' . $img)) : null;
@endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
html, body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; -webkit-font-smoothing: antialiased; }
.pf { padding: 18px 28px 80px; width: 100%; max-width: 1200px; }
.pf-head { display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
.pf-head > div:first-child { flex: 1; min-width: 240px; }
.pf-head h1 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -.02em; }
.pf-head p { margin: 4px 0 0; color: var(--text-2); font-size: 13.5px; max-width: 620px; }
.pf-tools { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.pf-sel, .pf-in { min-height: 40px; padding: 0 12px; border: 0; border-radius: 10px; background: var(--surface); color: var(--text); font: 600 13.5px var(--font); box-shadow: inset 0 0 0 1px var(--border); }
.pf-in { width: 100%; min-width: 0; text-align: right; font-variant-numeric: tabular-nums; }
.pf-in:focus, .pf-sel:focus { outline: none; box-shadow: inset 0 0 0 2px var(--sv-blue, #1F7CF6); }
.pf-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 40px; padding: 0 16px; border: 0; border-radius: 10px; font: 700 13.5px var(--font); cursor: pointer; text-decoration: none; background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1px var(--border); white-space: nowrap; }
.pf-btn.is-main { background: var(--sv-btn, #1F7CF6); color: #fff; box-shadow: none; }
.pf-flash { padding: 12px 14px; border-radius: 12px; font-weight: 700; font-size: 13.5px; margin-bottom: 12px; }
.pf-flash.is-ok { background: var(--sv-ok50, #F0FDF4); color: var(--sv-ok, #15803D); } .pf-flash.is-err { background: var(--sv-err50, #FEF2F2); color: var(--sv-err, #DC2626); }
.pf-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
.pf-kpi { padding: 14px 16px; border-radius: 14px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); }
.pf-kpi small { display: block; color: var(--text-2); font-size: 12px; font-weight: 700; }
.pf-kpi b { display: block; margin-top: 4px; font-size: 22px; letter-spacing: -.02em; font-variant-numeric: tabular-nums; }
.pf-kpi span { font-size: 12px; color: var(--text-2); font-weight: 600; }
.pf-kpi.is-main { box-shadow: inset 0 0 0 2px var(--sv-blue, #1F7CF6); }
.is-pos { color: var(--sv-ok, #15803D); } .is-neg { color: var(--sv-err, #DC2626); }
.pf-note { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; border-radius: 12px; background: #FFFBEB; color: #92400E; font-size: 13.5px; font-weight: 600; margin-bottom: 12px; }
.pf-note a { color: inherit; font-weight: 800; }
.pf-card { border-radius: 14px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); overflow: hidden; margin-bottom: 14px; }
.pf-card-hd { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding: 14px 16px; border-bottom: 1px solid var(--border); }
.pf-card-hd h2 { margin: 0; font-size: 15px; }
.pf-card-hd small { color: var(--text-2); font-size: 12.5px; }
.pf-scroll { overflow-x: auto; }
.pf-t { width: 100%; border-collapse: collapse; font-size: 13.5px; min-width: 860px; }
.pf-t th, .pf-t td { padding: 11px 12px; text-align: right; border-bottom: 1px solid var(--border); white-space: nowrap; font-variant-numeric: tabular-nums; }
.pf-t th { color: var(--text-2); font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; background: var(--bg); }
.pf-t th:first-child, .pf-t td:first-child { text-align: left; white-space: normal; min-width: 220px; }
.pf-t tbody tr:last-child td { border-bottom: 0; }
.pf-t tfoot td { font-weight: 800; background: var(--bg); border-top: 1px solid var(--border); border-bottom: 0; }
.pf-prod { display: flex; align-items: center; gap: 10px; }
.pf-prod img, .pf-prod i { width: 36px; height: 36px; border-radius: 9px; object-fit: cover; flex-shrink: 0; background: var(--bg); box-shadow: inset 0 0 0 1px var(--border); }
.pf-prod b { display: block; font-size: 13.5px; } .pf-prod small { display: block; color: var(--text-2); font-size: 12px; }
.pf-miss { display: inline-block; padding: 2px 8px; border-radius: 999px; background: #FFFBEB; color: #B45309; font-size: 11px; font-weight: 800; }
.pf-muted { color: var(--text-2); }
.pf-empty { padding: 34px 16px; text-align: center; color: var(--text-2); }
.pf-empty b { display: block; color: var(--text); font-size: 15px; margin-bottom: 4px; }
.pf-set { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; padding: 16px; }
.pf-set label { display: block; font-weight: 700; font-size: 13.5px; margin-bottom: 6px; }
.pf-set small { display: block; color: var(--text-2); font-size: 12px; margin-top: 5px; }
.pf-et td { vertical-align: middle; }
.pf-et .pf-in { max-width: 170px; margin-left: auto; display: block; }
.pf-sticky { position: sticky; bottom: 0; display: flex; justify-content: flex-end; gap: 8px; padding: 12px 16px; background: var(--surface); border-top: 1px solid var(--border); }
.pf-how { color: var(--text-2); font-size: 12.5px; padding: 0 2px; }
@media (max-width: 900px) { .pf { padding: 14px 14px 80px; } .pf-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } .pf-set { grid-template-columns: 1fr; } }
</style>
@include('boutique._partials.sv-theme')
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'profit'])
    <main class="main vs-main">
        <div class="pf">
            <div class="pf-head">
                <div>
                    <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu" style="margin-bottom:8px">☰</button>
                    <h1>Bénéfices</h1>
                    <p>Ce que chaque produit te rapporte vraiment, une fois payés l’achat, la livraison, les retours et la pub. Calculé sur les commandes livrées du mois.</p>
                </div>
                <form class="pf-tools" method="GET" action="{{ route('boutique.profit.index') }}">
                    @if($editing)<input type="hidden" name="couts" value="1">@endif
                    <label class="sr-only" for="pfMonth" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)">Mois</label>
                    <select class="pf-sel" id="pfMonth" name="mois" onchange="this.form.submit()">
                        @foreach($months as $k => $label)<option value="{{ $k }}" @selected($k === $month)>{{ ucfirst($label) }}</option>@endforeach
                    </select>
                    @if($editing)
                        <a class="pf-btn" href="{{ route('boutique.profit.index', ['mois' => $month]) }}">Voir les bénéfices</a>
                    @else
                        <a class="pf-btn is-main" href="{{ route('boutique.profit.index', ['mois' => $month, 'couts' => 1]) }}">Renseigner mes coûts</a>
                    @endif
                </form>
            </div>

            @if(session('success'))<p class="pf-flash is-ok" role="status">{{ session('success') }}</p>@endif
            @if($errors->any())<p class="pf-flash is-err" role="alert">{{ $errors->first() }}</p>@endif

            @if(!$editing)
                <div class="pf-kpis">
                    <div class="pf-kpi"><small>Chiffre d’affaires livré</small><b>{{ $n($t['revenue']) }}</b><span>{{ $devise }} · {{ $report['orders'] - $report['unassigned']['orders'] }} commande{{ $report['orders'] - $report['unassigned']['orders'] > 1 ? 's' : '' }} livrée{{ $report['orders'] - $report['unassigned']['orders'] > 1 ? 's' : '' }}</span></div>
                    <div class="pf-kpi"><small>Coûts</small><b>{{ $n($t['costs']) }}</b><span>achat, livraison, retours, pub</span></div>
                    <div class="pf-kpi is-main"><small>Bénéfice net</small><b class="{{ $t['profit'] >= 0 ? 'is-pos' : 'is-neg' }}">{{ $t['profit'] < 0 ? '−' : '' }}{{ $n(abs($t['profit'])) }}</b><span>{{ $devise }}{{ $report['missing'] ? ' · incomplet' : '' }}</span></div>
                    <div class="pf-kpi"><small>Marge</small><b class="{{ ($t['margin'] ?? 0) >= 0 ? '' : 'is-neg' }}">{{ $t['margin'] === null ? '—' : $t['margin'] . ' %' }}</b><span>du chiffre d’affaires</span></div>
                </div>

                @if($report['missing'] > 0)
                <p class="pf-note" role="status">⚠ {{ $report['missing'] }} produit{{ $report['missing'] > 1 ? 's vendus n’ont' : ' vendu n’a' }} pas de prix d’achat : le bénéfice affiché est trop haut. <a href="{{ route('boutique.profit.index', ['mois' => $month, 'couts' => 1]) }}">Ajouter les prix d’achat</a></p>
                @endif
                @if($report['unassigned']['orders'] > 0)
                <p class="pf-note" style="background:var(--bg);color:var(--text-2)">ℹ {{ $report['unassigned']['orders'] }} commande{{ $report['unassigned']['orders'] > 1 ? 's livrées n’ont' : ' livrée n’a' }} pas de détail des produits ({{ $n($report['unassigned']['amount']) }} {{ $devise }}) : {{ $report['unassigned']['orders'] > 1 ? 'elles ne sont pas comptées' : 'elle n’est pas comptée' }} ici, car on ne sait pas quels produits ont été vendus.</p>
                @endif
                @if($shop->delivery_cost === null && $report['orders'] > 0)
                <p class="pf-note">ℹ Tu n’as pas indiqué ce que te coûte une livraison : elle est comptée à 0. <a href="{{ route('boutique.profit.index', ['mois' => $month, 'couts' => 1]) }}">Renseigner les frais</a></p>
                @endif

                <section class="pf-card" aria-labelledby="pfTH">
                    <div class="pf-card-hd"><h2 id="pfTH">Par produit · {{ $months[$month] ?? $month }}</h2><small>Du plus rentable au moins rentable</small></div>
                    @if(empty($report['rows']))
                        <div class="pf-empty"><b>Aucune vente livrée ce mois-ci</b>Les bénéfices apparaissent dès qu’une commande passe à « Livrée » dans Commandes.</div>
                    @else
                    <div class="pf-scroll">
                        <table class="pf-t">
                            <thead><tr><th scope="col">Produit</th><th scope="col">Vendus</th><th scope="col">Chiffre d’affaires</th><th scope="col">Achat</th><th scope="col">Livraison</th><th scope="col">Retours</th><th scope="col">Pub</th><th scope="col">Bénéfice</th><th scope="col">Marge</th></tr></thead>
                            <tbody>
                            @foreach($report['rows'] as $r)
                                <tr>
                                    <td><div class="pf-prod">@if($src = $thumb($r['image']))<img src="{{ $src }}" alt="" width="36" height="36" loading="lazy">@else<i></i>@endif<span><b>{{ $r['name'] }}</b><small>@if($r['cost'] !== null)Achat {{ $n($r['cost']) }} · vente {{ $n($r['price']) }}@else<span class="pf-miss">Prix d’achat manquant</span>@endif</small></span></div></td>
                                    <td>{{ $r['qty'] }}@if($r['returned'])<div class="pf-muted" style="font-size:11.5px">{{ $r['returned'] }} retour{{ $r['returned'] > 1 ? 's' : '' }}</div>@endif</td>
                                    <td>{{ $n($r['revenue']) }}</td>
                                    <td class="pf-muted">{{ $r['cost'] === null && $r['qty'] ? '?' : $neg($r['buy']) }}</td>
                                    <td class="pf-muted">{{ $neg($r['delivery']) }}</td>
                                    <td class="pf-muted">{{ $neg($r['returns']) }}</td>
                                    <td class="pf-muted">{{ $neg($r['ads']) }}</td>
                                    <td><b class="{{ $r['profit'] >= 0 ? 'is-pos' : 'is-neg' }}">{{ $r['profit'] < 0 ? '−' : '' }}{{ $n(abs($r['profit'])) }}</b>@unless($r['complete'])<div class="pf-miss" style="margin-top:3px">incomplet</div>@endunless</td>
                                    <td>{{ $r['margin'] === null ? '—' : $r['margin'] . ' %' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot><tr><td>Total</td><td>{{ $t['qty'] }}</td><td>{{ $n($t['revenue']) }}</td><td>{{ $neg($t['buy']) }}</td><td>{{ $neg($t['delivery']) }}</td><td>{{ $neg($t['returns']) }}</td><td>{{ $neg($t['ads']) }}</td><td class="{{ $t['profit'] >= 0 ? 'is-pos' : 'is-neg' }}">{{ $t['profit'] < 0 ? '−' : '' }}{{ $n(abs($t['profit'])) }}</td><td>{{ $t['margin'] === null ? '—' : $t['margin'] . ' %' }}</td></tr></tfoot>
                        </table>
                    </div>
                    @endif
                </section>
                <p class="pf-how">Comment c’est calculé : chiffre d’affaires des commandes livrées (remises déduites) − prix d’achat × quantité − frais de livraison par commande livrée − frais par commande retournée − pub du mois. Quand une commande contient plusieurs produits, ses frais sont partagés selon la part de chaque produit. Montants en {{ $devise }}.</p>
            @else
                {{-- Mode « coûts » --}}
                <form method="POST" action="{{ route('boutique.profit.update') }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="mois" value="{{ $month }}">
                    <section class="pf-card" aria-labelledby="pfFH">
                        <div class="pf-card-hd"><h2 id="pfFH">Tes frais par commande</h2><small>Ce que tu paies toi-même, en {{ $devise }}</small></div>
                        <div class="pf-set">
                            <div><label for="pfDel">Une livraison te coûte</label><input class="pf-in" style="text-align:left" id="pfDel" name="delivery_cost" type="number" inputmode="numeric" min="0" step="1" placeholder="0" value="{{ old('delivery_cost', $shop->delivery_cost) }}"><small>Laisse 0 si c’est le client qui paie la livraison.</small></div>
                            <div><label for="pfRet">Un retour te coûte</label><input class="pf-in" style="text-align:left" id="pfRet" name="return_cost" type="number" inputmode="numeric" min="0" step="1" placeholder="0" value="{{ old('return_cost', $shop->return_cost) }}"><small>Quand le client refuse le colis et qu’il revient.</small></div>
                        </div>
                    </section>
                    <section class="pf-card" aria-labelledby="pfCH">
                        <div class="pf-card-hd"><h2 id="pfCH">Prix d’achat et pub · {{ $months[$month] ?? $month }}</h2><small>Le prix d’achat est gardé pour tous les mois · la pub est celle du mois choisi</small></div>
                        @if($all->isEmpty())
                            <div class="pf-empty"><b>Aucun produit</b>Ajoute d’abord des produits à ta boutique.</div>
                        @else
                        <div class="pf-scroll">
                            <table class="pf-t pf-et" style="min-width:620px">
                                <thead><tr><th scope="col">Produit</th><th scope="col">Prix de vente</th><th scope="col">Prix d’achat (1 unité)</th><th scope="col">Pub ce mois</th></tr></thead>
                                <tbody>
                                @foreach($all as $p)
                                    <tr>
                                        <td><div class="pf-prod">@if($src = $thumb($p->image))<img src="{{ $src }}" alt="" width="36" height="36" loading="lazy">@else<i></i>@endif<span><b>{{ $p->name }}</b>@unless($p->is_active)<small>Hors ligne</small>@endunless</span></div></td>
                                        <td class="pf-muted">{{ $n($p->price) }}</td>
                                        <td><input class="pf-in" name="cost[{{ $p->id }}]" type="number" inputmode="numeric" min="0" step="1" placeholder="—" aria-label="Prix d’achat de {{ $p->name }}" value="{{ old('cost.' . $p->id, $p->cost_price) }}"></td>
                                        <td><input class="pf-in" name="ads[{{ $p->id }}]" type="number" inputmode="numeric" min="0" step="1" placeholder="0" aria-label="Pub de {{ $p->name }} ce mois" value="{{ old('ads.' . $p->id, $ads[$p->id] ?? '') }}"></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                        <div class="pf-sticky"><a class="pf-btn" href="{{ route('boutique.profit.index', ['mois' => $month]) }}">Annuler</a><button type="submit" class="pf-btn is-main">Enregistrer et voir les bénéfices</button></div>
                    </section>
                </form>
            @endif
        </div>
    </main>
</div>
@endsection
