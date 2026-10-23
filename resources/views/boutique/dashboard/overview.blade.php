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
                        <div class="sv-hello-cta">
                            <a href="{{ route('boutique.orders.index', ['status' => 'en_attente']) }}" class="sv-btn sv-btn-amber">Traiter les commandes</a>
                            <button type="button" class="sv-btn sv-btn-line" data-share-shop="{{ $shopPublicUrl }}" data-share-name="{{ $shop->name }}">Partager ma boutique</button>
                        </div>
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
