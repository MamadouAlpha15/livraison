{{-- Section Texte : un titre et un paragraphe mis en forme --}}
@once
@push('pb-styles')
<style data-pb>
.pb-rt { max-width: 720px; display: flex; flex-direction: column; gap: 12px; }
.pb-rt--center { margin: 0 auto; text-align: center; }
.pb-rt h2 { margin: 0; font-size: clamp(22px, 4.5vw, 30px); line-height: 1.2; font-weight: 800; letter-spacing: -.01em; }
.pb-rt .pb-rte { color: var(--cs-muted); font-size: 16px; }
.pb-rt--center .pb-rte ul, .pb-rt--center .pb-rte ol { text-align: left; display: inline-block; }
</style>
@endpush
@endonce

<div class="pb-rt pb-rt--{{ $settings['align'] }}">
    @if (filled($settings['heading']))
        <h2>{{ $settings['heading'] }}</h2>
    @endif
    @if (filled($settings['content']))
        <div class="pb-rte">{!! $settings['content'] !!}</div>
    @endif
</div>
