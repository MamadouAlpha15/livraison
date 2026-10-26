/* ══════════════════════════════════════════
   AVATAR MENU
══════════════════════════════════════════ */
function toggleAvatarMenu() {
    document.getElementById('avatarMenu').classList.toggle('open');
}
document.addEventListener('click', e => {
    const av  = document.getElementById('navAvatar');
    const men = document.getElementById('avatarMenu');
    if (av && !av.contains(e.target) && men && !men.contains(e.target)) {
        men.classList.remove('open');
    }
});

/* ══════════════════════════════════════════
   RECHERCHE BOUTIQUES (JS côté client)
══════════════════════════════════════════ */
(function () {
    const inp       = document.getElementById('globalSearch');
    const grid      = document.getElementById('shopsGrid');
    const liveInfo  = document.getElementById('searchLiveInfo');
    const liveEmpty = document.getElementById('shopsLiveEmpty');
    const liveMsg   = document.getElementById('shopsLiveEmptyMsg');
    if (!inp || !grid) return;

    function escRe(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }
    function hlText(text, q) {
        if (!q) return text;
        return text.replace(new RegExp('(' + escRe(q) + ')', 'gi'), '<mark>$1</mark>');
    }
    function scrollToEl(el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function doSearch() {
        const q  = inp.value.trim();
        const ql = q.toLowerCase();

        /* ── 1. Filtrage SYNCHRONE (avant toute animation) ── */
        const cards = Array.from(grid.querySelectorAll('.shop-card'));
        let visible = 0, firstCard = null;
        cards.forEach(card => {
            const name     = card.dataset.name     || '';
            const type     = card.dataset.type     || '';
            const products = card.dataset.products || '';
            const matchShop = !ql || name.includes(ql) || type.includes(ql);
            const matchProd = !matchShop && !!ql && products.includes(ql);
            const match     = matchShop || matchProd;
            card.style.display = match ? '' : 'none';
            card.classList.toggle('prod-match', matchProd);
            const nameEl = card.querySelector('.shop-name-clamp');
            if (nameEl) {
                if (!card.dataset.origName) card.dataset.origName = nameEl.textContent.trim();
                nameEl.innerHTML = ql ? hlText(card.dataset.origName, q) : card.dataset.origName;
            }
            if (match) { visible++; if (!firstCard) firstCard = card; }
        });

        /* ── 2. Mise à jour UI ── */
        if (liveInfo) {
            if (ql) {
                liveInfo.innerHTML = visible > 0
                    ? `${visible} boutique${visible > 1 ? 's' : ''} pour <strong>"${q}"</strong>`
                    : `Aucun résultat pour <strong>"${q}"</strong>`;
                liveInfo.style.display = 'block';
            } else {
                liveInfo.style.display = 'none';
                cards.forEach(c => c.classList.remove('prod-match'));
            }
        }
        const sc = document.getElementById('shopCount');
        if (sc) sc.textContent = visible;
        if (liveEmpty) {
            liveEmpty.style.display = (visible === 0 && ql) ? 'block' : 'none';
            if (liveMsg) liveMsg.textContent = `Aucun résultat pour "${q}". Essayez un autre terme.`;
        }

        /* ── 3. Scroll après reflow complet ── */
        if (ql) {
            const target = firstCard || liveEmpty;
            if (target) setTimeout(() => scrollToEl(target), 50);
        } else {
            setTimeout(() => scrollToEl(grid), 50);
        }
    }

    let timer;
    inp.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(doSearch, 200); });
    inp.addEventListener('keydown', e => { if (e.key === 'Enter') { clearTimeout(timer); doSearch(); } });
    window.doSearch = doSearch;
})();

/* ══════════════════════════════════════════
   FILTRE CATÉGORIES
══════════════════════════════════════════ */

/* Initialise les compteurs par catégorie au chargement */
(function initCatCounts() {
    const counts = {};
    document.querySelectorAll('#shopsGrid .shop-card').forEach(card => {
        const t = (card.dataset.type || '').toLowerCase();
        counts[t] = (counts[t] || 0) + 1;
    });

    // Total dans pill "Toutes"
    const allEl = document.getElementById('catCntAll');
    if (allEl) {
        const total = document.querySelectorAll('#shopsGrid .shop-card').length;
        allEl.textContent = total;
    }

    // Compteur par catégorie
    document.querySelectorAll('#catFilter .cat-pill-cnt[data-cat]').forEach(el => {
        const key = (el.dataset.cat || '').toLowerCase();
        el.textContent = counts[key] || 0;
    });
})();

function filterByType(type, pillEl) {
    const grid = document.getElementById('shopsGrid');

    /* 1. Pills actives — aussi dans la sidebar */
    document.querySelectorAll('#catFilter .cat-pill, .sb-cat-item').forEach(p => p.classList.remove('active'));
    pillEl.classList.add('active');
    document.querySelectorAll(`.sb-cat-item[data-cat-type="${type}"]`).forEach(s => s.classList.add('active'));

    /* 2. Fade + filtrage */
    if (grid) grid.style.opacity = '0';
    setTimeout(() => {
        let count = 0;
        document.querySelectorAll('#shopsGrid .shop-card').forEach(card => {
            const match = !type || (card.dataset.type || '').toLowerCase() === type.toLowerCase();
            card.style.display = match ? '' : 'none';
            if (match) count++;
        });
        const sc = document.getElementById('shopCount');
        if (sc) sc.textContent = count;
        const allEl = document.getElementById('catCntAll');
        if (allEl && !type) allEl.textContent = document.querySelectorAll('#shopsGrid .shop-card').length;
        if (grid) grid.style.opacity = '1';
    }, 180);
}

/* ══════════════════════════════════════════
   DRAWER MESSAGES
══════════════════════════════════════════ */
function openMsgDrawer() {
    document.getElementById('msgDrawer').classList.add('open');
    document.getElementById('msgOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeMsgDrawer() {
    document.getElementById('msgDrawer').classList.remove('open');
    document.getElementById('msgOverlay').classList.remove('open');
    document.body.style.overflow = '';
}
