{{-- Bloc Livraison estimée : dates calculées à partir des délais de la boutique --}}
@php
    [$from, $to] = \App\PageBuilder\Blocks\Delivery::window($settings);
    $fmt = fn ($d) => $d->locale('fr')->isoFormat('ddd D MMM');
@endphp
@once
@push('pb-styles')
<style data-pb>
.pb-delivery { display: flex; align-items: center; gap: 10px; font-size: 14px; }
.pb-delivery .ico { color: var(--cs-button); flex: none; }
.pb-delivery strong { font-weight: 800; }
</style>
@endpush
@endonce
<p class="pb-delivery" style="margin:0">
    {!! \App\Support\IconLibrary::svg('truck', '', 20) !!}
    <span>{{ $settings['label'] }} :
        @if ($from->isSameDay($to))
            <strong>{{ $from->isToday() ? 'aujourd\'hui' : $fmt($from) }}</strong>
        @else
            <strong>entre le {{ $fmt($from) }} et le {{ $fmt($to) }}</strong>
        @endif
    </span>
</p>
