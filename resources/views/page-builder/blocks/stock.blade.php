{{-- Bloc Stock restant : stock réel, seulement sous le seuil --}}
@php $n = $ctx->stockLeft(); @endphp
@once
@push('pb-styles')
<style data-pb>
.pb-stock { display: inline-flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: #B45309; }
.pb-stock::before { content: ''; width: 8px; height: 8px; border-radius: 999px; background: #F59E0B; box-shadow: 0 0 0 4px rgba(245, 158, 11, .2); }
</style>
@endpush
@endonce
@if ($n !== null && $n > 0 && $n <= $settings['threshold'])
    <p class="pb-stock pb-num" style="margin:0">{{ str_replace('{n}', $n, $settings['text']) }}</p>
@elseif ($ctx->editing)
    <p class="pb-edit-note">Stock restant : s'affiche quand il reste {{ $settings['threshold'] }} articles ou moins ({{ $n === null ? 'stock non suivi' : 'stock actuel : ' . $n }}).</p>
@endif
