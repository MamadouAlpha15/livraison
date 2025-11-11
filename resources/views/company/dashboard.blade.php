@extends('layouts.app')

@push('styles')
<style>
.header {
  background: linear-gradient(90deg, rgba(2,132,199,0.06), rgba(14,77,146,0.03));
  padding: 1.2rem;
  border-radius: .6rem;
}
.driver-photo { width:64px; height:64px; object-fit:cover; border-radius:10px; }
.card-soft { box-shadow: 0 8px 24px rgba(4,40,90,.06); border-radius:10px; }
</style>
@endpush

@section('content')
<div class="container py-4">

    {{-- Messages flash --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(!$company)
        {{-- Pas d'entreprise --}}
        <div class="card p-4 text-center">
            <h3>Vous n'avez pas d'entreprise</h3>
            <p class="text-muted">Pour accéder au dashboard, créez votre entreprise de livraison.</p>
            <a href="{{ route('delivery.company.create') }}" class="btn btn-primary">Créer une entreprise</a>
        </div>
    @elseif(!$company->approved)
        {{-- Entreprise en attente d'approbation --}}
        <div class="alert alert-warning text-center">
            Votre entreprise <strong>{{ $company->name }}</strong> est en attente d'approbation par un administrateur.
            Vous ne pouvez pas ajouter de livreurs pour le moment.
        </div>
        <div class="card p-3 text-center">
            <img src="{{ $company->image ? asset('storage/'.$company->image) : asset('images/placeholder-company.png') }}"
                 alt="{{ $company->name }}" class="mb-3" style="width:150px; height:150px; object-fit:cover; border-radius:10px;">
            <h4>{{ $company->name }}</h4>
            <p class="text-muted">Commission : {{ number_format($company->commission_percent,2) }}%</p>
        </div>
    @else
        {{-- Entreprise approuvée — dashboard complet --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-0">{{ $company->name }} — Dashboard</h3>
                <div class="text-muted small">Gérez vos livreurs et votre compte entreprise</div>
            </div>

            <div>
                <a href="#" class="btn btn-outline-secondary btn-sm" onclick="location.reload()">⟳ Rafraîchir</a>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addDriverModal">
                    ➕ Ajouter un livreur
                </button>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-soft p-3">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $company->image ? asset('storage/'.$company->image) : asset('images/placeholder-company.png') }}" 
                             alt="" style="width:80px; height:80px; object-fit:cover; border-radius:8px;">
                        <div>
                            <div class="fw-semibold">{{ $company->name }}</div>
                            <div class="small text-muted">Commission: <strong>{{ number_format($company->commission_percent,2) }}%</strong></div>
                            <div class="small text-muted">Livreurs: <strong>{{ $drivers->total() }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card p-3">
                    <h6 class="mb-3">Statistiques rapides</h6>
                    <div class="row">
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Commandes</div>
                            <div class="fw-semibold">—</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Revenu (estim.)</div>
                            <div class="fw-semibold">—</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Livreurs actifs</div>
                            <div class="fw-semibold">{{ $drivers->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Liste des livreurs --}}
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Mes livreurs</h5>
                <div class="small text-muted">{{ $drivers->total() }} résultats</div>
            </div>

            <div class="row g-3">
                @forelse($drivers as $driver)
                    <div class="col-12 col-md-6">
                        <div class="d-flex align-items-center gap-3 p-3 border rounded">
                            <img src="{{ $driver->photo ? asset('storage/'.$driver->photo) : asset('images/avatar-placeholder.png') }}" 
                                 alt="" class="driver-photo">
                            <div class="flex-fill">
                                <div class="fw-semibold">{{ $driver->name }}</div>
                                <div class="small text-muted">{{ $driver->phone ?? '—' }} • {{ $driver->vehicle ?? 'Véhicule non défini' }}</div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('company.drivers.update', $driver) }}" class="btn btn-outline-secondary btn-sm">✎ Modifier</a>
                                <form action="{{ route('company.drivers.destroy', $driver) }}" method="POST" onsubmit="return confirm('Supprimer ce livreur ?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center text-muted py-4">Aucun livreur ajouté — commencez par en ajouter un.</div>
                    </div>
                @endforelse
            </div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $drivers->links() }}
            </div>
        </div>
    @endif
</div>

{{-- Modal Ajouter un livreur --}}
@if($company && $company->approved)
<div class="modal fade" id="addDriverModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('company.drivers.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Ajouter un livreur</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Nom *</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Téléphone</label>
            <input type="text" name="phone" class="form-control">
          </div>
          <div class="mb-2">
            <label class="form-label">Photo</label>
            <input type="file" name="photo" accept="image/*" class="form-control">
          </div>
          <div class="mb-2">
            <label class="form-label">Véhicule (optionnel)</label>
            <input type="text" name="vehicle" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">Annuler</button>
          <button class="btn btn-success" type="submit">Ajouter le livreur</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

@endsection
