{{-- Bloc Texte : paragraphe mis en forme (nettoyé à l'enregistrement par HtmlSanitizer) --}}
@once
@push('pb-styles')
<style data-pb>
.pb-text--body { font-size: 15px; color: var(--cs-muted); }
.pb-text--subtitle { font-size: 17px; font-weight: 700; color: var(--cs-text); }
.pb-text--caption { font-size: 13px; color: var(--cs-muted); }
</style>
@endpush
@endonce
@if (filled($settings['content']))
    <div class="pb-rte pb-text--{{ $settings['style'] }}">{!! $settings['content'] !!}</div>
@endif
