{{--
    Bandeau défilant + en-tête du modèle « Express » : menu à gauche, nom de la boutique au centre,
    recherche et panier à droite (menu repliable sur mobile). Les éléments du bandeau sont tous vrais :
    note et commandes livrées seulement si la boutique en a.
    Variables : $pm, $current ('home' | 'products' | 'product')
--}}
@php
    $s = $pm['shop'];
    $catalogUrl = $s['url'] . '?vue=produits';
    $band = array_values(array_filter([
        'Paiement à la livraison',
        'Appel de confirmation avant l’envoi',
        $s['verified'] ? 'Boutique vérifiée par Shopio' : null,
        $pm['rating'] ? '★ ' . number_format($pm['rating']['avg'], 1, ',', ' ') . '/5 · ' . $pm['rating']['count'] . ' avis clients' : null,
        $pm['delivered'] ? number_format($pm['delivered'], 0, ',', ' ') . ' commandes livrées' : null,
        'Suivi de commande sur Shopio',
    ]));
@endphp
<div class="ex-marquee" role="note" aria-label="{{ implode(' · ', $band) }}"><div class="ex-track" aria-hidden="true">@for($i = 0; $i < 4; $i++)@foreach($band as $t)<span>{{ $t }}</span>@endforeach @endfor</div></div>
<header class="ex-head" id="exHead" @if(!empty($editFrame)) data-sx="header" data-sx-name="En-tête" @endif>
    <div class="ex-wrap">
        <div style="display:flex;align-items:center">
            <button type="button" class="ex-icon ex-menu" id="exMenuBtn" aria-expanded="false" aria-controls="exNav" aria-label="Menu"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
            <nav class="ex-nav" id="exNav" aria-label="Navigation principale">
                <a href="{{ $s['url'] }}" @if($current === 'home') aria-current="page" @endif>Accueil</a>
                <a href="{{ $catalogUrl }}" @if($current === 'products') aria-current="page" @endif>Produits</a>
                <a href="#contact">Contact</a>
            </nav>
        </div>
        <a class="ex-brand" href="{{ $s['url'] }}">@if($s['logo'])<img src="{{ $s['logo'] }}" alt="">@endif<b>{{ $s['name'] }}</b></a>
        <div class="ex-tools">
            <button type="button" class="ex-icon" id="exSearchBtn" aria-expanded="false" aria-controls="exSearch" aria-label="Rechercher un produit"><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg></button>
            <button type="button" class="ex-icon" data-sm-cart aria-label="Ouvrir le panier"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l-1 13H7zM9 7a3 3 0 0 1 6 0"/></svg><span data-sm-count></span></button>
        </div>
    </div>
    <div class="ex-search" id="exSearch" hidden>
        <form class="ex-wrap" action="{{ $s['url'] }}" method="get" role="search">
            <input type="hidden" name="vue" value="produits">
            <label class="sm-sr" for="exQ">Rechercher</label>
            <input id="exQ" name="q" type="search" placeholder="Rechercher un produit…" value="{{ request('q') }}" maxlength="60">
            <button class="ex-btn" type="submit" style="text-transform:none;letter-spacing:0">Chercher</button>
        </form>
    </div>
</header>
<script>
(function () {
    const head = document.getElementById('exHead'), nav = document.getElementById('exNav'), mb = document.getElementById('exMenuBtn'), sb = document.getElementById('exSearchBtn'), sr = document.getElementById('exSearch');
    const onScroll = () => head.classList.toggle('is-stuck', window.scrollY > 4);
    window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
    mb.addEventListener('click', () => { const o = !nav.classList.contains('is-open'); nav.classList.toggle('is-open', o); mb.setAttribute('aria-expanded', o); });
    nav.addEventListener('click', e => { if (e.target.closest('a')) { nav.classList.remove('is-open'); mb.setAttribute('aria-expanded', 'false'); } });
    sb.addEventListener('click', () => { sr.hidden = !sr.hidden; sb.setAttribute('aria-expanded', !sr.hidden); if (!sr.hidden) document.getElementById('exQ').focus(); });
})();
</script>
