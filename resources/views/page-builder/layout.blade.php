{{--
    Document d'une page construite (sections et blocs). Les sections ajoutent leur CSS avec
    @push('pb-styles') dans une balise <style data-pb> ; l'aperçu de l'éditeur les remplace à chaque rendu.
    Données : $ctx (App\PageBuilder\RenderContext)
--}}
@php
    $shopPage = \App\Support\ProductPage::shop($ctx->shop);
    $accent = $shopPage['accent'];
@endphp
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ $ctx->csrfPlaceholder ? \App\PageBuilder\Renderer::CSRF_PLACEHOLDER : csrf_token() }}">
@if ($ctx->editing || empty($seo))
<title>{{ $ctx->product->name }} · {{ $ctx->shop->name }}</title>
<meta name="robots" content="noindex, nofollow">
@else
<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
<link rel="canonical" href="{{ $seo['url'] }}">
<meta property="og:type" content="product">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['url'] }}">
@if ($seo['image'])<meta property="og:image" content="{{ $seo['image'] }}">@endif
<meta property="product:price:amount" content="{{ (int) round($ctx->price()) }}">
<meta property="product:price:currency" content="GNF">
<meta name="twitter:card" content="summary_large_image">
@foreach ($seo['ld'] as $ld)
<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endforeach
@endif
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root { color-scheme: light; --accent: {{ $accent }}; --on-accent: {{ \App\Support\ProductPage::onAccent($accent) }};
        --ink: #16162A; --ink2: #45455E; --muted: #62627C; --line: #E3E8F2; --soft: #F6F9FF;
        --font: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif; }
*, *::before, *::after { box-sizing: border-box; }
[hidden] { display: none !important; }
html { -webkit-text-size-adjust: 100%; }
body { margin: 0; background: #FFFFFF; color: var(--ink); font-family: var(--font); line-height: 1.55; -webkit-font-smoothing: antialiased; }
img { max-width: 100%; display: block; }
h1, h2, h3 { text-wrap: balance; }
.pb-num { font-variant-numeric: tabular-nums; }

.pb-header { border-bottom: 1px solid var(--line); background: #FFFFFF; }
.pb-header-in { max-width: 1120px; margin: 0 auto; display: flex; align-items: center; gap: 10px; min-height: 60px;
                padding-left: max(16px, env(safe-area-inset-left, 0px)); padding-right: max(16px, env(safe-area-inset-right, 0px)); }
.pb-shop { display: inline-flex; align-items: center; gap: 10px; color: var(--ink); text-decoration: none; font-weight: 800; font-size: 16px; min-width: 0; }
.pb-shop span:last-child { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pb-shop-av { width: 34px; height: 34px; border-radius: 10px; background: var(--accent); color: var(--on-accent); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; flex: none; }

.pb-section { background: var(--cs-bg); color: var(--cs-text); padding-top: var(--pt-m); padding-bottom: var(--pb-m); }
@media (min-width: 750px) { .pb-section { padding-top: var(--pt-d); padding-bottom: var(--pb-d); } }
.pb-container { max-width: 1120px; margin: 0 auto; padding-left: max(16px, env(safe-area-inset-left, 0px)); padding-right: max(16px, env(safe-area-inset-right, 0px)); }

.pb-rte { overflow-wrap: anywhere; }
.pb-rte > :first-child { margin-top: 0; }
.pb-rte > :last-child { margin-bottom: 0; }
.pb-rte p, .pb-rte ul, .pb-rte ol, .pb-rte blockquote { margin: 0 0 .8em; }
.pb-rte a { color: inherit; text-decoration: underline; text-underline-offset: 2px; }
.pb-rte blockquote { border-left: 3px solid var(--cs-line); padding-left: 14px; color: var(--cs-muted); }
.pb-rte h3, .pb-rte h4 { margin: 1em 0 .4em; line-height: 1.25; }

@if ($ctx->editing)
/* Aperçu de l'éditeur : sections et blocs cliquables */
.pb-editing [data-section-id] { position: relative; cursor: pointer; }
.pb-editing [data-section-id]:hover { outline: 2px dashed rgba(31, 124, 246, .55); outline-offset: -2px; }
.pb-editing [data-block-id]:hover { outline: 1px dashed #1F7CF6; outline-offset: 3px; }
.pb-editing .pb-is-selected { outline: 2px solid #1F7CF6 !important; outline-offset: -2px; }
.pb-editing [data-block-id].pb-is-selected { outline-offset: 3px; }
@endif
@media (prefers-reduced-motion: reduce) { *, *::before, *::after { scroll-behavior: auto !important; transition: none !important; } }
</style>
@stack('pb-styles')
</head>
<body class="{{ $ctx->editing ? 'pb-editing' : '' }}">
<header class="pb-header">
    <div class="pb-header-in">
        <a class="pb-shop" href="{{ $ctx->editing ? '#' : url('/') }}">
            <span class="pb-shop-av" aria-hidden="true">{{ mb_strtoupper(mb_substr($ctx->shop->name, 0, 1)) }}</span>
            <span>{{ $ctx->shop->name }}</span>
        </a>
    </div>
</header>

<main id="pb-main">
    @yield('main')
</main>

<script>
/* Galerie : clic sur une miniature (délégué au document, survit aux rendus de l'aperçu) */
document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-pb-thumb]');
    if (!t) return;
    var g = t.closest('[data-pb-gallery]');
    var main = g && g.querySelector('[data-pb-main-img]');
    if (!main) return;
    main.src = t.getAttribute('data-src');
    g.querySelectorAll('[data-pb-thumb]').forEach(function (n) { n.setAttribute('aria-current', n === t ? 'true' : 'false'); });
});

/* Vidéo YouTube : le lecteur (sans cookies) n'est chargé qu'au clic */
document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-pb-yt]');
    if (!b) return;
    var f = document.createElement('iframe');
    f.src = b.getAttribute('data-pb-yt');
    f.title = b.getAttribute('aria-label') || 'Vidéo';
    f.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
    f.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;border:0';
    b.replaceWith(f);
});

/*
 * Section Produit : prix selon l'option, paliers, récapitulatif, quantité, compte à rebours et commande.
 * Les montants affichés sont indicatifs : le serveur (App\PageBuilder\Commerce\Checkout) recalcule tout.
 */
(function () {
    function fmt(n) { return Math.round(n).toLocaleString('fr-FR').replace(/[\s ]/g, ' ') + ' GNF'; }
    function data(form) {
        try { return JSON.parse(form.querySelector('[data-pb-data]').textContent); } catch (e) { return null; }
    }
    function discounted(base, type, value, maxPct) {
        if (type === 'percent') return Math.round(base * (100 - Math.min(Math.max(value, 0), maxPct)) / 100);
        if (type === 'amount') return Math.max(0, base - Math.max(value, 0));
        return base;
    }
    function text(form, sel, value, hide) {
        form.querySelectorAll(sel).forEach(function (el) { el.textContent = value; if (hide !== undefined) el.hidden = hide; });
    }
    function line(label, amount) {
        var row = document.createElement('div');
        row.className = 'pb-sum-line';
        var a = document.createElement('span'); a.textContent = label;
        var b = document.createElement('span'); b.textContent = fmt(amount);
        row.append(a, b);
        return row;
    }

    function update(form) {
        var d = data(form);
        if (!d) return;
        var v = form.querySelector('[data-pb-variant]:checked') || form.querySelector('select[data-pb-variant]');
        var unit = d.unit, vid = v ? parseInt(v.value, 10) : null;
        d.variants.forEach(function (x) { if (x.id === vid) unit = x.unit; });

        var lines = [];
        if (d.tiers) {
            var chosen = form.querySelector('[data-pb-tier]:checked');
            Object.keys(d.tiers).forEach(function (i) {
                var t = d.tiers[i], base = Math.round(unit * t.qty), total = discounted(base, t.discount, t.value, d.maxPct);
                text(form, '[data-pb-tier-total="' + i + '"]', fmt(total));
                text(form, '[data-pb-tier-base="' + i + '"]', fmt(base), total >= base);
                text(form, '[data-pb-tier-saving="' + i + '"]', 'Tu économises ' + fmt(base - total), total >= base);
                if (chosen && chosen.value === i) lines.push([t.label, total]);
            });
        } else {
            var q = form.querySelector('[data-pb-qty]');
            var qty = q ? Math.min(Math.max(parseInt(q.value, 10) || 1, 1), parseInt(q.max, 10) || 1) : 1;
            lines.push([(qty > 1 ? qty + ' × ' : '') + d.name, unit * qty]);
        }
        form.querySelectorAll('[data-pb-upsell]:checked').forEach(function (c) {
            d.upsells.forEach(function (u) { if (String(u.id) === c.value) lines.push([u.name, u.price]); });
        });

        var commune = form.querySelector('[data-pb-commune]');
        var fee = commune && commune.value && d.fees[commune.value] !== undefined ? parseInt(d.fees[commune.value], 10) : null;
        var feeText = !commune || !commune.value ? (Object.keys(d.fees).length ? 'Choisis ta commune' : 'À confirmer par la boutique')
            : (fee === null ? 'À confirmer par la boutique' : (fee === 0 ? 'Offerte' : fmt(fee)));
        text(form, '[data-pb-fee]', feeText);

        var box = form.querySelector('[data-pb-sum-lines]');
        if (box) { box.replaceChildren.apply(box, lines.map(function (l) { return line(l[0], l[1]); })); }
        var sum = lines.reduce(function (s, l) { return s + l[1]; }, 0) + (fee || 0);
        text(form, '[data-pb-total]', fmt(sum) + (fee === null && commune && commune.value ? ' + livraison' : ''));
    }

    function updateAll() { document.querySelectorAll('[data-pb-checkout]').forEach(update); }

    ['change', 'input'].forEach(function (type) {
        document.addEventListener(type, function (e) {
            var form = e.target.closest && e.target.closest('[data-pb-checkout]');
            if (form) update(form);
        });
    });
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-pb-step]');
        if (!b) return;
        var input = b.parentElement.querySelector('[data-pb-qty]');
        var next = (parseInt(input.value, 10) || 1) + parseInt(b.getAttribute('data-pb-step'), 10);
        input.value = Math.min(Math.max(next, 1), parseInt(input.max, 10) || 1);
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });

    // Compte à rebours vers une vraie échéance ; disparaît une fois passée
    function tick() {
        document.querySelectorAll('[data-pb-countdown]').forEach(function (el) {
            var left = Math.max(0, parseInt(el.getAttribute('data-pb-countdown'), 10) - Math.floor(Date.now() / 1000));
            if (left <= 0) { el.hidden = true; return; }
            var parts = { d: Math.floor(left / 86400), h: Math.floor(left % 86400 / 3600), m: Math.floor(left % 3600 / 60), s: left % 60 };
            Object.keys(parts).forEach(function (k) {
                var n = el.querySelector('[data-u="' + k + '"]');
                if (n) n.textContent = k === 'd' ? parts[k] : String(parts[k]).padStart(2, '0');
            });
        });
    }
    setInterval(tick, 1000);

    document.addEventListener('submit', function (e) {
        var form = e.target.closest && e.target.closest('[data-pb-checkout]');
        if (!form) return;
        e.preventDefault();
        if (document.body.classList.contains('pb-editing')) return; // aperçu de l'éditeur : pas de vraie commande
        var d = data(form), btn = form.querySelector('[data-pb-submit]'), flash = form.querySelector('[data-pb-error]');
        if (!d || !btn) return;
        form.querySelectorAll('[data-err]').forEach(function (el) { el.textContent = ''; });
        form.querySelectorAll('[aria-invalid]').forEach(function (el) { el.removeAttribute('aria-invalid'); });
        flash.hidden = true;
        btn.disabled = true;

        fetch(d.url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form),
        }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (body) { return { status: r.status, body: body }; });
        }).then(function (res) {
            if (res.status === 201) {
                form.querySelector('[data-pb-order]').hidden = true;
                var done = form.querySelector('[data-pb-done]');
                text(form, '[data-pb-done-total]', fmt(res.body.total));
                form.querySelector('[data-pb-track]').href = res.body.tracking || '#';
                done.hidden = false;
                done.focus();
                done.scrollIntoView({ block: 'center' });
                return;
            }
            // Même produit commandé avec ce numéro il y a peu : avertissement, le client peut confirmer
            if (res.status === 409 && res.body.duplicate) {
                var dup = form.querySelector('[data-pb-dup]');
                text(form, '[data-pb-dup-msg]', res.body.message);
                dup.hidden = false;
                dup.scrollIntoView({ block: 'center' });
                return;
            }
            // Code SMS demandé (ou déjà envoyé) : on affiche la case du code
            if (res.status === 202 && res.body.otp_required) {
                var otp = form.querySelector('[data-pb-otp]');
                text(form, '[data-pb-otp-msg]', res.body.message);
                otp.hidden = false;
                otp.querySelector('input').focus();
                return;
            }
            if (res.body.otp_required) form.querySelector('[data-pb-otp]').hidden = false;
            var errors = res.body.errors || {}, first = null;
            Object.keys(errors).forEach(function (k) {
                var slot = form.querySelector('[data-err="' + CSS.escape(k) + '"]');
                if (slot) {
                    slot.textContent = errors[k][0];
                    var input = slot.parentElement.querySelector('input, select, textarea');
                    if (input) { input.setAttribute('aria-invalid', 'true'); first = first || input; }
                }
            });
            flash.textContent = res.body.message || (res.status === 429 ? 'Trop de tentatives : patiente une minute puis réessaie.'
                : 'La commande n\'a pas pu être envoyée. Réessaie.');
            flash.hidden = false;
            (first || flash).focus && (first || flash).scrollIntoView({ block: 'center' });
            if (first) first.focus();
        }).catch(function () {
            flash.textContent = 'Connexion perdue : vérifie ton réseau et réessaie.';
            flash.hidden = false;
        }).finally(function () { btn.disabled = false; });
    });

    document.addEventListener('click', function (e) {
        if (document.body.classList.contains('pb-editing')) return;
        var form = e.target.closest && e.target.closest('[data-pb-checkout]');
        // « C'est une nouvelle commande » : renvoi avec la confirmation
        if (form && e.target.closest('[data-pb-dup-confirm]')) {
            form.querySelector('[data-pb-dup-flag]').value = '1';
            form.querySelector('[data-pb-dup]').hidden = true;
            form.requestSubmit();
        }
        // Nouveau code SMS : renvoi sans code (le serveur limite à un envoi par minute)
        if (form && e.target.closest('[data-pb-otp-resend]')) {
            form.querySelector('[data-pb-otp] input').value = '';
            form.requestSubmit();
        }
        // « Me prévenir du retour en stock » (client connecté)
        var stock = e.target.closest('[data-pb-stock]');
        if (stock) {
            stock.disabled = true;
            fetch(stock.getAttribute('data-pb-stock'), {
                method: 'POST', credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            }).then(function (r) { return r.json(); }).then(function (d) {
                stock.setAttribute('aria-pressed', d.active ? 'true' : 'false');
                stock.querySelector('span').textContent = d.active ? 'Alerte activée : tu seras prévenu' : 'Me prévenir du retour en stock';
            }).catch(function () {}).finally(function () { stock.disabled = false; });
        }
    });

    document.addEventListener('pb:rendered', function () { updateAll(); tick(); });
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', updateAll); else updateAll();
})();
</script>
@if ($ctx->editing)
<script>
/* Pont avec l'éditeur : sélection au clic, rendu en direct sans recharger (la position de défilement est gardée) */
(function () {
    var selected = null;
    function post(m) { window.parent.postMessage(m, window.location.origin); }
    function find(sel) {
        if (!sel || !sel.section) return null;
        if (sel.block) return document.querySelector('[data-block-id="' + CSS.escape(sel.block) + '"]');
        return document.querySelector('[data-section-id="' + CSS.escape(sel.section) + '"]');
    }
    function mark(scroll) {
        document.querySelectorAll('.pb-is-selected').forEach(function (n) { n.classList.remove('pb-is-selected'); });
        var el = find(selected);
        if (!el) return;
        el.classList.add('pb-is-selected');
        if (scroll) {
            var r = el.getBoundingClientRect();
            if (r.bottom < 60 || r.top > window.innerHeight - 60) {
                el.scrollIntoView({ block: 'center', behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
            }
        }
    }
    document.addEventListener('click', function (e) {
        if (e.target.closest('a, button[type="submit"]')) e.preventDefault();
        var s = e.target.closest('[data-section-id]');
        if (!s) return;
        var b = e.target.closest('[data-block-id]');
        selected = { section: s.getAttribute('data-section-id'), block: b ? b.getAttribute('data-block-id') : null };
        mark(false);
        post({ type: 'pb:select', section: selected.section, block: selected.block });
    }, true);
    document.addEventListener('submit', function (e) { e.preventDefault(); }, true);
    window.addEventListener('message', function (e) {
        if (e.source !== window.parent || e.origin !== window.location.origin) return;
        var m = e.data || {};
        if (m.type === 'pb:render') {
            var doc = new DOMParser().parseFromString(m.html, 'text/html');
            var next = doc.getElementById('pb-main');
            if (!next) return;
            document.getElementById('pb-main').replaceWith(document.adoptNode(next));
            document.querySelectorAll('style[data-pb]').forEach(function (n) { n.remove(); });
            doc.querySelectorAll('style[data-pb]').forEach(function (n) { document.head.appendChild(document.adoptNode(n)); });
            mark(false);
            document.dispatchEvent(new CustomEvent('pb:rendered'));
        } else if (m.type === 'pb:select') {
            selected = m.section ? { section: m.section, block: m.block || null } : null;
            mark(true);
        }
    });
    post({ type: 'pb:ready' });
})();
</script>
@endif
</body>
</html>
