{{--
=====================================================
PARTIALS/FEATURED-SHOPS.BLADE.PHP
=====================================================
Rangée « Boutiques à la une » : uniquement les boutiques dont la campagne
« Boutique à la une » est en cours (App\Support\Campaigns\SponsoredPlacements::featuredShops).
Rien n'est affiché quand aucune campagne ne tourne. Variable attendue :
  $featuredShops → Collection<Shop> (products_count et sponsored_campaign_id remplis)
=====================================================
--}}
@if(($featuredShops ?? collect())->isNotEmpty())
<style>
.fsh-row { display: flex; gap: 14px; overflow-x: auto; scroll-snap-type: x mandatory; padding: 2px 2px 10px; margin-bottom: 26px; scrollbar-width: thin; }
.fsh-card { flex: 0 0 236px; scroll-snap-align: start; display: flex; flex-direction: column; background: #fff; border-radius: 20px; overflow: hidden; text-decoration: none; color: inherit; box-shadow: 0 0 0 1px #E3E8F2; transition: transform .22s cubic-bezier(.22,1,.36,1), box-shadow .22s; }
.fsh-card:hover { transform: translateY(-3px); box-shadow: 0 0 0 1px #D6E6FF, 0 16px 32px -18px rgba(11,30,61,.35); }
.fsh-card:focus-visible { outline: 3px solid #1F7CF6; outline-offset: 2px; }
.fsh-img { position: relative; height: 118px; background: linear-gradient(135deg, #0B1E3D, #132B52); display: flex; align-items: center; justify-content: center; }
.fsh-img img { width: 100%; height: 100%; object-fit: cover; }
.fsh-initial { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; font-size: 40px; font-weight: 800; color: #fff; opacity: .9; }
.fsh-body { padding: 12px 14px 14px; display: flex; flex-direction: column; gap: 5px; flex: 1; }
.fsh-name { font-size: 14.5px; font-weight: 800; color: #16162A; line-height: 1.25; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.fsh-meta { font-size: 12px; color: #62627C; font-variant-numeric: tabular-nums; }
.fsh-foot { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: auto; padding-top: 6px; }
.fsh-cta { font-size: 12.5px; font-weight: 800; color: #fff; background: #1668D8; border-radius: 999px; padding: 7px 14px; white-space: nowrap; }
.fsh-card:hover .fsh-cta { background: #1260C9; }
@media (max-width: 600px) {
    .fsh-row { gap: 10px; margin-bottom: 20px; }
    .fsh-card { flex-basis: 70%; }
    .fsh-img { height: 100px; }
}
@media (prefers-reduced-motion: reduce) { .fsh-card, .fsh-card:hover { transition: none; transform: none; } }
</style>
<div class="sec-hd">
    <div class="sec-title">{!! \App\Support\IconLibrary::svg('store') !!} Boutiques <strong>à la une</strong></div>
    <a href="{{ route('shops.index') }}" class="sec-link">Voir toutes les boutiques</a>
</div>
<div class="fsh-row">
    @foreach($featuredShops as $shop)
    <a href="{{ \App\Support\Campaigns\SponsoredPlacements::url(\App\Support\Storefront::publicUrl($shop), $shop) }}" class="fsh-card">
        <div class="fsh-img">
            @if($shop->image)
                <img src="{{ asset('storage/' . $shop->image) }}" alt="{{ $shop->name }}" loading="lazy" decoding="async" width="236" height="118">
            @else
                <span class="fsh-initial" aria-hidden="true">{{ mb_strtoupper(mb_substr($shop->name, 0, 1)) }}</span>
            @endif
        </div>
        <div class="fsh-body">
            <div class="fsh-name">{{ $shop->name }}</div>
            <div class="fsh-meta">{{ number_format($shop->products_count, 0, ',', ' ') }} produit{{ $shop->products_count > 1 ? 's' : '' }}</div>
            <div class="fsh-foot">
                @include('partials.sponsored-tag')
                <span class="fsh-cta">Visiter</span>
            </div>
        </div>
    </a>
    @endforeach
</div>
@endif
