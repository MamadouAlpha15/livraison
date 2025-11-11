@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">➕ Ajouter un produit</div>
    <div class="card-body">
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Nom du produit -->
            <div class="mb-3">
                <label class="form-label">Nom du produit</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <!-- Prix -->
            <div class="mb-3">
                <label class="form-label">Prix (GNF)</label>
                <input type="number" name="price" step="0.01" class="form-control" required>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>

            <!-- Image -->
            <div class="mb-3">
                <label class="form-label">Image</label>
                <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event)">
            </div>

            <!-- Aperçu de l'image -->
            <div class="mb-3 text-center">
                <img id="preview" src="" alt="Aperçu de l'image" style="max-width:200px; display:none; border-radius:10px; border:1px solid #ddd;">
            </div>

            <!-- Bouton -->
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>
</div>

<!-- Script pour aperçu image -->
<script>
    function previewImage(event) {
        const preview = document.getElementById('preview');
        const file = event.target.files[0];

        if(file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    }
</script>
@endsection
