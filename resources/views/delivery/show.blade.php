@extends('layouts.app')

@push('styles')
<style>
.header-img { width:100%; height:280px; object-fit:cover; border-radius:12px; }
.company-card { border-radius:12px; box-shadow: 0 10px 30px rgba(2, 54, 120, .06); }
.contact-btn { border-radius: 999px; }
@media (max-width:768px){ .header-img { height:180px; } }
</style>
@endpush

@section('content')
<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card company-card p-3">
        @if($company->image)
          <img src="{{ asset('storage/'.$company->image) }}" alt="{{ $company->name }}" class="header-img mb-3">
        @endif

        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h2 class="mb-1">{{ $company->name }}</h2>
            <div class="small text-muted mb-2">{{ $company->address ?? 'Adresse non renseignée' }}</div>
            <div class="mb-3">{{ $company->description }}</div>
            <div class="d-flex gap-3 mb-3">
              <div class="badge bg-light">Commission: <strong>{{ number_format($company->commission_percent,2) }}%</strong></div>
              <div class="badge bg-light">Livreurs: <strong>{{ $company->drivers()->count() }}</strong></div>
              <div class="badge bg-light">Contact: <strong>{{ $company->phone ?? '—' }}</strong></div>
            </div>
          </div>

          <div class="text-end">
            <div class="rating mb-2">⭐ {{ number_format($company->reviews_avg_rating ?? 0,1) }}/5</div>
            @auth
              @if(Auth::user()->role === 'vendeur')
                <a href="{{ route('company.chat.show', [$company]) }}" class="btn btn-primary contact-btn">🗨️ Discuter</a>
              @endif
            @else
              <a href="{{ route('login') }}" class="btn btn-outline-primary contact-btn">Se connecter pour discuter</a>
            @endauth
          </div>
        </div>

        <hr>
        <h5>Nos livreurs</h5>
        <div class="row g-3">
          @forelse($company->drivers as $driver)
            <div class="col-6 col-md-4">
              <div class="card p-2">
                <div class="d-flex gap-2 align-items-center">
                  <img src="{{ $driver->photo ? asset('storage/'.$driver->photo) : asset('images/avatar-placeholder.png') }}" alt="" style="width:56px; height:56px; object-fit:cover; border-radius:8px;">
                  <div>
                    <div class="fw-semibold">{{ $driver->name }}</div>
                    <div class="small text-muted">{{ $driver->phone ?? '—' }}</div>
                  </div>
                </div>
              </div>
            </div>
          @empty
            <div class="col-12">
              <div class="text-muted">Aucun livreur visible publiquement.</div>
            </div>
          @endforelse
        </div>
      </div>
    </div>

    <aside class="col-lg-4">
      <div class="card p-3">
        <h6>Contact rapide</h6>
        <p class="mb-1"><i class="bi bi-telephone"></i> {{ $company->phone ?? '—' }}</p>
        <p class="mb-1"><i class="bi bi-envelope"></i> {{ $company->email ?? '—' }}</p>

        <div class="mt-3">
          @auth
            @if(Auth::user()->role === 'vendeur')
              <a href="{{ route('company.chat.show', [$company]) }}" class="btn btn-primary w-100">Contacter & négocier</a>
            @endif
          @else
            <a href="{{ route('login') }}" class="btn btn-outline-primary w-100">Se connecter pour contacter</a>
          @endauth
        </div>
      </div>

      <div class="card p-3 mt-3">
        <h6>Informations</h6>
        <p class="small text-muted mb-0">Approuvée: <strong>{{ $company->approved ? 'Oui' : 'Non' }}</strong></p>
        <p class="small text-muted mb-0">Date création: <strong>{{ $company->created_at->format('d/m/Y') }}</strong></p>
      </div>
    </aside>
  </div>
</div>
@endsection
