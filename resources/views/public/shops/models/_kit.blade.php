{{--
    Parcours d'achat commun aux modèles de page boutique (même parcours, styles différents) :
      1. Fiche rapide (fenêtre centrale) : photo, catégorie, nom, prix, badges, description, panier, favori,
         « Négocier le prix avec la boutique », 3 suggestions (même catégorie et prix proche d'abord).
      2. Favoris et « vus récemment » (6 produits), gardés dans ce navigateur.
      3. Panier (tiroir à droite) : −/+, retirer, total ; l'icône du panier rebondit à chaque ajout.
      4. Commande : nom, téléphone (9 chiffres commençant par 6), commune, repère ; erreurs sous chaque champ.
      5. Confirmation : vraie commande enregistrée, « Commande #… envoyée ».
    API pour les modèles (window.SM) : products, byId(id), money(n), fav(id), favs(), recent(), cartCount(),
    cartTotal(), add(id, el), open(id), openCart(), canBuy(p), buyLabel(p), suggest(p), subscribe(fn).
    Attributs reconnus partout : data-sm-quick="id", data-sm-add="id", data-sm-fav="id", data-sm-cart,
    data-sm-count (nombre d'articles), data-sm-total (total du panier).
    Variables : $shop, $pm, $previewLink
--}}
<div class="sm-scrim" id="smScrim" hidden></div>

<div class="sm-quick" id="smQuick" role="dialog" aria-modal="true" aria-labelledby="smQName" hidden>
    <button type="button" class="sm-x" data-sm-close aria-label="Fermer">&times;</button>
    <div class="sm-q-media" id="smQMedia"></div>
    <div class="sm-q-body">
        <span class="sm-q-cat" id="smQCat"></span>
        <h2 id="smQName"></h2>
        <div class="sm-q-price" id="smQPrice"></div>
        <div class="sm-badges" id="smQBadges"></div>
        <p class="sm-q-desc" id="smQDesc"></p>
        <div class="sm-q-actions">
            <button type="button" class="sm-btn sm-btn-main" id="smQAdd">Ajouter au panier</button>
            <button type="button" class="sm-fav" id="smQFav" aria-pressed="false" aria-label="Ajouter aux favoris"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20.5s-7.5-4.6-9.3-9.2C1.4 7.9 3.6 4.5 7 4.5c2 0 3.5 1.1 5 3 1.5-1.9 3-3 5-3 3.4 0 5.6 3.4 4.3 6.8-1.8 4.6-9.3 9.2-9.3 9.2z"/></svg></button>
        </div>
        <p class="sm-note" id="smQNote" hidden></p>
        <div class="sm-q-links"><a id="smQNego" hidden>Négocier le prix avec la boutique</a><a id="smQPage">Voir la fiche complète</a></div>
        <div class="sm-q-sugg" id="smQSuggBox"><h3>Vous aimerez aussi</h3><div class="sm-sugg" id="smQSugg"></div></div>
    </div>
</div>

<aside class="sm-cart" id="smCart" role="dialog" aria-modal="true" aria-labelledby="smCartTitle" hidden>
    <button type="button" class="sm-x" data-sm-close aria-label="Fermer le panier">&times;</button>
    <div class="sm-cart-hd"><h2 id="smCartTitle">Mon panier</h2></div>
    {{-- Étape 1 : panier --}}
    <div class="sm-cart-body" id="smStepCart"></div>
    <div class="sm-cart-ft" id="smCartFt">
        <div class="sm-total"><span>Total</span><b data-sm-total></b></div>
        <p class="sm-note">Frais de livraison confirmés par la boutique au téléphone.</p>
        <button type="button" class="sm-btn sm-btn-main" id="smToForm">Commander</button>
    </div>
    {{-- Étape 2 : commande --}}
    <form class="sm-cart-body sm-form" id="smStepForm" hidden novalidate>
        <div class="sm-general" id="smGeneral" role="alert"></div>
        <div class="sm-field"><label for="smName">Nom complet @if($pm['user'])<small>(facultatif)</small>@endif</label><input id="smName" name="name" autocomplete="name" maxlength="80" value="{{ $pm['user']['name'] ?? '' }}" aria-describedby="smNameErr"><p class="sm-err" id="smNameErr"></p></div>
        <div class="sm-field"><label for="smPhone">Téléphone</label><input id="smPhone" name="phone" type="tel" inputmode="tel" autocomplete="tel" maxlength="16" placeholder="622 00 00 00" value="{{ $pm['user']['phone'] ?? '' }}" aria-describedby="smPhoneErr"><p class="sm-err" id="smPhoneErr"></p></div>
        <div class="sm-field"><label for="smCommune">Commune</label><select id="smCommune" name="commune" aria-describedby="smCommuneErr"><option value="">Choisir…</option>@foreach($pm['communes'] as $c)<option>{{ $c }}</option>@endforeach</select><p class="sm-err" id="smCommuneErr"></p></div>
        <div class="sm-field"><label for="smLandmark">Repère <small>(facultatif)</small></label><input id="smLandmark" name="landmark" maxlength="120" placeholder="Ex. près de la pharmacie, 2e rue à gauche"></div>
        <div class="sm-cod"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 7h20v10H2zM12 9.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5"/></svg><span><strong>Paiement à la livraison</strong>, en espèces, au livreur. Rien à payer avant.</span></div>
        <div class="sm-total"><span>Total</span><b data-sm-total></b></div>
        <button type="submit" class="sm-btn sm-btn-main" id="smSend">Envoyer ma commande</button>
        <button type="button" class="sm-btn sm-btn-ghost" id="smBack">Retour au panier</button>
    </form>
    {{-- Étape 3 : confirmation --}}
    <div class="sm-cart-body" id="smStepDone" hidden></div>
</aside>

<div class="sm-toast" id="smToast" role="status" hidden></div>

@if($previewLink)
<form class="sm-fb" method="POST" action="{{ route('storefront.preview-feedback', $previewLink['token']) }}">
    @csrf
    <h2>Votre avis sur cette vitrine</h2>
    <p>{{ $shop->name }} prépare sa boutique et aimerait savoir ce que vous en pensez avant de la publier.</p>
    @if(session('feedback_sent'))<p style="color:#15803D;font-weight:800" role="status">Merci, votre avis a été envoyé à la boutique.</p>@endif
    <label class="sm-sr" for="smFbName">Votre prénom (facultatif)</label><input id="smFbName" name="name" maxlength="40" placeholder="Votre prénom (facultatif)">
    <label class="sm-sr" for="smFbMsg">Votre avis</label><textarea id="smFbMsg" name="message" maxlength="500" required placeholder="Ce qui vous plaît, ce qui manque…"></textarea>
    @error('message')<p class="sm-err" role="alert">{{ $message }}</p>@enderror
    <button type="submit">Envoyer mon avis</button>
</form>
@endif

<script>
window.SM = (function () {
    'use strict';
    const PM = @json($pm);
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const $ = id => document.getElementById(id);
    const esc = v => String(v == null ? '' : v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const money = n => Math.round(Number(n)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ' + PM.shop.currency;
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const byId = id => PM.products.find(p => p.id === +id);
    const K = k => 'shopio_pm_' + k + '_' + PM.shop.id;
    const load = (k, d) => { try { return JSON.parse(localStorage.getItem(K(k))) || d; } catch (e) { return d; } };
    const save = (k, v) => { try { localStorage.setItem(K(k), JSON.stringify(v)); } catch (e) {} };
    // Panier, favoris et vus récemment : seulement des produits encore en vente
    let cart = load('cart', []).filter(l => byId(l.id) && !byId(l.id).variants);
    let favs = load('fav', []).filter(id => byId(id));
    let recent = load('recent', []).filter(id => byId(id));
    const subs = [];
    function changed() {
        save('cart', cart); save('fav', favs); save('recent', recent);
        const n = cartCount(), t = money(cartTotal());
        document.querySelectorAll('[data-sm-count]').forEach(el => { el.textContent = n ? n : ''; });
        document.querySelectorAll('[data-sm-total]').forEach(el => { el.textContent = t; });
        document.querySelectorAll('[data-sm-fav]').forEach(el => { const on = favs.includes(+el.dataset.smFav); el.setAttribute('aria-pressed', on ? 'true' : 'false'); el.setAttribute('aria-label', (on ? 'Retirer des favoris : ' : 'Ajouter aux favoris : ') + (byId(el.dataset.smFav) || {}).name); });
        subs.forEach(fn => { try { fn(); } catch (e) {} });
    }
    const cartCount = () => cart.reduce((a, l) => a + l.qty, 0);
    const cartTotal = () => cart.reduce((a, l) => a + l.qty * (byId(l.id) || { price: 0 }).price, 0);
    const out = p => p.stock !== null && p.stock !== undefined && p.stock <= 0;
    const canBuy = p => !PM.settings.vacation && !out(p);
    const buyLabel = p => PM.settings.vacation ? 'Indisponible' : out(p) ? 'Rupture' : p.variants ? 'Choisir les options' : 'Ajouter au panier';

    let toastT = null;
    function toast(msg) { const t = $('smToast'); t.textContent = msg; t.hidden = false; clearTimeout(toastT); toastT = setTimeout(() => { t.hidden = true; }, 2400); }
    function bounce() {
        document.querySelectorAll('[data-sm-cart]').forEach(el => { el.classList.remove('sm-bounce'); void el.offsetWidth; el.classList.add('sm-bounce'); });
    }

    function add(id, from) {
        const p = byId(id);
        if (!p) return;
        if (PM.settings.vacation) return toast('La boutique est en pause : les commandes reprendront à son retour.');
        if (out(p)) return toast('« ' + p.name + ' » est en rupture de stock.');
        if (p.variants) { window.location.href = p.url; return; }
        const line = cart.find(l => l.id === p.id);
        if (line) {
            if (p.stock !== null && line.qty >= p.stock) return toast('Il ne reste que ' + p.stock + ' « ' + p.name + ' » en stock.');
            line.qty++;
        } else cart.push({ id: p.id, qty: 1 });
        changed(); bounce(); toast('« ' + p.name + ' » ajouté au panier');
        if (from) { from.classList.remove('sm-added'); void from.offsetWidth; from.classList.add('sm-added'); }
    }
    function toggleFav(id, el) {
        id = +id;
        favs = favs.includes(id) ? favs.filter(x => x !== id) : [id].concat(favs);
        changed();
        if (el && !reduce) { el.classList.add('is-pop'); setTimeout(() => el.classList.remove('is-pop'), 260); }
        return favs.includes(id);
    }
    function seen(id) { recent = [id].concat(recent.filter(x => x !== id)).slice(0, 6); changed(); }
    /* Suggestions : même catégorie et prix proche d'abord, puis les prix les plus proches */
    function suggest(p, n) {
        return PM.products.filter(x => x.id !== p.id)
            .map(x => ({ x: x, s: (x.cat === p.cat && p.cat ? 0 : 1) * 1e12 + Math.abs(x.price - p.price) }))
            .sort((a, b) => a.s - b.s).slice(0, n || 3).map(o => o.x);
    }

    /* ── Fenêtres : fiche rapide et panier (focus gardé, Échap pour fermer) ── */
    let openEl = null, returnTo = null;
    function show(el) {
        if (openEl && openEl !== el) openEl.hidden = true;
        if (!openEl) returnTo = document.activeElement;
        openEl = el; el.hidden = false; $('smScrim').hidden = false;
        document.documentElement.style.overflow = 'hidden';
        el.scrollTop = 0;
        const f = el.querySelector('button, a[href], input, select, textarea');
        if (f) setTimeout(() => f.focus(), 30);
    }
    function close() {
        if (!openEl) return;
        openEl.hidden = true; $('smScrim').hidden = true; openEl = null;
        document.documentElement.style.overflow = '';
        if (returnTo && returnTo.focus) returnTo.focus();
        if (!$('smStepDone').hidden) stepCart();
    }
    document.addEventListener('keydown', e => {
        if (!openEl) return;
        if (e.key === 'Escape') { e.preventDefault(); close(); return; }
        if (e.key === 'Tab') {
            const list = [...openEl.querySelectorAll('button:not([disabled]), a[href], input, select, textarea')].filter(x => x.offsetParent !== null);
            if (!list.length) return;
            const first = list[0], last = list[list.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    });
    $('smScrim').addEventListener('click', close);
    document.querySelectorAll('[data-sm-close]').forEach(b => b.addEventListener('click', close));

    let quickId = null;
    function open(id) {
        const p = byId(id);
        if (!p) return;
        quickId = p.id; seen(p.id);
        $('smQMedia').innerHTML = p.photo ? '<img src="' + esc(p.photo) + '" alt="' + esc(p.name) + '">' : '<span>' + esc(p.name) + '</span>';
        $('smQCat').textContent = p.cat || PM.shop.name;
        $('smQName').textContent = p.name;
        $('smQPrice').innerHTML = esc(money(p.price)) + (p.old ? '<s>' + esc(money(p.old)) + '</s>' : '');
        $('smQBadges').innerHTML = (PM.shop.verified ? '<span class="sm-badge is-ok">✓ Boutique vérifiée</span>' : '') + '<span class="sm-badge">Paiement à la livraison</span>' + (PM.settings.nego ? '<span class="sm-badge">Négociable</span>' : '')
            + (PM.settings.stock && p.stock !== null && p.stock > 0 && p.stock <= 5 ? '<span class="sm-badge">Plus que ' + p.stock + ' en stock</span>' : '');
        $('smQDesc').textContent = p.desc; $('smQDesc').hidden = !p.desc;
        const addB = $('smQAdd'); addB.textContent = buyLabel(p); addB.disabled = !canBuy(p);
        $('smQNote').hidden = !p.variants; $('smQNote').textContent = p.variants ? 'Ce produit a des options (taille, couleur…) : vous les choisirez sur sa fiche.' : '';
        $('smQNego').hidden = !PM.settings.nego; $('smQNego').href = p.nego;
        $('smQPage').href = p.url;
        const sg = suggest(p);
        $('smQSuggBox').hidden = !sg.length;
        $('smQSugg').innerHTML = sg.map(x => '<button type="button" data-sm-quick="' + x.id + '"><i' + (x.thumb ? ' style="background-image:url(\'' + esc(x.thumb) + '\')"' : '') + '></i>' + esc(x.name) + '<b>' + esc(money(x.price)) + '</b></button>').join('');
        $('smQFav').dataset.smFav = p.id;
        changed();
        show($('smQuick'));
    }
    $('smQAdd').addEventListener('click', () => add(quickId, $('smQAdd')));
    $('smQFav').addEventListener('click', () => toggleFav(quickId, $('smQFav')));

    /* ── Panier ── */
    function stepCart() {
        $('smCartTitle').textContent = 'Mon panier';
        $('smStepForm').hidden = true; $('smStepDone').hidden = true; $('smStepCart').hidden = false;
        if (!cart.length) {
            $('smCartFt').hidden = true;
            $('smStepCart').innerHTML = '<div class="sm-empty"><svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3 4h2l2.4 11h11L21 7H6.2M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2zM18 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg><b>Votre panier est vide</b><span>Ajoutez des produits pour passer commande.</span><button type="button" class="sm-btn sm-btn-main" data-sm-browse>Voir les produits</button></div>';
            return;
        }
        $('smCartFt').hidden = false;
        $('smStepCart').innerHTML = cart.map(l => { const p = byId(l.id); return '<div class="sm-line"><i' + (p.thumb ? ' style="background-image:url(\'' + esc(p.thumb) + '\')"' : '') + '></i><div style="min-width:0"><b>' + esc(p.name) + '</b><small>' + esc(money(p.price)) + ' l’unité</small><div><span class="sm-qty"><button type="button" data-q="' + p.id + '" data-d="-1" aria-label="Retirer un ' + esc(p.name) + '"' + (l.qty <= 1 ? ' disabled' : '') + '>−</button><span aria-live="polite">' + l.qty + '</span><button type="button" data-q="' + p.id + '" data-d="1" aria-label="Ajouter un ' + esc(p.name) + '"' + (p.stock !== null && l.qty >= p.stock ? ' disabled' : '') + '>+</button></span></div><button type="button" class="sm-rm" data-rm="' + p.id + '">Retirer</button></div><span class="sm-line-total">' + esc(money(p.price * l.qty)) + '</span></div>'; }).join('');
    }
    $('smStepCart').addEventListener('click', e => {
        const q = e.target.closest('[data-q]'), rm = e.target.closest('[data-rm]'), browse = e.target.closest('[data-sm-browse]');
        if (q) { const l = cart.find(x => x.id === +q.dataset.q); if (l) { l.qty = Math.max(1, l.qty + +q.dataset.d); changed(); stepCart(); } }
        if (rm) { cart = cart.filter(x => x.id !== +rm.dataset.rm); changed(); stepCart(); toast('Produit retiré du panier'); }
        if (browse) { close(); const g = document.getElementById('produits'); if (g) g.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth' }); }
    });
    function openCart() { stepCart(); show($('smCart')); }
    $('smToForm').addEventListener('click', () => {
        if (PM.settings.vacation) return toast('La boutique est en pause : les commandes reprendront à son retour.');
        $('smCartTitle').textContent = 'Ma commande';
        $('smStepCart').hidden = true; $('smCartFt').hidden = true; $('smStepForm').hidden = false;
        setTimeout(() => (PM.user ? $('smPhone') : $('smName')).focus(), 30);
    });
    $('smBack').addEventListener('click', stepCart);

    /* ── Commande : contrôles sous chaque champ, puis vraie commande ── */
    const fields = { name: 'smName', phone: 'smPhone', commune: 'smCommune' };
    function setErr(k, msg) { const el = $(fields[k]); $(fields[k] + 'Err').textContent = msg || ''; if (msg) el.setAttribute('aria-invalid', 'true'); else el.removeAttribute('aria-invalid'); }
    Object.keys(fields).forEach(k => $(fields[k]).addEventListener('input', () => setErr(k, '')));
    function check() {
        let ok = true;
        const name = $('smName').value.trim(), words = name.split(/\s+/).filter(w => w.length >= 2);
        if (!PM.user && (words.length < 2)) { setErr('name', name ? 'Indiquez votre prénom et votre nom (2 lettres minimum chacun).' : 'Indiquez votre prénom et votre nom.'); ok = false; }
        let d = $('smPhone').value.replace(/\D/g, '');
        if (d.length === 12 && d.startsWith('224')) d = d.slice(3);
        if (!/^6\d{8}$/.test(d)) { setErr('phone', d ? 'Le numéro doit avoir 9 chiffres et commencer par 6 (ex. 622 00 00 00).' : 'Indiquez votre numéro de téléphone.'); ok = false; }
        if (!$('smCommune').value) { setErr('commune', 'Choisissez votre commune.'); ok = false; }
        return ok;
    }
    $('smStepForm').addEventListener('submit', async e => {
        e.preventDefault();
        $('smGeneral').textContent = '';
        if (!check()) { const bad = $('smStepForm').querySelector('[aria-invalid="true"]'); if (bad) bad.focus(); return; }
        const btn = $('smSend'); btn.disabled = true; btn.textContent = 'Envoi…';
        try {
            const r = await fetch(PM.urls.order, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify({ items: cart.map(l => ({ id: l.id, qty: l.qty })), name: $('smName').value.trim(), phone: $('smPhone').value.trim(), commune: $('smCommune').value, landmark: $('smLandmark').value.trim() }) });
            const j = await r.json().catch(() => ({}));
            if (r.status === 201) return done(j);
            if (j.errors) { Object.entries(j.errors).forEach(([k, v]) => { if (fields[k]) setErr(k, v[0]); else $('smGeneral').textContent = v[0]; }); }
            else $('smGeneral').textContent = j.message || (r.status === 429 ? 'Trop d’essais : patientez une minute puis réessayez.' : 'La commande n’a pas pu être envoyée. Réessayez.');
            const bad = $('smStepForm').querySelector('[aria-invalid="true"]'); if (bad) bad.focus();
        } catch (x) { $('smGeneral').textContent = 'Connexion impossible : vérifiez votre internet puis réessayez.'; }
        finally { btn.disabled = false; btn.textContent = 'Envoyer ma commande'; }
    });
    function done(j) {
        const phone = $('smPhone').value.trim();
        cart = []; changed();
        $('smCartTitle').textContent = 'Commande envoyée';
        $('smStepForm').hidden = true; $('smStepDone').hidden = false;
        $('smStepDone').innerHTML = '<div class="sm-done" role="status"><svg class="sm-check" viewBox="0 0 100 100" aria-hidden="true"><circle cx="50" cy="50" r="44"/><path d="M30 52l14 14 26-30"/></svg><h3>Commande #' + j.id + ' envoyée</h3><p>' + esc(PM.shop.name) + ' vous appelle au ' + esc(phone) + ' pour confirmer.</p><p><strong>Total : ' + esc(money(j.total)) + '</strong> · à payer à la livraison.</p>' +
            (j.wa ? '<a class="sm-btn sm-btn-wa" href="' + esc(j.wa) + '" target="_blank" rel="noopener">Envoyer aussi sur WhatsApp</a>' : '') +
            '<a class="sm-btn sm-btn-ghost" href="' + esc(j.tracking) + '">Suivre ma commande</a><button type="button" class="sm-btn sm-btn-main" data-sm-continue>Continuer mes achats</button></div>';
        $('smStepDone').querySelector('[data-sm-continue]').addEventListener('click', close);
        $('smStepDone').querySelector('[data-sm-continue]').focus();
    }

    /* ── Attributs data-sm-* utilisables par tous les modèles ── */
    document.addEventListener('click', e => {
        const q = e.target.closest('[data-sm-quick]'), a = e.target.closest('[data-sm-add]'), f = e.target.closest('[data-sm-fav]'), c = e.target.closest('[data-sm-cart]');
        if (a) { e.preventDefault(); add(a.dataset.smAdd, a); return; }
        if (f && f !== $('smQFav')) { e.preventDefault(); toggleFav(f.dataset.smFav, f); return; }
        if (q) { e.preventDefault(); open(q.dataset.smQuick); return; }
        if (c) { e.preventDefault(); openCart(); }
    });
    window.addEventListener('storage', e => { if (e.key && e.key.indexOf('shopio_pm_') === 0) { cart = load('cart', []).filter(l => byId(l.id)); favs = load('fav', []).filter(id => byId(id)); recent = load('recent', []).filter(id => byId(id)); changed(); } });
    setTimeout(changed, 0);

    return {
        data: PM, products: PM.products, byId: byId, money: money, esc: esc, reduce: reduce,
        fav: id => favs.includes(+id), favs: () => favs.slice(), recent: () => recent.map(byId).filter(Boolean),
        cartCount: cartCount, cartTotal: cartTotal, cartLines: () => cart.map(l => ({ p: byId(l.id), qty: l.qty })),
        add: add, open: open, openCart: openCart, toggleFav: toggleFav, canBuy: canBuy, buyLabel: buyLabel, suggest: suggest, toast: toast,
        subscribe: fn => { subs.push(fn); },
    };
})();
</script>
