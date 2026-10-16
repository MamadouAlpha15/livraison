{{--
    Pixel Meta (Facebook) d'un vendeur — code de base officiel de Meta.
    @include('partials.meta-pixel', ['pixelId' => $shop->meta_pixel_id, 'events' => [['ViewContent', [...], 'id-optionnel']]])
    'pageView' => false : n'envoie pas PageView (ex. page de confirmation Shopio après un achat).
    Ne l'inclure que si App\Support\MetaPixel::activeFor($shop) est vrai.
    Le pixel est initialisé une seule fois par page (window.__shopioPixels), même s'il est inclus deux fois.
    En mode test (page Marketing → Tester mon pixel), un panneau liste les événements envoyés.
--}}
@php
    $events = $events ?? [];
    $pageView = $pageView ?? true;
    $pixelTest = \App\Support\MetaPixel::test();
    $isPixelTest = $pixelTest && $pixelTest['pixel'] === (string) $pixelId;
@endphp
@if($isPixelTest)
@once
{{-- Panneau du mode test : visible uniquement par le vendeur qui l'a lancé --}}
<aside class="pxt" id="pxt" aria-live="polite" aria-label="Test du pixel Meta">
    <div class="pxt-head">
        <span class="pxt-dot" id="pxtDot"></span>
        <strong>Test du pixel</strong>
        <span class="pxt-id">{{ $pixelId }}</span>
        <button type="button" class="pxt-min" id="pxtMin" aria-label="Réduire le panneau">–</button>
    </div>
    <div class="pxt-body">
        <p class="pxt-status" id="pxtStatus">Connexion à Facebook…</p>
        <ol class="pxt-list" id="pxtList"></ol>
        <p class="pxt-tip">Naviguez comme un client : ouvrez un produit, ajoutez-le au panier, commencez une commande. Chaque événement envoyé à Facebook s'affiche ici.</p>
        <div class="pxt-actions">
            <a href="{{ route('boutique.marketing') }}#pixel">Retour au marketing</a>
            <form method="POST" action="{{ route('boutique.marketing.pixel-test.stop') }}">
                @csrf
                <button type="submit">Quitter le test</button>
            </form>
        </div>
    </div>
</aside>
<style>
.pxt{position:fixed;left:max(16px,env(safe-area-inset-left,0px));bottom:calc(16px + env(safe-area-inset-bottom,0px));z-index:2147483000;width:min(340px,calc(100vw - 32px));background:#0B1E3D;color:#E6EEFB;border-radius:18px;box-shadow:0 18px 50px rgba(11,30,61,.35);font:500 13px/1.45 'Plus Jakarta Sans',system-ui,sans-serif;overflow:hidden}
.pxt-head{display:flex;align-items:center;gap:8px;padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.08)}
.pxt-head strong{font-weight:700;color:#fff}
.pxt-id{margin-left:auto;font-size:11px;color:#8FA6C8;font-variant-numeric:tabular-nums}
.pxt-dot{width:9px;height:9px;border-radius:50%;background:#F59E0B;flex:none}
.pxt-dot.ok{background:#22C55E}.pxt-dot.ko{background:#EF4444}
.pxt-min{background:rgba(255,255,255,.08);border:0;color:#fff;width:24px;height:24px;border-radius:50%;cursor:pointer;font-size:15px;line-height:1}
.pxt.is-min .pxt-body{display:none}
.pxt-body{padding:12px 14px 14px;display:flex;flex-direction:column;gap:10px}
.pxt-status{margin:0;color:#C9D7EE}
.pxt-status.ko{color:#FCA5A5}
.pxt-list{margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:6px;max-height:180px;overflow:auto}
.pxt-list li{display:flex;justify-content:space-between;gap:10px;background:rgba(31,124,246,.16);border-radius:10px;padding:7px 10px}
.pxt-list b{color:#fff;font-weight:700}
.pxt-list small{color:#8FA6C8;font-variant-numeric:tabular-nums;white-space:nowrap}
.pxt-list em{display:block;font-style:normal;color:#AFC3E2;font-size:12px}
.pxt-tip{margin:0;font-size:12px;color:#8FA6C8}
.pxt-actions{display:flex;gap:8px;flex-wrap:wrap}
.pxt-actions a,.pxt-actions button{flex:1;text-align:center;border-radius:999px;padding:8px 12px;font:700 12px 'Plus Jakarta Sans',system-ui,sans-serif;cursor:pointer;text-decoration:none;border:0}
.pxt-actions a{background:rgba(255,255,255,.1);color:#fff}
.pxt-actions form{flex:1;display:flex;margin:0}
.pxt-actions button{background:#1668D8;color:#fff}
.pxt-actions button:hover{background:#1260C9}
.pxt-actions a:focus-visible,.pxt-actions button:focus-visible,.pxt-min:focus-visible{outline:2px solid #F59E0B;outline-offset:2px}
</style>
<script>
(function () {
    var list = document.getElementById('pxtList'), status = document.getElementById('pxtStatus'), dot = document.getElementById('pxtDot');
    var labels = { PageView: 'Page vue', ViewContent: 'Produit consulté', AddToCart: 'Ajout au panier', InitiateCheckout: 'Début de commande', Purchase: 'Achat' };
    window.shopioPixelTestLog = function (name, params) {
        var li = document.createElement('li'), left = document.createElement('div'), b = document.createElement('b'), t = document.createElement('small');
        b.textContent = labels[name] ? labels[name] + ' (' + name + ')' : name;
        left.appendChild(b);
        if (params && params.value) {
            var em = document.createElement('em');
            em.textContent = Number(params.value).toLocaleString('fr-FR') + ' ' + (params.currency || '');
            left.appendChild(em);
        }
        t.textContent = new Date().toLocaleTimeString('fr-FR');
        li.appendChild(left); li.appendChild(t);
        list.insertBefore(li, list.firstChild);
    };
    var set = function (cls, text) { dot.className = 'pxt-dot ' + cls; status.className = 'pxt-status ' + cls; status.textContent = text; };
    window.shopioPixelTestScript = function (script) {
        if (!script) return;
        var done = false;
        script.addEventListener('load', function () { done = true; set('ok', 'Pixel connecté : les événements partent bien vers Facebook.'); });
        script.addEventListener('error', function () { done = true; set('ko', 'Facebook est bloqué sur ce navigateur (bloqueur de publicité, réseau). Désactivez le bloqueur pour tester.'); });
        setTimeout(function () { if (!done) set('ko', 'Facebook ne répond pas. Vérifiez la connexion internet.'); }, 10000);
    };
    document.getElementById('pxtMin').addEventListener('click', function () {
        var p = document.getElementById('pxt'); p.classList.toggle('is-min');
        this.textContent = p.classList.contains('is-min') ? '+' : '–';
        this.setAttribute('aria-label', p.classList.contains('is-min') ? 'Agrandir le panneau' : 'Réduire le panneau');
    });
})();
</script>
@endonce
@endif
<script>
(function () {
    var id = @json((string) $pixelId);
    if (!/^\d{10,20}$/.test(id)) return;
    var testMode = @json((bool) $isPixelTest);
    var firstLoad = !window.fbq;
    if (firstLoad) {
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');
        if (testMode && window.shopioPixelTestScript) window.shopioPixelTestScript(document.querySelector('script[src*="fbevents.js"]'));
    }
    var log = function (name, params) { if (testMode && window.shopioPixelTestLog) window.shopioPixelTestLog(name, params); };
    window.__shopioPixels = window.__shopioPixels || {};
    if (!window.__shopioPixels[id]) {
        fbq('init', id);
        @if($pageView) fbq('trackSingle', id, 'PageView'); log('PageView'); @endif
        window.__shopioPixels[id] = true;
    }
    /* Raccourci pour les événements déclenchés plus tard par la page (panier, commande) */
    window.shopioPixelTrack = function (name, params, eventId) {
        if (window.fbq) fbq('trackSingle', id, name, params || {}, eventId ? { eventID: eventId } : {});
        log(name, params);
    };
    @foreach($events as $ev)
    window.shopioPixelTrack(@json($ev[0]), @json($ev[1] ?? []), @json($ev[2] ?? null));
    @endforeach
})();
</script>
@if($pageView)<noscript><img height="1" width="1" style="display:none" alt="" src="https://www.facebook.com/tr?id={{ urlencode((string) $pixelId) }}&amp;ev=PageView&amp;noscript=1"></noscript>@endif
