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
        </div>
    </div>

    {{-- Grid des cartes fonctionnelles (toujours garder les boutons) --}}
    <div class="row g-4">
        {{-- Card component (réutilisable) --}}
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
                            {{-- Bouton style selon type (conserve les routes originales) --}}
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

       {{-- Livreurs disponibles (amélioré) --}}
<div class="col-12 col-sm-6 col-lg-4">
    <div class="card h-100 shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fs-4">🟢 <span class="fw-bold">Livreurs disponibles</span></div>
                <small class="text-muted">En ligne</small>
            </div>

            @if($livreursDisponibles->isEmpty())
                {{-- Alerte + menu déroulant vers entreprises de livraison --}}
                <div class="alert alert-warning small">
                    <strong>Vous n’avez pas de livreur</strong><br>
                    <span class="text-muted">Contactez une entreprise de livraison pour déléguer la livraison.</span>
                </div>

                <div class="mb-3">
                    <label for="selectDeliveryCompany" class="form-label small fw-semibold">Choisir une entreprise de livraison</label>
                    <select id="selectDeliveryCompany" class="form-select">
                        <option value="">— Sélectionnez une entreprise —</option>
                        @foreach($deliveryCompanies as $company)
                            <option value="{{ $company->id }}" data-name="{{ $company->name }}">
                                {{ $company->name }} — {{ number_format($company->commission_percent ?? 0, 2) }}% 
                                @if($company->phone) · {{ $company->phone }} @endif
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text small text-muted">Après sélection, vous serez redirigé vers le chat pour négocier le tarif.</div>
                </div>

                <div class="d-flex gap-2">
                    <button id="btnContactCompany" class="btn btn-primary" disabled>Contacter</button>
                    <button id="btnResetCompany" class="btn btn-outline-secondary" disabled>Annuler</button>
                </div>

            @else
                <ul class="list-unstyled small mb-3" style="max-height:160px; overflow:auto;">
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
            @endif
        </div>
    </div>
</div>

       

    </div>

    {{-- Barre d'actions rapide en bas --}}
    <div class="mt-4 d-flex gap-2 flex-column flex-md-row">
        <a href="{{ route('support.index') }}" class="btn btn-outline-primary flex-fill">🎧 Support client</a>
        <a href="{{ route('boutique.shops.index') }}" class="btn btn-outline-dark flex-fill">🏬 Mes boutiques</a>
        <a href="{{ route('products.index') }}" class="btn btn-success flex-fill d-md-none">+ Produit</a>
    </div>

</div>

{{-- Styles additionnels pour l'effet premium (peut être extrait dans un fichier CSS) --}}
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
    const btnReset = document.getElementById('btnResetCompany');

    if (!select) return;

    // enable / disable buttons selon selection
    select.addEventListener('change', () => {
        const val = select.value;
        btnContact.disabled = !val;
        btnReset.disabled = !val;
    });

    // action Contacter -> redirection vers le chat de l'entreprise
    btnContact.addEventListener('click', () => {
        const id = select.value;
        const name = select.selectedOptions[0]?.dataset?.name || '';
        if (!id) return;

        // message initial (URL-encode)
        const initMessage = encodeURIComponent('Écrivez-nous pour en savoir plus');

        // Construire l'URL de chat :
        // route attend /company/{company}/chat/{shop?}
        // Ici on redirige vers /company/{id}/chat?init=...
        const chatUrl = `${window.location.origin}/company/${id}/chat?init=${initMessage}`;

        // confirmation rapide (optionnelle)
        if (confirm(`Contacter "${name}" maintenant ?`)) {
            window.location.href = chatUrl;
        }
    });

    // reset selection
    btnReset.addEventListener('click', () => {
        select.value = '';
        btnContact.disabled = true;
        btnReset.disabled = true;
    });
});
</script>
@endpush
