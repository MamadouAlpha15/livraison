{{-- Script de l'éditeur produit (inclus par vendeur/products/page.blade.php, variable $peData). --}}
<script>
(function () {
    'use strict';
    const G = @json($peData);
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const $ = id => document.getElementById(id);
    const X = window.SXFields(G, 'product');
    const { esc, svg, IC } = X;
    let C = JSON.parse(JSON.stringify(G.content)), Z = JSON.parse(JSON.stringify(G.design));
    let pageScope = G.ownPage ? 'product' : 'all', formScope = G.ownForm ? 'product' : 'shop';
    let dirty = false, saving = false, tab = 'sections', infoTab = 'galerie', openBuy = null, openSec = null, libTab = 'all', libQuery = '', packErr = {}, aiNote = '';
    const money = n => Math.round(Number(n) || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ' + G.currency;
    const P = G.product;

    /* ───────── Données : lecture et écriture par chemin (« C.hook », « Z.offer.title », « C.faq.2.q ») ───────── */
    const root = r => r === 'C' ? C : Z;
    const getp = path => { const [r, ...ks] = path.split('.'); return ks.reduce((o, k) => o == null ? o : o[k], root(r)); };
    const setp = (path, v) => {
        const [r, ...ks] = path.split('.'); let o = root(r);
        for (let i = 0; i < ks.length - 1; i++) { if (o[ks[i]] == null) o[ks[i]] = /^\d+$/.test(ks[i + 1]) ? [] : {}; o = o[ks[i]]; }
        o[ks[ks.length - 1]] = v;
    };

    /* ───────── État ───────── */
    function setState(t, cls) { const s = $('peState'); s.textContent = t; s.className = 'pe-state' + (cls ? ' ' + cls : ''); }
    function touch() { dirty = true; setState('Modifications non sauvegardées', 'is-dirty'); }
    window.addEventListener('beforeunload', e => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
    // Pour « Modifier la page » (éditeur intégré) : savoir s'il reste des changements, et quitter sans alerte
    window.PE = { dirty: () => dirty, leave() { dirty = false; } };

    /* ───────── Petits composants ───────── */
    const sw = (path, label, disabled) => '<label class="pe-sw" title="' + esc(label) + '"><input type="checkbox" data-b="' + path + '"' + (getp(path) ? ' checked' : '') + (disabled ? ' disabled' : '') + ' aria-label="' + esc(label) + '"><span></span></label>';
    const line = (title, sub, right) => '<div class="pe-line"><div><b>' + title + '</b>' + (sub ? '<small>' + sub + '</small>' : '') + '</div>' + (right || '') + '</div>';
    const txt = (path, label, max, ph) => { const id = 'b_' + path.replace(/\W/g, '_'); return '<div><label class="sx-lbl" for="' + id + '">' + esc(label) + '</label><input class="sx-input" id="' + id + '" data-b="' + path + '" maxlength="' + max + '" placeholder="' + esc(ph || '') + '" value="' + esc(getp(path) ?? '') + '"></div>'; };
    const area = (path, label, max, ph, hint) => { const id = 'b_' + path.replace(/\W/g, '_'); return '<div><label class="sx-lbl" for="' + id + '">' + esc(label) + '</label><textarea class="sx-area" id="' + id + '" data-b="' + path + '" maxlength="' + max + '" placeholder="' + esc(ph || '') + '">' + esc(getp(path) ?? '') + '</textarea>' + (hint ? '<span class="sx-hint">' + hint + '</span>' : '') + '</div>'; };
    const color = (path, label, def) => { const v = getp(path); return '<div><span class="sx-lbl">' + esc(label) + '</span><div class="pe-color"><input type="color" data-b="' + path + '" value="' + esc((v || def || '#FFFFFF').toLowerCase()) + '" aria-label="' + esc(label) + '"><code>' + esc(v || 'Par défaut') + '</code>' + (v ? '<button type="button" class="sx-btn sx-btn-sm" data-reset="' + path + '">Par défaut</button>' : '') + '</div></div>'; };
    const choice = (path, opts) => '<div class="pe-choice">' + Object.entries(opts).map(([v, l]) => '<button type="button" data-pick="' + path + '" data-v="' + v + '" aria-pressed="' + (String(getp(path)) === v) + '">' + esc(l) + '</button>').join('') + '</div>';
    const space = path => { const v = getp(path) || [0, 0]; return '<div class="pe-space"><span class="sx-lbl" style="margin:0">Espacement en plus</span>' + ['Haut', 'Bas'].map((l, k) => '<label class="pe-range"><span>' + l + '</span><input type="range" min="0" max="' + G.spaceMax + '" step="4" data-b="' + path + '.' + k + '" data-int value="' + (v[k] || 0) + '"><output>' + (v[k] || 0) + ' px</output></label>').join('') + '</div>'; };
    const listEd = (path, label, max, maxlen, ph) => { const arr = getp(path) || []; return '<div style="display:grid;gap:6px"><span class="sx-lbl" style="margin:0">' + esc(label) + '</span>' + arr.map((v, n) => '<div class="sx-row"><input class="sx-input" data-b="' + path + '.' + n + '" maxlength="' + maxlen + '" value="' + esc(v) + '" placeholder="' + esc(ph) + '" aria-label="' + esc(label) + ' ' + (n + 1) + '"><button type="button" class="sx-icon" data-ldel="' + path + '" data-n="' + n + '" aria-label="Supprimer">' + svg(IC.trash, 15) + '</button></div>').join('') + (arr.length < max ? '<button type="button" class="sx-btn sx-btn-sm" data-ladd="' + path + '" data-new="str" style="justify-self:start">+ Ajouter</button>' : '<span class="sx-hint">' + max + ' au maximum.</span>') + '</div>'; };
    const pairEd = (path, label, max, keys, labels, lens, phs) => { const arr = getp(path) || []; return '<div style="display:grid;gap:8px"><span class="sx-lbl" style="margin:0">' + esc(label) + '</span>' + arr.map((it, n) => '<div class="sx-pair"><span class="sx-pair-hd">' + esc(label.replace(/s$/, '')) + ' ' + (n + 1) + '<button type="button" class="sx-icon" style="width:28px;height:28px" data-ldel="' + path + '" data-n="' + n + '" aria-label="Supprimer">' + svg(IC.trash, 15) + '</button></span>' +
        keys.map((k, j) => lens[j] > 200 ? '<textarea class="sx-area" style="min-height:64px" data-b="' + path + '.' + n + '.' + k + '" maxlength="' + lens[j] + '" placeholder="' + esc(phs[j]) + '" aria-label="' + esc(labels[j]) + '">' + esc(it[k] || '') + '</textarea>' : '<input class="sx-input" data-b="' + path + '.' + n + '.' + k + '" maxlength="' + lens[j] + '" placeholder="' + esc(phs[j]) + '" value="' + esc(it[k] || '') + '" aria-label="' + esc(labels[j]) + '">').join('') + '</div>').join('') +
        (arr.length < max ? '<button type="button" class="sx-btn sx-btn-sm" data-ladd="' + path + '" data-new="' + keys.join(',') + '" style="justify-self:start">+ Ajouter</button>' : '<span class="sx-hint">' + max + ' au maximum.</span>') + '</div>'; };
    const goto = (t, label) => '<button type="button" class="sx-btn sx-btn-sm" data-goto="' + t + '" style="justify-self:start">' + esc(label) + ' →</button>';
    const itemHd = (name, tag, i, total, toggleHtml, kind, isOpen) => '<div class="sx-item-hd"><span class="sx-grip" aria-hidden="true">' + svg(IC.grip) + '</span><button type="button" class="sx-item-name" data-open="' + kind + '" aria-expanded="' + isOpen + '"><b>' + esc(name) + (tag ? '<span class="pe-tag">' + esc(tag) + '</span>' : '') + '</b></button>' +
        '<button type="button" class="sx-icon" data-move="-1" aria-label="Monter"' + (i === 0 ? ' disabled' : '') + '>' + svg(IC.up, 16) + '</button><button type="button" class="sx-icon" data-move="1" aria-label="Descendre"' + (i === total - 1 ? ' disabled' : '') + '>' + svg(IC.down, 16) + '</button>' + toggleHtml + '</div>';

    /* ═════════════ Onglet Infos ═════════════ */
    function paneInfos() {
        const sub = { galerie: 'Galerie', infos: 'Infos', variantes: 'Variantes', options: 'Options' };
        let body = '';
        if (infoTab === 'galerie') body = '<div class="pe-card"><h3>Images du produit</h3>' + (G.photos.length ? '<div class="pe-gal">' + G.photos.map(u => '<i style="background-image:url(\'' + esc(u) + '\')"></i>').join('') + '</div>' : '<p class="sx-hint">Pas encore de photo.</p>') + '<p class="sx-hint">La première image est la photo principale. Ajoutez, retirez ou changez l’ordre dans la fiche du produit.</p><a class="sx-btn sx-btn-sm" style="justify-self:start" href="' + esc(G.urls.edit) + '">Gérer les photos</a></div>' + photoPro();
        if (infoTab === 'infos') body = '<div class="pe-card"><h3>Infos du produit</h3><dl class="pe-kv"><dt>Nom</dt><dd>' + esc(P.name) + '</dd><dt>Prix</dt><dd>' + esc(money(P.price)) + (P.old ? ' <s style="color:var(--sx-ink3);font-weight:500">' + esc(money(P.old)) + '</s>' : '') + '</dd><dt>Stock</dt><dd>' + (P.stock === null ? 'Non suivi' : P.stock) + '</dd><dt>Catégorie</dt><dd>' + esc(P.category || '—') + '</dd></dl><a class="sx-btn sx-btn-sm" style="justify-self:start" href="' + esc(G.urls.edit) + '">Modifier le nom, le prix, le stock</a></div>';
        if (infoTab === 'variantes') body = '<div class="pe-card"><h3>Variantes du produit</h3><p class="sx-hint">' + (P.variants ? 'Ce produit a des variantes (taille, couleur…). Le client les choisit avant de commander.' : 'Pas de variante : le produit se vend tel quel.') + ' Taille, couleur, matière : les variantes se règlent dans la fiche du produit.</p><a class="sx-btn sx-btn-sm" style="justify-self:start" href="' + esc(G.urls.edit) + '">Gérer les variantes</a></div>';
        if (infoTab === 'options') body = '<div class="pe-card"><h3>Options et suppléments</h3><p class="sx-hint">Les suppléments payants (gravure, emballage cadeau, garantie…) ne sont pas encore disponibles sur Shopio. Le prix payé reste celui du produit et de ses packs.</p></div>';
        return '<div class="pe-sub" role="group" aria-label="Infos">' + Object.entries(sub).map(([k, l]) => '<button type="button" data-info="' + k + '" aria-pressed="' + (infoTab === k) + '">' + l + '</button>').join('') + '</div>' + body;
    }

    /* ───── Galerie → Photos pro (Shopio IA) : la vraie photo du produit, fond blanc studio ou mise en scène ───── */
    let ph = { style: 'studio', wish: '', busy: false, result: null, msg: '', cls: '' };
    function photoPro() {
        if (!G.urls.photoAi) return '';
        const styles = { studio: 'Fond blanc studio', scene: 'Mise en scène' };
        return '<div class="pe-card sx-aimg" style="margin-top:12px"><h3 style="margin:0;display:flex;align-items:center;gap:7px">' + svg('M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8zM19 3v4M17 5h4', 16) + ' Photos pro avec Shopio IA</h3>' +
            '<p class="sx-hint" style="margin:0">À partir de la vraie photo principale : le produit reste le même, Shopio IA soigne le fond et la lumière. Vous choisissez ensuite de l’ajouter ou non.</p>' +
            (!G.photos.length ? '<p class="sx-hint">Ajoutez d’abord une vraie photo du produit.</p>' :
            '<div class="sx-seg" role="group" aria-label="Style de la photo">' + Object.entries(styles).map(([k, l]) => '<button type="button" data-pstyle="' + k + '" aria-pressed="' + (ph.style === k) + '">' + l + '</button>').join('') + '</div>' +
            '<input class="sx-input" id="pePhWish" maxlength="300" value="' + esc(ph.wish) + '" placeholder="' + (ph.style === 'studio' ? 'Précision (facultatif) : ex. vue de face, ombre très légère' : 'Décor souhaité (facultatif) : ex. salon moderne, tissu wax, plage') + '" aria-label="Précision pour la photo">' +
            '<button type="button" class="sx-btn sx-btn-sm sx-ai-btn' + (ph.busy ? ' is-busy' : '') + '" id="pePhGo" style="justify-self:start"' + (ph.busy ? ' disabled' : '') + '>' + (ph.busy ? 'Création en cours…' : svg('M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8zM19 3v4M17 5h4', 16) + ' Créer la photo') + '</button>' +
            (ph.result ? '<div style="display:grid;gap:8px"><img src="' + esc(ph.result.url) + '" alt="Photo créée par Shopio IA" style="width:100%;max-width:320px;aspect-ratio:1;object-fit:cover;border-radius:14px;box-shadow:0 0 0 1px var(--sx-line)">' +
                '<div style="display:flex;gap:8px;flex-wrap:wrap"><button type="button" class="sx-btn sx-btn-sm" data-ph-add="1">★ Mettre en photo principale</button><button type="button" class="sx-btn sx-btn-sm" data-ph-add="0">+ Ajouter à la galerie</button><button type="button" class="sx-btn sx-btn-sm sx-btn-danger" data-ph-drop>Ne pas garder</button></div></div>' : '')) +
            '<span class="sx-msg ' + ph.cls + '" aria-live="polite">' + esc(ph.msg || (G.aiPro ? 'Environ 30 secondes à 1 minute. Vérifiez toujours que le produit est fidèle à l’original.' : 'Réservé au plan Pro.')) + '</span></div>';
    }
    async function phPost(url, body) {
        let r, j = {};
        try { r = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(body) }); j = await r.json().catch(() => ({})); }
        catch (x) { throw new Error('Connexion impossible : vérifiez votre internet puis réessayez.'); }
        if (!r.ok) throw new Error(j.error || (j.errors && Object.values(j.errors)[0][0]) || (r.status === 429 ? 'Trop d’essais : patientez une minute.' : 'La photo n’a pas pu être créée. Réessayez.'));
        return j;
    }
    async function phMake() {
        if (!G.aiPro) { ph.msg = 'Les photos Shopio IA sont réservées au plan Pro.'; ph.cls = 'is-err'; render(); return; }
        ph.busy = true; ph.result = null; ph.msg = 'Shopio IA regarde votre photo et crée la nouvelle… (30 secondes à 1 minute)'; ph.cls = ''; render();
        try { ph.result = await phPost(G.urls.photoAi, { style: ph.style, wish: ph.wish.trim() }); ph.msg = 'Photo créée : vérifiez que le produit est bien fidèle à l’original, puis choisissez.'; ph.cls = 'is-ok'; }
        catch (e) { ph.msg = e.message; ph.cls = 'is-err'; }
        ph.busy = false; render();
    }
    async function phAttach(main) {
        try {
            const j = await phPost(G.urls.photoAttach, { path: ph.result.path, main: main });
            G.photos = j.photos; ph.result = null; ph.msg = main ? 'Photo principale changée : c’est fait, rien d’autre à sauvegarder.' : 'Photo ajoutée à la galerie : c’est fait, rien d’autre à sauvegarder.'; ph.cls = 'is-ok';
            load();
        } catch (e) { ph.msg = e.message; ph.cls = 'is-err'; }
        render();
    }

    /* ═════════════ Onglet Sections ═════════════ */
    function buyBody(b, i) {
        const t = b.type; let h = '';
        if (t === 'rating') h = txt('Z.rating.mention', 'Mention (en gras)', 20, 'Automatique : Excellent, Très bien…') + '<p class="sx-hint">' + (G.rating ? 'Note et nombre d’avis : les vrais (' + String(G.rating.avg).replace('.', ',') + '/5 · ' + G.rating.count + ' avis). Ils ne se modifient pas.' : 'Pas encore d’avis : le bandeau reste caché jusqu’au premier avis client.') + '</p>';
        if (t === 'title' || t === 'price') h = '<p class="sx-hint">Se modifie dans l’onglet Infos (fiche du produit).</p>' + goto('infos', 'Onglet Infos');
        if (t === 'hook') h = area('C.hook', 'Accroche', 220, 'Ex. Rangement **ultra-pratique** et **confortable**.', 'Astuce : entourez un mot avec **mot** pour le mettre en gras.');
        if (t === 'benefits') h = listEd('C.benefits', 'Bénéfices', 8, 90, 'Ex. Poches compatibles et modulables');
        if (t === 'delivery') h = txt('C.promise', 'Texte au-dessus des étapes', 90, 'Ex. Commandez avant 18 h, livré aujourd’hui') + '<p class="sx-hint">Seulement si vous tenez vraiment ce délai. Vide : les 3 étapes restent affichées.</p>';
        if (t === 'offer') h = '<p class="sx-hint">Les packs et leur apparence se règlent dans l’onglet Offres.</p>' + goto('offres', 'Onglet Offres');
        if (t === 'form') h = '<p class="sx-hint">Le formulaire (champs, textes, couleurs, animation) se règle dans l’onglet Formulaire.</p>' + goto('formulaire', 'Onglet Formulaire');
        if (t === 'trust') h = txt('Z.trust.text', 'Phrase', 120, 'Commandez en toute confiance — paiement à la réception') + '<div style="display:flex;gap:14px;flex-wrap:wrap">' + [['cash', 'Espèces'], ['om', 'Orange Money'], ['momo', 'MTN MoMo']].map(([k, l]) => '<label class="sx-check"><input type="checkbox" data-b="Z.trust.' + k + '"' + (Z.trust[k] ? ' checked' : '') + '> ' + l + '</label>').join('') + '</div><p class="sx-hint">Cochez seulement les moyens de paiement que vous acceptez vraiment.</p>';
        return '<div class="sx-body">' + h + space('Z.buy.' + i + '.sp') + '</div>';
    }
    function secBody(s, i) {
        const t = s.type, d = s.d; let h = '';
        const title = (ph) => txt('Z.page.' + i + '.d.title', 'Titre', 80, ph);
        if (t === 'p_info') h = area('C.usage', 'Comment l’utiliser ?', 1500, '') + area('C.details', 'Description détaillée', 3000, 'Vide : la description du produit') + area('C.shipping', 'Livraison et garantie', 1500, 'Vide : texte standard (appel de confirmation, paiement à la livraison, suivi)');
        else if (t === 'p_highlight') h = txt('C.highlight.title', 'Titre (le 1er mot en couleur)', 70, 'Ex. Qualité tactique professionnelle') + area('C.highlight.text', 'Texte', 600, '') + txt('C.highlight.cta', 'Texte du bouton', 24, 'Je le veux') + '<p class="sx-hint">Avec 3 photos du produit, inclinées. S’affiche si le titre est rempli.</p>';
        else if (t === 'p_reviews') h = title(G.defaults.product.p_reviews.title) + '<p class="sx-hint">Les 3 derniers avis laissés sur Shopio par vos clients livrés. Rien n’est inventé.</p>';
        else if (t === 'p_band') h = txt('Z.band.text', 'Texte affiché', 40, 'Vide : le nom de la boutique') + '<div class="pe-grid2">' + color('Z.band.bg', 'Fond', '#2F5D1E') + color('Z.band.ink', 'Texte', '#FFFFFF') + '</div>';
        else if (t === 'p_story') h = txt('C.story.title', 'Titre', 80, 'Ex. Avant / Après : organisez votre équipement') + txt('C.story.kicker', 'Sous-titre', 140, '') + area('C.story.text', 'Texte', 1200, '') + '<p class="sx-hint">Avec la photo principale. S’affiche si le titre est rempli.</p>';
        else if (t === 'p_audience') h = txt('C.audience.title', 'Titre', 60, 'Ex. C’est pour qui ?') + txt('C.audience.kicker', 'Sous-titre', 80, '') + area('C.audience.text', 'Texte', 1200, '') + '<p class="sx-hint">Avec la 2e photo. S’affiche si le titre est rempli.</p>';
        else if (t === 'p_results') h = title(G.defaults.product.p_results.title) + '<p class="sx-hint">Part d’avis 4–5 étoiles, note moyenne et commandes livrées : calculées sur vos vraies données. Elles ne se saisissent pas.</p>';
        else if (t === 'p_love') h = txt('C.love.title', 'Titre', 80, 'Pourquoi vous allez l’adorer') + txt('C.love.kicker', 'Sous-titre', 120, '') + pairEd('C.love.items', 'Raisons', 6, ['title', 'text'], ['Titre', 'Explication'], [60, 160], ['Ex. Durabilité garantie', 'Ex. Coutures renforcées']);
        else if (t === 'p_compare') h = txt('C.compare.title', 'Titre', 80, 'Pourquoi nous choisir ?') + listEd('C.compare.rows', 'Points garantis', 8, 60, 'Ex. Qualité premium garantie') + '<p class="sx-hint">Seulement ce que vous garantissez vraiment à chaque commande.</p>';
        else if (t === 'p_faq') h = title(G.defaults.product.p_faq.title) + pairEd('C.faq', 'Questions', 10, ['q', 'a'], ['Question', 'Réponse'], [140, 600], ['Ex. Quel est le délai de livraison ?', 'Votre réponse']) + '<p class="sx-hint">Vide : les questions habituelles (commande, paiement, livraison).</p>';
        else if (t === 'p_related') h = title(G.defaults.product.p_related.title);
        else h = (X.aiWritable(t) ? X.aiRow() : '') + (G.fields[t] || []).map(f => X.field(s, i, f)).join('');
        return '<div class="sx-body">' + h + space('Z.page.' + i + '.sp') + (X.isBuiltin(t) ? '' : '<button type="button" class="sx-btn sx-btn-sm sx-btn-danger" data-sdel style="justify-self:start">' + svg(IC.trash, 15) + ' Supprimer la section</button>') + '</div>';
    }
    function paneSections() {
        const buy = Z.buy.map((b, i) => {
            const meta = G.buy[b.type], o = openBuy === b.type;
            const tog = meta[1] ? sw('Z.buy.' + i + '.on', 'Afficher « ' + meta[0] + ' »') : '';
            return '<li class="sx-item' + (o ? ' is-open' : '') + (b.on ? '' : ' is-off') + '" data-bi="' + i + '" draggable="true">' + itemHd(meta[0], meta[2] ? 'onglet ' + meta[2][0].toUpperCase() + meta[2].slice(1) : '', i, Z.buy.length, tog, 'buy', o) + (o ? buyBody(b, i) : '') + '</li>';
        }).join('');
        const page = Z.page.map((s, i) => {
            const o = openSec === s.id, name = G.types[s.type] ? G.types[s.type][0] : s.type;
            return '<li class="sx-item' + (o ? ' is-open' : '') + (s.on ? '' : ' is-off') + '" data-i="' + i + '" draggable="true">' + itemHd(name, X.isBuiltin(s.type) ? '' : 'ajoutée', i, Z.page.length, sw('Z.page.' + i + '.on', 'Afficher « ' + name + ' »'), 'page', o) + (o ? secBody(s, i) : '') + '</li>';
        }).join('');
        const custom = Z.page.filter(s => !X.isBuiltin(s.type)).length;
        return '<div class="pe-ai"><p id="peAiMsg">Shopio IA rédige toute la page (accroche, bénéfices, sections, questions) et les sections que vous avez ajoutées, d’après le nom, la description et les photos. Vous relisez, puis vous sauvegardez.</p><button type="button" class="sx-btn sx-btn-sm" id="peAi">✨ Régénérer avec Shopio IA</button>' +
                '<input class="sx-input" id="peAiNote" maxlength="500" style="flex:1 1 100%" placeholder="Consigne (facultatif) : ex. plus court, parlez de la Tabaski, ton plus luxe…" value="' + esc(aiNote) + '" aria-label="Consigne pour Shopio IA"></div>' +
            '<div class="pe-card"><h3>Réglages</h3>' + line('Panier flottant', 'La barre « Commander » qui suit le client en bas de l’écran', sw('Z.sticky', 'Panier flottant')) + '</div>' +
            '<div class="pe-card"><h3>Bloc d’achat</h3><p class="sx-hint">À droite des photos sur ordinateur, sous les photos sur mobile. Glissez pour changer l’ordre.</p><ol class="sx-list" id="peBuy">' + buy + '</ol></div>' +
            '<div class="pe-card"><h3>Sections de la page</h3><p class="sx-hint">Pleine largeur, sous le bloc d’achat.</p>' +
                '<div><span class="sx-lbl">Cette mise en page s’applique à</span><div class="sx-seg" role="group" aria-label="Portée des sections"><button type="button" data-pscope="all" aria-pressed="' + (pageScope === 'all') + '">Tous les produits</button><button type="button" data-pscope="product" aria-pressed="' + (pageScope === 'product') + '">Ce produit seulement</button></div></div>' +
                '<ol class="sx-list" id="pePage">' + page + '</ol>' +
                '<div class="sx-add"><b style="font-size:14px">Ajouter une section</b><input class="sx-input" type="search" id="peSearch" placeholder="Rechercher une section…" value="' + esc(libQuery) + '" aria-label="Rechercher une section"><div class="sx-tabs" role="group" aria-label="Catégories de sections">' +
                    [['all', 'Tout']].concat(Object.entries(G.library).map(([k, [l]]) => [k, l])).map(([k, l]) => '<button type="button" data-lib="' + k + '" aria-pressed="' + (libTab === k) + '">' + esc(l) + '</button>').join('') + '</div><div class="sx-add-grid" id="peLib">' + libGrid(custom) + '</div></div>' +
            '</div>';
    }
    const norm = v => String(v).toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    function libGrid(custom) {
        if (custom >= G.limits.custom) return '<p class="sx-hint" style="grid-column:1/-1">Maximum ' + G.limits.custom + ' sections ajoutées.</p>';
        const types = (libTab === 'all' ? G.addable : G.library[libTab][1]).filter(t => !libQuery || norm(G.types[t][0] + ' ' + G.types[t][1]).includes(norm(libQuery)));
        return types.length ? types.map(t => '<button type="button" data-add="' + t + '"><b>+ ' + esc(G.types[t][0]) + '</b><span>' + esc(G.types[t][1]) + '</span></button>').join('') : '<p class="sx-hint" style="grid-column:1/-1">Aucune section ne correspond.</p>';
    }

    /* ═════════════ Onglet Couleurs ═════════════ */
    function paneColors() {
        const font = (path, label) => '<div><label class="sx-lbl" for="f_' + path + '">' + label + '</label><select class="sx-select" id="f_' + path + '" data-b="' + path + '">' + Object.entries(G.fonts).map(([v, l]) => '<option value="' + esc(v) + '"' + (getp(path) === v ? ' selected' : '') + '>' + esc(l) + '</option>').join('') + '</select></div>';
        return '<div class="pe-card"><h3>Couleur du produit</h3><p class="sx-hint">Prix, boutons et packs de cette page. Par défaut : la couleur de la boutique.</p>' + color('Z.colors.accent', 'Couleur', '#2F5D1E') + '</div>' +
            '<div class="pe-card"><h3>Fond de la page produit</h3>' + color('Z.colors.bg', 'Fond', '#FFFFFF') + '</div>' +
            '<div class="pe-card"><h3>Pied de page</h3><p class="sx-hint">Seulement sur cette page produit. Sans couleur choisie, il garde celles de la boutique.</p><div class="pe-grid2">' + color('Z.colors.foot_bg', 'Fond', '#6F6B66') + color('Z.colors.foot_ink', 'Texte', '#FFFFFF') + '</div></div>' +
            '<div class="pe-card"><h3>Polices</h3><div class="pe-grid2">' + font('Z.colors.font_title', 'Titres') + font('Z.colors.font_body', 'Corps de texte') + '</div></div>';
    }

    /* ═════════════ Onglet Formulaire ═════════════ */
    function paneForm() {
        const F = Z.form;
        const field = (name, tags, right) => '<div class="pe-line" style="padding:8px 0;border-top:1px solid var(--sx-line)"><div><b>' + name + '</b><small>' + tags + '</small></div>' + right + '</div>';
        return '<div class="pe-card"><h3>Formulaire de commande</h3><p class="sx-hint">Personnalisez le formulaire de toute la boutique, ou celui de ce produit seulement.</p><div class="sx-seg" role="group" aria-label="Portée du formulaire"><button type="button" data-fscope="shop" aria-pressed="' + (formScope === 'shop') + '">Toute la boutique</button><button type="button" data-fscope="product" aria-pressed="' + (formScope === 'product') + '">Ce produit</button></div>' + (formScope === 'product' ? '<p class="sx-hint">Ce produit a son propre formulaire : les changements ne touchent que lui.</p>' : '<p class="sx-hint">Les changements s’appliquent à tous les produits qui utilisent le formulaire de la boutique.</p>') + '</div>' +
            '<div class="pe-card"><h3>Affichage</h3>' + line('Formulaire intégré', 'Le formulaire est sur la page produit. Sinon, le bouton ouvre le panier.', sw('Z.form.integrated', 'Formulaire intégré')) + '</div>' +
            '<div class="pe-card"><h3>Animation du bouton</h3>' + choice('Z.form.anim', G.anims) + (F.anim !== 'none' ? '<span class="sx-lbl" style="margin:0">Intensité</span><div class="pe-choice" style="grid-template-columns:repeat(3,minmax(0,1fr))">' + Object.entries(G.intensities).map(([v, l]) => '<button type="button" data-pick="Z.form.intensity" data-v="' + v + '" aria-pressed="' + (F.intensity === v) + '">' + l + '</button>').join('') + '</div>' : '') + '</div>' +
            '<div class="pe-card"><h3>Style du formulaire</h3><div class="pe-grid2">' + color('Z.form.bg', 'Fond', '#FFFFFF') + color('Z.form.border', 'Bordure', '#111111') + color('Z.form.title_c', 'Titre', '#111111') + color('Z.form.label_c', 'Labels', '#111111') + '</div></div>' +
            '<div class="pe-card"><h3>Textes</h3>' + txt('Z.form.title', 'Titre du formulaire', 60, 'Finaliser la commande') + txt('Z.form.button', 'Texte du bouton', 40, 'Confirmer la commande') + txt('Z.form.note', 'Message sous le bouton', 100, '💵 Paiement à la livraison') + '</div>' +
            '<div class="pe-card"><h3>Champs du formulaire</h3><div>' +
                field('Code promo', 'Bientôt disponible', '<label class="pe-sw"><input type="checkbox" disabled aria-label="Code promo"><span></span></label>') +
                field('Nom', 'Texte · requis pour les visiteurs', '<label class="pe-sw"><input type="checkbox" checked disabled aria-label="Nom"><span></span></label>') +
                field('Téléphone', 'Téléphone · requis', '<label class="pe-sw"><input type="checkbox" checked disabled aria-label="Téléphone"><span></span></label>') +
                field('Commune', 'Zone de livraison · requis', '<label class="pe-sw"><input type="checkbox" checked disabled aria-label="Commune"><span></span></label>') +
                field('Adresse', 'Quartier, rue, repère' + (F.address ? ' · <label class="sx-check" style="display:inline-flex;font-size:12px"><input type="checkbox" data-b="Z.form.address_req"' + (F.address_req ? ' checked' : '') + '> obligatoire</label>' : ''), sw('Z.form.address', 'Adresse')) +
            '</div></div>';
    }

    /* ═════════════ Onglet Offres ═════════════ */
    function paneOffers() {
        const O = Z.offer, unit = G.unit;
        const tiers = C.packs.map((k, n) => {
            const q = +k.qty || 0, pr = +String(k.price || '').replace(/\D/g, '') || 0, full = q * unit, save = full - pr;
            const msg = packErr['packs.' + n] || (!q || !pr ? '' : save <= 0 ? 'Trop cher : ' + q + ' à l’unité coûtent déjà ' + money(full) + '.' : 'Le client économise ' + money(save) + ' (−' + Math.round(save / full * 100) + ' %), soit ' + money(pr / q) + ' par produit.');
            const bad = !!packErr['packs.' + n] || (q && pr && save <= 0);
            return '<div class="pe-tier"><div><label class="sx-lbl" for="pq' + n + '">Quantité</label><select class="sx-select" id="pq' + n + '" data-b="C.packs.' + n + '.qty" data-int><option value="">—</option>' + Array.from({ length: 9 }, (_, j) => j + 2).map(v => '<option value="' + v + '"' + (q === v ? ' selected' : '') + '>' + v + ' produits</option>').join('') + '</select></div>' +
                '<div><label class="sx-lbl" for="pp' + n + '">Prix total (' + esc(G.currency) + ')</label><input class="sx-input" id="pp' + n + '" inputmode="numeric" data-b="C.packs.' + n + '.price" data-int data-pack value="' + esc(k.price || '') + '" placeholder="Ex. ' + Math.round(unit * 2 * .9) + '"></div>' +
                '<div><label class="sx-lbl" for="pl' + n + '">Étiquette</label><input class="sx-input" id="pl' + n + '" maxlength="20" data-b="C.packs.' + n + '.label" value="' + esc(k.label || '') + '" placeholder="' + ['Populaire', 'Meilleure offre', ''][n] + '"></div>' +
                '<button type="button" class="sx-icon" data-ldel="C.packs" data-n="' + n + '" aria-label="Supprimer le palier">' + svg(IC.trash, 15) + '</button>' +
                '<label class="pe-pre"><input type="radio" name="pePre" data-pre="' + q + '"' + (O.preselect === q && q ? ' checked' : '') + (q ? '' : ' disabled') + '> Préchoisi</label><output class="' + (bad ? 'is-bad' : '') + '" data-pout="' + n + '">' + esc(msg) + '</output></div>';
        }).join('');
        return '<div class="pe-card"><h3>Offres et packs</h3>' + line('Afficher sur la page produit', 'Le bloc « Pack et économie » au-dessus de la quantité', sw('Z.offer.show', 'Afficher les packs')) + '</div>' +
            '<div class="pe-card"><h3>Apparence</h3>' + txt('Z.offer.title', 'Titre du bloc', 50, 'Pack et économie') + txt('Z.offer.subtitle', 'Sous-titre (facultatif)', 80, 'Plus vous achetez, plus vous économisez') +
                line('Afficher l’image du produit', 'La photo à côté de chaque pack', sw('Z.offer.image', 'Image du produit')) + line('Cacher le prix barré', 'N’affiche pas le prix d’origine sous le prix réduit', sw('Z.offer.hide_strike', 'Cacher le prix barré')) + line('Afficher le prix par unité', 'Ex. « soit 225 000 GNF / unité »', sw('Z.offer.unit', 'Prix par unité')) + color('Z.offer.frame', 'Couleur du cadre (pack choisi)', '#2F5D1E') + '</div>' +
            '<div class="pe-card"><h3>Paliers de remise</h3><p class="sx-hint">Le prix d’un pack est le prix <b>total</b>, plus bas que l’achat à l’unité. Il s’applique aussi dans le panier et à la commande.</p>' +
                '<div class="pe-tier" style="grid-template-columns:minmax(0,1fr) auto"><div><b>1 produit</b><span class="sx-hint" style="display:block">' + esc(money(unit)) + ' · prix de la fiche produit</span></div><label class="pe-pre"><input type="radio" name="pePre" data-pre="1"' + (O.preselect === 1 ? ' checked' : '') + '> Préchoisi</label></div>' +
                tiers + (C.packs.length < G.limits.packs ? '<button type="button" class="sx-btn sx-btn-sm" data-ladd="C.packs" data-new="qty,price,label" style="justify-self:start">+ Ajouter un palier</button>' : '') + '</div>';
    }

    /* ───────── Rendu ───────── */
    const PANES = { infos: paneInfos, sections: paneSections, couleurs: paneColors, formulaire: paneForm, offres: paneOffers };
    function render() {
        const pane = document.querySelector('[data-pane="' + tab + '"]');
        const y = window.scrollY;
        pane.innerHTML = PANES[tab]();
        $('peCount').textContent = Z.buy.length + Z.page.length;
        window.scrollTo(0, y);
    }
    function showTab(t) {
        tab = t;
        document.querySelectorAll('[data-tab]').forEach(b => b.setAttribute('aria-selected', b.dataset.tab === t ? 'true' : 'false'));
        document.querySelectorAll('[data-pane]').forEach(p => { p.hidden = p.dataset.pane !== t; });
        render();
    }
    document.querySelectorAll('[data-tab]').forEach(b => b.addEventListener('click', () => showTab(b.dataset.tab)));

    /* ───────── Saisie : champs liés (data-b) ───────── */
    const panes = document.querySelector('.pe-layout > div');
    panes.addEventListener('input', e => {
        const el = e.target;
        if (el.dataset.b) {
            let v = el.type === 'checkbox' ? el.checked : el.value;
            if (el.dataset.int !== undefined) v = el.tagName === 'SELECT' ? (parseInt(v, 10) || '') : (el.type === 'range' ? +v : String(v).replace(/\D/g, ''));
            if (el.type === 'color') v = el.value.toUpperCase();
            setp(el.dataset.b, v); touch();
            if (el.type === 'range') el.nextElementSibling.textContent = v + ' px';
            if (el.type === 'color') { const code = el.parentNode.querySelector('code'); if (code) code.textContent = v; }
            if (el.dataset.pack !== undefined) { packErr = {}; }
            return;
        }
        // Champs des sections ajoutées (même logique que Boutique → Sections)
        const li = el.closest('[data-i]'); if (!li) return;
        const s = Z.page[+li.dataset.i], d = s.d;
        if (el.dataset.k) d[el.dataset.k] = el.value;
        else if (el.dataset.lk && el.type !== 'checkbox') { const arr = d[el.dataset.lk], n = +el.dataset.n; if (el.dataset.f) arr[n][el.dataset.f] = el.value; else arr[n] = el.value; }
        else return;
        touch();
    });
    panes.addEventListener('change', e => {
        const el = e.target;
        if (el.dataset.b && (el.type === 'checkbox' || el.tagName === 'SELECT')) {
            let v = el.type === 'checkbox' ? el.checked : el.value;
            if (el.dataset.int !== undefined) v = parseInt(v, 10) || '';
            setp(el.dataset.b, v); touch();
            // Ce qui change l'affichage de l'onglet : on le redessine
            if (/^(Z\.form\.address|Z\.buy\.\d+\.on|Z\.page\.\d+\.on|C\.packs\.\d+\.qty)$/.test(el.dataset.b)) { packErr = {}; render(); }
            return;
        }
        if (el.dataset.pre !== undefined) { Z.offer.preselect = +el.dataset.pre; touch(); return; }
        const li = el.closest('[data-i]'); if (!li) return;
        const d = Z.page[+li.dataset.i].d;
        if (el.tagName === 'SELECT' && el.dataset.k) d[el.dataset.k] = el.value;
        else if (el.dataset.bool) d[el.dataset.bool] = el.checked;
        else if (el.type === 'checkbox' && el.dataset.lk) d[el.dataset.lk][+el.dataset.n][el.dataset.f] = el.checked;
        else return;
        touch();
    });
    panes.addEventListener('focusout', e => { if (e.target.dataset && e.target.dataset.pack !== undefined && tab === 'offres') render(); });

    /* ───────── Clics ───────── */
    panes.addEventListener('click', e => {
        const t = e.target;
        const g = t.closest('[data-goto]'); if (g) { showTab(g.dataset.goto); return; }
        const inf = t.closest('[data-info]'); if (inf) { infoTab = inf.dataset.info; render(); return; }
        const pst = t.closest('[data-pstyle]'); if (pst) { ph.style = pst.dataset.pstyle; render(); return; }
        if (t.closest('#pePhGo')) { if (!ph.busy) phMake(); return; }
        const pha = t.closest('[data-ph-add]'); if (pha && ph.result) { phAttach(pha.dataset.phAdd === '1'); return; }
        if (t.closest('[data-ph-drop]')) { ph.result = null; ph.msg = 'Photo écartée.'; ph.cls = ''; render(); return; }
        const rs = t.closest('[data-reset]'); if (rs) { setp(rs.dataset.reset, null); touch(); render(); return; }
        const pk = t.closest('[data-pick]'); if (pk) { setp(pk.dataset.pick, pk.dataset.v); touch(); render(); return; }
        const ps = t.closest('[data-pscope]'); if (ps) { pageScope = ps.dataset.pscope; touch(); render(); return; }
        const fs = t.closest('[data-fscope]'); if (fs) { formScope = fs.dataset.fscope; touch(); render(); return; }
        const la = t.closest('[data-ladd]');
        if (la) { const arr = getp(la.dataset.ladd) || []; const keys = la.dataset.new; arr.push(keys === 'str' ? '' : Object.fromEntries(keys.split(',').map(k => [k, '']))); setp(la.dataset.ladd, arr); touch(); render(); return; }
        const ld = t.closest('[data-ldel]');
        if (ld) { const arr = getp(ld.dataset.ldel); arr.splice(+ld.dataset.n, 1); if (ld.dataset.ldel === 'C.packs') packErr = {}; touch(); render(); return; }
        const lt = t.closest('[data-lib]'); if (lt) { libTab = lt.dataset.lib; render(); return; }
        if (t.id === 'peAi' || t.closest('#peAi')) { aiFill(); return; }
        const sai = t.closest('[data-sai]');
        if (sai) { const sli = sai.closest('[data-i]'); if (sli) aiSection(+sli.dataset.i, sai); return; }
        const add = t.closest('[data-add]');
        if (add) {
            const s = { id: 's' + Math.random().toString(36).slice(2, 10).padEnd(8, '0'), type: add.dataset.add, on: true, sp: [0, 0], d: X.blank(add.dataset.add) };
            const faq = Z.page.findIndex(x => x.type === 'p_faq');
            Z.page.splice(faq === -1 ? Z.page.length : faq, 0, s);
            openSec = s.id; touch(); render();
            const li = document.querySelector('#pePage .is-open'); if (li) li.scrollIntoView({ block: 'center', behavior: 'smooth' });
            return;
        }
        // Bloc d'achat
        const bli = t.closest('[data-bi]');
        if (bli) {
            const i = +bli.dataset.bi, b = Z.buy[i];
            if (t.closest('[data-open]')) { openBuy = openBuy === b.type ? null : b.type; render(); return; }
            const mv = t.closest('[data-move]'); if (mv && !mv.disabled) { const j = i + +mv.dataset.move; [Z.buy[i], Z.buy[j]] = [Z.buy[j], Z.buy[i]]; touch(); render(); return; }
            return;
        }
        // Sections de la page
        const li = t.closest('[data-i]'); if (!li) return;
        const i = +li.dataset.i, s = Z.page[i], d = s.d;
        if (t.closest('[data-open]')) { openSec = openSec === s.id ? null : s.id; render(); return; }
        const mv = t.closest('[data-move]'); if (mv && !mv.disabled) { const j = i + +mv.dataset.move; [Z.page[i], Z.page[j]] = [Z.page[j], Z.page[i]]; touch(); render(); return; }
        if (t.closest('[data-sdel]')) { if (confirm('Supprimer la section « ' + G.types[s.type][0] + ' » ?')) { Z.page.splice(i, 1); openSec = null; touch(); render(); } return; }
        const sg = t.closest('[data-seg]'); if (sg) { d[sg.dataset.seg] = sg.dataset.seg === 'limit' ? +sg.dataset.v : sg.dataset.v; touch(); render(); return; }
        if (t.closest('[data-list-add]')) { const k = t.closest('[data-list-add]').dataset.listAdd; d[k].push(JSON.parse(JSON.stringify(X.newItem(X.kindOf(s.type, k))))); touch(); render(); return; }
        const dl = t.closest('[data-list-del]'); if (dl) { d[dl.dataset.listDel].splice(+dl.dataset.n, 1); touch(); render(); return; }
        const ic = t.closest('[data-icon]'); if (ic) { d.items[+ic.dataset.n].icon = ic.dataset.icon; touch(); render(); return; }
        const rt = t.closest('[data-rating]'); if (rt) { d.items[+rt.dataset.n].rating = +rt.dataset.rating; touch(); render(); return; }
        const aim = t.closest('[data-aimg]'); if (aim) { X.aiPhoto(s, aim, () => { openSec = s.id; touch(); render(); }); return; }
        const up = t.closest('[data-upload]'); if (up) { uploadFor = { s: s, mode: up.dataset.upload, k: up.dataset.lk, n: up.dataset.n === undefined ? null : +up.dataset.n }; $('peFile').click(); return; }
        if (t.closest('[data-img-del]')) { d.img = null; touch(); render(); }
    });
    panes.addEventListener('input', e => { if (e.target.id === 'peAiNote') { aiNote = e.target.value; return; } if (e.target.id === 'pePhWish') { ph.wish = e.target.value; return; } if (e.target.id === 'peSearch') { libQuery = e.target.value.trim(); if (libQuery) libTab = 'all'; const g = $('peLib'); if (g) g.innerHTML = libGrid(Z.page.filter(s => !X.isBuiltin(s.type)).length); } });

    /* Glisser-déposer (bloc d'achat et sections) */
    let drag = null;
    panes.addEventListener('dragstart', e => { const li = e.target.closest('[data-bi],[data-i]'); if (!li || e.target.closest('input,textarea,select')) { e.preventDefault(); return; } drag = li.dataset.bi !== undefined ? ['buy', +li.dataset.bi] : ['page', +li.dataset.i]; li.classList.add('is-drag'); e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', '1'); });
    panes.addEventListener('dragover', e => { const li = e.target.closest(drag && drag[0] === 'buy' ? '[data-bi]' : '[data-i]'); if (!drag || !li) return; e.preventDefault(); document.querySelectorAll('.sx-item.is-over').forEach(x => x.classList.remove('is-over')); li.classList.add('is-over'); });
    panes.addEventListener('drop', e => { const li = e.target.closest(drag && drag[0] === 'buy' ? '[data-bi]' : '[data-i]'); if (!drag || !li) return; e.preventDefault(); const arr = drag[0] === 'buy' ? Z.buy : Z.page, to = +(li.dataset.bi ?? li.dataset.i); if (to !== drag[1]) { const [m] = arr.splice(drag[1], 1); arr.splice(to, 0, m); touch(); } drag = null; render(); });
    panes.addEventListener('dragend', () => { drag = null; document.querySelectorAll('.is-drag,.is-over').forEach(x => x.classList.remove('is-drag', 'is-over')); });

    /* Photos des sections ajoutées */
    let uploadFor = null;
    $('peFile').addEventListener('change', async e => {
        const f = e.target.files[0]; e.target.value = '';
        const u = uploadFor; if (!f || !u) return;
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(f.type)) return setState('Format refusé : JPG, PNG ou WebP.', 'is-err');
        if (f.size > 5 * 1048576) return setState('Photo trop lourde : 5 Mo maximum.', 'is-err');
        setState('Envoi de la photo…');
        const fd = new FormData(); fd.append('file', f); fd.append('_token', CSRF);
        try {
            const r = await fetch(G.urls.upload, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } });
            const j = await r.json();
            if (!r.ok) return setState((j.errors && j.errors.file && j.errors.file[0]) || j.message || 'Photo refusée.', 'is-err');
            G.images[j.path] = j.url;
            if (u.mode === 'img') u.s.d.img = j.path;
            else if (u.mode === 'new') u.s.d[u.k].push({ img: j.path, title: '', text: '', button: '', link: '' });
            else u.s.d[u.k][u.n].img = j.path;
            touch(); render();
        } catch (x) { setState('Envoi impossible : vérifiez la connexion.', 'is-err'); }
    });

    /* ───────── Shopio IA ───────── */
    async function aiFill() {
        const btn = $('peAi'), msg = $('peAiMsg');
        const say = (t, c) => { if (msg) { msg.textContent = t; msg.className = c || ''; } };
        const filled = C.hook || C.benefits.length || C.highlight.title || C.story.title || C.love.items.length || C.faq.length;
        if (filled && !confirm('Shopio IA va remplacer les textes de la page (les packs déjà remplis sont gardés). Continuer ?')) return;
        btn.disabled = true; say('Shopio IA rédige votre page… (environ 30 secondes)');
        try {
            const r = await fetch(G.urls.ai, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify({ instructions: aiNote }) });
            const j = await r.json().catch(() => ({}));
            if (!r.ok || !j.landing) { say(j.error || (r.status === 429 ? 'Trop d’essais : patientez une minute.' : 'Shopio IA n’a pas pu rédiger la page. Réessayez.'), 'is-err'); return; }
            const L = j.landing;
            ['hook', 'promise', 'usage', 'details', 'shipping'].forEach(k => { if (L[k] !== undefined) C[k] = L[k] || ''; });
            C.benefits = L.benefits || [];
            ['highlight', 'story', 'audience'].forEach(b => { C[b] = Object.assign({}, C[b], L[b] || {}); });
            C.love = { title: (L.love || {}).title || '', kicker: (L.love || {}).kicker || '', items: (L.love || {}).items || [] };
            C.compare = { title: (L.compare || {}).title || '', rows: (L.compare || {}).rows || [] };
            C.faq = L.faq || [];
            const packsEmpty = !C.packs.some(k => k.qty);
            if (packsEmpty && (j.packs || []).length) C.packs = j.packs.map(k => ({ qty: k.qty, price: k.price, label: k.label || '' }));
            touch(); render();
            // Puis les sections ajoutées à la page : à chaque régénération, l'IA les rédige aussi
            const added = Z.page.filter(s => !X.isBuiltin(s.type) && X.aiWritable(s.type));
            let note = '';
            if (added.length) {
                const m1 = $('peAiMsg'); if (m1) { m1.textContent = 'Page rédigée. Shopio IA rédige maintenant vos ' + added.length + ' section' + (added.length > 1 ? 's' : '') + ' ajoutée' + (added.length > 1 ? 's' : '') + '…'; m1.className = ''; }
                try { applySections(await X.aiWrite(G.urls.sectionsAi, added, { instructions: aiNote })); render(); }
                catch (err) { note = ' Sections ajoutées non rédigées : ' + err.message; }
            }
            const m = $('peAiMsg'); if (m) { m.textContent = 'Page rédigée : relisez, corrigez si besoin, puis touchez « Sauvegarder ».' + note; m.className = note ? 'is-err' : 'is-ok'; }
        } catch (x) { say('Connexion impossible : vérifiez votre internet puis réessayez.', 'is-err'); }
        finally { const b = $('peAi'); if (b) b.disabled = false; }
    }

    /** Contenu rédigé par Shopio IA posé dans les sections (photos, liens et réglages déjà gardés par le serveur). */
    function applySections(map) {
        Object.entries(map || {}).forEach(([id, d]) => { const s = Z.page.find(x => x.id === id); if (s && d && typeof d === 'object') s.d = d; });
        touch();
    }
    /** « ✨ Rédiger avec Shopio IA » dans une section. */
    async function aiSection(i, btn) {
        const s = Z.page[i]; if (!s) return;
        const msg = btn.parentNode.querySelector('[data-sai-msg]');
        const say = (t, c) => { if (msg) { msg.textContent = t; msg.className = 'sx-msg ' + (c || ''); } };
        if (!G.aiPro) { say('Shopio IA est réservé au plan Pro.', 'is-err'); return; }
        btn.disabled = true; btn.classList.add('is-busy'); say('Shopio IA rédige cette section… (environ 15 secondes)');
        try {
            applySections(await X.aiWrite(G.urls.sectionsAi, [s], { instructions: aiNote }));
            openSec = s.id; render();
            const li = document.querySelector('#pePage .is-open [data-sai-msg]');
            if (li) { li.textContent = 'Section rédigée : relisez, puis touchez « Sauvegarder ».'; li.className = 'sx-msg is-ok'; }
        } catch (err) { say(err.message, 'is-err'); btn.disabled = false; btn.classList.remove('is-busy'); }
    }

    /* ───────── Sauvegarder ───────── */
    $('peSave').addEventListener('click', async () => {
        if (saving) return;
        saving = true; $('peSave').disabled = true; $('peSave').textContent = 'Sauvegarde…';
        try {
            const r = await fetch(G.urls.save, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ content: C, design: Z, form_scope: formScope, page_scope: pageScope }) });
            const j = await r.json().catch(() => ({}));
            if (!r.ok) {
                packErr = {};
                Object.entries(j.errors || {}).forEach(([k, v]) => { if (k.startsWith('packs.')) packErr[k] = v[0]; });
                if (Object.keys(packErr).length) { showTab('offres'); setState('Corrigez les paliers de remise (onglet Offres).', 'is-err'); }
                else setState((j.errors && Object.values(j.errors)[0][0]) || j.message || 'Sauvegarde impossible.', 'is-err');
                return;
            }
            C.packs = j.packs.map(k => ({ qty: k.qty, price: k.price, label: k.label || '' }));
            dirty = false; packErr = {};
            setState('Sauvegardé ✓', 'is-ok'); render(); load();
        } catch (x) { setState('Sauvegarde impossible : vérifiez la connexion.', 'is-err'); }
        finally { saving = false; $('peSave').disabled = false; $('peSave').textContent = 'Sauvegarder'; }
    });

    /* ───────── Publier / dépublier ───────── */
    $('pePublish').addEventListener('click', async () => {
        const b = $('pePublish'); b.disabled = true;
        try {
            const r = await fetch(G.urls.toggle, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
            const j = await r.json(); if (!r.ok) throw new Error();
            $('peStatus').textContent = j.active ? 'Publié' : 'Brouillon'; $('peStatus').classList.toggle('is-on', j.active);
            b.textContent = j.active ? 'Dépublier' : 'Publier'; b.classList.toggle('is-off', j.active);
            load();
        } catch (e) { setState('Le statut n’a pas pu être changé. Réessayez.', 'is-err'); }
        b.disabled = false;
    });

    /* ───────── Changer de produit ───────── */
    $('peProduct').addEventListener('change', e => { if (dirty && !confirm('Vos modifications ne sont pas sauvegardées. Changer de produit quand même ?')) { e.target.value = location.pathname; return; } dirty = false; location.href = e.target.value; });

    /* ───────── Aperçu ───────── */
    const iframe = $('peIframe'), frame = $('peFrame');
    let dev = 'phone';
    function fit() {
        if (dev !== 'desk') { iframe.style.transform = ''; iframe.style.height = '100%'; return; }
        const k = frame.clientWidth / 1280; if (!k) return;
        iframe.style.transform = 'scale(' + k + ')'; iframe.style.height = (frame.clientHeight / k) + 'px';
    }
    function load() {
        if (!G.urls.preview) { frame.classList.remove('is-loading'); iframe.srcdoc = '<p style="font:600 15px system-ui;padding:24px;color:#45455E">L’aperçu s’affiche quand votre boutique est validée.</p>'; return; }
        frame.classList.add('is-loading'); iframe.src = G.urls.preview + '?apercu=1&t=' + Date.now();
    }
    iframe.addEventListener('load', () => { frame.classList.remove('is-loading'); fit(); });
    document.querySelectorAll('[data-dev]').forEach(b => b.addEventListener('click', () => {
        dev = b.dataset.dev; document.querySelectorAll('[data-dev]').forEach(x => x.setAttribute('aria-pressed', x === b ? 'true' : 'false'));
        frame.className = 'pe-frame ' + (dev === 'desk' ? 'is-desk' : 'is-phone'); fit();
    }));
    if ('ResizeObserver' in window) new ResizeObserver(fit).observe(frame);
    $('peReload').addEventListener('click', load);

    /* Après « Créer avec Shopio IA » : avertissement éventuel */
    try { const w = sessionStorage.getItem('wzWarning'); if (w && $('peWarn')) { $('peWarn').textContent = w; $('peWarn').hidden = false; } sessionStorage.removeItem('wzWarning'); } catch (e) {}

    showTab('sections');
    if (document.readyState === 'complete') load(); else window.addEventListener('load', load);
})();
</script>
