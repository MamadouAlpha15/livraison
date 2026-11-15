{{--
    resources/views/vendeur/shop/sections.blade.php
    Route     : GET /boutique/vitrine/sections → Vendeur\StorefrontController@sections → name('boutique.storefront.sections')
    Rôle      : « Boutique → Sections » — les sections de la page d'accueil, pour chaque thème : modifier les textes,
                masquer, déplacer (glisser ou flèches), ajouter (texte, image et texte, bannière, catégorie, questions),
                supprimer. Aperçu en direct de la vraie vitrine (?modele=…&apercu=1) rechargé à chaque enregistrement.
                « Enregistrer et publier » → StorefrontController@saveSections (JSON).
    Variables : $shop, $theme, $published, $home, $models, $defaults, $images, $categories, $publicUrl
--}}
@extends('layouts.app')
@section('title', 'Sections de la page d’accueil · ' . $shop->name)
@php
    use App\Support\HomeSections as HS;
    $bodyClass = 'is-dashboard';
    $sxData = [
        'theme' => $theme, 'published' => $published, 'home' => $home, 'models' => $models,
        'types' => HS::TYPES, 'fields' => HS::FIELDS, 'builtin' => HS::BUILTIN, 'addable' => HS::ADDABLE,
        'defaults' => $defaults, 'tones' => HS::TONES, 'images' => (object) $images, 'categories' => $categories,
        'limits' => ['custom' => HS::CUSTOM_MAX, 'faq' => HS::FAQ_MAX],
        'urls' => [
            'save' => route('boutique.storefront.sections.update'), 'upload' => route('boutique.grow.cover-upload'),
            'preview' => $publicUrl, 'self' => route('boutique.storefront.sections'),
        ],
    ];
@endphp

@push('styles')
<link href="{{ \App\Support\Assets::v('fonts/dashboard-fonts.css') }}" rel="stylesheet">
<style>
:root {
    --sx-blue: #1668D8; --sx-blue-h: #1260C9; --sx-b50: #EAF2FF; --sx-night: #0B1E3D;
    --sx-bg: #F6F9FF; --sx-surf: #FFFFFF; --sx-line: #E3E8F2; --sx-soft: #F1F5FB; --sx-stage: #DCE6F5;
    --sx-ink: #16162A; --sx-ink2: #45455E; --sx-ink3: #62627C; --sx-ok: #15803D; --sx-ok50: #E7F6EC; --sx-err: #B91C1C; --sx-amb: #F59E0B; --sx-ambi: #92400E;
    --sx-font: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif; --sx-out: cubic-bezier(.22,1,.36,1);
}
[data-theme="dark"] {
    --sx-b50: #13284B; --sx-bg: #0A1426; --sx-surf: #10203B; --sx-line: #22355A; --sx-soft: #0D1A31; --sx-stage: #071022;
    --sx-ink: #EAF0FA; --sx-ink2: #C3CEE0; --sx-ink3: #9DAAC2; --sx-ok: #4ADE80; --sx-ok50: #0F2A1C; --sx-err: #F87171; --sx-ambi: #FBBF24;
}
#pwaModal, #pushNotifBanner { display: none !important; }
html, body { background: var(--sx-bg); }
.sx { font-family: var(--sx-font); color: var(--sx-ink); font-size: 14.5px; }
.sx *, .sx *::before, .sx *::after { box-sizing: border-box; }
.sx :focus-visible { outline: 3px solid #7DB4FF; outline-offset: 2px; }
.sx-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
.sx-top { background: var(--sx-night); color: #fff; }
.sx-top-in { max-width: 1500px; margin: 0 auto; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.sx-top h1 { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -.02em; }
.sx-top p { margin: 4px 0 0; color: #C9D6EA; font-size: 13.5px; }
.sx-links { display: flex; gap: 8px; flex-wrap: wrap; }
.sx-btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 40px; padding: 0 14px; border-radius: 11px; border: 1px solid var(--sx-line); background: var(--sx-surf); color: var(--sx-ink); font: 700 13.5px var(--sx-font); text-decoration: none; cursor: pointer; transition: background-color 160ms, border-color 160ms, transform 160ms var(--sx-out); }
.sx-btn:hover { border-color: #B9C7DD; }
.sx-btn:active { transform: scale(.98); }
.sx-btn:disabled { opacity: .5; cursor: not-allowed; }
.sx-btn-main { background: var(--sx-blue); border-color: var(--sx-blue); color: #fff; }
.sx-btn-main:hover { background: var(--sx-blue-h); border-color: var(--sx-blue-h); }
.sx-btn-sm { min-height: 34px; padding: 0 11px; font-size: 12.5px; border-radius: 9px; }
.sx-btn-danger { color: var(--sx-err); }
.sx-top .sx-btn { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.18); color: #fff; }
.sx-top .sx-btn:hover { background: rgba(255,255,255,.16); }

.sx-layout { max-width: 1500px; margin: 0 auto; padding: 18px 16px 110px; display: grid; grid-template-columns: minmax(0, 440px) minmax(0, 1fr); gap: 18px; align-items: start; }
.sx-layout > *, .sx-panel > *, .sx-list > *, .sx-add > *, .sx-add-grid > *, .sx-body > * { min-width: 0; }
.sx-panel { display: grid; gap: 12px; }
.sx-themes { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; padding: 5px; border-radius: 14px; background: var(--sx-surf); border: 1px solid var(--sx-line); }
.sx-themes a { display: flex; align-items: center; justify-content: center; gap: 6px; min-height: 40px; border-radius: 10px; text-decoration: none; color: var(--sx-ink2); font-weight: 700; }
.sx-themes a[aria-current="page"] { background: var(--sx-night); color: #fff; }
.sx-themes small { font-size: 10.5px; padding: 2px 7px; border-radius: 999px; background: var(--sx-ok50); color: var(--sx-ok); font-weight: 800; }
.sx-hint { margin: 0; color: var(--sx-ink3); font-size: 13px; line-height: 1.5; }

.sx-list { display: grid; gap: 8px; margin: 0; padding: 0; list-style: none; }
.sx-item { background: var(--sx-surf); border: 1px solid var(--sx-line); border-radius: 14px; transition: box-shadow 200ms, border-color 200ms, opacity 200ms; }
.sx-item.is-open { border-color: var(--sx-blue); box-shadow: 0 0 0 3px var(--sx-b50); }
.sx-item.is-off .sx-item-name { opacity: .5; }
.sx-item.is-drag { opacity: .4; }
.sx-item.is-over { box-shadow: 0 -3px 0 var(--sx-blue); }
.sx-item-hd { display: flex; align-items: center; gap: 6px; padding: 8px 8px 8px 6px; }
.sx-grip { width: 26px; height: 36px; display: grid; place-items: center; color: var(--sx-ink3); cursor: grab; flex-shrink: 0; }
.sx-item-name { flex: 1; min-width: 0; border: 0; background: none; padding: 6px 4px; text-align: left; cursor: pointer; color: var(--sx-ink); font-family: var(--sx-font); }
.sx-item-name b { display: flex; align-items: center; gap: 6px; font-size: 14px; }
.sx-item-name span { display: block; font-size: 12px; color: var(--sx-ink3); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sx-tag { font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 999px; background: var(--sx-b50); color: var(--sx-blue); letter-spacing: .03em; }
.sx-icon { width: 34px; height: 34px; border: 0; border-radius: 9px; background: none; color: var(--sx-ink2); display: grid; place-items: center; cursor: pointer; flex-shrink: 0; }
.sx-icon:hover { background: var(--sx-soft); }
.sx-icon:disabled { opacity: .3; cursor: default; background: none; }
.sx-body { display: grid; gap: 12px; padding: 4px 14px 16px; border-top: 1px solid var(--sx-line); padding-top: 14px; }
.sx-lbl { display: block; font-weight: 700; font-size: 12.5px; margin-bottom: 5px; color: var(--sx-ink2); }
.sx-input, .sx-select, .sx-area { width: 100%; min-height: 42px; padding: 9px 11px; border: 1px solid var(--sx-line); border-radius: 10px; background: var(--sx-surf); color: var(--sx-ink); font: 500 14px var(--sx-font); }
.sx-area { min-height: 90px; resize: vertical; line-height: 1.5; }
.sx-input:focus, .sx-select:focus, .sx-area:focus { outline: none; border-color: var(--sx-blue); box-shadow: 0 0 0 3px var(--sx-b50); }
.sx-count { display: block; text-align: right; font-size: 11.5px; color: var(--sx-ink3); margin-top: 3px; }
.sx-seg { display: inline-flex; gap: 4px; padding: 4px; border-radius: 10px; background: var(--sx-soft); }
.sx-seg button { min-height: 32px; padding: 0 12px; border: 0; border-radius: 7px; background: none; color: var(--sx-ink2); font: 700 12.5px var(--sx-font); cursor: pointer; }
.sx-seg button[aria-pressed="true"] { background: var(--sx-surf); color: var(--sx-ink); box-shadow: 0 1px 3px rgba(0,0,0,.12); }
.sx-img { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.sx-img i { width: 88px; height: 62px; border-radius: 10px; background: var(--sx-soft) center / cover; border: 1px solid var(--sx-line); }
.sx-pair { display: grid; gap: 6px; padding: 10px; border-radius: 12px; background: var(--sx-soft); }
.sx-pair-hd { display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 800; color: var(--sx-ink3); }
.sx-msg { font-size: 12.5px; font-weight: 600; color: var(--sx-ink3); }
.sx-msg.is-err { color: var(--sx-err); } .sx-msg.is-ok { color: var(--sx-ok); }

.sx-add { border: 2px dashed #B9C7DD; border-radius: 14px; padding: 14px; display: grid; gap: 10px; background: transparent; }
.sx-add-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.sx-add-grid button { display: grid; gap: 4px; padding: 12px; border: 1px solid var(--sx-line); border-radius: 12px; background: var(--sx-surf); text-align: left; cursor: pointer; font-family: var(--sx-font); color: var(--sx-ink); transition: border-color 160ms, transform 160ms var(--sx-out); }
.sx-add-grid button:hover { border-color: var(--sx-blue); transform: translateY(-1px); }
.sx-add-grid b { font-size: 13.5px; } .sx-add-grid span { font-size: 12px; color: var(--sx-ink3); line-height: 1.4; }

.sx-preview { position: sticky; top: 78px; display: grid; gap: 10px; }
.sx-preview-bar { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.sx-stage { border-radius: 18px; background: var(--sx-stage); padding: 14px; display: flex; justify-content: center; min-width: 0; height: calc(100vh - 170px); min-height: 520px; overflow: hidden; }
.sx-frame { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 40px -24px rgba(11,30,61,.5); position: relative; height: 100%; }
.sx-frame.is-phone { width: min(390px, 100%); }
.sx-frame.is-desk { width: 100%; }
.sx-frame iframe { border: 0; display: block; background: #fff; }
.sx-frame.is-phone iframe { width: 100%; height: 100%; }
.sx-frame.is-desk iframe { width: 1280px; transform-origin: 0 0; }
.sx-loading { position: absolute; inset: 0; display: grid; place-items: center; background: rgba(255,255,255,.7); font-weight: 700; color: var(--sx-ink2); opacity: 0; pointer-events: none; transition: opacity 200ms; }
.sx-frame.is-loading .sx-loading { opacity: 1; }

.sx-bar { position: fixed; left: 0; right: 0; bottom: 0; z-index: 900; padding: 12px 16px calc(12px + env(safe-area-inset-bottom, 0px)); background: rgba(255,255,255,.94); backdrop-filter: blur(8px); border-top: 1px solid var(--sx-line); }
[data-theme="dark"] .sx-bar { background: rgba(10,20,38,.94); }
.sx-bar-in { max-width: 1500px; margin: 0 auto; display: flex; align-items: center; gap: 12px; }
.sx-bar-in p { flex: 1; margin: 0; font-size: 13.5px; font-weight: 600; color: var(--sx-ink3); }
.sx-bar-in p.is-dirty { color: var(--sx-ambi); }
.sx-bar-in p.is-err { color: var(--sx-err); }

@media (max-width: 1100px) {
    .sx-layout { grid-template-columns: minmax(0, 1fr); }
    .sx-preview { position: static; }
    .sx-stage { height: 640px; min-height: 0; }
}
@media (max-width: 560px) {
    .sx-add-grid { grid-template-columns: minmax(0, 1fr); }
    .sx-bar-in p { font-size: 12.5px; }
    .sx-stage { padding: 8px; height: 560px; }
}
@media (prefers-reduced-motion: reduce) { .sx *, .sx *::after { transition-duration: 1ms !important; } }
</style>
@endpush

@section('content')
<div class="sx" id="sx">
    <header class="sx-top"><div class="sx-top-in">
        <div>
            <h1>Sections de la page d’accueil</h1>
            <p>Modifiez les textes, masquez, déplacez ou ajoutez des sections. L’aperçu montre votre vraie vitrine.</p>
        </div>
        <nav class="sx-links" aria-label="Autres pages">
            <a class="sx-btn sx-btn-sm" href="{{ $publicUrl }}" target="_blank" rel="noopener">Voir ma vitrine ↗</a>
            <a class="sx-btn sx-btn-sm" href="{{ route('boutique.storefront.edit') }}">Ma vitrine</a>
            <a class="sx-btn sx-btn-sm" href="{{ route('boutique.grow') }}">Boutique</a>
        </nav>
    </div></header>

    <div class="sx-layout">
        <div class="sx-panel">
            <nav class="sx-themes" aria-label="Thème à modifier">
                @foreach($models as $k => [$label])
                <a href="{{ route('boutique.storefront.sections', ['theme' => $k]) }}" @if($k === $theme) aria-current="page" @endif data-theme-link>{{ $label }}@if($k === $published)<small>en ligne</small>@endif</a>
                @endforeach
            </nav>
            @if($theme !== $published)
            <p class="sx-hint" style="padding:10px 12px;border-radius:12px;background:#FEF3E0;color:#92400E">Vous modifiez le thème {{ $models[$theme][0] }}, qui n’est pas en ligne. Vos clients verront ces sections quand vous choisirez ce thème dans <a href="{{ route('boutique.storefront.edit') }}" style="color:inherit">Ma vitrine</a>.</p>
            @endif
            <ol class="sx-list" id="sxList" aria-label="Sections, dans l’ordre de la page"></ol>
            <div class="sx-add" id="sxAdd">
                <b style="font-size:14px">Ajouter une section</b>
                <div class="sx-add-grid" id="sxAddGrid"></div>
            </div>
        </div>

        <section class="sx-preview" aria-label="Aperçu">
            <div class="sx-preview-bar">
                <div class="sx-seg" role="group" aria-label="Appareil"><button type="button" data-dev="phone" aria-pressed="true">Mobile</button><button type="button" data-dev="desk" aria-pressed="false">Ordinateur</button></div>
                <button type="button" class="sx-btn sx-btn-sm" id="sxReload">Recharger l’aperçu</button>
            </div>
            <div class="sx-stage" id="sxStage">
                <div class="sx-frame is-phone is-loading" id="sxFrame"><iframe id="sxIframe" title="Aperçu de la page d’accueil"></iframe><div class="sx-loading">Chargement…</div></div>
            </div>
        </section>
    </div>

    <div class="sx-bar"><div class="sx-bar-in">
        <p id="sxState" aria-live="polite">Tout est enregistré</p>
        <button type="button" class="sx-btn sx-btn-main" id="sxSave">Enregistrer et publier</button>
    </div></div>
    <input type="file" id="sxFile" accept="image/jpeg,image/png,image/webp" class="sx-sr" tabindex="-1">
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    const G = @json($sxData);
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const $ = id => document.getElementById(id);
    const esc = v => String(v == null ? '' : v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const svg = (d, s) => '<svg width="' + (s || 18) + '" height="' + (s || 18) + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' + d + '"/></svg>';
    const IC = { grip: 'M9 6h.01M15 6h.01M9 12h.01M15 12h.01M9 18h.01M15 18h.01', up: 'M12 19V5M5 12l7-7 7 7', down: 'M12 5v14M19 12l-7 7-7-7', eye: 'M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z', eyeOff: 'M3 3l18 18M10.6 5.1A10 10 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.6 6.6C3.9 8.4 2 12 2 12s3.6 7 10 7c1.9 0 3.6-.6 5-1.5', trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3', chev: 'm6 9 6 6 6-6' };
    const theme = G.theme, DEF = G.defaults[theme] || {};
    const isBuiltin = t => (G.builtin[theme] || []).includes(t);
    let list = JSON.parse(JSON.stringify(G.home[theme] || []));
    let open = null, dirty = false, saving = false;

    /* ───────── État : modifié / enregistré ───────── */
    function setState(txt, cls) { const s = $('sxState'); s.textContent = txt; s.className = cls || ''; }
    function touch() { dirty = true; setState('Modifications non enregistrées', 'is-dirty'); }
    window.addEventListener('beforeunload', e => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
    document.querySelectorAll('[data-theme-link]').forEach(a => a.addEventListener('click', e => { if (dirty && !confirm('Vos modifications ne sont pas enregistrées. Changer de thème quand même ?')) e.preventDefault(); else dirty = false; }));

    /* ───────── Résumé d'une section (ligne sous son nom) ───────── */
    function summary(s) {
        const d = s.d || {};
        if (!s.on) return 'Masquée';
        if (s.type === 'category') return d.cat ? d.cat + ' · ' + d.limit + ' produits' : 'Choisissez une catégorie';
        if (s.type === 'imgtext' && !d.img && !d.title) return 'Ajoutez une photo et un titre';
        if ((s.type === 'banner' || s.type === 'text') && !d.title && !d.text) return 'À remplir';
        if (s.type === 'qa') { const n = (d.items || []).filter(q => q.q).length; return n ? n + ' question' + (n > 1 ? 's' : '') : 'Ajoutez vos questions'; }
        const t = d.title || (DEF[s.type] || {}).title;
        return t || G.types[s.type][1];
    }

    /* ───────── Champs d'une section ───────── */
    function field(s, i, [k, kind, label, max]) {
        const d = s.d, ph = (DEF[s.type] || {})[k], id = 'f' + i + k;
        const lbl = '<label class="sx-lbl" for="' + id + '">' + esc(label) + '</label>';
        if (kind === 'line' || kind === 'link') {
            return '<div>' + lbl + '<input class="sx-input" id="' + id + '" data-k="' + k + '" maxlength="' + (max || 300) + '"' + (kind === 'link' ? ' type="url" inputmode="url" placeholder="https://…"' : ' placeholder="' + esc(ph || '') + '"') + ' value="' + esc(d[k]) + '">' + (kind === 'line' && max ? '<span class="sx-count" data-count="' + k + '">' + (d[k] || '').length + ' / ' + max + '</span>' : '') + '</div>';
        }
        if (kind === 'text') return '<div>' + lbl + '<textarea class="sx-area" id="' + id + '" data-k="' + k + '" maxlength="' + max + '" placeholder="' + esc(ph || '') + '">' + esc(d[k]) + '</textarea><span class="sx-count" data-count="' + k + '">' + (d[k] || '').length + ' / ' + max + '</span></div>';
        if (kind === 'img') {
            const url = d.img ? G.images[d.img] : null;
            return '<div><span class="sx-lbl">' + esc(label) + '</span><div class="sx-img"><i' + (url ? ' style="background-image:url(\'' + esc(url) + '\')"' : '') + '></i><button type="button" class="sx-btn sx-btn-sm" data-upload>' + (d.img ? 'Changer la photo' : 'Choisir une photo') + '</button>' + (d.img ? '<button type="button" class="sx-btn sx-btn-sm sx-btn-danger" data-img-del>Retirer</button>' : '') + '</div><span class="sx-msg" data-img-msg>JPG, PNG ou WebP · 5 Mo max</span></div>';
        }
        const seg = (opts) => '<div><span class="sx-lbl">' + esc(label) + '</span><div class="sx-seg" role="group" aria-label="' + esc(label) + '">' + Object.entries(opts).map(([v, l]) => '<button type="button" data-seg="' + k + '" data-v="' + v + '" aria-pressed="' + (String(d[k]) === v) + '">' + esc(l) + '</button>').join('') + '</div></div>';
        if (kind === 'side') return seg({ left: 'Gauche', right: 'Droite' });
        if (kind === 'align') return seg({ left: 'À gauche', center: 'Centré' });
        if (kind === 'tone') return seg(G.tones);
        if (kind === 'limit') return seg({ 4: '4', 8: '8' });
        if (kind === 'cat') {
            if (!G.categories.length) return '<p class="sx-hint">Rangez d’abord vos produits par catégorie (fiche produit → Catégorie).</p>';
            return '<div>' + lbl + '<select class="sx-select" id="' + id + '" data-k="' + k + '"><option value="">Choisir une catégorie…</option>' + G.categories.map(c => '<option' + (c === d[k] ? ' selected' : '') + '>' + esc(c) + '</option>').join('') + '</select></div>';
        }
        if (kind === 'items') {
            const di = (DEF.promise || {}).items || [];
            return '<div style="display:grid;gap:8px"><span class="sx-lbl" style="margin:0">' + esc(label) + '</span>' + d.items.map((it, n) => '<div class="sx-pair"><span class="sx-pair-hd">Engagement ' + (n + 1) + '</span><input class="sx-input" data-item="' + n + '" data-ik="title" maxlength="40" placeholder="' + esc((di[n] || {}).title || '') + '" value="' + esc(it.title) + '" aria-label="Titre de l’engagement ' + (n + 1) + '"><input class="sx-input" data-item="' + n + '" data-ik="text" maxlength="80" placeholder="' + esc((di[n] || {}).text || '') + '" value="' + esc(it.text) + '" aria-label="Texte de l’engagement ' + (n + 1) + '"></div>').join('') + '</div>';
        }
        if (kind === 'faq') {
            return '<div style="display:grid;gap:8px"><span class="sx-lbl" style="margin:0">' + esc(label) + '</span>' + d.items.map((q, n) => '<div class="sx-pair"><span class="sx-pair-hd">Question ' + (n + 1) + '<button type="button" class="sx-icon" style="width:28px;height:28px" data-faq-del="' + n + '" aria-label="Supprimer la question ' + (n + 1) + '">' + svg(IC.trash, 15) + '</button></span><input class="sx-input" data-faq="' + n + '" data-fk="q" maxlength="150" placeholder="Ex. Livrez-vous hors de Conakry ?" value="' + esc(q.q) + '" aria-label="Question ' + (n + 1) + '"><textarea class="sx-area" style="min-height:70px" data-faq="' + n + '" data-fk="a" maxlength="600" placeholder="Votre réponse" aria-label="Réponse ' + (n + 1) + '">' + esc(q.a) + '</textarea></div>').join('') +
                (d.items.length < G.limits.faq ? '<button type="button" class="sx-btn sx-btn-sm" data-faq-add style="justify-self:start">+ Ajouter une question</button>' : '') + '</div>';
        }
        return '';
    }

    /* ───────── Liste ───────── */
    function render() {
        const custom = list.filter(s => !isBuiltin(s.type)).length;
        $('sxList').innerHTML = list.map((s, i) => {
            const t = G.types[s.type], o = open === s.id, fields = G.fields[s.type] || [];
            return '<li class="sx-item' + (o ? ' is-open' : '') + (s.on ? '' : ' is-off') + '" data-i="' + i + '" draggable="true">' +
                '<div class="sx-item-hd"><span class="sx-grip" aria-hidden="true" title="Glisser pour déplacer">' + svg(IC.grip) + '</span>' +
                '<button type="button" class="sx-item-name" data-open aria-expanded="' + o + '"><b>' + esc(t[0]) + (isBuiltin(s.type) ? '' : '<span class="sx-tag">AJOUTÉE</span>') + '</b><span>' + esc(summary(s)) + '</span></button>' +
                '<button type="button" class="sx-icon" data-move="-1" aria-label="Monter « ' + esc(t[0]) + ' »"' + (i === 0 ? ' disabled' : '') + '>' + svg(IC.up, 16) + '</button>' +
                '<button type="button" class="sx-icon" data-move="1" aria-label="Descendre « ' + esc(t[0]) + ' »"' + (i === list.length - 1 ? ' disabled' : '') + '>' + svg(IC.down, 16) + '</button>' +
                '<button type="button" class="sx-icon" data-toggle aria-pressed="' + !s.on + '" aria-label="' + (s.on ? 'Masquer' : 'Afficher') + ' « ' + esc(t[0]) + ' »" title="' + (s.on ? 'Masquer' : 'Afficher') + '">' + svg(s.on ? IC.eye : IC.eyeOff, 17) + '</button></div>' +
                (o ? '<div class="sx-body">' + (fields.length ? fields.map(f => field(s, i, f)).join('') : '<p class="sx-hint">' + esc(t[1]) + '. Rien à écrire : elle se remplit avec vos produits.</p>') +
                    (isBuiltin(s.type) ? '<p class="sx-hint">Un champ laissé vide garde le texte prévu par le thème (affiché en gris).</p>' : '<button type="button" class="sx-btn sx-btn-sm sx-btn-danger" data-del style="justify-self:start">' + svg(IC.trash, 15) + ' Supprimer la section</button>') + '</div>' : '') + '</li>';
        }).join('');
        $('sxAddGrid').innerHTML = custom >= G.limits.custom ? '<p class="sx-hint" style="grid-column:1/-1">Maximum ' + G.limits.custom + ' sections ajoutées.</p>'
            : G.addable.map(t => '<button type="button" data-add="' + t + '"><b>+ ' + esc(G.types[t][0]) + '</b><span>' + esc(G.types[t][1]) + '</span></button>').join('');
    }
    const at = el => list[+el.closest('[data-i]').dataset.i];
    const refreshSummary = li => { const s = list[+li.dataset.i]; li.querySelector('.sx-item-name span').textContent = summary(s); };

    $('sxList').addEventListener('click', e => {
        const li = e.target.closest('[data-i]'); if (!li) return;
        const i = +li.dataset.i, s = list[i];
        if (e.target.closest('[data-open]')) { open = open === s.id ? null : s.id; render(); return; }
        const mv = e.target.closest('[data-move]');
        if (mv && !mv.disabled) { const j = i + +mv.dataset.move; [list[i], list[j]] = [list[j], list[i]]; touch(); render(); $('sxList').querySelector('[data-i="' + j + '"] [data-move="' + mv.dataset.move + '"]')?.focus(); return; }
        if (e.target.closest('[data-toggle]')) { s.on = !s.on; touch(); render(); return; }
        if (e.target.closest('[data-del]')) { if (confirm('Supprimer la section « ' + G.types[s.type][0] + ' » ?')) { list.splice(i, 1); open = null; touch(); render(); } return; }
        const sg = e.target.closest('[data-seg]');
        if (sg) { s.d[sg.dataset.seg] = sg.dataset.seg === 'limit' ? +sg.dataset.v : sg.dataset.v; sg.parentNode.querySelectorAll('button').forEach(b => b.setAttribute('aria-pressed', b === sg ? 'true' : 'false')); touch(); return; }
        if (e.target.closest('[data-faq-add]')) { s.d.items.push({ q: '', a: '' }); touch(); render(); return; }
        const fd = e.target.closest('[data-faq-del]');
        if (fd) { s.d.items.splice(+fd.dataset.faqDel, 1); touch(); render(); return; }
        if (e.target.closest('[data-upload]')) { uploadFor = s; $('sxFile').click(); return; }
        if (e.target.closest('[data-img-del]')) { s.d.img = null; touch(); render(); }
    });
    $('sxList').addEventListener('input', e => {
        const t = e.target, li = t.closest('[data-i]'); if (!li) return;
        const s = list[+li.dataset.i];
        if (t.dataset.k) { s.d[t.dataset.k] = t.value; const c = li.querySelector('[data-count="' + t.dataset.k + '"]'); if (c) c.textContent = t.value.length + ' / ' + t.maxLength; }
        else if (t.dataset.item) s.d.items[+t.dataset.item][t.dataset.ik] = t.value;
        else if (t.dataset.faq) s.d.items[+t.dataset.faq][t.dataset.fk] = t.value;
        touch(); refreshSummary(li);
    });
    $('sxList').addEventListener('change', e => { if (e.target.tagName === 'SELECT') { const li = e.target.closest('[data-i]'); list[+li.dataset.i].d[e.target.dataset.k] = e.target.value; touch(); refreshSummary(li); } });

    /* Glisser-déposer */
    let dragI = null;
    $('sxList').addEventListener('dragstart', e => { const li = e.target.closest('[data-i]'); if (!li || e.target.closest('input,textarea,select')) { e.preventDefault(); return; } dragI = +li.dataset.i; li.classList.add('is-drag'); e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', String(dragI)); });
    $('sxList').addEventListener('dragover', e => { const li = e.target.closest('[data-i]'); if (dragI === null || !li) return; e.preventDefault(); document.querySelectorAll('.sx-item.is-over').forEach(x => x.classList.remove('is-over')); li.classList.add('is-over'); });
    $('sxList').addEventListener('drop', e => { const li = e.target.closest('[data-i]'); if (dragI === null || !li) return; e.preventDefault(); const to = +li.dataset.i; if (to !== dragI) { const [m] = list.splice(dragI, 1); list.splice(to, 0, m); touch(); } dragI = null; render(); });
    $('sxList').addEventListener('dragend', () => { dragI = null; document.querySelectorAll('.is-drag,.is-over').forEach(x => x.classList.remove('is-drag', 'is-over')); });

    /* Ajouter une section : placée en bas, ouverte pour être remplie */
    const blank = t => {
        const d = {};
        (G.fields[t] || []).forEach(([k, kind]) => { d[k] = ({ img: null, side: 'left', align: 'left', tone: 'accent', limit: 4, faq: [{ q: '', a: '' }], items: [{ title: '', text: '' }, { title: '', text: '' }, { title: '', text: '' }] })[kind] ?? ''; });
        if (t === 'category' && G.categories.length) d.cat = G.categories[0];
        return d;
    };
    $('sxAddGrid').addEventListener('click', e => {
        const b = e.target.closest('[data-add]'); if (!b) return;
        const s = { id: 's' + Math.random().toString(36).slice(2, 10), type: b.dataset.add, on: true, d: blank(b.dataset.add) };
        // Avant les questions fréquentes, sinon en bas
        const faq = list.findIndex(x => x.type === 'faq');
        list.splice(faq === -1 ? list.length : faq, 0, s);
        open = s.id; touch(); render();
        const li = $('sxList').querySelector('.is-open'); if (li) { li.scrollIntoView({ block: 'center', behavior: 'smooth' }); li.querySelector('input,textarea,select,button[data-upload]')?.focus({ preventScroll: true }); }
    });

    /* Photo : envoyée tout de suite, gardée dans la section */
    let uploadFor = null;
    $('sxFile').addEventListener('change', async e => {
        const f = e.target.files[0]; e.target.value = '';
        const s = uploadFor; if (!f || !s) return;
        const msg = () => $('sxList').querySelector('.is-open [data-img-msg]');
        const say = (t, c) => { const m = msg(); if (m) { m.textContent = t; m.className = 'sx-msg ' + (c || ''); } };
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(f.type)) return say('Format refusé : JPG, PNG ou WebP.', 'is-err');
        if (f.size > 5 * 1048576) return say('Photo trop lourde : 5 Mo maximum.', 'is-err');
        say('Envoi en cours…');
        const fd = new FormData(); fd.append('file', f); fd.append('_token', CSRF);
        try {
            const r = await fetch(G.urls.upload, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } });
            const j = await r.json();
            if (!r.ok) return say((j.errors && j.errors.file && j.errors.file[0]) || j.message || 'Photo refusée.', 'is-err');
            G.images[j.path] = j.url; s.d.img = j.path; touch(); render();
            say(j.width && j.width < 1000 ? 'Photo de ' + j.width + ' px : elle risque d’être floue.' : 'Photo ajoutée.', j.width && j.width < 1000 ? 'is-err' : 'is-ok');
        } catch (x) { say('Envoi impossible : vérifiez la connexion.', 'is-err'); }
    });

    /* ───────── Aperçu : la vraie vitrine, dans le thème modifié ───────── */
    const iframe = $('sxIframe'), frame = $('sxFrame');
    let dev = 'phone';
    function fit() {
        if (dev !== 'desk') { iframe.style.transform = ''; iframe.style.height = '100%'; return; }
        const k = frame.clientWidth / 1280;
        iframe.style.transform = 'scale(' + k + ')'; iframe.style.height = (frame.clientHeight / k) + 'px';
    }
    function load() { frame.classList.add('is-loading'); iframe.src = G.urls.preview + '?modele=' + theme + '&apercu=1&t=' + Date.now(); }
    iframe.addEventListener('load', () => frame.classList.remove('is-loading'));
    document.querySelectorAll('[data-dev]').forEach(b => b.addEventListener('click', () => {
        dev = b.dataset.dev; document.querySelectorAll('[data-dev]').forEach(x => x.setAttribute('aria-pressed', x === b ? 'true' : 'false'));
        frame.className = 'sx-frame ' + (dev === 'desk' ? 'is-desk' : 'is-phone'); fit();
    }));
    if ('ResizeObserver' in window) new ResizeObserver(fit).observe(frame); else window.addEventListener('resize', fit);
    iframe.addEventListener('load', fit);
    $('sxReload').addEventListener('click', load);

    /* ───────── Enregistrer et publier ───────── */
    $('sxSave').addEventListener('click', async () => {
        if (saving) return;
        const noCat = list.find(s => s.on && s.type === 'category' && !s.d.cat);
        if (noCat) { open = noCat.id; render(); setState('Choisissez la catégorie de la section « Produits d’une catégorie ».', 'is-err'); return; }
        saving = true; $('sxSave').disabled = true; $('sxSave').textContent = 'Enregistrement…';
        try {
            const r = await fetch(G.urls.save, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify({ theme: theme, sections: list }) });
            const j = await r.json().catch(() => ({}));
            if (!r.ok) { setState((j.errors && Object.values(j.errors)[0][0]) || j.message || 'Enregistrement impossible.', 'is-err'); return; }
            list = j.sections; dirty = false;
            setState(theme === G.published ? 'Enregistré et publié : vos clients voient ces sections.' : 'Enregistré. Choisissez ce thème dans « Ma vitrine » pour le mettre en ligne.', '');
            render(); load();
        } catch (x) { setState('Enregistrement impossible : vérifiez la connexion.', 'is-err'); }
        finally { saving = false; $('sxSave').disabled = false; $('sxSave').textContent = 'Enregistrer et publier'; }
    });

    render();
    // L'aperçu démarre une fois la page chargée (il ne retarde pas l'éditeur)
    if (document.readyState === 'complete') load(); else window.addEventListener('load', load);
})();
</script>
@endpush
