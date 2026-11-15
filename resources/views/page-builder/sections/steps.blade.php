{{-- Section Étapes de commande : étapes numérotées (un vrai ordre) --}}
@once
@push('pb-styles')
<style data-pb>
.pb-steps h2 { margin: 0 0 18px; font-size: clamp(22px, 4.5vw, 30px); font-weight: 800; line-height: 1.2; text-align: center; }
.pb-steps ol { list-style: none; margin: 0; padding: 0; display: grid; gap: 12px; counter-reset: pbstep; }
@media (min-width: 750px) { .pb-steps ol { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; } }
.pb-steps .pb-block { counter-increment: pbstep; }
.pb-step-item { display: grid; grid-template-columns: auto 1fr; gap: 4px 12px; align-items: start; padding: 16px; border-radius: 16px; border: 1px solid var(--cs-line); background: color-mix(in srgb, var(--cs-text) 3%, var(--cs-bg)); height: 100%; }
.pb-step-ico { grid-row: span 2; width: 44px; height: 44px; border-radius: 12px; background: var(--cs-button); color: var(--cs-on-button); display: flex; align-items: center; justify-content: center; position: relative; }
.pb-step-ico::after { content: counter(pbstep); position: absolute; top: -7px; right: -7px; min-width: 20px; height: 20px; border-radius: 999px; background: var(--cs-text); color: var(--cs-bg); font-size: 11px; font-weight: 800; display: flex; align-items: center; justify-content: center; padding: 0 5px; }
.pb-step-item strong { font-size: 15px; font-weight: 800; }
.pb-step-item span { font-size: 14px; color: var(--cs-muted); }
</style>
@endpush
@endonce
@if ($blocks)
    <div class="pb-steps">
        @if (filled($settings['heading']))<h2>{{ $settings['heading'] }}</h2>@endif
        <ol>
            @foreach ($blocks as $b)
                <li style="display:contents">@include('page-builder._block', ['b' => $b])</li>
            @endforeach
        </ol>
    </div>
@endif
