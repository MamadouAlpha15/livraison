{{-- Section Questions fréquentes : une question par bloc --}}
@once
@push('pb-styles')
<style data-pb>
.pb-faq { max-width: 760px; margin: 0 auto; }
.pb-faq h2 { margin: 0 0 14px; font-size: clamp(22px, 4.5vw, 30px); font-weight: 800; line-height: 1.2; text-align: center; }
.pb-faq details { border-bottom: 1px solid var(--cs-line); }
.pb-faq summary { display: flex; align-items: center; gap: 12px; min-height: 56px; padding: 12px 0; cursor: pointer; font-weight: 800; font-size: 16px; list-style: none; }
.pb-faq summary::-webkit-details-marker { display: none; }
.pb-faq summary::after { content: '+'; margin-left: auto; font-size: 24px; font-weight: 600; color: var(--cs-muted); flex: none; }
.pb-faq details[open] summary::after { content: '−'; }
.pb-faq summary:focus-visible { outline: 3px solid var(--cs-text); outline-offset: 2px; }
.pb-faq .pb-rte { padding: 0 0 16px; color: var(--cs-muted); font-size: 15px; }
</style>
@endpush
@endonce
@if ($blocks)
    <div class="pb-faq">
        @if (filled($settings['heading']))<h2>{{ $settings['heading'] }}</h2>@endif
        @foreach ($blocks as $b)
            @include('page-builder._block', ['b' => $b])
        @endforeach
    </div>
@elseif ($ctx->editing)
    <p style="margin:0;padding:12px;border:1px dashed #9DB6E0;border-radius:10px;font-size:13px;color:#45455E;background:#F6F9FF">Questions fréquentes : ajoute une question.</p>
@endif
