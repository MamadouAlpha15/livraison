{{--
    Une carte de la vue en direct (Afrique ou Monde) : pays en nids d'abeille, petits pays en points, étiquettes
    des grands pays et du pays de la boutique. Les repères (visiteurs, commandes) sont ajoutés par le JavaScript de la page.
    Variables : $key (africa|world), $map (LiveMaps::get), $shopIso, $labelMin (surface minimale pour une étiquette),
                $font (taille des étiquettes), $hidden
--}}
@php
    [$vx, $vy, $vw, $vh] = $map['viewBox'];
    // Un pays peut avoir plusieurs tracés (Somalie + Somaliland) : étiquette au centre du plus grand
    $labels = collect($map['countries'])->sortByDesc('area')->unique('iso')
        ->filter(fn ($c) => $c['area'] >= $labelMin || $c['iso'] === $shopIso || ($key === 'africa' && $c['area'] < 60));
@endphp
<svg viewBox="{{ $vx }} {{ $vy }} {{ $vw }} {{ $vh }}" preserveAspectRatio="xMidYMid meet" role="img" aria-label="{{ $key === 'world' ? 'Carte du monde' : 'Carte de l’Afrique' }}" class="lv-svg" data-map="{{ $key }}" @if($hidden) hidden @endif>
    <g class="lv-countries">
        @foreach($map['countries'] as $c)
            @if($c['d'] === '' || ($key === 'africa' && $c['area'] < 60))
                <circle class="lv-isle {{ $c['iso'] === $shopIso ? 'is-shop' : '' }}" data-iso="{{ $c['iso'] }}" data-name="{{ $c['name'] }}" cx="{{ $c['c'][0] }}" cy="{{ $c['c'][1] }}" r="{{ $key === 'world' ? 2.4 : 4 }}"/>
            @else
                <path class="lv-c {{ $c['iso'] === $shopIso ? 'is-shop' : '' }}" data-iso="{{ $c['iso'] }}" data-name="{{ $c['name'] }}" d="{{ $c['d'] }}"/>
            @endif
        @endforeach
    </g>
    <g aria-hidden="true" style="font-size:{{ $font }}px">
        @foreach($labels as $c)
            <text class="lv-lbl" x="{{ $c['c'][0] }}" y="{{ $c['c'][1] + ($c['area'] < 60 ? $font + 5 : $font / 3) }}">{{ $c['name'] }}</text>
        @endforeach
    </g>
    <g class="lv-pins" aria-hidden="true"></g>
</svg>
