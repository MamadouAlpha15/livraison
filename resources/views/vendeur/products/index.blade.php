@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 fw-bold mb-0">📦 Mes Produits</h2>
            <div class="small text-muted">Gérez vos produits rapidement — aperçu, modification et actions en un clic.</div>
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-success btn-lg shadow-sm">➕ Ajouter un produit</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
    @endif

    {{-- Vue premium: cards responsives --}}
    <div class="row g-3 mb-4">
        @forelse($products as $product)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card h-100 shadow-sm product-card">
                    <div class="position-relative">
                        @if($product->image)
                            <img src="{{ asset('storage/'.$product->image) }}" class="card-img-top" alt="{{ $product->name }}" style="height:180px; object-fit:cover;">
                        @else
                            <div class="placeholder-image d-flex align-items-center justify-content-center" style="height:180px;">
                                <div class="text-muted">Aucune image</div>
                            </div>
                        @endif

                        <div class="badge-container position-absolute top-0 end-0 m-2">
                            @if($product->is_active)
                                <span class="badge bg-success">✅ Actif</span>
                            @else
                                <span class="badge bg-danger">❌ Inactif</span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-1">{{ Str::limit($product->name, 40) }}</h5>
                        <p class="small text-muted mb-2">{{ Str::limit($product->description ?? '', 60) }}</p>

                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">{{ number_format($product->price, 0, ',', ' ') }} GNF</div>
                                <div class="small text-muted">Stock: {{ $product->stock ?? 0 }}</div>
                            </div>

                            <div class="d-flex flex-column align-items-end gap-2">
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-warning">✏️ Modifier</a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirmDelete();">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">🗑️ Supprimer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm p-4 text-center">
                    <h5 class="mb-2">Aucun produit trouvé</h5>
                    <p class="text-muted">Ajoutez votre premier produit pour commencer à vendre.</p>
                    <a href="{{ route('products.create') }}" class="btn btn-primary">➕ Ajouter un produit</a>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-3">
        {{ $products->links() }}
    </div>
</div>

@section('styles')
<style>
/* Cards premium */
.product-card{ border-radius:1rem; overflow:hidden; }
.product-card .card-body{ padding:1rem; }
.placeholder-image{ background: linear-gradient(90deg, #f8f9fa, #f1f3f5); border-bottom:1px solid rgba(0,0,0,0.03); }
.badge-container .badge{ font-size:0.85rem; padding:.45rem .6rem; border-radius:.6rem; }
.card-img-top{ transition: transform .2s ease; }
.product-card:hover .card-img-top{ transform: scale(1.03); }
</style>
@endsection

@section('scripts')
<script>
function confirmDelete(){
    return confirm('Supprimer ce produit ? Cette action est irréversible.');
}
</script>
@endsection

@endsection
