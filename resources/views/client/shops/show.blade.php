@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">🏪 {{ $shop->name }}</h2>
    <p>{{ $shop->description ?? 'Aucune description disponible.' }}</p>

    <div class="row">
        @forelse($products as $product)
            <div class="col-md-4 mb-3">
                <div class="card h-100 shadow-sm">
                    @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" class="card-img-top" alt="{{ $product->name }}" style="height:200px; object-fit:cover;">
                    @endif
                    <div class="card-body text-center">
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <p class="card-text text-muted">
                            {{ number_format($product->price, 0, ',', ' ') }} GNF
                        </p>
                        <p>{{ Str::limit($product->description, 50) }}</p>

                        <a href="{{ route('client.orders.createFromProduct', $product->id) }}" 
   class="btn btn-success btn-sm">
    🛒 Commander
</a>

                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">Aucun produit disponible pour cette boutique.</p>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $products->links() }}
    </div>
</div>
@endsection
