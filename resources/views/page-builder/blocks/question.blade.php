{{-- Bloc Question (section Questions fréquentes) --}}
@if (filled($settings['question']))
    <details>
        <summary>{{ $settings['question'] }}</summary>
        @if (filled($settings['answer']))<div class="pb-rte">{!! $settings['answer'] !!}</div>@endif
    </details>
@endif
