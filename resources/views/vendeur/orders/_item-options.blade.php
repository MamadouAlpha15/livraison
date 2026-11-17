{{-- Choix faits par le client sur une page construite (order_items.options) : offre, cadeau, complémentaire, réponses aux champs --}}
@php $o = is_array($item->options) ? $item->options : []; @endphp
@if (!empty($o['offer']['saving']))
    <div class="prod-ref" style="color:#15803D;font-weight:700">Offre « {{ $o['offer']['label'] }} » : {{ number_format($o['offer']['saving'], 0, ',', ' ') }} {{ $devise ?? 'GNF' }} d'économie</div>
@endif
@if (!empty($o['gift']))
    <div class="prod-ref" style="color:#15803D;font-weight:700">🎁 Cadeau offert avec l'offre</div>
@endif
@if (!empty($o['upsell']))
    <div class="prod-ref" style="color:#1668D8;font-weight:700">Ajouté depuis la page produit{{ !empty($o['discount']) ? ' (−' . (int) $o['discount'] . ' %)' : '' }}</div>
@endif
@if (!empty($o['duplicate_of']))
    <div class="prod-ref" style="color:#B45309;font-weight:700">⚠ Possible doublon de la commande n° {{ $o['duplicate_of'] }} : le client a confirmé une nouvelle commande, vérifie-le à l'appel</div>
@endif
@if (!empty($o['phone_verified']))
    <div class="prod-ref" style="color:#15803D;font-weight:700">✓ Numéro confirmé par code SMS</div>
@endif
@foreach ($o['custom'] ?? [] as $answer)
    <div class="prod-ref" style="color:#16162A"><strong>{{ $answer['label'] }} :</strong> {{ $answer['value'] }}</div>
@endforeach
