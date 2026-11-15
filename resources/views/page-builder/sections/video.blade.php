{{-- Section Vidéo : MP4 de la boutique, YouTube (chargé au clic, sans cookies) ou TikTok --}}
@php
    $v = \App\PageBuilder\Media\VideoSource::from($settings['video']);
    $ratio = ['portrait' => '9 / 16', 'landscape' => '16 / 9', 'square' => '1 / 1'][$settings['format']] ?? '9 / 16';
@endphp
@once
@push('pb-styles')
<style data-pb>
.pb-vid { display: flex; flex-direction: column; align-items: center; gap: 14px; text-align: center; }
.pb-vid h2 { margin: 0; font-size: clamp(22px, 4.5vw, 30px); font-weight: 800; line-height: 1.2; }
.pb-vid-box { width: 100%; border-radius: 18px; overflow: hidden; background: #0B1E3D; position: relative; }
.pb-vid-box video, .pb-vid-box iframe { width: 100%; height: 100%; display: block; border: 0; object-fit: cover; }
.pb-vid-yt { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; padding: 0; cursor: pointer; background: #0B1E3D center / cover no-repeat; display: flex; align-items: center; justify-content: center; }
.pb-vid-yt span { width: 72px; height: 72px; border-radius: 999px; background: rgba(255, 255, 255, .92); display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 30px rgba(0, 0, 0, .35); }
.pb-vid-yt span::after { content: ''; border-left: 22px solid #0B1E3D; border-top: 13px solid transparent; border-bottom: 13px solid transparent; margin-left: 6px; }
.pb-vid-yt:focus-visible { outline: 3px solid #FFFFFF; outline-offset: -6px; }
.pb-vid p { margin: 0; color: var(--cs-muted); font-size: 14px; }
</style>
@endpush
@endonce
@if ($v)
    <div class="pb-vid">
        @if (filled($settings['heading']))<h2>{{ $settings['heading'] }}</h2>@endif
        @if ($v['kind'] === 'mp4')
            <div class="pb-vid-box" style="aspect-ratio: {{ $ratio }}; max-width: {{ $settings['format'] === 'landscape' ? '880px' : '400px' }}">
                @if ($settings['autoplay'])
                    <video src="{{ $v['src'] }}" autoplay muted loop playsinline preload="metadata" aria-label="{{ $settings['heading'] ?: 'Vidéo du produit' }}"></video>
                @else
                    <video src="{{ $v['src'] }}" controls playsinline preload="metadata" aria-label="{{ $settings['heading'] ?: 'Vidéo du produit' }}"></video>
                @endif
            </div>
        @elseif ($v['kind'] === 'youtube')
            <div class="pb-vid-box" style="aspect-ratio: 16 / 9; max-width: 880px">
                <button type="button" class="pb-vid-yt" data-pb-yt="{{ $v['src'] }}" style="background-image:url('{{ $v['thumb'] }}')" aria-label="Lire la vidéo{{ $settings['heading'] ? ' : ' . $settings['heading'] : '' }}"><span></span></button>
            </div>
        @else
            <div class="pb-vid-box" style="aspect-ratio: 9 / 16; max-width: 340px; background: #000">
                <iframe src="{{ $v['src'] }}" loading="lazy" allow="encrypted-media; fullscreen" title="{{ $settings['heading'] ?: 'Vidéo TikTok' }}"></iframe>
            </div>
        @endif
        @if (filled($settings['caption']))<p>{{ $settings['caption'] }}</p>@endif
    </div>
@elseif ($ctx->editing)
    <p style="margin:0;padding:12px;border:1px dashed #9DB6E0;border-radius:10px;font-size:13px;color:#45455E;background:#F6F9FF">Vidéo : envoie un MP4 ou colle un lien YouTube ou TikTok dans les réglages.</p>
@endif
