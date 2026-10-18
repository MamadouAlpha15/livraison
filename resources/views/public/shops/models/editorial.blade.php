{{--
    Modèle de page « Éditorial » : un magazine. Crème #FBF8F3 texturé, titres Lora, accent bleu, angles droits.
    Tête de journal (édition du jour), sommaire fixe, Une (photo en zoom lent), N°01 Lookbook horizontal,
    N°02 Guide budget (curseur sur les vrais prix de la boutique), N°03 une pièce sur fond bleu nuit,
    N°04 index de toutes les pièces. Les sections apparaissent au défilement.
--}}
@php
    $s = $pm['shop'];
    $title = $s['slogan'] ?: $s['name'];
    $edition = now('Africa/Conakry')->translatedFormat('l j F Y');
    $city = $s['address'] !== '' ? \Illuminate\Support\Str::limit($s['address'], 34) : 'Boutique en ligne';
@endphp
<!doctype html>
<html lang="fr">
<head>
@include('public.shops.models._head')
<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --cream: #FBF8F3; --paper: #F3EDE2; --ink: #1A1714; --ink-2: #5E574D; --blue: #1668D8; --night: #0B1E3D;
    --m-bg: var(--cream); --m-surface: #FFFDF9; --m-ink: var(--ink); --m-ink2: var(--ink-2); --m-line: #E2D9C9; --m-soft: #F1EBDF;
    --m-accent: var(--blue); --m-accent-ink: #fff; --m-radius: 0px; --m-btn-radius: 0px;
    --m-title: 'Lora', Georgia, serif; --m-body: 'Plus Jakarta Sans', system-ui, sans-serif;
    --out: cubic-bezier(.22,1,.36,1);
}
body { background: radial-gradient(rgba(26,23,20,.045) 1px, transparent 1px) 0 0 / 7px 7px, var(--cream); }
.ed-wrap { max-width: 1240px; margin: 0 auto; padding: 0 max(20px, env(safe-area-inset-left, 0px)); }
/* Tête de journal */
.ed-mast { padding: 22px 0 0; text-align: center; }
.ed-meta { display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding-bottom: 12px; border-bottom: 1px solid var(--ink); font-size: 12.5px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--ink-2); }
.ed-meta button { border: 0; background: none; font: 800 12.5px var(--m-body); letter-spacing: .08em; text-transform: uppercase; color: var(--ink); cursor: pointer; padding: 0; }
.ed-meta button span { color: var(--blue); }
.ed-name { margin: 18px 0 6px; font: 700 clamp(44px, 8vw, 96px)/.95 var(--m-title); letter-spacing: -.025em; }
.ed-tag { margin: 0 0 16px; font: italic 500 17px var(--m-title); color: var(--ink-2); }
/* Sommaire fixe */
.ed-toc { position: sticky; top: 0; z-index: 40; background: rgba(251,248,243,.94); backdrop-filter: blur(8px); border-block: 3px double var(--ink); }
.ed-toc nav { display: flex; justify-content: center; gap: 30px; overflow-x: auto; scrollbar-width: none; padding: 10px 0; }
.ed-toc a { flex-shrink: 0; color: var(--ink); text-decoration: none; font: 600 14px var(--m-body); }
.ed-toc a i { font: italic 600 14px var(--m-title); color: var(--blue); margin-right: 6px; }
.ed-toc a:hover { text-decoration: underline; text-underline-offset: 4px; }
/* Une */
.ed-une { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); gap: 46px; padding: 46px 0 60px; border-bottom: 1px solid var(--m-line); }
.ed-une-img { position: relative; aspect-ratio: 4 / 3.2; overflow: hidden; background: var(--paper); }
.ed-une-img img { width: 100%; height: 100%; object-fit: cover; display: block; animation: edZoom 22s ease-in-out infinite alternate; }
@keyframes edZoom { to { transform: scale(1.12); } }
.ed-une-img figcaption { position: absolute; left: 0; bottom: 0; padding: 8px 12px; background: var(--cream); font: italic 500 13px var(--m-title); color: var(--ink-2); }
.ed-une h1 { margin: 0 0 16px; font: 700 clamp(34px, 5vw, 62px)/1.04 var(--m-title); letter-spacing: -.02em; text-wrap: balance; }
.ed-une p.ed-lede { margin: 0 0 24px; font: 500 18px/1.65 var(--m-title); color: var(--ink-2); }
.ed-sum { list-style: none; margin: 0; padding: 0; border-top: 1px solid var(--ink); }
.ed-sum li { border-bottom: 1px solid var(--m-line); }
.ed-sum a { display: grid; grid-template-columns: 54px minmax(0, 1fr); gap: 10px; padding: 12px 0; color: var(--ink); text-decoration: none; }
.ed-sum a i { font: italic 600 15px var(--m-title); color: var(--blue); }
.ed-sum a b { font: 600 17px var(--m-title); }
.ed-sum a span { display: block; font-size: 13px; color: var(--ink-2); }
/* Sections numérotées */
.ed-sec { padding: 70px 0; border-bottom: 1px solid var(--m-line); }
.ed-kick { display: flex; align-items: baseline; gap: 14px; margin-bottom: 26px; }
.ed-kick i { font: italic 600 22px var(--m-title); color: var(--blue); }
.ed-kick h2 { margin: 0; font: 700 clamp(30px, 4vw, 46px)/1.05 var(--m-title); letter-spacing: -.015em; }
.ed-kick span { margin-left: auto; color: var(--ink-2); font-size: 13.5px; }
/* Visible par défaut ; seules les sections encore hors de l’écran au chargement apparaissent au défilement */
.ed-reveal { transition: opacity 900ms var(--out), transform 900ms var(--out); }
.ed-reveal.is-pending { opacity: 0; transform: translateY(30px); }
/* N°01 Lookbook horizontal */
.ed-look { display: flex; gap: 26px; overflow-x: auto; scroll-snap-type: x mandatory; padding: 10px 2px 26px; scrollbar-color: var(--ink) transparent; }
.ed-lk { flex: 0 0 280px; scroll-snap-align: start; border: 0; background: none; padding: 0; text-align: left; cursor: pointer; color: var(--ink); }
.ed-lk:nth-child(even) { margin-top: 56px; }
.ed-lk i { display: block; font: italic 600 40px var(--m-title); color: var(--blue); line-height: 1; margin-bottom: 8px; }
.ed-lk span { display: block; aspect-ratio: 3 / 4; background: var(--paper) center / cover; margin-bottom: 12px; transition: transform 600ms var(--out); }
.ed-lk:hover span { transform: translateY(-6px); }
.ed-lk b { display: block; font: 600 19px/1.2 var(--m-title); }
.ed-lk small { color: var(--ink-2); font-size: 14px; font-variant-numeric: tabular-nums; }
.ed-arrows { display: flex; gap: 8px; }
.ed-arrows button { width: 46px; height: 46px; border: 1px solid var(--ink); background: transparent; color: var(--ink); font-size: 20px; cursor: pointer; }
.ed-arrows button:hover { background: var(--ink); color: var(--cream); }
/* N°02 Guide budget */
.ed-budget { display: grid; grid-template-columns: minmax(0, .9fr) minmax(0, 1.4fr); gap: 46px; align-items: start; }
.ed-range label { display: block; font: italic 500 17px var(--m-title); color: var(--ink-2); margin-bottom: 6px; }
.ed-range output { display: block; font: 700 clamp(34px, 4vw, 48px) var(--m-title); font-variant-numeric: tabular-nums; letter-spacing: -.02em; }
.ed-range input { width: 100%; margin: 14px 0 6px; accent-color: var(--blue); min-height: 30px; }
.ed-range small { display: flex; justify-content: space-between; color: var(--ink-2); font-size: 12.5px; font-variant-numeric: tabular-nums; }
.ed-for { display: flex; gap: 0; margin-top: 22px; border: 1px solid var(--ink); }
.ed-for button { flex: 1; min-height: 46px; border: 0; background: transparent; font: 700 14px var(--m-body); color: var(--ink); cursor: pointer; }
.ed-for button + button { border-left: 1px solid var(--ink); }
.ed-for button[aria-pressed="true"] { background: var(--ink); color: var(--cream); }
.ed-picks { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
.ed-pick { border: 0; background: var(--m-surface); padding: 0 0 14px; text-align: left; cursor: pointer; color: var(--ink); box-shadow: 0 0 0 1px var(--m-line); animation: smFade 400ms var(--out); }
.ed-pick span { display: block; aspect-ratio: 1; background: var(--paper) center / cover; margin-bottom: 10px; }
.ed-pick b { display: block; padding: 0 12px; font: 600 16px/1.25 var(--m-title); }
.ed-pick small { display: block; padding: 4px 12px 0; color: var(--blue); font-weight: 800; font-variant-numeric: tabular-nums; }
.ed-nothing { padding: 30px; border: 1px dashed var(--ink-2); font: italic 500 17px var(--m-title); color: var(--ink-2); }
/* N°03 une pièce sur fond bleu nuit */
.ed-night { background: var(--night); color: #F5EFE1; border: 0; }
.ed-night .ed-kick i { color: #7DB4FF; }
.ed-feat { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr); gap: 50px; align-items: center; }
.ed-feat-img { aspect-ratio: 1; background: #132B52 center / cover; box-shadow: 30px 30px 0 rgba(125,180,255,.12); }
.ed-feat h3 { margin: 0 0 12px; font: 700 clamp(30px, 4vw, 48px)/1.05 var(--m-title); }
.ed-feat p { color: #C9D6EA; font: 500 17px/1.65 var(--m-title); }
.ed-feat .ed-price { font: 700 26px var(--m-body); color: #FCD34D; font-variant-numeric: tabular-nums; }
.ed-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 48px; padding: 0 22px; border: 1px solid currentColor; background: transparent; color: inherit; font: 800 13.5px var(--m-body); letter-spacing: .08em; text-transform: uppercase; cursor: pointer; text-decoration: none; }
.ed-btn.is-fill { background: var(--blue); border-color: var(--blue); color: #fff; }
/* N°04 index */
.ed-index { width: 100%; border-collapse: collapse; }
.ed-index th { text-align: left; font: 800 11.5px var(--m-body); letter-spacing: .1em; text-transform: uppercase; color: var(--ink-2); padding: 0 10px 10px; border-bottom: 1px solid var(--ink); }
.ed-index td { padding: 14px 10px; border-bottom: 1px solid var(--m-line); vertical-align: middle; }
.ed-index td:first-child { font: italic 600 15px var(--m-title); color: var(--blue); width: 50px; font-variant-numeric: tabular-nums; }
.ed-index .ed-iname { border: 0; background: none; padding: 0; font: 600 18px var(--m-title); color: var(--ink); cursor: pointer; text-align: left; }
.ed-index .ed-iname:hover { text-decoration: underline; text-underline-offset: 4px; }
.ed-index td.ed-num { text-align: right; font-weight: 800; font-variant-numeric: tabular-nums; white-space: nowrap; }
.ed-index .ed-add { min-height: 40px; padding: 0 14px; border: 1px solid var(--ink); background: transparent; font: 700 12.5px var(--m-body); cursor: pointer; color: var(--ink); }
.ed-index .ed-add:hover:not(:disabled) { background: var(--ink); color: var(--cream); }
.ed-index .ed-add:disabled { opacity: .45; cursor: not-allowed; }
.ed-foot { padding: 36px 0 calc(40px + env(safe-area-inset-bottom, 0px)); text-align: center; font: italic 500 15px var(--m-title); color: var(--ink-2); }
@media (max-width: 900px) {
    .ed-une, .ed-budget, .ed-feat { grid-template-columns: minmax(0, 1fr); gap: 30px; }
    .ed-picks { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ed-toc nav { justify-content: flex-start; padding-inline: 4px; }
    .ed-lk { flex-basis: 220px; }
    .ed-index .ed-hide { display: none; }
}
</style>
</head>
<body class="no-js">
<script>document.body.classList.remove('no-js');</script>
@include('public.shops.models._top')
<header class="ed-wrap ed-mast">
    <div class="ed-meta"><span>Édition du {{ $edition }}</span><span>Le journal de la boutique · {{ $city }}</span><button type="button" data-sm-cart aria-label="Ouvrir le panier">Panier <span data-sm-count></span></button></div>
    <div class="ed-name">{{ $s['name'] }}</div>
    <p class="ed-tag">@if($s['verified'])Boutique vérifiée · @endif paiement à la livraison</p>
</header>
<div class="ed-toc"><nav class="ed-wrap" aria-label="Sommaire"><a href="#n1"><i>N°01</i>Lookbook</a><a href="#n2"><i>N°02</i>Guide budget</a><a href="#n3"><i>N°03</i>La pièce</a><a href="#produits"><i>N°04</i>Index</a></nav></div>

<main class="ed-wrap">
    <section class="ed-une" aria-labelledby="edUneH">
        <figure class="ed-une-img" id="edUneImg" style="margin:0"></figure>
        <div>
            <h1 id="edUneH">{{ $title }}</h1>
            <p class="ed-lede">{{ count($pm['products']) }} pièce{{ count($pm['products']) > 1 ? 's' : '' }}@if(count($pm['categories'])), en {{ count($pm['categories']) }} rubrique{{ count($pm['categories']) > 1 ? 's' : '' }} @endif — à commander en ligne, à payer à la livraison.</p>
            <ol class="ed-sum">
                <li><a href="#n1"><i>N°01</i><span><b>Lookbook</b><span>Les pièces, une à une</span></span></a></li>
                <li><a href="#n2"><i>N°02</i><span><b>Guide budget</b><span>Les pièces qui tiennent dans votre budget</span></span></a></li>
                <li><a href="#n3"><i>N°03</i><span><b>La pièce</b><span>À découvrir ce jour</span></span></a></li>
                <li><a href="#produits"><i>N°04</i><span><b>Index</b><span>Toutes les pièces, avec leur prix</span></span></a></li>
            </ol>
        </div>
    </section>

    <section class="ed-sec ed-reveal" id="n1" aria-labelledby="edN1">
        <div class="ed-kick"><i>N°01</i><h2 id="edN1">Lookbook</h2><span class="ed-arrows"><button type="button" id="edPrev" aria-label="Pièces précédentes">←</button><button type="button" id="edNext" aria-label="Pièces suivantes">→</button></span></div>
        <div class="ed-look" id="edLook"></div>
    </section>

    <section class="ed-sec ed-reveal" id="n2" aria-labelledby="edN2">
        <div class="ed-kick"><i>N°02</i><h2 id="edN2">Guide budget</h2></div>
        <div class="ed-budget">
            <div>
                <div class="ed-range"><label for="edBudget">Votre budget</label><output id="edBudgetOut" for="edBudget"></output><input type="range" id="edBudget"><small><span id="edMin"></span><span id="edMax"></span></small></div>
                <div class="ed-for" role="group" aria-label="C’est pour"><button type="button" data-for="moi" aria-pressed="true">Moi</button><button type="button" data-for="cadeau" aria-pressed="false">Un cadeau</button><button type="button" data-for="mariage" aria-pressed="false">Un mariage</button></div>
            </div>
            <div><p class="ed-tag" id="edForTxt" style="margin-top:0"></p><div class="ed-picks" id="edPicks" aria-live="polite"></div></div>
        </div>
    </section>
</main>

<section class="ed-sec ed-night ed-reveal" id="n3" aria-labelledby="edN3"><div class="ed-wrap" id="edFeat"></div></section>

<main class="ed-wrap">
    <section class="ed-sec ed-reveal" id="produits" aria-labelledby="edN4">
        <div class="ed-kick"><i>N°04</i><h2 id="edN4">Index</h2><span id="edCount"></span></div>
        <div style="overflow-x:auto"><table class="ed-index"><thead><tr><th scope="col">N°</th><th scope="col">Pièce</th><th scope="col" class="ed-hide">Rubrique</th><th scope="col" style="text-align:right">Prix</th><th scope="col"><span class="sm-sr">Ajouter</span></th></tr></thead><tbody id="edIndex"></tbody></table></div>
    </section>
</main>
<footer class="ed-foot">{{ $s['name'] }} — le journal de la boutique, sur Shopio.</footer>

@include('public.shops.models._kit')
<script>
(function () {
    'use strict';
    const S = window.SM, D = S.data, esc = S.esc, $ = id => document.getElementById(id);
    const P = S.products, num = i => String(i + 1).padStart(2, '0');
    const withPhoto = P.filter(p => p.photo);
    /* Une */
    const une = P.find(p => p.featured && p.photo) || withPhoto[0];
    if (une) $('edUneImg').innerHTML = '<img src="' + esc(une.photo) + '" alt="' + esc(une.name) + '"><figcaption>' + esc(une.name) + ' · ' + esc(S.money(une.price)) + '</figcaption>';
    else $('edUneImg').hidden = true;
    /* N°01 Lookbook : cartes décalées, numéros en italique */
    const look = (withPhoto.length ? withPhoto : P).slice(0, 12);
    $('edLook').innerHTML = look.map((p, i) => '<button type="button" class="ed-lk" data-sm-quick="' + p.id + '"><i>' + num(i) + '</i><span' + (p.photo ? ' style="background-image:url(\'' + esc(p.photo) + '\')"' : '') + '></span><b>' + esc(p.name) + '</b><small>' + esc(S.money(p.price)) + '</small></button>').join('');
    const step = () => Math.min(600, $('edLook').clientWidth * .8);
    $('edPrev').addEventListener('click', () => $('edLook').scrollBy({ left: -step(), behavior: S.reduce ? 'auto' : 'smooth' }));
    $('edNext').addEventListener('click', () => $('edLook').scrollBy({ left: step(), behavior: S.reduce ? 'auto' : 'smooth' }));
    /* N°02 Guide budget : curseur entre le prix le plus bas et le plus haut de la boutique */
    const prices = P.map(p => p.price).filter(x => x > 0);
    if (prices.length) {
        const lo = Math.min(...prices), hi = Math.max(...prices), stepV = Math.max(1000, Math.pow(10, Math.floor(Math.log10(Math.max(1, hi - lo))) - 1));
        const r = $('edBudget');
        r.min = Math.floor(lo / stepV) * stepV; r.max = Math.ceil(hi / stepV) * stepV; r.step = stepV; r.value = Math.round((+r.min + +r.max) / 2 / stepV) * stepV;
        $('edMin').textContent = S.money(r.min); $('edMax').textContent = S.money(r.max);
        let forWho = 'moi';
        const TXT = { moi: 'Pour vous, les pièces les plus proches de votre budget :', cadeau: 'À offrir, les pièces les plus proches de votre budget :', mariage: 'Pour le mariage, les pièces les plus proches de votre budget :' };
        function picks() {
            const b = +r.value;
            $('edBudgetOut').textContent = S.money(b);
            $('edForTxt').textContent = TXT[forWho];
            const list = P.filter(p => p.price <= b).sort((x, y) => y.price - x.price).slice(0, 3);
            $('edPicks').innerHTML = list.length ? list.map(p => '<button type="button" class="ed-pick" data-sm-quick="' + p.id + '"><span' + (p.photo ? ' style="background-image:url(\'' + esc(p.photo) + '\')"' : '') + '></span><b>' + esc(p.name) + '</b><small>' + esc(S.money(p.price)) + '</small></button>').join('')
                : '<p class="ed-nothing" style="grid-column:1/-1">Aucune pièce à ' + esc(S.money(b)) + ' ou moins pour l’instant. La moins chère est à ' + esc(S.money(lo)) + '.</p>';
        }
        r.addEventListener('input', picks);
        document.querySelectorAll('[data-for]').forEach(btn => btn.addEventListener('click', () => { forWho = btn.dataset.for; document.querySelectorAll('[data-for]').forEach(x => x.setAttribute('aria-pressed', x === btn)); picks(); }));
        picks();
    } else { $('n2').hidden = true; }
    /* N°03 une pièce d'une autre rubrique que la Une, sur fond bleu nuit */
    const feat = P.find(p => p.photo && une && p.cat !== une.cat && p.id !== une.id) || withPhoto.find(p => !une || p.id !== une.id);
    if (feat) $('edFeat').innerHTML = '<div class="ed-kick"><i>N°03</i><h2 id="edN3">La pièce</h2></div><div class="ed-feat"><div class="ed-feat-img" style="background-image:url(\'' + esc(feat.photo) + '\')" role="img" aria-label="' + esc(feat.name) + '"></div><div><p class="ed-tag" style="color:#9FB3D3">' + esc(feat.cat || D.shop.name) + '</p><h3>' + esc(feat.name) + '</h3>' + (feat.desc ? '<p>' + esc(feat.desc.slice(0, 260)) + '</p>' : '') + '<p class="ed-price">' + esc(S.money(feat.price)) + '</p><div style="display:flex;gap:10px;flex-wrap:wrap"><button type="button" class="ed-btn is-fill" data-sm-add="' + feat.id + '"' + (S.canBuy(feat) ? '' : ' disabled') + '>' + esc(S.buyLabel(feat)) + '</button><button type="button" class="ed-btn" data-sm-quick="' + feat.id + '">Lire la fiche</button></div></div></div>';
    else $('n3').hidden = true;
    /* N°04 Index de toutes les pièces */
    let cat = @json($activeCategory['name'] ?? '');
    const list = P.filter(p => !cat || p.cat === cat);
    $('edCount').textContent = list.length + ' pièce' + (list.length > 1 ? 's' : '');
    $('edIndex').innerHTML = list.length ? list.map((p, i) => '<tr><td>' + num(i) + '</td><td><button type="button" class="ed-iname" data-sm-quick="' + p.id + '">' + esc(p.name) + '</button></td><td class="ed-hide" style="color:var(--ink-2)">' + esc(p.cat) + '</td><td class="ed-num">' + esc(S.money(p.price)) + '</td><td style="text-align:right"><button type="button" class="ed-add" data-sm-add="' + p.id + '"' + (S.canBuy(p) ? '' : ' disabled') + '>' + (S.canBuy(p) ? (p.variants ? 'Options' : 'Ajouter') : esc(S.buyLabel(p))) + '</button></td></tr>').join('')
        : '<tr><td colspan="5" style="font-style:italic">Aucun produit dans cette catégorie pour l’instant.</td></tr>';
    /* Apparition au défilement */
    if ('IntersectionObserver' in window && !S.reduce) {
        const io = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) { e.target.classList.remove('is-pending'); io.unobserve(e.target); } }), { rootMargin: '0px 0px -10% 0px' });
        document.querySelectorAll('.ed-reveal').forEach(el => { if (el.getBoundingClientRect().top > window.innerHeight) { el.classList.add('is-pending'); io.observe(el); } });
    }
})();
</script>
</body>
</html>
