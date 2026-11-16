{{--
    Vitrine affichée dans l'éditeur (« Modifier la page », propriétaire seulement : PublicShopController ?edition=1&cadre=1).
    Chaque section est entourée au survol (toujours visible sur téléphone) avec sa petite barre : Modifier, Monter,
    Descendre, Masquer/Afficher, Dupliquer, Supprimer ; « + Ajouter une section » entre deux sections ; l'en-tête
    ouvre son panneau. Les actions sont faites par l'éditeur (window.parent.SXE) ; les liens ne quittent pas la page.
--}}
<style>
.sxe-sec { position: relative; min-height: 40px; }
.sxe-sec:hover, .sxe-sec.is-sel, [data-sx="header"]:hover, [data-sx="header"].is-sel { outline: 2px solid #1F7CF6; outline-offset: -2px; }
.sxe-sec.is-off { opacity: 1; }
.sxe-bar { position: absolute; top: 8px; right: 8px; z-index: 900; display: none; align-items: center; gap: 2px; padding: 4px; border-radius: 12px; background: #0B1E3D; box-shadow: 0 10px 24px -10px rgba(11,30,61,.6); font: 700 12.5px 'Plus Jakarta Sans', system-ui, sans-serif; }
.sxe-bar b { padding: 0 8px 0 6px; color: #C9D6EA; font-weight: 700; white-space: nowrap; max-width: 160px; overflow: hidden; text-overflow: ellipsis; }
.sxe-bar button { display: inline-flex; align-items: center; justify-content: center; gap: 5px; min-width: 32px; height: 32px; padding: 0 8px; border: 0; border-radius: 9px; background: transparent; color: #fff; font: inherit; cursor: pointer; }
.sxe-bar button:hover:not(:disabled) { background: #1B3563; }
.sxe-bar button:disabled { opacity: .35; cursor: default; }
.sxe-bar button.is-main { background: #1F7CF6; }
.sxe-bar button.is-del:hover { background: #B91C1C; }
.sxe-sec:hover > .sxe-bar, .sxe-sec.is-sel > .sxe-bar, [data-sx="header"]:hover > .sxe-bar, [data-sx="header"].is-sel > .sxe-bar { display: flex; }
.sxe-add { position: relative; height: 0; z-index: 800; }
.sxe-add button { position: absolute; left: 50%; top: 0; transform: translate(-50%, -50%); display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 14px; border: 0; border-radius: 999px; background: #1F7CF6; color: #fff; font: 700 13px 'Plus Jakarta Sans', system-ui, sans-serif; cursor: pointer; box-shadow: 0 8px 18px -8px rgba(31,124,246,.8); opacity: 0; transition: opacity 160ms; }
.sxe-add:hover button, .sxe-add button:focus-visible, main:hover .sxe-add button { opacity: .92; }
.sxe-ph { display: flex; align-items: center; justify-content: center; gap: 8px; min-height: 76px; margin: 6px 12px; padding: 14px; border-radius: 14px; border: 2px dashed #C3CBDA; background: #F6F9FF; color: #45455E; font: 600 13.5px 'Plus Jakarta Sans', system-ui, sans-serif; text-align: center; }
.sxe-toast { position: fixed; left: 50%; bottom: 18px; transform: translateX(-50%); z-index: 2000; padding: 10px 16px; border-radius: 999px; background: #0B1E3D; color: #fff; font: 700 13px 'Plus Jakarta Sans', system-ui, sans-serif; box-shadow: 0 12px 30px rgba(11,30,61,.35); }
/* Téléphone (pas de survol) : barres toujours visibles, plus compactes */
@media (hover: none) {
    .sxe-bar { display: flex; top: 6px; right: 6px; }
    .sxe-bar b { display: none; }
    .sxe-bar button span { display: none; }
    .sxe-add button { opacity: .92; }
    .sxe-sec { outline: 1px dashed rgba(31,124,246,.45); outline-offset: -1px; }
}
/* Apparitions au défilement : tout est visible tout de suite dans l'éditeur */
[data-rv] { opacity: 1 !important; transform: none !important; }
</style>
<script>
(function () {
    'use strict';
    const P = window.parent !== window ? window.parent : null;
    const ed = () => (P && P.SXE) || null;
    const I = {
        edit: 'M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z', up: 'M12 19V5M5 12l7-7 7 7', down: 'M12 5v14M19 12l-7 7-7-7',
        eye: 'M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z', off: 'M3 3l18 18M10.6 5.1A10 10 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.6 6.6C3.9 8.4 2 12 2 12s3.6 7 10 7c1.9 0 3.6-.6 5-1.5',
        dup: 'M8 8h12v12H8zM4 16V4h12', del: 'M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3', plus: 'M12 5v14M5 12h14',
    };
    const svg = d => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' + d + '"/></svg>';
    const esc = v => String(v == null ? '' : v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    let toastT;
    function toast(t) { let el = document.querySelector('.sxe-toast'); if (!el) { el = document.createElement('div'); el.className = 'sxe-toast'; el.setAttribute('role', 'status'); document.body.appendChild(el); } el.textContent = t; el.hidden = false; clearTimeout(toastT); toastT = setTimeout(() => { el.hidden = true; }, 2200); }

    function build() {
        const secs = [...document.querySelectorAll('.sxe-sec')];
        secs.forEach((el, i) => {
            const on = el.dataset.sxOn === '1', builtin = el.dataset.sxBuiltin === '1', name = el.dataset.sxName;
            // Section masquée, ou sans rien à montrer pour l'instant (pas encore d'avis, de chiffres…)
            const visible = [...el.children].some(c => c.offsetHeight > 0);
            if (!on) el.insertAdjacentHTML('afterbegin', '<div class="sxe-ph">' + svg(I.off) + ' « ' + esc(name) + ' » est masquée : vos clients ne la voient pas.</div>');
            else if (!visible) el.insertAdjacentHTML('afterbegin', '<div class="sxe-ph">« ' + esc(name) + ' » s’affichera quand il y aura de quoi la remplir (vrais avis, vraies commandes, produits…).</div>');
            el.classList.toggle('is-off', !on);
            el.insertAdjacentHTML('afterbegin', '<div class="sxe-bar" role="toolbar" aria-label="Section ' + esc(name) + '"><b>' + esc(name) + '</b>' +
                '<button type="button" class="is-main" data-act="edit" title="Modifier">' + svg(I.edit) + '<span>Modifier</span></button>' +
                '<button type="button" data-act="up" title="Monter" aria-label="Monter"' + (i === 0 ? ' disabled' : '') + '>' + svg(I.up) + '</button>' +
                '<button type="button" data-act="down" title="Descendre" aria-label="Descendre"' + (i === secs.length - 1 ? ' disabled' : '') + '>' + svg(I.down) + '</button>' +
                '<button type="button" data-act="toggle" title="' + (on ? 'Masquer' : 'Afficher') + '" aria-label="' + (on ? 'Masquer' : 'Afficher') + '">' + svg(on ? I.eye : I.off) + '</button>' +
                (builtin ? '' : '<button type="button" data-act="dup" title="Dupliquer" aria-label="Dupliquer">' + svg(I.dup) + '</button><button type="button" class="is-del" data-act="del" title="Supprimer" aria-label="Supprimer">' + svg(I.del) + '</button>') + '</div>');
            el.insertAdjacentHTML('afterend', '<div class="sxe-add"><button type="button" data-add="' + esc(el.dataset.sx) + '">' + svg(I.plus) + ' Ajouter une section</button></div>');
        });
        if (secs[0]) secs[0].insertAdjacentHTML('beforebegin', '<div class="sxe-add"><button type="button" data-add="">' + svg(I.plus) + ' Ajouter une section</button></div>');
        const head = document.querySelector('[data-sx="header"]');
        if (head) head.insertAdjacentHTML('beforeend', '<div class="sxe-bar" role="toolbar" aria-label="En-tête"><b>En-tête</b><button type="button" class="is-main" data-act="header">' + svg(I.edit) + '<span>Modifier l’en-tête</span></button></div>');
    }

    // Clics : actions de l'éditeur ; un lien ou un formulaire ne quitte jamais la page en mode édition
    document.addEventListener('click', e => {
        const E = ed(), act = e.target.closest('[data-act]'), add = e.target.closest('[data-add]');
        if (act || add) {
            e.preventDefault(); e.stopPropagation();
            if (!E) return;
            if (add) return E.add(add.dataset.add || null);
            if (act.dataset.act === 'header') return E.header();
            const id = act.closest('[data-sx]').dataset.sx;
            return E.action(act.dataset.act, id);
        }
        const link = e.target.closest('a[href]');
        if (link && !link.getAttribute('href').startsWith('#')) { e.preventDefault(); toast('Les liens sont désactivés pendant la modification.'); }
        // Toucher une section l'ouvre dans le panneau (sauf ses boutons à elle : carrousel, panier…)
        if (!e.target.closest('button, input, select, textarea, summary, label') && E) {
            const sec = e.target.closest('[data-sx]');
            if (sec) { e.preventDefault(); sec.dataset.sx === 'header' ? E.header() : E.action('edit', sec.dataset.sx); }
        }
    }, true);
    document.addEventListener('submit', e => { e.preventDefault(); toast('Les formulaires sont désactivés pendant la modification.'); }, true);

    // Pour l'éditeur : mettre une section en avant (sélection et défilement)
    window.SXF = {
        select(id, scroll) {
            document.querySelectorAll('.is-sel').forEach(x => x.classList.remove('is-sel'));
            const el = id ? document.querySelector('[data-sx="' + CSS.escape(id) + '"]') : null;
            if (!el) return;
            el.classList.add('is-sel');
            if (scroll) el.scrollIntoView({ block: 'center', behavior: 'smooth' });
        },
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', build); else build();
    window.addEventListener('load', () => {
        // Page « Panier » du sélecteur : le panier s'ouvre (il est le même sur toutes les pages)
        if (new URLSearchParams(location.search).get('panier') === '1') { const c = document.querySelector('[data-sm-cart]'); if (c) c.click(); }
        const E = ed(); if (E) E.frameReady(window);
    });
})();
</script>
