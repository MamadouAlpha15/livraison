{{--
=====================================================
PARTIALS/CATALOGUE-PAGINATION.BLADE.PHP
=====================================================
Pagination du catalogue de l'accueil, en français en dur (ne dépend pas de
la locale active, donc jamais de clé "pagination.previous" visible).
Utilisée via $products->links('partials.catalogue-pagination').
Les liens sont interceptés en JS (welcome.blade.php) pour un chargement
sans rechargement de page.
=====================================================
--}}
@if ($paginator->hasPages())
<nav class="cpg" aria-label="Pages du catalogue">
    <p class="cpg-info">
        Produits <strong>{{ number_format($paginator->firstItem(), 0, ',', ' ') }}–{{ number_format($paginator->lastItem(), 0, ',', ' ') }}</strong>
        sur <strong>{{ number_format($paginator->total(), 0, ',', ' ') }}</strong>
    </p>
    <ul class="cpg-list">
        <li>
            @if ($paginator->onFirstPage())
                <span class="cpg-btn cpg-nav is-disabled" aria-disabled="true">{!! \App\Support\IconLibrary::svg('arrow-left', '', 15) !!} <span class="cpg-nav-lbl">Précédent</span></span>
            @else
                <a class="cpg-btn cpg-nav" href="{{ $paginator->previousPageUrl() }}" rel="prev">{!! \App\Support\IconLibrary::svg('arrow-left', '', 15) !!} <span class="cpg-nav-lbl">Précédent</span></a>
            @endif
        </li>

        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="cpg-num"><span class="cpg-dots" aria-hidden="true">…</span></li>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    <li class="cpg-num">
                        @if ($page == $paginator->currentPage())
                            <span class="cpg-btn is-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="cpg-btn" href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                        @endif
                    </li>
                @endforeach
            @endif
        @endforeach

        {{-- Mobile : "Page X / Y" remplace la liste des numéros --}}
        <li class="cpg-compact" aria-hidden="true">Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</li>

        <li>
            @if ($paginator->hasMorePages())
                <a class="cpg-btn cpg-nav" href="{{ $paginator->nextPageUrl() }}" rel="next"><span class="cpg-nav-lbl">Suivant</span> <span class="cpg-flip">{!! \App\Support\IconLibrary::svg('arrow-left', '', 15) !!}</span></a>
            @else
                <span class="cpg-btn cpg-nav is-disabled" aria-disabled="true"><span class="cpg-nav-lbl">Suivant</span> <span class="cpg-flip">{!! \App\Support\IconLibrary::svg('arrow-left', '', 15) !!}</span></span>
            @endif
        </li>
    </ul>
</nav>
@endif
