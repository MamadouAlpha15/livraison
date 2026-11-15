{{--
    Enveloppe d'une section : jeu de couleurs et marges (mobile / ordinateur), puis la vue du type.
    Une section qui n'affiche rien (pas d'avis, « Vu dans » non confirmé…) ne laisse pas de bande vide.
--}}
@php
    $st = $s['settings'];
    $style = \App\PageBuilder\ColorSchemes::css($ctx->shop, $st['color_scheme'] ?? 'clair')
        . ';--pt-m:' . (int) $st['padding_top_mobile'] . 'px;--pb-m:' . (int) $st['padding_bottom_mobile'] . 'px'
        . ';--pt-d:' . (int) $st['padding_top_desktop'] . 'px;--pb-d:' . (int) $st['padding_bottom_desktop'] . 'px';
    ob_start();
@endphp
@include($s['type']->view(), ['section' => $s, 'settings' => $st, 'blocks' => $s['blocks'], 'ctx' => $ctx])
@php $inner = trim(ob_get_clean()); @endphp
@if ($inner !== '')
<section id="pb-{{ $s['id'] }}" class="pb-section pb-section--{{ $s['type']->type() }}" style="{{ $style }}"
    @if ($ctx->editing) data-section-id="{{ $s['id'] }}" @endif>
    <div class="pb-container">
        {!! $inner !!}
    </div>
</section>
@endif
