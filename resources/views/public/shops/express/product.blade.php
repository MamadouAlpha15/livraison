{{--
    Page produit longue du modèle « Express » (/s/{slug}/p/{12-nom}). Dans l'ordre :
      galerie (flèches, miniatures, zoom) · note réelle · titre · accroche · prix · avantages · délai en 3 étapes ·
      packs « Plus tu achètes, plus tu économises » · quantité · panier · « Finaliser la commande » sur la page ·
      volets (utilisation, description, livraison) · bloc mis en avant · avis réels · bande défilante ·
      « Avant / Après » · « Pour qui ? » · résultats réels · « Pourquoi vous allez l'adorer » · comparatif ·
      questions · produits proches · barre « Commander » fixe.
    Les sections longues n'apparaissent que si leur contenu est rempli (products.landing, voir ProductPage).
    Sous le haut de page (fixe), l'ordre, l'affichage et les sections ajoutées viennent de Boutique → Sections
    → Page produit (App\Support\HomeSections, liste « product »), pour tous les produits de la boutique.
    Variables : $shop, $pm, $product (données PageModels), $photos, $content, $happy, $ownerEdit
--}}
@php
    use App\Support\HomeSections as HS;
    $s = $pm['shop'];
    $c = $content;
    // Réglages du produit (éditeur produit : Sections, Couleurs, Formulaire, Offres) ; repli sur ceux de la boutique
    $dz = $design ?? \App\Support\ProductDesign::for(new \App\Models\Product(), ['home' => $pm['home'], 'form' => null]);
    $ppSections = $dz['page'];
    $fm = $dz['form'];
    $col = $dz['colors'];
    $mention = $dz['rating']['mention'] !== '' ? $dz['rating']['mention'] : ($pm['rating'] && $pm['rating']['avg'] >= 4.5 ? 'Excellent' : ($pm['rating'] && $pm['rating']['avg'] >= 4 ? 'Très bien' : 'Bien'));
    // Accroche : **mot** en gras
    $hookHtml = preg_replace('/\*\*(.+?)\*\*/u', '<b>$1</b>', e($c['hook']));
    $sp = fn ($x) => ($x['sp'][0] ?? 0) || ($x['sp'][1] ?? 0) ? ' style="margin-top:' . (int) $x['sp'][0] . 'px;margin-bottom:' . (int) $x['sp'][1] . 'px"' : '';
    $fonts = array_values(array_filter(array_unique([$col['font_title'], $col['font_body']])));
    $p = $product;
    $ph = fn ($i) => $photos[$i] ?? ($photos[0] ?? null);
    $pct = $p['old'] ? (int) round((1 - $p['price'] / $p['old']) * 100) : 0;
    $hlWords = preg_split('/\s+/u', trim($c['highlight']['title']));
    $hlFirst = array_shift($hlWords);
    $loveTitle = $c['love']['title'] !== '' ? $c['love']['title'] : 'Pourquoi vous allez l’adorer';
    $loveWords = preg_split('/\s+/u', trim($loveTitle));
    $loveLast = array_pop($loveWords);
    $hasLong = $c['highlight']['title'] !== '' || $c['story']['title'] !== '' || $c['audience']['title'] !== '' || count($c['love']['items']) || count($c['compare']['rows']);
@endphp
<!doctype html>
<html lang="fr">
<head>
@include('public.shops.models._head')
@include('public.shops.express._style')
<style>
.ex-crumb { display: flex; gap: 8px; flex-wrap: wrap; padding: 18px 0 0; font-size: 13px; color: var(--ink-3); }
.ex-crumb a { color: var(--acc-dark); text-decoration: none; } .ex-crumb a:hover { text-decoration: underline; }
.ex-pp { display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr); gap: clamp(26px, 5vw, 64px); align-items: start; padding: 22px 0 60px; }
/* Galerie */
.ex-gal { position: sticky; top: 90px; }
.ex-gal-main { position: relative; aspect-ratio: 1; border-radius: 18px; overflow: hidden; background: var(--soft); }
.ex-gal-main img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 500ms var(--out), transform 900ms var(--out); transform: scale(1.03); }
.ex-gal-main img.is-on { opacity: 1; transform: none; }
.ex-gal-main .ex-noimg { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; padding: 30px; text-align: center; font: 700 28px var(--m-title); color: var(--ink-3); }
.ex-gal-btn { position: absolute; z-index: 2; width: 42px; height: 42px; border: 0; border-radius: 50%; background: rgba(255,255,255,.92); color: var(--ink); cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 16px -8px rgba(0,0,0,.5); transition: transform 200ms var(--out); }
.ex-gal-btn:hover { transform: scale(1.08); }
.ex-gal-prev { left: 12px; top: 50%; margin-top: -21px; } .ex-gal-next { right: 12px; top: 50%; margin-top: -21px; } .ex-gal-zoom { right: 12px; top: 12px; }
.ex-gal-thumbs { display: flex; gap: 10px; margin-top: 12px; overflow-x: auto; padding-bottom: 4px; }
.ex-gal-thumbs button { flex-shrink: 0; width: 70px; height: 70px; padding: 0; border: 0; border-radius: 10px; overflow: hidden; background: var(--soft); cursor: pointer; opacity: .6; box-shadow: inset 0 0 0 2px transparent; transition: opacity 200ms; }
.ex-gal-thumbs button[aria-current="true"] { opacity: 1; box-shadow: 0 0 0 2px var(--ink); }
.ex-gal-thumbs img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ex-zoom { position: fixed; inset: 0; z-index: 980; display: flex; align-items: center; justify-content: center; padding: 20px; background: rgba(0,0,0,.88); cursor: zoom-out; }
.ex-zoom img { max-width: 100%; max-height: 100%; border-radius: 10px; }
/* Infos */
.ex-rate { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 13px; color: var(--ink-2); }
.ex-rate b { color: var(--ink); }
.ex-pp h1 { margin: 10px 0 0; font: 600 clamp(26px, 3.4vw, 36px)/1.18 var(--m-title); letter-spacing: -.02em; text-wrap: balance; }
.ex-hook { margin: 12px 0 0; color: var(--ink-2); font-size: 15.5px; line-height: 1.6; }
.ex-pp-price { display: flex; align-items: baseline; gap: 12px; flex-wrap: wrap; margin-top: 18px; font: 800 30px var(--m-body); color: var(--acc-dark); font-variant-numeric: tabular-nums; }
.ex-pp-price s { font-size: 17px; font-weight: 500; color: var(--ink-3); }
.ex-pp-price em { padding: 4px 10px; border-radius: 6px; background: #FDECEA; color: #B42318; font: 800 13px var(--m-body); font-style: normal; }
.ex-benefits { display: grid; gap: 9px; margin: 20px 0 0; padding: 0; list-style: none; }
.ex-benefits li { display: flex; gap: 10px; align-items: flex-start; font-size: 14.5px; line-height: 1.45; }
.ex-benefits li::before { content: ''; flex-shrink: 0; width: 20px; height: 20px; margin-top: 1px; border-radius: 50%; background: var(--acc-soft) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='11' height='11' fill='none' stroke='%23111' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M2 6l2.4 2.4L9.5 3'/%3E%3C/svg%3E") no-repeat center; }
.ex-steps { margin-top: 24px; }
.ex-steps > p { margin: 0 0 12px; font-size: 13.5px; font-weight: 600; }
.ex-steps > p b { color: var(--acc-dark); }
.ex-steps ol { position: relative; display: flex; justify-content: space-between; margin: 0; padding: 0; list-style: none; }
.ex-steps ol::before { content: ''; position: absolute; left: 12%; right: 12%; top: 22px; border-top: 2px dashed var(--line); }
.ex-steps li { position: relative; flex: 1; display: flex; flex-direction: column; align-items: center; gap: 2px; text-align: center; font-size: 12.5px; font-weight: 700; }
.ex-steps li i { width: 46px; height: 46px; margin-bottom: 6px; border-radius: 50%; background: #fff; box-shadow: 0 0 0 1.5px var(--line); display: flex; align-items: center; justify-content: center; color: var(--ink); }
.ex-steps li small { color: var(--ink-3); font-weight: 500; font-size: 11.5px; }
/* Packs */
.ex-packs { margin-top: 26px; }
.ex-packs-h { display: flex; align-items: center; gap: 12px; font: 800 13px var(--m-title); letter-spacing: .08em; text-transform: uppercase; }
.ex-packs-h::before, .ex-packs-h::after { content: ''; flex: 1; height: 1px; background: var(--line); }
.ex-packs > p { margin: 4px 0 14px; text-align: center; color: var(--ink-3); font-size: 13px; }
.ex-pack { position: relative; display: grid; grid-template-columns: 46px minmax(0, 1fr) auto; gap: 12px; align-items: center; width: 100%; margin-top: 12px; padding: 12px 14px; border: 0; border-radius: 10px; background: #fff; color: var(--ink); text-align: left; cursor: pointer; box-shadow: inset 0 0 0 1.5px var(--line); transition: box-shadow 200ms, background-color 200ms; font: inherit; }
.ex-pack:hover { box-shadow: inset 0 0 0 1.5px var(--ink-3); }
.ex-pack[aria-checked="true"] { background: var(--acc-soft); box-shadow: inset 0 0 0 2px var(--acc); }
.ex-pack i { position: relative; width: 46px; height: 46px; border-radius: 8px; background: var(--soft) center / cover; font-style: normal; }
.ex-pack i b { position: absolute; right: -6px; bottom: -6px; min-width: 22px; height: 22px; padding: 0 5px; border-radius: 999px; background: #111; color: #fff; font-size: 11px; display: flex; align-items: center; justify-content: center; }
.ex-pack strong { font-size: 14.5px; }
.ex-pack strong em { margin-left: 6px; padding: 2px 7px; border-radius: 5px; background: var(--acc); color: var(--acc-ink); font: 800 11px var(--m-body); font-style: normal; }
.ex-pack small { display: block; color: var(--acc-dark); font-size: 12px; font-weight: 600; }
.ex-pack span:last-child { text-align: right; font-weight: 800; font-variant-numeric: tabular-nums; }
.ex-pack span:last-child s { display: block; color: var(--ink-3); font-size: 12px; font-weight: 500; }
.ex-pack .ex-flag { position: absolute; right: 10px; top: -10px; padding: 3px 9px; border-radius: 5px; background: var(--acc); color: var(--acc-ink); font: 800 10.5px var(--m-body); letter-spacing: .06em; text-transform: uppercase; }
/* Quantité, panier, commande */
.ex-qty-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 20px; }
.ex-qty-row > span { font-size: 13.5px; font-weight: 700; }
.ex-qty { display: inline-flex; align-items: center; border-radius: 10px; box-shadow: inset 0 0 0 1.5px var(--line); }
.ex-qty button { width: 44px; height: 44px; border: 0; background: none; font-size: 20px; cursor: pointer; color: var(--ink); }
.ex-qty button:disabled { opacity: .3; cursor: default; }
.ex-qty output { min-width: 34px; text-align: center; font-weight: 800; font-variant-numeric: tabular-nums; }
.ex-add { width: 100%; margin-top: 12px; }
.ex-co { margin-top: 18px; padding: 18px; border-radius: 14px; background: #fff; box-shadow: 0 0 0 1.5px var(--ink), 0 18px 40px -26px rgba(0,0,0,.5); }
.ex-co h2 { margin: 0 0 14px; padding-bottom: 12px; border-bottom: 1.5px solid var(--ink); text-align: center; font: 700 17px var(--m-title); }
.ex-recap { padding: 12px 14px; border-radius: 10px; background: var(--soft); }
.ex-recap small { color: var(--ink-3); font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
.ex-recap div { display: flex; justify-content: space-between; gap: 10px; margin-top: 6px; font-size: 13.5px; color: var(--ink-2); }
.ex-recap div span:first-child { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ex-recap div.is-total { margin-top: 10px; padding-top: 10px; border-top: 1px dashed var(--line); color: var(--ink); font-weight: 800; font-size: 16px; font-variant-numeric: tabular-nums; }
.ex-field { display: flex; flex-direction: column; gap: 6px; margin-top: 14px; }
.ex-field label { font-size: 13px; font-weight: 700; }
.ex-field label b { color: #D92D20; }
.ex-field input, .ex-field select { width: 100%; min-height: 48px; padding: 0 14px; border: 0; border-radius: 10px; background: var(--soft); color: var(--ink); font: 500 15.5px var(--m-body); box-shadow: inset 0 0 0 1px var(--line); outline: 0; }
.ex-field input:focus, .ex-field select:focus { box-shadow: inset 0 0 0 2px var(--acc); background: #fff; }
.ex-field [aria-invalid="true"] { box-shadow: inset 0 0 0 2px #D92D20; }
.ex-tel { display: flex; gap: 8px; }
.ex-tel span { display: flex; align-items: center; padding: 0 12px; border-radius: 10px; background: var(--soft); box-shadow: inset 0 0 0 1px var(--line); font-weight: 700; font-size: 14.5px; }
.ex-err { margin: 0; color: #D92D20; font-size: 12.5px; font-weight: 600; }
.ex-err:empty { display: none; }
.ex-co .ex-btn { width: 100%; margin-top: 18px; min-height: 52px; text-transform: none; letter-spacing: 0; font-size: 15.5px; }
.ex-co-cod { margin: 10px 0 0; text-align: center; font-size: 13px; color: var(--ink-2); }
.ex-general { margin-top: 12px; padding: 10px 12px; border-radius: 10px; background: #FDECEA; color: #B42318; font-weight: 600; font-size: 14px; }
.ex-general:empty { display: none; }
.ex-done { text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 8px 0; }
.ex-done h3 { margin: 6px 0 0; font: 700 21px var(--m-title); }
.ex-done p { margin: 0; color: var(--ink-2); }
.ex-done .ex-btn { width: 100%; margin-top: 8px; }
.ex-trust { display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 14px; font-size: 13px; font-weight: 700; }
.ex-acc-list { margin-top: 22px; border-top: 1px solid var(--line); }
.ex-acc-list details { border-bottom: 1px solid var(--line); }
.ex-acc-list summary { display: flex; justify-content: space-between; align-items: center; min-height: 54px; cursor: pointer; list-style: none; font-weight: 700; font-size: 14.5px; }
.ex-acc-list summary::-webkit-details-marker { display: none; }
.ex-acc-list summary::after { content: '+'; font-size: 22px; font-weight: 400; transition: transform 300ms var(--out); }
.ex-acc-list details[open] summary::after { transform: rotate(45deg); }
.ex-acc-list p { margin: 0 0 16px; color: var(--ink-2); line-height: 1.7; white-space: pre-line; }
/* Sections de vente */
.ex-panel { border-radius: 22px; background: var(--sage); }
.ex-hl { position: relative; display: grid; grid-template-columns: 150px minmax(0, 1fr) 150px; gap: 26px; align-items: center; padding: clamp(26px, 4vw, 44px); }
.ex-polas { display: flex; flex-direction: column; gap: 18px; }
.ex-pola { padding: 7px 7px 20px; border-radius: 6px; background: #fff; box-shadow: 0 18px 30px -18px rgba(0,0,0,.5); transform: rotate(-5deg); transition: transform 500ms var(--out); }
.ex-pola:nth-child(2) { transform: rotate(4deg) translateX(10px); }
.ex-polas.is-right .ex-pola { transform: rotate(6deg); }
.ex-pola:hover { transform: rotate(0) scale(1.04); }
.ex-pola img, .ex-pola span { display: block; width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 3px; background: var(--soft); }
.ex-hl-mid { text-align: center; }
.ex-hl-mid h2 { margin: 0; font: 800 clamp(24px, 3vw, 32px)/1.2 var(--m-title); text-transform: uppercase; letter-spacing: -.01em; }
.ex-hl-mid p { margin: 14px auto 0; max-width: 56ch; color: var(--ink-2); line-height: 1.65; font-size: 14.5px; }
.ex-hl-mid .ex-btn { width: 100%; max-width: 420px; margin-top: 22px; }
.ex-mini { display: flex; justify-content: center; gap: 18px; flex-wrap: wrap; margin-top: 18px; font-size: 12.5px; color: var(--ink-2); }
.ex-mini span { display: flex; align-items: center; gap: 6px; }
.ex-tested h2 { text-align: center; }
.ex-tested h2 .ex-serif { display: block; color: var(--acc-dark); font-size: 1.08em; }
.ex-split { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: clamp(26px, 5vw, 56px); align-items: center; padding: clamp(26px, 4vw, 48px); }
.ex-split-img { border-radius: 16px; overflow: hidden; background: #fff; aspect-ratio: 1; box-shadow: 0 20px 40px -28px rgba(0,0,0,.5); }
.ex-split-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ex-split h2 { font: 600 clamp(28px, 3.6vw, 42px)/1.12 var(--m-title); letter-spacing: -.02em; margin: 0; }
.ex-split .ex-text { margin: 18px 0 0; color: var(--ink-2); line-height: 1.8; font-size: 14.5px; white-space: pre-line; }
.ex-results { display: grid; gap: 4px; max-width: 560px; margin: 26px auto 0; }
.ex-results div { display: grid; grid-template-columns: 120px minmax(0, 1fr); align-items: center; gap: 16px; padding: 14px 0; border-bottom: 1px solid var(--line); }
.ex-results b { font: 800 28px var(--m-title); color: var(--acc-dark); font-variant-numeric: tabular-nums; }
.ex-results span { color: var(--ink-2); }
.ex-love h2 { font-size: clamp(26px, 3.2vw, 36px); }
.ex-love h2 .ex-serif { color: var(--acc-dark); font-size: 1.15em; }
.ex-love ul { display: grid; gap: 16px; margin: 22px 0 0; padding: 0; list-style: none; }
.ex-love li { display: grid; grid-template-columns: 26px minmax(0, 1fr); gap: 10px; }
.ex-love li svg { color: var(--acc-dark); margin-top: 1px; }
.ex-love li b { display: block; font-size: 15px; } .ex-love li span { color: var(--ink-2); font-size: 13.5px; line-height: 1.5; }
.ex-compare { margin: 30px auto 0; max-width: 680px; }
.ex-compare table { width: 100%; border-collapse: separate; border-spacing: 0; }
.ex-compare th, .ex-compare td { padding: 15px 14px; font-size: 14.5px; }
.ex-compare th { font-size: 11.5px; letter-spacing: .1em; text-transform: uppercase; color: var(--ink-3); font-weight: 800; }
.ex-compare td:first-child { text-align: left; }
.ex-compare td + td, .ex-compare th + th { width: 110px; text-align: center; }
.ex-compare .is-us { background: #fff; color: var(--ink); }
.ex-compare thead .is-us { border-radius: 12px 12px 0 0; color: var(--acc-dark); }
.ex-compare tbody tr:last-child .is-us { border-radius: 0 0 12px 12px; }
.ex-compare tbody tr + tr td { border-top: 1px solid rgba(0,0,0,.06); }
.ex-yes { color: var(--acc-dark); font-weight: 800; } .ex-no { color: var(--ink-3); }
.ex-related { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-top: 28px; }
/* Barre « Commander » fixe */
.ex-sticky { position: fixed; z-index: 70; left: 50%; bottom: max(14px, env(safe-area-inset-bottom, 0px)); display: flex; align-items: center; gap: 18px; width: min(460px, calc(100% - 24px)); padding: 8px 8px 8px 20px; border-radius: 14px; background: #fff; box-shadow: 0 0 0 1px var(--line), 0 20px 40px -14px rgba(0,0,0,.45); transform: translate(-50%, 0); transition: transform 400ms var(--out), opacity 300ms; }
.ex-sticky.is-hidden { transform: translate(-50%, 140%); opacity: 0; pointer-events: none; }
.ex-sticky b { flex: 1; font-size: 16px; font-variant-numeric: tabular-nums; }
.ex-sticky .ex-btn { min-height: 44px; padding: 0 20px; text-transform: none; letter-spacing: 0; box-shadow: none; }
body { padding-bottom: 86px; }
@media (max-width: 900px) {
    .ex-pp { grid-template-columns: minmax(0, 1fr); }
    .ex-gal { position: static; }
    .ex-hl { grid-template-columns: minmax(0, 1fr); }
    .ex-polas { flex-direction: row; justify-content: center; }
    .ex-polas .ex-pola { width: 120px; }
    .ex-polas.is-right { display: none; }
    .ex-split { grid-template-columns: minmax(0, 1fr); }
    .ex-split.is-flip .ex-split-img { order: -1; }
    .ex-related { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ex-results div { grid-template-columns: 90px minmax(0, 1fr); }
}
/* Bouton de commande animé (éditeur produit → Formulaire) */
.ex-anim-shake { animation: exShake 3s ease-in-out infinite; }
.ex-anim-bounce { animation: exBounce 2.4s ease-in-out infinite; }
.ex-anim-slide { position: relative; overflow: hidden; }
.ex-anim-slide::after { content: ''; position: absolute; top: 0; bottom: 0; left: -60%; width: 45%; background: linear-gradient(100deg, transparent, rgba(255,255,255,.45), transparent); transform: skewX(-18deg); animation: exSlide 2.8s ease-in-out infinite; }
@keyframes exShake { 0%, 84%, 100% { transform: translateX(0); } 86% { transform: translateX(calc(var(--amp) * -1)); } 89% { transform: translateX(var(--amp)); } 92% { transform: translateX(calc(var(--amp) * -1)); } 95% { transform: translateX(var(--amp)); } }
@keyframes exBounce { 0%, 70%, 100% { transform: translateY(0); } 80% { transform: translateY(calc(var(--amp) * -1.6)); } 90% { transform: translateY(calc(var(--amp) * -.5)); } }
@keyframes exSlide { 0%, 55% { left: -60%; } 100% { left: 130%; } }
.ex-co { background: var(--fm-bg, #fff); box-shadow: 0 0 0 1.5px var(--fm-border, var(--ink)), 0 18px 40px -26px rgba(0,0,0,.5); }
.ex-co h2 { color: var(--fm-title, inherit); border-bottom-color: var(--fm-border, var(--ink)); }
.ex-co .ex-field label { color: var(--fm-label, inherit); }
.ex-pack[aria-checked="true"] { box-shadow: inset 0 0 0 2px var(--pack-c, var(--acc)); }
.ex-pack.no-img { grid-template-columns: minmax(0, 1fr) auto; }
.ex-pack-unit { display: block; color: var(--ink-3); font-size: 11.5px; font-weight: 600; }
.ex-pack span:last-child { white-space: nowrap; }
.ex-pack strong { display: inline-flex; align-items: center; flex-wrap: wrap; gap: 2px 0; }
.ex-trust-pay { display: flex; justify-content: center; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
.ex-trust-pay span { padding: 3px 9px; border-radius: 6px; font-size: 11.5px; font-weight: 800; }
.ex-info { padding-bottom: clamp(30px, 5vw, 56px); }
.ex-info .ex-acc-list { margin-top: 0; }
@media (prefers-reduced-motion: reduce) { .ex-anim-shake, .ex-anim-bounce, .ex-anim-slide::after { animation: none; } }
</style>
@if(count($fonts))<link href="https://fonts.googleapis.com/css2?{{ implode('&', array_map(fn ($f) => 'family=' . str_replace(' ', '+', $f) . ':wght@400;600;700;800', $fonts)) }}&display=swap" rel="stylesheet">@endif
<style>
:root {
@if($col['accent'])
    @php $ak = $col['accent']; @endphp
    --acc: {{ $ak }}; --acc-ink: {{ \App\Support\ProductDesign::ink($ak) }}; --acc-soft: color-mix(in srgb, {{ $ak }} 12%, #fff); --acc-dark: color-mix(in srgb, {{ $ak }} 82%, #000);
@endif
@if($col['font_title'])    --m-title: '{{ $col['font_title'] }}', system-ui, sans-serif;@endif
@if($col['font_body'])    --m-body: '{{ $col['font_body'] }}', system-ui, sans-serif;@endif
@if($fm['bg'])    --fm-bg: {{ $fm['bg'] }};@endif
@if($fm['border'])    --fm-border: {{ $fm['border'] }};@endif
@if($fm['title_c'])    --fm-title: {{ $fm['title_c'] }};@endif
@if($fm['label_c'])    --fm-label: {{ $fm['label_c'] }};@endif
@if($dz['offer']['frame'])    --pack-c: {{ $dz['offer']['frame'] }};@endif
    --amp: {{ ['soft' => '2px', 'medium' => '4px', 'strong' => '7px'][$fm['intensity']] }};
}
@if($col['font_body']) body { font-family: var(--m-body); } @endif
@if($col['bg']) body, main { background: {{ $col['bg'] }}; } @endif
@if($col['foot_bg'] || $col['foot_ink'])
.ex-foot { @if($col['foot_bg'])background: {{ $col['foot_bg'] }};@endif @if($col['foot_ink'])color: {{ $col['foot_ink'] }};@endif }
@if($col['foot_ink']) .ex-foot p, .ex-foot ul, .ex-foot h3, .ex-foot-bottom { color: {{ $col['foot_ink'] }}; } @endif
@endif
</style>
</head>
<body>
@include('public.shops.models._top', ['isOwner' => false])
@include('public.shops.express._header', ['current' => 'product'])

<main>
    <div class="ex-wrap">
        <nav class="ex-crumb" aria-label="Fil d’Ariane"><a href="{{ $s['url'] }}">Accueil</a><span aria-hidden="true">›</span><a href="{{ $s['url'] }}?vue=produits">Produits</a>@if($p['cat'])<span aria-hidden="true">›</span><span>{{ $p['cat'] }}</span>@endif</nav>
        @if(!empty($draft))
        <p class="ex-sub" role="note" style="margin-top:12px;padding:10px 14px;border-radius:10px;background:#FEF3C7;color:#78350F;font-size:13.5px"><b>Brouillon :</b> cette page n’est visible que par vous. Les clients la verront quand vous publierez le produit.</p>
        @endif
        @if($ownerEdit && !request()->boolean('apercu'))
        <p class="ex-sub" role="note" style="margin-top:12px;padding:10px 14px;border-radius:10px;background:#EAF2FF;color:#0B1E3D;font-size:13.5px"><b>Visible par vous seul :</b> @if(!$hasLong)les sections de vente (packs, « Pour qui ? », « Pourquoi vous allez l’adorer », comparatif…) apparaissent dès que la page de vente est remplie. @endif<a href="{{ route('products.page.edit', $product['id']) }}" style="color:#1668D8;font-weight:700">Modifier la page de vente</a></p>
        @endif

        <section class="ex-pp" aria-labelledby="exPName">
            <div class="ex-gal">
                <div class="ex-gal-main" id="exGal">
                    @forelse($photos as $i => $src)
                    <img src="{{ $src }}" alt="{{ $p['name'] }}{{ count($photos) > 1 ? ' — photo ' . ($i + 1) . ' sur ' . count($photos) : '' }}" @if($i === 0) class="is-on" fetchpriority="high" @else loading="lazy" @endif>
                    @empty
                    <span class="ex-noimg">{{ $p['name'] }}</span>
                    @endforelse
                    @if(count($photos))<button type="button" class="ex-gal-btn ex-gal-zoom" id="exZoomBtn" aria-label="Agrandir la photo"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5M11 8v6M8 11h6"/></svg></button>@endif
                    @if(count($photos) > 1)
                    <button type="button" class="ex-gal-btn ex-gal-prev" data-gal="-1" aria-label="Photo précédente"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></button>
                    <button type="button" class="ex-gal-btn ex-gal-next" data-gal="1" aria-label="Photo suivante"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button>
                    @endif
                </div>
                @if(count($photos) > 1)
                <div class="ex-gal-thumbs" id="exThumbs">@foreach($photos as $i => $src)<button type="button" data-i="{{ $i }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}" aria-label="Voir la photo {{ $i + 1 }}"><img src="{{ $src }}" alt="" loading="lazy"></button>@endforeach</div>
                @endif
            </div>

            <div>
                @foreach($dz['buy'] as $bk)
                @continue(!$bk['on'])
                @switch($bk['type'])
                @case('rating')
                @if($pm['rating'])
                <p class="ex-rate"{!! $sp($bk) ?: ' style="margin:0"' !!}><span class="ex-stars" aria-hidden="true">★★★★★</span><b>{{ $mention }}</b><span>Note {{ number_format($pm['rating']['avg'], 1, ',', ' ') }}/5 · {{ $pm['rating']['count'] }} avis clients de la boutique</span></p>
                @endif
                @break
                @case('title')
                <h1 id="exPName"{!! $sp($bk) !!}>{{ $p['name'] }}</h1>
                @break
                @case('hook')
                @if($c['hook'] !== '')<p class="ex-hook"{!! $sp($bk) !!}>{!! $hookHtml !!}</p>@endif
                @break
                @case('price')
                <p class="ex-pp-price"{!! $sp($bk) ?: ' style="margin-bottom:0"' !!}><span>{{ number_format($p['price'], 0, ',', ' ') }} {{ $s['currency'] }}</span>@if($p['old'] && !$dz['offer']['hide_strike'])<s>{{ number_format($p['old'], 0, ',', ' ') }} {{ $s['currency'] }}</s>@if($pct >= 5)<em>−{{ $pct }} %</em>@endif @endif</p>
                @break
                @case('benefits')
                @if(count($c['benefits']))
                <ul class="ex-benefits"{!! $sp($bk) !!}>@foreach($c['benefits'] as $b)<li>{{ $b }}</li>@endforeach</ul>
                @endif
                @break
                @case('delivery')
                <div class="ex-steps"{!! $sp($bk) !!}>
                    @if($c['promise'] !== '')<p>{{ $c['promise'] }}</p>@endif
                    <ol>
                        <li><i><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l-1 13H7zM9 7a3 3 0 0 1 6 0"/></svg></i>Commandé<small>Aujourd’hui</small></li>
                        <li><i><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></svg></i>Confirmé<small>Par téléphone</small></li>
                        <li><i><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l8 4.5v9L12 21l-8-4.5v-9z"/><path d="M4 7.5l8 4.5 8-4.5M12 12v9"/></svg></i>Livré<small>Payé à la réception</small></li>
                    </ol>
                </div>
                @break
                @case('offer')
                @if($dz['offer']['show'])<div id="exOffer"{!! $sp($bk) !!}></div>@endif
                @break
                @case('qty')
                <div id="exQtyBox"{!! $sp($bk) !!}></div>
                @break
                @case('addcart')
                <div id="exAddBox"{!! $sp($bk) !!}></div>
                @break
                @case('form')
                <div id="exFormBox"{!! $sp($bk) !!}></div>
                @break
                @case('trust')
                <div{!! $sp($bk) !!}>
                    <p class="ex-trust"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2l8 3v6c0 5-3.4 9.4-8 11-4.6-1.6-8-6-8-11V5z"/></svg>{{ $dz['trust']['text'] !== '' ? $dz['trust']['text'] : 'Commandez en toute confiance — paiement à la réception' }}</p>
                    @if($dz['trust']['cash'] || $dz['trust']['om'] || $dz['trust']['momo'])
                    <div class="ex-trust-pay">@if($dz['trust']['cash'])<span style="background:#E7F6EC;color:#15803D">Espèces à la livraison</span>@endif @if($dz['trust']['om'])<span style="background:#FF7900;color:#fff">Orange Money</span>@endif @if($dz['trust']['momo'])<span style="background:#FFCC00;color:#111">MTN MoMo</span>@endif</div>
                    @endif
                </div>
                @break
                @endswitch
                @endforeach
                <div id="exBuy"></div>
            </div>
        </section>
    </div>

    {{-- Sections sous le haut de page, dans l'ordre choisi par le vendeur (Boutique → Sections → Page produit) --}}
    @foreach($ppSections as $sec)
    @continue(!$sec['on'])
    @if(($sec['sp'][0] ?? 0) || ($sec['sp'][1] ?? 0))<div style="padding-top:{{ (int) $sec['sp'][0] }}px;padding-bottom:{{ (int) $sec['sp'][1] }}px">@endif
    @switch($sec['type'])
    @case('p_info')
    <section class="ex-info"@if($sec['sp'][0] ?? 0) style="padding-top:{{ (int) $sec['sp'][0] }}px"@endif>
        <div class="ex-wrap ex-narrow">
            <div class="ex-acc-list">
                @if($c['usage'] !== '')<details><summary>Comment l’utiliser ?</summary><p>{{ $c['usage'] }}</p></details>@endif
                @if($c['details'] !== '')<details><summary>Description détaillée</summary><p>{{ $c['details'] }}</p></details>@endif
                <details><summary>Livraison et garantie</summary><p>{{ $c['shipping'] !== '' ? $c['shipping'] : 'Après votre commande, la boutique vous appelle pour confirmer le délai et les frais de livraison. Vous payez en espèces au livreur, à la réception du colis, et vous suivez votre commande sur Shopio.' }}</p></details>
            </div>
        </div>
    </section>
    @break

    @case('p_highlight')
    @if($c['highlight']['title'] !== '')
    <section style="padding:0 0 clamp(48px,7vw,84px)">
        <div class="ex-wrap ex-narrow">
            <div class="ex-panel ex-hl" data-rv>
                <div class="ex-polas">@foreach([1, 2] as $i)<div class="ex-pola">@if($ph($i))<img src="{{ $ph($i) }}" alt="" loading="lazy">@else<span></span>@endif</div>@endforeach</div>
                <div class="ex-hl-mid">
                    <h2><span class="ex-acc">{{ $hlFirst }}</span> {{ implode(' ', $hlWords) }}</h2>
                    @if($c['highlight']['text'] !== '')<p>{{ $c['highlight']['text'] }}</p>@endif
                    <a class="ex-btn is-acc is-pill" href="#exCheckout" data-ex-order>{{ $c['highlight']['cta'] !== '' ? $c['highlight']['cta'] : 'Je le veux' }}</a>
                    <div class="ex-mini"><span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5 5-5.5"/></svg>Qualité vérifiée par la boutique</span><span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 6h13v10H1zM14 9h4l4 4v3h-8z"/></svg>Livraison à domicile</span><span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/></svg>Paiement à la livraison</span></div>
                </div>
                <div class="ex-polas is-right"><div class="ex-pola">@if($ph(0))<img src="{{ $ph(0) }}" alt="" loading="lazy">@else<span></span>@endif</div></div>
            </div>
        </div>
    </section>
    @endif
    @break

    @case('p_reviews')
    @if(count($pm['reviews']))
    <section class="ex-sec ex-tested" aria-labelledby="exTestedH" style="padding-top:0">
        <div class="ex-wrap">
            <div data-rv style="text-align:center"><h2 class="ex-h2" id="exTestedH">@if($sec['d']['title'] !== ''){{ $sec['d']['title'] }}@else Testé et approuvé par <span class="ex-serif">des clients de la boutique</span>@endif</h2><p class="ex-sub">Avis laissés par de vrais acheteurs après leur livraison.</p></div>
            <div class="ex-reviews">
                @foreach($pm['reviews']->take(3) as $i => $rv)
                <article class="ex-review" data-rv style="--d:{{ min($i, 5) * 70 }}ms">
                    <span class="ex-stars" role="img" aria-label="{{ max(1, min(5, $rv['rating'])) }} sur 5">{{ str_repeat('★', max(1, min(5, $rv['rating']))) }}<span style="opacity:.25">{{ str_repeat('★', 5 - max(1, min(5, $rv['rating']))) }}</span></span>
                    <p>{{ $rv['text'] }}</p>
                    <footer><i aria-hidden="true">{{ mb_strtoupper(mb_substr($rv['name'], 0, 1)) }}</i><span>{{ $rv['name'] }}<small>Client vérifié</small></span></footer>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @break

    @case('p_band')
    <div class="ex-band" aria-hidden="true" style="@if($dz['band']['bg'])background:{{ $dz['band']['bg'] }};@endif @if($dz['band']['ink'])color:{{ $dz['band']['ink'] }};@endif"><div class="ex-track">@for($i = 0; $i < 16; $i++)<span>{{ $dz['band']['text'] !== '' ? $dz['band']['text'] : $s['name'] }}</span>@endfor</div></div>
    @break

    @case('p_story')
    @if($c['story']['title'] !== '')
    <section class="ex-sec" style="padding-bottom:0">
        <div class="ex-wrap ex-narrow" style="display:grid;gap:clamp(30px,5vw,56px)">
            @if($c['story']['title'] !== '')
            <div class="ex-panel ex-split" data-rv>
                <div class="ex-split-img">@if($ph(0))<img src="{{ $ph(0) }}" alt="{{ $p['name'] }}" loading="lazy">@endif</div>
                <div><h2>{{ $c['story']['title'] }}</h2>@if($c['story']['kicker'] !== '')<p class="ex-caps">{{ $c['story']['kicker'] }}</p>@endif @if($c['story']['text'] !== '')<p class="ex-text">{{ $c['story']['text'] }}</p>@endif</div>
            </div>
            @endif
        </div>
    </section>
    @endif
    @break

    @case('p_audience')
    @if($c['audience']['title'] !== '')
    <section class="ex-sec" style="padding-bottom:0">
        <div class="ex-wrap ex-narrow" style="display:grid;gap:clamp(30px,5vw,56px)">
            @if($c['audience']['title'] !== '')
            <div class="ex-panel ex-split is-flip" data-rv>
                <div><h2>{{ $c['audience']['title'] }}</h2>@if($c['audience']['kicker'] !== '')<p class="ex-caps">{{ $c['audience']['kicker'] }}</p>@endif @if($c['audience']['text'] !== '')<p class="ex-text">{{ $c['audience']['text'] }}</p>@endif</div>
                <div class="ex-split-img">@if($ph(1))<img src="{{ $ph(1) }}" alt="" loading="lazy">@endif</div>
            </div>
            @endif
        </div>
    </section>
    @endif
    @break

    @case('p_results')
    @if($happy !== null || $pm['rating'] || $pm['delivered'])
    <section class="ex-sec" aria-labelledby="exResH" style="padding-bottom:0">
        <div class="ex-wrap ex-narrow" data-rv style="text-align:center">
            <h2 class="ex-h2" id="exResH">@if($sec['d']['title'] !== ''){{ $sec['d']['title'] }}@else Des résultats <span class="ex-acc">prouvés</span>@endif</h2>
            <div class="ex-results" style="text-align:left">
                @if($happy !== null)<div><b>{{ $happy }} %</b><span>des avis clients donnent 4 ou 5 étoiles</span></div>@endif
                @if($pm['rating'])<div><b>{{ number_format($pm['rating']['avg'], 1, ',', ' ') }}/5</b><span>note moyenne sur {{ $pm['rating']['count'] }} avis</span></div>@endif
                @if($pm['delivered'])<div><b>{{ number_format($pm['delivered'], 0, ',', ' ') }}</b><span>commandes livrées par la boutique</span></div>@endif
            </div>
            <p class="ex-sub" style="font-size:12.5px">Calculé sur les vrais avis et les vraies commandes de la boutique.</p>
        </div>
    </section>
    @endif
    @break

    @case('p_love')
    @if(count($c['love']['items']))
    <section class="ex-sec ex-love" aria-labelledby="exLoveH" style="padding-bottom:0">
        <div class="ex-wrap ex-narrow">
            <div class="ex-panel ex-split" data-rv>
                <div>
                    <h2 id="exLoveH">{{ implode(' ', $loveWords) }} <span class="ex-serif">{{ $loveLast }}</span></h2>
                    @if($c['love']['kicker'] !== '')<p class="ex-sub">{{ $c['love']['kicker'] }}</p>@endif
                    <ul>@foreach($c['love']['items'] as $it)<li><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5 5-5.5"/></svg><div><b>{{ $it['title'] }}</b>@if($it['text'] !== '')<span>{{ $it['text'] }}</span>@endif</div></li>@endforeach</ul>
                </div>
                <div class="ex-split-img">@if($ph(2))<img src="{{ $ph(2) }}" alt="" loading="lazy">@endif</div>
            </div>
        </div>
    </section>
    @endif
    @break

    @case('p_compare')
    @if(count($c['compare']['rows']))
    <section class="ex-sec" aria-labelledby="exCmpH" style="padding-bottom:0">
        <div class="ex-wrap ex-narrow">
            <div class="ex-panel" style="padding:clamp(26px,4vw,44px)" data-rv>
                <h2 class="ex-h2" id="exCmpH" style="text-align:center"><span class="ex-serif" style="font-size:1.15em">{{ $c['compare']['title'] !== '' ? $c['compare']['title'] : 'Pourquoi nous choisir ?' }}</span></h2>
                <div class="ex-compare">
                    <table>
                        <thead><tr><th scope="col"><span class="sm-sr">Critère</span></th><th scope="col" class="is-us">Notre produit</th><th scope="col">Autres</th></tr></thead>
                        <tbody>@foreach($c['compare']['rows'] as $row)<tr><td>{{ $row }}</td><td class="is-us"><span class="ex-yes" aria-label="Oui">✓</span></td><td><span class="ex-no" aria-label="Pas toujours">✕</span></td></tr>@endforeach</tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    @endif
    @break

    @case('p_related')
    <section class="ex-sec" id="exRelatedSec" aria-labelledby="exRelH">
        <div class="ex-wrap">
            <div style="display:flex;justify-content:space-between;align-items:baseline;gap:12px;flex-wrap:wrap" data-rv><h2 class="ex-h2" id="exRelH">{{ HS::txt('product', $sec, 'title') }}</h2><a href="{{ $s['url'] }}?vue=produits" style="font-weight:700;color:var(--acc-dark)">Voir tous les produits</a></div>
            <div class="ex-related" id="exRelated"></div>
        </div>
    </section>
    @break

    @case('p_faq')
    @include('public.shops.express._faq', ['faq' => $c['faq'], 'faqBg' => 'var(--sage)', 'faqTitle' => $sec['d']['title']])
    @break

    @default
    @include('public.shops.models._section', ['sec' => $sec, 'theme' => 'express'])
    @endswitch
    @if(($sec['sp'][0] ?? 0) || ($sec['sp'][1] ?? 0))</div>@endif
    @endforeach
</main>
@include('public.shops.express._footer', ['noFaq' => true])

@if($dz['sticky'])<div class="ex-sticky is-hidden" id="exSticky" aria-hidden="true"><b id="exStickyPrice"></b><a class="ex-btn is-acc @if($fm['anim'] !== 'none') ex-anim-{{ $fm['anim'] }} @endif" href="#exCheckout" data-ex-order tabindex="-1"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l-1 13H7zM9 7a3 3 0 0 1 6 0"/></svg>Commander</a></div>@endif
<div class="ex-zoom" id="exZoom" hidden role="dialog" aria-modal="true" aria-label="Photo agrandie"><img alt=""></div>

@include('public.shops.models._kit')
<script>
(function () {
    'use strict';
    const S = window.SM, D = S.data, esc = S.esc, $ = id => document.getElementById(id);
    const P = S.byId(@json($p['id']));
    const photos = @json($photos);
    if (S.liveProduct) S.liveProduct(@json($p['id'])); // vue en direct du vendeur : produit regardé

    /* ── Produits proches : même catégorie et prix proche d'abord ── */
    const rel = P ? S.suggest(P, 4) : [];
    if (rel.length && $('exRelated')) $('exRelated').innerHTML = rel.map(x => '<article class="ex-card"><a class="ex-card-img" href="' + esc(x.page) + '">' + (x.photo ? '<img src="' + esc(x.photo) + '" alt="" loading="lazy">' : '<span class="ex-noimg">' + esc(x.name) + '</span>') + '</a><div class="ex-card-body"><h3><a href="' + esc(x.page) + '">' + esc(x.name) + '</a></h3><p class="ex-price" style="margin:0 0 10px">' + esc(S.money(x.price)) + (x.old ? '<s>' + esc(S.money(x.old)) + '</s>' : '') + '</p><a class="ex-btn" href="' + esc(x.page) + '">Commander</a></div></article>').join('');
    else if ($('exRelatedSec')) $('exRelatedSec').hidden = true;

    /* ── Galerie : flèches, miniatures, glisser au doigt, zoom ── */
    const imgs = [...$('exGal').querySelectorAll('img')];
    let at = 0;
    function show(i) {
        if (!imgs.length) return;
        at = (i + imgs.length) % imgs.length;
        imgs.forEach((im, k) => im.classList.toggle('is-on', k === at));
        if ($('exThumbs')) $('exThumbs').querySelectorAll('button').forEach((b, k) => b.setAttribute('aria-current', k === at ? 'true' : 'false'));
    }
    $('exGal').addEventListener('click', e => { const b = e.target.closest('[data-gal]'); if (b) show(at + +b.dataset.gal); });
    if ($('exThumbs')) $('exThumbs').addEventListener('click', e => { const b = e.target.closest('[data-i]'); if (b) show(+b.dataset.i); });
    let x0 = null;
    $('exGal').addEventListener('touchstart', e => { x0 = e.touches[0].clientX; }, { passive: true });
    $('exGal').addEventListener('touchend', e => { if (x0 === null) return; const dx = e.changedTouches[0].clientX - x0; if (Math.abs(dx) > 40) show(at + (dx < 0 ? 1 : -1)); x0 = null; });
    const zoom = $('exZoom');
    if ($('exZoomBtn')) $('exZoomBtn').addEventListener('click', () => { zoom.querySelector('img').src = photos[at]; zoom.hidden = false; });
    zoom.addEventListener('click', () => { zoom.hidden = true; });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !zoom.hidden) zoom.hidden = true; });

    /* ── Achat : chaque élément dans son emplacement (ordre et affichage réglés dans l'éditeur produit) ── */
    const money = S.money;
    const FM = @json($fm), OF = @json($dz['offer']);
    const slot = id => $(id);
    const anim = FM.anim !== 'none' ? ' ex-anim-' + FM.anim : '';
    if (!P) { return; }
    const maxQ = P.stock !== null && P.stock !== undefined ? Math.max(0, Math.min(99, P.stock)) : 99;
    const options = [{ qty: 1, price: P.price, label: '' }].concat(P.packs || []).filter(o => o.qty <= Math.max(1, maxQ));
    let qty = options.some(o => o.qty === OF.preselect) ? OF.preselect : 1;
    const thumb = P.thumb ? ' style="background-image:url(\'' + esc(P.thumb) + '\')"' : '';

    if (P.variants || !S.canBuy(P)) {
        const first = ['exOffer', 'exQtyBox', 'exAddBox', 'exFormBox', 'exBuy'].map(slot).find(Boolean);
        first.innerHTML = '<div style="margin-top:24px">' + (P.variants
            ? '<a class="ex-btn is-acc" style="width:100%" href="' + esc(P.url) + '">Choisir les options et commander</a><p class="ex-co-cod">Ce produit a des options (taille, couleur…).</p>'
            : '<button type="button" class="ex-btn" style="width:100%" disabled>' + esc(S.buyLabel(P)) + '</button>') + '</div>';
    } else {
        if (slot('exOffer') && options.length > 1) slot('exOffer').innerHTML = '<div class="ex-packs" role="radiogroup" aria-labelledby="exPackH"><div class="ex-packs-h" id="exPackH">' + esc(OF.title || 'Pack et économie') + '</div>' + (OF.subtitle ? '<p>' + esc(OF.subtitle) + '</p>' : '') +
            options.map(o => { const full = P.price * o.qty, save = full - o.price, pc = Math.round(save / full * 100);
                return '<button type="button" class="ex-pack' + (OF.image ? '' : ' no-img') + '" role="radio" data-q="' + o.qty + '" aria-checked="false">' + (o.label ? '<span class="ex-flag">' + esc(o.label) + '</span>' : '') +
                    (OF.image ? '<i' + thumb + '><b>×' + o.qty + '</b></i>' : '') + '<span><strong>' + o.qty + ' produit' + (o.qty > 1 ? 's' : '') + (pc > 0 ? '<em>−' + pc + ' %</em>' : '') + '</strong>' + (save > 0 ? '<small>Vous économisez ' + esc(money(save)) + '</small>' : '') + (OF.unit && o.qty > 1 ? '<span class="ex-pack-unit">soit ' + esc(money(o.price / o.qty)) + ' / unité</span>' : '') + '</span>' +
                    '<span>' + esc(money(o.price)) + (save > 0 && !OF.hide_strike ? '<s>' + esc(money(full)) + '</s>' : '') + '</span></button>'; }).join('') + '</div>';
        if (slot('exQtyBox')) slot('exQtyBox').innerHTML = '<div class="ex-qty-row"><span>Quantité</span><div class="ex-qty"><button type="button" id="exMinus" aria-label="Retirer un">−</button><output id="exQty" aria-live="polite">1</output><button type="button" id="exPlus" aria-label="Ajouter un">+</button></div></div>';
        if (slot('exAddBox')) slot('exAddBox').innerHTML = '<button type="button" class="ex-btn is-line ex-add" id="exAdd"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 4h2l2.4 11h11L21 7H6.2"/><circle cx="9" cy="19.5" r="1"/><circle cx="18" cy="19.5" r="1"/></svg>Ajouter au panier</button>';
        // Formulaire intégré à la page, ou bouton qui ouvre le panier (formulaire de commande du panier)
        const formSlot = slot('exFormBox') || (FM.integrated ? null : slot('exAddBox'));
        if (formSlot && FM.integrated) formSlot.innerHTML =
            '<form class="ex-co" id="exCheckout" novalidate aria-labelledby="exCoH"><h2 id="exCoH">' + esc(FM.title) + '</h2>' +
                '<div class="ex-recap"><small>Récapitulatif</small><div><span id="exRecapName"></span><span id="exRecapLine"></span></div><div class="is-total"><span>Total à payer</span><span id="exTotal"></span></div></div>' +
                '<div class="ex-general" id="exGeneral" role="alert"></div>' +
                '<div class="ex-field"><label for="exName">Nom complet ' + (D.user ? '<small>(facultatif)</small>' : '<b aria-hidden="true">*</b>') + '</label><input id="exName" autocomplete="name" maxlength="80" placeholder="Votre prénom et nom" value="' + esc(D.user ? D.user.name : '') + '" aria-describedby="exNameErr"><p class="ex-err" id="exNameErr"></p></div>' +
                '<div class="ex-field"><label for="exPhone">Téléphone <b aria-hidden="true">*</b></label><div class="ex-tel"><span>+224</span><input id="exPhone" type="tel" inputmode="tel" autocomplete="tel-national" maxlength="16" placeholder="622 12 34 56" value="' + esc(D.user ? D.user.phone : '') + '" aria-describedby="exPhoneErr"></div><p class="ex-err" id="exPhoneErr"></p>' + (D.settings.carts ? '<small style="color:var(--ink-3);font-size:12px">Votre numéro sert seulement à la boutique pour finaliser votre commande.</small>' : '') + '</div>' +
                '<div class="ex-field"><label for="exCommune">Commune <b aria-hidden="true">*</b></label><select id="exCommune" aria-describedby="exCommuneErr"><option value="">Choisir…</option>' + D.communes.map(c => '<option>' + esc(c) + '</option>').join('') + '</select><p class="ex-err" id="exCommuneErr"></p></div>' +
                (FM.address ? '<div class="ex-field"><label for="exLandmark">Adresse ' + (FM.address_req ? '<b aria-hidden="true">*</b>' : '<small>(quartier, rue, repère)</small>') + '</label><input id="exLandmark" maxlength="120" placeholder="Ex. Kipé, près de la pharmacie" aria-describedby="exLandmarkErr"><p class="ex-err" id="exLandmarkErr"></p></div>' : '') +
                '<button type="submit" class="ex-btn is-acc' + anim + '" id="exSend">' + esc(FM.button) + '</button>' +
                (FM.note ? '<p class="ex-co-cod">' + esc(FM.note) + '</p>' : '') +
            '</form>';
        else if (formSlot) formSlot.insertAdjacentHTML('beforeend', '<button type="button" class="ex-btn is-acc' + anim + '" id="exOrderCart" style="width:100%;margin-top:12px">' + esc(FM.button) + '</button>' + (FM.note ? '<p class="ex-co-cod">' + esc(FM.note) + '</p>' : ''));

        function render() {
            const total = S.lineTotal(P, qty);
            if ($('exQty')) { $('exQty').textContent = qty; $('exMinus').disabled = qty <= 1; $('exPlus').disabled = qty >= maxQ; }
            document.querySelectorAll('.ex-pack').forEach(b => b.setAttribute('aria-checked', +b.dataset.q === qty ? 'true' : 'false'));
            if ($('exRecapName')) { $('exRecapName').textContent = P.name; $('exRecapLine').textContent = '× ' + qty; $('exTotal').textContent = money(total); }
            if ($('exStickyPrice')) $('exStickyPrice').textContent = money(total);
        }
        document.addEventListener('click', e => { const k = e.target.closest('.ex-pack'); if (k) { qty = +k.dataset.q; render(); } });
        if ($('exMinus')) { $('exMinus').addEventListener('click', () => { qty = Math.max(1, qty - 1); render(); }); $('exPlus').addEventListener('click', () => { qty = Math.min(maxQ, qty + 1); render(); }); }
        if ($('exAdd')) $('exAdd').addEventListener('click', () => S.add(P.id, $('exAdd'), qty));
        // Sans formulaire sur la page : le produit va au panier, qui s'ouvre sur sa propre commande
        const toCart = el => { S.add(P.id, el, qty); setTimeout(() => S.openCart(), 250); };
        if ($('exOrderCart')) $('exOrderCart').addEventListener('click', () => toCart($('exOrderCart')));
        render();

        /* Contrôles sous chaque champ, puis vraie commande (même adresse que le panier) */
        if ($('exCheckout')) {
        const F = { name: 'exName', phone: 'exPhone', commune: 'exCommune' };
        if ($('exLandmark')) F.landmark = 'exLandmark';
        // Ce que le client remplit est gardé pour la boutique (paniers abandonnés), jusqu'à sa commande
        if (S.capture) $('exCheckout').addEventListener('input', () => S.capture(() => ({ items: [{ id: P.id, qty: qty }], name: $('exName').value.trim(), phone: $('exPhone').value, commune: $('exCommune').value, landmark: $('exLandmark') ? $('exLandmark').value.trim() : '' })));
        const setErr = (k, m) => { const el = $(F[k]); $(F[k] + 'Err').textContent = m || ''; if (m) el.setAttribute('aria-invalid', 'true'); else el.removeAttribute('aria-invalid'); };
        Object.keys(F).forEach(k => $(F[k]).addEventListener('input', () => setErr(k, '')));
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        $('exCheckout').addEventListener('submit', async e => {
            e.preventDefault();
            $('exGeneral').textContent = '';
            let ok = true;
            const name = $('exName').value.trim();
            if (!D.user && name.split(/\s+/).filter(w => w.length >= 2).length < 2) { setErr('name', name ? 'Indiquez votre prénom et votre nom.' : 'Indiquez votre nom complet.'); ok = false; }
            let d = $('exPhone').value.replace(/\D/g, ''); if (d.length === 12 && d.startsWith('224')) d = d.slice(3);
            if (!/^6\d{8}$/.test(d)) { setErr('phone', d ? 'Le numéro doit avoir 9 chiffres et commencer par 6.' : 'Indiquez votre numéro de téléphone.'); ok = false; }
            if (!$('exCommune').value) { setErr('commune', 'Choisissez votre commune.'); ok = false; }
            if (FM.address && FM.address_req && !$('exLandmark').value.trim()) { setErr('landmark', 'Indiquez votre adresse (quartier, rue, repère).'); ok = false; }
            if (!ok) { const bad = $('exCheckout').querySelector('[aria-invalid="true"]'); if (bad) bad.focus(); return; }
            const btn = $('exSend'); btn.disabled = true; btn.textContent = 'Envoi…';
            try {
                const r = await fetch(D.urls.order, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ visitor: S.visitor, items: [{ id: P.id, qty: qty }], name: name, phone: d, commune: $('exCommune').value, landmark: $('exLandmark') ? $('exLandmark').value.trim() : '' }) });
                const j = await r.json().catch(() => ({}));
                if (r.status === 201) {
                    $('exCheckout').innerHTML = '<div class="ex-done" role="status"><svg class="sm-check" viewBox="0 0 100 100" aria-hidden="true"><circle cx="50" cy="50" r="44"/><path d="M30 52l14 14 26-30"/></svg><h3>Commande n° ' + j.id + ' envoyée</h3><p>' + esc(D.shop.name) + ' vous appelle au ' + esc(d.replace(/(\d{3})(\d{2})(\d{2})(\d{2})/, '$1 $2 $3 $4')) + ' pour confirmer.</p><p><strong>Total : ' + esc(money(j.total)) + '</strong> · à payer à la livraison</p>' +
                        (j.wa ? '<a class="ex-btn is-acc" href="' + esc(j.wa) + '" target="_blank" rel="noopener">Envoyer aussi sur WhatsApp</a>' : '') + '<a class="ex-btn is-line" href="' + esc(j.tracking) + '">Suivre ma commande</a></div>';
                    if ($('exSticky')) $('exSticky').remove();
                    $('exCheckout').scrollIntoView({ behavior: S.reduce ? 'auto' : 'smooth', block: 'center' });
                    return;
                }
                if (j.errors) Object.entries(j.errors).forEach(([k, v]) => { if (F[k]) setErr(k, v[0]); else $('exGeneral').textContent = v[0]; });
                else $('exGeneral').textContent = j.message || (r.status === 429 ? 'Trop d’essais : patientez une minute puis réessayez.' : 'La commande n’a pas pu être envoyée. Réessayez.');
            } catch (x) { $('exGeneral').textContent = 'Connexion impossible : vérifiez votre internet puis réessayez.'; }
            btn.disabled = false; btn.textContent = FM.button;
        });
        }
    }

    /* ── Barre « Commander » flottante : vers le formulaire, sinon vers le panier ── */
    const sticky = $('exSticky');
    if (sticky) {
        const target = $('exCheckout') || $('exOrderCart');
        if (!target || P.variants || !S.canBuy(P)) { sticky.remove(); }
        else {
            const toggle = hide => { sticky.classList.toggle('is-hidden', hide); sticky.setAttribute('aria-hidden', hide ? 'true' : 'false'); sticky.querySelector('a').tabIndex = hide ? -1 : 0; };
            let seen = false, pastTop = false;
            const upd = () => toggle(seen || !pastTop);
            new IntersectionObserver(([e]) => { seen = e.isIntersecting; upd(); }).observe(target);
            window.addEventListener('scroll', () => { pastTop = window.scrollY > 500; upd(); }, { passive: true });
        }
    }
    document.addEventListener('click', e => {
        const a = e.target.closest('[data-ex-order]');
        if (!a) return;
        if (!$('exCheckout')) { if ($('exOrderCart')) { e.preventDefault(); $('exOrderCart').click(); } return; }
        e.preventDefault();
        $('exCheckout').scrollIntoView({ behavior: S.reduce ? 'auto' : 'smooth', block: 'center' });
        setTimeout(() => { const f = $('exName'); if (f && !f.value) f.focus({ preventScroll: true }); }, S.reduce ? 0 : 500);
    });

    /* ── Apparitions au défilement ── */
    const io = 'IntersectionObserver' in window && !S.reduce ? new IntersectionObserver(es => es.forEach(en => { if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); } }), { rootMargin: '0px 0px -8% 0px' }) : null;
    document.querySelectorAll('[data-rv]').forEach(el => io ? io.observe(el) : el.classList.add('is-in'));
})();
</script>
</body>
</html>
