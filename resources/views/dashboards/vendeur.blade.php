@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">🏪 Tableau de bord Vendeur</h1>

    <!-- 📌 Infos boutique -->
    <div class="alert alert-info">
        Vous gérez actuellement la boutique : <strong>{{ $shop->name }}</strong>
    </div>

    <div class="row mb-4">
        <!-- Ma boutique -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">🏬 Ma Boutique</h5>
                    <p class="card-text">Modifier les infos de ma boutique</p>
                    <a href="{{ route('shop.index') }}" class="btn btn-primary">Accéder</a>
                </div>
            </div>
        </div>

        <!-- Produits -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">📦 Produits</h5>
                    <p class="card-text">Ajouter, modifier ou supprimer mes produits</p>
                    <a href="{{ route('products.index') }}" class="btn btn-success">Accéder</a>
                </div>
            </div>
        </div>

        <!-- Commandes -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">🛒 Commandes</h5>
                    <p class="card-text">Voir et gérer les commandes reçues</p>
                    <a href="{{ route('orders.index') }}" class="btn btn-warning">Accéder</a>
                </div>
            </div>
        </div>

        <!-- Avis -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">⭐ Avis</h5>
                    <p class="card-text">Voir les avis des clients</p>
                    <a href="{{ route('reviews.index') }}" class="btn btn-info">Accéder</a>
                </div>
            </div>
        </div>

        <!-- Paiements -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">💵 Paiements</h5>
                    <p class="card-text">Consulter l’historique de mes paiements</p>
                    <a href="{{ route('payments.index') }}" class="btn btn-success">Accéder</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 📌 Section Produits -->
    <div class="card mt-4">
        <div class="card-header">
            <h4>📦 Mes Produits</h4>
            <a href="{{ route('products.create') }}" class="btn btn-primary float-end">
                ➕ Ajouter un produit
            </a>
        </div>
        <div class="card-body">
            @if($products->count())
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Prix</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ number_format($product->price, 0, ',', ' ') }} GNF</td>
                
                                <td>
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">✏ Modifier</a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce produit ?')">🗑 Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $products->links() }}
            @else
                <p class="text-muted">Aucun produit pour le moment.</p>
            @endif
        </div>
    </div>
</div>
@endsection
