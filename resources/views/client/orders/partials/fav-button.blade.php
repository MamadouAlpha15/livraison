{{-- Bouton favori de la fiche produit (connecté : bascule en AJAX ; visiteur : connexion puis retour au produit) --}}
@auth
<button type="button" class="pp-icon-btn pp-fav {{ $isFavorited ? 'is-fav' : '' }}" id="ppFav"
        data-url="{{ route('client.products.favorite.toggle', $product) }}"
        aria-pressed="{{ $isFavorited ? 'true' : 'false' }}"
        aria-label="{{ $isFavorited ? 'Retirer des favoris' : 'Ajouter aux favoris' }}">
    {!! \App\Support\IconLibrary::svg('heart', '', 20) !!}
</button>
@else
<a href="{{ route('login', ['redirect' => url()->current()]) }}" class="pp-icon-btn pp-fav" aria-label="Se connecter pour ajouter aux favoris">
    {!! \App\Support\IconLibrary::svg('heart', '', 20) !!}
</a>
@endauth
