{{-- Section Vu dans : visible des clients seulement si le vendeur a confirmé, et seulement les logos avec image --}}
@php $logos = array_values(array_filter($blocks, fn ($b) => filled($b['settings']['image']))); @endphp
@once
@push('pb-styles')
<style data-pb>
.pb-fi { text-align: center; }
.pb-fi h2 { margin: 0 0 16px; font-size: 13px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--cs-muted); }
.pb-fi-row { display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 18px 32px; }
.pb-fi-row img { height: 34px; width: auto; max-width: 140px; object-fit: contain; filter: grayscale(1); opacity: .8; }
.pb-fi-row a:hover img, .pb-fi-row a:focus-visible img { filter: none; opacity: 1; }
.pb-fi-row a:focus-visible { outline: 3px solid var(--cs-text); outline-offset: 4px; }
</style>
@endpush
@endonce
@if (($settings['confirmed'] || $ctx->editing) && ($logos || $ctx->editing))
    <div class="pb-fi">
        @if ($ctx->editing && !$settings['confirmed'])
            <p style="margin:0 0 12px;padding:12px;border:1px dashed #9DB6E0;border-radius:10px;font-size:13px;color:#45455E;background:#F6F9FF;text-align:left">Vu dans : cachée pour tes clients tant que tu n'as pas coché « Je confirme que ces médias ont réellement parlé de ma boutique ».</p>
        @endif
        @if (filled($settings['heading']))<h2>{{ $settings['heading'] }}</h2>@endif
        <div class="pb-fi-row">
            @foreach ($blocks as $b)
                @include('page-builder._block', ['b' => $b])
            @endforeach
        </div>
    </div>
@endif
