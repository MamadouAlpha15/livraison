@if(isset($categories) && $categories->isNotEmpty())
<div id="categories" style="scroll-margin-top:80px">
   
    <div class="pop-cats-row" id="popCatsRow">
        <a href="{{ route('client.dashboard') }}?{{ http_build_query(array_filter(array_merge($baseParams, ['cat' => null]))) }}#catalogue" class="pop-cat-chip {{ !request('cat') ? 'active' : '' }}">
            <span class="pop-cat-ico">{!! $si('store',22) !!}</span>
            <span class="pop-cat-name">Toutes</span>
            <span class="pop-cat-cnt">{{ $products->total() }} produits</span>
        </a>
        @foreach($categories->take(6) as $cat)
        <a href="{{ route('client.dashboard') }}?{{ http_build_query(array_filter(array_merge($baseParams, ['cat' => $cat->type]))) }}#catalogue" class="pop-cat-chip {{ request('cat') === $cat->type ? 'active' : '' }}">
            <span class="pop-cat-ico">{!! $getSbEmoji($cat->type) !!}</span>
            <span class="pop-cat-name">{{ $cat->type }}</span>
            <span class="pop-cat-cnt">{{ $cat->shop_count }} produit{{ $cat->shop_count > 1 ? 's' : '' }}</span>
        </a>
        @endforeach
        @if($categories->count() > 6)
        @foreach($categories->skip(6) as $cat)
        <a href="{{ route('client.dashboard') }}?{{ http_build_query(array_filter(array_merge($baseParams, ['cat' => $cat->type]))) }}#catalogue" class="pop-cat-chip pop-cat-extra {{ request('cat') === $cat->type ? 'active' : '' }}" style="display:none">
            <span class="pop-cat-ico">{!! $getSbEmoji($cat->type) !!}</span>
            <span class="pop-cat-name">{{ $cat->type }}</span>
            <span class="pop-cat-cnt">{{ $cat->shop_count }} produit{{ $cat->shop_count > 1 ? 's' : '' }}</span>
        </a>
        @endforeach
        @endif
    </div>
    @if($categories->count() > 6)
    <div style="text-align:center;margin-top:10px">
        <button id="popCatsMoreBtn" onclick="togglePopCats(this)" data-count="{{ $categories->count() - 6 }}"
            style="display:inline-flex;align-items:center;gap:6px;padding:7px 18px;border-radius:20px;border:1.5px solid var(--orange);background:var(--orange-lt);color:var(--orange);font-size:12px;font-weight:700;cursor:pointer;font-family:var(--font);transition:all .2s">
            ＋ Voir les {{ $categories->count() - 6 }} autres catégories
        </button>
    </div>
    @endif
</div>
@endif
