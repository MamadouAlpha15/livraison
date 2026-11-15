{{-- Champs des sections (barre d'icônes, avis, diapositives, vidéos…) : partagés par l'éditeur de sections
     (Boutique → Sections) et l'éditeur produit. window.SXFields(G, thème) renvoie les outils de rendu. --}}
<script>
window.SXFields = function (G, theme) {
    'use strict';
    const esc = v => String(v == null ? '' : v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const svg = (d, s) => '<svg width="' + (s || 18) + '" height="' + (s || 18) + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' + d + '"/></svg>';
    const IC = { grip: 'M9 6h.01M15 6h.01M9 12h.01M15 12h.01M9 18h.01M15 18h.01', up: 'M12 19V5M5 12l7-7 7 7', down: 'M12 5v14M19 12l-7 7-7-7', eye: 'M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z', eyeOff: 'M3 3l18 18M10.6 5.1A10 10 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.6 6.6C3.9 8.4 2 12 2 12s3.6 7 10 7c1.9 0 3.6-.6 5-1.5', trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3' };
    const DEF = t => (G.defaults[theme] || {})[t] || (G.defaults.custom || {})[t] || {};
    const isBuiltin = t => (G.builtin[theme] || []).includes(t);
    const MAX = G.limits.list;
    /* ───────── Résumé d'une section (ligne sous son nom) ───────── */
    function summary(s) {
        const d = s.d || {}, n = Array.isArray(d.items) ? d.items.length : 0, plural = (k, w) => k + ' ' + w + (k > 1 ? 's' : '');
        if (!s.on) return 'Masquée';
        switch (s.type) {
            case 'category': return d.cat ? d.cat + ' · ' + d.limit + ' produits' : 'Choisissez une catégorie';
            case 'imgtext': return d.title || (d.img ? 'Photo ajoutée' : 'Ajoutez une photo et un titre');
            case 'qa': case 'faq2': { const q = (d.items || []).filter(x => x.q).length; return q ? plural(q, 'question') : 'Ajoutez vos questions'; }
            case 'icons': return n ? plural(n, 'icône') : 'Ajoutez des icônes';
            case 'quotes': case 'fbposts': case 'testimonials': return n ? plural(n, 'avis') .replace('aviss', 'avis') : 'Ajoutez de vrais avis de clients';
            case 'compare': return n ? plural(n, 'ligne') : 'Ajoutez des lignes';
            case 'slideshow': return n ? plural(n, 'diapositive') : 'Ajoutez des photos';
            case 'videos': case 'reel': return n ? plural(n, 'vidéo') : 'Ajoutez des liens de vidéos';
            case 'marquee': return n ? d.items[0] : 'Ajoutez des messages';
            case 'divider': return G.styles[d.style] || '';
            case 'promo': return d.code ? 'Code ' + d.code : 'Indiquez le code';
            case 'stats': return 'Vos vrais chiffres, calculés par Shopio';
            case 'trust': return d.text || DEF('trust').text;
        }
        if ((s.type === 'banner' || s.type === 'text') && !d.title && !d.text) return 'À remplir';
        return d.title || DEF(s.type).title || G.types[s.type][1];
    }

    /* ───────── Champs ───────── */
    const input = (attrs, val, ph, max) => '<input class="sx-input" ' + attrs + ' maxlength="' + max + '" placeholder="' + esc(ph || '') + '" value="' + esc(val) + '">';
    const seg = (k, label, opts, cur) => '<div><span class="sx-lbl">' + esc(label) + '</span><div class="sx-seg" role="group" aria-label="' + esc(label) + '">' + Object.entries(opts).map(([v, l]) => '<button type="button" data-seg="' + k + '" data-v="' + v + '" aria-pressed="' + (String(cur) === v) + '">' + esc(l) + '</button>').join('') + '</div></div>';
    const imgBox = (path, attrs) => { const url = path ? G.images[path] : null; return '<div class="sx-mini"><i' + (url ? ' style="background-image:url(\'' + esc(url) + '\')"' : '') + '></i><button type="button" class="sx-btn sx-btn-sm" ' + attrs + '>' + (path ? 'Changer' : 'Photo') + '</button></div>'; };
    const addBtn = (k, n, label) => n < MAX ? '<button type="button" class="sx-btn sx-btn-sm" data-list-add="' + k + '" style="justify-self:start">+ ' + esc(label) + '</button>' : '<p class="sx-hint">' + MAX + ' au maximum.</p>';
    const delBtn = (k, n, label) => '<button type="button" class="sx-icon" style="width:28px;height:28px" data-list-del="' + k + '" data-n="' + n + '" aria-label="' + esc(label) + '">' + svg(IC.trash, 15) + '</button>';
    const lf = (k, n, f) => 'data-lk="' + k + '" data-n="' + n + '" data-f="' + f + '"';

    function field(s, i, [k, kind, label, max]) {
        const d = s.d, ph = DEF(s.type)[k], id = 'f' + i + k;
        const lbl = '<label class="sx-lbl" for="' + id + '">' + esc(label) + '</label>';
        const count = max ? '<span class="sx-count" data-count="' + k + '">' + (d[k] || '').length + ' / ' + max + '</span>' : '';
        switch (kind) {
            case 'line': case 'code': return '<div>' + lbl + input('id="' + id + '" data-k="' + k + '"' + (kind === 'code' ? ' style="text-transform:uppercase;letter-spacing:.06em"' : ''), d[k], kind === 'code' ? 'Ex. BIENVENUE10' : ph, max) + count + (kind === 'code' ? '<span class="sx-hint">Créez d’abord ce code dans « Codes promo », sinon il ne marchera pas à la commande.</span>' : '') + '</div>';
            case 'link': return '<div>' + lbl + input('id="' + id + '" data-k="' + k + '" type="url" inputmode="url"', d[k], 'https://…', 300) + '</div>';
            case 'text': return '<div>' + lbl + '<textarea class="sx-area" id="' + id + '" data-k="' + k + '" maxlength="' + max + '" placeholder="' + esc(ph || '') + '">' + esc(d[k]) + '</textarea>' + count + '</div>';
            case 'img': { const url = d.img ? G.images[d.img] : null; return '<div><span class="sx-lbl">' + esc(label) + '</span><div class="sx-img"><i' + (url ? ' style="background-image:url(\'' + esc(url) + '\')"' : '') + '></i><button type="button" class="sx-btn sx-btn-sm" data-upload="img">' + (d.img ? 'Changer la photo' : 'Choisir une photo') + '</button>' + (d.img ? '<button type="button" class="sx-btn sx-btn-sm sx-btn-danger" data-img-del>Retirer</button>' : '') + '</div><span class="sx-msg" data-img-msg>JPG, PNG ou WebP · 5 Mo max</span></div>'; }
            case 'bool': return '<label class="sx-check"><input type="checkbox" data-bool="' + k + '"' + (d[k] ? ' checked' : '') + '> ' + esc(label) + '</label>';
            case 'side': return seg(k, label, { left: 'Gauche', right: 'Droite' }, d[k]);
            case 'align': return seg(k, label, { left: 'À gauche', center: 'Centré' }, d[k]);
            case 'tone': return seg(k, label, G.tones, d[k]);
            case 'style': return seg(k, label, G.styles, d[k]);
            case 'format': return seg(k, label, G.formats, d[k]);
            case 'limit': return seg(k, label, { 4: '4', 8: '8' }, d[k]);
            case 'cat':
                if (!G.categories.length) return '<p class="sx-hint">Rangez d’abord vos produits par catégorie (fiche produit → Catégorie).</p>';
                return '<div>' + lbl + '<select class="sx-select" id="' + id + '" data-k="' + k + '"><option value="">Choisir une catégorie…</option>' + G.categories.map(c => '<option' + (c === d[k] ? ' selected' : '') + '>' + esc(c) + '</option>').join('') + '</select></div>';
            case 'items': {
                const di = DEF('promise').items || [];
                return '<div style="display:grid;gap:8px"><span class="sx-lbl" style="margin:0">' + esc(label) + '</span>' + d.items.map((it, n) => '<div class="sx-pair"><span class="sx-pair-hd">Engagement ' + (n + 1) + '</span>' + input(lf(k, n, 'title') + ' aria-label="Titre ' + (n + 1) + '"', it.title, (di[n] || {}).title, 40) + input(lf(k, n, 'text') + ' aria-label="Texte ' + (n + 1) + '"', it.text, (di[n] || {}).text, 80) + '</div>').join('') + '</div>';
            }
            case 'faq':
                return '<div style="display:grid;gap:8px"><span class="sx-lbl" style="margin:0">' + esc(label) + '</span>' + d.items.map((q, n) => '<div class="sx-pair"><span class="sx-pair-hd">Question ' + (n + 1) + delBtn(k, n, 'Supprimer la question ' + (n + 1)) + '</span>' + input(lf(k, n, 'q') + ' aria-label="Question ' + (n + 1) + '"', q.q, 'Ex. Livrez-vous hors de Conakry ?', 150) + '<textarea class="sx-area" style="min-height:70px" ' + lf(k, n, 'a') + ' maxlength="600" placeholder="Votre réponse" aria-label="Réponse ' + (n + 1) + '">' + esc(q.a) + '</textarea></div>').join('') + addBtn(k, d.items.length, 'Ajouter une question') + '</div>';
            case 'icons':
                return '<div style="display:grid;gap:8px"><span class="sx-lbl" style="margin:0">' + esc(label) + '</span>' + d.items.map((it, n) => '<div class="sx-pair"><span class="sx-pair-hd">Icône ' + (n + 1) + delBtn(k, n, 'Supprimer l’icône ' + (n + 1)) + '</span><div class="sx-iconpick" role="group" aria-label="Image de l’icône ' + (n + 1) + '">' + Object.entries(G.icons).map(([key, [l, path]]) => '<button type="button" data-icon="' + key + '" data-n="' + n + '" aria-pressed="' + (it.icon === key) + '" title="' + esc(l) + '" aria-label="' + esc(l) + '">' + svg(path, 17) + '</button>').join('') + '</div>' + input(lf(k, n, 'title') + ' aria-label="Titre de l’icône ' + (n + 1) + '"', it.title, 'Ex. Livraison express', 40) + input(lf(k, n, 'text') + ' aria-label="Sous-titre de l’icône ' + (n + 1) + '"', it.text, 'Ex. 24 h à Conakry', 80) + '</div>').join('') + addBtn(k, d.items.length, 'Ajouter une icône') + '</div>';
            case 'rows':
                return '<div style="display:grid;gap:8px"><span class="sx-lbl" style="margin:0">' + esc(label) + '</span>' + d.items.map((r, n) => '<div class="sx-pair"><div class="sx-row">' + input(lf(k, n, 'label') + ' aria-label="Ligne ' + (n + 1) + '"', r.label, 'Ex. Paiement à la livraison', 60) + delBtn(k, n, 'Supprimer la ligne ' + (n + 1)) + '</div><div style="display:flex;gap:14px;flex-wrap:wrap"><label class="sx-check"><input type="checkbox" ' + lf(k, n, 'us') + (r.us ? ' checked' : '') + '> Votre boutique</label><label class="sx-check"><input type="checkbox" ' + lf(k, n, 'them') + (r.them ? ' checked' : '') + '> Les autres</label></div></div>').join('') + addBtn(k, d.items.length, 'Ajouter une ligne') + '</div>';
            case 'people':
                return '<div style="display:grid;gap:8px"><span class="sx-lbl" style="margin:0">' + esc(label) + '</span><p class="sx-warn">N’ajoutez que de vrais avis de vos clients, avec leur accord. Les faux avis sont interdits sur Shopio et trompent vos clients.</p>' + d.items.map((p, n) => '<div class="sx-pair"><span class="sx-pair-hd">Avis ' + (n + 1) + delBtn(k, n, 'Supprimer l’avis ' + (n + 1)) + '</span>' + imgBox(p.img, 'data-upload="item" data-lk="' + k + '" data-n="' + n + '"') +
                    input(lf(k, n, 'name') + ' aria-label="Nom du client"', p.name, 'Prénom et initiale, ex. Fatoumata K.', 40) + input(lf(k, n, 'label') + ' aria-label="Précision"', p.label, 'Ex. Cliente fidèle', 40) +
                    (s.type === 'testimonials' || s.type === 'quotes' ? input(lf(k, n, 'title') + ' aria-label="Phrase forte"', p.title, 'Ex. Livraison ultra rapide !', 80) : '') +
                    '<textarea class="sx-area" style="min-height:70px" ' + lf(k, n, 'text') + ' maxlength="400" placeholder="Ce que le client a dit" aria-label="Avis">' + esc(p.text) + '</textarea>' +
                    (s.type === 'quotes' ? '<div class="sx-seg" role="group" aria-label="Note">' + [1, 2, 3, 4, 5].map(r => '<button type="button" data-rating="' + r + '" data-n="' + n + '" aria-pressed="' + (p.rating === r) + '">' + r + ' ★</button>').join('') + '</div>' : '') + '</div>').join('') + addBtn(k, d.items.length, 'Ajouter un avis') + '</div>';
            case 'slides':
                return '<div style="display:grid;gap:8px"><span class="sx-lbl" style="margin:0">' + esc(label) + '</span>' + d.items.map((sl, n) => '<div class="sx-pair"><span class="sx-pair-hd">Diapositive ' + (n + 1) + delBtn(k, n, 'Supprimer la diapositive ' + (n + 1)) + '</span>' + imgBox(sl.img, 'data-upload="item" data-lk="' + k + '" data-n="' + n + '"') +
                    input(lf(k, n, 'title') + ' aria-label="Titre"', sl.title, 'Titre (facultatif)', 80) + input(lf(k, n, 'text') + ' aria-label="Texte"', sl.text, 'Texte (facultatif)', 160) + input(lf(k, n, 'button') + ' aria-label="Bouton"', sl.button, 'Texte du bouton (facultatif)', 30) + input(lf(k, n, 'link') + ' type="url" aria-label="Lien"', sl.link, 'Lien (vide = vos produits)', 300) + '</div>').join('') +
                    (d.items.length < MAX ? '<button type="button" class="sx-btn sx-btn-sm" data-upload="new" data-lk="' + k + '" style="justify-self:start">+ Ajouter une photo</button>' : '') + '<span class="sx-msg" data-img-msg>JPG, PNG ou WebP · 5 Mo max · idéal 1600 px de large</span></div>';
            case 'videos':
                return '<div style="display:grid;gap:8px"><span class="sx-lbl" style="margin:0">' + esc(label) + '</span><span class="sx-hint">Collez le lien d’une vidéo YouTube, TikTok ou Facebook.</span>' + d.items.map((u, n) => '<div class="sx-row">' + input('data-lk="' + k + '" data-n="' + n + '" data-f="" type="url" aria-label="Lien de la vidéo ' + (n + 1) + '"', u, 'https://youtube.com/shorts/…', 300) + delBtn(k, n, 'Supprimer la vidéo ' + (n + 1)) + '</div>').join('') + addBtn(k, d.items.length, 'Ajouter une vidéo') + '</div>';
            case 'lines':
                return '<div style="display:grid;gap:8px"><span class="sx-lbl" style="margin:0">' + esc(label) + '</span>' + d.items.map((l, n) => '<div class="sx-row">' + input('data-lk="' + k + '" data-n="' + n + '" data-f="" aria-label="Message ' + (n + 1) + '"', l, 'Ex. Paiement à la livraison partout à Conakry', 80) + delBtn(k, n, 'Supprimer le message ' + (n + 1)) + '</div>').join('') + addBtn(k, d.items.length, 'Ajouter un message') + '</div>';
        }
        return '';
    }
    const newItem = kind => ({ faq: { q: '', a: '' }, icons: { icon: 'truck', title: '', text: '' }, rows: { label: '', us: true, them: false }, people: { img: null, name: '', label: '', title: '', text: '', rating: 5 }, videos: '', lines: '' })[kind];
    const kindOf = (type, k) => ((G.fields[type] || []).find(f => f[0] === k) || [])[1];

    const blank = t => {
        const d = {};
        (G.fields[t] || []).forEach(([k, kind]) => { d[k] = ({ img: null, bool: true, side: 'left', align: 'left', tone: 'accent', style: 'wave', format: 'portrait', limit: 4, items: [{ title: '', text: '' }, { title: '', text: '' }, { title: '', text: '' }] })[kind] ?? (['faq', 'icons', 'rows', 'people', 'slides', 'videos', 'lines'].includes(kind) ? [] : ''); });
        if (t === 'category' && G.categories.length) d.cat = G.categories[0];
        if (t === 'slideshow') d.auto = false;
        if (t === 'promo') d.tone = 'dark';
        if (t === 'trust') { d.om = false; d.momo = false; }
        // Une première ligne prête à remplir
        const first = { qa: 'faq', faq2: 'faq', icons: 'icons', compare: 'rows', quotes: 'people', fbposts: 'people', testimonials: 'people', videos: 'videos', reel: 'videos', marquee: 'lines' }[t];
        if (first) d.items = [newItem(first)];
        if (t === 'icons') d.items = [{ icon: 'truck', title: '', text: '' }, { icon: 'cash', title: '', text: '' }, { icon: 'return', title: '', text: '' }];
        return d;
    };
    return { esc, svg, IC, DEF, isBuiltin, MAX, summary, field, newItem, kindOf, blank };
};
</script>
