/*
 * Éditeur de page produit (sections et blocs) — vue : resources/views/vendeur/page-builder/editor.blade.php
 *
 * L'état est le JSON de la page (sections, ordre, blocs, ordre des blocs, réglages). Chaque modification :
 *   - est ajoutée à l'historique (annuler / rétablir ; une frappe continue dans un même champ compte pour une étape) ;
 *   - redessine l'aperçu : le serveur valide et rend la page, le cadre remplace son contenu sans recharger ;
 *   - déclenche l'enregistrement automatique du brouillon (la révision détecte une modification faite ailleurs).
 * Le serveur reste seul juge : PageValidator refuse ce qui ne respecte pas les schémas.
 */
import Alpine from 'alpinejs';
import Sortable from 'sortablejs';

const SAVE_DELAY = 1200;
const PREVIEW_DELAY = 300;
const HISTORY_MAX = 60;
const MAX_SECTIONS = 40;
const DESKTOP_WIDTH = 1280;

const copy = (v) => JSON.parse(JSON.stringify(v));
const isObj = (v) => v !== null && typeof v === 'object' && !Array.isArray(v);

/** Le JSON venu de PHP peut contenir [] à la place de {} (tableau vide) : on remet des objets partout */
function normalize(raw) {
    const page = isObj(raw) ? copy(raw) : {};
    page.sections = isObj(page.sections) ? page.sections : {};
    page.seo = isObj(page.seo) ? page.seo : {};
    page.order = Array.isArray(page.order) ? page.order.filter((id) => page.sections[id]) : Object.keys(page.sections);
    for (const s of Object.values(page.sections)) {
        s.settings = isObj(s.settings) ? s.settings : {};
        s.blocks = isObj(s.blocks) ? s.blocks : {};
        s.block_order = Array.isArray(s.block_order) ? s.block_order.filter((id) => s.blocks[id]) : Object.keys(s.blocks);
        s.disabled = !!s.disabled;
        for (const b of Object.values(s.blocks)) {
            b.settings = isObj(b.settings) ? b.settings : {};
            b.disabled = !!b.disabled;
        }
    }
    return page;
}

function newId(prefix, taken) {
    const base = String(prefix).toLowerCase().replace(/[^a-z0-9]+/g, '_').slice(0, 20);
    let id;
    do {
        id = base + '_' + Math.random().toString(36).slice(2, 8).padEnd(6, '0');
    } while (taken.includes(id));
    return id;
}

function defaults(schema) {
    const out = {};
    for (const s of schema.settings || []) {
        if (s.type !== 'header') out[s.id] = s.default ?? null;
    }
    return out;
}

const strip = (html) => String(html ?? '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();

document.addEventListener('alpine:init', () => {
    Alpine.data('pageBuilder', (config) => ({
        page: normalize(config.page),
        revision: config.revision,
        S: config.sections,
        B: config.blocks,
        library: config.library,
        schemes: config.schemes,
        icons: config.icons,
        products: config.products,
        urls: config.urls,
        mediaBase: config.mediaBase,
        published: config.published,

        sel: { section: null, block: null },
        device: 'mobile',
        phone: false,
        scale: 1,
        tab: 'tree',
        status: 'saved',
        message: '',
        errors: {},
        notice: '',
        undoStack: [],
        redoStack: [],
        libraryOpen: false,
        libraryAt: null,
        blockMenu: null,
        rowMenu: null,
        modal: null,
        modalLoading: false,
        templates: { shopio: [], mine: [], categories: {}, max: 20 },
        versions: [],
        tplForm: { name: '', category: 'general', error: '' },
        seoDefaults: config.seoDefaults,
        publishing: false,
        togglingLive: false,
        aiBusy: false,
        uploading: null,

        _last: '',
        _lastKey: null,
        _lastAt: 0,
        _saveTimer: null,
        _previewTimer: null,
        _previewSeq: 0,
        _saving: null,
        _noticeTimer: null,

        init() {
            this._last = JSON.stringify(this.page);
            this.sel.section = this.page.order[0] ?? null;
            const mq = window.matchMedia('(max-width: 899px)');
            this.phone = mq.matches;
            mq.addEventListener('change', (e) => { this.phone = e.matches; });
            // Aperçu « ordinateur » : la page est rendue à 1280 px puis réduite à la largeur disponible
            new ResizeObserver(() => {
                this.scale = Math.min(1, Math.max(0.3, (this.$refs.stage.clientWidth - 36) / DESKTOP_WIDTH));
            }).observe(this.$refs.stage);
            window.addEventListener('message', (e) => this.onFrameMessage(e));
            window.addEventListener('keydown', (e) => this.onKey(e));
            window.addEventListener('beforeunload', (e) => {
                if (['dirty', 'saving', 'error', 'offline'].includes(this.status)) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
            this.$nextTick(() => this.sortable(this.$refs.sections, 'section', (ids) => { this.page.order = ids; }));
        },

        /* ───────── Lecture ───────── */

        get current() {
            const s = this.page.sections[this.sel.section];
            if (!s) return null;
            if (this.sel.block) {
                const b = s.blocks[this.sel.block];
                const schema = b && this.B[b.type];
                return schema ? { kind: 'block', item: b, schema, path: `sections.${this.sel.section}.blocks.${this.sel.block}` } : null;
            }
            const schema = this.S[s.type];
            return schema ? { kind: 'section', item: s, schema, path: `sections.${this.sel.section}` } : null;
        },

        /** Clé qui change avec l'élément sélectionné : les champs (texte riche notamment) sont recréés */
        get selKey() {
            return (this.sel.section ?? '') + '/' + (this.sel.block ?? '');
        },

        section(id) { return this.page.sections[id]; },
        sectionSchema(id) { return this.S[this.page.sections[id]?.type] ?? null; },
        blockSchema(sid, bid) { return this.B[this.page.sections[sid]?.blocks[bid]?.type] ?? null; },

        /** Nom affiché dans l'arbre : le titre saisi s'il y en a un, sinon le nom du type */
        label(item, schema) {
            if (!schema) return 'Élément inconnu';
            // Bloc unique dans sa section (prix, formulaire…) : son nom suffit et reste lisible
            if (schema.limit === 1) return schema.name;
            for (const key of ['heading', 'title', 'question', 'name', 'label', 'text', 'content']) {
                const v = strip(item?.settings?.[key]);
                if (v) return v.length > 38 ? v.slice(0, 37) + '…' : v;
            }
            return schema.name;
        },

        isStatic(id) { return !!this.sectionSchema(id)?.static; },

        hasError(prefix) {
            return Object.keys(this.errors).some((k) => k === prefix || k.startsWith(prefix + '.'));
        },

        fieldError(id) {
            return this.current ? (this.errors[`${this.current.path}.settings.${id}`]?.[0] ?? null) : null;
        },

        get pageErrors() {
            return Object.entries(this.errors).filter(([k]) => k === 'page' || k === 'order').map(([, v]) => v[0]);
        },

        get canUndo() { return this.undoStack.length > 0; },
        get canRedo() { return this.redoStack.length > 0; },

        get statusText() {
            return {
                saved: 'Brouillon enregistré',
                dirty: 'Modifications en cours…',
                saving: 'Enregistrement…',
                error: 'Non enregistré : corrige les champs en rouge',
                conflict: 'Page modifiée ailleurs',
                offline: 'Connexion perdue, nouvel essai…',
            }[this.status];
        },

        /* ───────── Sélection ───────── */

        select(section, block = null, fromFrame = false) {
            this.sel = { section, block };
            this.blockMenu = null;
            this.rowMenu = null;
            if (!fromFrame) this.postFrame({ type: 'pb:select', section, block });
            if (this.isPhone()) this.tab = 'settings';
        },

        isPhone() { return this.phone; },

        get desktopPreview() { return this.device === 'desktop' && !this.phone; },

        fixSelection() {
            const s = this.page.sections[this.sel.section];
            if (!s) this.sel = { section: this.page.order[0] ?? null, block: null };
            else if (this.sel.block && !s.blocks[this.sel.block]) this.sel.block = null;
        },

        /* ───────── Réglages ───────── */

        val(id) {
            const c = this.current;
            if (!c) return null;
            const v = c.item.settings[id];
            return v === undefined ? (c.schema.settings.find((s) => s.id === id)?.default ?? null) : v;
        },

        set(id, value) {
            const c = this.current;
            if (!c) return;
            c.item.settings[id] = value;
            this.commit(`${c.path}.${id}`);
        },

        listHas(id, productId) { return (this.val(id) || []).includes(productId); },

        listToggle(setting, productId) {
            const list = [...(this.val(setting.id) || [])];
            const i = list.indexOf(productId);
            if (i >= 0) list.splice(i, 1);
            else if (list.length < (setting.max ?? 8)) list.push(productId);
            this.set(setting.id, list);
        },

        mediaUrl(path) { return path ? this.mediaBase + path : null; },

        async upload(setting, event) {
            const file = event.target.files?.[0];
            event.target.value = '';
            if (!file) return;
            const video = setting.type === 'video';
            if (file.size > (video ? 20 : 8) * 1024 * 1024) {
                this.flash(video ? 'La vidéo dépasse 20 Mo : raccourcis-la ou colle un lien YouTube ou TikTok.' : 'L\'image dépasse 8 Mo : choisis une image plus légère.');
                return;
            }
            const target = { section: this.sel.section, block: this.sel.block };
            this.uploading = setting.id;
            const form = new FormData();
            form.append(video ? 'video' : 'image', file);
            try {
                const r = await this.request('POST', video ? this.urls.video : this.urls.upload, form);
                if (!r.ok) {
                    const field = video ? 'video' : 'image';
                    this.flash(r.data?.errors?.[field]?.[0] ?? (r.status === 413 ? 'Fichier trop lourd pour le serveur.' : r.data?.message) ?? 'Envoi impossible. Réessaie.');
                    return;
                }
                // L'image va à l'élément sélectionné au moment de l'envoi, même si la sélection a changé depuis
                const s = this.page.sections[target.section];
                const item = target.block ? s?.blocks[target.block] : s;
                if (item) {
                    item.settings[setting.id] = r.data.path;
                    this.commit(null);
                }
            } catch {
                this.flash('Connexion perdue pendant l\'envoi. Réessaie.');
            } finally {
                this.uploading = null;
            }
        },

        /* ───────── Sections ───────── */

        typeCount(type) { return Object.values(this.page.sections).filter((s) => s.type === type).length; },

        canAddSection(type) {
            const max = this.S[type]?.max_per_page;
            return this.page.order.length < MAX_SECTIONS && (max == null || this.typeCount(type) < max);
        },

        openLibrary(at = null) {
            this.libraryAt = at;
            this.libraryOpen = true;
        },

        buildSection(type, presetIndex = 0) {
            const schema = this.S[type];
            const preset = schema.presets?.[presetIndex] ?? schema.presets?.[0] ?? {};
            const blocks = {};
            const order = [];
            for (const b of preset.blocks || []) {
                if (!this.B[b.type]) continue;
                const id = newId(b.type, order);
                blocks[id] = { type: b.type, settings: { ...defaults(this.B[b.type]), ...(b.settings || {}) }, disabled: false };
                order.push(id);
            }
            return { type, settings: { ...defaults(schema), ...(preset.settings || {}) }, blocks, block_order: order, disabled: false };
        },

        addSection(type, presetIndex = 0) {
            if (!this.canAddSection(type)) return;
            const id = newId(type, Object.keys(this.page.sections));
            this.page.sections[id] = this.buildSection(type, presetIndex);
            const at = this.libraryAt ?? this.page.order.length;
            this.page.order.splice(at, 0, id);
            this.libraryOpen = false;
            this.commit(null);
            this.select(id);
        },

        duplicateSection(id) {
            const s = this.page.sections[id];
            if (!s || this.isStatic(id) || !this.canAddSection(s.type)) return;
            const dup = copy(s);
            dup.blocks = {};
            dup.block_order = [];
            for (const bid of s.block_order) {
                const nid = newId(s.blocks[bid].type, dup.block_order);
                dup.blocks[nid] = copy(s.blocks[bid]);
                dup.block_order.push(nid);
            }
            const nid = newId(s.type, Object.keys(this.page.sections));
            this.page.sections[nid] = dup;
            this.page.order.splice(this.page.order.indexOf(id) + 1, 0, nid);
            this.commit(null);
            this.select(nid);
        },

        removeSection(id) {
            if (this.isStatic(id)) return;
            const name = this.label(this.page.sections[id], this.sectionSchema(id));
            delete this.page.sections[id];
            this.page.order = this.page.order.filter((x) => x !== id);
            this.fixSelection();
            this.commit(null);
            this.flash(`« ${name} » supprimée. Ctrl + Z pour annuler.`);
        },

        toggleSection(id) {
            if (this.isStatic(id)) return;
            this.page.sections[id].disabled = !this.page.sections[id].disabled;
            this.commit(null);
        },

        moveSection(id, delta) {
            const i = this.page.order.indexOf(id);
            const j = i + delta;
            if (i < 0 || j < 0 || j >= this.page.order.length) return;
            const order = [...this.page.order];
            [order[i], order[j]] = [order[j], order[i]];
            this.page.order = order;
            this.commit(null);
        },

        /* ───────── Blocs ───────── */

        /** Bloc exigé par la section (le formulaire de commande de la section Produit) : ni masqué ni supprimé */
        isRequiredBlock(sid, bid) {
            const type = this.page.sections[sid]?.blocks[bid]?.type;
            return !!type && (this.sectionSchema(sid)?.required_blocks || []).includes(type);
        },

        blockTypes(sid) {
            return (this.sectionSchema(sid)?.blocks || []).map((t) => this.B[t]).filter(Boolean);
        },

        canAddBlock(sid, type) {
            const s = this.page.sections[sid];
            const schema = this.sectionSchema(sid);
            if (!s || !schema || s.block_order.length >= schema.max_blocks) return false;
            const limit = this.B[type]?.limit;
            return limit == null || Object.values(s.blocks).filter((b) => b.type === type).length < limit;
        },

        addBlock(sid, type) {
            if (!this.canAddBlock(sid, type)) return;
            const s = this.page.sections[sid];
            const id = newId(type, s.block_order);
            s.blocks[id] = { type, settings: defaults(this.B[type]), disabled: false };
            // Après le bloc sélectionné ; sinon avant le premier bloc obligatoire (le formulaire de commande) ; sinon à la fin
            const required = this.sectionSchema(sid)?.required_blocks || [];
            let at = this.sel.section === sid && this.sel.block ? s.block_order.indexOf(this.sel.block) + 1 : -1;
            if (at <= 0) at = s.block_order.findIndex((bid) => required.includes(s.blocks[bid]?.type));
            s.block_order.splice(at < 0 ? s.block_order.length : at, 0, id);
            this.blockMenu = null;
            this.commit(null);
            this.select(sid, id);
        },

        duplicateBlock(sid, bid) {
            const s = this.page.sections[sid];
            const b = s?.blocks[bid];
            if (!b || !this.canAddBlock(sid, b.type)) return;
            const id = newId(b.type, s.block_order);
            s.blocks[id] = copy(b);
            s.block_order.splice(s.block_order.indexOf(bid) + 1, 0, id);
            this.commit(null);
            this.select(sid, id);
        },

        removeBlock(sid, bid) {
            const s = this.page.sections[sid];
            if (!s?.blocks[bid] || this.isRequiredBlock(sid, bid)) return;
            const name = this.label(s.blocks[bid], this.blockSchema(sid, bid));
            delete s.blocks[bid];
            s.block_order = s.block_order.filter((x) => x !== bid);
            this.fixSelection();
            this.commit(null);
            this.flash(`« ${name} » supprimé. Ctrl + Z pour annuler.`);
        },

        toggleBlock(sid, bid) {
            const b = this.page.sections[sid]?.blocks[bid];
            if (!b || this.isRequiredBlock(sid, bid)) return;
            b.disabled = !b.disabled;
            this.commit(null);
        },

        /* ───────── Glisser-déposer ───────── */

        /**
         * SortableJS déplace les éléments du DOM ; Alpine doit rester maître du DOM. On lit le nouvel ordre,
         * on remet l'élément à sa place d'origine, puis on change les données : Alpine redessine la liste.
         */
        sortable(el, kind, apply) {
            if (!el || el._pbSortable) return;
            const rows = () => [...el.querySelectorAll(`:scope > .pbe-${kind}`)];
            el._pbSortable = Sortable.create(el, {
                handle: `[data-handle="${kind}"]`,
                draggable: `.pbe-${kind}`,
                animation: 150,
                ghostClass: 'pbe-ghost',
                onEnd: (e) => {
                    if (e.oldDraggableIndex === e.newDraggableIndex) return;
                    const ids = rows().map((n) => n.dataset.id);
                    e.item.remove();
                    el.insertBefore(e.item, rows()[e.oldDraggableIndex] ?? null);
                    apply(ids);
                    this.commit(null);
                },
            });
        },

        sortBlocks(el, sid) {
            this.sortable(el, 'block', (ids) => { this.page.sections[sid].block_order = ids; });
        },

        /* ───────── Historique ───────── */

        /** Enregistre une étape d'historique (sauf frappe continue dans le même champ) et lance aperçu + sauvegarde */
        commit(key) {
            const now = Date.now();
            const json = JSON.stringify(this.page);
            if (json === this._last) return;
            if (!(key && key === this._lastKey && now - this._lastAt < 900)) {
                this.undoStack.push(this._last);
                if (this.undoStack.length > HISTORY_MAX) this.undoStack.shift();
                this.redoStack = [];
            }
            this._lastKey = key;
            this._lastAt = now;
            this._last = json;
            this.changed();
        },

        undo() {
            if (!this.canUndo) return;
            this.redoStack.push(this._last);
            this.restore(this.undoStack.pop());
        },

        redo() {
            if (!this.canRedo) return;
            this.undoStack.push(this._last);
            this.restore(this.redoStack.pop());
        },

        restore(json) {
            this.page = normalize(JSON.parse(json));
            this._last = json;
            this._lastKey = null;
            this.fixSelection();
            this.changed();
            this.postFrame({ type: 'pb:select', ...this.sel });
        },

        onKey(e) {
            if (!(e.ctrlKey || e.metaKey)) return;
            const k = e.key.toLowerCase();
            if (k === 's') {
                e.preventDefault();
                this.save();
                return;
            }
            // Dans un champ de saisie, on laisse l'annulation native du navigateur
            if (e.target.closest('input, textarea, select, [contenteditable="true"]')) return;
            if (k === 'z' && !e.shiftKey) { e.preventDefault(); this.undo(); }
            else if ((k === 'z' && e.shiftKey) || k === 'y') { e.preventDefault(); this.redo(); }
        },

        /* ───────── Aperçu ───────── */

        changed() {
            if (this.status !== 'conflict') this.status = 'dirty';
            clearTimeout(this._saveTimer);
            this._saveTimer = setTimeout(() => this.save(), SAVE_DELAY);
            clearTimeout(this._previewTimer);
            this._previewTimer = setTimeout(() => this.refreshPreview(), PREVIEW_DELAY);
        },

        postFrame(message) {
            this.$refs.frame?.contentWindow?.postMessage(JSON.parse(JSON.stringify(message)), window.location.origin);
        },

        onFrameMessage(e) {
            if (e.source !== this.$refs.frame?.contentWindow || e.origin !== window.location.origin) return;
            const m = e.data || {};
            if (m.type === 'pb:ready') {
                this.postFrame({ type: 'pb:select', ...this.sel });
                if (this.status !== 'saved') this.refreshPreview();
            } else if (m.type === 'pb:select' && this.page.sections[m.section]) {
                this.select(m.section, m.block && this.page.sections[m.section].blocks[m.block] ? m.block : null, true);
            }
        },

        async refreshPreview() {
            const seq = ++this._previewSeq;
            try {
                const r = await this.request('POST', this.urls.preview, { page: JSON.parse(this._last) });
                if (seq !== this._previewSeq) return; // une version plus récente est déjà demandée
                if (r.status === 422) {
                    this.errors = r.data.errors || {};
                    return;
                }
                if (!r.ok) return;
                this.errors = {};
                this.postFrame({ type: 'pb:render', html: r.data });
            } catch {
                // hors connexion : l'enregistrement signale le problème
            }
        },

        /* ───────── Enregistrement et publication ───────── */

        async save() {
            clearTimeout(this._saveTimer);
            if (this.status === 'conflict' || this.status === 'saved') return this.status === 'saved';
            if (this._saving) {
                await this._saving;
                return this.status === 'saved' ? true : this.save();
            }
            const body = this._last;
            this.status = 'saving';
            this._saving = (async () => {
                try {
                    const r = await this.request('PUT', this.urls.save, { page: JSON.parse(body), revision: this.revision });
                    if (r.status === 409) {
                        this.status = 'conflict';
                        this.message = r.data.message;
                        return;
                    }
                    if (r.status === 422) {
                        this.errors = r.data.errors || {};
                        this.status = 'error';
                        return;
                    }
                    if (!r.ok) throw new Error(String(r.status));
                    this.revision = r.data.revision;
                    this.published.pending = r.data.pending;
                    this.errors = {};
                    this.status = this._last === body ? 'saved' : 'dirty';
                    if (this.status === 'dirty') this._saveTimer = setTimeout(() => this.save(), SAVE_DELAY);
                } catch {
                    this.status = 'offline';
                    this._saveTimer = setTimeout(() => { this.status = 'dirty'; this.save(); }, 5000);
                }
            })();
            await this._saving;
            this._saving = null;
            return this.status === 'saved';
        },

        async publish() {
            if (this.publishing) return;
            this.publishing = true;
            try {
                if (!(await this.save())) {
                    this.flash(this.status === 'conflict' ? this.message : 'Enregistre d\'abord la page (corrige les champs en rouge).');
                    return;
                }
                const r = await this.request('POST', this.urls.publish, {});
                if (r.status === 422) {
                    this.errors = r.data.errors || {};
                    this.flash('Publication impossible : corrige les champs en rouge.');
                    return;
                }
                if (!r.ok) throw new Error(String(r.status));
                this.revision = r.data.revision;
                this.published = { version: r.data.version, at: r.data.at, pending: false, enabled: r.data.enabled };
                this.flash(r.data.enabled
                    ? `Page publiée (version ${r.data.version}).`
                    : `Version ${r.data.version} publiée. Active « Visible par les clients » pour la montrer.`);
            } catch {
                this.flash('Publication impossible pour le moment. Vérifie ta connexion et réessaie.');
            } finally {
                this.publishing = false;
            }
        },

        /* ───────── Modèles, historique, référencement ───────── */

        async openTemplates() {
            this.modal = 'templates';
            this.modalLoading = true;
            try {
                const r = await this.request('GET', this.urls.templates);
                if (r.ok) this.templates = r.data;
            } finally {
                this.modalLoading = false;
            }
        },

        async openHistory() {
            this.modal = 'history';
            this.modalLoading = true;
            try {
                const r = await this.request('GET', this.urls.versions);
                if (r.ok) this.versions = r.data.versions;
            } finally {
                this.modalLoading = false;
            }
        },

        applyTemplate(t) {
            if (!window.confirm(`Remplacer ton brouillon par le modèle « ${t.name} » ? Tu pourras revenir en arrière avec « Annuler ».`)) return;
            this.serverReplace(this.urls.templateApply.replace('__T__', t.id));
        },

        restoreVersion(v) {
            if (!window.confirm(`Remettre la version ${v.version} dans ton brouillon ? Rien n'est publié tant que tu ne cliques pas sur « Publier ».`)) return;
            this.serverReplace(this.urls.restore.replace('__V__', v.version));
        },

        /** Le serveur remplace le brouillon (modèle, ancienne version) ; l'étape d'avant reste dans « Annuler » */
        async serverReplace(url) {
            if (!(await this.save())) {
                this.flash(this.status === 'conflict' ? this.message : 'Enregistre d\'abord la page (corrige les champs en rouge).');
                return;
            }
            const r = await this.request('POST', url, {});
            if (!r.ok) {
                this.flash(Object.values(r.data?.errors || {})[0]?.[0] ?? r.data?.message ?? 'Action impossible pour le moment. Réessaie.');
                return;
            }
            this.undoStack.push(this._last);
            this.redoStack = [];
            this.page = normalize(r.data.page);
            this._last = JSON.stringify(this.page);
            this._lastKey = null;
            this.revision = r.data.revision;
            this.published.pending = r.data.pending;
            this.errors = {};
            this.status = 'saved';
            this.modal = null;
            this.sel = { section: this.page.order.includes('main') ? 'main' : (this.page.order[0] ?? null), block: null };
            this.flash(r.data.message);
            this.refreshPreview();
        },

        async saveTemplate() {
            this.tplForm.error = '';
            const r = await this.request('POST', this.urls.templateStore, { page: JSON.parse(this._last), name: this.tplForm.name, category: this.tplForm.category });
            if (!r.ok) {
                this.tplForm.error = Object.values(r.data?.errors || {})[0]?.[0] ?? 'Enregistrement impossible. Réessaie.';
                return;
            }
            this.tplForm.name = '';
            this.flash(r.data.message);
            this.openTemplates();
        },

        async deleteTemplate(t) {
            if (!window.confirm(`Supprimer le modèle « ${t.name} » ? Les pages qui l'ont utilisé ne changent pas.`)) return;
            const r = await this.request('DELETE', this.urls.templateDestroy.replace('__T__', t.id), {});
            this.flash(r.ok ? r.data.message : 'Suppression impossible.');
            this.openTemplates();
        },

        setSeo(key, value) {
            this.page.seo[key] = value;
            this.commit(`seo.${key}`);
        },

        get seoPreview() {
            return {
                title: (this.page.seo.title || '').trim() || this.seoDefaults.title,
                description: (this.page.seo.description || '').trim() || this.seoDefaults.description,
                url: this.seoDefaults.url,
            };
        },

        /** Shopio IA écrit bannière, présentation, questions et référencement à partir de la fiche produit (brouillon) */
        async aiWrite() {
            if (this.aiBusy) return;
            if (!window.confirm('Shopio IA va écrire une bannière, une présentation avec les points forts, des questions fréquentes et ton référencement Google, à partir de ta fiche produit. Tes questions déjà écrites sont gardées, et « Annuler » revient en arrière. Continuer ?')) return;
            this.aiBusy = true;
            this.flash('Shopio IA écrit ta page… (environ 20 secondes)');
            try {
                await this.serverReplace(this.urls.ai);
            } finally {
                this.aiBusy = false;
            }
        },

        /** Interrupteur de la page construite pour ce produit (désactivée : page produit habituelle) */
        async toggleLive(on) {
            if (this.togglingLive) return;
            this.togglingLive = true;
            try {
                const r = await this.request('PUT', this.urls.toggle, { enabled: on });
                if (!r.ok) {
                    this.flash(r.data?.errors?.enabled?.[0] ?? 'Changement impossible pour le moment. Réessaie.');
                    return;
                }
                this.published.enabled = r.data.enabled;
                this.flash(r.data.message);
            } catch {
                this.flash('Connexion perdue. Réessaie.');
            } finally {
                this.togglingLive = false;
                // La case suit toujours l'état enregistré, même après un refus
                this.$nextTick(() => { const box = document.getElementById('pbe-live'); if (box) box.checked = !!this.published.enabled; });
            }
        },

        flash(text) {
            this.notice = text;
            clearTimeout(this._noticeTimer);
            this._noticeTimer = setTimeout(() => { this.notice = ''; }, 5000);
        },

        async request(method, url, data) {
            const isForm = data instanceof FormData;
            const res = await fetch(url, {
                method,
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                    ...(isForm ? {} : { 'Content-Type': 'application/json' }),
                },
                body: method === 'GET' ? undefined : (isForm ? data : JSON.stringify(data)),
            });
            const type = res.headers.get('content-type') || '';
            const body = type.includes('json') ? await res.json() : await res.text();
            return { ok: res.ok, status: res.status, data: body };
        },

        /* ───────── Texte riche ───────── */

        rte(command, el) {
            el.focus();
            if (command === 'link') {
                const url = window.prompt('Adresse du lien (https://…)', 'https://');
                if (!url || !/^(https?:\/\/|mailto:|tel:)/i.test(url)) return;
                document.execCommand('createLink', false, url);
            } else {
                document.execCommand(command, false, null);
            }
            el.dispatchEvent(new Event('input'));
        },
    }));
});

window.Alpine = Alpine;
Alpine.start();
