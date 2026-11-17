{{--
    Couleurs choisies par le vendeur pour ce thème (Modifier la page → Couleurs). Rien n'est écrit quand une couleur
    est vide : le thème garde les siennes. Variables : $pm (PageModels::data), $theme (express | signature)
--}}
@php
    $cl = $pm['colors'][$theme] ?? [];
    $acc = $cl['accent'] ?? null; $bg = $cl['bg'] ?? null; $ink = $cl['ink'] ?? null;
    $accInk = $acc ? \App\Support\Storefront::inkFor($acc) : null;
@endphp
@if($acc || $bg || $ink)
<style>
:root {
@if($theme === 'signature')
    @if($acc) --forest: {{ $acc }}; --forest-2: color-mix(in srgb, {{ $acc }} 82%, #fff); --forest-soft: color-mix(in srgb, {{ $acc }} 12%, #fff); --m-accent: {{ $acc }}; --m-accent-ink: {{ $accInk }}; --m-accent-text: {{ $acc }}; @endif
    @if($bg) --ivory: {{ $bg }}; --ivory-2: color-mix(in srgb, {{ $bg }} 92%, #000); --m-bg: {{ $bg }}; @endif
    @if($ink) --ink: {{ $ink }}; --ink-2: color-mix(in srgb, {{ $ink }} 72%, #fff); --ink-3: color-mix(in srgb, {{ $ink }} 52%, #fff); --m-ink: {{ $ink }}; @endif
@else
    @if($acc) --acc: {{ $acc }}; --acc-ink: {{ $accInk }}; @endif
    @if($bg) --m-bg: {{ $bg }}; --soft: color-mix(in srgb, {{ $bg }} 94%, #000); @endif
    @if($ink) --ink: {{ $ink }}; --ink-2: color-mix(in srgb, {{ $ink }} 72%, #fff); --ink-3: color-mix(in srgb, {{ $ink }} 52%, #fff); --m-ink: {{ $ink }}; @endif
@endif
}
@if($bg) body { background: {{ $bg }}; } @endif
</style>
@endif
