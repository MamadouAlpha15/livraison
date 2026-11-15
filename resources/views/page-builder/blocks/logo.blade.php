{{-- Bloc Logo (section Vu dans) --}}
@php $src = $ctx->media($settings['image'], 'medium'); @endphp
@if ($src)
    @if (filled($settings['link']))
        <a href="{{ $settings['link'] }}" target="_blank" rel="noopener nofollow"><img src="{{ $src }}" alt="{{ $settings['name'] ?: 'Média' }}" loading="lazy"></a>
    @else
        <img src="{{ $src }}" alt="{{ $settings['name'] ?: 'Média' }}" loading="lazy">
    @endif
@elseif ($ctx->editing)
    <span style="display:inline-flex;align-items:center;height:34px;padding:0 12px;border:1px dashed #9DB6E0;border-radius:8px;font-size:12px;color:#45455E">Logo à choisir</span>
@endif
