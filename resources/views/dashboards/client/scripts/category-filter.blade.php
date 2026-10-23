/* ══════════════════════════════════════════
   FILTRE PAR CATÉGORIE (sidebar + pop-cats)
══════════════════════════════════════════ */
/* ── Toggle catégories pop-cats ── */
function togglePopCats(btn) {
    const extras = document.querySelectorAll('.pop-cat-extra');
    const row    = document.getElementById('popCatsRow');
    const open   = extras.length > 0 && extras[0].style.display !== 'none';
    if (open) {
        extras.forEach(el => el.style.display = 'none');
        btn.innerHTML = '＋ ' + btn.dataset.count + ' catégories';
    } else {
        extras.forEach(el => el.style.display = '');
        btn.innerHTML = '✕ Réduire';
        setTimeout(() => row.scrollLeft = row.scrollWidth, 50);
    }
}

function filterByCat(type) {
    /* Trouver la pill correspondante et déléguer à filterByType */
    let pill;
    if (!type) {
        pill = document.querySelector('#catFilter .cat-pill');
    } else {
        pill = document.querySelector(`#catFilter .cat-pill[data-type-val="${type.replace(/"/g,'&quot;')}"]`);
    }
    if (pill) filterByType(type, pill);

    /* Sync états actifs sidebar + pop-cats + pop-cat-chips */
    document.querySelectorAll('.sb-cat-item').forEach(el => {
        el.classList.toggle('active', el.dataset.catType === type);
    });
    document.querySelectorAll('.pop-cat-chip').forEach(el => {
        el.classList.toggle('active', el.dataset.catType === type);
    });
    document.querySelectorAll('.pop-cat-card').forEach(el => {
        el.classList.toggle('active', el.dataset.catType === type);
    });

    /* Scroll vers la grille */
    const boutiques = document.getElementById('boutiques');
    if (boutiques) boutiques.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/* Animation d'entrée gérée en CSS (cardFadeIn) — plus performante que JS */
