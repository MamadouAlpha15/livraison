@extends('layouts.app')

@section('content')
<div class="container py-5">
    {{-- En-tête premium --}}
    <div class="rounded-3 p-4 mb-5 text-white" style="background: linear-gradient(135deg,#0d6efd 0%, #6610f2 100%); box-shadow: 0 8px 30px rgba(13,110,253,.15);">
        <div class="d-flex align-items-center justify-content-between flex-column flex-md-row">
            <div>
                <h1 class="h3 fw-bold mb-1">👑 Tableau de bord Admin</h1>
                <p class="mb-0 opacity-90">Bienvenue <strong>{{ Auth::user()->name }}</strong> — <span class="badge bg-danger">ADMIN</span></p>
                <small class="d-block opacity-75 mt-2">Vue claire et rapide pour gérer votre boutique — tous les boutons gardés, mieux organisés.</small>
            </div>

            <div class="mt-3 mt-md-0 d-flex gap-2">
                <a href="{{ route('support.index') }}" class="btn btn-outline-light btn-sm shadow-sm">🎧 Support</a>
                <a href="{{ route('boutique.reports.index') }}" class="btn btn-light btn-sm shadow-sm">📑 Rapports</a>
            </div>
        </div>
    </div>

    {{-- Quick stats / alert --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            @if(!$shop)
                <div class="alert alert-info shadow-sm d-flex align-items-center justify-content-between">
                    <div>
                        <strong>Vous n’avez pas encore de boutique.</strong>
                        <div class="small text-muted">Créez-la pour commencer à vendre.</div>
                    </div>
                    <a href="{{ route('boutique.shops.create') }}" class="btn btn-primary">➕ Créer ma boutique</a>
                </div>
            @else
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted">Taux de commission livreur</div>
                            <div class="h5 fw-bold">{{ $shop->commission_rate * 100 }} %</div>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('boutique.commissions.index') }}" class="btn btn-outline-primary btn-sm">Gérer</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="d-none d-md-block">
            <a href="{{ route('products.create') }}" class="btn btn-success shadow-sm">+ Produit</a>
        </div>
    </div>

    {{-- Grid des cartes fonctionnelles --}}
    <div class="row g-4">
        @php
            $cards = [
                ['emoji'=>'🏬','title'=>'Ma Boutique','desc'=>'Créer / modifier les informations.','route'=>route('boutique.shops.index'),'class'=>'primary'],
                ['emoji'=>'📦','title'=>'Produits','desc'=>'Ajouter, modifier ou supprimer.','route'=>route('products.index'),'class'=>'success'],
                ['emoji'=>'🛒','title'=>'Commandes','desc'=>'Suivre et gérer les commandes.','route'=>route('orders.index'),'class'=>'warning'],
                ['emoji'=>'💸','title'=>'Commissions livreurs','desc'=>'Voir et payer les commissions.','route'=>route('boutique.commissions.index'),'class'=>'outline-success'],
                ['emoji'=>'⭐','title'=>'Avis Clients','desc'=>'Consulter les retours clients.','route'=>route('reviews.index'),'class'=>'info'],
                ['emoji'=>'💵','title'=>'Paiements','desc'=>'Suivre vos paiements et revenus.','route'=>route('payments.index'),'class'=>'outline-success'],
                ['emoji'=>'⚙️','title'=>'Gestion des rôles','desc'=>'Créer vendeurs et livreurs.','route'=>route('boutique.employees.index'),'class'=>'danger'],
                ['emoji'=>'📊','title'=>'Statistiques','desc'=>'Suivre les ventes et performances.','route'=>route('boutique.stats.index'),'class'=>'warning'],
                ['emoji'=>'📑','title'=>'Rapports','desc'=>'Consulter les rapports mensuels.','route'=>route('boutique.reports.index'),'class'=>'info'],
            ];
        @endphp

        @foreach($cards as $c)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 hover-lift">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="display-6">{!! $c['emoji'] !!}</div>
                            <div class="text-end">
                                <small class="text-muted">{{ $c['desc'] }}</small>
                            </div>
                        </div>

                        <h5 class="mt-3 mb-3 fw-bold">{{ $c['title'] }}</h5>

                        <div class="mt-auto">
                            @php
                                $btnClass = 'btn btn-primary w-100';
                                if(str_contains($c['class'],'success')) $btnClass = 'btn btn-success w-100';
                                if(str_contains($c['class'],'warning')) $btnClass = 'btn btn-warning w-100';
                                if(str_contains($c['class'],'danger')) $btnClass = 'btn btn-danger w-100';
                                if(str_contains($c['class'],'info')) $btnClass = 'btn btn-info w-100';
                                if(str_contains($c['class'],'outline')) $btnClass = 'btn btn-outline-success w-100';
                            @endphp
                            <a href="{{ $c['route'] }}" class="{{ $btnClass }}">Ouvrir</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Carte Livreurs disponibles --}}
        @if($livreursDisponibles->isNotEmpty())
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 hover-lift">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="fs-4">🟢 <span class="fw-bold">Livreurs disponibles</span></div>
                            <small class="text-muted">En ligne</small>
                        </div>
                        <ul class="list-unstyled small mb-0" style="max-height:160px; overflow:auto;">
                            @foreach($livreursDisponibles as $livreur)
                                <li class="d-flex justify-content-between align-items-center py-1">
                                    <div>
                                        <strong>{{ $livreur->name }}</strong>
                                        <div class="small text-muted">{{ $livreur->phone ?? '' }}</div>
                                    </div>
                                    <span class="badge bg-success">En ligne</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @else
            {{-- Carte Contact Entreprise de Livraison --}}
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 hover-lift">
                    <div class="card-body d-flex flex-column">
                        <div class="display-6 text-center mb-2">🚚</div>
                        <h5 class="fw-bold text-center">Pas de livreur disponible</h5>
                        <p class="small text-muted text-center mb-3">Contactez une entreprise de livraison pour déléguer vos commandes.</p>

                        <select id="selectDeliveryCompany" class="form-select mb-3">
                            <option value="">— Sélectionnez une entreprise —</option>
                            @foreach($deliveryCompanies as $company)
                                @if($company->phone)
                                    <option value="{{ $company->phone }}" data-name="{{ $company->name }}">{{ $company->name }} — {{ number_format($company->commission_percent ?? 0, 2) }}%</option>
                                @endif
                            @endforeach
                        </select>

                        <button id="btnContactCompany" class="btn btn-primary w-100" disabled>Contacter</button>
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- Barre d'actions rapide en bas --}}
    <div class="mt-4 d-flex gap-2 flex-column flex-md-row">
        <a href="{{ route('support.index') }}" class="btn btn-outline-primary flex-fill">🎧 Support client</a>
        <a href="{{ route('boutique.shops.index') }}" class="btn btn-outline-dark flex-fill">🏬 Mes boutiques</a>
        <a href="{{ route('products.index') }}" class="btn btn-success flex-fill d-md-none">+ Produit</a>
    </div>

</div>

@section('styles')
<style>
.hover-lift{ transition: transform .15s ease, box-shadow .15s ease; border-radius: .8rem; }
.hover-lift:hover{ transform: translateY(-6px); box-shadow: 0 12px 30px rgba(0,0,0,.08); }
.card .display-6{ font-size: 1.6rem; }
</style>
@endsection

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('selectDeliveryCompany');
    const btnContact = document.getElementById('btnContactCompany');

    if (!select) return;

    select.addEventListener('change', () => {
        btnContact.disabled = !select.value;
    });

    btnContact.addEventListener('click', () => {
        const phone = select.value;
        if(!phone) return;
        // ouvre WhatsApp directement vers le numéro sélectionné
        const url = `https://wa.me/${phone.replace(/\D/g,'')}`;
        window.open(url, '_blank');
    });
});
</script>
@endpush
