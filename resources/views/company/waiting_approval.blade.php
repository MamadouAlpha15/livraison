@extends('layouts.app')

@section('content')
<div class="container py-5">
  <div class="card mx-auto shadow-sm" style="max-width:760px;">
    <div class="card-body text-center p-5">
      <h3 class="mb-3">⏳ En attente d’approbation</h3>
      <p class="text-muted mb-3">Votre entreprise <strong>{{ $company->name ?? '' }}</strong> a bien été créée. Elle doit être approuvée par un administrateur avant que vous puissiez ajouter des livreurs et recevoir des demandes.</p>
      <p class="small text-muted">Nous vous enverrons une notification par e-mail dès que l’approbation sera effectuée.</p>

      <div class="mt-3">
        <a href="{{ route('welcome') }}" class="btn btn-outline-primary">Retour à l’accueil</a>
      </div>
    </div>
  </div>
</div>
@endsection
