@extends('layouts.app')
@php $bodyClass = 'is-dashboard'; @endphp

@section('title', $product->name)

@php
    $gallery = $product->gallery ? json_decode($product->gallery, true) : [];
@endphp

<style>
    body {
        background: #f6f7fb;
    }

    /* WRAPPER */
    .product-page {
        max-width: 1100px;
        margin: 25px auto;
        background: #fff;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(0,0,0,0.08);
        display: flex;
        flex-wrap: wrap;
        position: relative;
    }

    /* CLOSE BTN */
    .close-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        border: none;
        background: #ff6a00;
        color: #fff;
        font-size: 20px;
        cursor: pointer;
        z-index: 10;
        transition: 0.2s;
    }

    .close-btn:hover {
        background: #e85f00;
        transform: scale(1.05);
    }

    /* LEFT IMAGE SECTION */
    .left {
        flex: 1;
        min-width: 320px;
        background: #fafafa;
        padding: 15px;
    }

    .main-image {
    width: 100%;
    flex: 1;              /* 🔥 remplit tout l’espace */
    height: 300px;           /* hauteur fixe */
    min-height: 420px;    /* fallback */
    object-fit: cover;    /* image propre pleine zone */
    border-radius: 12px;
    background: #fff;
}

    /* IMAGE WRAP */
    .main-img-wrap {
        position: relative; display: flex; align-items: center; justify-content: center;
        background: #fafafa; border-radius: 12px; overflow: hidden; margin-bottom: 10px;
    }

    /* GALLERY THUMBS */
    .gallery {
        display: flex;
        gap: 8px;
        margin-top: 10px;
        overflow-x: auto;
        padding-bottom: 4px;
    }
    .gallery::-webkit-scrollbar { height: 4px; }
    .gallery::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }

    .gal-thumb {
        width: 70px;
        height: 70px;
        border-radius: 10px;
        object-fit: cover;
        cursor: pointer;
        border: 2px solid transparent;
        flex-shrink: 0;
        transition: 0.2s;
    }
    .gal-thumb:hover { border-color: #ff6a00; transform: scale(1.05); }
    .gal-thumb.active { border-color: #ff6a00; box-shadow: 0 0 0 2px #ffe0cc; }

    /* ARROWS */
    .gal-arrow {
        position: absolute; top: 50%; transform: translateY(-50%);
        background: rgba(0,0,0,.42); color: #fff; border: none; border-radius: 50%;
        width: 38px; height: 38px; font-size: 22px; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: background .15s; z-index: 2; line-height: 1;
    }
    .gal-arrow:hover { background: rgba(0,0,0,.7); }
    .gal-arrow-l { left: 8px; }
    .gal-arrow-r { right: 8px; }
    .gal-counter {
        position: absolute; bottom: 8px; right: 10px;
        background: rgba(0,0,0,.5); color: #fff; font-size: 11px; font-weight: 700;
        padding: 2px 8px; border-radius: 20px; pointer-events: none;
    }

    /* RIGHT CONTENT */
    .right {
        flex: 1;
        min-width: 320px;
        padding: 25px;
    }

    .title {
        font-size: 26px;
        font-weight: 700;
        color: #222;
    }

    .price-box {
        margin: 12px 0;
    }

    .price {
        font-size: 28px;
        font-weight: bold;
        color: #ff6a00;
    }

    .old-price {
        font-size: 16px;
        text-decoration: line-through;
        color: #aaa;
        margin-left: 8px;
    }

    .badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 15px;
    }

    .badge {
        background: #fff3e6;
        color: #ff6a00;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .desc {
        margin-top: 18px;
        line-height: 1.7;
        color: #555;
        font-size: 14px;
    }

    /* MOBILE */
    @media (max-width: 768px) {
        .product-page {
            flex-direction: column;
            margin: 10px;
        }

        .main-image {
            height: 280px;
        }

        .right {
            padding: 15px;
        }

        .title {
            font-size: 20px;
        }

        .price {
            font-size: 22px;
        }

        .gallery img {
            width: 60px;
            height: 60px;
        }
    }
</style>

<div class="product-page">

    {{-- CLOSE --}}
    <button class="close-btn" onclick="goBack()">×</button>

    {{-- LEFT IMAGE --}}
    <div class="left">
        <div class="main-img-wrap" id="mainImgWrap">
            <img id="mainImage"
                 src="{{ \App\Services\ImageOptimizer::url($product->image, 'medium') ?? asset('storage/'.$product->image) }}"
                 class="main-image" style="cursor:zoom-in" onclick="openLightbox(_curIdx)">
            @if(count($gallery) > 0)
            <button class="gal-arrow gal-arrow-l" onclick="switchIdx(_curIdx - 1)">‹</button>
            <button class="gal-arrow gal-arrow-r" onclick="switchIdx(_curIdx + 1)">›</button>
            <div class="gal-counter" id="galCounter"></div>
            @endif
        </div>

        @php
            $allPhotos = array_filter(array_merge(
                [$product->image ? (\App\Services\ImageOptimizer::url($product->image, 'medium') ?? asset('storage/'.$product->image)) : null],
                array_map(fn($g) => \App\Services\ImageOptimizer::url($g, 'medium') ?? asset('storage/'.$g), $gallery)
            ));
            $allThumbs = array_filter(array_merge(
                [$product->image ? (\App\Services\ImageOptimizer::url($product->image, 'thumb') ?? asset('storage/'.$product->image)) : null],
                array_map(fn($g) => \App\Services\ImageOptimizer::url($g, 'thumb') ?? asset('storage/'.$g), $gallery)
            ));
            $allPhotos = array_values($allPhotos);
            $allThumbs = array_values($allThumbs);
        @endphp

        @if(count($allThumbs) > 1)
        <div class="gallery" id="galThumbRow">
            @foreach($allThumbs as $i => $thumb)
            <img src="{{ $thumb }}" class="gal-thumb {{ $i === 0 ? 'active' : '' }}"
                 data-idx="{{ $i }}" onclick="switchIdx({{ $i }})" alt="Photo {{ $i+1 }}" loading="lazy">
            @endforeach
        </div>
        @endif
    </div>

    {{-- RIGHT INFO --}}
    <div class="right">

        <div class="title">{{ $product->name }}</div>

        <div class="price-box">
            <span class="price">{{ $product->price }} GNF</span>

            @if($product->original_price)
                <span class="old-price">{{ $product->original_price }} GNF</span>
            @endif
        </div>

        <div class="badges">
            <div class="badge">📦 Stock: {{ $product->stock ?? 0 }}</div>
            <div class="badge">📂 {{ $product->category }}</div>
            @if($product->unit)
                <div class="badge">⚖️ {{ $product->unit }}</div>
            @endif
        </div>

        <div class="desc">
            {{ $product->description }}
        </div>

    </div>
</div>

<script>
/* ── Photos ── */
const _allPhotos = @json($allPhotos);
let _curIdx = 0;

function switchIdx(idx) {
    if (!_allPhotos.length) return;
    _curIdx = ((idx % _allPhotos.length) + _allPhotos.length) % _allPhotos.length;
    document.getElementById('mainImage').src = _allPhotos[_curIdx];

    /* miniatures */
    document.querySelectorAll('.gal-thumb').forEach((t, i) => {
        t.classList.toggle('active', i === _curIdx);
        if (i === _curIdx) t.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    });

    /* compteur */
    const ctr = document.getElementById('galCounter');
    if (ctr && _allPhotos.length > 1) ctr.textContent = `${_curIdx + 1} / ${_allPhotos.length}`;
}

/* Lightbox plein écran */
function openLightbox(startIdx) {
    if (!_allPhotos.length) return;
    let idx = startIdx || _curIdx;

    const overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.93);z-index:9999;display:flex;align-items:center;justify-content:center;';

    const img = document.createElement('img');
    img.style.cssText = 'max-width:92%;max-height:92%;object-fit:contain;border-radius:10px;user-select:none;';

    const setImg = (i) => {
        idx = ((i % _allPhotos.length) + _allPhotos.length) % _allPhotos.length;
        img.src = _allPhotos[idx];
        ctr.textContent = _allPhotos.length > 1 ? `${idx + 1} / ${_allPhotos.length}` : '';
    };

    const ctr = document.createElement('div');
    ctr.style.cssText = 'position:absolute;bottom:20px;left:50%;transform:translateX(-50%);color:#fff;font-size:13px;font-weight:700;background:rgba(0,0,0,.5);padding:4px 14px;border-radius:20px;';

    const close = document.createElement('button');
    close.textContent = '✕';
    close.style.cssText = 'position:absolute;top:16px;right:16px;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:20px;width:40px;height:40px;border-radius:50%;cursor:pointer;';
    close.onclick = () => document.body.removeChild(overlay);

    let touchX = 0;
    overlay.addEventListener('touchstart', e => { touchX = e.touches[0].clientX; });
    overlay.addEventListener('touchend', e => {
        const dx = e.changedTouches[0].clientX - touchX;
        if (Math.abs(dx) > 40) setImg(idx + (dx < 0 ? 1 : -1));
    });
    overlay.addEventListener('click', e => { if (e.target === overlay) document.body.removeChild(overlay); });

    if (_allPhotos.length > 1) {
        const mkArrow = (dir, label) => {
            const btn = document.createElement('button');
            btn.textContent = label;
            btn.style.cssText = `position:absolute;top:50%;transform:translateY(-50%);${dir}:16px;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:28px;width:48px;height:48px;border-radius:50%;cursor:pointer;`;
            btn.onclick = (e) => { e.stopPropagation(); setImg(idx + (dir === 'left' ? -1 : 1)); };
            return btn;
        };
        overlay.appendChild(mkArrow('left', '‹'));
        overlay.appendChild(mkArrow('right', '›'));
    }

    overlay.appendChild(img);
    overlay.appendChild(ctr);
    overlay.appendChild(close);
    document.body.appendChild(overlay);
    setImg(idx);

    const onKey = (e) => {
        if (e.key === 'Escape') { document.body.removeChild(overlay); document.removeEventListener('keydown', onKey); }
        if (e.key === 'ArrowLeft')  setImg(idx - 1);
        if (e.key === 'ArrowRight') setImg(idx + 1);
    };
    document.addEventListener('keydown', onKey);
}

/* Initialiser le compteur au chargement */
document.addEventListener('DOMContentLoaded', () => {
    if (_allPhotos.length > 1) {
        const ctr = document.getElementById('galCounter');
        if (ctr) ctr.textContent = `1 / ${_allPhotos.length}`;
    }
});

function goBack() {
    if (window.history.length > 1) window.history.back();
    else window.location.href = '/client/dashboard';
}
</script>