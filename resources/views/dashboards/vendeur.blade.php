@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">🏪 Tableau de bord Vendeur</h1>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Sécurise la variable pour éviter un 500 si oubli côté contrôleur --}}
    @php
        /** @var \Illuminate\Support\Collection $livreursDisponibles */
        $livreursDisponibles = $livreursDisponibles ?? collect();
    @endphp

    {{-- 📌 Infos boutique --}}
    <div class="alert alert-info">
        Vous gérez actuellement la boutique :
        <strong>{{ $shop->name }}</strong>
    </div>

    {{-- Ligne 1 : Accès rapides --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">🏬 Ma Boutique</h5>
                    <p class="card-text">Modifier les informations de ma boutique.</p>
                    <a href="{{ route('shop.index') }}" class="btn btn-primary">Accéder</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">📦 Produits</h5>
                    <p class="card-text">Ajouter, modifier ou supprimer mes produits.</p>
                    <a href="{{ route('products.index') }}" class="btn btn-success">Accéder</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">🛒 Commandes</h5>
                    <p class="card-text">Voir et gérer les commandes reçues.</p>
                    <a href="{{ route('orders.index') }}" class="btn btn-warning">Accéder</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Ligne 2 : Livreurs / Avis / Paiements --}}
    <div class="row g-3 mb-4">
        {{-- Livreurs disponibles --}}
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="fs-3 mb-2">🟢</div>
                    <h5 class="card-title fw-bold text-info">Livreurs disponibles</h5>

                    @if($livreursDisponibles->isEmpty())
                        <p class="text-muted small mb-2">Aucun livreur n’est en ligne pour le moment.</p>
                    @else
                        <p class="text-muted small mb-2">
                            {{ $livreursDisponibles->count() }} livreur(s) actuellement en ligne :
                        </p>
                        <ul class="list-unstyled small text-start mx-auto" style="max-height: 150px; overflow-y: auto; max-width: 320px;">
                            @foreach($livreursDisponibles as $livreur)
                                <li class="mb-1">
                                    <strong>{{ $livreur->name }}</strong>
                                    <span class="badge bg-success ms-1">🟢 En ligne</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <a href="{{ route('employe.orders.index') }}" class="btn btn-outline-info w-100 mt-2">
                        ➕ Assigner une commande
                    </a>
                </div>
            </div>
        </div>
                        <!-- ✅ Nouveau : Assigner les commandes aux livreurs -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 border-success">
                <div class="card-body text-center">
                    <div class="fs-3 mb-2">🚚</div>
                    <h5 class="card-title fw-bold text-success">Assignation Commandes</h5>
                    <p class="text-muted small">Assigner les commandes aux livreurs disponibles.</p>
                    <a href="{{ route('employe.orders.index') }}" class="btn btn-success w-100">
                        ➕ Assigner une commande
                    </a>
                </div>
            </div>
        </div>

        {{-- Avis --}}
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">⭐ Avis</h5>
                    <p class="card-text">Voir les avis des clients.</p>
                    <a href="{{ route('reviews.index') }}" class="btn btn-info">Accéder</a>
                </div>
            </div>
        </div>

        {{-- Paiements --}}
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">💵 Paiements</h5>
                    <p class="card-text">Consulter l’historique des paiements.</p>
                    <a href="{{ route('payments.index') }}" class="btn btn-success">Accéder</a>
                </div>
            </div>
        </div>
    </div>

    {{-- 📌 Section Produits --}}
    <div class="card mt-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="mb-0">📦 Mes Produits</h4>
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                ➕ Ajouter un produit
            </a>
        </div>

        <div class="card-body">
            @isset($products)
                @if($products->count())
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-3">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:70px">#</th>
                                    <th>Nom</th>
                                    <th style="width:160px">Prix</th>
                                    <th style="width:240px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ number_format($product->price, 0, ',', ' ') }} GNF</td>
                                        <td>
                                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning me-1">
                                                ✏ Modifier
                                            </a>
                                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Supprimer ce produit ?')">
                                                    🗑 Supprimer
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination si c'est un LengthAwarePaginator --}}
                    @if(method_exists($products, 'links'))
                        {{ $products->links() }}
                    @endif
                @else
                    <p class="text-muted mb-0">Aucun produit pour le moment.</p>
                @endif
            @else
                <p class="text-muted mb-0">La liste des produits n’a pas été fournie au template.</p>
            @endisset
        </div>
    </div>
</div>
@endsection
