{{-- Section Image et texte (ou avant / après) --}}
@php
    $img = $ctx->media($settings['image'], 'large');
    $img2 = $settings['mode'] === 'before_after' ? $ctx->media($settings['image2'], 'large') : null;
    $href = $settings['button_link'] ?: '#commander';
    // Images à montrer : chez les clients, seulement celles qui existent ; dans l'éditeur, des cases à remplir
    $figures = collect($settings['mode'] === 'before_after' ? [['Avant', $img], ['Après', $img2]] : [[null, $img]])
        ->filter(fn ($f) => $f[1] || $ctx->editing)->values()->all();
@endphp
@once
@push('pb-styles')
<style data-pb>
.pb-iwt { display: grid; gap: 22px; align-items: center; }
.pb-iwt > * { min-width: 0; }
@media (min-width: 750px) { .pb-iwt { grid-template-columns: 1fr 1fr; gap: 48px; } .pb-iwt--right .pb-iwt-media { order: 2; } .pb-iwt--text { grid-template-columns: minmax(0, 720px); justify-content: center; } }
.pb-iwt-media { display: grid; gap: 10px; }
.pb-iwt-media--pair { grid-template-columns: 1fr 1fr; }
.pb-iwt-fig { margin: 0; position: relative; border-radius: 18px; overflow: hidden; background: color-mix(in srgb, var(--cs-text) 6%, var(--cs-bg)); aspect-ratio: 4 / 5; }
.pb-iwt-media:not(.pb-iwt-media--pair) .pb-iwt-fig { aspect-ratio: 4 / 3; }
.pb-iwt-fig img { width: 100%; height: 100%; object-fit: cover; }
.pb-iwt-fig figcaption { position: absolute; left: 10px; top: 10px; background: rgba(11, 30, 61, .82); color: #FFFFFF; font-size: 12px; font-weight: 800; padding: 4px 10px; border-radius: 999px; }
.pb-iwt-empty { display: flex; align-items: center; justify-content: center; color: var(--cs-muted); font-size: 13px; font-weight: 600; text-align: center; padding: 16px; }
.pb-iwt-text { display: flex; flex-direction: column; gap: 12px; align-items: flex-start; }
.pb-iwt-text h2 { margin: 0; font-size: clamp(22px, 4.5vw, 32px); font-weight: 800; line-height: 1.2; }
.pb-iwt-text .pb-rte { color: var(--cs-muted); font-size: 16px; }
.pb-iwt-btn { display: inline-flex; align-items: center; min-height: 48px; padding: 0 20px; border-radius: 12px; background: var(--cs-button); color: var(--cs-on-button); font-weight: 800; text-decoration: none; }
.pb-iwt-btn:focus-visible { outline: 3px solid var(--cs-text); outline-offset: 2px; }
</style>
@endpush
@endonce
@if ($img || $ctx->editing || filled($settings['heading']))
<div class="pb-iwt pb-iwt--{{ $settings['image_side'] }} {{ $figures ? '' : 'pb-iwt--text' }}">
    @if ($figures)
    <div class="pb-iwt-media {{ count($figures) === 2 ? 'pb-iwt-media--pair' : '' }}">
        @foreach ($figures as [$tag, $src])
            <figure class="pb-iwt-fig">
                @if ($src)
                    <img src="{{ $src }}" alt="{{ $tag ? $tag . ' · ' : '' }}{{ $settings['heading'] ?: $ctx->product->name }}" loading="lazy">
                @elseif ($ctx->editing)
                    <div class="pb-iwt-fig pb-iwt-empty" style="height:100%">Choisis une image dans les réglages</div>
                @endif
                @if ($tag)<figcaption>{{ $tag }}</figcaption>@endif
            </figure>
        @endforeach
    </div>
    @endif
    <div class="pb-iwt-text">
        @if (filled($settings['heading']))<h2>{{ $settings['heading'] }}</h2>@endif
        @if (filled($settings['content']))<div class="pb-rte">{!! $settings['content'] !!}</div>@endif
        @if (filled($settings['button_label']))<a class="pb-iwt-btn" href="{{ $href }}">{{ $settings['button_label'] }}</a>@endif
    </div>
</div>
@endif
