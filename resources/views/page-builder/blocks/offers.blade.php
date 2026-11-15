{{-- Bloc Offres groupées : paliers calculés sur le prix réel (le navigateur met à jour selon l'option choisie) --}}
@php
    $tiers = $checkout->tiers($checkout->unitPrice($firstVariant));
    $selectedTier = $checkout->defaultTier($tiers);
@endphp
@once
@push('pb-styles')
<style data-pb>
.pb-offers { display: grid; gap: 10px; }
.pb-offers h3 { margin: 0 0 2px; font-size: 16px; font-weight: 800; }
.pb-tier { position: relative; display: grid; grid-template-columns: auto 1fr auto; align-items: center; gap: 12px; padding: 14px; border: 1.5px solid var(--cs-line); border-radius: 14px; cursor: pointer; background: var(--cs-bg); }
.pb-tier:has(input:checked) { border-color: var(--cs-button); box-shadow: inset 0 0 0 1px var(--cs-button); background: color-mix(in srgb, var(--cs-button) 6%, var(--cs-bg)); }
.pb-tier:focus-within { outline: 3px solid var(--cs-text); outline-offset: 2px; }
.pb-tier input { width: 20px; height: 20px; margin: 0; accent-color: var(--cs-button); }
.pb-tier-name { font-weight: 800; font-size: 15px; }
.pb-tier-sub { display: block; font-size: 13px; color: var(--cs-muted); font-weight: 600; margin-top: 2px; }
.pb-tier-price { text-align: right; }
.pb-tier-price strong { display: block; font-size: 17px; font-weight: 800; }
.pb-tier-price s { font-size: 13px; color: var(--cs-muted); }
.pb-tier-badge { position: absolute; top: -10px; right: 12px; background: var(--cs-button); color: var(--cs-on-button); font-size: 11px; font-weight: 800; padding: 3px 9px; border-radius: 999px; letter-spacing: .02em; }
.pb-tier-gift { grid-column: 2 / -1; display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: var(--cs-text); }
.pb-tier-gift img { width: 32px; height: 32px; border-radius: 8px; object-fit: cover; }
</style>
@endpush
@endonce
@if ($tiers)
    <fieldset class="pb-offers" style="border:0;margin:0;padding:0;min-width:0">
        <legend class="pb-lbl" style="padding:0">{{ $settings['heading'] }}</legend>
        @foreach ($tiers as $i => $t)
            <label class="pb-tier">
                @if (filled($t['badge']))<span class="pb-tier-badge">{{ $t['badge'] }}</span>@endif
                <input type="radio" name="tier" value="{{ $i }}" data-pb-tier @checked($i === $selectedTier)>
                <span>
                    <span class="pb-tier-name">{{ $t['label'] }}</span>
                    <span class="pb-tier-sub pb-num" data-pb-tier-saving="{{ $i }}" @if (!$t['saving']) hidden @endif>Tu économises {{ \App\Support\Money::gnf($t['saving']) }}</span>
                </span>
                <span class="pb-tier-price pb-num">
                    <strong data-pb-tier-total="{{ $i }}">{{ \App\Support\Money::gnf($t['total']) }}</strong>
                    <s data-pb-tier-base="{{ $i }}" @if (!$t['saving']) hidden @endif>{{ \App\Support\Money::gnf($t['base']) }}</s>
                </span>
                @if ($t['gift'])
                    <span class="pb-tier-gift">
                        @if ($t['gift']->image)<img src="{{ \App\Services\ImageOptimizer::url($t['gift']->image, 'thumb') }}" alt="" loading="lazy">@endif
                        + {{ $t['gift']->name }} offert
                    </span>
                @endif
            </label>
        @endforeach
    </fieldset>
@endif
