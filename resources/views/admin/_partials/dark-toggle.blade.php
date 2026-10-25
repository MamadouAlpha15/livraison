{{-- Bouton mode nuit / mode jour (script commun dans layouts.app : data-dark-toggle). --}}
@php
    $dtMoon = \App\Support\IconLibrary::svg('moon', '', 18);
    $dtSun  = \App\Support\IconLibrary::svg('sun', '', 18);
@endphp
<button type="button" class="tb-dark {{ $class ?? '' }}" data-dark-toggle data-moon-icon="{{ $dtMoon }}" data-sun-icon="{{ $dtSun }}"
        title="Mode sombre" aria-label="Basculer le mode nuit">{!! $dtMoon !!}</button>
