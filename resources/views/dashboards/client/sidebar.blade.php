<aside class="sidebar">
<div class="sidebar-sticky">

    {{-- Fidélité --}}
    <div class="sb-loyalty-card">
        <a href="{{ route('client.loyalty.index') }}" class="sb-loyalty-top">
            <span class="sb-loyalty-ico">{!! \App\Support\IconLibrary::svg('gift', '', 26) !!}</span>
            <div style="flex:1;min-width:0">
                <div class="sb-loyalty-pts">{{ number_format($loyaltyPoints, 0, ',', ' ') }} <span>points</span></div>
                <div class="sb-loyalty-value">= {{ number_format($loyaltyPoints, 0, ',', ' ') }} GNF de réduction</div>
            </div>
        </a>
        <div class="sb-loyalty-bar-wrap">
            <div class="sb-loyalty-bar" style="width:{{ $loyaltyProgressPercent }}%"></div>
        </div>
        <a href="{{ route('client.loyalty.index') }}" class="sb-loyalty-next">
            Encore {{ number_format($loyaltyNextMilestone - $loyaltyPoints, 0, ',', ' ') }} points pour {{ number_format($loyaltyNextMilestone, 0, ',', ' ') }} GNF de réduction
        </a>
        <a href="{{ route('client.loyalty.index') }}" class="sb-loyalty-refer">{!! \App\Support\IconLibrary::svg('user', '', 14) !!} Parrainez un ami → +{{ number_format($loyaltyReferralBonus, 0, ',', ' ') }} GNF</a>
    </div>

    {{-- Explorer catégories --}}
    @if(isset($categories) && $categories->isNotEmpty())
    <div class="sb-card sb-card-explorer">
        <div class="sb-hd">{!! $si('grid',15) !!} Explorer</div>
        <div class="sb-cat-wrap-mob">
        <a href="{{ route('client.dashboard') }}?{{ http_build_query(array_filter(array_merge($baseParams, ['cat' => null]))) }}#catalogue" class="sb-cat-item {{ !request('cat') ? 'active' : '' }}">
            <span class="sb-cat-ico">{!! $si('store',17) !!}</span>
            <span class="sb-cat-name">Toutes</span>
            <span class="sb-cat-cnt">{{ $products->total() }}</span>
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('client.dashboard') }}?{{ http_build_query(array_filter(array_merge($baseParams, ['cat' => $cat->type]))) }}#catalogue" class="sb-cat-item {{ request('cat') === $cat->type ? 'active' : '' }}">
            <span class="sb-cat-ico">{!! $getSbEmoji($cat->type) !!}</span>
            <span class="sb-cat-name">{{ $cat->type }}</span>
            <span class="sb-cat-cnt">{{ $cat->shop_count }}</span>
        </a>
        @endforeach
        </div>
    </div>
    @endif

    {{-- Localisation --}}
    @if($countryName)
    <div class="sb-card" style="padding:12px 14px">
        <div style="font-size:10.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px">Où souhaitez-vous être livré ?</div>
        <div style="display:flex;align-items:center;justify-content:space-between;background:var(--grey);border:1px solid var(--border);border-radius:8px;padding:8px 12px">
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:18px">@if($countryFlag){{ $countryFlag }}@else{!! \App\Support\IconLibrary::svg('map-pin', '', 18) !!}@endif</span>
                <span style="font-size:12.5px;font-weight:700;color:var(--text)">{{ $countryName }}</span>
            </div>
            <span style="color:var(--muted);font-size:13px">→</span>
        </div>
    </div>
    @endif



</div>
</aside>
