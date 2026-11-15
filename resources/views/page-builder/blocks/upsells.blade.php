{{-- Bloc Produits complémentaires : à cocher, prix recalculé sur le serveur --}}
@php $items = $checkout->upsellProducts(); @endphp
@once
@push('pb-styles')
<style data-pb>
.pb-ups { display: grid; gap: 8px; }
.pb-up { display: grid; grid-template-columns: auto 52px 1fr auto; align-items: center; gap: 12px; padding: 10px 12px; border: 1px solid var(--cs-line); border-radius: 12px; cursor: pointer; }
.pb-up:has(input:checked) { border-color: var(--cs-button); }
.pb-up:focus-within { outline: 3px solid var(--cs-text); outline-offset: 2px; }
.pb-up input { width: 20px; height: 20px; margin: 0; accent-color: var(--cs-button); }
.pb-up img, .pb-up i { width: 52px; height: 52px; border-radius: 10px; object-fit: cover; background: var(--soft); }
.pb-up-name { font-weight: 700; font-size: 14px; overflow-wrap: anywhere; }
.pb-up-price { text-align: right; font-weight: 800; font-size: 14px; }
.pb-up-price s { display: block; font-weight: 600; font-size: 12px; color: var(--cs-muted); }
</style>
@endpush
@endonce
@if ($items->isNotEmpty())
    <fieldset class="pb-ups" style="border:0;margin:0;padding:0;min-width:0">
        <legend class="pb-lbl" style="padding:0">{{ $settings['heading'] }}</legend>
        @foreach ($items as $p)
            @php $price = $checkout->upsellPrice($p); @endphp
            <label class="pb-up">
                <input type="checkbox" name="upsells[]" value="{{ $p->id }}" data-pb-upsell>
                @if ($p->image)<img src="{{ \App\Services\ImageOptimizer::url($p->image, 'thumb') }}" alt="" loading="lazy">@else<i></i>@endif
                <span class="pb-up-name">{{ $p->name }}</span>
                <span class="pb-up-price pb-num">+ {{ \App\Support\Money::gnf($price) }}@if ($price < round((float) $p->current_price))<s>{{ \App\Support\Money::gnf($p->current_price) }}</s>@endif</span>
            </label>
        @endforeach
    </fieldset>
@elseif ($ctx->editing)
    <p class="pb-edit-note">Produits complémentaires : choisis jusqu'à 3 produits en ligne et en stock dans les réglages du bloc.</p>
@endif
