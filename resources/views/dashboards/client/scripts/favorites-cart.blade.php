/* ══════════════════════════════════════════
   FAVORIS
══════════════════════════════════════════ */
const _favRouteBase  = '{{ route("client.favorites.index") }}';
const _favToggleBase = '{{ url("/client/favorites") }}';
let   _favoriteIds   = new Set({{ json_encode($favoriteIds) }});

function openFavDrawer() {
    loadFavList();
    document.getElementById('favDrawer').classList.add('open');
    document.getElementById('favOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeFavDrawer() {
    document.getElementById('favDrawer').classList.remove('open');
    document.getElementById('favOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

function toggleFavorite(shopId, btn) {
    // Feedback visuel immédiat (optimistic UI)
    btn.style.opacity = '0.5';
    btn.disabled = true;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    fetch(`${_favToggleBase}/${shopId}/toggle`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' }
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        if (data.favorited) {
            _favoriteIds.add(shopId);
        } else {
            _favoriteIds.delete(shopId);
        }
        // Met à jour TOUS les boutons cœur liés à cette boutique
        document.querySelectorAll(`[data-shop-id="${shopId}"]`).forEach(b => {
            if (b.classList.contains('top-shop-heart') || b.classList.contains('shop-card-fav-btn')) {
                b.disabled = false;
                b.style.opacity = '';
                if (data.favorited) {
                    b.classList.add('favorited');
                    b.title = 'Retirer des favoris';
                } else {
                    b.classList.remove('favorited');
                    b.title = 'Ajouter aux favoris';
                }
            }
        });
        // Badge navbar
        const badge = document.getElementById('navFavBadge');
        if (badge) {
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.classList.add('show');
            } else {
                badge.textContent = '';
                badge.classList.remove('show');
            }
        }
        // Sous-titre drawer
        const sub = document.getElementById('favDrawerSub');
        if (sub) sub.textContent = data.count + ' boutique' + (data.count !== 1 ? 's' : '') + ' sauvegardée' + (data.count !== 1 ? 's' : '');
        // Footer
        const footer = document.getElementById('favDrawerFooter');
        if (footer) footer.style.display = data.count === 0 ? 'none' : '';
        // Si le drawer est ouvert, rafraîchir
        if (document.getElementById('favDrawer').classList.contains('open')) {
            loadFavList();
        }
    })
    .catch(err => {
        console.error('Erreur favoris:', err);
        btn.style.opacity = '';
        btn.disabled = false;
    });
}

/* Favoris PRODUITS (carrousel "Recommandé pour vous") — même logique que toggleFavorite() mais pour un produit */
function toggleProductFavorite(productId, btn) {
    // Mise à jour OPTIMISTE : le cœur change tout de suite, sans attendre le serveur
    // (la requête part en arrière-plan). On rétablit l'état d'origine seulement en cas d'échec.
    const cards = document.querySelectorAll(`.reco-card-fav[data-product-id="${productId}"]`);
    const wasFavorited = btn.classList.contains('favorited');
    const nowFavorited = !wasFavorited;

    cards.forEach(b => {
        b.classList.toggle('favorited', nowFavorited);
        b.title = nowFavorited ? 'Retirer des favoris' : 'Ajouter aux favoris';
        b.disabled = true;
    });

    const navBadge = document.getElementById('navFavBadge');
    const prevBadgeCount = navBadge ? (parseInt(navBadge.textContent, 10) || 0) : 0;
    if (navBadge) {
        const optimisticCount = Math.max(0, prevBadgeCount + (nowFavorited ? 1 : -1));
        navBadge.textContent = optimisticCount > 0 ? optimisticCount : '';
        navBadge.classList.toggle('show', optimisticCount > 0);
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    fetch(`/client/products/${productId}/favorite`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' }
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        // Recale sur la vraie réponse du serveur (par sécurité) et réactive les boutons
        cards.forEach(b => {
            b.disabled = false;
            b.classList.toggle('favorited', data.favorited);
            b.title = data.favorited ? 'Retirer des favoris' : 'Ajouter aux favoris';
        });
        if (navBadge) {
            navBadge.textContent = data.count > 0 ? data.count : '';
            navBadge.classList.toggle('show', data.count > 0);
        }
    })
    .catch(err => {
        console.error('Erreur favoris produit:', err);
        // Échec réseau → on annule la mise à jour optimiste
        cards.forEach(b => {
            b.disabled = false;
            b.classList.toggle('favorited', wasFavorited);
            b.title = wasFavorited ? 'Retirer des favoris' : 'Ajouter aux favoris';
        });
        if (navBadge) {
            navBadge.textContent = prevBadgeCount > 0 ? prevBadgeCount : '';
            navBadge.classList.toggle('show', prevBadgeCount > 0);
        }
    });
}

/* ══════════════════════════════════════════
   AJOUTER AU PANIER (depuis une carte produit)
══════════════════════════════════════════ */
function addToCart(productId, btn) {
    if (btn.disabled) return;
    btn.disabled = true;
    const originalHtml = btn.innerHTML;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    fetch(`{{ url('/client/cart/add') }}/${productId}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ quantity: 1 })
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (!ok) throw new Error(data.message || 'Erreur');
        btn.classList.add('added');
        btn.innerHTML = '✓ Ajouté';
        const navBadge = document.getElementById('navCartBadge');
        if (navBadge) {
            navBadge.textContent = data.count > 0 ? data.count : '';
            navBadge.classList.toggle('show', data.count > 0);
        }
        setTimeout(() => {
            btn.classList.remove('added');
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }, 1600);
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}
