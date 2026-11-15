{{-- Bloc Note des clients : avis réels uniquement --}}
@php $r = $ctx->reviewStats(); @endphp
@once
@push('pb-styles')
<style data-pb>
.pb-rating { display: inline-flex; align-items: center; gap: 8px; font-size: 14px; color: var(--cs-text); }
.pb-rating .ico { color: #F59E0B; }
.pb-rating span { color: var(--cs-muted); font-weight: 600; }
</style>
@endpush
@endonce
@if ($r['avg'] && $r['count'] >= $settings['min_reviews'])
    <div class="pb-rating pb-num" aria-label="Note {{ number_format($r['avg'], 1, ',', '') }} sur 5, {{ $r['count'] }} avis">
        {!! \App\Support\IconLibrary::stars($r['avg'], 16) !!}
        <strong>{{ number_format($r['avg'], 1, ',', '') }}</strong>
        @if ($settings['show_count'])<span>({{ $r['count'] }} avis)</span>@endif
    </div>
@elseif ($ctx->editing)
    <p class="pb-edit-note">Note des clients : apparaîtra dès {{ $settings['min_reviews'] }} avis réel{{ $settings['min_reviews'] > 1 ? 's' : '' }} (actuellement {{ $r['count'] }}).</p>
@endif
