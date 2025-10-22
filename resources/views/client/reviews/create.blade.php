@extends('layouts.app')

@section('content')
<div class="container">
    <h2>⭐ Donner un avis sur ma commande</h2>

    <form method="POST" action="{{ route('client.reviews.store', $order) }}">
        @csrf

        <!-- Note -->
        <div class="mb-3">
            <label for="rating" class="form-label">Note</label>
            <select name="rating" id="rating" class="form-select" required>
                <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                <option value="4">⭐⭐⭐⭐ Très bien</option>
                <option value="3">⭐⭐⭐ Moyen</option>
                <option value="2">⭐⭐ Mauvais</option>
                <option value="1">⭐ Très mauvais</option>
            </select>
        </div>

        <!-- Commentaire -->
        <div class="mb-3">
            <label for="comment" class="form-label">Commentaire (optionnel)</label>
            <textarea name="comment" id="comment" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-success">✅ Envoyer mon avis</button>
    </form>
</div>
@endsection
