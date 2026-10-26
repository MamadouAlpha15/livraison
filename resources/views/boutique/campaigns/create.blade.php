{{--
    Assistant « Nouvelle campagne » : 1 Type → 2 Quoi promouvoir → 3 Objectif (réseaux) → 4 Public (réseaux)
    → 5 Formule et date → 6 Récapitulatif et paiement. Un seul formulaire ; le serveur revérifie tout
    (règles, prix, places) à l'envoi : les montants affichés ici ne sont jamais envoyés.
    Données : Vendeur\AdCampaignController@create — prix et places : @availability
--}}
@extends('layouts.app')

@section('title', 'Nouvelle campagne · ' . $shop->name)

@php
    $bodyClass = 'is-dashboard';
    use App\Support\Campaigns\CampaignPricing as P;
    use App\Support\IconLibrary as Ico;

    $packagesJs = $packages->map(fn ($p) => [
        'code' => $p->code, 'type' => $p->type, 'name' => $p->name, 'maxItems' => $p->max_items,
        'shopWide' => $p->allows_shop_wide, 'durations' => $p->durations(), 'defaultDays' => $p->duration_days,
        'prices' => collect($p->durations())->mapWithKeys(fn ($d) => [$d => P::format($p->priceFor($d))]),
        'where' => $p->details['where'] ?? null, 'promotes' => $p->details['promotes'] ?? null,
        'communes' => \App\Support\Campaigns\CampaignRules::allowsCommunes($p),
    ])->values();

    $old = [
        'type'      => old('type', $prefill['type']),
        'scope'     => old('scope', $prefill['scope']),
        'products'  => array_map('intval', old('products', $prefill['products'])),
        'package'   => old('package', $prefill['package']),
        'days'      => (int) old('days', $prefill['days']),
        'starts_on' => old('starts_on', $prefill['starts_on']),
        'payment'   => old('payment'),
        'objective' => old('objective', $prefill['objective']),
        'communes'  => old('communes', $prefill['targeting']['communes']),
        'age_min'   => (int) old('age_min', $prefill['targeting']['age_min']),
        'age_max'   => (int) old('age_max', $prefill['targeting']['age_max']),
        'genre'     => old('genre', $prefill['targeting']['genre']),
    ];
    $objectiveIcons = ['messages' => 'message', 'visites' => 'eye', 'notoriete' => 'megaphone'];
    $objectiveHelp = [
        'messages'  => 'Les gens t\'écrivent pour demander un prix ou passer commande.',
        'visites'   => 'Les gens viennent voir ta boutique et tes produits sur Shopio.',
        'notoriete' => 'Le plus de monde possible découvre ta boutique.',
    ];
    $serverErrors = collect($errors->get('campaign'))->flatten()
        ->merge(collect($errors->getMessages())->except('campaign')->flatten())->values();
@endphp

@push('styles')
@include('boutique.campaigns._styles')
<style>
/* ── Assistant ── */
.wz { display: grid; gap: 20px; max-width: 860px; }
.wz-progress { display: grid; gap: 10px; }
.wz-progress-top { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; font-size: 13px; color: var(--text-2); }
.wz-progress-top strong { color: var(--text); font-size: 15px; }
.wz-bar { height: 8px; border-radius: 999px; background: var(--border); overflow: hidden; }
.wz-bar span { display: block; height: 100%; border-radius: 999px; background: var(--sv-btn); width: var(--p, 25%); transition: width 320ms var(--sv-e-out); }
.wz-steps { display: flex; gap: 6px; flex-wrap: wrap; list-style: none; margin: 0; padding: 0; }
.wz-steps li { font-size: 12px; font-weight: 700; color: var(--muted); padding: 4px 10px; border-radius: 999px; background: color-mix(in srgb, var(--muted) 10%, transparent); }
.wz-steps li.is-done { color: var(--sv-ok); background: var(--sv-ok50); }
.wz-steps li.is-on { color: #fff; background: var(--sv-btn); }

.wz-panel { padding: 24px; display: grid; gap: 18px; }
.wz-panel[hidden] { display: none; }
.wz-panel h2 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -.01em; }
.wz-hint { margin: -10px 0 0; font-size: 14px; color: var(--text-2); line-height: 1.5; }

/* Cartes à choisir (radio / case) */
.cp-choices { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; }
.cp-choice { position: relative; display: flex; gap: 14px; align-items: flex-start; padding: 18px; border-radius: 16px; background: var(--surface); box-shadow: inset 0 0 0 1.5px var(--border); cursor: pointer; transition: box-shadow 160ms, background-color 160ms; }
.cp-choice:hover { box-shadow: inset 0 0 0 1.5px var(--sv-blue); }
.cp-choice input { position: absolute; opacity: 0; pointer-events: none; }
.cp-choice:has(input:checked) { box-shadow: inset 0 0 0 2px var(--sv-btn); background: var(--sv-b50); }
.cp-choice.is-off { opacity: .55; cursor: not-allowed; }
.cp-choice.is-off:hover { box-shadow: inset 0 0 0 1.5px var(--border); }
.cp-choice-ico { flex: none; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: var(--sv-b50); color: var(--sv-btn); }
.cp-choice-ico.amber { background: var(--sv-amb50); color: var(--sv-amb-ink); }
.cp-choice-name { font-weight: 700; font-size: 15px; }
.cp-choice-desc { font-size: 13px; color: var(--text-2); margin-top: 3px; line-height: 1.45; }
.cp-choice-meta { font-size: 13px; font-weight: 700; color: var(--sv-btn); margin-top: 6px; }
.cp-choice-off { font-size: 12px; font-weight: 700; color: var(--sv-amb-ink); margin-top: 6px; }

/* Produits */
.wz-search { width: 100%; min-height: 44px; padding: 0 16px; border-radius: 999px; border: 0; box-shadow: inset 0 0 0 1.5px var(--border); background: var(--surface); color: var(--text); font: 500 14px var(--font); }
.wz-search:focus { outline: none; box-shadow: inset 0 0 0 2px var(--sv-blue); }
.wz-products { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px; max-height: 420px; overflow-y: auto; padding: 2px; }
.wz-prod { position: relative; display: flex; gap: 12px; align-items: center; padding: 10px; border-radius: 14px; background: var(--surface); box-shadow: inset 0 0 0 1.5px var(--border); cursor: pointer; }
.wz-prod input { position: absolute; opacity: 0; pointer-events: none; }
.wz-prod:has(input:checked) { box-shadow: inset 0 0 0 2px var(--sv-btn); background: var(--sv-b50); }
.wz-prod:has(input:focus-visible) { outline: 3px solid var(--sv-blue); outline-offset: 2px; }
.wz-prod.is-off { opacity: .5; cursor: not-allowed; }
.wz-prod img, .wz-prod-ph { width: 48px; height: 48px; border-radius: 10px; object-fit: cover; flex: none; background: var(--bg); display: flex; align-items: center; justify-content: center; color: var(--muted); }
.wz-prod-name { font-size: 14px; font-weight: 700; overflow-wrap: anywhere; }
.wz-prod-sub { font-size: 12px; color: var(--text-2); margin-top: 2px; }
.wz-prod-check { margin-left: auto; flex: none; width: 22px; height: 22px; border-radius: 7px; box-shadow: inset 0 0 0 1.5px var(--border-dk, var(--border)); display: flex; align-items: center; justify-content: center; color: transparent; }
.wz-prod:has(input:checked) .wz-prod-check { background: var(--sv-btn); box-shadow: none; color: #fff; }
.wz-counter { font-size: 13px; color: var(--text-2); }

/* Durée, date, devis */
.wz-row { display: flex; gap: 16px; flex-wrap: wrap; align-items: flex-end; }
.wz-field { display: grid; gap: 6px; }
.wz-field label, .wz-label { font-size: 13px; font-weight: 700; }
.wz-date { min-height: 44px; padding: 0 14px; border-radius: 12px; border: 0; box-shadow: inset 0 0 0 1.5px var(--border); background: var(--surface); color: var(--text); font: 600 14px var(--font); }
.wz-date:focus { outline: none; box-shadow: inset 0 0 0 2px var(--sv-blue); }
.wz-durations { display: inline-flex; gap: 4px; padding: 4px; border-radius: 999px; box-shadow: inset 0 0 0 1.5px var(--border); background: var(--surface); }
.wz-durations label { min-height: 36px; display: inline-flex; align-items: center; padding: 0 14px; border-radius: 999px; font-weight: 700; font-size: 13px; color: var(--text-2); cursor: pointer; }
.wz-durations input { position: absolute; opacity: 0; pointer-events: none; }
.wz-durations label:has(input:checked) { background: var(--sv-btn); color: #fff; }
.wz-durations label:has(input:focus-visible) { outline: 3px solid var(--sv-blue); outline-offset: 2px; }
.wz-quote { padding: 16px 18px; border-radius: 16px; background: var(--bg); display: grid; gap: 6px; font-size: 14px; }
.wz-quote-price { font-size: 24px; font-weight: 800; font-variant-numeric: tabular-nums; }
.wz-quote-err { color: var(--sv-err); font-weight: 600; }
.wz-full { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 12px 14px; border-radius: 14px; background: var(--sv-amb50); color: var(--sv-amb-ink); font-weight: 700; font-size: 14px; }

/* Public (réseaux sociaux) */
.wz-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.wz-chip { position: relative; display: inline-flex; align-items: center; min-height: 40px; padding: 0 16px; border-radius: 999px; font-size: 14px; font-weight: 700; color: var(--text-2); background: var(--surface); box-shadow: inset 0 0 0 1.5px var(--border); cursor: pointer; }
.wz-chip input { position: absolute; opacity: 0; pointer-events: none; }
.wz-chip:has(input:checked) { background: var(--sv-b50); color: var(--sv-btn); box-shadow: inset 0 0 0 2px var(--sv-btn); }
.wz-chip:has(input:focus-visible) { outline: 3px solid var(--sv-blue); outline-offset: 2px; }
.wz-age { width: 96px; }
.wz-info { padding: 12px 14px; border-radius: 14px; background: var(--sv-b50); color: var(--sv-btn); font-size: 13.5px; font-weight: 600; line-height: 1.45; }

/* Récapitulatif */
.wz-recap { display: grid; gap: 0; border-radius: 16px; box-shadow: inset 0 0 0 1px var(--border); overflow: hidden; }
.wz-recap div { display: flex; justify-content: space-between; gap: 16px; padding: 12px 16px; font-size: 14px; border-top: 1px solid var(--border); }
.wz-recap div:first-child { border-top: 0; }
.wz-recap dt { color: var(--text-2); margin: 0; }
.wz-recap dd { margin: 0; font-weight: 700; text-align: right; overflow-wrap: anywhere; }
.wz-recap .wz-total { background: var(--sv-b50); font-size: 16px; }
.wz-recap .wz-total dd { color: var(--sv-btn); font-size: 18px; }
.wz-rules { font-size: 13px; color: var(--text-2); line-height: 1.55; }
.wz-rules summary { cursor: pointer; font-weight: 700; color: var(--sv-btn); }
.wz-rules ul { margin: 8px 0 0; padding-left: 18px; }
.wz-accept { display: flex; gap: 10px; align-items: flex-start; font-size: 14px; font-weight: 600; cursor: pointer; }
.wz-accept input { width: 20px; height: 20px; margin: 0; accent-color: var(--sv-btn); flex: none; }

/* Barre d'actions : collée en bas sur téléphone */
.wz-actions { display: flex; justify-content: space-between; gap: 12px; }
.wz-actions #btnNext, .wz-actions #btnPay { margin-left: auto; }
@media (max-width: 760px) {
    .wz-panel { padding: 18px; }
    .wz-actions { position: fixed; left: 0; right: 0; bottom: 0; z-index: 35; padding: 12px max(16px, env(safe-area-inset-right, 0px)) max(12px, env(safe-area-inset-bottom, 0px)) max(16px, env(safe-area-inset-left, 0px)); background: var(--surface); box-shadow: 0 -8px 24px rgba(11,30,61,.12); }
    .wz-actions .cp-btn { flex: 1; }
    .wz-steps { display: none; }
    .wz-products { max-height: none; }
}
</style>
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'campaigns'])

    <main class="main vs-main">
        <div class="topbar cp-top">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <div class="tb-info">
                <a href="{{ route('boutique.campaigns.index') }}" class="cp-back">{!! Ico::svg('arrow-left', '', 15) !!} Campagnes</a>
                <h1 class="cp-title">Nouvelle campagne</h1>
            </div>
        </div>

        <div class="cp-content">
            <form method="POST" action="{{ route('boutique.campaigns.store') }}" class="wz" id="wizard" novalidate>
                @csrf
                <input type="hidden" name="type" id="fType" value="{{ $old['type'] }}">

                @if($serverErrors->isNotEmpty())
                <div class="cp-flash cp-flash-err" role="alert">
                    Ta campagne n'a pas pu être créée :
                    <ul>@foreach($serverErrors as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif

                <div class="wz-progress" aria-live="polite">
                    <div class="wz-progress-top"><strong id="stepTitle">Type de campagne</strong><span id="stepCount">Étape 1 sur 4</span></div>
                    <div class="wz-bar"><span id="stepBar"></span></div>
                    <ol class="wz-steps" id="stepList"></ol>
                </div>

                {{-- 1 · Type --}}
                <section class="cp-card wz-panel" data-step="type" data-title="Type de campagne">
                    <h2>Quel type de campagne ?</h2>
                    <div class="cp-choices">
                        <label class="cp-choice">
                            <input type="radio" name="type_choice" value="boost" @checked($old['type'] === 'boost')>
                            <span class="cp-choice-ico">{!! Ico::svg('star', '', 22) !!}</span>
                            <span>
                                <span class="cp-choice-name">Mise en avant Shopio</span>
                                <span class="cp-choice-desc" style="display:block">Tes produits ou ta boutique apparaissent en « Sponsorisé » sur l'accueil, les catégories et la recherche de Shopio. Ça démarre dès le paiement.</span>
                            </span>
                        </label>
                        <label class="cp-choice">
                            <input type="radio" name="type_choice" value="social" @checked($old['type'] === 'social')>
                            <span class="cp-choice-ico amber">{!! Ico::svg('megaphone', '', 22) !!}</span>
                            <span>
                                <span class="cp-choice-name">Campagne réseaux sociaux</span>
                                <span class="cp-choice-desc" style="display:block">L'équipe Shopio crée les visuels et diffuse ta pub sur Facebook, Instagram et TikTok. Elle commence au plus tôt dans 2 jours.</span>
                            </span>
                        </label>
                    </div>
                </section>

                {{-- 2 · Quoi promouvoir --}}
                <section class="cp-card wz-panel" data-step="what" data-title="Quoi promouvoir" hidden>
                    <h2>Que veux-tu promouvoir ?</h2>
                    <div class="cp-choices">
                        <label class="cp-choice">
                            <input type="radio" name="scope" value="products" @checked($old['scope'] === 'products')>
                            <span class="cp-choice-ico">{!! Ico::svg('package', '', 22) !!}</span>
                            <span><span class="cp-choice-name">Des produits</span><span class="cp-choice-desc" style="display:block" data-for="boost">Un à trois produits de ton catalogue.</span><span class="cp-choice-desc" style="display:block" data-for="social">Jusqu'à cinq produits mis en avant dans la pub.</span></span>
                        </label>
                        <label class="cp-choice">
                            <input type="radio" name="scope" value="shop" @checked($old['scope'] === 'shop')>
                            <span class="cp-choice-ico">{!! Ico::svg('store', '', 22) !!}</span>
                            <span><span class="cp-choice-name">Toute la boutique</span><span class="cp-choice-desc" style="display:block" data-for="boost">Ta boutique dans le carrousel « Boutiques à la une » de l'accueil.</span><span class="cp-choice-desc" style="display:block" data-for="social">La pub présente ta boutique et renvoie vers elle.</span></span>
                        </label>
                    </div>

                    <div id="productPicker" class="wz-field" hidden>
                        <div class="wz-row" style="justify-content:space-between;align-items:center">
                            <span class="wz-label" id="prodMaxLbl">Choisis jusqu'à 3 produits</span>
                            <span class="wz-counter" id="prodCounter" aria-live="polite"></span>
                        </div>
                        <input type="search" class="wz-search" id="prodSearch" placeholder="Rechercher un produit" aria-label="Rechercher un produit" autocomplete="off">
                        @if($products->isEmpty())
                        <p class="wz-hint" style="margin:0">Ta boutique n'a pas encore de produit. <a class="cp-link" href="{{ route('products.create') }}">Ajoute un produit</a> pour le promouvoir.</p>
                        @endif
                        <div class="wz-products" id="prodGrid">
                            @foreach($products as $p)
                            <label class="wz-prod {{ $p['promotable'] ? '' : 'is-off' }}" data-name="{{ mb_strtolower($p['name']) }}">
                                <input type="checkbox" name="products[]" value="{{ $p['id'] }}" @checked(in_array($p['id'], $old['products'], true) && $p['promotable']) @disabled(!$p['promotable'])>
                                @if($p['image'])<img src="{{ $p['image'] }}" alt="" loading="lazy">@else<span class="wz-prod-ph">{!! Ico::svg('package', '', 20) !!}</span>@endif
                                <span style="min-width:0">
                                    <span class="wz-prod-name" style="display:block">{{ $p['name'] }}</span>
                                    <span class="wz-prod-sub" style="display:block">{{ $p['promotable'] ? $p['price'] : 'En rupture ou désactivé' }}</span>
                                </span>
                                <span class="wz-prod-check" aria-hidden="true">{!! Ico::svg('check', '', 14) !!}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- 3 · Objectif (réseaux sociaux) --}}
                <section class="cp-card wz-panel" data-step="goal" data-title="Objectif" hidden>
                    <h2>Que veux-tu obtenir ?</h2>
                    <div class="cp-choices">
                        @foreach(\App\Models\AdCampaign::OBJECTIVES as $key => $label)
                        <label class="cp-choice">
                            <input type="radio" name="objective" value="{{ $key }}" @checked($old['objective'] === $key)>
                            <span class="cp-choice-ico">{!! Ico::svg($objectiveIcons[$key], '', 22) !!}</span>
                            <span><span class="cp-choice-name">{{ $label }}</span><span class="cp-choice-desc" style="display:block">{{ $objectiveHelp[$key] }}</span></span>
                        </label>
                        @endforeach
                    </div>
                </section>

                {{-- 4 · Public (réseaux sociaux) --}}
                <section class="cp-card wz-panel" data-step="audience" data-title="Public" hidden>
                    <h2>Qui doit voir ta pub ?</h2>
                    <p class="wz-hint">Par défaut : tout Conakry, de 18 à 45 ans.</p>
                    <fieldset class="wz-field" style="border:0;padding:0;margin:0">
                        <legend class="wz-label" style="margin-bottom:6px">Communes</legend>
                        <div class="wz-chips">
                            @foreach(\App\Models\AdCampaign::COMMUNES as $commune)
                            <label class="wz-chip"><input type="checkbox" name="communes[]" value="{{ $commune }}" @checked(in_array($commune, (array) $old['communes'], true))>{{ $commune }}</label>
                            @endforeach
                        </div>
                        <div class="wz-info" style="margin-top:8px">Le choix des communes est inclus dans la formule Premium. Avec Starter et Pro, ta pub est montrée dans tout Conakry.</div>
                    </fieldset>
                    <div class="wz-row">
                        <div class="wz-field">
                            <label for="ageMin">Âge minimum</label>
                            <input type="number" class="wz-date wz-age" name="age_min" id="ageMin" min="{{ \App\Models\AdCampaign::AGE_MIN }}" max="{{ \App\Models\AdCampaign::AGE_MAX }}" value="{{ $old['age_min'] }}" inputmode="numeric">
                        </div>
                        <div class="wz-field">
                            <label for="ageMax">Âge maximum</label>
                            <input type="number" class="wz-date wz-age" name="age_max" id="ageMax" min="{{ \App\Models\AdCampaign::AGE_MIN }}" max="{{ \App\Models\AdCampaign::AGE_MAX }}" value="{{ $old['age_max'] }}" inputmode="numeric">
                        </div>
                    </div>
                    <fieldset class="wz-field" style="border:0;padding:0;margin:0">
                        <legend class="wz-label" style="margin-bottom:6px">Genre</legend>
                        <div class="wz-chips">
                            @foreach(\App\Models\AdCampaign::GENDERS as $key => $label)
                            <label class="wz-chip"><input type="radio" name="genre" value="{{ $key }}" @checked($old['genre'] === $key)>{{ $label }}</label>
                            @endforeach
                        </div>
                    </fieldset>
                    <p class="wz-quote-err" id="ageErr" hidden style="margin:0">L'âge minimum doit être inférieur ou égal à l'âge maximum (entre {{ \App\Models\AdCampaign::AGE_MIN }} et {{ \App\Models\AdCampaign::AGE_MAX }} ans).</p>
                </section>

                {{-- 5 · Formule et date --}}
                <section class="cp-card wz-panel" data-step="package" data-title="Formule et date" hidden>
                    <h2>Choisis ta formule</h2>
                    <div class="cp-choices" id="packageList">
                        @foreach($packages as $p)
                        <label class="cp-choice" data-package="{{ $p->code }}" data-type="{{ $p->type }}">
                            <input type="radio" name="package" value="{{ $p->code }}" @checked($old['package'] === $p->code)>
                            <span style="min-width:0">
                                <span class="cp-choice-name">{{ $p->name }}</span>
                                <span class="cp-choice-desc" style="display:block">{{ $p->details['promotes'] ?? '' }}{{ isset($p->details['where']) ? ' · ' . $p->details['where'] : '' }}</span>
                                <span class="cp-choice-meta" style="display:block">{{ P::format($p->priceFor($p->duration_days)) }} / {{ $p->duration_days }} jours</span>
                                <span class="cp-choice-off" data-off style="display:none"></span>
                            </span>
                        </label>
                        @endforeach
                    </div>

                    <div class="wz-row">
                        <div class="wz-field">
                            <span class="wz-label" id="durLbl">Durée</span>
                            <div class="wz-durations" id="durations" role="radiogroup" aria-labelledby="durLbl"></div>
                        </div>
                        <div class="wz-field">
                            <label for="startsOn">Date de début</label>
                            <input type="date" class="wz-date" name="starts_on" id="startsOn" value="{{ $old['starts_on'] }}" min="{{ $earliest[$old['type']] ?? $earliest['boost'] }}">
                        </div>
                    </div>

                    <div class="wz-quote" id="quote" aria-live="polite"><span style="color:var(--text-2)">Choisis une formule pour voir le prix.</span></div>
                </section>

                {{-- 6 · Récapitulatif et paiement --}}
                <section class="cp-card wz-panel" data-step="pay" data-title="Récapitulatif et paiement" hidden>
                    <h2>Vérifie et paie</h2>
                    <dl class="wz-recap" id="recap"></dl>

                    <div class="wz-field">
                        <span class="wz-label">Moyen de paiement</span>
                        <div class="cp-choices">
                            <label class="cp-choice">
                                <input type="radio" name="payment" value="mobile_money" @checked($old['payment'] !== 'credit')>
                                <span class="cp-choice-ico">{!! Ico::svg('smartphone', '', 22) !!}</span>
                                <span><span class="cp-choice-name">Mobile Money</span><span class="cp-choice-desc" style="display:block">Orange Money ou MTN, depuis ton téléphone. Confirmation automatique.</span></span>
                            </label>
                            <label class="cp-choice" id="creditChoice">
                                <input type="radio" name="payment" value="credit" @checked($old['payment'] === 'credit')>
                                <span class="cp-choice-ico">{!! Ico::svg('wallet', '', 22) !!}</span>
                                <span>
                                    <span class="cp-choice-name">Crédit Shopio</span>
                                    <span class="cp-choice-desc" style="display:block">Solde disponible : {{ P::format($credit) }}</span>
                                    <span class="cp-choice-off" id="creditOff" style="display:none">Solde insuffisant pour cette campagne</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <details class="wz-rules">
                        <summary>Les règles des campagnes</summary>
                        <ul>
                            <li>La campagne est payée en entier avant de démarrer. Une campagne non payée sous 24 h est supprimée.</li>
                            <li>Tu peux avoir 5 campagnes actives à la fois, et un produit ne peut pas être dans deux mises en avant en même temps.</li>
                            <li>Si un produit promu passe en rupture ou est désactivé, la campagne se met en pause et les jours restants sont reportés. Elle reprend quand le produit revient.</li>
                            <li>Annulation avant le démarrage : remboursement intégral sur ton Crédit Shopio. Après le démarrage : pas de remboursement.</li>
                            <li>Si l'équipe Shopio refuse ta campagne (produit interdit, photo trompeuse), tu es remboursé en entier.</li>
                            <li>Réseaux sociaux : l'équipe Shopio te propose les visuels, tu les valides ou demandes une modification (2 fois au plus). Sans validation sous 5 jours, la campagne est annulée et le budget pub remboursé. Si tu annules après la création des visuels, seul le budget pub est remboursé.</li>
                        </ul>
                    </details>
                    <label class="wz-accept"><input type="checkbox" name="accept_rules" value="1" id="acceptRules" @checked(old('accept_rules'))> J'accepte les règles des campagnes</label>
                </section>

                <input type="hidden" name="days" id="fDays" value="{{ $old['days'] ?: '' }}">

                <div class="wz-actions">
                    <button type="button" class="cp-btn cp-btn-line" id="btnBack">{!! Ico::svg('arrow-left', '', 16) !!} Retour</button>
                    <button type="button" class="cp-btn cp-btn-blue" id="btnNext">Continuer</button>
                    <button type="submit" class="cp-btn cp-btn-blue" id="btnPay" hidden>Payer</button>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const form      = document.getElementById('wizard');
    const PACKAGES  = {!! json_encode($packagesJs) !!};
    const EARLIEST  = {!! json_encode($earliest) !!};
    const CREDIT    = {{ (int) $credit }};
    const AVAIL_URL = {!! json_encode(route('boutique.campaigns.availability')) !!};
    const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content || form.querySelector('[name=_token]').value;
    const MAX_PRODUCTS = t => Math.max(1, ...PACKAGES.filter(p => p.type === t).map(p => p.maxItems));
    const STEPS = { boost: ['type', 'what', 'package', 'pay'], social: ['type', 'what', 'goal', 'audience', 'package', 'pay'] };
    const OBJECTIVES = {!! json_encode(\App\Models\AdCampaign::OBJECTIVES) !!};
    const GENDERS = {!! json_encode(\App\Models\AdCampaign::GENDERS) !!};
    const COMMUNES = {!! json_encode(\App\Models\AdCampaign::COMMUNES) !!};
    const AGE = { min: {{ \App\Models\AdCampaign::AGE_MIN }}, max: {{ \App\Models\AdCampaign::AGE_MAX }} };

    const $  = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => [...r.querySelectorAll(s)];
    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    const state = {
        type: $('#fType').value || 'boost',
        step: 0,
        quote: null,
    };

    const panels = () => STEPS[state.type].map(k => $(`[data-step="${k}"]`));
    const scope = () => $('input[name=scope]:checked')?.value || null;
    const chosen = () => $$('input[name="products[]"]:checked').map(i => +i.value);
    const pkg = () => PACKAGES.find(p => p.code === $('input[name=package]:checked')?.value) || null;

    /* ── Navigation entre les étapes ── */
    function render() {
        const list = panels();
        list.forEach((p, i) => p.hidden = i !== state.step);
        $$('[data-step]').filter(p => !list.includes(p)).forEach(p => p.hidden = true);
        const n = list.length, cur = list[state.step];
        $('#stepTitle').textContent = cur.dataset.title;
        $('#stepCount').textContent = `Étape ${state.step + 1} sur ${n}`;
        $('#stepBar').style.setProperty('--p', `${(state.step + 1) / n * 100}%`);
        $('#stepList').innerHTML = list.map((p, i) => `<li class="${i < state.step ? 'is-done' : i === state.step ? 'is-on' : ''}">${i + 1} · ${esc(p.dataset.title)}</li>`).join('');
        $('#btnBack').hidden = state.step === 0;
        const last = state.step === n - 1;
        $('#btnNext').hidden = last;
        $('#btnPay').hidden = !last;
        if (cur.dataset.step === 'package') { filterPackages(); refreshQuote(); }
        if (last) renderRecap();
        updateNext();
    }

    function canContinue() {
        const step = panels()[state.step].dataset.step;
        if (step === 'type') return !!$('input[name=type_choice]:checked');
        if (step === 'what') return scope() === 'shop' || (scope() === 'products' && chosen().length > 0);
        if (step === 'goal') return !!$('input[name=objective]:checked');
        if (step === 'audience') return agesOk() && $$('input[name="communes[]"]:checked').length > 0;
        if (step === 'package') return !!pkg() && !!state.quote?.ok;
        return true;
    }
    function updateNext() {
        $('#btnNext').disabled = !canContinue();
        const payBtn = $('#btnPay');
        const credit = $('input[name=payment]:checked')?.value === 'credit';
        payBtn.disabled = !$('#acceptRules').checked || !state.quote?.ok || (credit && CREDIT < (state.quote?.price_gnf || 0));
        payBtn.textContent = state.quote?.ok ? `Payer ${state.quote.price}` : 'Payer';
    }

    $('#btnNext').addEventListener('click', () => { if (canContinue()) { state.step++; render(); window.scrollTo({ top: 0, behavior: 'smooth' }); } });
    $('#btnBack').addEventListener('click', () => { if (state.step > 0) { state.step--; render(); } });

    /* ── Étape 1 : type ── */
    function applyType() {
        $('#startsOn').min = EARLIEST[state.type];
        if (!$('#startsOn').value || $('#startsOn').value < EARLIEST[state.type]) $('#startsOn').value = EARLIEST[state.type];
        $$('[data-for]').forEach(el => el.style.display = el.dataset.for === state.type ? 'block' : 'none');
        const max = MAX_PRODUCTS(state.type);
        $('#prodMaxLbl').textContent = `Choisis jusqu'à ${max} produit${max > 1 ? 's' : ''}`;
        // Trop de produits cochés pour ce type : on garde les premiers
        $$('input[name="products[]"]:checked').slice(max).forEach(i => i.checked = false);
        const pk = pkg();
        if (pk && pk.type !== state.type) $('input[name=package]:checked').checked = false;
        syncProducts();
    }
    $$('input[name=type_choice]').forEach(r => r.addEventListener('change', () => {
        state.type = r.value; $('#fType').value = r.value;
        applyType();
    }));

    /* ── Étapes 3 et 4 : objectif et public (réseaux sociaux) ── */
    function agesOk() {
        const a = +$('#ageMin').value, b = +$('#ageMax').value;
        return Number.isInteger(a) && Number.isInteger(b) && a >= AGE.min && b <= AGE.max && a <= b;
    }
    $$('input[name=objective]').forEach(r => r.addEventListener('change', updateNext));
    $$('input[name="communes[]"], input[name=genre]').forEach(r => r.addEventListener('change', updateNext));
    ['#ageMin', '#ageMax'].forEach(id => $(id).addEventListener('input', () => { $('#ageErr').hidden = agesOk(); updateNext(); }));
    function audienceLabel() {
        const p = pkg();
        const picked = $$('input[name="communes[]"]:checked').map(i => i.value);
        const where = (!p || !p.communes || picked.length === COMMUNES.length) ? 'Tout Conakry' : picked.join(', ');
        const g = $('input[name=genre]:checked')?.value || 'tous';
        return `${where} · ${$('#ageMin').value}–${$('#ageMax').value} ans · ${GENDERS[g]}`;
    }

    /* ── Étape 2 : produits ── */
    function syncProducts() {
        $('#productPicker').hidden = scope() !== 'products';
        const n = chosen().length, max = MAX_PRODUCTS(state.type);
        $('#prodCounter').textContent = n ? `${n} sur ${max} choisi${n > 1 ? 's' : ''}` : '';
        // Au-delà du maximum, les autres produits ne sont plus cochables
        $$('input[name="products[]"]').forEach(i => {
            const blocked = i.closest('.wz-prod').classList.contains('is-off');
            i.disabled = blocked || (!i.checked && n >= max);
        });
        state.quote = null;
        updateNext();
    }
    $$('input[name=scope]').forEach(r => r.addEventListener('change', syncProducts));
    $('#prodGrid')?.addEventListener('change', syncProducts);
    $('#prodSearch')?.addEventListener('input', e => {
        const q = e.target.value.trim().toLowerCase();
        $$('.wz-prod').forEach(l => l.hidden = q && !l.dataset.name.includes(q));
    });

    /* ── Étape 5 : formule, durée, date, prix et places ── */
    function offReason(p) {
        if (p.type !== state.type) return 'hide';
        const n = chosen().length;
        if (scope() === 'shop' && !p.shopWide) return 'Pour des produits précis';
        if (scope() === 'products' && p.maxItems === 0) return 'Pour toute la boutique';
        if (scope() === 'products' && n > p.maxItems) return `Jusqu'à ${p.maxItems} produit${p.maxItems > 1 ? 's' : ''}`;
        return null;
    }
    function filterPackages() {
        let firstOk = null;
        $$('#packageList [data-package]').forEach(card => {
            const p = PACKAGES.find(x => x.code === card.dataset.package);
            const why = offReason(p), input = $('input', card), off = $('[data-off]', card);
            card.hidden = why === 'hide';
            const blocked = why && why !== 'hide';
            card.classList.toggle('is-off', !!blocked);
            input.disabled = !!blocked;
            off.style.display = blocked ? 'block' : 'none';
            off.textContent = blocked ? why : '';
            if (blocked && input.checked) input.checked = false;
            if (!why && !firstOk) firstOk = input;
        });
        if (!$('input[name=package]:checked') && firstOk) firstOk.checked = true;
        renderDurations();
    }
    function renderDurations() {
        const p = pkg(), box = $('#durations');
        if (!p) { box.innerHTML = ''; return; }
        let days = +$('#fDays').value;
        if (!p.durations.includes(days)) days = p.durations.includes(p.defaultDays) ? p.defaultDays : p.durations[0];
        $('#fDays').value = days;
        box.innerHTML = p.durations.map(d => `<label><input type="radio" name="days_choice" value="${d}" ${d === days ? 'checked' : ''}>${d} jours</label>`).join('');
    }
    $('#packageList').addEventListener('change', () => { renderDurations(); refreshQuote(); });
    $('#durations').addEventListener('change', e => { $('#fDays').value = e.target.value; refreshQuote(); });
    $('#startsOn').addEventListener('change', refreshQuote);

    let quoteTimer = null, quoteSeq = 0;
    function refreshQuote() {
        state.quote = null; updateNext();
        const p = pkg();
        if (!p || !$('#startsOn').value) return;
        $('#quote').innerHTML = '<span style="color:var(--text-2)">Calcul du prix…</span>';
        clearTimeout(quoteTimer);
        quoteTimer = setTimeout(async () => {
            const seq = ++quoteSeq;
            try {
                const res = await fetch(AVAIL_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ package: p.code, days: +$('#fDays').value, starts_on: $('#startsOn').value, products: scope() === 'products' ? chosen() : [] }),
                });
                const data = await res.json();
                if (seq !== quoteSeq) return;          // une réponse plus récente arrive
                state.quote = res.ok ? data : { ok: false, errors: ['Impossible de calculer le prix. Réessaie.'] };
            } catch (_) {
                state.quote = { ok: false, errors: ['Pas de connexion. Réessaie dans un instant.'] };
            }
            renderQuote(); updateNext();
        }, 250);
    }
    function renderQuote() {
        const q = state.quote, box = $('#quote');
        if (!q) return;
        let html = '';
        if (q.price) {
            html += `<div class="wz-quote-price">${esc(q.price)}</div><div>Du ${esc(q.starts_label)} au ${esc(q.ends_on)}</div>`;
            if (q.ad_budget) html += `<div style="color:var(--text-2)">dont budget pub ${esc(q.ad_budget)} et frais Shopio ${esc(q.fee)}</div>`;
        }
        if (q.full) {
            html += `<div class="wz-full">Complet sur ces dates.${q.full.next ? ` Prochaine date libre : ${esc(q.full.next_label)}` : ''}
                     ${q.full.next ? `<button type="button" class="cp-btn cp-btn-line cp-btn-sm" data-next="${esc(q.full.next)}">Choisir cette date</button>` : ''}</div>`;
        } else if (q.errors?.length) {
            html += q.errors.map(e => `<div class="wz-quote-err">${esc(e)}</div>`).join('');
        }
        box.innerHTML = html;
    }
    $('#quote').addEventListener('click', e => {
        const b = e.target.closest('[data-next]');
        if (b) { $('#startsOn').value = b.dataset.next; refreshQuote(); }
    });

    /* ── Étape 6 : récapitulatif et paiement ── */
    function renderRecap() {
        const p = pkg(), q = state.quote || {};
        const names = $$('input[name="products[]"]:checked').map(i => i.closest('.wz-prod').querySelector('.wz-prod-name').textContent.trim());
        const rows = [
            ['Type', state.type === 'boost' ? 'Mise en avant Shopio' : 'Campagne réseaux sociaux'],
            ['Promotion', scope() === 'shop' ? 'Toute la boutique' : names.join(', ')],
            ['Formule', p ? `${p.name} · ${$('#fDays').value} jours` : ''],
            ['Dates', q.ends_on ? `Du ${q.starts_label} au ${q.ends_on}` : ''],
        ];
        if (state.type === 'social') {
            rows.push(['Objectif', OBJECTIVES[$('input[name=objective]:checked')?.value] || ''], ['Public', audienceLabel()]);
        }
        if (q.ad_budget) rows.push(['Budget pub', q.ad_budget], ['Frais Shopio', q.fee]);
        $('#recap').innerHTML = rows.map(([k, v]) => `<div><dt>${esc(k)}</dt><dd>${esc(v)}</dd></div>`).join('')
            + `<div class="wz-total"><dt>Total à payer</dt><dd>${esc(q.price || '')}</dd></div>`;

        const enough = CREDIT >= (q.price_gnf || 0) && CREDIT > 0;
        const creditInput = $('#creditChoice input');
        creditInput.disabled = !enough;
        $('#creditChoice').classList.toggle('is-off', !enough);
        $('#creditOff').style.display = enough ? 'none' : 'block';
        if (!enough && creditInput.checked) $('input[name=payment][value=mobile_money]').checked = true;
        updateNext();
    }
    $$('input[name=payment]').forEach(r => r.addEventListener('change', updateNext));
    $('#acceptRules').addEventListener('change', updateNext);

    // Les durées choisies passent par le champ caché « days » ; les radios d'affichage ne sont pas envoyés
    form.addEventListener('submit', e => {
        if ($('#btnPay').disabled) { e.preventDefault(); return; }
        $$('input[name=days_choice]').forEach(i => i.disabled = true);
        $$('input[name="products[]"]').forEach(i => { if (scope() !== 'products') i.checked = false; });
        // Objectif et public ne concernent que les réseaux sociaux
        if (state.type !== 'social') $$('[data-step=goal] input, [data-step=audience] input').forEach(i => i.disabled = true);
        $('#btnPay').disabled = true; $('#btnPay').textContent = 'Paiement en cours…';
    });

    /* ── Démarrage : pré-remplissage (« Booster », « Relancer », retour d'erreur) ── */
    if (!$('#startsOn').value) $('#startsOn').value = EARLIEST[state.type];
    if (!$('input[name=type_choice]:checked')) $(`input[name=type_choice][value=${state.type}]`).checked = true;
    applyType();
    const hasErrors = {{ $serverErrors->isNotEmpty() ? 'true' : 'false' }};
    if (hasErrors && pkg()) state.step = panels().length - 2;          // retour sur « Formule » pour corriger
    else if (scope() && (scope() === 'shop' || chosen().length)) {
        const needsGoal = state.type === 'social' && !$('input[name=objective]:checked');
        state.step = !pkg() ? 1 : needsGoal ? STEPS.social.indexOf('goal') : panels().length - 2;
    }
    render();
})();
</script>
@endpush

@push('styles')
@include('boutique._partials.sv-theme')
@endpush
