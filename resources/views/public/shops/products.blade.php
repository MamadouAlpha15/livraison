@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3">
        <h2 class="h4 mb-2 mb-md-0">
            🏪 {{ $shop->name }}
            <span class="badge bg-secondary text-capitalize">{{ $shop->type ?? 'boutique' }}</span>
        </h2>

        {{-- Cas pharmacie : bouton commande/ordonnance global --}}
        @if(Str::lower($shop->type) === 'pharmacie')
            <a href="{{ route('client.orders.create', ['shop_id' => $shop->id]) }}" class="btn btn-primary">
                💊 Commander (joindre mon ordonnance)
            </a>
        @endif
    </div>

    <div class="row g-3">
        @forelse($products as $product)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" class="card-img-top" alt="{{ $product->name }}" style="object-fit:cover;height:180px;">
                    @endif
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-1">{{ $product->name }}</h5>
                        <p class="text-muted small mb-2">{{ number_format($product->price,0,',',' ') }} GNF</p>
                        <p class="card-text flex-grow-1">{{ Str::limit($product->description, 90) }}</p>

                        {{-- Commander produit : uniquement si ce n'est pas une pharmacie --}}
                        @if(Str::lower($shop->type) !== 'pharmacie')
                            @auth
                                @if(Auth::user()->role === 'client')
                                    <a href="{{ route('client.orders.createFromProduct', $product) }}" class="btn btn-success btn-sm mt-auto">
                                        🛒 Commander
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm mt-auto">
                                        Se connecter pour commander
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm mt-auto">
                                    Se connecter pour commander
                                </a>
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">Aucun produit pour le moment.</div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $products->links() }}
    </div>
</div>
@endsection
