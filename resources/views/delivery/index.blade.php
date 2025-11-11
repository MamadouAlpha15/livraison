@extends('layouts.app')

@push('styles')
<style>
.hero {
  background: linear-gradient(90deg, rgba(14,77,146,0.06), rgba(2,132,199,0.03));
  border-radius: .8rem;
  padding: 2.5rem;
}
.shop-card-img {
  height: 190px;
  object-fit: cover;
  border-radius: 14px 14px 0 0;
  transition: transform .35s;
}
.shop-card-img:hover { transform: scale(1.04); }
.shop-card { border-radius: 14px; overflow: hidden; box-shadow: 0 12px 30px rgba(12, 40, 80, 0.06); transition: transform .25s; }
.shop-card:hover { transform: translateY(-6px); }
.btn-premium { border-radius: 999px; padding: 0.55rem 1.3rem; font-weight: 700; }
.rating { color: #ffc107; font-weight:700; }
.meta { font-size: .9rem; color: #6c757d; }
@media (max-width: 576px) {
  .shop-card-img { height: 150px; }
}
</style>
@endpush

@section('content')
<div class="container py-4">
  <div class="hero mb-4 d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
    <div>
      <h1 class="h3 fw-bold mb-1">Entreprises de livraison</h1>
      <p class="mb-0 text-muted">Choisissez une entreprise de livraison fiable — comparez les commissions, discutez le tarif et assignez un livreur.</p>
    </div>
    <div class="d-flex gap-2">
      @auth
        <a href="{{ route('delivery.company.create') }}" class="btn btn-primary btn-premium">➕ S'inscrire comme entreprise</a>
      @else
        <a href="{{ route('register') }}" class="btn btn-outline-primary btn-premium">S'inscrire</a>
      @endauth
    </div>
  </div>

  <div class="row g-4">
    @forelse($companies as $company)
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="card shop-card h-100">
          @if($company->image)
            <img src="{{ asset('storage/'.$company->image) }}" alt="{{ $company->name }}" class="shop-card-img w-100">
          @else
            <div class="bg-light d-flex align-items-center justify-content-center shop-card-img">
              <span class="text-muted">Pas d’image</span>
            </div>
          @endif

          <div class="card-body d-flex flex-column">
            <h5 class="card-title mb-1">{{ $company->name }}</h5>
            <div class="meta mb-2">{{ Str::limit($company->description, 90) }}</div>

            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <small class="text-muted me-2"><i class="bi bi-telephone-fill"></i> {{ $company->phone ?? '—' }}</small>
                <small class="text-muted"><i class="bi bi-geo-alt-fill"></i> {{ Str::limit($company->address ?? '—', 30) }}</small>
              </div>
              <div class="text-end">
                <div class="rating">⭐ {{ number_format($company->reviews_avg_rating ?? 0,1) }}/5</div>
                <div class="small text-muted">{{ $company->drivers()->count() }} livreur(s)</div>
              </div>
            </div>

            <div class="mt-auto d-grid gap-2">
              <a href="{{ route('delivery.companies.show', $company) }}" class="btn btn-outline-primary btn-sm">Voir l’entreprise</a>
              @auth
                @if(Auth::user()->role === 'vendeur')
                  <a href="{{ route('company.chat.show', [$company]) }}" class="btn btn-primary btn-sm">Contacter</a>
                @endif
              @endauth
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <div class="alert alert-info">Aucune entreprise de livraison pour le moment.</div>
      </div>
    @endforelse
  </div>

  <div class="mt-4 d-flex justify-content-center">
    {{ $companies->links() }}
  </div>
</div>
@endsection
