@if(isset($flashProducts) && $flashProducts->isNotEmpty())
@php
    $flashLoop = $flashProducts->count() >= 4;
    $flashPasses = $flashLoop ? 2 : 1;
@endphp
<div class="flash-section">
    <div class="flash-section-hd">
        <div>
            <div class="flash-section-title"><span class="bolt">{!! \App\Support\IconLibrary::svg('zap', '', 18) !!}</span> Ventes Flash</div>
            <div class="flash-section-sub">Offres à durée limitée — dépêchez-vous !</div>
        </div>
    </div>
    <div class="flash-row-outer {{ $flashLoop ? 'flash-loop' : '' }}" id="flashRowOuter">
        <div class="flash-row {{ $flashLoop ? 'flash-auto' : '' }}" id="flashRow">
            @for ($fpass = 0; $fpass < $flashPasses; $fpass++)
                @foreach($flashProducts as $fp)
                <a href="{{ route('client.products.show', $fp) }}" class="flash-card" @if($fpass > 0) aria-hidden="true" tabindex="-1" @endif>
                    <div class="flash-card-img">
                        @if($fp->image)
                            <img src="{{ \App\Services\ImageOptimizer::url($fp->image, 'medium') }}" alt="{{ $fp->name }}" loading="lazy">
                        @else
                            <div class="reco-card-ph">{!! $si('bag', 26) !!}</div>
                        @endif
                        <span class="flash-card-badge">-{{ $fp->flash_discount_percent }}%</span>
                    </div>
                    <div class="flash-card-body">
                        <div class="flash-card-shop">{{ $fp->shop->name ?? '' }}</div>
                        <div class="flash-card-name">{{ $fp->name }}</div>
                        <div class="flash-card-price-row">
                            <span class="flash-card-price">{{ number_format($fp->flash_price, 0, ',', ' ') }}</span>
                            <span class="flash-card-orig">{{ number_format($fp->price, 0, ',', ' ') }}</span>
                            <span style="font-size:10px;color:var(--muted)">{{ $fp->shop->currency ?? 'GNF' }}</span>
                        </div>
                        <div class="flash-card-countdown" data-ends="{{ $fp->flash_ends_at->timestamp }}">
                            {!! \App\Support\IconLibrary::svg('clock', '', 14) !!} <span class="flash-cd-val">--:--:--</span>
                        </div>
                    </div>
                </a>
                @endforeach
            @endfor
        </div>
    </div>
</div>
@endif
