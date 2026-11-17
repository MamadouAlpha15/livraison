{{--
    Produits → « Créer avec Shopio IA » : un produit en 3 étapes.
      1. Photos : glisser-déposer, coller (Ctrl+V) ou parcourir ; 4 au plus ; la première est la principale.
         Chaque photo est envoyée dès qu'elle est choisie (products.upload.image → WebP optimisé).
      2. Prix et détails : prix, prix barré, stock, catégorie, nom (facultatif avec Shopio IA), consignes de style.
      3. Création : produit créé en brouillon, textes et page de vente rédigés par Shopio IA, puis ouverture
         de la « Page de vente » avec aperçu en direct pour relire et publier.
    Variables : $shop, $devise, $categories, $aiReady
--}}
@extends('layouts.app')
@section('title', 'Nouveau produit')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root { --bg: #f8fafc; --surface: #fff; --border: #e2e8f0; --text: #0f172a; --text-2: #475569; --muted: #64748b; --font: 'Plus Jakarta Sans', sans-serif; }
[data-theme="dark"] { --bg: #0b0f1a; --surface: #141925; --border: #262d3d; --text: #e6e9f0; --text-2: #b0b8c9; --muted: #8b95aa; }
.wz, .wz *, .wz *::before, .wz *::after { box-sizing: border-box; }
.wz { max-width: 720px; margin: 0 auto; padding: 22px 20px 80px; font-family: var(--font); color: var(--text); }
.wz-back { display: inline-flex; align-items: center; gap: 6px; color: var(--text-2); font-weight: 700; font-size: 14px; text-decoration: none; }
.wz-back:hover { color: var(--sv-btn, #1668D8); }
.wz-steps { display: flex; align-items: center; justify-content: center; gap: 0; margin: 18px 0 22px; }
.wz-steps i { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font: 800 14px var(--font); font-style: normal; background: var(--surface); color: var(--muted); box-shadow: inset 0 0 0 1.5px var(--border); transition: background-color 300ms, color 300ms; }
.wz-steps i.is-on { background: var(--sv-btn, #1668D8); color: #fff; box-shadow: none; }
.wz-steps i.is-done { background: var(--sv-btn, #1668D8); color: #fff; box-shadow: none; }
.wz-steps span { width: 70px; height: 2px; background: var(--border); transition: background-color 300ms; }
.wz-steps span.is-done { background: var(--sv-btn, #1668D8); }
.wz h1 { margin: 0; text-align: center; font: 800 24px var(--font); letter-spacing: -.02em; }
.wz .wz-sub { margin: 6px 0 22px; text-align: center; color: var(--muted); font-size: 14px; }
.wz-card { padding: 22px; border-radius: 22px; background: var(--surface); box-shadow: 0 0 0 1px var(--border); }
/* Étape 1 : photos */
.wz-drop { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; min-height: 220px; padding: 26px; border: 2px dashed var(--sv-b100, #D6E6FF); border-radius: 20px; background: var(--sv-b50, #EAF2FF); text-align: center; cursor: pointer; transition: border-color 200ms, background-color 200ms; }
.wz-drop.is-over { border-color: var(--sv-blue, #1F7CF6); background: #DCEBFF; }
.wz-drop b { font-size: 16px; }
.wz-drop small { color: var(--muted); font-size: 13px; }
.wz-thumbs { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-top: 16px; }
.wz-thumb { position: relative; aspect-ratio: 1; border-radius: 16px; overflow: hidden; background: var(--bg); box-shadow: 0 0 0 1px var(--border); }
.wz-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.wz-thumb.is-busy img { opacity: .45; }
.wz-thumb.is-busy::after { content: ''; position: absolute; left: 50%; top: 50%; width: 28px; height: 28px; margin: -14px 0 0 -14px; border-radius: 50%; border: 3px solid #fff; border-top-color: var(--sv-btn, #1668D8); animation: wzSpin 800ms linear infinite; }
.wz-thumb.is-err { box-shadow: 0 0 0 2px var(--sv-err, #DC2626); }
.wz-main { position: absolute; left: 8px; bottom: 8px; padding: 3px 9px; border-radius: 999px; background: var(--sv-btn, #1668D8); color: #fff; font-size: 11px; font-weight: 800; }
.wz-thumb button { position: absolute; border: 0; cursor: pointer; font: 700 12px var(--font); }
.wz-rm { top: 6px; right: 6px; width: 28px; height: 28px; border-radius: 50%; background: rgba(15,23,42,.75); color: #fff; font-size: 16px !important; line-height: 1; }
.wz-mk { left: 6px; top: 6px; padding: 4px 8px; border-radius: 999px; background: rgba(255,255,255,.92); color: var(--text); }
.wz-add { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; aspect-ratio: 1; border-radius: 16px; border: 2px dashed var(--border); background: transparent; color: var(--muted); font: 700 13px var(--font); cursor: pointer; }
.wz-imp { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 16px; background: linear-gradient(135deg, #EAF2FF, #F3E8FF); color: var(--text); text-decoration: none; }
.wz-imp > span:first-child { font-size: 26px; } .wz-imp span:nth-child(2) { display: grid; gap: 2px; flex: 1; } .wz-imp b { font-size: 15px; } .wz-imp small { color: var(--muted); font-size: 12.5px; line-height: 1.4; }
.wz-imp i { font-style: normal; font-weight: 800; color: var(--sv-btn, #1668D8); } .wz-imp:hover { filter: brightness(.98); }
.wz-pro input { flex: 1; min-width: 0; min-height: 46px; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 14px; background: var(--bg); color: var(--text); font: 500 15px var(--font); }
.wz-pro input:focus { outline: 0; border-color: var(--sv-blue, #1F7CF6); box-shadow: 0 0 0 3px var(--sv-b50, #EAF2FF); }
.wz-or { display: flex; align-items: center; gap: 12px; margin: 16px 0; color: var(--muted); font-size: 12.5px; font-weight: 700; }
.wz-or::before, .wz-or::after { content: ''; flex: 1; height: 1px; background: var(--border); }
.wz-pro { display: grid; gap: 8px; margin-top: 16px; padding: 14px; border-radius: 16px; background: linear-gradient(135deg, #EAF2FF, #F3E8FF); }
.wz-pro small { color: var(--muted); font-size: 13px; }
.wz-pro-row { display: flex; gap: 8px; flex-wrap: wrap; }
.wz-pro-row .wz-btn { min-height: 42px; }
.wz-help.is-err { color: var(--sv-err, #DC2626); font-weight: 700; } .wz-help.is-ok { color: var(--sv-ok, #059669); font-weight: 700; }
[data-theme="dark"] .wz-pro { background: linear-gradient(135deg, #13284B, #2A1B4A); }
.wz-count { margin: 12px 0 0; text-align: center; color: var(--muted); font-size: 13px; }
@keyframes wzSpin { to { transform: rotate(360deg); } }
/* Étape 2 : prix et détails */
.wz-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.wz-field { display: flex; flex-direction: column; gap: 6px; }
.wz-field.is-wide { grid-column: 1 / -1; }
.wz-field label { font-size: 13px; font-weight: 800; }
.wz-field label small { font-weight: 600; color: var(--muted); }
.wz-field input, .wz-field textarea { width: 100%; min-height: 48px; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 14px; background: var(--bg); color: var(--text); font: 500 15px var(--font); }
.wz-field textarea { min-height: 90px; resize: vertical; line-height: 1.5; }
.wz-field input:focus, .wz-field textarea:focus { outline: 0; border-color: var(--sv-blue, #1F7CF6); box-shadow: 0 0 0 3px var(--sv-b50, #EAF2FF); }
.wz-field [aria-invalid="true"] { border-color: var(--sv-err, #DC2626); }
.wz-help { margin: 0; color: var(--muted); font-size: 12.5px; line-height: 1.45; }
.wz-err { margin: 0; color: var(--sv-err, #DC2626); font-size: 13px; font-weight: 700; }
.wz-err:empty, .wz-alert:empty { display: none; }
.wz-alert { margin: 0 0 14px; padding: 12px 14px; border-radius: 14px; background: var(--sv-err50, #FDECEC); color: var(--sv-err, #B91C1C); font-weight: 700; font-size: 14px; }
.wz-note { margin: 0 0 16px; padding: 12px 14px; border-radius: 14px; background: var(--sv-amb50, #FEF3C7); color: var(--sv-amb-ink, #78350F); font-size: 13.5px; line-height: 1.5; }
.wz-actions { display: flex; justify-content: space-between; gap: 10px; margin-top: 20px; }
.wz-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 48px; padding: 0 22px; border: 0; border-radius: 999px; background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1.5px var(--border); font: 800 14.5px var(--font); cursor: pointer; text-decoration: none; }
.wz-btn.is-main { background: var(--sv-btn, #1668D8); color: #fff; box-shadow: none; }
.wz-btn.is-ai { background: linear-gradient(135deg, #1668D8, #7C3AED); color: #fff; box-shadow: none; }
.wz-btn:disabled { opacity: .45; cursor: not-allowed; }
/* Étape 3 : création */
.wz-make { text-align: center; padding: 40px 10px; }
.wz-orb { width: 86px; height: 86px; margin: 0 auto 18px; border-radius: 26px; background: linear-gradient(135deg, #1668D8, #7C3AED); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 38px; box-shadow: 0 20px 40px -18px rgba(22,104,216,.8); animation: wzFloat 2.4s ease-in-out infinite; }
@keyframes wzFloat { 50% { transform: translateY(-8px) rotate(4deg); } }
.wz-phase { display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin: 24px 0 18px; }
.wz-phase li { display: flex; flex-direction: column; align-items: center; gap: 6px; width: 92px; font-size: 12px; font-weight: 700; color: var(--muted); }
.wz-phase li i { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-style: normal; font-weight: 800; background: var(--surface); box-shadow: inset 0 0 0 1.5px var(--border); }
.wz-phase li.is-on { color: var(--sv-btn, #1668D8); } .wz-phase li.is-on i { box-shadow: inset 0 0 0 2px var(--sv-btn, #1668D8); }
.wz-phase li.is-done { color: var(--text); } .wz-phase li.is-done i { background: var(--sv-btn, #1668D8); color: #fff; box-shadow: none; }
.wz-bar { height: 8px; max-width: 420px; margin: 0 auto; border-radius: 999px; background: var(--border); overflow: hidden; }
.wz-bar b { display: block; height: 100%; width: 0; border-radius: 999px; background: linear-gradient(90deg, #1668D8, #7C3AED); transition: width 600ms cubic-bezier(.22,1,.36,1); }
.wz-pct { margin: 8px 0 0; color: var(--muted); font-size: 13px; font-variant-numeric: tabular-nums; }
@media (max-width: 600px) {
    .wz-grid { grid-template-columns: minmax(0, 1fr); }
    .wz-thumbs { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .wz-steps span { width: 40px; }
}
@media (prefers-reduced-motion: reduce) { .wz-orb, .wz-thumb.is-busy::after { animation: none; } }
</style>
@include('boutique._partials.sv-theme')
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'products'])
    <main class="main vs-main">
        <div class="wz">
            <a class="wz-back" href="{{ route('products.index') }}">← Retour aux produits</a>
            <div class="wz-steps" aria-hidden="true"><i id="wzS1" class="is-on">1</i><span id="wzL1"></span><i id="wzS2">2</i><span id="wzL2"></span><i id="wzS3">3</i></div>

            {{-- Étape 1 : photos --}}
            <section id="wzStep1" aria-labelledby="wzH1">
                <h1 id="wzH1">Ajoutez les photos du produit</h1>
                <p class="wz-sub">Glissez-déposez, collez (Ctrl+V) ou touchez pour choisir. La première photo est la photo principale.</p>
                <div class="wz-card">
                    <a class="wz-imp" href="{{ route('products.import') }}"><span aria-hidden="true">🔗</span><span><b>Importer depuis un lien</b><small>Alibaba, AliExpress, 1688, Amazon, Jumia, Shopify… : Shopio IA prépare la page et calcule ton prix en GNF.</small></span><i aria-hidden="true">→</i></a>
                    <div class="wz-or" aria-hidden="true"><span>ou ajoutez vos photos</span></div>
                    <div class="wz-drop" id="wzDrop" role="button" tabindex="0" aria-describedby="wzDropHelp">
                        <span aria-hidden="true" style="font-size:34px">📷</span>
                        <b>Glissez vos photos ici</b>
                        <small id="wzDropHelp">ou touchez pour choisir · JPG, PNG ou WebP · 4 photos au maximum</small>
                        <span class="wz-btn is-main" style="pointer-events:none;min-height:42px">Choisir des photos</span>
                    </div>
                    <input type="file" id="wzFile" accept="image/jpeg,image/png,image/webp,image/gif" multiple hidden>
                    <div class="wz-thumbs" id="wzThumbs" aria-live="polite"></div>
                    <p class="wz-count" id="wzCount">Aucune photo pour l’instant.</p>
                    <p class="wz-err" id="wzPhotoErr" role="alert" style="text-align:center;margin-top:8px"></p>
                    @if($photoAi)
                    <div class="wz-pro" id="wzPro" hidden>
                        <b>✨ Photos pro avec Shopio IA</b>
                        <small>À partir de la photo principale : votre vrai produit, avec un fond et une lumière de professionnel.</small>
                        <div class="wz-pro-row"><button type="button" class="wz-btn" data-pro="studio">Fond blanc studio</button><button type="button" class="wz-btn" data-pro="scene">Mise en scène</button></div>
                        <input id="wzWish" maxlength="300" placeholder="Décor ou précision (facultatif) : ex. salon moderne, tissu wax…" aria-label="Décor ou précision pour la photo">
                        <p class="wz-help" id="wzProMsg" aria-live="polite">Environ 30 secondes à 1 minute par photo. Vérifiez toujours que le produit est fidèle à l’original.</p>
                    </div>
                    @endif
                </div>
                <div class="wz-actions"><a class="wz-btn" href="{{ route('products.create') }}">Créer sans l’IA</a><button type="button" class="wz-btn is-main" id="wzNext1" disabled>Continuer →</button></div>
            </section>

            {{-- Étape 2 : prix et détails --}}
            <section id="wzStep2" aria-labelledby="wzH2" hidden>
                <h1 id="wzH2">Prix et détails</h1>
                <p class="wz-sub">{{ $aiReady ? 'Shopio IA s’occupe du nom, de la description et de la page de vente.' : 'Indiquez le prix et le nom de votre produit.' }}</p>
                <form class="wz-card" id="wzForm" novalidate>
                    <p class="wz-alert" id="wzAlert" role="alert"></p>
                    @unless($aiReady)<p class="wz-note">La rédaction automatique (nom, description, page de vente) est incluse dans le <b>plan Pro</b>. Sans elle, le produit est créé avec votre nom et vous complétez sa page ensuite.</p>@endunless
                    <div class="wz-grid">
                        <div class="wz-field"><label for="wzPrice">Prix de vente ({{ $devise }}) *</label><input id="wzPrice" name="price" inputmode="numeric" placeholder="Ex. 250000" autocomplete="off"><p class="wz-err" id="wzPriceErr"></p></div>
                        <div class="wz-field"><label for="wzOld">Prix barré <small>(facultatif)</small></label><input id="wzOld" name="original_price" inputmode="numeric" placeholder="Ex. 300000" autocomplete="off"><p class="wz-help">Affiché barré pour montrer la réduction.</p><p class="wz-err" id="wzOldErr"></p></div>
                        <div class="wz-field"><label for="wzStock">Quantité en stock *</label><input id="wzStock" name="stock" inputmode="numeric" placeholder="Ex. 20" autocomplete="off"><p class="wz-err" id="wzStockErr"></p></div>
                        <div class="wz-field"><label for="wzCat">Catégorie <small>(facultatif)</small></label><input id="wzCat" name="category" list="wzCats" maxlength="100" placeholder="Choisir ou écrire une catégorie"><datalist id="wzCats">@foreach($categories as $c)<option value="{{ $c }}">@endforeach</datalist><p class="wz-help">Écrivez un nouveau nom pour créer une catégorie.</p></div>
                        <div class="wz-field is-wide"><label for="wzName">Nom du produit {!! $aiReady ? '<small>(facultatif : Shopio IA le trouve d’après les photos)</small>' : '*' !!}</label><input id="wzName" name="name" maxlength="120" placeholder="Ex. Sac à dos urbain 25 L"><p class="wz-err" id="wzNameErr"></p></div>
                        @if($aiReady)
                        <div class="wz-field is-wide"><label for="wzInstr">Consignes pour Shopio IA <small>(facultatif)</small></label><textarea id="wzInstr" name="instructions" maxlength="500" placeholder="Ex. textes courts, mettez en avant la solidité, ton chaleureux"></textarea><p class="wz-help"><span id="wzInstrN">0</span>/500 · le ton et le style des textes. Shopio IA n’invente jamais de chiffres, d’avis ni de délais.</p></div>
                        @endif
                    </div>
                    <div class="wz-actions"><button type="button" class="wz-btn" id="wzBack2">← Retour</button><button type="submit" class="wz-btn {{ $aiReady ? 'is-ai' : 'is-main' }}" id="wzGo">{{ $aiReady ? '✨ Créer avec Shopio IA' : 'Créer le produit' }}</button></div>
                </form>
            </section>

            {{-- Étape 3 : création --}}
            <section id="wzStep3" aria-labelledby="wzH3" hidden>
                <div class="wz-card wz-make">
                    <div class="wz-orb" aria-hidden="true">✨</div>
                    <h1 id="wzH3">Création en cours</h1>
                    <p class="wz-sub" id="wzPhaseText" role="status" aria-live="polite" style="margin-bottom:0">Envoi des photos…</p>
                    <ol class="wz-phase" id="wzPhases" style="list-style:none;padding:0">
                        <li class="is-done"><i>✓</i>Photos</li>
                        @if($aiReady)<li><i>2</i>Détection</li><li><i>3</i>Textes</li><li><i>4</i>Page de vente</li>@endif
                        <li><i>{{ $aiReady ? 5 : 2 }}</i>Finalisation</li>
                    </ol>
                    <div class="wz-bar" role="progressbar" aria-label="Progression" aria-valuemin="0" aria-valuemax="100" id="wzBar"><b id="wzBarIn"></b></div>
                    <p class="wz-pct" id="wzPct">0 %</p>
                </div>
            </section>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const $ = id => document.getElementById(id);
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const AI = @json($aiReady), MAX = 4;
    const UPLOAD = @json(route('products.upload.image')), STORE = @json(route('products.wizard.store'));
    const PHOTO = @json(route('products.wizard.photo'));
    let photos = []; // { key, url, path, busy, err }

    /* ── Navigation entre les étapes ── */
    function go(n) {
        [1, 2, 3].forEach(k => { $('wzStep' + k).hidden = k !== n; $('wzS' + k).className = k < n ? 'is-done' : k === n ? 'is-on' : ''; $('wzS' + k).textContent = k < n ? '✓' : k; });
        $('wzL1').className = n > 1 ? 'is-done' : ''; $('wzL2').className = n > 2 ? 'is-done' : '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        const f = $('wzStep' + n).querySelector('input:not([type=file]), button.is-main');
        if (n === 2 && f) setTimeout(() => $('wzPrice').focus(), 200);
    }

    /* ── Étape 1 : photos envoyées une à une, dès qu'elles sont choisies ── */
    function render() {
        const ready = photos.filter(p => p.path && !p.err).length, busy = photos.some(p => p.busy);
        $('wzThumbs').innerHTML = photos.map((p, i) => '<div class="wz-thumb' + (p.busy ? ' is-busy' : '') + (p.err ? ' is-err' : '') + '">' +
            '<img src="' + p.url + '" alt="Photo ' + (i + 1) + '">' + (i === 0 ? '<span class="wz-main">Photo principale</span>' : (p.path ? '<button type="button" class="wz-mk" data-main="' + i + '" aria-label="Mettre la photo ' + (i + 1) + ' en principale">★ Principale</button>' : '')) +
            '<button type="button" class="wz-rm" data-rm="' + i + '" aria-label="Retirer la photo ' + (i + 1) + '">×</button></div>').join('') +
            (photos.length && photos.length < MAX ? '<button type="button" class="wz-add" data-add>＋<span>Ajouter</span></button>' : '');
        $('wzCount').textContent = photos.length ? ready + ' photo' + (ready > 1 ? 's' : '') + ' prête' + (ready > 1 ? 's' : '') + ' sur ' + MAX + ' au maximum' + (busy ? ' · envoi en cours…' : '') : 'Aucune photo pour l’instant.';
        $('wzNext1').disabled = !ready || busy;
        $('wzDrop').hidden = photos.length > 0;
        if ($('wzPro')) $('wzPro').hidden = !ready;
    }

    /* ── Importer depuis un lien : nom, description et photos d'une autre boutique ── */
    async function postJson(url, body) {
        let r, j = {};
        try { r = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(body) }); j = await r.json().catch(() => ({})); }
        catch (x) { throw new Error('Connexion impossible : vérifiez votre internet puis réessayez.'); }
        if (!r.ok) throw new Error(j.error || (j.errors && Object.values(j.errors)[0][0]) || (r.status === 429 ? 'Trop d’essais : patientez une minute.' : 'Une erreur est survenue. Réessayez.'));
        return j;
    }
    const say = (id, t, c) => { $(id).textContent = t; $(id).className = 'wz-help' + (c ? ' ' + c : ''); };
    /* ── Photos pro : Shopio IA part de la photo principale (vraie photo) ── */
    if ($('wzPro')) $('wzPro').addEventListener('click', async e => {
        const b = e.target.closest('[data-pro]'); if (!b) return;
        const main = photos.find(p => p.path && !p.err);
        if (!main) return;
        if (photos.length >= MAX) { say('wzProMsg', '4 photos au maximum : retirez-en une pour en créer une nouvelle.', 'is-err'); return; }
        const p = { url: main.url, path: null, busy: true, err: false };
        photos.push(p); render();
        $('wzPro').querySelectorAll('[data-pro]').forEach(x => x.disabled = true);
        say('wzProMsg', 'Shopio IA regarde votre photo et crée la nouvelle… (30 secondes à 1 minute)');
        try {
            const j = await postJson(PHOTO, { source: main.path, style: b.dataset.pro, wish: $('wzWish').value.trim() });
            p.url = j.url; p.path = j.path;
            say('wzProMsg', 'Photo créée : vérifiez que le produit est fidèle. Touchez « ★ Principale » pour en faire la photo principale, ou × pour la retirer.', 'is-ok');
        } catch (err) { photos.splice(photos.indexOf(p), 1); say('wzProMsg', err.message, 'is-err'); }
        p.busy = false; render();
        $('wzPro').querySelectorAll('[data-pro]').forEach(x => x.disabled = false);
    });
    async function upload(file) {
        if (photos.length >= MAX) { $('wzPhotoErr').textContent = '4 photos au maximum.'; return; }
        if (!/^image\/(jpeg|png|webp|gif)$/.test(file.type)) { $('wzPhotoErr').textContent = '« ' + file.name + ' » n’est pas une photo (JPG, PNG ou WebP).'; return; }
        if (file.size > 20 * 1024 * 1024) { $('wzPhotoErr').textContent = '« ' + file.name + ' » est trop lourde (20 Mo au maximum).'; return; }
        $('wzPhotoErr').textContent = '';
        const p = { url: URL.createObjectURL(file), path: null, busy: true, err: false };
        photos.push(p); render();
        const fd = new FormData(); fd.append('file', file); fd.append('folder', 'products');
        try {
            const r = await fetch(UPLOAD, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
            const j = await r.json().catch(() => ({}));
            if (!r.ok || !j.path) throw new Error((j.errors && j.errors.file && j.errors.file[0]) || 'Envoi impossible');
            p.path = j.path;
        } catch (e) { p.err = true; $('wzPhotoErr').textContent = 'Une photo n’a pas pu être envoyée : retirez-la puis réessayez.'; }
        p.busy = false; render();
    }
    const pick = () => $('wzFile').click();
    const take = files => [...files].slice(0, MAX - photos.length).forEach(upload);
    $('wzDrop').addEventListener('click', pick);
    $('wzDrop').addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); pick(); } });
    $('wzFile').addEventListener('change', e => { take(e.target.files); e.target.value = ''; });
    ['dragenter', 'dragover'].forEach(t => document.addEventListener(t, e => { if ($('wzStep1').hidden) return; e.preventDefault(); $('wzDrop').classList.add('is-over'); }));
    ['dragleave', 'drop'].forEach(t => document.addEventListener(t, e => { $('wzDrop').classList.remove('is-over'); }));
    document.addEventListener('drop', e => { if ($('wzStep1').hidden) return; e.preventDefault(); if (e.dataTransfer && e.dataTransfer.files.length) take(e.dataTransfer.files); });
    document.addEventListener('paste', e => { if ($('wzStep1').hidden) return; const f = [...(e.clipboardData ? e.clipboardData.files : [])]; if (f.length) { e.preventDefault(); take(f); } });
    $('wzThumbs').addEventListener('click', e => {
        const rm = e.target.closest('[data-rm]'), mk = e.target.closest('[data-main]'), add = e.target.closest('[data-add]');
        if (rm) { photos.splice(+rm.dataset.rm, 1); $('wzPhotoErr').textContent = ''; render(); }
        if (mk) { const i = +mk.dataset.main; photos.unshift(photos.splice(i, 1)[0]); render(); }
        if (add) pick();
    });
    $('wzNext1').addEventListener('click', () => go(2));
    $('wzBack2').addEventListener('click', () => go(1));
    if ($('wzInstr')) $('wzInstr').addEventListener('input', e => { $('wzInstrN').textContent = e.target.value.length; });

    /* ── Étape 2 → 3 : contrôles, puis création ── */
    const F = { price: 'wzPrice', original_price: 'wzOld', stock: 'wzStock', name: 'wzName' };
    const num = v => String(v || '').replace(/[\s .]/g, '');
    function setErr(k, m) { const el = $(F[k]); if (!el) return; $(F[k] + 'Err').textContent = m || ''; if (m) el.setAttribute('aria-invalid', 'true'); else el.removeAttribute('aria-invalid'); }
    Object.keys(F).forEach(k => $(F[k]) && $(F[k]).addEventListener('input', () => setErr(k, '')));
    let timer = null;
    function phase(i, pct, text) {
        const lis = [...$('wzPhases').children];
        lis.forEach((li, k) => { li.className = k < i ? 'is-done' : k === i ? 'is-on' : ''; li.querySelector('i').textContent = k < i ? '✓' : k + 1; });
        $('wzBarIn').style.width = pct + '%'; $('wzPct').textContent = pct + ' %'; $('wzBar').setAttribute('aria-valuenow', pct);
        $('wzPhaseText').textContent = text;
    }
    $('wzForm').addEventListener('submit', async e => {
        e.preventDefault();
        $('wzAlert').textContent = '';
        let ok = true;
        const price = num($('wzPrice').value), old = num($('wzOld').value), stock = num($('wzStock').value);
        if (!/^\d+$/.test(price) || +price < 1) { setErr('price', 'Indiquez le prix de vente, en chiffres.'); ok = false; }
        if (old && (!/^\d+$/.test(old) || +old <= +price)) { setErr('original_price', 'Le prix barré doit être plus élevé que le prix de vente.'); ok = false; }
        if (!/^\d+$/.test(stock)) { setErr('stock', 'Indiquez combien de pièces vous avez (0 si vous n’en avez plus).'); ok = false; }
        if (!AI && !$('wzName').value.trim()) { setErr('name', 'Indiquez le nom du produit.'); ok = false; }
        if (!ok) { const bad = $('wzForm').querySelector('[aria-invalid="true"]'); if (bad) bad.focus(); return; }

        go(3);
        const steps = AI ? [[1, 18, 'Détection du produit…'], [2, 42, 'Rédaction des textes…'], [3, 70, 'Création de la page de vente…'], [3, 86, 'Dernières vérifications…']] : [[1, 60, 'Finalisation…']];
        let s = 0; phase(1, 8, AI ? 'Détection du produit…' : 'Finalisation…');
        timer = setInterval(() => { if (s < steps.length) { phase(...steps[s]); s++; } }, AI ? 6000 : 500);
        try {
            const body = { images: photos.filter(p => p.path && !p.err).map(p => p.path), price: +price, original_price: old ? +old : null, stock: +stock, category: $('wzCat').value.trim(), name: $('wzName').value.trim(), instructions: $('wzInstr') ? $('wzInstr').value.trim() : '' };
            const r = await fetch(STORE, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(body) });
            const j = await r.json().catch(() => ({}));
            clearInterval(timer);
            if (r.status === 201) {
                phase($('wzPhases').children.length, 100, '« ' + j.name + ' » est prêt ! Ouverture de la page de vente…');
                if (j.warning) try { sessionStorage.setItem('wzWarning', j.warning); } catch (x) {}
                setTimeout(() => { window.location.href = j.redirect; }, 900);
                return;
            }
            if (r.status === 402 && j.upgrade) { window.location.href = j.upgrade; return; }
            const photoErr = j.errors ? Object.keys(j.errors).find(k => k.indexOf('images') === 0) : null;
            if (photoErr) { go(1); $('wzPhotoErr').textContent = j.errors[photoErr][0]; return; }
            go(2);
            if (j.errors) Object.entries(j.errors).forEach(([k, v]) => { if (F[k]) setErr(k, v[0]); else $('wzAlert').textContent = v[0]; });
            else $('wzAlert').textContent = j.error || j.message || 'Le produit n’a pas pu être créé. Réessayez.';
        } catch (x) {
            clearInterval(timer); go(2);
            $('wzAlert').textContent = 'Connexion impossible : vérifiez votre internet puis réessayez.';
        }
    });
    render();
})();
</script>
@endpush
