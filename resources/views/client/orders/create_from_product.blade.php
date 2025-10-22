@extends('layouts.app')

@push('styles')
<style>
    .product-img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        border-radius: .5rem;
    }
    .product-card {
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0,0,0,.1);
    }
</style>
@endpush

@section('content')
<div class="container">
    <h2 class="mb-4">🛒 Commander ce produit</h2>

    <div class="card product-card shadow-sm mb-4">
        <div class="row g-0">
            <!-- Image produit -->
            <div class="col-md-4">
                @if($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" 
                         alt="{{ $product->name }}" 
                         class="product-img">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center product-img">
                        <span class="text-muted">Pas d’image</span>
                    </div>
                @endif
            </div>

            <!-- Infos produit -->
            <div class="col-md-8">
                <div class="card-body">
                    <h4 class="card-title mb-2">{{ $product->name }}</h4>
                    <h5 class="text-success fw-bold mb-2">
                        {{ number_format($product->price,0,',',' ') }} GNF
                    </h5>
                    <p class="card-text text-muted">
                        {{ $product->description }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire commande -->
    <form method="POST" action="{{ route('client.orders.storeProduct') }}" class="card shadow-sm p-4">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">

        <div class="mb-3" style="max-width: 250px;">
            <label for="quantity" class="form-label">Quantité</label>
            <input type="number" name="quantity" id="quantity" 
                   class="form-control" min="1" value="1" required>
        </div>

        <div class="alert alert-info">
            💵 Paiement prévu : <strong>Cash à la livraison</strong>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success flex-fill">
                ✅ Valider ma commande
            </button>
            <a href="{{ route('public.shops.products', $product->shop) }}" 
               class="btn btn-outline-secondary flex-fill">
                ↩️ Retour à la boutique
            </a>
        </div>
    </form>
</div>
@endsection
