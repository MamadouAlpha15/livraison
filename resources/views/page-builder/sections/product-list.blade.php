{{-- Section Produits de la boutique --}}
@php $items = \App\PageBuilder\Sections\ProductGrid::products($settings, $ctx); @endphp
@once
@push('pb-styles')
<style data-pb>
.pb-pl h2 { margin: 0 0 16px; font-size: clamp(22px, 4.5vw, 30px); font-weight: 800; line-height: 1.2; }
.pb-pl-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
@media (min-width: 750px) { .pb-pl-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; } }
.pb-pl-card { display: flex; flex-direction: column; gap: 6px; color: var(--cs-text); text-decoration: none; }
.pb-pl-card:focus-visible { outline: 3px solid var(--cs-text); outline-offset: 3px; border-radius: 14px; }
.pb-pl-img { aspect-ratio: 1 / 1; border-radius: 14px; overflow: hidden; background: color-mix(in srgb, var(--cs-text) 6%, var(--cs-bg)); }
.pb-pl-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .25s ease; }
.pb-pl-card:hover .pb-pl-img img { transform: scale(1.03); }
.pb-pl-name { font-weight: 700; font-size: 14px; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.pb-pl-price { font-weight: 800; font-size: 15px; }
</style>
@endpush
@endonce
@if ($items->isNotEmpty())
    <div class="pb-pl">
        @if (filled($settings['heading']))<h2>{{ $settings['heading'] }}</h2>@endif
        <div class="pb-pl-grid">
            @foreach ($items as $p)
                <a class="pb-pl-card" href="{{ route('client.products.show', $p) }}">
                    <span class="pb-pl-img">@if ($p->image)<img src="{{ \App\Services\ImageOptimizer::url($p->image, 'medium') }}" alt="" loading="lazy">@endif</span>
                    <span class="pb-pl-name">{{ $p->name }}</span>
                    <span class="pb-pl-price pb-num">{{ \App\Support\Money::gnf($p->current_price) }}</span>
                </a>
            @endforeach
        </div>
    </div>
@elseif ($ctx->editing)
    <p style="margin:0;padding:12px;border:1px dashed #9DB6E0;border-radius:10px;font-size:13px;color:#45455E;background:#F6F9FF">Produits de la boutique : aucun autre produit en ligne à montrer.</p>
@endif
