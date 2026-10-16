{{--
    Guide de la photo principale (formulaires Ajouter / Modifier un produit).
    Les cartes produit recadrent la photo pour remplir leur cadre (object-fit: cover) :
      - accueil : cadre paysage ≈ 3:2 (welcome.blade.php : .prod-card-img, .reco-card-img, .flash-card-img) ;
      - vitrine de la boutique : cadre presque carré (public/shops/products : .amz-card-img) ;
      - fiche produit : cadre carré (create_from_product : .pp-stage).
    Format qui marche partout : photo carrée, produit dans la bande centrale (2/3 de la hauteur).
    API pour la page : window.pigCheck(url) après le choix d'une photo, window.pigReset() à la suppression.
    « Adapter automatiquement » recompose la photo en carré 1200 × 1200 (produit entier dans la
    zone visible de l'accueil) puis l'envoie avec uploadMainImage(file) de la page.
--}}
<div class="pig" id="pig" aria-live="polite">
    <div class="pig-head">
        <div class="pig-rule" aria-hidden="true">
            <span class="pig-rule-cut"></span><span class="pig-rule-safe"></span><span class="pig-rule-cut"></span>
        </div>
        <div class="pig-head-txt">
            <strong>Format conseillé : photo carrée, 1200 × 1200 px</strong>
            <span>Minimum 800 × 800 px. Produit bien centré, sur un fond clair et uni. Sur l'accueil, la photo est affichée en largeur : le haut et le bas (environ 1/6 chacun) sont coupés. Gardez le produit dans la bande centrale.</span>
        </div>
    </div>

    <div class="pig-result" id="pigResult" hidden>
        <p class="pig-status" id="pigStatus"></p>
        <div class="pig-frames">
            <figure class="pig-frame">
                <div class="pig-box pig-box-home"><img alt="" id="pigImgHome"></div>
                <figcaption>Accueil</figcaption>
            </figure>
            <figure class="pig-frame">
                <div class="pig-box pig-box-shop"><img alt="" id="pigImgShop"></div>
                <figcaption>Vitrine</figcaption>
            </figure>
            <figure class="pig-frame">
                <div class="pig-box pig-box-page"><img alt="" id="pigImgPage"></div>
                <figcaption>Fiche produit</figcaption>
            </figure>
        </div>
        <div class="pig-actions" id="pigActions" hidden>
            <button type="button" class="pig-btn" id="pigFix">Adapter automatiquement</button>
            <span class="pig-actions-hint">Place tout le produit dans la zone visible, sur un carré de 1200 × 1200 px, avec un fond de la couleur des bords de la photo.</span>
        </div>
    </div>
</div>

<style>
.pig { margin-top: 16px; border-radius: 16px; border: 1px solid var(--border, #E3E8F2); background: var(--surface, #fff); padding: 14px 16px; display: flex; flex-direction: column; gap: 14px; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
.pig-head { display: flex; gap: 14px; align-items: flex-start; }
.pig-head-txt { display: flex; flex-direction: column; gap: 3px; font-size: 13px; line-height: 1.5; color: var(--text-2, #45455E); max-width: 70ch; }
.pig-head-txt strong { font-size: 14px; color: var(--text, #16162A); }
/* Schéma : carré avec la bande visible sur l'accueil */
.pig-rule { width: 54px; height: 54px; border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; flex: none; border: 1.5px solid #1F7CF6; }
.pig-rule-cut { flex: 1; background: repeating-linear-gradient(135deg, rgba(31,124,246,.10) 0 4px, rgba(31,124,246,.28) 4px 6px); }
.pig-rule-safe { flex: 4; background: #EAF2FF; border-block: 1.5px dashed #1F7CF6; }
.pig-status { margin: 0; font-size: 13px; font-weight: 700; padding: 8px 12px; border-radius: 12px; line-height: 1.45; }
.pig-status.is-ok { background: #E7F6EC; color: #15803D; }
.pig-status.is-warn { background: #FEF3E0; color: #92400E; }
.pig-frames { display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end; }
.pig-frame { margin: 0; display: flex; flex-direction: column; gap: 6px; align-items: center; }
.pig-frame figcaption { font-size: 12px; font-weight: 700; color: var(--muted, #62627C); }
.pig-box { background: #F1F5FD; border-radius: 10px; overflow: hidden; box-shadow: 0 0 0 1px var(--border, #E3E8F2); }
.pig-box img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pig-box-home { width: 150px; height: 100px; }   /* ≈ 3:2, comme les cartes de l'accueil */
.pig-box-shop { width: 112px; height: 108px; }   /* presque carré, comme la vitrine */
.pig-box-page { width: 108px; height: 108px; }   /* carré, comme la fiche produit */
.pig-actions { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
.pig-actions-hint { font-size: 12px; color: var(--muted, #62627C); flex: 1 1 220px; line-height: 1.45; }
.pig-btn { min-height: 40px; padding: 0 16px; border-radius: 999px; border: 0; background: #1668D8; color: #fff; font: 700 13px 'Plus Jakarta Sans', system-ui, sans-serif; cursor: pointer; }
.pig-btn:hover { background: #1260C9; }
.pig-btn:disabled { opacity: .6; cursor: default; }
.pig-btn:focus-visible { outline: 3px solid #1F7CF6; outline-offset: 2px; }
[data-theme="dark"] .pig-rule-safe { background: #13284B; }
[data-theme="dark"] .pig-box { background: #13284B; }
[data-theme="dark"] .pig-status.is-ok { background: #0F2A1C; color: #4ADE80; }
[data-theme="dark"] .pig-status.is-warn { background: #2E2208; color: #FBBF24; }
@media (max-width: 480px) { .pig-box-home { width: 132px; height: 88px; } .pig-box-shop, .pig-box-page { width: 92px; height: 92px; } }
</style>

<script>
(function () {
    var HOME_RATIO = 1.5, MIN_SIDE = 800, OUT = 1200;
    var result = document.getElementById('pigResult'), status = document.getElementById('pigStatus');
    var actions = document.getElementById('pigActions'), fixBtn = document.getElementById('pigFix');
    var imgs = ['pigImgHome', 'pigImgShop', 'pigImgPage'].map(function (id) { return document.getElementById(id); });
    var current = null;

    function pct(x) { return Math.round(x * 100); }

    window.pigReset = function () { current = null; result.hidden = true; };

    var ownUrl = null;
    window.pigCheck = function (url) {
        if (!url) return window.pigReset();
        // Image locale (blob) : la page la libère après l'envoi, on en garde une copie
        if (url.indexOf('blob:') === 0) {
            fetch(url).then(function (r) { return r.blob(); }).then(function (b) {
                if (ownUrl) URL.revokeObjectURL(ownUrl);
                ownUrl = URL.createObjectURL(b);
                analyse(ownUrl);
            }).catch(window.pigReset);
            return;
        }
        analyse(url);
    };

    function analyse(url) {
        var img = new Image();
        img.onload = function () {
            current = img;
            var w = img.naturalWidth, h = img.naturalHeight, r = w / h, notes = [];

            if (Math.min(w, h) < MIN_SIDE) notes.push('Photo petite (' + w + ' × ' + h + ' px) : elle risque d’être floue. Utilisez au moins 800 × 800 px.');
            if (r < 0.95) {
                notes.push('Photo en hauteur : sur l’accueil, ' + pct(1 - r / HOME_RATIO) + ' % de la hauteur est coupé (haut et bas).');
            } else if (r > 1.15) {
                notes.push('Photo en largeur : sur la fiche produit et la vitrine, ' + pct(1 - 1 / r) + ' % de la largeur est coupé (gauche et droite).');
            }

            status.className = 'pig-status ' + (notes.length ? 'is-warn' : 'is-ok');
            status.textContent = notes.length ? notes.join(' ') : 'Format adapté (' + w + ' × ' + h + ' px). Vérifiez sur l’aperçu « Accueil » que le produit n’est pas coupé en haut ou en bas.';
            imgs.forEach(function (el) { el.src = url; });
            // Le bouton reste proposé : même une photo carrée peut avoir un produit qui touche les bords
            actions.hidden = false;
            result.hidden = false;
        };
        img.onerror = window.pigReset;
        img.src = url;
    }

    /* Couleur moyenne des bords de la photo : sert de fond autour du produit */
    function edgeColor(img) {
        var c = document.createElement('canvas'), s = 40;
        c.width = s; c.height = s;
        var x = c.getContext('2d');
        x.drawImage(img, 0, 0, s, s);
        var d = x.getImageData(0, 0, s, s).data, r = 0, g = 0, b = 0, n = 0;
        for (var i = 0; i < s; i++) {
            [[i, 0], [i, s - 1], [0, i], [s - 1, i]].forEach(function (p) {
                var k = (p[1] * s + p[0]) * 4; r += d[k]; g += d[k + 1]; b += d[k + 2]; n++;
            });
        }
        return 'rgb(' + Math.round(r / n) + ',' + Math.round(g / n) + ',' + Math.round(b / n) + ')';
    }

    fixBtn.addEventListener('click', function () {
        if (!current || typeof window.uploadMainImage !== 'function') return;
        var w = current.naturalWidth, h = current.naturalHeight;
        // Zone sûre : toute la largeur et les 2/3 centraux de la hauteur (visibles sur l'accueil)
        var safeW = OUT * 0.94, safeH = OUT / HOME_RATIO * 0.94;
        var k = Math.min(safeW / w, safeH / h);
        var dw = Math.round(w * k), dh = Math.round(h * k);
        var c = document.createElement('canvas');
        c.width = OUT; c.height = OUT;
        var x = c.getContext('2d');
        try {
            x.fillStyle = edgeColor(current);
            x.fillRect(0, 0, OUT, OUT);
            x.imageSmoothingQuality = 'high';
            x.drawImage(current, Math.round((OUT - dw) / 2), Math.round((OUT - dh) / 2), dw, dh);
        } catch (e) {
            status.className = 'pig-status is-warn';
            status.textContent = 'Impossible d’adapter cette photo ici. Choisissez-la à nouveau depuis votre appareil.';
            return;
        }
        fixBtn.disabled = true;
        c.toBlob(function (blob) {
            fixBtn.disabled = false;
            if (!blob) return;
            window.uploadMainImage(new File([blob], 'photo-adaptee.jpg', { type: 'image/jpeg' }));
        }, 'image/jpeg', 0.9);
    });
})();
</script>
