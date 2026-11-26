@include('vendeur.shop._section-fields')
{{-- Script de « Sections de la page d'accueil » (inclus par vendeur/shop/sections.blade.php, variable $sxData). --}}
<script>
(function () {
    'use strict';
    const G = @json($sxData);
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const $ = id => document.getElementById(id);
    const theme = G.theme;
    const { esc, svg, IC, DEF, isBuiltin, MAX, summary, field, newItem, kindOf, blank } = window.SXFields(G, theme);
    let list = JSON.parse(JSON.stringify(G.home[theme] || []));
    let open = null, dirty = false, saving = false, tab = 'all', query = '';

    /* ───────── État : modifié / enregistré ───────── */
    function setState(txt, cls) { const s = $('sxState'); s.textContent = txt; s.className = cls || ''; }
    function touch() { dirty = true; setState('Modifications non enregistrées', 'is-dirty'); }
    window.addEventListener('beforeunload', e => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
    document.querySelectorAll('[data-theme-link]').forEach(a => a.addEventListener('click', e => { if (dirty && !confirm('Vos modifications ne sont pas enregistrées. Changer de thème quand même ?')) e.preventDefault(); else dirty = false; }));

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
                (o ? '<div class="sx-body">' + (fields.length ? fields.map(f => field(s, i, f)).join('') : '<p class="sx-hint">' + esc(t[1]) + (G.isProduct ? '.' : '. Rien à écrire : elle se remplit avec vos produits.') + '</p>') +
                    (s.type === 'stats' ? '<p class="sx-hint">Les chiffres viennent de votre boutique (avis, commandes livrées, produits) : ils ne peuvent pas être modifiés.</p>' : '') +
                    (isBuiltin(s.type) && G.isProduct ? '<p class="sx-hint">Le contenu de cette section vient de la « Page de vente » de chaque produit.</p>' : '') +
                    (isBuiltin(s.type) && !G.isProduct ? '<p class="sx-hint">Un champ laissé vide garde le texte prévu par le thème (affiché en gris).</p>' : '') +
                    (!G.isProduct && G.ai.writable.includes(s.type) ? '<button type="button" class="sx-btn sx-btn-sm sx-btn-ai" data-ai style="justify-self:start"' + (G.ai.ready ? '' : ' disabled title="' + (G.ai.pro ? 'Shopio IA n’est pas configuré' : 'Réservé au plan Pro') + '"') + '>✦ Améliorer avec Shopio IA</button>' + (G.testimonial.includes(s.type) ? '<p class="sx-hint">Shopio IA réécrit seulement le titre : les avis restent ceux de vos vrais clients.</p>' : '') : '') +
                    (isBuiltin(s.type) ? '' : '<button type="button" class="sx-btn sx-btn-sm sx-btn-danger" data-del style="justify-self:start">' + svg(IC.trash, 15) + ' Supprimer la section</button>') + '</div>' : '') + '</li>';
        }).join('');
        renderLibrary(custom);
    }

    /* ───────── Bibliothèque : onglets, recherche ───────── */
    const norm = v => String(v).toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    function renderLibrary(custom) {
        $('sxTabs').innerHTML = [['all', 'Tout']].concat(Object.entries(G.library).map(([k, [l]]) => [k, l])).map(([k, l]) => '<button type="button" data-tab="' + k + '" aria-pressed="' + (tab === k) + '">' + esc(l) + '</button>').join('');
        if (custom >= G.limits.custom) { $('sxAddGrid').innerHTML = '<p class="sx-hint" style="grid-column:1/-1">Maximum ' + G.limits.custom + ' sections ajoutées.</p>'; return; }
        const types = tab === 'all' ? G.addable : G.library[tab][1];
        const shown = types.filter(t => !query || norm(G.types[t][0] + ' ' + G.types[t][1]).includes(norm(query)));
        $('sxAddGrid').innerHTML = shown.length ? shown.map(t => '<button type="button" data-add="' + t + '"><b>+ ' + esc(G.types[t][0]) + '</b><span>' + esc(G.types[t][1]) + '</span></button>').join('')
            : '<p class="sx-hint" style="grid-column:1/-1">Aucune section ne correspond à « ' + esc(query) + ' ».</p>';
    }
    $('sxTabs').addEventListener('click', e => { const b = e.target.closest('[data-tab]'); if (b) { tab = b.dataset.tab; renderLibrary(list.filter(s => !isBuiltin(s.type)).length); } });
    $('sxSearch').addEventListener('input', e => { query = e.target.value.trim(); if (query) tab = 'all'; renderLibrary(list.filter(s => !isBuiltin(s.type)).length); });

    const refreshSummary = li => { const s = list[+li.dataset.i]; li.querySelector('.sx-item-name span').textContent = summary(s); };

    $('sxList').addEventListener('click', e => {
        const li = e.target.closest('[data-i]'); if (!li) return;
        const i = +li.dataset.i, s = list[i], d = s.d;
        if (e.target.closest('[data-open]')) { open = open === s.id ? null : s.id; render(); return; }
        const mv = e.target.closest('[data-move]');
        if (mv && !mv.disabled) { const j = i + +mv.dataset.move; [list[i], list[j]] = [list[j], list[i]]; touch(); render(); $('sxList').querySelector('[data-i="' + j + '"] [data-move="' + mv.dataset.move + '"]')?.focus(); return; }
        if (e.target.closest('[data-toggle]')) { s.on = !s.on; touch(); render(); return; }
        if (e.target.closest('[data-ai]')) { improve([s], e.target.closest('[data-ai]')); return; }
        if (e.target.closest('[data-del]')) { if (confirm('Supprimer la section « ' + G.types[s.type][0] + ' » ?')) { list.splice(i, 1); open = null; touch(); render(); } return; }
        const sg = e.target.closest('[data-seg]');
        if (sg) { d[sg.dataset.seg] = sg.dataset.seg === 'limit' ? +sg.dataset.v : sg.dataset.v; sg.parentNode.querySelectorAll('button').forEach(b => b.setAttribute('aria-pressed', b === sg ? 'true' : 'false')); touch(); refreshSummary(li); return; }
        const la = e.target.closest('[data-list-add]');
        if (la) { const k = la.dataset.listAdd; if (d[k].length < MAX) { d[k].push(JSON.parse(JSON.stringify(newItem(kindOf(s.type, k))))); touch(); render(); const ins = li.ownerDocument.querySelectorAll('.is-open [data-lk="' + k + '"]'); ins[ins.length - 1]?.focus(); } return; }
        const ld = e.target.closest('[data-list-del]');
        if (ld) { d[ld.dataset.listDel].splice(+ld.dataset.n, 1); touch(); render(); return; }
        const ic = e.target.closest('[data-icon]');
        if (ic) { d.items[+ic.dataset.n].icon = ic.dataset.icon; ic.parentNode.querySelectorAll('button').forEach(b => b.setAttribute('aria-pressed', b === ic ? 'true' : 'false')); touch(); return; }
        const rt = e.target.closest('[data-rating]');
        if (rt) { d.items[+rt.dataset.n].rating = +rt.dataset.rating; rt.parentNode.querySelectorAll('button').forEach(b => b.setAttribute('aria-pressed', b === rt ? 'true' : 'false')); touch(); return; }
        const up = e.target.closest('[data-upload]');
        if (up) { uploadFor = { s: s, mode: up.dataset.upload, k: up.dataset.lk, n: up.dataset.n === undefined ? null : +up.dataset.n }; $('sxFile').click(); return; }
        if (e.target.closest('[data-img-del]')) { d.img = null; touch(); render(); }
    });
    $('sxList').addEventListener('input', e => {
        const el = e.target, li = el.closest('[data-i]'); if (!li) return;
        const s = list[+li.dataset.i], d = s.d;
        if (el.dataset.k) { d[el.dataset.k] = el.value; const c = li.querySelector('[data-count="' + el.dataset.k + '"]'); if (c) c.textContent = el.value.length + ' / ' + el.maxLength; }
        else if (el.dataset.lk && el.type !== 'checkbox') { const arr = d[el.dataset.lk], n = +el.dataset.n; if (el.dataset.f) arr[n][el.dataset.f] = el.value; else arr[n] = el.value; }
        else return;
        touch(); refreshSummary(li);
    });
    $('sxList').addEventListener('change', e => {
        const el = e.target, li = el.closest('[data-i]'); if (!li) return;
        const d = list[+li.dataset.i].d;
        if (el.tagName === 'SELECT') d[el.dataset.k] = el.value;
        else if (el.dataset.bool) d[el.dataset.bool] = el.checked;
        else if (el.type === 'checkbox' && el.dataset.lk) d[el.dataset.lk][+el.dataset.n][el.dataset.f] = el.checked;
        else return;
        touch(); refreshSummary(li);
    });

    /* ───────── Shopio IA : textes améliorés, à relire avant de publier ───────── */
    let aiBusy = false;
    async function improve(sections, btn) {
        if (aiBusy || !G.ai.ready) return;
        const todo = sections.filter(s => G.ai.writable.includes(s.type));
        const msg = $('sxAiMsg');
        if (!todo.length) { if (msg) { msg.textContent = 'Aucune section avec du texte à améliorer.'; msg.className = 'sx-msg is-err'; } return; }
        aiBusy = true;
        const label = btn ? btn.textContent : '';
        document.querySelectorAll('.sx-btn-ai').forEach(b => { b.disabled = true; });
        if (btn) { btn.classList.add('is-busy'); btn.textContent = 'Shopio IA écrit…'; }
        if (msg) { msg.textContent = todo.length > 1 ? 'Shopio IA réécrit ' + todo.length + ' sections…' : ''; msg.className = 'sx-msg'; }
        try {
            const r = await fetch(G.urls.ai, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ theme: theme, sections: todo.map(s => ({ id: s.id, type: s.type, d: s.d })), hint: $('sxAiHint') ? $('sxAiHint').value : '' }) });
            const j = await r.json().catch(() => ({}));
            if (!r.ok) { const t = j.error || (j.errors && Object.values(j.errors)[0][0]) || j.message || 'Shopio IA n’a pas répondu.'; if (msg) { msg.textContent = t; msg.className = 'sx-msg is-err'; } setState(t, 'is-err'); return; }
            let n = 0;
            list.forEach(s => { if (j.sections && j.sections[s.id]) { s.d = j.sections[s.id]; n++; } });
            if (todo.length === 1) open = todo[0].id;
            touch(); render();
            Object.keys(j.sections || {}).forEach(id => { const i = list.findIndex(s => s.id === id); const li = $('sxList').querySelector('[data-i="' + i + '"]'); if (li) li.classList.add('is-ai'); });
            const done = n > 1 ? n + ' sections réécrites par Shopio IA : relisez-les, puis « Enregistrer et publier ».' : 'Textes réécrits par Shopio IA : relisez-les, puis « Enregistrer et publier ».';
            setState(done, 'is-dirty');
            if (msg) { msg.textContent = done; msg.className = 'sx-msg is-ok'; }
        } catch (x) {
            setState('Shopio IA ne répond pas : vérifiez la connexion.', 'is-err');
        } finally {
            aiBusy = false;
            document.querySelectorAll('.sx-btn-ai').forEach(b => { b.disabled = !G.ai.ready; });
            if (btn && btn.isConnected) { btn.classList.remove('is-busy'); btn.textContent = label; }
            if ($('sxAiAll')) $('sxAiAll').textContent = '✦ Améliorer toute la page';
        }
    }
    if ($('sxAiAll')) $('sxAiAll').addEventListener('click', () => improve(list.filter(s => s.on), $('sxAiAll')));

    /* Glisser-déposer */
    let dragI = null;
    $('sxList').addEventListener('dragstart', e => { const li = e.target.closest('[data-i]'); if (!li || e.target.closest('input,textarea,select')) { e.preventDefault(); return; } dragI = +li.dataset.i; li.classList.add('is-drag'); e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', String(dragI)); });
    $('sxList').addEventListener('dragover', e => { const li = e.target.closest('[data-i]'); if (dragI === null || !li) return; e.preventDefault(); document.querySelectorAll('.sx-item.is-over').forEach(x => x.classList.remove('is-over')); li.classList.add('is-over'); });
    $('sxList').addEventListener('drop', e => { const li = e.target.closest('[data-i]'); if (dragI === null || !li) return; e.preventDefault(); const to = +li.dataset.i; if (to !== dragI) { const [m] = list.splice(dragI, 1); list.splice(to, 0, m); touch(); } dragI = null; render(); });
    $('sxList').addEventListener('dragend', () => { dragI = null; document.querySelectorAll('.is-drag,.is-over').forEach(x => x.classList.remove('is-drag', 'is-over')); });

    /* Ajouter une section : placée avant les questions fréquentes, ouverte pour être remplie */
    $('sxAddGrid').addEventListener('click', e => {
        const b = e.target.closest('[data-add]'); if (!b) return;
        const s = { id: 's' + Math.random().toString(36).slice(2, 10).padEnd(8, '0'), type: b.dataset.add, on: true, d: blank(b.dataset.add) };
        const faq = list.findIndex(x => x.type === 'faq' || x.type === 'p_faq');
        list.splice(faq === -1 ? list.length : faq, 0, s);
        open = s.id; touch(); render();
        setState('Section « ' + G.types[s.type][0] + ' » ajoutée : remplissez-la puis enregistrez.', 'is-dirty');
        const li = $('sxList').querySelector('.is-open'); if (li) { li.scrollIntoView({ block: 'center', behavior: 'smooth' }); li.querySelector('input,textarea,select,button[data-upload]')?.focus({ preventScroll: true }); }
    });

    /* Photos : envoyées tout de suite, gardées dans la section */
    let uploadFor = null;
    $('sxFile').addEventListener('change', async e => {
        const f = e.target.files[0]; e.target.value = '';
        const u = uploadFor; if (!f || !u) return;
        const say = (t, c) => { const m = $('sxList').querySelector('.is-open [data-img-msg]'); if (m) { m.textContent = t; m.className = 'sx-msg ' + (c || ''); } else if (c === 'is-err') setState(t, 'is-err'); };
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(f.type)) return say('Format refusé : JPG, PNG ou WebP.', 'is-err');
        if (f.size > 5 * 1048576) return say('Photo trop lourde : 5 Mo maximum.', 'is-err');
        say('Envoi en cours…');
        const fd = new FormData(); fd.append('file', f); fd.append('_token', CSRF);
        try {
            const r = await fetch(G.urls.upload, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } });
            const j = await r.json();
            if (!r.ok) return say((j.errors && j.errors.file && j.errors.file[0]) || j.message || 'Photo refusée.', 'is-err');
            G.images[j.path] = j.url;
            if (u.mode === 'img') u.s.d.img = j.path;
            else if (u.mode === 'new') u.s.d[u.k].push({ img: j.path, title: '', text: '', button: '', link: '' });
            else u.s.d[u.k][u.n].img = j.path;
            touch(); render();
            say(j.width && j.width < 1000 ? 'Photo de ' + j.width + ' px : elle risque d’être floue.' : 'Photo ajoutée.', j.width && j.width < 1000 ? 'is-err' : 'is-ok');
        } catch (x) { say('Envoi impossible : vérifiez la connexion.', 'is-err'); }
    });

    /* ───────── Aperçu : la vraie vitrine, dans le thème modifié ───────── */
    const iframe = $('sxIframe'), frame = $('sxFrame');
    let dev = 'phone';
    function fit() {
        if (dev !== 'desk') { iframe.style.transform = ''; iframe.style.height = '100%'; return; }
        const k = frame.clientWidth / 1280; if (!k) return;
        iframe.style.transform = 'scale(' + k + ')'; iframe.style.height = (frame.clientHeight / k) + 'px';
    }
    function load() {
        if (G.isProduct && !G.products.length) { frame.classList.remove('is-loading'); iframe.srcdoc = '<p style="font:600 15px system-ui;padding:24px;color:#45455E">Ajoutez un produit pour voir l’aperçu de la page produit.</p>'; return; }
        frame.classList.add('is-loading');
        iframe.src = G.isProduct ? G.products[+($('sxProduct') ? $('sxProduct').value : 0)].url + '?apercu=1&t=' + Date.now() : G.urls.preview + '?modele=' + theme + '&apercu=1&t=' + Date.now();
    }
    if ($('sxProduct')) $('sxProduct').addEventListener('change', load);
    iframe.addEventListener('load', () => { frame.classList.remove('is-loading'); fit(); });
    document.querySelectorAll('[data-dev]').forEach(b => b.addEventListener('click', () => {
        dev = b.dataset.dev; document.querySelectorAll('[data-dev]').forEach(x => x.setAttribute('aria-pressed', x === b ? 'true' : 'false'));
        frame.className = 'sx-frame ' + (dev === 'desk' ? 'is-desk' : 'is-phone'); fit();
    }));
    if ('ResizeObserver' in window) new ResizeObserver(fit).observe(frame); else window.addEventListener('resize', fit);
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
            // Lignes vides et liens non valides sont retirés par le serveur : on le dit si c'est le cas
            const count = l => l.reduce((n, s) => n + (Array.isArray(s.d.items) && s.type !== 'promise' ? s.d.items.length : 0), 0);
            const dropped = count(list) > count(j.sections);
            list = j.sections; dirty = false;
            setState((theme === G.published || G.isProduct ? 'Enregistré et publié : vos clients voient ces sections.' : 'Enregistré. Choisissez ce thème dans « Ma vitrine » pour le mettre en ligne.') + (dropped ? ' Les lignes vides et les liens non reconnus ont été retirés.' : ''), '');
            render(); load();
        } catch (x) { setState('Enregistrement impossible : vérifiez la connexion.', 'is-err'); }
        finally { saving = false; $('sxSave').disabled = false; $('sxSave').textContent = 'Enregistrer et publier'; }
    });

    render();
    // L'aperçu démarre une fois la page chargée (il ne retarde pas l'éditeur)
    if (document.readyState === 'complete') load(); else window.addEventListener('load', load);
})();
</script>
