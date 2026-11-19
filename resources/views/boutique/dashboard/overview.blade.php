            <section class="sv-hero sv-in">
                <div class="sv-hello" id="svHello">
                    <div class="sv-hello-txt">
                        <span class="sv-chip">{{ ucfirst($_dayLabel($now)) }} {{ $now->day }} {{ mb_strtolower(__('app.months')[$now->month]) }}</span>
                        <h2 class="sv-hello-title">Bonjour, {{ $firstName }}.
                            @if($pendingCount > 0)
                                <span>{{ $pendingCount }} commande{{ $pendingCount > 1 ? 's vous attendent' : ' vous attend' }}.</span>
                            @else
                                <span>Tout est à jour.</span>
                            @endif
                        </h2>
                        <p class="sv-hello-sub">
                            @if($cmdLivreesMonth === 0)
                                Aucune vente livrée ce mois-ci pour l'instant.
                            @else
                                {{ $cmdLivreesMonth }} vente{{ $cmdLivreesMonth > 1 ? 's livrées' : ' livrée' }} ce mois-ci, pour {{ number_format($caMonth, 0, ',', ' ') }} {{ $devise }} net.
                            @endif
                            @if($unassignedCount > 0)
                                Assignez un livreur aux commandes en attente pour les débloquer.
                            @endif
                        </p>
                        {{-- Actions regroupées (façon Sellio) : une seule couleur pour l'action principale --}}
                        <div class="db-acts" role="group" aria-label="Actions rapides">
                            <a href="{{ route('boutique.orders.index', ['etape' => 'nouvelle']) }}" class="db-act is-main"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 4 7v10l8 4 8-4V7zM4 7l8 4 8-4M12 11v10"/></svg>Traiter les commandes @if($pendingCount > 0)<b>{{ $pendingCount }}</b>@endif</a>
                            <a href="{{ route('boutique.orders.index', ['nouvelle' => 1]) }}" class="db-act"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Nouvelle commande</a>
                            <a href="{{ $isPro || $totalProduits < $maxProduits ? route('products.create') : route('boutique.subscription.upgrade') }}" class="db-act"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12 12 3h8v8l-9 9zM15.5 8.5h.01"/></svg>Ajouter un produit</a>
                            <a href="{{ route('boutique.storefront.edit') }}" class="db-act"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>Personnaliser ma boutique</a>
                            <button type="button" class="db-act" data-share-shop="{{ $shopPublicUrl }}" data-share-name="{{ $shop->name }}"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7M16 6l-4-4-4 4M12 2v13"/></svg>Partager ma boutique</button>
                        </div>
                        {{-- Témoignage du vendeur sur Shopio (page d'accueil, après validation) --}}
                        <a href="{{ route('boutique.testimonial.edit') }}" class="db-tm">★ Donne ton avis sur Shopio</a>
                    </div>
                    {{-- Scène 3D décorative : sphère bleue, carte « Visites ce mois », pièce GNF --}}
                    <div class="sv-scene" aria-hidden="true">
                        <div class="sv-stage" id="svStage">
                            <div class="sv-layer sv-sphere"></div>
                            <div class="sv-layer sv-float-card"><div class="sv-float">
                                <div class="sv-float-lbl">Visites ce mois</div>
                                <div class="sv-float-val sv-num" data-count="{{ $visitesMonth }}">{{ number_format($visitesMonth, 0, ',', ' ') }}</div>
                            </div></div>
                            <div class="sv-layer sv-coin-wrap"><div class="sv-coin">{{ $devise }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="sv-card sv-visits">
                    <div class="sv-card-hd">
                        <h2>Visites de la boutique</h2>
                    </div>
                    <div class="sv-visits-grid">
                        @foreach([
                            ["Aujourd'hui", $visitesToday, 'b'],
                            ['Cette semaine', $visitesWeek, 'a'],
                            ['Ce mois', $visitesMonth, 'g'],
                            ['Au total', $visitesTotal, 'n'],
                        ] as [$vLbl, $vVal, $vTone])
                        <div class="sv-visit sv-tone-{{ $vTone }}">
                            <div class="sv-visit-val sv-num" data-count="{{ $vVal }}">{{ number_format($vVal, 0, ',', ' ') }}</div>
                            <div class="sv-visit-lbl">{{ $vLbl }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </section>
