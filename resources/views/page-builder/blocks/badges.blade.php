{{-- Bloc Engagements : jusqu'à trois (icône + texte) --}}
@php
    $items = collect([1, 2, 3])->map(fn ($i) => ['icon' => $settings["icon{$i}"], 'text' => $settings["text{$i}"]])->filter(fn ($x) => filled($x['text']));
@endphp
@once
@push('pb-styles')
<style data-pb>
.pb-badges { display: grid; gap: 8px; margin: 0; padding: 0; list-style: none; }
.pb-badges--row { grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); }
.pb-badges li { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 700; line-height: 1.3; }
.pb-badges--row li { flex-direction: column; text-align: center; gap: 6px; padding: 12px 8px; border-radius: 12px; background: color-mix(in srgb, var(--cs-text) 4%, var(--cs-bg)); }
.pb-badges .ico { color: var(--cs-button); flex: none; }
</style>
@endpush
@endonce
@if ($items->isNotEmpty())
    <ul class="pb-badges pb-badges--{{ $settings['layout'] }}">
        @foreach ($items as $it)
            <li>{!! \App\Support\IconLibrary::svg($it['icon'] ?: 'check', '', 22) !!}<span>{{ $it['text'] }}</span></li>
        @endforeach
    </ul>
@endif
