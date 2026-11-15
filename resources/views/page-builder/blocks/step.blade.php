{{-- Bloc Étape (section Étapes de commande) --}}
<div class="pb-step-item">
    <span class="pb-step-ico" aria-hidden="true">{!! \App\Support\IconLibrary::svg($settings['icon'] ?: 'check', '', 22) !!}</span>
    <strong>{{ $settings['title'] }}</strong>
    @if (filled($settings['text']))<span>{{ $settings['text'] }}</span>@endif
</div>
