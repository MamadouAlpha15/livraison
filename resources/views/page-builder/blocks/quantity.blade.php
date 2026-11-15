{{-- Bloc Quantité (ignoré quand des offres groupées fixent la quantité) --}}
@php
    $max = $checkout->maxQuantity();
    $left = $ctx->stockLeft();
    if ($left !== null) $max = max(1, min($max, $left));
@endphp
@once
@push('pb-styles')
<style data-pb>
.pb-step { display: inline-flex; align-items: center; border: 1px solid var(--cs-line); border-radius: 12px; overflow: hidden; background: #FFFFFF; }
.pb-step button { width: 48px; height: 48px; border: 0; background: transparent; font-size: 22px; font-weight: 700; cursor: pointer; color: #16162A; }
.pb-step input { width: 56px; height: 48px; border: 0; text-align: center; font: inherit; font-size: 17px; font-weight: 800; color: #16162A; -moz-appearance: textfield; }
.pb-step input::-webkit-inner-spin-button, .pb-step input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
</style>
@endpush
@endonce
@if ($checkout->offers)
    @if ($ctx->editing)<p class="pb-edit-note">Quantité : masquée, car les offres groupées fixent déjà la quantité.</p>@endif
@else
    <div>
        <label class="pb-lbl" for="pb-qty-{{ $block['id'] }}">{{ $settings['label'] }}</label>
        <div class="pb-step">
            <button type="button" data-pb-step="-1" aria-label="Retirer un">−</button>
            <input id="pb-qty-{{ $block['id'] }}" name="quantity" type="number" inputmode="numeric" min="1" max="{{ $max }}" value="1" data-pb-qty class="pb-num">
            <button type="button" data-pb-step="1" aria-label="Ajouter un">+</button>
        </div>
    </div>
@endif
