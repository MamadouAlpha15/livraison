@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Entreprises en attente d'approbation</h3>
  </div>

  <div class="row g-3">
    @forelse($pending as $company)
      <div class="col-12">
        <div class="card p-3 d-flex align-items-center">
          <div class="row w-100 g-2">
            <div class="col-auto">
              <img src="{{ $company->image ? asset('storage/'.$company->image) : asset('images/placeholder-company.png') }}" alt="" style="width:80px; height:80px; object-fit:cover; border-radius:8px;">
            </div>
            <div class="col">
              <h5 class="mb-1">{{ $company->name }}</h5>
              <div class="small text-muted">{{ Str::limit($company->description, 140) }}</div>
            </div>
            <div class="col-auto d-flex gap-2 align-items-center">
              <form action="{{ route('admin.delivery-company.approve', $company) }}" method="POST">
                @csrf @method('PUT')
                <button class="btn btn-success">✅ Approuver</button>
              </form>
              <form action="{{ route('admin.delivery-company.destroy', $company) }}" method="POST" onsubmit="return confirm('Supprimer cette entreprise ?');">
                @csrf @method('DELETE')
                <button class="btn btn-danger">Supprimer</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <div class="alert alert-info">Aucune entreprise en attente.</div>
      </div>
    @endforelse
  </div>

  <div class="mt-3">
    {{ $pending->links() }}
  </div>
</div>
@endsection
