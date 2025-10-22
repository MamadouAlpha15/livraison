@extends('layouts.app')

@section('content')
<div class="container">
    <h2>⭐ Avis sur mes produits et livraisons</h2>

    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>#</th>
                <th>Commande</th>
                <th>Client</th>
                <th>Note</th>
                <th>Commentaire</th>
                <th>Livreur</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviews as $review)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>#{{ $review->order->id }}</td>
                <td>{{ $review->client->name }}</td>
                <td>⭐ {{ $review->rating }}/5</td>
                <td>{{ $review->comment ?? '—' }}</td>
                <td>{{ $review->livreur ? $review->livreur->name : '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Aucun avis pour l’instant.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
