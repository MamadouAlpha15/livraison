@extends('layouts.app')

@push('styles')
<style>
.product-card {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    transition: transform .3s, box-shadow .3s;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(0,0,0,0.18);
}
.product-card img {
    height: 180px;
    object-fit: cover;
}
.btn-premium {
    border-radius: 50px;
    padding: 0.5rem 1.5rem;
    font-weight: 600;
}

</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">{{ $shop->name }} 
            <span class="badge bg-secondary text-capitalize">{{ $shop->type ?? 'boutique' }}</span>
        </h2>
        <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-premium">⬅ Retour aux boutiques</a>
    </div>

    <div class="row g-4">
        @forelse($products as $product)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card product-card h-100">
                    @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
                    @endif
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <p class="text-muted small mb-2">{{ number_format($product->price,0,',',' ') }} GNF</p>
                        <p class="card-text flex-grow-1">{{ $product->description }}</p>

                        <div class="mt-auto">
                            @auth
                                @if(Auth::user()->role === 'client')
                                    <a href="{{ route('client.orders.createFromProduct', $product) }}" class="btn btn-success btn-sm btn-premium w-100">
                                        🛒 Commander
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-sm btn-premium w-100" onclick="alert('Veuillez vous inscrire pour commander')">
                                    🛒 Commander
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">Aucun produit pour le moment.</div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>
@endsection
