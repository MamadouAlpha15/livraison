{{-- Bloc Onglet repliable : description du produit ou texte libre --}}
@php
    $isDesc = $settings['source'] === 'description';
    $body = $isDesc ? trim((string) $ctx->product->description) : (string) $settings['content'];
@endphp
@once
@push('pb-styles')
<style data-pb>
.pb-acc { border-top: 1px solid var(--cs-line); border-bottom: 1px solid var(--cs-line); }
.pb-block--collapsible + .pb-block--collapsible .pb-acc { border-top: 0; margin-top: -14px; }
.pb-acc summary { display: flex; align-items: center; gap: 10px; min-height: 52px; cursor: pointer; font-weight: 800; font-size: 15px; list-style: none; }
.pb-acc summary::-webkit-details-marker { display: none; }
.pb-acc summary .ico { color: var(--cs-button); flex: none; }
.pb-acc summary::after { content: '+'; margin-left: auto; font-size: 22px; font-weight: 600; color: var(--cs-muted); }
.pb-acc[open] summary::after { content: '−'; }
.pb-acc summary:focus-visible { outline: 3px solid var(--cs-text); outline-offset: 2px; }
.pb-acc-body { padding: 0 0 16px; font-size: 15px; color: var(--cs-muted); }
</style>
@endpush
@endonce
@if (filled(strip_tags($body)))
    <details class="pb-acc" @if ($settings['open']) open @endif>
        <summary>@if ($settings['icon']){!! \App\Support\IconLibrary::svg($settings['icon'], '', 18) !!}@endif<span>{{ $settings['heading'] }}</span></summary>
        <div class="pb-acc-body pb-rte">
            @if ($isDesc){!! nl2br(e($body)) !!}@else{!! $body !!}@endif
        </div>
    </details>
@elseif ($ctx->editing)
    <p class="pb-edit-note">Onglet « {{ $settings['heading'] }} » : {{ $isDesc ? 'ce produit n\'a pas encore de description.' : 'ajoute ton texte dans les réglages.' }}</p>
@endif
