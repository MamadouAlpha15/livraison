{{-- Bloc Clients livrés : nombre réel de commandes livrées --}}
@php $n = $ctx->deliveredCount(); @endphp
@if ($n >= $settings['min'])
    <p class="pb-num" style="margin:0;display:flex;align-items:center;gap:8px;font-size:14px;font-weight:700">
        {!! \App\Support\IconLibrary::svg('users', '', 18) !!}
        <span>{{ str_replace('{n}', number_format($n, 0, ',', "\u{00A0}"), $settings['text']) }}</span>
    </p>
@elseif ($ctx->editing)
    <p class="pb-edit-note">Clients livrés : s'affiche à partir de {{ $settings['min'] }} commandes livrées (actuellement {{ $n }}).</p>
@endif
