{{-- Cartes des commandes, groupées par jour (page Commandes et mise à jour en direct). Variables : $groups --}}
@php $ob = \App\Support\OrderBoard::STAGES; @endphp
@forelse($groups as $day => $cards)
<h2 class="ob-day">{{ $day }}</h2>
@foreach($cards as $c)
<article class="ob-card" data-order="{{ json_encode($c) }}" data-id="{{ $c['id'] }}" tabindex="0" aria-label="Commande n° {{ $c['id'] }}, {{ $c['name'] }}, {{ $c['total'] }}">
    <div class="ob-card-main">
        <b class="ob-name">{{ $c['name'] }}</b>
        <dl class="ob-meta">
            <div><dt>Montant</dt><dd class="ob-amount">{{ $c['total'] }}</dd></div>
            <div><dt>Articles</dt><dd>{{ $c['count'] }} article{{ $c['count'] > 1 ? 's' : '' }}</dd></div>
            <div><dt>Heure</dt><dd>{{ $c['time'] }}</dd></div>
            @if($c['followupLabel'] && $c['stage'] === 'programmee')<div><dt>Prévue</dt><dd>{{ $c['followupLabel'] }}</dd></div>@endif
            @if($c['source'])<div><dt>Source</dt><dd>{{ $c['source'] }}</dd></div>@endif
        </dl>
        <small class="ob-ref">#{{ $c['id'] }} · {{ $c['dateShort'] }}@if($c['note'] !== '') · 📝 note @endif</small>
    </div>
    <div class="ob-card-side">
        <button type="button" class="ob-pill is-{{ $ob[$c['stage']][3] }}" data-stage-menu aria-haspopup="menu" aria-label="Étape : {{ $ob[$c['stage']][0] }}, changer"><span>{{ $ob[$c['stage']][0] }}</span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></button>
        <button type="button" class="ob-kebab" data-kebab aria-haspopup="menu" aria-label="Plus d’actions"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="5" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="12" cy="19" r="1.8"/></svg></button>
    </div>
</article>
@endforeach
@empty
<div class="ob-empty">
    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 4 7v10l8 4 8-4V7zM4 7l8 4 8-4M12 11v10"/></svg>
    <b>Aucune commande ici</b>
    <span>Les nouvelles commandes apparaissent automatiquement, sans recharger la page.</span>
</div>
@endforelse
