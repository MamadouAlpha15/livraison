{{--
    Espace vendeur → Ma boutique → « Pages produit » : réglages communs à toutes les pages produit
    (bandeau, couleur du bouton, badges, étapes, questions fréquentes, frais de livraison par commune).
    Données : Vendeur\ProductPageController@editShop
--}}
@extends('layouts.app')

@section('title', 'Pages produit · ' . $shop->name)

@php
    $bodyClass = 'is-dashboard';
    use App\Support\IconLibrary as Ico;
    use App\Support\PageModels;
    use App\Support\ProductPage;
    $steps = old('steps', $page['steps']);
    $faq = old('faq', $page['faq']);
    $fees = old('delivery_fees', $page['delivery_fees']);
    $badges = old('badges', $page['badges']);
    $accent = old('accent', $page['accent']);
    $tpl = old('template', $page['template']);
@endphp

@push('styles')
@include('boutique._partials.sv-theme')
@include('boutique.followups._styles')
<style>
.ps-swatches { display: flex; flex-wrap: wrap; gap: 10px; }
.ps-sw { position: relative; display: grid; justify-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: var(--text-2); cursor: pointer; }
.ps-sw input { position: absolute; opacity: 0; }
.ps-sw i { width: 46px; height: 46px; border-radius: 14px; box-shadow: inset 0 0 0 1px rgba(0,0,0,.12); }
.ps-sw input:checked + i { outline: 3px solid var(--text); outline-offset: 3px; }
.ps-sw input:focus-visible + i { outline: 3px solid var(--sv-blue); outline-offset: 3px; }
.ps-fees { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; }
.ps-qa { display: grid; grid-template-columns: 1fr 2fr; gap: 8px; }
.ps-tpls { display: grid; grid-template-columns: repeat(auto-fit, minmax(165px, 1fr)); gap: 12px; }
.ps-tpl { position: relative; display: grid; gap: 6px; align-content: start; padding: 16px; border-radius: 16px; background: var(--surface); box-shadow: inset 0 0 0 1.5px var(--border); cursor: pointer; }
.ps-tpl input { position: absolute; opacity: 0; inset: 0; cursor: pointer; }
.ps-tpl:has(input:checked) { box-shadow: inset 0 0 0 2px var(--sv-blue); background: var(--sv-b50); }
.ps-tpl:has(input:focus-visible) { outline: 3px solid var(--sv-blue); outline-offset: 2px; }
.ps-tpl strong { font-size: 16px; display: flex; justify-content: space-between; gap: 8px; align-items: center; }
.ps-tpl em { font-style: normal; font-size: 12px; font-weight: 700; color: var(--sv-btn); }
.ps-tpl span { font-size: 13px; color: var(--text-2); line-height: 1.45; }
.ps-tpl a { position: relative; z-index: 1; justify-self: start; font-size: 13px; font-weight: 700; color: var(--sv-btn); text-decoration: none; margin-top: 4px; }
.ps-tpl a:hover { text-decoration: underline; }
.ps-check { font-size: 12px; font-weight: 800; color: var(--sv-btn); }
@media (max-width: 900px) { .ps-qa { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'products'])

    <main class="main vs-main">
        <div class="topbar cp-top">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <div class="tb-info">
                <h1 class="cp-title">{!! Ico::svg('sliders', '', 22) !!} Pages produit</h1>
                <div class="tb-sub">Réglages communs à toutes tes pages produit. Chaque produit a ensuite ses propres offres et points forts.</div>
            </div>
            @if($sample)<a href="{{ route('client.products.show', $sample) }}" target="_blank" rel="noopener" class="cp-btn cp-btn-line cp-btn-sm">{!! Ico::svg('eye', '', 16) !!} Voir un exemple</a>@endif
        </div>

        <div class="cp-content">
            @if(session('success'))<div class="cp-flash cp-flash-ok" role="status">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="cp-flash cp-flash-err" role="alert">@foreach(collect($errors->all())->unique() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

            <form method="POST" action="{{ route('boutique.product-page.update') }}" class="fu-form">
                @csrf @method('PUT')

                <section class="cp-card fu-sec" aria-labelledby="psTpl">
                    <h2 id="psTpl" class="fu-h2">Modèle de page</h2>
                    <p class="fu-help" style="margin:0">Chaque modèle change l'ordre et la mise en avant des sections. Tu peux en choisir un autre pour un produit précis, depuis la page de ce produit.</p>
                    <div class="ps-tpls">
                        @foreach(ProductPage::TEMPLATES as $key => $t)
                        <label class="ps-tpl">
                            <input type="radio" name="template" value="{{ $key }}" @checked($tpl === $key)>
                            <strong>{{ $t['name'] }} @if($tpl === $key)<span class="ps-check">✓ Choisi</span>@endif</strong>
                            <em>{{ $t['for'] }}</em>
                            <span>{{ $t['pitch'] }}</span>
                            @if($sample)<a href="{{ route('client.products.show', ['product' => $sample, 'modele' => $key]) }}" target="_blank" rel="noopener">Voir l'aperçu →</a>@endif
                        </label>
                        @endforeach
                    </div>
                </section>

                <section class="cp-card fu-sec" aria-labelledby="psLook">
                    <h2 id="psLook" class="fu-h2">Apparence</h2>
                    <div class="fu-field">
                        <label for="psBanner">Bandeau du haut</label>
                        <input id="psBanner" name="banner" class="fu-input" maxlength="120" placeholder="Ex. : Livraison partout à Conakry · Paiement à la livraison" value="{{ old('banner', $page['banner']) }}">
                        <span class="fu-help">Vide : pas de bandeau. Écris seulement ce qui est vrai pour ta boutique.</span>
                    </div>
                    <div class="fu-field">
                        <span class="fu-label">Couleur du bouton « Commander »</span>
                        <div class="ps-swatches">
                            @foreach(ProductPage::ACCENTS as $hex => $name)
                            <label class="ps-sw"><input type="radio" name="accent" value="{{ $hex }}" @checked(strtoupper($accent) === $hex)><i style="background:{{ $hex }}"></i>{{ $name }}</label>
                            @endforeach
                        </div>
                    </div>
                    <div class="fu-field">
                        <span class="fu-label">Badges de confiance</span>
                        <div class="fu-checks">
                            @foreach(ProductPage::BADGES as $key => $b)
                            <label class="fu-pill"><input type="checkbox" name="badges[]" value="{{ $key }}" @checked(in_array($key, $badges, true))><span>{{ $b['label'] }}</span></label>
                            @endforeach
                        </div>
                        <span class="fu-help">« Boutique vérifiée » ne s'affiche que si ta boutique est validée par Shopio.</span>
                    </div>
                    <label class="fu-pill" style="justify-self:start"><input type="checkbox" name="show_offer_button" value="1" @checked(old('show_offer_button', $page['show_offer_button']))><span>Bouton « Faire une offre au vendeur »</span></label>
                    <label class="fu-pill" style="justify-self:start"><input type="checkbox" name="whatsapp" value="1" @checked(old('whatsapp', $page['whatsapp'])) @disabled(blank($shop->phone))><span>Bouton « Une question ? WhatsApp »</span></label>
                    @if(blank($shop->phone))<span class="fu-help">Ajoute le téléphone de ta boutique (Ma boutique) pour activer le bouton WhatsApp.</span>@endif
                    <div class="fu-field">
                        <label for="psGuarantee">Ton engagement (facultatif)</label>
                        <input id="psGuarantee" name="guarantee" class="fu-input" maxlength="200" placeholder="Ex. : Échange sous 3 jours si l'article ne te convient pas" value="{{ old('guarantee', $page['guarantee']) }}">
                        <span class="fu-help">Affiché dans un encadré « Notre engagement ». N'écris que ce que tu fais vraiment.</span>
                    </div>
                </section>

                <section class="cp-card fu-sec" aria-labelledby="psFees">
                    <h2 id="psFees" class="fu-h2">Frais de livraison par commune</h2>
                    <p class="fu-help" style="margin:0">Ajoutés au total affiché au client. Vide : « confirmés par la boutique ».</p>
                    <div class="ps-fees">
                        @foreach(PageModels::COMMUNES as $c)
                        <div class="fu-field"><label for="fee{{ $loop->index }}">{{ $c }} (GNF)</label>
                            <input id="fee{{ $loop->index }}" name="delivery_fees[{{ $c }}]" type="number" min="0" inputmode="numeric" class="fu-input" placeholder="—" value="{{ $fees[$c] ?? '' }}"></div>
                        @endforeach
                    </div>
                </section>

                <section class="cp-card fu-sec" aria-labelledby="psSteps">
                    <h2 id="psSteps" class="fu-h2">« Comment ça se passe »</h2>
                    @for($i = 0; $i < 4; $i++)
                    <div class="fu-field"><label for="st{{ $i }}">Étape {{ $i + 1 }}</label>
                        <input id="st{{ $i }}" name="steps[]" class="fu-input" maxlength="120" value="{{ $steps[$i] ?? '' }}"></div>
                    @endfor
                </section>

                <section class="cp-card fu-sec" aria-labelledby="psFaq">
                    <h2 id="psFaq" class="fu-h2">Questions fréquentes de la boutique</h2>
                    @for($i = 0; $i < ProductPage::MAX_FAQ; $i++)
                    <div class="ps-qa">
                        <label class="sr-only" for="sq{{ $i }}">Question {{ $i + 1 }}</label>
                        <input id="sq{{ $i }}" name="faq[{{ $i }}][q]" class="fu-input" maxlength="160" placeholder="Question" value="{{ $faq[$i]['q'] ?? '' }}">
                        <label class="sr-only" for="sa{{ $i }}">Réponse {{ $i + 1 }}</label>
                        <input id="sa{{ $i }}" name="faq[{{ $i }}][a]" class="fu-input" maxlength="600" placeholder="Réponse" value="{{ $faq[$i]['a'] ?? '' }}">
                    </div>
                    @endfor
                </section>

                <div class="fu-save"><button type="submit" class="cp-btn cp-btn-blue">Enregistrer</button></div>
            </form>
        </div>
    </main>
</div>
@endsection
