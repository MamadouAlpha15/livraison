@if(isset($allTopShops) && $allTopShops->isNotEmpty())
@php
$rGrads = [
    'linear-gradient(135deg,#1F7CF6,#1260C9)',
    'linear-gradient(135deg,#f5576c,#f093fb)',
    'linear-gradient(135deg,#4facfe,#00c6fb)',
    'linear-gradient(135deg,#cc2b5e,#753a88)',
    'linear-gradient(135deg,#ee0979,#ff6a00)',
    'linear-gradient(135deg,#24c6dc,#514a9d)',
    'linear-gradient(135deg,#11998e,#38ef7d)',
    'linear-gradient(135deg,#f59e0b,#f97316)',
];
@endphp
<div class="rank-overlay" id="rankOverlay" onclick="if(event.target===this)closeRankingModal()">
    <div class="rank-modal">
        <div class="rank-modal-head">
            <span class="rank-modal-title">{!! \App\Support\IconLibrary::svg('trophy', '', 18) !!} Classement des boutiques</span>
            <button class="rank-modal-close" onclick="closeRankingModal()">✕</button>
        </div>
        <div class="rank-modal-body" id="rankModalBody">
            @foreach($allTopShops as $i => $ts)
            @php
                $rParts   = explode(' ', $ts->name);
                $rInit    = strtoupper(substr($rParts[0],0,1)) . strtoupper(substr($rParts[1] ?? 'X',0,1));
                $rGrad    = $rGrads[abs(crc32($ts->name)) % count($rGrads)];
                $medal    = null;
                $posClass = $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : ''));
            @endphp
            <a href="{{ route('public.shops.products', $ts) }}" class="rank-row" data-rank-idx="{{ $i }}">
                <div class="rank-pos {{ $posClass }}">{{ $medal ?? ($i + 1) }}</div>
                <div class="rank-av" style="background:{{ $rGrad }}">
                    @if($ts->image)
                        <img src="{{ \App\Services\ImageOptimizer::url($ts->image, 'thumb') }}" alt="{{ $ts->name }}">
                    @else
                        {{ $rInit }}
                    @endif
                </div>
                <div class="rank-info">
                    <div class="rank-name">{{ $ts->name }}</div>
                    <div class="rank-stats">
                        {!! \App\Support\IconLibrary::svg('star', '', 13) !!} <strong>{{ $ts->avg_rating ? number_format($ts->avg_rating, 1) : '—' }}</strong>
                        &nbsp;·&nbsp; {{ number_format($ts->sales_count ?? 0) }} ventes
                    </div>
                </div>
                <span class="rank-link">Voir →</span>
            </a>
            @endforeach
        </div>
        <div class="rank-modal-foot" id="rankModalFoot" style="display:none;">
            <span class="rank-pg-info" id="rankPgInfo"></span>
            <div class="rank-pg-btns">
                <button class="rank-pg-btn" id="rankPgPrev" onclick="rankChangePage(-1)">← Préc.</button>
                <button class="rank-pg-btn" id="rankPgNext" onclick="rankChangePage(1)">Suiv. →</button>
            </div>
        </div>
    </div>
</div>
@endif
