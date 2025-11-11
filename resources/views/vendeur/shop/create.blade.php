@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">🏪 Créer ma boutique</div>
    @if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('info'))
  <div class="alert alert-info">{{ session('info') }}</div>
@endif

@if ($errors->has('global'))
  <div class="alert alert-danger">{{ $errors->first('global') }}</div>
@endif

    <div class="card-body">
        <form action="{{ route('shop.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Nom -->
            <div class="mb-3">
                <label class="form-label">Nom de la boutique</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <!-- Type -->
            <div class="mb-3">
                <label class="form-label">Type (restaurant, pharmacie...)</label>
                <input type="text" name="type" class="form-control">
            </div>

            <!-- Adresse -->
            <div class="mb-3">
                <label class="form-label">Adresse</label>
                <input type="text" name="address" class="form-control">
            </div>
            <!--email -->
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="text" name="email" class="form-control">
            </div>

            <!-- Téléphone -->
            <div class="mb-3">
                <label class="form-label">Téléphone</label>
                <input type="text" name="phone" class="form-control">
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>

            <!-- Image de la boutique -->
            <div class="mb-3">
                <label class="form-label">Image / Logo de la boutique</label>
                <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event)">
            </div>

            <!-- Aperçu de l’image -->
            <div class="mb-3 text-center">
                <img id="preview" src="" alt="Aperçu" style="max-width:200px; display:none; border-radius:10px; border:1px solid #ddd;">
            </div>
            <!-- Taux de commission des livreurs -->
             <div class="mb-3">
  <label for="commission_rate" class="form-label">Taux de commission des livreurs (%)</label>
  <input type="number" step="0.01" min="0" max="100"
         name="commission_rate"
         id="commission_rate"
         value="{{ old('commission_rate', $shop->commission_rate ?? 20) }}"
         class="form-control"
         placeholder="Ex : 15">
  <small class="text-muted">Ex : 15 = 15 % de commission sur chaque commande livrée.</small>
</div>


            <!-- Bouton -->
            <button type="submit" class="btn btn-primary">Créer</button>
        </form>
    </div>
</div>

<!-- Script pour aperçu image -->
<script>
    function previewImage(event) {
        const preview = document.getElementById('preview');
        preview.src = URL.createObjectURL(event.target.files[0]);
        preview.style.display = 'block';
    }
</script>
@endsection
