{{--
    resources/views/vendeur/products/import.blade.php
    Route     : GET /products/importer → Vendeur\ProductImportController@show → name('products.import')
    Rôle      : « Importer les photos d'un produit ». Le vendeur colle le lien d'un produit (Alibaba, AliExpress, Shopify,
                YouCan…) ; Shopio récupère les photos de la page (ou celles lues par le bouton « Importer dans Shopio »
                des favoris, pour les sites qui bloquent). Il coche celles à garder, confirme ses droits, puis la fiche
                produit habituelle s'ouvre avec ces photos déjà ajoutées. Ni texte, ni prix, ni IA.
    Variables : $shop, $keep (photos gardées au plus)
--}}
@extends('layouts.app')
@section('title', 'Importer des photos · ' . $shop->name)
@php
    $bodyClass = 'is-dashboard';
    $ipData = ['keep' => $keep, 'urls' => ['photos' => route('products.import.photos'), 'use' => route('products.import.use')]];
    $ii = fn (string $d, int $s = 18) => '<svg width="' . $s . '" height="' . $s . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $d . '"/></svg>';
    $IP = [
        'back'  => 'M15 6l-6 6 6 6',
        'link'  => 'M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1',
        'image' => 'M4 5h16v14H4zM4 15l4.5-4.5 4 4 2.5-2.5L20 17M15.5 9.5h.01',
        'down'  => 'M12 4v11M7 10l5 5 5-5M5 20h14',
    ];
@endphp

@push('styles')
<style>
.ip, .ip *, .ip *::before, .ip *::after { box-sizing: border-box; }
.ip { max-width: 1100px; margin: 0 auto; padding: 8px 4px 60px; }
.ip-back { display: inline-flex; align-items: center; gap: 4px; color: var(--muted); font-size: 14px; font-weight: 700; text-decoration: none; margin-bottom: 10px; }
.ip-back:hover { color: var(--sv-btn, #1668D8); }
.ip h1 { margin: 0; font: 800 24px var(--font); letter-spacing: -.02em; color: var(--text); }
.ip-sub { margin: 6px 0 20px; color: var(--muted); font-size: 14px; max-width: 70ch; line-height: 1.5; }
.ip-grid { display: grid; grid-template-columns: minmax(0, 380px) minmax(0, 1fr); gap: 20px; align-items: start; }
.ip-card { display: grid; gap: 14px; padding: 20px; border-radius: 18px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); }
.ip-card h2 { margin: 0; font: 800 16px var(--font); color: var(--text); }
.ip-f { display: grid; gap: 6px; }
.ip-f label { font-size: 13.5px; font-weight: 700; color: var(--text); }
.ip-in { position: relative; }
.ip-in svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--muted); }
.ip-in input { width: 100%; min-height: 48px; padding: 10px 14px 10px 40px; border: 1px solid var(--border); border-radius: 12px; background: var(--surface); color: var(--text); font: 500 15px var(--font); }
.ip-help { margin: 0; color: var(--muted); font-size: 13px; line-height: 1.5; }
.ip-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 48px; padding: 0 22px; border: 0; border-radius: 999px; background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1px var(--border); font: 700 15px var(--font); cursor: pointer; text-decoration: none; }
.ip-btn.is-main { background: var(--sv-btn, #1668D8); color: #fff; box-shadow: none; }
.ip-btn.is-main:hover:not(:disabled) { background: var(--sv-btn-h, #1260C9); }
.ip-btn:disabled { opacity: .55; cursor: default; }
.ip-more summary { cursor: pointer; font-size: 13.5px; font-weight: 700; color: var(--text-2); }
.ip-more summary:hover { color: var(--sv-btn, #1668D8); }
.ip-more ol { margin: 10px 0 0; padding-left: 20px; display: grid; gap: 6px; font-size: 13.5px; color: var(--text-2); line-height: 1.5; }
.ip-bm { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 999px; background: var(--surface); color: var(--text) !important; box-shadow: inset 0 0 0 1px var(--border); font-weight: 700; text-decoration: none; cursor: grab; white-space: nowrap; }
.ip-msg { margin: 0; padding: 12px 14px; border-radius: 12px; font-size: 14px; font-weight: 600; line-height: 1.5; background: var(--bg); color: var(--text); box-shadow: inset 0 0 0 1px var(--border); }
.ip-msg.is-err { color: var(--sv-err, #B91C1C); box-shadow: inset 0 0 0 1px currentColor; }
.ip-msg:empty { display: none; }
/* État vide et chargement */
.ip-empty { text-align: center; padding: 44px 16px; color: var(--muted); }
.ip-empty-ico { width: 60px; height: 60px; margin: 0 auto 12px; border-radius: 16px; display: flex; align-items: center; justify-content: center; background: var(--sv-b50, #EAF2FF); color: var(--sv-btn, #1668D8); }
.ip-empty b { display: block; color: var(--text); font-size: 15px; margin-bottom: 4px; }
.ip-spin { width: 28px; height: 28px; margin: 0 auto 12px; border-radius: 50%; border: 3px solid var(--border); border-top-color: var(--sv-btn, #1668D8); animation: ipSpin .8s linear infinite; }
@keyframes ipSpin { to { transform: rotate(360deg); } }
/* Photos trouvées */
.ip-hd { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.ip-count { font-size: 13px; font-weight: 700; color: var(--muted); }
.ip-tools { display: flex; gap: 6px; }
.ip-link { border: 0; background: none; padding: 4px 6px; color: var(--sv-btn, #1668D8); font: 700 13px var(--font); cursor: pointer; }
.ip-pics { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; }
.ip-pic { position: relative; display: block; aspect-ratio: 1; border-radius: 14px; overflow: hidden; background: var(--bg); box-shadow: inset 0 0 0 1px var(--border); cursor: pointer; }
.ip-pic img { width: 100%; height: 100%; object-fit: cover; display: block; transition: opacity 160ms; }
.ip-pic input { position: absolute; opacity: 0; width: 1px; height: 1px; }
.ip-pic .ip-tick { position: absolute; top: 8px; right: 8px; width: 26px; height: 26px; border-radius: 8px; background: rgba(255,255,255,.92); box-shadow: inset 0 0 0 1.5px #C3CBDA; display: flex; align-items: center; justify-content: center; color: transparent; }
.ip-pic input:checked ~ .ip-tick { background: var(--sv-btn, #1668D8); box-shadow: none; color: #fff; }
.ip-pic input:not(:checked) ~ img { opacity: .45; }
.ip-pic input:checked ~ .ip-frame { position: absolute; inset: 0; border-radius: 14px; box-shadow: inset 0 0 0 2.5px var(--sv-btn, #1668D8); pointer-events: none; }
.ip-pic input:focus-visible ~ .ip-frame { outline: 3px solid #7DB4FF; outline-offset: -3px; }
.ip-main { position: absolute; left: 8px; bottom: 8px; padding: 3px 8px; border-radius: 999px; background: rgba(22,22,42,.8); color: #fff; font-size: 11.5px; font-weight: 700; }
.ip-check { display: flex; gap: 10px; align-items: flex-start; font-size: 13.5px; color: var(--text-2); line-height: 1.5; cursor: pointer; }
.ip-check input { width: 18px; height: 18px; margin-top: 2px; flex-shrink: 0; accent-color: var(--sv-btn, #1668D8); }
.ip-foot { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
@media (max-width: 960px) { .ip-grid { grid-template-columns: minmax(0, 1fr); } }
@media (max-width: 520px) { .ip-pics { grid-template-columns: repeat(2, minmax(0, 1fr)); } .ip-foot .ip-btn { width: 100%; } .ip h1 { font-size: 21px; } }
@media (prefers-reduced-motion: reduce) { .ip-spin { animation-duration: 2.4s; } }
</style>
@include('boutique._partials.sv-theme')
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'products'])
    <main class="main vs-main">
        <div class="ip">
            <a class="ip-back" href="{{ route('products.index') }}">{!! $ii($IP['back'], 16) !!} Retour aux produits</a>
            <h1>Importer les photos d’un produit</h1>
            <p class="ip-sub">Colle le lien d’un produit (Alibaba, AliExpress, Shopify, YouCan…). Shopio récupère ses photos ; tu choisis celles à garder, puis tu écris toi-même le nom, le prix et la description.</p>

            <div class="ip-grid">
                {{-- Le lien --}}
                <form class="ip-card" id="ipForm" novalidate data-ajax>
                    <h2>Le lien du produit</h2>
                    <div class="ip-f">
                        <label for="ipUrl">Lien de la page du produit</label>
                        <div class="ip-in">{!! $ii($IP['link'], 17) !!}<input id="ipUrl" name="url" type="url" inputmode="url" autocomplete="off" placeholder="https://… (la page d’un seul produit)" required></div>
                    </div>
                    <p class="ip-msg" id="ipFormMsg" role="alert"></p>
                    <button type="submit" class="ip-btn is-main" id="ipGo">{!! $ii($IP['down'], 18) !!} Trouver les photos</button>
                    <details class="ip-more" id="ipHelp">
                        <summary>Le site bloque Shopio ? (ordinateur)</summary>
                        <ol>
                            <li>Glisse ce bouton dans ta barre de favoris (une seule fois) : <a class="ip-bm" href="{{ \App\Support\ImportBookmarklet::href(route('products.import')) }}" onclick="event.preventDefault();alert('Glisse ce bouton dans ta barre de favoris (ne clique pas dessus ici).')">Importer dans Shopio</a></li>
                            <li>Ouvre la page du produit (AliExpress, Alibaba…).</li>
                            <li>Touche « Importer dans Shopio » dans tes favoris : cette page s’ouvre avec les photos du produit.</li>
                        </ol>
                        <p class="ip-help" style="margin-top:8px">Barre de favoris cachée ? Ctrl+Maj+B (Chrome, Edge).</p>
                    </details>
                </form>

                {{-- Les photos trouvées --}}
                <section id="ipOut" aria-live="polite">
                    <div class="ip-card ip-empty">
                        <div class="ip-empty-ico">{!! $ii($IP['image'], 28) !!}</div>
                        <b>Les photos du produit s’afficheront ici</b>
                        <span>Tu pourras choisir celles à garder ({{ $keep }} au plus).</span>
                    </div>
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
    const TICK = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
    let R = null, images = null;

    async function post(url, body) {
        let r, j = {};
        try { r = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(body) }); j = await r.json().catch(() => ({})); }
        catch (x) { throw new Error('Connexion impossible : vérifie ton internet puis réessaie.'); }
        if (!r.ok) { const e = new Error(j.error || (j.errors && Object.values(j.errors)[0][0]) || 'Une erreur est survenue. Réessaie.'); e.blocked = !!j.blocked; throw e; }
        return j;
    }
    const formMsg = (t, cls) => { $('ipFormMsg').textContent = t || ''; $('ipFormMsg').className = 'ip-msg ' + (cls || ''); };

    /* ───────── 1. Trouver les photos ───────── */
    async function find() {
        const url = $('ipUrl').value.trim();
        if (!url) { formMsg('Colle le lien du produit.', 'is-err'); $('ipUrl').focus(); return; }
        formMsg(''); $('ipGo').disabled = true;
        $('ipOut').innerHTML = '<div class="ip-card ip-empty"><div class="ip-spin" aria-hidden="true"></div><b>Shopio récupère les photos…</b><span>Quelques secondes.</span></div>';
        try {
            R = await post(G.urls.photos, images ? { url, images } : { url });
            draw();
        } catch (x) {
            $('ipOut').innerHTML = '<div class="ip-card"><p class="ip-msg is-err">' + esc(x.message) + '</p></div>';
            if (x.blocked) $('ipHelp').open = true;
        } finally { images = null; $('ipGo').disabled = false; }
    }
    $('ipForm').addEventListener('submit', e => { e.preventDefault(); find(); });

    /* ───────── 2. Choisir les photos ───────── */
    function draw() {
        $('ipOut').innerHTML = '<div class="ip-card">' +
            '<div class="ip-hd"><h2>Photos trouvées</h2><div class="ip-tools"><button type="button" class="ip-link" data-all="1">Tout cocher</button><button type="button" class="ip-link" data-all="0">Tout décocher</button></div></div>' +
            '<p class="ip-help" style="margin:-6px 0 0">La première photo cochée devient la photo principale.</p>' +
            '<div class="ip-pics" id="ipPics">' + R.photos.map((p, i) => '<label class="ip-pic"><input type="checkbox" value="' + esc(p.path) + '"' + (i < G.keep ? ' checked' : '') + ' aria-label="Photo ' + (i + 1) + '"><img src="' + esc(p.url) + '" alt="" loading="lazy"><span class="ip-tick">' + TICK + '</span><span class="ip-frame"></span></label>').join('') + '</div>' +
            '<span class="ip-count" id="ipCount"></span>' +
            '<label class="ip-check"><input type="checkbox" id="ipDroits"> Je confirme avoir le droit d’utiliser ces photos (mon produit, ou mon fournisseur me l’autorise).</label>' +
            '<p class="ip-msg" id="ipUseMsg" role="alert"></p>' +
            '<div class="ip-foot"><button type="button" class="ip-btn is-main" id="ipUse">Créer le produit avec ces photos</button></div>' +
            '</div>';
        count();
    }
    const checked = () => [...document.querySelectorAll('#ipPics input:checked')];
    function count() {
        const n = checked().length;
        document.querySelectorAll('#ipPics .ip-main').forEach(el => el.remove());
        if (n) checked()[0].closest('.ip-pic').insertAdjacentHTML('beforeend', '<span class="ip-main">Principale</span>');
        $('ipCount').textContent = n + ' photo' + (n > 1 ? 's' : '') + ' choisie' + (n > 1 ? 's' : '') + ' sur ' + R.photos.length + ' · ' + G.keep + ' au plus';
        $('ipUse').disabled = n === 0;
    }
    $('ipOut').addEventListener('change', e => {
        if (!e.target.closest('#ipPics')) return;
        if (e.target.checked && checked().length > G.keep) { e.target.checked = false; alert(G.keep + ' photos au maximum.'); }
        count();
    });
    $('ipOut').addEventListener('click', async e => {
        const all = e.target.closest('[data-all]');
        if (all) { document.querySelectorAll('#ipPics input').forEach((c, i) => { c.checked = all.dataset.all === '1' && i < G.keep; }); count(); return; }
        if (!e.target.closest('#ipUse')) return;
        const b = $('ipUse'), msg = $('ipUseMsg');
        if (!$('ipDroits').checked) { msg.textContent = 'Confirme que tu as le droit d’utiliser ces photos.'; msg.className = 'ip-msg is-err'; return; }
        b.disabled = true; b.textContent = 'Ouverture de la fiche produit…'; msg.textContent = '';
        try {
            const j = await post(G.urls.use, { token: R.token, droits: true, photos: checked().map(c => c.value) });
            location.href = j.redirect;
        } catch (x) { msg.textContent = x.message; msg.className = 'ip-msg is-err'; b.disabled = false; b.textContent = 'Créer le produit avec ces photos'; }
    });

    /* ───────── Ouvert par le bouton « Importer dans Shopio » : les photos lues dans le navigateur ───────── */
    const m = location.hash.match(/^#import=(.+)$/);
    if (m) {
        history.replaceState(null, '', location.pathname + location.search);
        try {
            const d = JSON.parse(decodeURIComponent(m[1]));
            $('ipUrl').value = d.url || '';
            images = (d.images || []).filter(u => typeof u === 'string').slice(0, 12);
            if (!images.length) images = null;
            find();
        } catch (x) { formMsg('Import illisible : touche de nouveau le bouton sur la page du produit.', 'is-err'); }
    }
})();
</script>
@endpush
