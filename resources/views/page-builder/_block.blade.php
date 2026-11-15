{{-- Enveloppe d'un bloc, puis la vue de son type. Un bloc qui n'affiche rien (pas d'avis, stock élevé…) ne laisse pas de vide. --}}
@php ob_start(); @endphp
@include($b['type']->view(), ['block' => $b, 'settings' => $b['settings'], 'ctx' => $ctx])
@php $inner = trim(ob_get_clean()); @endphp
@if ($inner !== '')
<div class="pb-block pb-block--{{ $b['type']->type() }}" @if ($ctx->editing) data-block-id="{{ $b['id'] }}" @endif>
    {!! $inner !!}
</div>
@endif
