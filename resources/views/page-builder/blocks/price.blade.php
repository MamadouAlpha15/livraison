{{-- Bloc Prix : prix actuel, ancien prix barré et économie (seulement si l'ancien prix est réellement plus élevé) --}}
@php $compare = $ctx->compareAt(); @endphp
@once
@push('pb-styles')
<style data-pb>
.pb-price { display: flex; align-items: baseline; flex-wrap: wrap; gap: 6px 10px; }
.pb-price strong { font-size: 28px; font-weight: 800; letter-spacing: -.01em; }
.pb-price s { color: var(--cs-muted); font-size: 16px; font-weight: 600; }
.pb-price-save { align-self: center; background: var(--cs-button); color: var(--cs-on-button); font-size: 12px; font-weight: 800; padding: 4px 9px; border-radius: 999px; }
</style>
@endpush
@endonce
<div class="pb-price pb-num">
    <strong>{{ \App\Support\Money::gnf($ctx->price()) }}</strong>
    @if ($compare && $settings['show_compare'])
        <s>{{ \App\Support\Money::gnf($compare) }}</s>
    @endif
    @if ($compare && $settings['show_saving'])
        <span class="pb-price-save">−{{ $ctx->savingPercent() }} %</span>
    @endif
</div>
