@extends('layouts.app')

@push('styles')
<style>
.create-card { max-width: 920px; margin: 0 auto; border-radius: 14px; box-shadow: 0 16px 40px rgba(11,45,90,0.06); overflow: hidden; }
.preview-img { width:100%; height:240px; object-fit:cover; border-radius:8px; background:#f5f7fa; display:block; }
.form-note { font-size:.9rem; color:#6c757d; }
</style>
@endpush

@section('content')

<div class="container py-5">
  <div class="card p-4">
    <h3 class="mb-3">Vous n'avez pas d'entreprise de livraison</h3>
    <p class="text-muted">Pour accéder au tableau de bord de l'entreprise vous devez d'abord créer votre entreprise. Après création, elle sera en attente d'approbation par le superadmin. Tant que l'entreprise n'est pas approuvée, vous ne pourrez pas ajouter de livreurs.</p>

    <div class="mt-4">
      <!-- Bouton vers le formulaire de création -->
      <a href="{{ route('delivery.company.create') }}" class="btn btn-primary">Créer une entreprise de livraison</a>
    </div>
<div class="container py-5">
  <div class="card create-card">
    <div class="row g-0">
      <div class="col-md-5 p-4 bg-white">
        <h3 class="mb-2">Créer votre entreprise de livraison</h3>
        <p class="form-note mb-4">Après soumission, un administrateur doit approuver votre entreprise. Vous pourrez ensuite ajouter vos livreurs.</p>

        <form action="{{ route('delivery.company.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
          @csrf

          <div class="mb-3">
            <label class="form-label">Nom de l'entreprise *</label>
            <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="row g-2">
            <div class="col-6 mb-3">
              <label class="form-label">Téléphone</label>
              <input type="text" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror">
              @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-6 mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
              @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Adresse</label>
            <input type="text" name="address" value="{{ old('address') }}" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">Commission (%) pour chaque livraison *</label>
            <input type="number" min="0" max="100" step="0.01" name="commission_percent" value="{{ old('commission_percent', 0) }}" class="form-control @error('commission_percent') is-invalid @enderror" required>
            @error('commission_percent') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Logo / Image</label>
            <input type="file" name="image" id="imageInput" accept="image/*" class="form-control">
            <div class="form-text">JPG, PNG ou WEBP — max 4MB.</div>
            @error('image') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>

          <div class="d-grid">
            <button type="submit" class="btn btn-success btn-premium">Créer mon entreprise (en attente d'approbation)</button>
          </div>
        </form>
      </div>

      <div class="col-md-7 p-4 bg-light d-flex flex-column align-items-center justify-content-center">
        <div style="width:90%">
          <img id="preview" src="{{ asset('images/placeholder-company.png') }}" alt="Aperçu" class="preview-img mb-3">
          <h5 class="mb-1">Aperçu de votre page entreprise</h5>
          <p class="text-muted small">L'image sera affichée en haut de votre page publique. Choisissez une photo représentative.</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('imageInput')?.addEventListener('change', function(e){
  const file = e.target.files[0];
  if (!file) return;
  const url = URL.createObjectURL(file);
  document.getElementById('preview').src = url;
});
</script>
@endpush
