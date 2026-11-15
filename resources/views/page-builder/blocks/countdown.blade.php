{{-- Bloc Compte à rebours : vraie échéance (vente flash ou date fixe), disparaît une fois passée --}}
@php $end = \App\PageBuilder\Blocks\Countdown::deadline($settings, $ctx->product); @endphp
@once
@push('pb-styles')
<style data-pb>
.pb-cd { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; padding: 12px 14px; border-radius: 14px; background: #0B1E3D; color: #FFFFFF; }
.pb-cd-label { font-weight: 700; font-size: 14px; }
.pb-cd-time { display: flex; gap: 6px; }
.pb-cd-time span { min-width: 46px; text-align: center; padding: 6px 4px; border-radius: 10px; background: rgba(255, 255, 255, .1); font-weight: 800; font-size: 17px; line-height: 1.1; }
.pb-cd-time small { display: block; font-size: 10px; font-weight: 600; color: #9FB3D3; text-transform: uppercase; letter-spacing: .04em; }
</style>
@endpush
@endonce
@if ($end)
    @php $left = max(0, now()->diffInSeconds($end, false)); @endphp
    <div class="pb-cd" data-pb-countdown="{{ $end->getTimestamp() }}" role="timer" aria-label="{{ $settings['label'] }} : fin le {{ $end->locale('fr')->isoFormat('D MMMM à HH:mm') }}">
        <span class="pb-cd-label">{{ $settings['label'] }}</span>
        <span class="pb-cd-time pb-num" aria-hidden="true">
            <span><b data-u="d">{{ intdiv((int) $left, 86400) }}</b><small>j</small></span>
            <span><b data-u="h">{{ sprintf('%02d', intdiv((int) $left % 86400, 3600)) }}</b><small>h</small></span>
            <span><b data-u="m">{{ sprintf('%02d', intdiv((int) $left % 3600, 60)) }}</b><small>min</small></span>
            <span><b data-u="s">{{ sprintf('%02d', (int) $left % 60) }}</b><small>s</small></span>
        </span>
    </div>
@elseif ($ctx->editing)
    <p class="pb-edit-note">Compte à rebours : {{ $settings['source'] === 'flash' ? 'aucune vente flash en cours sur ce produit.' : 'choisis une date de fin à venir.' }} Il s'affiche seulement avant une vraie échéance.</p>
@endif
