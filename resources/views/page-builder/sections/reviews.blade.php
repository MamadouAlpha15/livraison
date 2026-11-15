{{-- Section Avis clients : avis réels uniquement, cachée sans avis --}}
@php
    $stats = $ctx->reviewStats();
    $reviews = $ctx->reviews((int) $settings['limit'], (bool) $settings['only_commented']);
    $who = function ($r) {
        $parts = preg_split('/\s+/', trim((string) $r->client?->name)) ?: [];
        $first = $parts[0] ?? '';
        return $first === '' ? 'Client' : $first . (isset($parts[1]) ? ' ' . mb_strtoupper(mb_substr($parts[1], 0, 1)) . '.' : '');
    };
@endphp
@once
@push('pb-styles')
<style data-pb>
.pb-rv-head { display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 8px 16px; margin-bottom: 18px; }
.pb-rv-head h2 { margin: 0; font-size: clamp(22px, 4.5vw, 30px); font-weight: 800; line-height: 1.2; }
.pb-rv-sum { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: var(--cs-muted); }
.pb-rv-sum strong { color: var(--cs-text); font-size: 18px; }
.pb-rv-sum .ico, .pb-rv-card .ico { color: #F59E0B; }
.pb-rv-items { display: grid; gap: 12px; }
.pb-rv--grid { grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr)); }
.pb-rv-card { margin: 0; padding: 16px; border: 1px solid var(--cs-line); border-radius: 16px; display: flex; flex-direction: column; gap: 10px; background: var(--cs-bg); }
.pb-rv-card blockquote { margin: 0; font-size: 15px; line-height: 1.55; overflow-wrap: anywhere; }
.pb-rv-card figcaption { margin-top: auto; font-size: 13px; color: var(--cs-muted); font-weight: 600; }
.pb-rv-card figcaption strong { color: var(--cs-text); }
</style>
@endpush
@endonce
@if ($reviews->isNotEmpty())
    <div id="avis">
        <div class="pb-rv-head">
            <h2>{{ $settings['heading'] }}</h2>
            @if ($settings['show_summary'] && $stats['avg'])
                <span class="pb-rv-sum pb-num">{!! \App\Support\IconLibrary::stars($stats['avg'], 16) !!} <strong>{{ number_format($stats['avg'], 1, ',', '') }}</strong> · {{ $stats['count'] }} avis</span>
            @endif
        </div>
        <div class="pb-rv-items pb-rv--{{ $settings['layout'] }}">
            @foreach ($reviews as $r)
                <figure class="pb-rv-card">
                    <span aria-label="{{ $r->rating }} sur 5">{!! \App\Support\IconLibrary::stars((float) $r->rating, 14) !!}</span>
                    @if (filled($r->comment))<blockquote>{{ $r->comment }}</blockquote>@endif
                    <figcaption><strong>{{ $who($r) }}</strong> · commande vérifiée · {{ $r->created_at->locale('fr')->isoFormat('MMMM YYYY') }}</figcaption>
                </figure>
            @endforeach
        </div>
    </div>
@elseif ($ctx->editing)
    <p class="pb-edit-note" style="margin:0;padding:12px;border:1px dashed #9DB6E0;border-radius:10px;font-size:13px;color:#45455E;background:#F6F9FF">Avis clients : la section apparaîtra dès le premier avis réel sur ce produit (aucun pour l'instant). Les avis ne peuvent pas être inventés ni modifiés.</p>
@endif
