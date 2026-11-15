{{-- Section Bannière d'annonce : une ligne courte --}}
@if (filled($settings['text']))
    @php $inner = ($settings['icon'] ? \App\Support\IconLibrary::svg($settings['icon'], '', 16) : '') . '<span>' . e($settings['text']) . '</span>'; @endphp
    <p style="margin:0;display:flex;align-items:center;justify-content:center;gap:8px;text-align:center;font-size:13px;font-weight:700;line-height:1.35">
        @if (filled($settings['link']))
            <a href="{{ $settings['link'] }}" style="color:inherit;display:inline-flex;align-items:center;gap:8px;text-decoration:underline;text-underline-offset:3px">{!! $inner !!}</a>
        @else
            {!! $inner !!}
        @endif
    </p>
@endif
