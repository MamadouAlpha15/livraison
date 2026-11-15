{{-- Script de « Boutique » (inclus par vendeur/shop/grow.blade.php, mêmes variables). --}}
@php
    $gwData = [
        'shop' => [
            'name' => $shop->name, 'initials' => $initials, 'logo' => $logoUrl, 'verified' => (bool) $shop->is_approved,
            'address' => (string) $shop->address, 'type' => (string) $shop->type, 'description' => \Illuminate\Support\Str::limit(trim(strip_tags((string) $shop->description)), 120),
            'publicUrl' => $publicUrl, 'host' => $host, 'productCount' => $products->count(),
        ],
        'accent' => $accent,
        'colors' => \App\Support\Storefront::IDENTITY_COLORS,
        'gradients' => \App\Support\Storefront::COVER_GRADIENTS,
        'sources' => \App\Support\Storefront::COVER_SOURCES,
        'cover' => $published['cover'],
        'logo' => $published['logo'],
        'coverUrls' => (object) $coverUrls,
        'productPhoto' => $productPhoto,
        'products' => $products,
        'reviews' => ['count' => $reviewCount, 'avg' => $reviewAvg, 'min' => \App\Support\Growth::RATING_MIN],
        'delivered' => $delivered,
        'currency' => $shop->currency ?? 'GNF',
        'urls' => [
            'coverUpload' => route('boutique.grow.cover-upload'), 'logoUpload' => route('boutique.grow.logo-upload'),
            'qr' => route('boutique.grow.qr'), 'poster' => route('boutique.grow.poster'), 'grow' => route('boutique.grow'),
        ],
    ];
@endphp
<script>
(function () {
    'use strict';
    const G = @json($gwData);
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const $ = id => document.getElementById(id);
    const esc = v => String(v == null ? '' : v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const money = n => Math.round(Number(n)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ' + G.currency;
    const lum = h => [1, 3, 5].map(i => parseInt(h.substr(i, 2), 16) / 255).map(v => v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4)).reduce((a, v, i) => a + v * [0.2126, 0.7152, 0.0722][i], 0);
    const inkFor = c => (1.05 / (lum(c) + 0.05)) >= 3 ? '#FFFFFF' : '#16162A';
    const svgI = (d, s) => '<svg width="' + (s || 16) + '" height="' + (s || 16) + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' + d + '"/></svg>';

    let toastT = null;
    function toast(msg) { const t = $('gwToast'); t.textContent = msg; t.hidden = false; clearTimeout(toastT); toastT = setTimeout(() => { t.hidden = true; }, 2600); }

    /* ───────── Onglets : entrée en perspective, adresse mise à jour ───────── */
    const tabs = [...document.querySelectorAll('[data-tab]')];
    function openTab(k, focus) {
        tabs.forEach(t => { const on = t.dataset.tab === k; t.setAttribute('aria-selected', on ? 'true' : 'false'); t.tabIndex = on ? 0 : -1; if (on && focus) t.focus(); });
        document.querySelectorAll('.gw-tabpanel').forEach(p => {
            const on = p.id === 'tab-' + k;
            p.hidden = !on;
            if (on && !reduce) { p.classList.remove('is-entering'); void p.offsetWidth; p.classList.add('is-entering'); }
        });
        history.replaceState(null, '', G.urls.grow + '?onglet=' + k);
        if (k === 'couverture') renderPreview();
    }
    tabs.forEach((t, i) => {
        t.addEventListener('click', e => { e.preventDefault(); openTab(t.dataset.tab); });
        t.addEventListener('keydown', e => {
            if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
            e.preventDefault();
            const n = tabs[(i + (e.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length];
            openTab(n.dataset.tab, true);
        });
    });

    /* Copier un lien */
    document.addEventListener('click', async e => {
        const b = e.target.closest('[data-copy]');
        if (!b) return;
        const input = document.querySelector(b.dataset.copy);
        try { await navigator.clipboard.writeText(input.value); } catch (x) { input.select(); document.execCommand && document.execCommand('copy'); }
        const old = b.textContent; b.textContent = 'Copié'; setTimeout(() => { b.textContent = old; }, 1800);
        toast('Lien copié');
    });

    /* Segments (un seul bouton actif) et interrupteurs */
    function seg(name, value, onChange) {
        const box = document.querySelector('[data-seg="' + name + '"]');
        const paint = v => box.querySelectorAll('button').forEach(b => b.setAttribute('aria-pressed', b.dataset.v === v ? 'true' : 'false'));
        paint(value);
        box.addEventListener('click', e => { const b = e.target.closest('button'); if (!b || b.disabled) return; paint(b.dataset.v); onChange(b.dataset.v); });
        return paint;
    }
    function sw(el, on, onChange) {
        el.setAttribute('aria-checked', on ? 'true' : 'false');
        el.addEventListener('click', () => { if (el.disabled) return; const v = el.getAttribute('aria-checked') !== 'true'; el.setAttribute('aria-checked', v ? 'true' : 'false'); onChange(v); });
    }

    /* Rendu de la couverture : mêmes classes et styles que la vitrine */
    function slideHTML(s, i, current) {
        const bg = s.url ? '' : ' style="background:' + (G.gradients[s.src] || G.gradients.default) + '"';
        return '<div class="sf-cover-slide' + (i === current ? ' is-on' : '') + '"' + bg + '>' + (s.url ? '<img src="' + esc(s.url) + '" alt="">' : '') + '</div>';
    }
    function coverHTML(o) {
        const st = o.state, slides = o.slides, cur = o.current || 0;
        const nameOn = st.nameOn || st.pos === 'over';
        const logo = !st.logoUrl ? '<span class="sf-cover-initials">' + esc(G.shop.initials) + '</span>' : '<img src="' + esc(st.logoUrl) + '" alt="">';
        const verified = G.shop.verified ? '<span class="sf-cv-chip is-verified">' + svgI('m5 12.5 4.5 4.5L19 7.5', 12) + ' Boutique vérifiée</span>' : '';
        const rating = G.reviews.count >= G.reviews.min && G.reviews.avg ? '<span class="sf-cv-chip is-proof">★ ' + String(G.reviews.avg).replace('.', ',') + ' · ' + G.reviews.count + ' avis</span>' : '';
        const dots = slides.length > 1 ? '<div class="sf-cover-dots">' + slides.map((s, i) => '<button type="button" data-cdot="' + i + '" aria-label="Diapo ' + (i + 1) + '"' + (i === cur ? ' aria-current="true"' : '') + '></button>').join('') + '</div>' : '';
        const name = esc(st.name || G.shop.name);
        return '<header class="sf-cover sf-cover-h-' + st.height + ' sf-logo-' + st.pos + ' sf-logo-' + st.shape + (o.phone ? ' sf-cv-phone' : '') + '" style="--lg0:' + st.size + 'px;--cv-y:' + st.y + '%;--cv-veil:' + (st.veil / 100) + ';--sf-accent:' + st.color + ';--sf-ink:' + inkFor(st.color) + '">' +
            '<div class="sf-cover-media">' + slides.map((s, i) => slideHTML(s, i, cur)).join('') + '<div class="sf-cover-veil"></div>' +
            (o.zone ? '<div class="gw-zone" style="width:' + o.zone + '%"><span>Visible sur téléphone</span></div>' : '') +
            (nameOn ? '<div class="sf-cover-text"><div class="sf-cover-name">' + name + '</div>' + (st.slogan ? '<p class="sf-cover-slogan">' + esc(st.slogan) + '</p>' : '') + '</div>' : '') + dots + '</div>' +
            '<div class="sf-cover-logo">' + logo + '</div>' +
            '<div class="sf-cover-bar">' + (nameOn ? '' : '<div class="sf-cover-title"><div class="sf-cover-name">' + name + '</div>' + verified + '</div>' + (st.slogan ? '<p class="sf-cover-sub">' + esc(st.slogan) + '</p>' : '')) +
            '<div class="sf-cover-meta">' + (nameOn ? verified : '') + rating + '<span class="sf-cv-chip">Paiement à la livraison</span>' + (G.shop.address ? '<span class="sf-cv-chip">' + esc(G.shop.address.slice(0, 28)) + '</span>' : '') + '<span class="sf-cv-chip">' + G.shop.productCount + ' produit' + (G.shop.productCount > 1 ? 's' : '') + '</span></div></div></header>';
    }
    /* Diapo prête à afficher : adresse de la photo (importée ou d'un produit) */
    function resolveSlide(s) {
        if (s.src === 'photo') return Object.assign({}, s, { url: s.url || G.coverUrls[s.img] || null });
        if (s.src === 'product') {
            const p = s.pid ? G.products.find(x => x.id === s.pid) : null;
            return Object.assign({}, s, { url: (p && p.photo) || G.productPhoto });
        }
        return Object.assign({}, s, { url: null });
    }

    /* ═════════════ 1. Couverture et logo ═════════════ */
    const saved = {
        src: G.cover.slides[0].src, y: G.cover.y, veil: G.cover.veil, height: G.cover.height, nameOn: G.cover.on ? G.cover.name_on : true,
        logoSrc: G.shop.logo && G.logo.src === 'current' ? 'current' : 'initials', logoUrl: G.shop.logo,
        shape: G.logo.shape, pos: G.logo.pos, size: G.logo.size, color: G.accent, name: $('gwName').value, slogan: $('gwSlogan').value,
    };
    if (saved.src === 'product' && !G.productPhoto) saved.src = 'default';
    let C = Object.assign({}, saved), device = 'desktop', zone = false, uploadedLogo = null;
    let uploaded = G.cover.slides[0].src === 'photo' && G.cover.slides[0].img ? { img: G.cover.slides[0].img, url: G.coverUrls[G.cover.slides[0].img] } : null;
    const savedUploaded = uploaded;

    function coverThumbs() {
        const opts = [['default', null], ['product', G.productPhoto], ['night', null], ['gold', null], ['blue', null]].filter(([k, u]) => k !== 'product' || u);
        if (uploaded) opts.push(['photo', uploaded.url]);
        $('gwCoverThumbs').innerHTML = opts.map(([k, u]) =>
            '<button type="button" class="gw-thumb" role="radio" aria-checked="' + (C.src === k) + '" data-cover="' + k + '"' + (u ? '' : ' style="background:' + G.gradients[k] + '"') + '>' + (u ? '<img src="' + esc(u) + '" alt="">' : '') + '<span>' + esc(G.sources[k]) + '</span></button>').join('');
    }
    $('gwCoverThumbs').addEventListener('click', e => { const b = e.target.closest('[data-cover]'); if (!b) return; C.src = b.dataset.cover; renderId(); });
    function logoOpts() {
        const opts = [];
        if (G.shop.logo) opts.push(['current', 'Logo actuel', '<img src="' + esc(G.shop.logo) + '" alt="">']);
        opts.push(['initials', 'Initiales', '<span style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:' + C.color + ';color:' + inkFor(C.color) + '">' + esc(G.shop.initials) + '</span>']);
        opts.push(['upload', uploadedLogo ? 'Nouveau logo' : 'Importer', uploadedLogo ? '<img src="' + esc(uploadedLogo.url) + '" alt="">' : svgI('M12 5v14M5 12h14', 20)]);
        $('gwLogoOpts').innerHTML = opts.map(([k, l, i]) => '<button type="button" class="gw-logo-opt" role="radio" aria-checked="' + (C.logoSrc === k) + '" data-logo="' + k + '"><i>' + i + '</i>' + l + '</button>').join('');
    }
    $('gwLogoOpts').addEventListener('click', e => {
        const b = e.target.closest('[data-logo]');
        if (!b) return;
        if (b.dataset.logo === 'upload' && !uploadedLogo) { $('gwLogoFile').click(); return; }
        C.logoSrc = b.dataset.logo;
        C.logoUrl = C.logoSrc === 'upload' ? uploadedLogo.url : (C.logoSrc === 'current' ? G.shop.logo : null);
        renderId();
    });
    function colorSwatches() {
        const list = Object.assign({}, G.colors);
        if (!list[G.accent]) list[G.accent] = 'Votre couleur actuelle';
        $('gwColors').innerHTML = Object.entries(list).map(([hex, name]) => '<button type="button" class="gw-swatch" role="radio" aria-checked="' + (C.color.toUpperCase() === hex) + '" aria-label="' + esc(name) + '" title="' + esc(name) + '" data-color="' + hex + '" style="background:' + hex + '"></button>').join('');
    }
    $('gwColors').addEventListener('click', e => { const b = e.target.closest('[data-color]'); if (b) { C.color = b.dataset.color; renderId(); } });

    const segHeight = seg('height', C.height, v => { C.height = v; renderId(); });
    const segShape = seg('shape', C.shape, v => { C.shape = v; renderId(); });
    const segPos = seg('pos', C.pos, v => { C.pos = v; renderId(); });
    seg('device', device, v => { device = v; renderPreview(true); });
    sw($('gwZone'), zone, v => { zone = v; renderPreview(); });
    const nameOnSw = $('gwNameOn');
    sw(nameOnSw, C.nameOn, v => { C.nameOn = v; renderId(); });
    const ranges = { gwY: ['y', ' %'], gwVeil: ['veil', ' %'], gwSize: ['size', ' px'] };
    const paintRanges = () => Object.entries(ranges).forEach(([id, [k, u]]) => { $(id).value = C[k]; $(id + 'Out').textContent = C[k] + u; });
    Object.entries(ranges).forEach(([id, [k, u]]) => $(id).addEventListener('input', () => { C[k] = +$(id).value; $(id + 'Out').textContent = C[k] + u; renderPreview(); syncHidden(); }));
    paintRanges();
    function counters() { $('gwNameCount').textContent = $('gwName').value.length + ' / 30' + ($('gwName').value.length > 30 ? ' · nom actuel gardé tel quel, raccourcissez-le pour le modifier' : ''); $('gwSloganCount').textContent = $('gwSlogan').value.length + ' / 60'; }
    $('gwName').addEventListener('input', () => { C.name = $('gwName').value; $('gwNameErr').textContent = ''; $('gwName').removeAttribute('aria-invalid'); counters(); renderPreview(); });
    $('gwSlogan').addEventListener('input', () => { C.slogan = $('gwSlogan').value; counters(); renderPreview(); });
    counters();

    function syncHidden() {
        const set = (k, v) => { $('h_' + k).value = v; };
        set('cover_src', C.src); set('cover_img', C.src === 'photo' && uploaded ? uploaded.img : '');
        set('cover_y', C.y); set('cover_veil', C.veil); set('cover_height', C.height); set('name_on', C.nameOn ? 1 : 0);
        set('logo_src', C.logoSrc); set('logo_img', C.logoSrc === 'upload' && uploadedLogo ? uploadedLogo.path : '');
        set('logo_shape', C.shape); set('logo_pos', C.pos); set('logo_size', C.size); set('color', C.color.toUpperCase());
    }
    const coverSlides = () => [resolveSlide({ src: C.src, img: C.src === 'photo' && uploaded ? uploaded.img : null, url: C.src === 'photo' && uploaded ? uploaded.url : null, pid: null })];
    const logoBox = () => C.logoUrl && C.logoSrc !== 'initials' ? '<img src="' + esc(C.logoUrl) + '" alt="">' : '<span style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:' + C.color + ';color:' + inkFor(C.color) + '">' + esc(G.shop.initials) + '</span>';
    function renderPreview(anim) {
        const st = Object.assign({}, C, { logoUrl: C.logoSrc === 'initials' ? null : C.logoUrl });
        const prods = G.products.slice(0, 4);
        const prodHTML = '<div class="gw-prods">' + (prods.length ? prods.map(p => '<div class="gw-prod"><div class="gw-prod-img">' + (p.photo ? '<img src="' + esc(p.thumb || p.photo) + '" alt="">' : svgI('M3 5h18v14H3zM3 15l5-5 4 4 3-3 6 6', 28)) + '</div><div><b>' + esc(p.name) + '</b><span>' + esc(p.priceLabel) + '</span></div></div>').join('') : '<p class="gw-hint" style="grid-column:1/-1">Vos produits apparaîtront ici.</p>') + '</div>';
        // Zone visible sur téléphone : part de la largeur gardée quand la couverture passe au format téléphone (390 px)
        let zoneW = 0;
        if (zone && device === 'desktop') {
            const w = Math.max(320, $('gwStage').clientWidth - 36), h = { l: 380, m: 300, s: 200 }[C.height], hp = { l: 230, m: 180, s: 130 }[C.height];
            zoneW = Math.min(100, Math.round((390 / hp) / (w / h) * 100));
        }
        const cover = coverHTML({ state: st, phone: device === 'phone', slides: coverSlides(), zone: zoneW });
        const style = anim ? '' : ' style="animation:none"';
        $('gwStage').innerHTML = device === 'phone'
            ? '<div class="gw-phone gw-page"' + style + '>' + cover + prodHTML + '</div>'
            : '<div class="gw-browser gw-page"' + style + '><div class="gw-bar"><span class="gw-dots"><i></i><i></i><i></i></span><span class="gw-url">' + esc(G.shop.host) + '</span></div>' + cover + prodHTML + '</div>';
        // Carte de partage WhatsApp et ligne de la recherche Shopio
        const img = coverSlides()[0].url || null;
        $('gwWaCard').innerHTML = '<div class="gw-wa"><div class="gw-wa-bubble"><div class="gw-wa-card"><div class="gw-wa-img" style="background:' + (img ? '#0B1E3D' : (G.gradients[C.src] || G.gradients.default)) + '">' + (img ? '<img src="' + esc(img) + '" alt="">' : '') + '<span class="gw-wa-logo">' + logoBox() + '</span></div><div class="gw-wa-txt"><b>' + esc(C.name || G.shop.name) + '</b><span>' + esc(C.slogan || G.shop.description || 'Boutique en ligne sur Shopio') + '</span><small>' + esc(G.shop.host.split('/')[0]) + '</small></div></div><div class="gw-wa-msg">' + esc(G.shop.publicUrl) + '</div><span class="gw-wa-time">Aperçu du lien partagé</span></div></div>';
        const rating = G.reviews.count >= G.reviews.min && G.reviews.avg ? ' · ★ ' + String(G.reviews.avg).replace('.', ',') + ' (' + G.reviews.count + ' avis)' : '';
        const radius = C.shape === 'round' ? '50%' : C.shape === 'square' ? '8px' : '16px';
        $('gwSearchRow').innerHTML = '<div class="gw-search"><span class="gw-search-logo" style="border-radius:' + radius + '">' + logoBox() + '</span><div style="min-width:0"><b>' + esc(C.name || G.shop.name) + (G.shop.verified ? ' <span class="gw-pill gw-pill-ok" style="height:22px">Vérifiée</span>' : '') + '</b><p>' + esc(C.slogan || G.shop.type || 'Boutique Shopio') + '</p><small>' + G.shop.productCount + ' produit' + (G.shop.productCount > 1 ? 's' : '') + rating + (G.shop.address ? ' · ' + esc(G.shop.address.slice(0, 30)) : '') + '</small></div></div>';
    }
    function renderId() {
        coverThumbs(); logoOpts(); colorSwatches();
        segHeight(C.height); segShape(C.shape); segPos(C.pos);
        nameOnSw.setAttribute('aria-checked', C.nameOn ? 'true' : 'false');
        renderPreview(); syncHidden();
    }

    /* Import d'une image : format, poids, puis envoi ; la largeur sert à prévenir d'un flou */
    async function uploadImage(file, url, maxMb, msgEl, types, typesLabel) {
        msgEl.className = 'gw-hint';
        if (!types.includes(file.type)) { msgEl.className = 'gw-err'; msgEl.textContent = 'Format refusé : ' + typesLabel + ' uniquement.'; return null; }
        if (file.size > maxMb * 1048576) { msgEl.className = 'gw-err'; msgEl.textContent = 'Fichier trop lourd : ' + maxMb + ' Mo maximum (le vôtre fait ' + (file.size / 1048576).toFixed(1).replace('.', ',') + ' Mo).'; return null; }
        msgEl.textContent = 'Envoi en cours…';
        const fd = new FormData(); fd.append('file', file); fd.append('_token', CSRF);
        try {
            const r = await fetch(url, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } });
            const j = await r.json();
            if (!r.ok) { msgEl.className = 'gw-err'; msgEl.textContent = (j.errors && j.errors.file && j.errors.file[0]) || j.message || 'Image refusée.'; return null; }
            return j;
        } catch (e) { msgEl.className = 'gw-err'; msgEl.textContent = 'Envoi impossible : vérifiez la connexion.'; return null; }
    }
    const COVER_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    async function onCoverFile(file) {
        const msg = $('gwCoverMsg');
        const j = await uploadImage(file, G.urls.coverUpload, 5, msg, COVER_TYPES, 'JPG, PNG ou WebP');
        if (!j) return;
        uploaded = { img: j.path, url: j.url };
        G.coverUrls[j.path] = j.url;
        C.src = 'photo';
        if (j.width && j.width < 1200) { msg.className = 'gw-warn'; msg.textContent = 'Image de ' + j.width + ' px de large : elle risque d’être floue, visez 1600 px.'; }
        else { msg.className = 'gw-okmsg'; msg.textContent = 'Photo importée' + (j.width ? ' (' + j.width + ' px de large)' : '') + '. Enregistrez pour la publier.'; }
        renderId();
    }
    $('gwCoverFile').addEventListener('change', e => { if (e.target.files[0]) onCoverFile(e.target.files[0]); e.target.value = ''; });
    const drop = $('gwCoverDrop');
    ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('is-over'); }));
    ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove('is-over'); }));
    drop.addEventListener('drop', e => { const f = e.dataTransfer.files[0]; if (f) onCoverFile(f); });
    $('gwLogoFile').addEventListener('change', async e => {
        const f = e.target.files[0]; e.target.value = '';
        if (!f) return;
        const j = await uploadImage(f, G.urls.logoUpload, 2, $('gwLogoMsg'), ['image/png', 'image/jpeg', 'image/webp'], 'PNG, JPG ou WebP');
        if (!j) return;
        uploadedLogo = j; C.logoSrc = 'upload'; C.logoUrl = j.url;
        $('gwLogoMsg').className = 'gw-okmsg'; $('gwLogoMsg').textContent = 'Logo importé : il remplacera le logo actuel à l’enregistrement.';
        renderId();
    });
    $('gwIdReset').addEventListener('click', () => {
        C = Object.assign({}, saved); uploaded = savedUploaded;
        $('gwName').value = saved.name; $('gwSlogan').value = saved.slogan;
        $('gwNameErr').textContent = ''; $('gwName').removeAttribute('aria-invalid');
        paintRanges(); counters(); renderId(); toast('Modifications annulées');
    });
    $('gwIdForm').addEventListener('submit', e => {
        const name = $('gwName').value.trim();
        if (name.length < 2) { e.preventDefault(); $('gwName').setAttribute('aria-invalid', 'true'); $('gwNameErr').textContent = name ? 'Le nom est trop court (2 caractères minimum).' : 'Le nom de la boutique est obligatoire.'; $('gwName').focus(); return; }
        syncHidden();
    });
    window.addEventListener('resize', () => { if (zone) renderPreview(); });
    renderId(); renderPreview(true);

    /* ═════════════ 2. Partage ═════════════ */
    let qrTarget = 'shop';
    const paintQr = seg('qr', qrTarget, v => { qrTarget = v; renderQr(); });
    if ($('gwPicks')) {
        let pid = G.products[0].id;
        const prod = () => G.products.find(p => p.id === pid);
        const tagged = (u, src) => u + (u.includes('?') ? '&' : '?') + 'utm_source=' + src + '&utm_medium=social';
        function renderPicks() {
            const first = G.products.slice(0, 6);
            if (!first.some(p => p.id === pid)) first[first.length - 1] = prod();
            $('gwPicks').innerHTML = first.map(p => '<button type="button" class="gw-pick" aria-pressed="' + (p.id === pid) + '" data-pid="' + p.id + '" aria-label="' + esc(p.name) + '" title="' + esc(p.name) + '">' + (p.thumb ? '<img src="' + esc(p.thumb) + '" alt="">' : esc(p.name.slice(0, 2).toUpperCase())) + '</button>').join('');
            const p = prod(), link = tagged(p.url, 'whatsapp');
            const text = p.name + ' — ' + p.priceLabel + '\nPaiement à la livraison.\nCommandez ici : ' + link;
            $('gwPLink').value = link;
            $('gwPText').value = text;
            $('gwPWa').href = 'https://wa.me/?text=' + encodeURIComponent(text);
            $('gwPBubble').innerHTML = '<div class="gw-wa-card"><div class="gw-wa-img">' + (p.photo ? '<img src="' + esc(p.photo) + '" alt="">' : '') + '<span class="gw-wa-logo">' + (G.shop.logo ? '<img src="' + esc(G.shop.logo) + '" alt="">' : esc(G.shop.initials)) + '</span><span class="gw-wa-price">' + esc(p.priceLabel) + '</span></div><div class="gw-wa-txt"><b>' + esc(p.name) + '</b><span>' + esc(G.shop.name) + ' · Paiement à la livraison</span><small>' + esc(G.shop.host.split('/')[0]) + '</small></div></div><div class="gw-wa-msg">' + esc(text) + '</div><span class="gw-wa-time">Aperçu dans WhatsApp</span>';
            $('gwPickAll').value = '';
        }
        $('gwPicks').addEventListener('click', e => { const b = e.target.closest('[data-pid]'); if (b) { pid = +b.dataset.pid; renderPicks(); } });
        $('gwPickAll').addEventListener('change', e => { if (e.target.value) { pid = +e.target.value; renderPicks(); } });
        $('gwPText').addEventListener('input', e => { $('gwPWa').href = 'https://wa.me/?text=' + encodeURIComponent(e.target.value); });
        $('gwPQr').addEventListener('click', () => { qrTarget = 'product'; $('gwQrProdSel').value = pid; paintQr(qrTarget); renderQr(); document.querySelector('.gw-qr').scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' }); });
        renderPicks();
    }
    function qrQuery() {
        if (qrTarget === 'cat') return 'categorie=' + encodeURIComponent($('gwQrCatSel').value);
        if (qrTarget === 'product') return 'produit=' + encodeURIComponent($('gwQrProdSel').value);
        return '';
    }
    function renderQr() {
        $('gwQrCat').hidden = qrTarget !== 'cat'; $('gwQrProd').hidden = qrTarget !== 'product';
        const q = qrQuery();
        $('gwQrImg').src = G.urls.qr + (q ? '?' + q : '');
        $('gwQrPoster').href = G.urls.poster + (q ? '?' + q : '');
        const label = qrTarget === 'cat' ? 'la catégorie « ' + $('gwQrCatSel').selectedOptions[0].text.replace(/ \(\d+\)$/, '') + ' »' : qrTarget === 'product' ? 'la fiche « ' + $('gwQrProdSel').selectedOptions[0].text + ' »' : 'votre boutique';
        $('gwQrHint').textContent = 'Mène à ' + label + '. Le QR mène toujours à la version en ligne de la page choisie.';
    }
    if ($('gwQrCatSel')) $('gwQrCatSel').addEventListener('change', renderQr);
    if ($('gwQrProdSel')) $('gwQrProdSel').addEventListener('change', renderQr);
    renderQr();
    /* PNG : le QR (SVG) sur fond blanc, avec le logo au centre */
    $('gwQrPng').addEventListener('click', async () => {
        try {
            const svgText = await (await fetch($('gwQrImg').src)).text();
            const load = src => new Promise((ok, ko) => { const i = new Image(); i.onload = () => ok(i); i.onerror = ko; i.src = src; });
            const qr = await load(URL.createObjectURL(new Blob([svgText], { type: 'image/svg+xml' })));
            const S = 1024, pad = 64, cv = document.createElement('canvas'); cv.width = cv.height = S;
            const x = cv.getContext('2d');
            const box = (px, py, w, r) => { x.beginPath(); if (x.roundRect) x.roundRect(px, py, w, w, r); else x.rect(px, py, w, w); };
            x.fillStyle = '#fff'; x.fillRect(0, 0, S, S);
            x.drawImage(qr, pad, pad, S - pad * 2, S - pad * 2);
            const L = 190, lx = (S - L) / 2;
            x.fillStyle = '#fff'; box(lx - 12, lx - 12, L + 24, 36); x.fill();
            let drawn = false;
            if (G.shop.logo) { try { const lg = await load(G.shop.logo); x.save(); box(lx, lx, L, 28); x.clip(); x.drawImage(lg, lx, lx, L, L); x.restore(); drawn = true; } catch (e) { drawn = false; } }
            if (!drawn) { x.fillStyle = '#1F7CF6'; box(lx, lx, L, 28); x.fill(); x.fillStyle = '#fff'; x.font = '800 80px "Plus Jakarta Sans", sans-serif'; x.textAlign = 'center'; x.textBaseline = 'middle'; x.fillText(G.shop.initials, S / 2, S / 2 + 4); }
            const a = document.createElement('a');
            a.download = 'qr-' + ({ shop: 'boutique', cat: 'categorie', product: 'produit' })[qrTarget] + '.png';
            a.href = cv.toDataURL('image/png'); a.click();
            toast('QR code téléchargé');
        } catch (e) { toast('Téléchargement impossible pour le moment.'); }
    });
    /* Lien de campagne : contrôle avant l'envoi */
    if ($('gwCampForm')) $('gwCampForm').addEventListener('submit', e => {
        const pct = Number($('gwCaPct').value), end = $('gwCaEnd').value, today = $('gwCaEnd').min;
        let ok = true;
        $('gwCaPctErr').textContent = ''; $('gwCaEndErr').textContent = '';
        $('gwCaPct').removeAttribute('aria-invalid'); $('gwCaEnd').removeAttribute('aria-invalid');
        if (!Number.isInteger(pct) || pct < 1 || pct > 90) { ok = false; $('gwCaPct').setAttribute('aria-invalid', 'true'); $('gwCaPctErr').textContent = 'La remise doit être comprise entre 1 et 90 %.'; }
        if (!end) { ok = false; $('gwCaEnd').setAttribute('aria-invalid', 'true'); $('gwCaEndErr').textContent = 'Choisissez la date de fin de la campagne.'; }
        else if (end < today) { ok = false; $('gwCaEnd').setAttribute('aria-invalid', 'true'); $('gwCaEndErr').textContent = 'La date de fin est déjà passée : choisissez aujourd’hui ou plus tard.'; }
        if (!ok) { e.preventDefault(); ($('gwCaPct').getAttribute('aria-invalid') ? $('gwCaPct') : $('gwCaEnd')).focus(); }
    });

    /* ═════════════ 3. Conversion ═════════════ */
    const conv = {};
    document.querySelectorAll('[data-conv]').forEach(el => { const k = el.dataset.conv; conv[k] = el.getAttribute('aria-checked') === 'true'; sw(el, conv[k], v => { conv[k] = v; $('c_' + k).value = v ? 1 : 0; paintPhone(); }); });
    function paintPhone() {
        $('gwPhBuy').textContent = conv.wa_mode ? 'Commander sur WhatsApp' : 'Commander';
        $('gwPhBuy').classList.toggle('is-wa', !!conv.wa_mode);
        const proofs = [];
        if (G.shop.verified) proofs.push(['Boutique vérifiée', true]);
        if (conv.proof_delivered && G.delivered >= 5) proofs.push([G.delivered + ' commandes livrées', false]);
        if (G.reviews.count >= G.reviews.min && G.reviews.avg) proofs.push(['★ ' + String(G.reviews.avg).replace('.', ','), false]);
        proofs.push(['Paiement à la livraison', false]);
        $('gwPhProofs').innerHTML = proofs.map(([l, ok]) => '<span' + (ok ? ' class="is-ok"' : '') + '>' + esc(l) + '</span>').join('');
    }
    paintPhone();
    const inner = document.querySelector('.gw-ph-inner');
    const fitScroll = () => { const box = document.querySelector('.gw-ph-scroll'); if (inner && box && box.clientHeight) inner.style.setProperty('--scroll', Math.min(0, box.clientHeight - inner.scrollHeight) + 'px'); };
    fitScroll();
    tabs.forEach(t => t.addEventListener('click', () => setTimeout(fitScroll, 60)));

    /* Onglet ouvert au chargement : son entrée en perspective */
    const first = document.querySelector('.gw-tabpanel:not([hidden])');
    if (first && !reduce) first.classList.add('is-entering');
})();
</script>
