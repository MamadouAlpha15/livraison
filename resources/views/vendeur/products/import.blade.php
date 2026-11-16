{{--
    resources/views/vendeur/products/import.blade.php
    Route     : GET /products/importer → Vendeur\ProductImportController@show → name('products.import')
    Rôle      : « L'Importateur Produit Shopio ». Le vendeur colle le lien d'un produit (Alibaba, AliExpress, 1688,
                Amazon, Jumia, Temu, Shein, Shopify…). Shopio IA lit la page (sinon : texte copié, captures d'écran ou
                bouton « Importer dans Shopio » des favoris), vérifie que le produit est autorisé et prépare la page :
                aperçu tel que le client le verra, calcul du prix en GNF en 3 lignes (fait par Shopio, pas par l'IA),
                ce qui reste à compléter, puis « Je publie ce produit comme ça, ou tu veux changer quelque chose ? ».
                Créer → produit en brouillon, ouvert dans l'éditeur (products.page.edit).
    Variables : $shop, $devise, $pro, $aiReady, $categories
--}}
@extends('layouts.app')
@section('title', 'Importer un produit · ' . $shop->name)
@php
    $bodyClass = 'is-dashboard';
    $ipData = [
        'pro' => $pro, 'aiReady' => $aiReady, 'categories' => $categories, 'devise' => $devise,
        'payment' => \App\Services\ProductImportAi::PAYMENT_BENEFIT,
        'urls' => ['analyze' => route('products.import.analyze'), 'create' => route('products.import.create')],
    ];
@endphp

@push('styles')
<style>
.ip { max-width: 1180px; margin: 0 auto; padding: 8px 4px 60px; }
.ip-back { display: inline-flex; color: var(--muted); font-size: 14px; font-weight: 700; text-decoration: none; margin-bottom: 10px; }
.ip h1 { margin: 0; font: 800 24px var(--font); letter-spacing: -.02em; }
.ip-sub { margin: 6px 0 20px; color: var(--muted); font-size: 14px; }
.ip-grid { display: grid; grid-template-columns: minmax(0, 400px) minmax(0, 1fr); gap: 20px; align-items: start; }
.ip-card { display: grid; gap: 14px; padding: 20px; border-radius: 20px; background: var(--surface); box-shadow: 0 0 0 1px var(--border); }
.ip-card h2 { margin: 0; font: 800 16px var(--font); }
.ip-f { display: grid; gap: 6px; }
.ip-f label, .ip-lbl { font-size: 13px; font-weight: 800; } .ip-f label small { font-weight: 600; color: var(--muted); }
.ip-f input, .ip-f textarea, .ip-f select { width: 100%; min-height: 46px; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 14px; background: var(--bg); color: var(--text); font: 500 15px var(--font); }
.ip-f textarea { min-height: 96px; resize: vertical; line-height: 1.5; }
.ip-f input:focus, .ip-f textarea:focus, .ip-f select:focus { outline: 0; border-color: var(--sv-blue, #1F7CF6); box-shadow: 0 0 0 3px var(--sv-b50, #EAF2FF); }
.ip-f input.is-ask { border-color: #F59E0B; box-shadow: 0 0 0 3px #FEF3C7; }
.ip-row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.ip-help { margin: 0; color: var(--muted); font-size: 12.5px; line-height: 1.45; }
.ip-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 48px; padding: 0 22px; border: 0; border-radius: 999px; background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1.5px var(--border); font: 800 14.5px var(--font); cursor: pointer; text-decoration: none; }
.ip-btn.is-ai { background: linear-gradient(135deg, #1668D8, #7C3AED); color: #fff; box-shadow: none; }
.ip-btn.is-main { background: var(--sv-btn, #1668D8); color: #fff; box-shadow: none; }
.ip-btn:disabled { opacity: .5; cursor: progress; }
.ip-more summary { cursor: pointer; font-size: 13.5px; font-weight: 800; color: var(--sv-btn, #1668D8); }
.ip-more > div { display: grid; gap: 12px; margin-top: 12px; }
.ip-way { display: grid; gap: 6px; padding: 12px 14px; border-radius: 14px; background: var(--bg); box-shadow: inset 0 0 0 1px var(--border); font-size: 13px; line-height: 1.5; }
.ip-way ol { margin: 0; padding-left: 20px; display: grid; gap: 4px; }
.ip-bm { display: inline-flex; padding: 5px 12px; border-radius: 999px; background: linear-gradient(135deg, #1668D8, #7C3AED); color: #fff !important; font-weight: 800; text-decoration: none; cursor: grab; white-space: nowrap; }
.ip-msg { margin: 0; padding: 12px 14px; border-radius: 14px; font-size: 14px; font-weight: 700; line-height: 1.5; }
.ip-msg.is-err { background: var(--sv-err50, #FDECEC); color: var(--sv-err, #B91C1C); }
.ip-msg.is-warn { background: #FEF3C7; color: #78350F; }
.ip-msg.is-ok { background: #E7F8EF; color: #065F46; }
.ip-msg:empty { display: none; }
.ip-wait { text-align: center; padding: 40px 10px; color: var(--muted); }
.ip-orb { width: 70px; height: 70px; margin: 0 auto 14px; border-radius: 22px; background: linear-gradient(135deg, #1668D8, #7C3AED); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 30px; animation: ipFloat 2.4s ease-in-out infinite; }
@keyframes ipFloat { 50% { transform: translateY(-8px) rotate(4deg); } }
/* Aperçu comme le client le verra */
.ip-prev { border-radius: 18px; overflow: hidden; box-shadow: 0 0 0 1px var(--border); background: #fff; color: #0F172A; }
.ip-prev-tag { padding: 8px 14px; background: #0F172A; color: #fff; font-size: 12px; font-weight: 800; letter-spacing: .02em; }
.ip-prev-in { display: grid; gap: 12px; padding: 16px; }
.ip-pics { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
.ip-pic { position: relative; aspect-ratio: 1; border-radius: 12px; overflow: hidden; background: #F1F5F9; border: 0; padding: 0; cursor: pointer; box-shadow: 0 0 0 1px #E2E8F0; }
.ip-pic img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ip-pic[aria-pressed="false"] img { opacity: .3; filter: grayscale(1); }
.ip-pic b { position: absolute; top: 6px; right: 6px; width: 22px; height: 22px; border-radius: 50%; background: #1668D8; color: #fff; font-size: 13px; display: flex; align-items: center; justify-content: center; }
.ip-pic[aria-pressed="false"] b { background: rgba(15,23,42,.6); }
.ip-prev h3 { margin: 0; font: 800 20px var(--font); line-height: 1.25; }
.ip-price { display: flex; align-items: baseline; gap: 10px; font-weight: 800; font-size: 22px; color: #1668D8; }
.ip-price s { color: #94A3B8; font-size: 15px; font-weight: 600; }
.ip-hook { margin: 0; font-size: 15px; font-weight: 600; color: #334155; }
.ip-bens { margin: 0; padding: 0; list-style: none; display: grid; gap: 6px; }
.ip-bens li { padding: 8px 12px; border-radius: 10px; background: #F1F5F9; font-size: 14px; font-weight: 600; }
.ip-prev p.ip-desc { margin: 0; font-size: 14px; line-height: 1.6; white-space: pre-line; color: #334155; }
.ip-prev h4 { margin: 4px 0 0; font-size: 14px; font-weight: 800; }
.ip-specs { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.ip-specs td { padding: 7px 10px; border-bottom: 1px solid #E2E8F0; vertical-align: top; } .ip-specs td:first-child { color: #64748B; width: 40%; }
.ip-todo { display: inline; padding: 1px 6px; border-radius: 6px; background: #FEF3C7; color: #92400E; font-weight: 800; font-size: 12.5px; }
.ip-faq { display: grid; gap: 8px; } .ip-faq div { padding: 10px 12px; border-radius: 10px; background: #F8FAFC; font-size: 13.5px; } .ip-faq b { display: block; margin-bottom: 3px; }
.ip-calc { margin: 0; padding: 0; list-style: none; display: grid; gap: 6px; font-size: 13.5px; line-height: 1.5; }
.ip-calc li { padding: 9px 12px; border-radius: 10px; background: var(--bg); box-shadow: inset 0 0 0 1px var(--border); font-variant-numeric: tabular-nums; }
.ip-list { margin: 0; padding-left: 20px; display: grid; gap: 5px; font-size: 13.5px; line-height: 1.5; }
.ip-check { display: flex; gap: 10px; align-items: flex-start; font-size: 14px; font-weight: 600; line-height: 1.5; }
.ip-check input { width: 20px; height: 20px; margin-top: 2px; flex: none; }
.ip-ask { padding: 14px 16px; border-radius: 16px; background: linear-gradient(135deg, #EAF2FF, #F3E8FF); display: grid; gap: 12px; }
.ip-ask p { margin: 0; font-size: 15px; font-weight: 800; }
.ip-ask div { display: flex; gap: 10px; flex-wrap: wrap; }
.ip-json { font-size: 12px; } .ip-json pre { max-height: 320px; overflow: auto; padding: 12px; border-radius: 12px; background: #0F172A; color: #E2E8F0; white-space: pre-wrap; word-break: break-word; }
.ip-shots { display: flex; gap: 6px; flex-wrap: wrap; } .ip-shots img { width: 56px; height: 56px; object-fit: cover; border-radius: 8px; box-shadow: 0 0 0 1px var(--border); }
[data-theme="dark"] .ip-ask { background: linear-gradient(135deg, #13284B, #2A1B4A); }
@media (max-width: 960px) { .ip-grid { grid-template-columns: minmax(0, 1fr); } }
@media (max-width: 520px) { .ip-pics { grid-template-columns: repeat(2, minmax(0, 1fr)); } .ip-row2 { grid-template-columns: minmax(0, 1fr); } }
@media (prefers-reduced-motion: reduce) { .ip-orb { animation: none; } }
</style>
@include('boutique._partials.sv-theme')
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'products'])
    <main class="main vs-main">
        <div class="ip">
            <a class="ip-back" href="{{ route('products.index') }}">← Retour aux produits</a>
            <h1>Importer un produit depuis un lien</h1>
            <p class="ip-sub">Alibaba, AliExpress, 1688, Amazon, Jumia, Temu, Shein, une boutique Shopify… Shopio IA lit la page, prépare ta page produit en français simple et calcule ton prix en {{ $devise }}. Rien n’est inventé.</p>

            <div class="ip-grid">
                {{-- Colonne gauche : le lien et l'aide à la lecture --}}
                <form class="ip-card" id="ipForm" novalidate>
                    <h2>1. Le produit</h2>
                    @unless($pro)<p class="ip-msg is-warn">L’Importateur Produit Shopio est inclus dans le <b>plan Pro</b>. <a href="{{ route('boutique.subscription.upgrade') }}">Passer au plan Pro</a></p>@endunless
                    @if($pro && !$aiReady)<p class="ip-msg is-warn">Shopio IA n’est pas encore configuré sur ce serveur.</p>@endif
                    <div class="ip-f"><label for="ipUrl">Lien du produit *</label><input id="ipUrl" name="url" type="url" inputmode="url" autocomplete="off" placeholder="https://… (la page d’un seul produit)" required></div>
                    <details class="ip-more">
                        <summary>Infos de ta boutique <small>(facultatif)</small></summary>
                        <div>
                            <div class="ip-f"><label for="ipVille">Ville de livraison</label><input id="ipVille" name="ville" maxlength="60" placeholder="Ex. Conakry"></div>
                            <div class="ip-f"><label for="ipDelai">Délai de livraison</label><input id="ipDelai" name="delai" maxlength="120" placeholder="Ex. 24 à 48 h à Conakry"></div>
                            <div class="ip-f"><label for="ipEchange">Échange ou retour</label><input id="ipEchange" name="echange" maxlength="300" placeholder="Ex. échange sous 3 jours si le produit est abîmé"></div>
                        </div>
                    </details>
                    <details class="ip-more" id="ipHelp">
                        <summary>Le site bloque ? Aide Shopio à lire la page</summary>
                        <div>
                            <div class="ip-way">
                                <b>Copier-coller (même sur téléphone)</b>
                                <span>Sur la page du produit : tout sélectionner, copier, puis colle ici.</span>
                                <div class="ip-f"><textarea id="ipText" name="text" maxlength="60000" placeholder="Colle ici le texte de la page du produit…" aria-label="Texte copié de la page"></textarea></div>
                            </div>
                            <div class="ip-way">
                                <b>Captures d’écran (4 au plus)</b>
                                <span>Le titre, le prix, les caractéristiques : Shopio IA lit les captures.</span>
                                <input type="file" id="ipShots" accept="image/jpeg,image/png,image/webp" multiple aria-label="Captures d’écran de la page">
                                <div class="ip-shots" id="ipShotsPrev"></div>
                            </div>
                            <div class="ip-way">
                                <b>Sur ordinateur : le bouton « Importer dans Shopio »</b>
                                <ol>
                                    <li>Glisse ce bouton dans ta barre de favoris (une seule fois) : <a class="ip-bm" href="{{ \App\Support\ImportBookmarklet::href(route('products.import')) }}" onclick="event.preventDefault();alert('Glisse ce bouton dans ta barre de favoris (ne clique pas dessus ici).')">📥 Importer dans Shopio</a></li>
                                    <li>Ouvre la page du produit (AliExpress, Alibaba…).</li>
                                    <li>Touche « Importer dans Shopio » dans tes favoris : cette page s’ouvre et lit le produit.</li>
                                </ol>
                                <small class="ip-help">Barre de favoris cachée ? Ctrl+Maj+B (Chrome, Edge).</small>
                            </div>
                        </div>
                    </details>
                    <p class="ip-msg" id="ipFormMsg" role="alert"></p>
                    <button type="submit" class="ip-btn is-ai" id="ipGo" @disabled(!$aiReady)>✨ Analyser avec Shopio IA</button>
                    <p class="ip-help">Environ 20 à 60 secondes. Shopio IA refuse les contrefaçons et les produits interdits en Guinée.</p>
                </form>

                {{-- Colonne droite : le résultat --}}
                <section id="ipOut" aria-live="polite">
                    <div class="ip-card ip-wait" id="ipEmpty"><div class="ip-orb" aria-hidden="true">🔗</div><b>Colle le lien d’un produit</b><p class="ip-help">L’aperçu de ta page produit s’affichera ici, avec ton prix en {{ $devise }}.</p></div>
                </section>
            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    const G = @json($ipData);
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const $ = id => document.getElementById(id);
    const esc = v => String(v == null ? '' : v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const gnf = n => Math.round(Number(n) || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' GNF';
    const num = n => String(Math.round(Number(n) * 100) / 100).replace('.', ',');
    const todo = s => esc(s).replace(/\[[^\]]*(COMPLÉTER|VILLE)[^\]]*\]/gi, m => '<span class="ip-todo">' + m + '</span>');
    let R = null, hint = null, busy = false;
    // Saisie du vendeur : prix, stock, retouches de la page
    const E = { taux: '', transport: '', douane: '', marge: '', prix: '', ancien: '', stock: '', ville: '', droits: false, photos: [] };

    /* ───────── Calcul du prix (le même que App\Services\ProductImportAi::pricing) ───────── */
    function pricing() {
        const s = R.source, val = k => E[k] === '' ? null : Number(String(E[k]).replace(/\s/g, '').replace(',', '.'));
        const rate = val('taux'), transport = val('transport'), customs = val('douane'), margin = val('marge');
        const manque = [];
        if (!s.prix_fournisseur) manque.push(['', 'Le prix du fournisseur (non trouvé sur la page)']);
        if (rate === null || !(rate > 0)) manque.push(['taux', 'Quel est le taux du jour : 1 ' + (s.devise || 'unité') + ' = combien de GNF ? [TAUX À COMPLÉTER]']);
        if (transport === null) manque.push(['transport', 'Combien coûte le transport par pièce jusqu’en Guinée (en GNF) ?']);
        if (customs === null) manque.push(['douane', 'Quelle est la douane (en %) ?']);
        if (margin === null) manque.push(['marge', 'Quelle marge veux-tu (en %) ?']);
        if (manque.length) return { manque };
        const revient = s.prix_fournisseur * rate * (1 + customs / 100) + transport;
        const vente = Math.ceil(Math.round(revient * (1 + margin / 100) * 100) / 100 / 5000) * 5000;
        const minimum = Math.ceil(Math.round(revient * 100) / 100 / 1000) * 1000;
        return { revient, vente, minimum, manque: [], lignes: [
            'Prix de revient : ' + num(s.prix_fournisseur) + ' ' + s.devise + ' × ' + gnf(rate).replace(' GNF', '') + ' × (1 + ' + num(customs) + ' % de douane) + ' + gnf(transport) + ' de transport = ' + gnf(revient),
            'Prix de vente suggéré : ' + gnf(revient) + ' + ' + num(margin) + ' % de marge, arrondi aux 5 000 supérieurs = ' + gnf(vente),
            'Prix minimum privé (négociation dans le chat, jamais montré au client) : ' + gnf(minimum),
        ] };
    }

    /* ───────── Résultat ───────── */
    function benefits() {
        const v = (E.ville || '').trim();
        return R.page.avantages_produit.slice(0, 2).concat(['🚚 Livraison à ' + (v || '[VILLE]'), G.payment]);
    }
    function aCompleter(pr) {
        const list = R.a_completer.slice();
        pr.manque.forEach(m => list.push(m[1]));
        if (E.stock === '') list.push('Ton vrai stock (le stock du fournisseur n’est pas le tien)');
        if (!(E.ville || '').trim()) list.push('Ta ville de livraison');
        if (R.source.moq) list.push('Le fournisseur vend par ' + R.source.moq + ' pièces minimum : prévois ta commande.');
        if (R.page.variantes.length) list.push('Crée les variantes (' + R.page.variantes.map(v => v.type).join(', ') + ') dans la fiche du produit pour que le client choisisse.');
        if (!E.droits) list.push('Confirmer que tu as le droit d’utiliser ces photos et ces textes');
        return list;
    }
    function produitJson(pr) {
        const p = R.page;
        return {
            source: { site: R.source.site, lien: R.source.lien, titre_origine: R.source.titre_origine, prix_fournisseur: R.source.prix_fournisseur, devise: R.source.devise, moq: R.source.moq, fournisseur: R.source.fournisseur },
            titre: p.titre, accroche: p.accroche, avantages: benefits(), description: p.description,
            caracteristiques: p.caracteristiques, variantes: p.variantes, contenu_colis: p.contenu_colis, categorie: p.categorie,
            prix_gnf: Number(E.prix) || pr.vente || 0, ancien_prix_gnf: Number(E.ancien) || null, prix_minimum_gnf: pr.minimum || 0,
            calcul: { taux_change: Number(E.taux) || 0, transport_gnf: Number(E.transport) || 0, douane_pourcent: Number(E.douane) || 0, marge_pourcent: Number(E.marge) || 0, prix_revient_gnf: pr.revient ? Math.round(pr.revient) : 0 },
            stock: Number(E.stock) || 0, photos: E.photos, photos_fournisseur: true, droits_confirmes: E.droits,
            faq: p.faq, a_completer: aCompleter(pr),
        };
    }
    const f = (id, label, val, attrs) => '<div class="ip-f"><label for="' + id + '">' + label + '</label><input id="' + id + '" value="' + esc(val) + '" ' + (attrs || '') + '></div>';

    function render() {
        const out = $('ipOut');
        if (!R) return;
        if (R.statut !== 'ok') {
            const cls = R.statut === 'refuse' ? 'is-err' : 'is-warn';
            const title = { refuse: '⛔ Produit refusé', doute: '⚠️ À vérifier avant de continuer', illisible: '📄 Page illisible', pas_produit: '🔗 Ce n’est pas la page d’un produit' }[R.statut];
            out.innerHTML = '<div class="ip-card"><h2>' + title + '</h2><p class="ip-msg ' + cls + '">' + esc(R.message) + '</p>' +
                (R.statut === 'illisible' ? '<p class="ip-help">Ouvre « Le site bloque ? Aide Shopio à lire la page » à gauche : copie-colle le texte, ajoute des captures d’écran, ou utilise le bouton des favoris.</p>' : '') + '</div>';
            if (R.statut === 'illisible') $('ipHelp').open = true;
            return;
        }
        const pr = pricing(), p = R.page;
        // Le prix suit la suggestion tant que le vendeur ne l'a pas écrit lui-même
        if (!E.prixTouched && pr.vente) E.prix = String(pr.vente);
        const ask = pr.manque.find(m => m[0]);
        out.innerHTML =
            '<div class="ip-card">' +
            '<h2>2. Ta page produit, comme le client la verra</h2>' +
            '<div class="ip-prev"><div class="ip-prev-tag">APERÇU · ' + esc(R.source.site) + '</div><div class="ip-prev-in">' +
            (R.photos.length ? '<div class="ip-pics" role="group" aria-label="Photos à garder (4 au plus)">' + R.photos.map((u, i) => '<button type="button" class="ip-pic" data-pic="' + i + '" aria-pressed="' + E.photos.includes(u) + '" title="Garder ou retirer cette photo"><img src="' + esc(u) + '" alt="Photo ' + (i + 1) + '" loading="lazy" referrerpolicy="no-referrer"><b>' + (E.photos.includes(u) ? '✓' : '+') + '</b></button>').join('') + '</div>' : '<p class="ip-help">Aucune photo trouvée : ajoute tes vraies photos du produit après la création.</p>') +
            '<h3 id="ipPvTitle">' + esc(p.titre) + '</h3>' +
            '<div class="ip-price"><span id="ipPvPrice">' + (Number(E.prix) ? gnf(E.prix) : '<span class="ip-todo">[PRIX À COMPLÉTER]</span>') + '</span>' + (Number(E.ancien) > Number(E.prix) ? '<s>' + gnf(E.ancien) + '</s>' : '') + '</div>' +
            (p.accroche ? '<p class="ip-hook">' + todo(p.accroche) + '</p>' : '') +
            '<ul class="ip-bens">' + benefits().map(b => '<li>' + todo(b) + '</li>').join('') + '</ul>' +
            (p.description ? '<p class="ip-desc">' + todo(p.description) + '</p>' : '') +
            (p.caracteristiques.length ? '<h4>Caractéristiques</h4><table class="ip-specs">' + p.caracteristiques.map(c => '<tr><td>' + esc(c.nom) + '</td><td>' + todo(c.valeur) + '</td></tr>').join('') + '</table>' : '') +
            (p.variantes.length ? '<h4>Variantes</h4>' + p.variantes.map(v => '<p class="ip-desc"><b>' + esc(v.type) + ' :</b> ' + v.options.map(esc).join(', ') + '</p>').join('') : '') +
            (p.contenu_colis.length ? '<h4>Dans le colis</h4><ul class="ip-list">' + p.contenu_colis.map(c => '<li>' + esc(c) + '</li>').join('') + '</ul>' : '') +
            (p.faq.length ? '<h4>Questions fréquentes</h4><div class="ip-faq">' + p.faq.map(q => '<div><b>' + todo(q.question) + '</b>' + todo(q.reponse) + '</div>').join('') + '</div>' : '') +
            '</div></div>' +
            (p.conseil_photo ? '<p class="ip-msg is-warn">📸 ' + esc(p.conseil_photo) + '</p>' : '') +
            '</div>' +

            '<div class="ip-card" style="margin-top:16px"><h2>3. Ton prix en ' + esc(G.devise) + '</h2>' +
            '<p class="ip-help">Prix lu chez le fournisseur : <b>' + (R.source.prix_fournisseur ? num(R.source.prix_fournisseur) + ' ' + esc(R.source.devise) : 'non trouvé') + '</b>' + (R.source.moq ? ' · minimum ' + R.source.moq + ' pièces' : '') + (R.source.fournisseur ? ' · ' + esc(R.source.fournisseur) : '') + '. Ce n’est jamais ton prix de vente.</p>' +
            (ask ? '<p class="ip-msg is-warn" id="ipAsk">' + todo(ask[1]) + '</p>' : '') +
            '<div class="ip-row2">' + f('ipTaux', 'Taux : 1 ' + esc(R.source.devise || 'unité') + ' = ? GNF', E.taux, 'inputmode="decimal" data-e="taux"' + (ask && ask[0] === 'taux' ? ' class="is-ask"' : '')) + f('ipTransport', 'Transport par pièce (GNF)', E.transport, 'inputmode="numeric" data-e="transport"' + (ask && ask[0] === 'transport' ? ' class="is-ask"' : '')) +
            f('ipDouane', 'Douane (%)', E.douane, 'inputmode="decimal" data-e="douane"' + (ask && ask[0] === 'douane' ? ' class="is-ask"' : '')) + f('ipMarge', 'Ta marge (%)', E.marge, 'inputmode="decimal" data-e="marge"' + (ask && ask[0] === 'marge' ? ' class="is-ask"' : '')) + '</div>' +
            (pr.lignes ? '<ul class="ip-calc">' + pr.lignes.map(l => '<li>' + esc(l) + '</li>').join('') + '</ul>' : '') +
            '<div class="ip-row2">' + f('ipPrix', 'Ton prix de vente (GNF) *', E.prix, 'inputmode="numeric" data-e="prix"') + f('ipAncien', 'Prix barré (facultatif)', E.ancien, 'inputmode="numeric" data-e="ancien"') + '</div>' +
            (pr.minimum && Number(E.prix) && Number(E.prix) < pr.minimum ? '<p class="ip-msg is-err">Attention : ce prix est sous ton prix de revient (' + gnf(pr.minimum) + ') : tu vendrais à perte.</p>' : '') +
            '</div>' +

            '<div class="ip-card" style="margin-top:16px"><h2>4. À compléter</h2>' +
            '<div class="ip-row2">' + f('ipStock', 'Ton vrai stock *', E.stock, 'inputmode="numeric" data-e="stock"') + f('ipVille2', 'Ville de livraison', E.ville, 'maxlength="60" data-e="ville" placeholder="Ex. Conakry"') + '</div>' +
            '<div class="ip-f"><label for="ipTitre">Titre (60 caractères au plus)</label><input id="ipTitre" maxlength="60" value="' + esc(p.titre) + '" data-p="titre"></div>' +
            '<div class="ip-f"><label for="ipAccroche">Accroche</label><input id="ipAccroche" maxlength="220" value="' + esc(p.accroche) + '" data-p="accroche"></div>' +
            p.avantages_produit.map((b, i) => '<div class="ip-f"><label for="ipAv' + i + '">Avantage du produit ' + (i + 1) + '</label><input id="ipAv' + i + '" maxlength="80" value="' + esc(b) + '" data-av="' + i + '"></div>').join('') +
            '<div class="ip-f"><label for="ipDesc">Description</label><textarea id="ipDesc" maxlength="1200" data-p="description">' + esc(p.description) + '</textarea></div>' +
            '<div class="ip-f"><label for="ipCat">Catégorie</label><select id="ipCat" data-p="categorie">' + G.categories.map(c => '<option' + (c === p.categorie ? ' selected' : '') + '>' + esc(c) + '</option>').join('') + '</select></div>' +
            '<ul class="ip-list">' + aCompleter(pr).map(t => '<li>' + todo(t) + '</li>').join('') + '</ul>' +
            '<p class="ip-help">Ce qui reste marqué « À COMPLÉTER » n’est jamais montré aux clients : complète-le ensuite dans l’éditeur du produit.</p>' +
            '<label class="ip-check"><input type="checkbox" id="ipDroits"' + (E.droits ? ' checked' : '') + '> Je confirme avoir le droit d’utiliser ces photos et ces textes (mon produit, mon fournisseur).</label>' +
            '<div class="ip-ask"><p>Je publie ce produit comme ça, ou tu veux changer quelque chose ?</p><div><button type="button" class="ip-btn is-main" id="ipCreate">✅ Créer le produit</button><button type="button" class="ip-btn" id="ipChange">✏️ Changer quelque chose</button></div><small class="ip-help">Le produit est créé en brouillon et s’ouvre dans l’éditeur : tu le publies après une dernière relecture.</small></div>' +
            '<p class="ip-msg" id="ipCreateMsg" role="alert"></p>' +
            '<details class="ip-json"><summary class="ip-help">Données du produit (JSON)</summary><pre id="ipJson"></pre></details>' +
            '</div>';
        $('ipJson').textContent = JSON.stringify(produitJson(pr), null, 2);
    }

    /* ───────── Saisie : le résultat se met à jour (sans perdre la case en cours) ───────── */
    $('ipOut').addEventListener('input', e => {
        const el = e.target; if (!R) return;
        if (el.dataset.e) { E[el.dataset.e] = el.value; if (el.dataset.e === 'prix') E.prixTouched = el.value.trim() !== ''; }
        else if (el.dataset.p) R.page[el.dataset.p] = el.value;
        else if (el.dataset.av !== undefined) R.page.avantages_produit[+el.dataset.av] = el.value;
        else return;
        if (el.dataset.e === 'ville') $('ipVille').value = el.value;
        const id = el.id, pos = el.selectionStart;
        render();
        const back = $(id); if (back) { back.focus(); try { back.setSelectionRange(pos, pos); } catch (x) {} }
    });
    $('ipOut').addEventListener('change', e => {
        if (e.target.id === 'ipDroits') { E.droits = e.target.checked; render(); }
        if (e.target.dataset.p === 'categorie') { R.page.categorie = e.target.value; render(); }
    });
    $('ipOut').addEventListener('click', e => {
        const pic = e.target.closest('[data-pic]');
        if (pic) {
            const u = R.photos[+pic.dataset.pic], i = E.photos.indexOf(u);
            if (i > -1) E.photos.splice(i, 1); else if (E.photos.length < 4) E.photos.push(u); else { alert('4 photos au maximum : retire d’abord une photo.'); return; }
            render(); return;
        }
        if (e.target.closest('#ipChange')) { $('ipTitre').scrollIntoView({ block: 'center', behavior: 'smooth' }); $('ipTitre').focus({ preventScroll: true }); return; }
        if (e.target.closest('#ipCreate')) create();
    });

    /* ───────── Analyse ───────── */
    $('ipShots').addEventListener('change', e => {
        const files = [...e.target.files].slice(0, 4);
        $('ipShotsPrev').innerHTML = files.map(f => '<img src="' + URL.createObjectURL(f) + '" alt="">').join('');
    });
    $('ipVille').addEventListener('input', e => { E.ville = e.target.value; if (R) render(); });
    const formMsg = (t, c) => { $('ipFormMsg').textContent = t; $('ipFormMsg').className = 'ip-msg' + (c ? ' ' + c : ''); };
    async function analyze() {
        if (busy) return;
        const url = $('ipUrl').value.trim();
        if (!/^https?:\/\/\S+\.\S+/i.test(url)) { formMsg('Colle l’adresse complète de la page du produit (elle commence par https://).', 'is-err'); $('ipUrl').focus(); return; }
        if (!G.pro) { formMsg('L’Importateur Produit Shopio est réservé au plan Pro.', 'is-err'); return; }
        const fd = new FormData();
        fd.append('url', url); fd.append('ville', $('ipVille').value.trim()); fd.append('delai', $('ipDelai').value.trim()); fd.append('echange', $('ipEchange').value.trim());
        if ($('ipText').value.trim()) fd.append('text', $('ipText').value.trim());
        [...$('ipShots').files].slice(0, 4).forEach(f => fd.append('shots[]', f));
        if (hint) fd.append('hint', JSON.stringify(hint));
        busy = true; $('ipGo').disabled = true; $('ipGo').textContent = 'Analyse en cours…'; formMsg('');
        $('ipOut').innerHTML = '<div class="ip-card ip-wait"><div class="ip-orb" aria-hidden="true">✨</div><b>Shopio IA lit la page du produit…</b><p class="ip-help">Lecture, vérification, rédaction en français simple : 20 à 60 secondes.</p></div>';
        try {
            const r = await fetch(G.urls.analyze, { method: 'POST', body: fd, headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
            const j = await r.json().catch(() => ({}));
            if (!r.ok) throw new Error(j.error || (j.errors && Object.values(j.errors)[0][0]) || (r.status === 429 ? 'Trop d’essais : patiente une minute.' : 'L’analyse n’a pas pu se faire. Réessaie.'));
            R = j; E.photos = j.photos.slice(0, 4); E.prix = ''; E.prixTouched = false; E.ville = $('ipVille').value.trim();
            render();
            $('ipOut').scrollIntoView({ block: 'start', behavior: 'smooth' });
        } catch (x) {
            $('ipOut').innerHTML = '<div class="ip-card"><p class="ip-msg is-err">' + esc(x.message) + '</p></div>';
        } finally { busy = false; $('ipGo').disabled = !G.aiReady; $('ipGo').textContent = '✨ Analyser avec Shopio IA'; }
    }
    $('ipForm').addEventListener('submit', e => { e.preventDefault(); analyze(); });

    /* ───────── Création ───────── */
    async function create() {
        const msg = (t, c) => { $('ipCreateMsg').textContent = t; $('ipCreateMsg').className = 'ip-msg' + (c ? ' ' + c : ''); };
        if (!E.droits) { msg('Confirme d’abord que tu as le droit d’utiliser ces photos et ces textes.', 'is-err'); $('ipDroits').focus(); return; }
        if (E.stock === '' || !/^\d+$/.test(String(E.stock).trim())) { msg('Indique ton vrai stock (un nombre).', 'is-err'); $('ipStock').focus(); return; }
        if (!/^\d+$/.test(String(E.prix).replace(/\s/g, '')) || !Number(String(E.prix).replace(/\s/g, ''))) { msg('Indique ton prix de vente en GNF (un nombre entier).', 'is-err'); $('ipPrix').focus(); return; }
        const b = $('ipCreate'); b.disabled = true; b.textContent = 'Création… (photos en cours de récupération)';
        const clean = v => v === '' ? null : String(v).replace(/\s/g, '').replace(',', '.');
        const body = {
            token: R.token, droits: true, titre: R.page.titre, accroche: R.page.accroche, avantages_produit: R.page.avantages_produit.slice(0, 2),
            description: R.page.description, categorie: R.page.categorie, ville: E.ville,
            taux: clean(E.taux), transport: clean(E.transport), douane: clean(E.douane), marge: clean(E.marge),
            prix: Number(clean(E.prix)), ancien_prix: E.ancien === '' ? null : Number(clean(E.ancien)), stock: Number(E.stock), photos: E.photos,
        };
        try {
            const r = await fetch(G.urls.create, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(body) });
            const j = await r.json().catch(() => ({}));
            if (!r.ok) throw new Error(j.error || (j.errors && Object.values(j.errors)[0][0]) || 'La création n’a pas pu se faire. Réessaie.');
            if (j.warning) alert(j.warning);
            location.href = j.redirect;
        } catch (x) { msg(x.message, 'is-err'); b.disabled = false; b.textContent = '✅ Créer le produit'; }
    }

    /* ───────── Ouvert par le bouton « Importer dans Shopio » : la page lue dans le navigateur ───────── */
    const m = location.hash.match(/^#import=(.+)$/);
    if (m) {
        history.replaceState(null, '', location.pathname + location.search);
        try {
            const d = JSON.parse(decodeURIComponent(m[1]));
            $('ipUrl').value = d.url || '';
            if (d.text) $('ipText').value = String(d.text).slice(0, 60000);
            hint = { name: d.name || '', description: d.description || '', images: (d.images || []).slice(0, 12), price: d.price || '', currency: d.currency || '' };
            if (G.aiReady) analyze(); else formMsg('Page reçue. L’analyse demande le plan Pro.', 'is-err');
        } catch (x) { formMsg('Import illisible : touche de nouveau le bouton sur la page du produit.', 'is-err'); }
    }
})();
</script>
@endpush
