@extends('layouts.app')

@section('title', 'Modifier la boutique')

@section('content')
<div class="container py-4">

    {{-- Fil d’Ariane --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('shop.index') }}">Mes boutiques</a></li>
            <li class="breadcrumb-item active" aria-current="page">Modifier : {{ $shop->name }}</li>
        </ol>
    </nav>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Modifier la boutique</h4>
            <span class="badge bg-light text-primary">{{ $shop->name }}</span>
        </div>

        <div class="card-body">
            {{-- Messages flash --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Veuillez corriger les erreurs suivantes :</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('shop.update', $shop->id) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')

                <div class="row g-4">
                    {{-- Colonne gauche --}}
                    <div class="col-lg-7">
                        {{-- Nom --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom de la boutique</label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $shop->name) }}"
                                maxlength="255"
                                required
                                placeholder="Ex. Shop Qualité"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Nom public affiché aux clients.</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">Email de contact</label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $shop->email) }}"
                                required
                                placeholder="exemple@domaine.com"
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Utilisé pour les notifications et la fiche boutique.</div>
                            @enderror
                        </div>

                        {{-- Téléphone (⚠️ aligné sur la colonne `phone`) --}}
                        <div class="mb-3">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input
                                type="tel"
                                name="phone"
                                id="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $shop->phone) }}"
                                placeholder="Ex. 622 00 00 00"
                                maxlength="20"
                            >
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Numéro affiché aux clients.</div>
                            @enderror
                        </div>

                        {{-- Adresse (⚠️ aligné sur la colonne `address`) --}}
                        <div class="mb-3">
                            <label for="address" class="form-label">Adresse</label>
                            <input
                                type="text"
                                name="address"
                                id="address"
                                class="form-control @error('address') is-invalid @enderror"
                                value="{{ old('address', $shop->address) }}"
                                placeholder="Ex. Cimenterie"
                                maxlength="255"
                            >
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Localisation de la boutique.</div>
                            @enderror
                        </div>
                        {{-- Taux de commission des livreurs --}}
                   <div class="mb-3">
    <label for="commission_rate" class="form-label">Commission (%)</label>
    <input
        type="number"
        step="0.01"
        min="0"
        max="100"
        name="commission_rate"
        id="commission_rate"
        class="form-control @error('commission_rate') is-invalid @enderror"
        value="{{ old('commission_rate', $shop->commission_rate_percent) }}"
        placeholder="Ex: 10 pour 10%"
    >
    <div class="form-text">
        Saisis un pourcentage entre 0 et 100. Exemple : <b>10</b> = 10%.
    </div>
    @error('commission_rate')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>


                        {{-- Description --}}
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea
                                name="description"
                                id="description"
                                rows="4"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Décrivez vos produits, horaires, services…"
                            >{{ old('description', $shop->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Quelques lignes pour mieux vous présenter.</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Colonne droite : image --}}
                    <div class="col-lg-5">
                        <div class="mb-3">
                            <label class="form-label d-block">Image actuelle</label>
                            @if($shop->image)
                                <img
                                    src="{{ asset('storage/' . $shop->image) }}"
                                    alt="Image actuelle de la boutique"
                                    class="rounded shadow-sm border"
                                    style="max-width: 100%; height: auto;"
                                >
                            @else
                                <div class="p-3 border rounded text-muted bg-light">Aucune image enregistrée</div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Choisir une nouvelle image</label>
                            <input
                                type="file"
                                name="image"
                                id="image"
                                class="form-control @error('image') is-invalid @enderror"
                                accept="image/*"
                            >
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">JPG, PNG, WEBP ou GIF — max 2 Mo.</div>
                            @enderror

                            {{-- Aperçu live de la nouvelle image --}}
                            <div class="mt-3" id="new-image-preview-container" style="display:none;">
                                <label class="form-label d-block text-muted">Nouvelle image sélectionnée :</label>
                                <img id="new-image-preview" class="rounded shadow-sm border" style="max-width:100%; height:auto;" alt="Aperçu nouvelle image">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Boutons --}}
                <div class="d-flex gap-2 justify-content-end mt-2">
                    <a href="{{ route('shop.index') }}" class="btn btn-outline-secondary">
                        ⬅ Retour
                    </a>
                    <button type="submit" class="btn btn-success">
                        💾 Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('image');
  const previewContainer = document.getElementById('new-image-preview-container');
  const preview = document.getElementById('new-image-preview');
  let lastURL = null;

  if (!input) return;

  input.addEventListener('change', (e) => {
    const file = e.target.files && e.target.files[0];
    if (!file) return;

    // Validation légère côté client (déjà refaite côté serveur)
    const okTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!okTypes.includes(file.type)) {
      alert("Format non supporté (JPG, PNG, WEBP ou GIF uniquement).");
      input.value = "";
      return;
    }

    const TWO_MB = 2 * 1024 * 1024;
    if (file.size > TWO_MB) {
      alert("Image trop lourde (max 2 Mo).");
      input.value = "";
      return;
    }

    if (lastURL) URL.revokeObjectURL(lastURL);
    lastURL = URL.createObjectURL(file);

    preview.src = lastURL;
    preview.alt = file.name;
    previewContainer.style.display = 'block';
  });
});
</script>
@endpush
