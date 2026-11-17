{{--
    Marketing → Qualité de la vitrine (ex-« Boutique → Qualité ») : visites, classement des catégories (30 jours),
    produits vus mais jamais commandés, score de vitrine (conseils vérifiables), avis reçus sur le lien d'aperçu.
    Le lien d'aperçu se crée maintenant depuis la vitrine : « Modifier la page » → « Partager un aperçu ».
    Toutes les valeurs viennent de la boutique : aucun chiffre n'est inventé.
    Variables : $shop, $visits, $categoryRanking, $seenNeverOrdered, $quality, $previewUrl, $feedback, $ico
--}}
@php
    $q = $quality;
    $ringColor = $q['score'] >= 80 ? '#15803D' : ($q['score'] >= 50 ? '#1F7CF6' : '#F59E0B');
@endphp
<style>
.mkq-tiles { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.mkq-tile { padding: 16px; border-radius: 20px; background: var(--surface); box-shadow: 0 0 0 1px var(--border); }
.mkq-tile b { display: block; font-size: 28px; font-weight: 800; letter-spacing: -.02em; font-variant-numeric: tabular-nums; color: var(--text); }
.mkq-tile span { font-size: 13px; color: var(--muted); font-weight: 600; }
.mkq-tile.is-night { background: var(--sv-night); box-shadow: none; } .mkq-tile.is-night b { color: #fff; } .mkq-tile.is-night span { color: #9FB3D3; }
.mkq-rank { margin: 0; padding: 0; display: flex; flex-direction: column; gap: 6px; counter-reset: rank; }
.mkq-rank li { list-style: none; display: grid; grid-template-columns: 28px minmax(0, 1fr) auto; gap: 10px; align-items: center; padding: 10px 12px; border-radius: 14px; background: var(--bg); font-size: 13.5px; }
.mkq-rank li::before { counter-increment: rank; content: counter(rank); width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: var(--sv-b50); color: var(--sv-btn); font-weight: 800; font-size: 12.5px; }
.mkq-seen { display: flex; align-items: center; gap: 10px; padding: 8px; border-radius: 14px; background: var(--surface); }
.mkq-seen img, .mkq-seen i { width: 40px; height: 40px; border-radius: 10px; object-fit: cover; flex-shrink: 0; background: var(--sv-b50); }
.mkq-seen div { flex: 1; min-width: 0; } .mkq-seen b { display: block; font-size: 13.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; } .mkq-seen span { font-size: 12px; color: var(--muted); }
.mkq-amber { padding: 16px; border-radius: 22px; background: var(--sv-amb50); display: flex; flex-direction: column; gap: 10px; }
.mkq-score { display: flex; gap: 20px; align-items: center; flex-wrap: wrap; }
.mkq-ring { --p: 0; position: relative; width: 140px; height: 140px; border-radius: 50%; flex-shrink: 0; background: conic-gradient(var(--ring) calc(var(--p) * 1%), var(--sv-b50) 0); display: flex; align-items: center; justify-content: center; }
.mkq-ring::before { content: ''; position: absolute; inset: 14px; border-radius: 50%; background: var(--surface); }
.mkq-ring b { position: relative; font-size: 36px; font-weight: 800; color: var(--text); font-variant-numeric: tabular-nums; } .mkq-ring small { font-size: 15px; }
.mkq-tips { display: flex; flex-direction: column; gap: 8px; margin: 0; padding: 0; list-style: none; }
.mkq-tip { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 16px; background: var(--bg); }
.mkq-tip-ico { width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: var(--sv-amb50); color: var(--sv-amb-ink); font-weight: 800; }
.mkq-tip.is-done .mkq-tip-ico { background: var(--sv-ok50); color: var(--sv-ok); }
.mkq-tip.is-done b { text-decoration: line-through; color: var(--text-2); }
.mkq-tip div { flex: 1; min-width: 0; } .mkq-tip b { display: block; font-size: 14px; color: var(--text); } .mkq-tip span { display: block; font-size: 12.5px; color: var(--muted); }
.mkq-fb { padding: 12px; border-radius: 16px; background: var(--bg); } .mkq-fb small { color: var(--muted); margin-left: 6px; } .mkq-fb p { margin: 4px 0 0; font-size: 14px; white-space: pre-line; overflow-wrap: anywhere; }
@media (max-width: 900px) { .mkq-tiles { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>

<div class="mkq-tiles" aria-label="Visites de la vitrine">
    <div class="mkq-tile"><b>{{ number_format($visits['today'], 0, ',', ' ') }}</b><span>Visites aujourd’hui</span></div>
    <div class="mkq-tile"><b>{{ number_format($visits['week'], 0, ',', ' ') }}</b><span>Sur 7 jours</span></div>
    <div class="mkq-tile"><b>{{ number_format($visits['month'], 0, ',', ' ') }}</b><span>Sur 30 jours</span></div>
    <div class="mkq-tile is-night"><b>{{ number_format($visits['total'], 0, ',', ' ') }}</b><span>Au total</span></div>
</div>

<div class="mk-grid">
    <section class="mk-card" aria-labelledby="mkqScH">
        <div class="mk-card-head"><span class="mk-card-ico">{!! $ico('m12 2 3 6.5 7 .8-5.2 4.8 1.4 7L12 17.6 5.8 21l1.4-7L2 9.3l7-.8z') !!}</span><div><h2 class="mk-card-title" id="mkqScH">Score de vitrine</h2><p class="mk-card-sub">{{ $q['done'] }} conseil{{ $q['done'] > 1 ? 's' : '' }} sur {{ count($q['tips']) }} déjà suivi{{ $q['done'] > 1 ? 's' : '' }}. Chaque point vient de votre boutique : rien n’est estimé.</p></div></div>
        <div class="mkq-score"><div class="mkq-ring" style="--p: {{ $q['score'] }}; --ring: {{ $ringColor }}" role="img" aria-label="Score de vitrine : {{ $q['score'] }} sur 100"><b>{{ $q['score'] }}<small>/100</small></b></div></div>
        <ul class="mkq-tips">
            @foreach(collect($q['tips'])->sortBy('done') as $t)
            <li class="mkq-tip {{ $t['done'] ? 'is-done' : '' }}">
                <span class="mkq-tip-ico" aria-hidden="true">{{ $t['done'] ? '✓' : '!' }}</span>
                <div><b>{{ $t['title'] }}<span class="sx-sr" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)">{{ $t['done'] ? ' : fait' : ' : à corriger' }}</span></b><span>{{ $t['hint'] }}</span></div>
                @unless($t['done'])<a class="mk-btn mk-btn-line mk-btn-sm" href="{{ $t['url'] }}">{{ $t['cta'] }}</a>@endunless
            </li>
            @endforeach
        </ul>
    </section>

    <div style="display:flex;flex-direction:column;gap:20px;min-width:0">
        <section class="mk-card" aria-labelledby="mkqRkH">
            <div class="mk-card-head"><span class="mk-card-ico">{!! $ico('M4 19h16M7 16V9M12 16V5M17 16v-4') !!}</span><div><h2 class="mk-card-title" id="mkqRkH">Classement des catégories</h2><p class="mk-card-sub">Vues des fiches produit et commandes, sur 30 jours.</p></div></div>
            @if(count($categoryRanking))
            <ol class="mkq-rank">@foreach(array_slice($categoryRanking, 0, 6) as $r)<li><span style="font-weight:700">{{ $r['name'] }}</span><span style="color:var(--muted);font-size:12.5px;font-variant-numeric:tabular-nums">{{ number_format($r['views'], 0, ',', ' ') }} vue{{ $r['views'] > 1 ? 's' : '' }} · {{ $r['orders'] }} commande{{ $r['orders'] > 1 ? 's' : '' }}</span></li>@endforeach</ol>
            @else
            <div class="mk-empty">Pas encore de vues de fiches produit sur 30 jours.</div>
            @endif
        </section>

        @if(count($seenNeverOrdered))
        <section class="mkq-amber" aria-labelledby="mkqSeenH">
            <h2 class="mk-card-title" id="mkqSeenH" style="color:var(--sv-amb-ink)">Vus mais jamais commandés</h2>
            <p class="mk-note" style="color:inherit">Vus au moins 5 fois en 30 jours, sans commande : vérifiez la photo, le prix et la description.</p>
            @foreach($seenNeverOrdered as $p)<div class="mkq-seen">@if($p['thumb'])<img src="{{ $p['thumb'] }}" alt="">@else<i></i>@endif<div><b>{{ $p['name'] }}</b><span>{{ $p['views'] }} vues · 0 commande</span></div><a class="mk-btn mk-btn-line mk-btn-sm" href="{{ $p['edit'] }}">Modifier</a></div>@endforeach
        </section>
        @endif

        <section class="mk-card" aria-labelledby="mkqFbH">
            <div class="mk-card-head"><span class="mk-card-ico">{!! $ico('M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z') !!}</span><div><h2 class="mk-card-title" id="mkqFbH">Avis reçus sur l’aperçu</h2><p class="mk-card-sub">Les personnes qui ouvrent votre lien d’aperçu (48 h) peuvent vous laisser un avis avant que vous publiiez.</p></div></div>
            @if($previewUrl)
            <div class="mk-copyrow"><input class="mk-url" id="mkqPv" value="{{ $previewUrl }}" readonly aria-label="Lien d’aperçu"><button type="button" class="mk-btn mk-btn-blue mk-btn-sm" data-copy="#mkqPv">Copier</button></div>
            <p class="mk-note">Valable jusqu’au {{ $shop->preview_expires_at->timezone('Africa/Conakry')->translatedFormat('j F à H:i') }}.</p>
            @endif
            @if($feedback->isNotEmpty())
            @foreach($feedback as $f)<div class="mkq-fb"><b>{{ $f['name'] }}</b><small>{{ \Carbon\Carbon::parse($f['at'])->timezone('Africa/Conakry')->translatedFormat('j M, H:i') }}</small><p>{{ $f['message'] }}</p></div>@endforeach
            @else
            <div class="mk-empty">Aucun avis pour l’instant.</div>
            @endif
            <div class="mk-row"><a class="mk-btn mk-btn-line mk-btn-sm" href="{{ route('boutique.storefront.live', ['ouvrir' => 'apercu']) }}">{{ $previewUrl ? 'Gérer le lien d’aperçu' : 'Créer un lien d’aperçu' }}</a></div>
        </section>
    </div>
</div>
