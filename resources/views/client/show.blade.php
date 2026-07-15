@extends('layouts.app')

@php $bodyClass = 'is-dashboard'; @endphp
@section('title', $product->name)

@php
    $gallery      = $product->gallery ? json_decode($product->gallery, true) : [];
    $hasPromo     = $product->original_price && $product->original_price > $product->price;
    $savings      = $hasPromo ? $product->original_price - $product->price : 0;
    $discountPct  = $hasPromo ? round(100 - ($product->price / $product->original_price * 100)) : 0;
    $stockOk      = ($product->stock ?? 0) > 5;
    $stockLow     = ($product->stock ?? 0) > 0 && ($product->stock ?? 0) <= 5;
    $stockOut     = ($product->stock ?? 0) <= 0;
    $unavailable  = $stockOut || $product->is_available === false;

    $allPhotos = array_values(array_filter(array_merge(
        [$product->image ? asset('storage/'.$product->image) : null],
        array_map(fn($g) => asset('storage/'.$g), $gallery)
    )));
    $allThumbs = $allPhotos;

    $tags      = $product->tags      ? array_filter(array_map('trim', explode(',', $product->tags)))      : [];
    $allergens = $product->allergens ? array_filter(array_map('trim', explode(',', $product->allergens))) : [];
@endphp

<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{ color-scheme: light only; }
body{background:#f0f2f5 !important;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:#111 !important}
@media (prefers-color-scheme: dark){
    body,*{background-color:revert;color:revert}
    body{background:#f0f2f5 !important;color:#111 !important}
    .card{background:#fff !important}
    .info-section,.product-name,.price,.desc,.section-label{color:inherit !important}
    .price{color:#111 !important}
    .desc{color:#4b5563 !important}
}

/* ── BACK BAR ── */
.back-bar{
    position:sticky;top:0;z-index:50;
    background:rgba(255,255,255,.92);
    backdrop-filter:blur(12px);
    border-bottom:1px solid #e5e7eb;
    display:flex;align-items:center;gap:10px;
    padding:0 16px;height:52px;
}
.back-btn{
    display:inline-flex;align-items:center;gap:6px;
    background:none;border:none;cursor:pointer;
    font-size:14px;font-weight:600;color:#374151;
    padding:6px 0;
}
.back-btn svg{flex-shrink:0}
.back-btn:hover{color:#111}
.bc{display:flex;align-items:center;gap:5px;font-size:12px;color:#9ca3af;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}
.bc a{color:#9ca3af;text-decoration:none}.bc a:hover{color:#374151}
.bc-sep{flex-shrink:0}

/* ── LAYOUT ── */
.page{max-width:1080px;margin:0 auto;padding:24px 16px 80px}

.card{background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07),0 8px 24px rgba(0,0,0,.05)}

/* ── GALLERY ── */
.gallery-section{position:relative;background:#f9fafb}
.main-img-wrap{position:relative;overflow:hidden;aspect-ratio:4/3;background:#f3f4f6;cursor:zoom-in}
.main-image{width:100%;height:100%;object-fit:cover;transition:opacity .18s}
.no-img{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:72px;color:#d1d5db}

/* badges sur la photo */
.img-badges{position:absolute;top:12px;left:12px;display:flex;flex-direction:column;gap:6px}
.bdg{display:inline-block;padding:4px 10px;border-radius:30px;font-size:11px;font-weight:700;letter-spacing:.3px}
.bdg-promo{background:#ef4444;color:#fff}
.bdg-featured{background:#f59e0b;color:#fff}

/* flèches */
.arrow{
    position:absolute;top:50%;transform:translateY(-50%);
    width:36px;height:36px;border-radius:50%;border:none;
    background:rgba(0,0,0,.45);color:#fff;font-size:20px;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    transition:.15s;opacity:0;z-index:2;
}
.main-img-wrap:hover .arrow{opacity:1}
@media(hover:none){.arrow{opacity:1;width:32px;height:32px;font-size:18px}}
.arrow:hover{background:rgba(0,0,0,.7)}
.arrow-l{left:10px}.arrow-r{right:10px}
.img-counter{
    position:absolute;bottom:10px;right:12px;
    background:rgba(0,0,0,.5);color:#fff;
    font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;pointer-events:none
}

/* miniatures */
.thumbs{display:flex;gap:8px;padding:10px 12px;overflow-x:auto;background:#fff;border-top:1px solid #f3f4f6;scroll-snap-type:x mandatory}
.thumbs::-webkit-scrollbar{height:3px}.thumbs::-webkit-scrollbar-thumb{background:#e5e7eb;border-radius:3px}
.thumb{
    width:60px;height:60px;border-radius:10px;object-fit:cover;
    flex-shrink:0;scroll-snap-align:start;cursor:pointer;
    border:2px solid transparent;transition:.15s;opacity:.65
}
.thumb:hover,.thumb.active{opacity:1;border-color:#111}

/* ── INFO SECTION ── */
.info-section{padding:22px 20px 28px}

.cat-chip{
    display:inline-block;font-size:11px;font-weight:700;
    color:#6366f1;background:#eef2ff;padding:3px 10px;
    border-radius:20px;margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px
}
.wish-btn{
    width:38px;height:38px;border-radius:50%;flex-shrink:0;
    background:#f9fafb;border:1px solid #e5e7eb;cursor:pointer;
    display:flex;align-items:center;justify-content:center;font-size:17px;
    transition:transform .15s,background .15s;
}
.wish-btn:hover{transform:scale(1.08);background:#fef2f2}
.wish-btn.active{background:#fef2f2;border-color:#fecaca}
.product-name{font-size:22px;font-weight:800;line-height:1.25;color:#111;margin-bottom:16px}

/* PRIX */
.price-row{display:flex;align-items:baseline;gap:10px;flex-wrap:wrap;margin-bottom:6px}
.price{font-size:28px;font-weight:900;color:#111}
.currency{font-size:16px;font-weight:700;color:#6b7280}
.old-price{font-size:14px;color:#9ca3af;text-decoration:line-through}
.savings-pill{
    display:inline-flex;align-items:center;gap:4px;
    background:#dcfce7;color:#16a34a;
    font-size:11.5px;font-weight:700;
    padding:3px 10px;border-radius:20px;margin-bottom:16px
}

/* VENTE FLASH */
.flash-badge{
    display:inline-flex;align-items:center;gap:5px;
    background:linear-gradient(135deg,#dc2626,#f97316);color:#fff;
    font-size:11.5px;font-weight:800;letter-spacing:.3px;
    padding:4px 12px;border-radius:20px;margin-bottom:10px;
    animation:flashPulse 1.6s ease-in-out infinite;
}
@keyframes flashPulse{0%,100%{opacity:1}50%{opacity:.75}}
.flash-countdown{
    display:inline-flex;align-items:center;gap:6px;
    background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;
    font-size:12.5px;font-weight:700;
    padding:6px 12px;border-radius:10px;margin-bottom:16px;
    font-family:ui-monospace,monospace;
}

/* VARIANTES */
.variant-picker{margin-bottom:18px}
.variant-pills{display:flex;flex-wrap:wrap;gap:8px}
.variant-pill{padding:9px 16px;border-radius:20px;border:1.5px solid #e5e7eb;background:#f9fafb;font-size:13px;font-weight:600;color:#374151;cursor:pointer;transition:all .15s;font-family:inherit}
.variant-pill:hover:not(.disabled){border-color:#111}
.variant-pill.selected{background:#111;color:#fff;border-color:#111}
.variant-pill.disabled{opacity:.45;text-decoration:line-through;cursor:not-allowed}

/* STOCK */
.stock-line{display:flex;align-items:center;gap:7px;font-size:13px;font-weight:600;margin-bottom:18px}
.dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.dot-ok {background:#22c55e;box-shadow:0 0 0 3px #dcfce7}
.dot-low{background:#f59e0b;box-shadow:0 0 0 3px #fef9c3}
.dot-out{background:#ef4444;box-shadow:0 0 0 3px #fee2e2}
.stock-ok-c {color:#15803d}.stock-low-c{color:#b45309}.stock-out-c{color:#dc2626}
.stock-note{color:#9ca3af;font-weight:400}

/* SÉPARATEUR */
.sep{height:1px;background:#f3f4f6;margin:18px 0}

/* DESCRIPTION */
.section-label{font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.7px;margin-bottom:8px}
.desc{font-size:14px;line-height:1.75;color:#4b5563;white-space:pre-line}

/* CHIPS (unité, prép) */
.chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px}
.chip{
    display:inline-flex;align-items:center;gap:5px;
    background:#f9fafb;border:1px solid #e5e7eb;
    color:#374151;font-size:12.5px;font-weight:600;
    padding:5px 12px;border-radius:20px
}

/* TAGS */
.tags{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:18px}
.tag{font-size:12px;font-weight:600;color:#6366f1;background:#eef2ff;padding:4px 10px;border-radius:20px}

/* ALLERGENS */
.allergens{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:18px}
.allergen{font-size:12px;font-weight:600;color:#b45309;background:#fef9c3;border:1px solid #fde68a;padding:4px 10px;border-radius:20px}

/* BOUTIQUE */
.shop-row{
    display:flex;align-items:center;gap:12px;
    background:#f9fafb;border:1px solid #e5e7eb;
    border-radius:14px;padding:13px 15px;margin-bottom:22px;
    text-decoration:none;transition:.15s;
}
.shop-row:hover{background:#f3f4f6;border-color:#d1d5db}
.shop-av{
    width:42px;height:42px;border-radius:11px;
    display:flex;align-items:center;justify-content:center;
    font-size:17px;font-weight:800;color:#fff;flex-shrink:0;overflow:hidden
}
.shop-av img{width:100%;height:100%;object-fit:cover}
.shop-name{font-size:14px;font-weight:700;color:#111}
.shop-type{font-size:11.5px;color:#9ca3af;margin-top:1px}
.shop-chevron{margin-left:auto;color:#9ca3af;font-size:18px;font-weight:300}

/* CTA */
.cta-stack{display:flex;flex-direction:column;gap:10px}
.btn-order{
    display:flex;align-items:center;justify-content:center;gap:8px;
    background:#111;color:#fff;
    font-size:15px;font-weight:800;letter-spacing:.2px;
    padding:16px 24px;border-radius:14px;
    text-decoration:none;transition:.18s;border:none;cursor:pointer
}
.btn-order:hover{background:#000;transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,0,0,.2)}
.btn-order.off{background:#e5e7eb;color:#9ca3af;pointer-events:none;cursor:default;transform:none;box-shadow:none}
.btn-back{
    display:flex;align-items:center;justify-content:center;gap:7px;
    background:#fff;border:1.5px solid #e5e7eb;color:#374151;
    font-size:14px;font-weight:700;padding:13px 24px;border-radius:14px;
    text-decoration:none;transition:.15s
}
.btn-back:hover{border-color:#9ca3af;color:#111}

/* ══ DESKTOP — côte à côte ══ */
@media(min-width:780px){
    .page{padding:32px 24px 80px}
    .card{display:grid;grid-template-columns:1fr 1fr;align-items:start}
    .gallery-section{border-right:1px solid #f3f4f6}
    .main-img-wrap{aspect-ratio:1/1}
    .info-section{padding:30px 28px 36px;overflow-y:auto;max-height:calc(100vh - 120px)}
    .product-name{font-size:26px}
    .price{font-size:32px}
}
@media(min-width:1024px){
    .page{padding:40px 32px 80px}
    .product-name{font-size:28px}
}
</style>

{{-- BACK BAR --}}
<div class="back-bar">
    <button class="back-btn" onclick="goBack()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Retour
    </button>
    <div class="bc">
        <a href="{{ auth()->check() ? route('client.dashboard') : url('/') }}">Accueil</a>
        <span class="bc-sep">›</span>
        <a href="{{ auth()->check() ? route('client.products.index') : route('shops.index') }}">Produits</a>
        <span class="bc-sep">›</span>
        <span>{{ Str::limit($product->name, 28) }}</span>
    </div>
</div>

<div class="page">
<div class="card">

    {{-- ════ GALERIE ════ --}}
    <div class="gallery-section">
        <div class="main-img-wrap" id="mainImgWrap">
            @if(count($allPhotos))
                <img id="mainImage" src="{{ $allPhotos[0] }}" class="main-image" alt="{{ $product->name }}" onclick="openLightbox(_curIdx)">
            @else
                <div class="no-img">🛍</div>
            @endif

            <div class="img-badges">
                @if($hasPromo)<span class="bdg bdg-promo">-{{ $discountPct }}%</span>@endif
                @if($product->is_featured)<span class="bdg bdg-featured">⭐ Vedette</span>@endif
            </div>

            @if(count($allPhotos) > 1)
                <button class="arrow arrow-l" onclick="switchIdx(_curIdx-1)">‹</button>
                <button class="arrow arrow-r" onclick="switchIdx(_curIdx+1)">›</button>
                <div class="img-counter" id="galCtr">1 / {{ count($allPhotos) }}</div>
            @endif
        </div>

        @if(count($allThumbs) > 1)
        <div class="thumbs" id="thumbRow">
            @foreach($allThumbs as $i => $t)
                <img src="{{ $t }}" class="thumb {{ $i===0?'active':'' }}" data-idx="{{ $i }}"
                     onclick="switchIdx({{ $i }})" alt="Photo {{ $i+1 }}" loading="lazy">
            @endforeach
        </div>
        @endif
    </div>

    {{-- ════ INFOS ════ --}}
    <div class="info-section">

        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px">
            @if($product->category)
                <div class="cat-chip">{{ $product->category }}</div>
            @else
                <div></div>
            @endif
            @auth
            <button type="button" class="wish-btn {{ ($isFavorited ?? false) ? 'active' : '' }}"
                    id="wishBtn" onclick="toggleWish({{ $product->id }})" title="Ajouter à ma liste de souhaits">
                <span id="wishIcon">{{ ($isFavorited ?? false) ? '❤️' : '🤍' }}</span>
            </button>
            @endauth
        </div>

        <div class="product-name">{{ $product->name }}</div>

        {{-- PRIX --}}
        @if($product->is_flash_active)
            <div class="flash-badge">⚡ VENTE FLASH −{{ $product->flash_discount_percent }}%</div>
            <div class="price-row">
                <span class="price" style="color:#dc2626">{{ number_format($product->flash_price, 0, ',', ' ') }}</span>
                <span class="currency">GNF</span>
                <span class="old-price">{{ number_format($product->price, 0, ',', ' ') }} GNF</span>
            </div>
            <div class="flash-countdown" id="flashCountdown" data-ends="{{ $product->flash_ends_at->timestamp }}">
                ⏳ Se termine dans <span id="flashTimer">--:--:--</span>
            </div>
        @else
            <div class="price-row">
                <span class="price">{{ number_format($product->price, 0, ',', ' ') }}</span>
                <span class="currency">GNF</span>
                @if($hasPromo)
                    <span class="old-price">{{ number_format($product->original_price, 0, ',', ' ') }} GNF</span>
                @endif
            </div>
            @if($hasPromo)
                <div class="savings-pill">✓ Économie de {{ number_format($savings, 0, ',', ' ') }} GNF</div>
            @endif
        @endif

        {{-- VARIANTES --}}
        @if($variants->isNotEmpty())
        <div class="variant-picker" id="variantPicker">
            <div class="section-label" style="margin-bottom:8px">Choisissez une option</div>
            <div class="variant-pills">
                @foreach($variants as $v)
                <button type="button" class="variant-pill {{ $v->out_of_stock ? 'disabled' : '' }}"
                        data-id="{{ $v->id }}" data-price="{{ $v->effective_price }}" data-image="{{ $v->image_url }}"
                        onclick="selectVariant(this)" {{ $v->out_of_stock ? 'disabled' : '' }}>
                    {{ $v->name }}{{ $v->out_of_stock ? ' (épuisé)' : '' }}
                </button>
                @endforeach
            </div>
            <div class="field-error" id="variantError" style="display:none;margin-top:8px;color:#dc2626;font-size:12.5px">
                Veuillez choisir une option avant de commander.
            </div>
        </div>
        @else
        {{-- STOCK --}}
        <div class="stock-line">
            @if($stockOk)
                <span class="dot dot-ok"></span>
                <span class="stock-ok-c">En stock</span>
                <span class="stock-note">— {{ number_format($product->stock) }} {{ $product->unit ?: 'unités' }}</span>
            @elseif($stockLow)
                <span class="dot dot-low"></span>
                <span class="stock-low-c">Stock limité</span>
                <span class="stock-note">— plus que {{ $product->stock }}</span>
            @else
                <span class="dot dot-out"></span>
                <span class="stock-out-c">Rupture de stock</span>
            @endif
        </div>
        @endif

        {{-- CHIPS --}}
        @php $chips = [] @endphp
        @if($product->unit) @php $chips[]=['⚖️',$product->unit] @endphp @endif
        @if($product->preparation_time) @php $chips[]=['⏱',$product->preparation_time.' min'] @endphp @endif
        @if(count($chips))
        <div class="chips">
            @foreach($chips as [$ico,$txt])
                <span class="chip">{{ $ico }} {{ $txt }}</span>
            @endforeach
        </div>
        @endif

        @if($product->description)
        <div class="sep"></div>
        <div class="section-label">Description</div>
        <div class="desc">{{ $product->description }}</div>
        @endif

        @if(count($tags))
        <div class="sep"></div>
        <div class="tags">
            @foreach($tags as $t)<span class="tag"># {{ $t }}</span>@endforeach
        </div>
        @endif

        @if(count($allergens))
        <div class="sep"></div>
        <div class="section-label">⚠️ Allergènes</div>
        <div class="allergens">
            @foreach($allergens as $a)<span class="allergen">{{ $a }}</span>@endforeach
        </div>
        @endif

        @if($shop)
        <div class="sep"></div>
        @php
            $grads=['linear-gradient(135deg,#667eea,#764ba2)','linear-gradient(135deg,#f5576c,#f093fb)','linear-gradient(135deg,#4facfe,#00c6fb)','linear-gradient(135deg,#ee0979,#ff6a00)','linear-gradient(135deg,#11998e,#38ef7d)','linear-gradient(135deg,#fc4a1a,#f7b733)'];
            $grad=$grads[abs(crc32($shop->name??''))%count($grads)];
        @endphp
        <a href="{{ auth()->check() ? route('client.shops.show', $shop) : route('public.shops.products', $shop) }}" class="shop-row">
            <div class="shop-av" style="background:{{ $grad }}">
                @if($shop->image)
                    <img src="{{ asset('storage/'.$shop->image) }}" alt="{{ $shop->name }}">
                @else
                    {{ strtoupper(mb_substr($shop->name??'B',0,1)) }}
                @endif
            </div>
            <div>
                <div class="shop-name">{{ $shop->name }}</div>
                @if($shop->type)<div class="shop-type">{{ $shop->type }}</div>@endif
            </div>
            <span class="shop-chevron">›</span>
        </a>
        @endif

        <div class="sep"></div>

        {{-- CTA --}}
        <div class="cta-stack">
            @if(!$unavailable)
                <a href="{{ route('client.orders.createFromProduct', $product) }}"
                   data-base-href="{{ route('client.orders.createFromProduct', $product) }}"
                   id="orderCta" class="btn-order"
                   @if($variants->isNotEmpty()) onclick="return checkVariantSelected(event)" @endif>
                    🛒 Commander maintenant
                </a>
            @else
                <span class="btn-order off">🚫 Indisponible actuellement</span>
            @endif
            <a href="{{ auth()->check() ? route('client.products.index') : route('shops.index') }}" class="btn-back">
                ← Tous les produits
            </a>
        </div>

    </div>
</div>
</div>

<script>
const _photos = @json($allPhotos);
let _curIdx = 0;

/* ══ COMPTE À REBOURS VENTE FLASH ══ */
(function(){
    const el = document.getElementById('flashCountdown');
    if(!el) return;
    const endsAt = parseInt(el.dataset.ends, 10) * 1000;
    const timerEl = document.getElementById('flashTimer');

    function tick(){
        const diff = endsAt - Date.now();
        if(diff <= 0){
            timerEl.textContent = 'Terminée';
            clearInterval(_flashInterval);
            return;
        }
        const d = Math.floor(diff / 86400000);
        const h = Math.floor((diff % 86400000) / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        const pad = n => String(n).padStart(2,'0');
        timerEl.textContent = (d > 0 ? d+'j ' : '') + pad(h)+':'+pad(m)+':'+pad(s);
    }
    tick();
    var _flashInterval = setInterval(tick, 1000);
})();

function _sync(idx){
    document.querySelectorAll('.thumb').forEach((t,i)=>{
        t.classList.toggle('active',i===idx);
        if(i===idx) t.scrollIntoView({behavior:'smooth',block:'nearest',inline:'center'});
    });
    const c=document.getElementById('galCtr');
    if(c) c.textContent=`${idx+1} / ${_photos.length}`;
}

function switchIdx(idx){
    if(!_photos.length) return;
    _curIdx=((idx%_photos.length)+_photos.length)%_photos.length;
    const img=document.getElementById('mainImage');
    if(!img) return;
    img.style.opacity='0';
    setTimeout(()=>{img.src=_photos[_curIdx];img.style.opacity='1';},150);
    _sync(_curIdx);
}

document.addEventListener('DOMContentLoaded',()=>{
    if(_photos.length<=1) return;
    const w=document.getElementById('mainImgWrap');
    if(!w) return;
    let sx=0,sy=0;
    w.addEventListener('touchstart',e=>{sx=e.touches[0].clientX;sy=e.touches[0].clientY;},{passive:true});
    w.addEventListener('touchend',e=>{
        const dx=e.changedTouches[0].clientX-sx,dy=e.changedTouches[0].clientY-sy;
        if(Math.abs(dx)>40&&Math.abs(dx)>Math.abs(dy)) switchIdx(_curIdx+(dx<0?1:-1));
    },{passive:true});
});

function openLightbox(si){
    if(!_photos.length) return;
    let idx=si??_curIdx;
    const ov=document.createElement('div');
    ov.style.cssText='position:fixed;inset:0;background:rgba(0,0,0,.96);z-index:9999;display:flex;align-items:center;justify-content:center;';
    const img=document.createElement('img');
    img.style.cssText='max-width:94%;max-height:86dvh;object-fit:contain;border-radius:8px;user-select:none;pointer-events:none;transition:opacity .15s;';
    const ctr=document.createElement('div');
    ctr.style.cssText='position:absolute;bottom:20px;left:50%;transform:translateX(-50%);color:#fff;font-size:12px;font-weight:700;background:rgba(255,255,255,.15);padding:4px 14px;border-radius:20px;white-space:nowrap;';
    const set=(i)=>{
        idx=((i%_photos.length)+_photos.length)%_photos.length;
        img.style.opacity='0';
        setTimeout(()=>{img.src=_photos[idx];img.style.opacity='1';},140);
        if(_photos.length>1) ctr.textContent=`${idx+1} / ${_photos.length}`;
    };
    const close=document.createElement('button');
    close.innerHTML='&times;';
    close.style.cssText='position:absolute;top:14px;right:14px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.2);color:#fff;font-size:22px;width:44px;height:44px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:2;';
    close.onclick=()=>{document.body.removeChild(ov);document.removeEventListener('keydown',onKey);};
    ov.addEventListener('click',e=>{if(e.target===ov)close.onclick();});
    let _sx=0,_sy=0;
    ov.addEventListener('touchstart',e=>{_sx=e.touches[0].clientX;_sy=e.touches[0].clientY;},{passive:true});
    ov.addEventListener('touchend',e=>{
        const dx=e.changedTouches[0].clientX-_sx,dy=e.changedTouches[0].clientY-_sy;
        if(Math.abs(dx)>40&&Math.abs(dx)>Math.abs(dy)) set(idx+(dx<0?1:-1));
    },{passive:true});
    if(_photos.length>1){
        const mk=(s,d)=>{
            const b=document.createElement('button');
            b.textContent=d<0?'‹':'›';
            b.style.cssText=`position:absolute;top:50%;transform:translateY(-50%);${s}:12px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);color:#fff;font-size:28px;width:46px;height:46px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:2;`;
            b.onclick=e=>{e.stopPropagation();set(idx+d);};
            return b;
        };
        ov.appendChild(mk('left',-1));
        ov.appendChild(mk('right',1));
    }
    ov.appendChild(img);ov.appendChild(ctr);ov.appendChild(close);
    document.body.appendChild(ov);set(idx);
    const onKey=e=>{
        if(e.key==='Escape')close.onclick();
        if(e.key==='ArrowLeft')set(idx-1);
        if(e.key==='ArrowRight')set(idx+1);
    };
    document.addEventListener('keydown',onKey);
}

function goBack(){
    if(window.history.length>1) window.history.back();
    else window.location.href='{{ route("client.products.index") }}';
}

/* ══ VARIANTES ══ */
let selectedVariantId = null;

function selectVariant(btn){
    if(btn.disabled) return;
    document.querySelectorAll('.variant-pill').forEach(p=>p.classList.remove('selected'));
    btn.classList.add('selected');
    selectedVariantId = btn.dataset.id;

    const price = parseFloat(btn.dataset.price);
    const priceEl = document.querySelector('.price');
    if(priceEl && !isNaN(price)) priceEl.textContent = Math.round(price).toLocaleString('fr-FR');

    // Si la variante a sa propre photo, on l'affiche ; sinon on revient à la photo par défaut du produit
    const mainImg = document.getElementById('mainImage');
    if(mainImg){
        mainImg.style.opacity = '0';
        const nextSrc = btn.dataset.image || (_photos[0] ?? mainImg.src);
        setTimeout(() => { mainImg.src = nextSrc; mainImg.style.opacity = '1'; }, 150);
    }

    const orderBtn = document.getElementById('orderCta');
    if(orderBtn){
        const base = orderBtn.dataset.baseHref;
        orderBtn.href = base + (base.includes('?') ? '&' : '?') + 'variant_id=' + selectedVariantId;
    }
    const err = document.getElementById('variantError');
    if(err) err.style.display = 'none';
}

function checkVariantSelected(e){
    if(!selectedVariantId){
        e.preventDefault();
        const err = document.getElementById('variantError');
        if(err) err.style.display = 'block';
        document.getElementById('variantPicker')?.scrollIntoView({behavior:'smooth', block:'center'});
        return false;
    }
    return true;
}

async function toggleWish(productId){
    const btn = document.getElementById('wishBtn');
    const icon = document.getElementById('wishIcon');
    if(!btn) return;
    btn.disabled = true;
    try{
        const res = await fetch('/client/products/' + productId + '/favorite', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        const data = await res.json();
        btn.classList.toggle('active', data.favorited);
        icon.textContent = data.favorited ? '❤️' : '🤍';
    } catch(e) {}
    btn.disabled = false;
}
</script>
