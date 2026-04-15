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

    /* GALLERY */
    .gallery {
        display: flex;
        gap: 8px;
        margin-top: 10px;
        overflow-x: auto;
    }

    .gallery img {
        width: 70px;
        height: 70px;
        border-radius: 10px;
        object-fit: cover;
        cursor: pointer;
        border: 2px solid transparent;
        flex-shrink: 0;
        transition: 0.2s;
    }

    .gallery img:hover {
        border-color: #ff6a00;
        transform: scale(1.05);
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
        <img id="mainImage"
             src="{{ asset('storage/' . $product->image) }}"
             class="main-image">

        <div class="gallery">
            <img src="{{ asset('storage/' . $product->image) }}"
                 onclick="changeImage(this.src)">

            @foreach($gallery as $img)
                <img src="{{ asset('storage/' . $img) }}"
                     onclick="changeImage(this.src)">
            @endforeach
        </div>
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
    function changeImage(src) {
        document.getElementById('mainImage').src = src;
    }

    function goBack() {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = "/client/dashboard";
        }
    }
</script>