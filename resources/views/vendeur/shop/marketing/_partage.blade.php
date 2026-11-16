{{--
    Marketing → Partage (ex-« Boutique → Partage ») : lien d'un produit prêt pour WhatsApp, QR code (boutique,
    catégorie ou produit) en PNG et affiche A5, liens par réseau avec leurs visites et commandes des 30 derniers
    jours (chiffres réels), liens de campagne avec remise (un vrai code promo, une date de fin).
    Variables : $shop, $publicUrl, $host, $products, $categoryLinks, $tracked, $promoLinks, $isPro, $logoUrl, $initials,
                $channels, $srcColors, $ico (marketing.blade.php)
--}}
<style>
.mkp-picks { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 8px; }
.mkp-pick { position: relative; aspect-ratio: 1; border: 0; padding: 0; border-radius: 14px; overflow: hidden; cursor: pointer; background: var(--sv-b50); color: var(--sv-btn); font-weight: 800; box-shadow: inset 0 0 0 1px var(--border); }
.mkp-pick[aria-pressed="true"] { box-shadow: 0 0 0 3px var(--surface), 0 0 0 5px var(--sv-blue); }
.mkp-pick img { width: 100%; height: 100%; object-fit: cover; display: block; }
.mkp-wa { background: #E5DDD5; border-radius: 18px; padding: 14px; }
.mkp-bubble { max-width: 340px; margin-left: auto; background: #D9FDD3; border-radius: 12px 4px 12px 12px; padding: 4px; color: #111B21; }
.mkp-card { background: #F0F2F5; border-radius: 8px; overflow: hidden; }
.mkp-img { position: relative; aspect-ratio: 1.91; background: #0B1E3D; }
.mkp-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.mkp-logo { position: absolute; left: 10px; bottom: 10px; width: 40px; height: 40px; border-radius: 50%; border: 3px solid #fff; overflow: hidden; background: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; }
.mkp-logo img { width: 100%; height: 100%; object-fit: cover; }
.mkp-price { position: absolute; right: 10px; bottom: 10px; padding: 5px 10px; border-radius: 999px; background: #fff; font-size: 12.5px; font-weight: 800; }
.mkp-txt { padding: 8px 10px 10px; } .mkp-txt b { display: block; font-size: 13.5px; } .mkp-txt span { display: block; font-size: 12.5px; color: #54656F; } .mkp-txt small { color: #8696A0; font-size: 11.5px; }
.mkp-msg { padding: 6px 8px 2px; font-size: 13.5px; white-space: pre-line; overflow-wrap: anywhere; }
.mkp-seg { display: flex; padding: 4px; gap: 2px; border-radius: 999px; background: var(--sv-b50); }
.mkp-seg button { flex: 1; min-height: 38px; border: 0; border-radius: 999px; background: transparent; font: 700 13px var(--font); color: var(--text-2); cursor: pointer; }
.mkp-seg button[aria-pressed="true"] { background: var(--surface); color: var(--sv-btn); box-shadow: 0 2px 6px rgba(11,30,61,.12); }
.mkp-seg button:disabled { opacity: .45; cursor: default; }
.mkp-qr { display: flex; gap: 18px; align-items: center; flex-wrap: wrap; }
.mkp-qr-box { position: relative; width: 200px; height: 200px; flex-shrink: 0; padding: 12px; border-radius: 22px; background: #fff; box-shadow: 0 20px 40px -20px rgba(11,30,61,.5), 0 0 0 1px var(--border); }
.mkp-qr-box img { width: 100%; height: 100%; display: block; }
.mkp-qr-logo { position: absolute; left: 50%; top: 50%; width: 22%; height: 22%; transform: translate(-50%, -50%); border-radius: 10px; border: 3px solid #fff; overflow: hidden; background: var(--sv-blue); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; }
.mkp-qr-logo img { width: 100%; height: 100%; object-fit: cover; }
.mkp-link { display: grid; grid-template-columns: 150px minmax(0, 1fr) auto auto; gap: 10px; align-items: center; }
.mkp-num { font-variant-numeric: tabular-nums; font-size: 12.5px; color: var(--muted); white-space: nowrap; text-align: right; }
.mkp-num b { color: var(--text); font-size: 14px; }
.mkp-camp { display: flex; flex-direction: column; gap: 8px; padding: 12px; border-radius: 18px; background: var(--bg); }
.mkp-camp-hd { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.mkp-code { font: 800 14px var(--font); letter-spacing: .06em; padding: 4px 10px; border-radius: 10px; background: var(--surface); box-shadow: inset 0 0 0 1.5px var(--border); }
.mkp-lock { display: flex; gap: 12px; align-items: flex-start; padding: 14px; border-radius: 18px; background: var(--sv-amb50); color: var(--sv-amb-ink); }
.mkp-form { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(0, .8fr) minmax(0, 1fr) auto; gap: 12px; align-items: end; }
@media (max-width: 760px) {
    .mkp-picks { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .mkp-link { grid-template-columns: minmax(0, 1fr) auto; }
    .mkp-link .mk-link-name { grid-column: 1 / -1; }
    .mkp-form { grid-template-columns: minmax(0, 1fr); }
}
</style>

<div class="mk-grid">
    {{-- ══ Lien d'un produit ══ --}}
    <section class="mk-card" aria-labelledby="mkpPLH">
        <div class="mk-card-head"><span class="mk-card-ico">{!! $ico('M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1') !!}</span><div><h2 class="mk-card-title" id="mkpPLH">Lien d’un produit</h2><p class="mk-card-sub">Choisissez un produit : le lien, le texte et l’aperçu WhatsApp sont prêts.</p></div></div>
        @if($products->isEmpty())
        <div class="mk-empty"><strong>Aucun produit en ligne pour l’instant</strong><a href="{{ route('products.create') }}" style="color:var(--sv-btn);font-weight:700">Ajouter un produit</a></div>
        @else
        <div class="mkp-picks" role="group" aria-label="Produit à partager" id="mkpPicks"></div>
        <label class="mk-field-lbl" for="mkpPickAll">Autre produit</label>
        <select class="mk-select" id="mkpPickAll"><option value="">Choisir…</option>@foreach($products as $p)<option value="{{ $p['id'] }}">{{ $p['name'] }} · {{ $p['priceLabel'] }}</option>@endforeach</select>
        <div class="mk-copyrow"><input class="mk-url" id="mkpPLink" readonly aria-label="Lien du produit"><button type="button" class="mk-btn mk-btn-blue mk-btn-sm" data-copy="#mkpPLink">Copier</button></div>
        <div class="mk-row"><button type="button" class="mk-btn mk-btn-line mk-btn-sm" id="mkpPQr">QR code de ce produit</button><a class="mk-btn mk-btn-blue mk-btn-sm" id="mkpPWa" target="_blank" rel="noopener" style="background:#15803D">Partager sur WhatsApp</a></div>
        <label class="mk-field-lbl" for="mkpPText">Texte de partage prêt</label>
        <textarea class="mk-select" id="mkpPText" rows="4" style="padding:10px 14px;min-height:96px"></textarea>
        <div class="mkp-wa" aria-label="Aperçu du message WhatsApp"><div class="mkp-bubble" id="mkpPBubble"></div></div>
        @endif
    </section>

    {{-- ══ QR code et affiche ══ --}}
    <section class="mk-card" id="qr" aria-labelledby="mkpQrH">
        <div class="mk-card-head"><span class="mk-card-ico">{!! $ico('M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h2v2h-2zM18 18h2v2h-2zM18 14h2M14 18v2') !!}</span><div><h2 class="mk-card-title" id="mkpQrH">QR code et affiche</h2><p class="mk-card-sub">Les visites venues du QR code apparaissent sous « QR code » dans vos statistiques.</p></div></div>
        <div class="mkp-seg" role="group" aria-label="Cible du QR code" id="mkpQrSeg"><button type="button" data-v="shop" aria-pressed="true">Boutique</button><button type="button" data-v="cat" aria-pressed="false" @if(!count($categoryLinks)) disabled @endif>Catégorie</button><button type="button" data-v="product" aria-pressed="false" @if($products->isEmpty()) disabled @endif>Produit</button></div>
        <div id="mkpQrCat" hidden><label class="mk-field-lbl" for="mkpQrCatSel">Catégorie</label><select class="mk-select" id="mkpQrCatSel">@foreach($categoryLinks as $c)<option value="{{ $c['slug'] }}">{{ $c['name'] }} ({{ $c['count'] }})</option>@endforeach</select></div>
        <div id="mkpQrProd" hidden><label class="mk-field-lbl" for="mkpQrProdSel">Produit</label><select class="mk-select" id="mkpQrProdSel">@foreach($products as $p)<option value="{{ $p['id'] }}">{{ $p['name'] }}</option>@endforeach</select></div>
        <div class="mkp-qr">
            <div class="mkp-qr-box"><img id="mkpQrImg" alt="QR code" width="176" height="176"><span class="mkp-qr-logo" aria-hidden="true">@if($logoUrl)<img src="{{ $logoUrl }}" alt="">@else{{ $initials }}@endif</span></div>
            <div style="flex:1 1 200px;display:flex;flex-direction:column;gap:10px">
                <p class="mk-note" id="mkpQrHint">Mène à votre boutique, toujours à la version en ligne de la page choisie.</p>
                <div class="mk-row"><button type="button" class="mk-btn mk-btn-blue mk-btn-sm" id="mkpQrPng">Télécharger (PNG)</button><a class="mk-btn mk-btn-line mk-btn-sm" id="mkpQrPoster" target="_blank" rel="noopener">Affiche A5</a></div>
            </div>
        </div>
    </section>
</div>

{{-- ══ Liens par réseau (avec visites et commandes réelles des 30 derniers jours) ══ --}}
<section class="mk-card" id="liens" aria-labelledby="mkpTrH">
    <div class="mk-card-head"><span class="mk-card-ico">{!! $ico('M5 21V4M5 4h11l-2 4 2 4H5') !!}</span><div><h2 class="mk-card-title" id="mkpTrH">Liens de suivi par réseau</h2><p class="mk-card-sub">Un lien différent sur chaque réseau : ils mènent à la même page, et chaque visite et chaque commande est rattachée au bon réseau. Chiffres des 30 derniers jours, aucun n’est inventé.</p></div></div>
    <label class="mk-field-lbl" for="mkTarget">Page à partager</label>
    <select class="mk-select" id="mkTarget">
        <option value="{{ $publicUrl }}">Ma vitrine (tous les produits)</option>
        @if(count($categoryLinks))<optgroup label="Une catégorie">@foreach($categoryLinks as $c)<option value="{{ $c['url'] }}">{{ $c['name'] }} ({{ $c['count'] }})</option>@endforeach</optgroup>@endif
        @if($products->isNotEmpty())<optgroup label="Un produit">@foreach($products as $p)<option value="{{ $p['url'] }}">{{ $p['name'] }}</option>@endforeach</optgroup>@endif
    </select>
    <ul class="mk-links">
        @foreach($tracked as $t)
        @php [$cname, $hint] = $channels[$t['source']] ?? [$t['label'], '']; @endphp
        <li class="mkp-link">
            <span class="mk-link-name"><b><span class="mk-dot" style="--c:{{ $srcColors[$t['source']] ?? '#94A3B8' }}"></span>{{ $cname }}</b><small>{{ $hint }}</small></span>
            <input type="text" class="mk-url" id="mkLink-{{ $t['source'] }}" data-channel="{{ $t['source'] }}" data-medium="{{ $t['source'] === 'qr' ? 'print' : 'social' }}" value="{{ $t['url'] }}" readonly aria-label="Lien de suivi {{ $cname }}">
            <span class="mkp-num"><b>{{ number_format($t['visits'], 0, ',', ' ') }}</b> visite{{ $t['visits'] > 1 ? 's' : '' }} · <b>{{ number_format($t['orders'], 0, ',', ' ') }}</b> commande{{ $t['orders'] > 1 ? 's' : '' }}</span>
            <span class="mk-link-actions">
                <button type="button" class="mk-btn mk-btn-line mk-btn-sm" data-copy="#mkLink-{{ $t['source'] }}">Copier</button>
                @if($t['source'] === 'whatsapp')<a class="mk-btn mk-btn-blue mk-btn-sm" id="mkWa" target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode('Découvrez ma boutique ' . $shop->name . ' sur Shopio : ' . $t['url']) }}">Envoyer</a>@endif
            </span>
        </li>
        @endforeach
    </ul>
</section>

{{-- ══ Lien de campagne avec remise ══ --}}
<section class="mk-card" id="campagne" aria-labelledby="mkpCaH">
    <div class="mk-card-head"><span class="mk-card-ico">{!! $ico('M20 12l-8 8-9-9V3h8zM7.5 7.5h.01') !!}</span><div><h2 class="mk-card-title" id="mkpCaH">Lien de campagne avec remise</h2><p class="mk-card-sub">Une remise, une date de fin : Shopio crée un vrai code promo et un lien vers la catégorie choisie. Le code s’applique à toute la boutique.</p></div></div>
    @if(!$isPro)
    <div class="mkp-lock"><div><b>Réservé au plan Pro</b><p class="mk-note" style="color:inherit">Les liens de campagne utilisent les codes promo. <a href="{{ route('boutique.subscription.upgrade') }}">Passer au plan Pro</a></p></div></div>
    @else
    <form method="POST" action="{{ route('boutique.marketing.promo-link') }}" class="mkp-form" id="mkpCampForm" novalidate>
        @csrf
        <div class="mk-camp-field"><label class="mk-field-lbl" for="mkpCaCat">Catégorie</label><select class="mk-select" id="mkpCaCat" name="category"><option value="">Toute la boutique</option>@foreach($categoryLinks as $c)<option value="{{ $c['slug'] }}" @selected(old('category') === $c['slug'])>{{ $c['name'] }}</option>@endforeach</select></div>
        <div class="mk-camp-field"><label class="mk-field-lbl" for="mkpCaPct">Remise %</label><input class="mk-select" id="mkpCaPct" name="discount" type="number" min="1" max="90" inputmode="numeric" value="{{ old('discount', 10) }}" @error('discount') aria-invalid="true" @enderror></div>
        <div class="mk-camp-field"><label class="mk-field-lbl" for="mkpCaEnd">Date de fin</label><input class="mk-select" id="mkpCaEnd" name="ends" type="date" min="{{ now('Africa/Conakry')->toDateString() }}" value="{{ old('ends') }}" @error('ends') aria-invalid="true" @enderror></div>
        <button type="submit" class="mk-btn mk-btn-blue">Créer</button>
    </form>
    @endif
    @if($errors->any())<p class="mk-form-err" role="alert" id="mkpCaErr">{{ $errors->first() }}</p>@else<p class="mk-form-err" role="alert" id="mkpCaErr"></p>@endif
    @if(count($promoLinks))
    <div style="display:flex;flex-direction:column;gap:8px">
        @foreach($promoLinks as $c)
        <div class="mkp-camp">
            <div class="mkp-camp-hd">
                <span class="mkp-code">{{ $c['code'] }}</span>
                <b>−{{ $c['value'] }} · {{ $c['target'] }}</b>
                <span class="mk-chip {{ $c['state'] === 'Active' ? 'is-warn' : '' }}">{{ $c['state'] === 'Active' ? 'Expire le ' . $c['expires'] : $c['state'] }}</span>
                <span class="mk-chip is-code" style="margin-left:0">{{ $c['uses'] }} utilisation{{ $c['uses'] > 1 ? 's' : '' }}</span>
            </div>
            <div class="mk-copyrow"><input class="mk-url" id="mkpCa{{ $c['id'] }}" value="{{ $c['url'] }}" readonly aria-label="Lien de la campagne {{ $c['code'] }}"><button type="button" class="mk-btn mk-btn-blue mk-btn-sm" data-copy="#mkpCa{{ $c['id'] }}">Copier</button>
                <form method="POST" action="{{ route('boutique.marketing.promo-link.stop', $c['id']) }}" data-confirm="Arrêter cette campagne ? Le code {{ $c['code'] }} ne fonctionnera plus." style="margin:0;display:flex">@csrf<button type="submit" class="mk-btn mk-btn-ghost mk-btn-sm">Arrêter</button></form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</section>

@php
    $mkpData = [
        'products' => $products, 'currency' => $shop->currency ?? 'GNF', 'shop' => ['name' => $shop->name, 'logo' => $logoUrl, 'initials' => $initials, 'host' => $host],
        'urls' => ['qr' => route('boutique.marketing.qr'), 'poster' => route('boutique.marketing.poster')],
    ];
@endphp
@push('scripts')
<script>
(function () {
    'use strict';
    const G = @json($mkpData);
    const $ = id => document.getElementById(id);
    const esc = v => String(v == null ? '' : v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let qrTarget = 'shop';
    function paintQrSeg() { document.querySelectorAll('#mkpQrSeg button').forEach(b => b.setAttribute('aria-pressed', b.dataset.v === qrTarget ? 'true' : 'false')); }
    $('mkpQrSeg').addEventListener('click', e => { const b = e.target.closest('button'); if (!b || b.disabled) return; qrTarget = b.dataset.v; paintQrSeg(); renderQr(); });

    /* Lien d'un produit : lien suivi WhatsApp, texte prêt, aperçu */
    if ($('mkpPicks')) {
        let pid = G.products[0].id;
        const prod = () => G.products.find(p => p.id === pid);
        const tagged = (u, src) => u + (u.includes('?') ? '&' : '?') + 'utm_source=' + src + '&utm_medium=social';
        function renderPicks() {
            const first = G.products.slice(0, 6);
            if (!first.some(p => p.id === pid)) first[first.length - 1] = prod();
            $('mkpPicks').innerHTML = first.map(p => '<button type="button" class="mkp-pick" aria-pressed="' + (p.id === pid) + '" data-pid="' + p.id + '" aria-label="' + esc(p.name) + '" title="' + esc(p.name) + '">' + (p.thumb ? '<img src="' + esc(p.thumb) + '" alt="">' : esc(p.name.slice(0, 2).toUpperCase())) + '</button>').join('');
            const p = prod(), link = tagged(p.url, 'whatsapp');
            const text = p.name + ' — ' + p.priceLabel + '\nPaiement à la livraison.\nCommandez ici : ' + link;
            $('mkpPLink').value = link; $('mkpPText').value = text;
            $('mkpPWa').href = 'https://wa.me/?text=' + encodeURIComponent(text);
            $('mkpPBubble').innerHTML = '<div class="mkp-card"><div class="mkp-img">' + (p.photo ? '<img src="' + esc(p.photo) + '" alt="">' : '') + '<span class="mkp-logo">' + (G.shop.logo ? '<img src="' + esc(G.shop.logo) + '" alt="">' : esc(G.shop.initials)) + '</span><span class="mkp-price">' + esc(p.priceLabel) + '</span></div><div class="mkp-txt"><b>' + esc(p.name) + '</b><span>' + esc(G.shop.name) + ' · Paiement à la livraison</span><small>' + esc(G.shop.host.split('/')[0]) + '</small></div></div><div class="mkp-msg">' + esc(text) + '</div>';
            $('mkpPickAll').value = '';
        }
        $('mkpPicks').addEventListener('click', e => { const b = e.target.closest('[data-pid]'); if (b) { pid = +b.dataset.pid; renderPicks(); } });
        $('mkpPickAll').addEventListener('change', e => { if (e.target.value) { pid = +e.target.value; renderPicks(); } });
        $('mkpPText').addEventListener('input', e => { $('mkpPWa').href = 'https://wa.me/?text=' + encodeURIComponent(e.target.value); });
        $('mkpPQr').addEventListener('click', () => { qrTarget = 'product'; $('mkpQrProdSel').value = pid; paintQrSeg(); renderQr(); $('qr').scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' }); });
        renderPicks();
    }

    /* QR code : boutique, catégorie ou produit ; PNG avec le logo au centre ; affiche A5 */
    function qrQuery() {
        if (qrTarget === 'cat') return 'categorie=' + encodeURIComponent($('mkpQrCatSel').value);
        if (qrTarget === 'product') return 'produit=' + encodeURIComponent($('mkpQrProdSel').value);
        return '';
    }
    function renderQr() {
        $('mkpQrCat').hidden = qrTarget !== 'cat'; $('mkpQrProd').hidden = qrTarget !== 'product';
        const q = qrQuery();
        $('mkpQrImg').src = G.urls.qr + (q ? '?' + q : '');
        $('mkpQrPoster').href = G.urls.poster + (q ? '?' + q : '');
        const label = qrTarget === 'cat' ? 'la catégorie « ' + $('mkpQrCatSel').selectedOptions[0].text.replace(/ \(\d+\)$/, '') + ' »' : qrTarget === 'product' ? 'la fiche « ' + $('mkpQrProdSel').selectedOptions[0].text + ' »' : 'votre boutique';
        $('mkpQrHint').textContent = 'Mène à ' + label + ', toujours à la version en ligne de la page choisie.';
    }
    if ($('mkpQrCatSel')) $('mkpQrCatSel').addEventListener('change', renderQr);
    if ($('mkpQrProdSel')) $('mkpQrProdSel').addEventListener('change', renderQr);
    renderQr();
    $('mkpQrPng').addEventListener('click', async () => {
        try {
            const svgText = await (await fetch($('mkpQrImg').src)).text();
            const load = src => new Promise((ok, ko) => { const i = new Image(); i.onload = () => ok(i); i.onerror = ko; i.src = src; });
            const qr = await load(URL.createObjectURL(new Blob([svgText], { type: 'image/svg+xml' })));
            const S = 1024, pad = 64, cv = document.createElement('canvas'); cv.width = cv.height = S;
            const x = cv.getContext('2d');
            const box = (px, py, w, r) => { x.beginPath(); if (x.roundRect) x.roundRect(px, py, w, w, r); else x.rect(px, py, w, w); };
            x.fillStyle = '#fff'; x.fillRect(0, 0, S, S);
            x.drawImage(qr, pad, pad, S - pad * 2, S - pad * 2);
            const L = 190, lx = (S - L) / 2;
            x.fillStyle = '#fff'; box(lx - 12, lx - 12, L + 24, 36); x.fill();
            let drawn = false;
            if (G.shop.logo) { try { const lg = await load(G.shop.logo); x.save(); box(lx, lx, L, 28); x.clip(); x.drawImage(lg, lx, lx, L, L); x.restore(); drawn = true; } catch (e) { drawn = false; } }
            if (!drawn) { x.fillStyle = '#1F7CF6'; box(lx, lx, L, 28); x.fill(); x.fillStyle = '#fff'; x.font = '800 80px "Plus Jakarta Sans", sans-serif'; x.textAlign = 'center'; x.textBaseline = 'middle'; x.fillText(G.shop.initials, S / 2, S / 2 + 4); }
            const a = document.createElement('a');
            a.download = 'qr-' + ({ shop: 'boutique', cat: 'categorie', product: 'produit' })[qrTarget] + '.png';
            a.href = cv.toDataURL('image/png'); a.click();
        } catch (e) { alert('Téléchargement impossible pour le moment.'); }
    });

    /* Lien de campagne : contrôle avant l'envoi */
    if ($('mkpCampForm')) $('mkpCampForm').addEventListener('submit', e => {
        const pct = Number($('mkpCaPct').value), end = $('mkpCaEnd').value, today = $('mkpCaEnd').min;
        let err = '';
        if (!Number.isInteger(pct) || pct < 1 || pct > 90) err = 'La remise doit être comprise entre 1 et 90 %.';
        else if (!end) err = 'Choisissez la date de fin de la campagne.';
        else if (end < today) err = 'La date de fin est déjà passée : choisissez aujourd’hui ou plus tard.';
        if (err) { e.preventDefault(); $('mkpCaErr').textContent = err; }
    });
})();
</script>
@endpush
