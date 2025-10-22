@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">✏️ Modifier le produit</div>
    <div class="card-body">
        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Nom du produit</label>
                <input type="text" name="name" class="form-control" value="{{ old('name',$product->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Prix (GNF)</label>
                <input type="number" name="price" step="0.01" class="form-control" value="{{ old('price',$product->price) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control">{{ old('description',$product->description) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Image</label>
                @if($product->image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/'.$product->image) }}" width="100" height="100" style="object-fit:cover">
                    </div>
                @endif
                <input type="file" name="image" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</div>
@endsection
