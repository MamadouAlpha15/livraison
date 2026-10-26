/* ══════════════════════════════════════════════════════════
   Carrousels auto-défilants (Recommandé / Ventes Flash)
   — Le défilement automatique avance via scrollLeft (scroll natif),
     donc l'utilisateur peut glisser librement au doigt/à la souris
     À TOUT MOMENT, y compris pendant le défilement automatique.
   — Dès qu'on relâche, l'auto-défilement reprend là où on l'a laissé
     (aucun saut, aucune réinitialisation de position).
   — Boucle infinie : le contenu est dupliqué x2, on revient au début
     dès qu'on atteint la moitié de la largeur totale.
   ══════════════════════════════════════════════════════════ */
function initAutoScrollCarousel(outer, row, autoClass, pxPerSecond) {
    if (!outer || !row || !row.classList.contains(autoClass)) return;

    let paused = false;
    let resumeTimeout = null;
    let lastTs = null;
    // scrollWidth est calculé UNE SEULE FOIS (pas à chaque frame) : lire cette propriété force
    // le navigateur à recalculer la mise en page, ce qui serait coûteux répété 60 fois/seconde.
    // Les cartes ont une largeur fixe en CSS, donc cette valeur ne bouge pas toute seule.
    let half = outer.scrollWidth / 2;
    window.addEventListener('resize', () => { half = outer.scrollWidth / 2; });

    function wrap() {
        if (half <= 0) return;
        if (outer.scrollLeft >= half) outer.scrollLeft -= half;
        else if (outer.scrollLeft < 0) outer.scrollLeft += half;
    }

    function frame(ts) {
        if (!paused) {
            const dt = lastTs ? (ts - lastTs) / 1000 : 0;
            outer.scrollLeft += pxPerSecond * dt;
            wrap();
        }
        lastTs = ts;
        requestAnimationFrame(frame);
    }

    function pause() {
        paused = true;
        if (resumeTimeout) clearTimeout(resumeTimeout);
    }
    function resumeLater(delay) {
        if (resumeTimeout) clearTimeout(resumeTimeout);
        resumeTimeout = setTimeout(() => { paused = false; lastTs = null; }, delay);
    }

    // Glissement manuel (souris/doigt) : on met en pause, puis on reprend après le relâchement
    outer.addEventListener('pointerdown', pause);
    outer.addEventListener('pointerup', () => resumeLater(1800));
    outer.addEventListener('pointercancel', () => resumeLater(1800));
    outer.addEventListener('touchstart', pause, { passive: true });
    outer.addEventListener('touchend', () => resumeLater(1800), { passive: true });
    // mouseenter/mouseleave = uniquement pour un vrai curseur de souris (desktop).
    // Sur iOS Safari, un simple effleurement peut déclencher un mouseenter "fantôme"
    // sans jamais déclencher le mouseleave correspondant (pas de curseur qui "part" sur
    // écran tactile) → le carrousel restait en pause pour toujours après le premier contact.
    if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        outer.addEventListener('mouseenter', pause);
        outer.addEventListener('mouseleave', () => resumeLater(400));
    }
    // Si l'utilisateur scrolle manuellement sans passer par pointerdown (molette, trackpad…)
    outer.addEventListener('wheel', () => { pause(); resumeLater(1800); }, { passive: true });
    // Sécurité : si le scroll manuel dépasse la boucle, on la reboucle aussi
    outer.addEventListener('scroll', wrap);

    requestAnimationFrame(frame);
}

(function () {
    const recoOuter = document.getElementById('recoRowOuter');
    const recoRow   = document.getElementById('recoRow');
    if (recoRow && recoRow.classList.contains('reco-auto')) {
        const count = recoRow.querySelectorAll('.reco-card').length / 2;
        const duration = Math.max(18, Math.round(count * 4)); // secondes pour un tour complet
        const speed = (recoOuter.scrollWidth / 2) / duration;
        initAutoScrollCarousel(recoOuter, recoRow, 'reco-auto', speed);
    }

    const flashOuter = document.getElementById('flashRowOuter');
    const flashRow   = document.getElementById('flashRow');
    if (flashRow && flashRow.classList.contains('flash-auto')) {
        const count = flashRow.querySelectorAll('.flash-card').length / 2;
        const duration = Math.max(16, Math.round(count * 3.5));
        const speed = (flashOuter.scrollWidth / 2) / duration;
        initAutoScrollCarousel(flashOuter, flashRow, 'flash-auto', speed);
    }
})();

/* Comptes à rebours des ventes flash (mis à jour chaque seconde) */
(function () {
    const cards = document.querySelectorAll('.flash-card-countdown[data-ends]');
    if (!cards.length) return;
    function tick() {
        cards.forEach(card => {
            const endsAt = parseInt(card.dataset.ends, 10) * 1000;
            const el = card.querySelector('.flash-cd-val');
            if (!el) return;
            const diff = endsAt - Date.now();
            if (diff <= 0) { el.textContent = 'Terminée'; return; }
            const d = Math.floor(diff / 86400000);
            const h = Math.floor((diff % 86400000) / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            const pad = n => String(n).padStart(2, '0');
            el.textContent = (d > 0 ? d + 'j ' : '') + pad(h) + ':' + pad(m) + ':' + pad(s);
        });
    }
    tick();
    setInterval(tick, 1000);
})();

function loadFavList() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    fetch(_favRouteBase, { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
        const container = document.getElementById('favShopsContainer');
        const empty     = document.getElementById('favEmptyState');
        const footer    = document.getElementById('favDrawerFooter');
        if (!container) return;
        container.innerHTML = '';
        if (!data.shops || data.shops.length === 0) {
            empty.style.display = '';
            if (footer) footer.style.display = 'none';
            return;
        }
        empty.style.display = 'none';
        if (footer) footer.style.display = '';
        data.shops.forEach(s => {
            const shopUrl = `{{ url('/client/shops') }}/${s.id}`;
            const imgHtml = s.image
                ? `<img src="/storage/${escHtml(s.image)}" alt="${escHtml(s.name)}" loading="lazy">`
                : `<div class="fav-shop-img-ph" style="background:linear-gradient(135deg,#F6F9FF,#EEF2F8)"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#62627C" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path d="M3 9l2-6h14l2 6"/><line x1="12" y1="9" x2="12" y2="21"/></svg></div>`;
            const card = document.createElement('div');
            card.style.cssText = 'display:flex;position:relative';
            card.innerHTML = `
                <a href="${shopUrl}" class="fav-shop-card" style="flex:1;text-decoration:none;color:inherit">
                    <div class="fav-shop-img">${imgHtml}</div>
                    <div class="fav-shop-info">
                        ${s.type ? `<div class="fav-shop-type">${escHtml(s.type)}</div>` : ''}
                        <div class="fav-shop-name">${escHtml(s.name)}</div>
                        <div class="fav-shop-meta">
                            ${s.products_count > 0 ? `<span class="fav-shop-chip"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg> ${s.products_count} produit${s.products_count > 1 ? 's' : ''}</span>` : ''}
                            ${s.sales_count > 0 ? `<span class="fav-shop-chip"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg> +${s.sales_count} ventes</span>` : ''}
                        </div>
                    </div>
                </a>
                <button class="fav-shop-rm" onclick="toggleFavorite(${parseInt(s.id, 10)}, this)" title="Retirer des favoris">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>`;
            container.appendChild(card);
        });
    })
    .catch(() => {});
}
