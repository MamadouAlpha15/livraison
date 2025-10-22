@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>📦 Mes Produits</h2>
    <a href="{{ route('products.create') }}" class="btn btn-success">➕ Ajouter un produit</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-striped table-bordered">
    <thead class="table-primary">
        <tr>
            <th>#</th>
            <th>Image</th>
            <th>Nom</th>
            <th>Prix</th>
            <th>Statut</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $product)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
                @if($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" width="60" height="60" style="object-fit:cover">
                @else
                    <span class="text-muted">Aucune</span>
                @endif
            </td>
            <td>{{ $product->name }}</td>
            <td>{{ number_format($product->price, 0, ',', ' ') }} GNF</td>
            <td>
                @if($product->is_active)
                    <span class="badge bg-success">✅ Actif</span>
                @else
                    <span class="badge bg-danger">❌ Inactif</span>
                @endif
            </td>

         <td>
            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">✏️ Modifier</a>
             <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce produit ?')">
                    🗑️ Supprimer
                </button>
            </form>
        </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">Aucun produit trouvé.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $products->links() }}
@endsection
