            <div class="today-grid">
                <div class="today-card">
                    <div class="today-icon">{!! $I['money_kpi'] !!}</div>
                    <div style="flex:1;min-width:0">
                        <div class="today-lbl">{{ __('app.today_net_revenue') }}</div>
                        <div class="today-val" id="todayCaVal">{{ number_format($caToday, 0, ',', ' ') }}</div>
                        <div class="today-unit">{{ $devise }}</div>
                        <div class="today-delta {{ $caTodayDelta > 0 ? 'up' : ($caTodayDelta < 0 ? 'down' : 'flat') }}" id="todayCaDelta">
                            @if($caTodayDelta > 0) ↑ +{{ $caTodayDelta }}% vs hier
                            @elseif($caTodayDelta < 0) ↓ {{ $caTodayDelta }}% vs hier
                            @else — Même niveau qu'hier @endif
                        </div>
                        @if($commToday > 0)
                        <div style="margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,.12);display:flex;flex-direction:column;gap:3px">
                            <div style="display:flex;justify-content:space-between;font-size:10px;color:rgba(255,255,255,.4)"><span>CA brut</span><span style="font-family:var(--mono);color:rgba(255,255,255,.6)">{{ number_format($caGrossToday,0,',',' ') }}</span></div>
                            <div style="display:flex;justify-content:space-between;font-size:10px;color:rgba(255,255,255,.4)"><span>Commissions</span><span style="font-family:var(--mono);color:#fca5a5">− {{ number_format($commToday,0,',',' ') }}</span></div>
                            <div style="display:flex;justify-content:space-between;font-size:10px;font-weight:700;padding-top:3px;border-top:1px dashed rgba(255,255,255,.1)"><span style="color:#7DB4FF">Net</span><span style="font-family:var(--mono);color:#7DB4FF">{{ number_format($caToday,0,',',' ') }}</span></div>
                        </div>
                        @endif
                    </div>
                    {{-- Wave sparkline décoration --}}
                    <div class="today-wave">
                        <svg viewBox="0 0 200 100" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0,70 C20,70 25,30 50,40 C75,50 80,20 100,25 C120,30 130,60 150,50 C170,40 180,15 200,20 L200,100 L0,100 Z" fill="rgba(255,255,255,.12)"/>
                            <path d="M0,80 C25,80 30,50 55,58 C80,66 90,35 115,42 C140,49 150,68 175,60 C190,55 200,35 210,30 L210,100 L0,100 Z" fill="rgba(255,255,255,.07)"/>
                            <path d="M0,70 C20,70 25,30 50,40 C75,50 80,20 100,25 C120,30 130,60 150,50 C170,40 180,15 200,20" stroke="rgba(255,255,255,.35)" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <div class="today-card" style="background:linear-gradient(135deg, #0c1a35 0%, #0f2850 40%, #0d3060 100%); box-shadow:0 8px 32px rgba(15,40,80,.4), 0 2px 8px rgba(0,0,0,.25); border-color:rgba(59,130,246,.25)">
                    <div class="today-icon">{!! $I['box_today'] !!}</div>
                    <div>
                        <div class="today-lbl">{{ __('app.today_orders') }}</div>
                        <div class="today-val" id="todayCmdVal">{{ $cmdToday }}</div>
                        <div class="today-unit">commandes reçues</div>
                        <div class="today-delta {{ $cmdToday >= $cmdYest ? 'up' : 'down' }}" id="todayCmdDelta">
                            @if($cmdToday > $cmdYest) ↑ +{{ $cmdToday - $cmdYest }} vs hier
                            @elseif($cmdToday < $cmdYest) ↓ {{ $cmdYest - $cmdToday }} de moins vs hier
                            @else — Même niveau qu'hier @endif
                        </div>
                    </div>
                    {{-- Wave sparkline décoration --}}
                    <div class="today-wave">
                        <svg viewBox="0 0 200 100" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0,60 C15,60 25,80 50,65 C75,50 85,25 110,35 C135,45 140,70 165,55 C182,44 195,20 210,25 L210,100 L0,100 Z" fill="rgba(22,104,216,.18)"/>
                            <path d="M0,75 C20,75 30,90 55,78 C80,66 90,42 115,50 C140,58 150,78 175,68 C192,61 200,42 210,38 L210,100 L0,100 Z" fill="rgba(22,104,216,.10)"/>
                            <path d="M0,60 C15,60 25,80 50,65 C75,50 85,25 110,35 C135,45 140,70 165,55 C182,44 195,20 210,25" stroke="rgba(22,104,216,.55)" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- BLOC B --}}
            @if($alerts->isNotEmpty())
            <div class="alerts-zone">
                @foreach($alerts as $alert)
                <div class="alert-item {{ $alert['type'] }}">
                    <span class="alert-ico">{!! $alert['ico'] !!}</span>
                    <span class="alert-msg">{{ $alert['msg'] }}</span>
                    @if($alert['link'])<a href="{{ $alert['link'] }}" class="alert-cta">{{ $alert['cta'] }} →</a>@endif
                </div>
                @endforeach
            </div>
            @endif

            {{-- BLOC C KANBAN --}}
            <section class="sv-row sv-row-pipe">
                <div class="sv-card sv-pipe-card sv-in">
                    <div class="sv-card-hd">
                        <h2>Pipeline des commandes</h2>
                        <a href="{{ route('boutique.orders.index') }}" class="sv-btn sv-btn-line-dk sv-btn-sm">Gérer les commandes</a>
                    </div>
                    <div class="sv-pipe">
                        @foreach($kanban as $i => $col)
                        @php [$pFront, $pSide, $pTop, $pStatus] = $pipeStyle[$col['key']]; @endphp
                        <a href="{{ route('boutique.orders.index', ['status' => $pStatus]) }}" class="sv-pcol"
                           aria-label="{{ $col['label'] }} : {{ $col['count'] }} commande{{ $col['count'] > 1 ? 's' : '' }}{{ $col['key'] === 'terminees' || $col['key'] === 'annulees' ? ' ce mois' : '' }}">
                            <span class="sv-prism" style="--h:{{ max(8, (int) round($col['count'] / $pipeMax * 150)) }}px;--front:{{ $pFront }};--side:{{ $pSide }};--top:{{ $pTop }};animation-delay:{{ $i * 90 }}ms" aria-hidden="true"><span class="sv-prism-s"></span><span class="sv-prism-t"></span></span>
                            <span class="sv-pcol-n sv-num" id="kb-{{ $col['key'] }}">{{ $col['count'] }}</span>
                            <span class="sv-pcol-lbl">{{ $col['label'] }}@if(in_array($col['key'], ['terminees', 'annulees']))<small>ce mois</small>@endif</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                <div class="sv-card sv-urgent sv-in">
                    <div class="sv-card-hd">
                        <h2>À traiter maintenant</h2>
                        @if($cmdEnRetard > 0)<span class="sv-badge sv-badge-err">{{ $cmdEnRetard }} depuis + de 2 h</span>@endif
                    </div>
                    @forelse($urgentOrders as $uo)
                    @php
                        $uoProd   = $uo->items->first()?->product?->name ?? 'Commande';
                        $uoMore   = $uo->items->count() > 1 ? ' + ' . ($uo->items->count() - 1) : '';
                        $uoClient = $uo->user->name ?? $uo->client_name ?? 'Client';
                    @endphp
                    <div class="sv-urgent-row">
                        <span class="sv-tile sv-tile-sm sv-tile-soft" aria-hidden="true">{!! $I['box'] !!}</span>
                        <div class="sv-urgent-txt">
                            <div class="sv-urgent-prod">{{ $uoProd }}{{ $uoMore }}</div>
                            <div class="sv-urgent-meta">#{{ $uo->id }} · {{ $uoClient }} · <span class="sv-num">{{ number_format($uo->total, 0, ',', ' ') }} {{ $devise }}</span> · {{ $uo->created_at->diffForHumans() }}</div>
                        </div>
                        <a href="{{ route('boutique.orders.index', ['search' => $uo->id]) }}" class="sv-btn sv-btn-blue sv-btn-sm">Assigner</a>
                    </div>
                    @empty
                    <p class="sv-ok">Aucune commande en attente de livreur. Tout est assigné.</p>
                    @endforelse
                    <a href="{{ route('boutique.orders.index', ['status' => 'en_attente']) }}" class="sv-btn sv-btn-line-dk sv-btn-sm" style="align-self:flex-start">Voir toutes les commandes en attente</a>
                </div>
            </section>

            {{-- BLOC D : Meilleurs clients · Actions rapides · Livraison --}}
            <section class="sv-row sv-row-3">
                <div class="sv-card sv-clients sv-in">
                    <div class="sv-card-hd">
                        <h2>Meilleurs clients</h2>
                        <a href="{{ route('boutique.clients.index') }}" class="sv-btn sv-btn-line-dk sv-btn-sm">Tous</a>
                    </div>
                    @forelse($topClients as $tc)
                    @php $tcName = $topClientNames[$tc->user_id] ?? $tc->client_name ?? 'Client'; @endphp
                    <div class="sv-client">
                        <div class="sv-client-top">
                            <strong>{{ $tcName }}</strong>
                            <span class="sv-num">{{ number_format($tc->spent, 0, ',', ' ') }} {{ $devise }}</span>
                        </div>
                        <div class="sv-bar" role="img" aria-label="{{ $tc->n }} commande{{ $tc->n > 1 ? 's' : '' }} livrée{{ $tc->n > 1 ? 's' : '' }}">
                            <span style="--r:{{ round($tc->spent / $topClientMax, 3) }}"></span>
                        </div>
                    </div>
                    @empty
                    <p class="sv-muted">Vos meilleurs clients apparaîtront ici après les premières commandes livrées.</p>
                    @endforelse
                </div>

            <div class="quick-section sv-in">
                <div class="quick-card">
                    <div class="quick-card-hd">
                        <h2 class="quick-card-title">Actions rapides</h2>
                    </div>
                    <div class="quick-grid">
                        {{-- Commandes — toujours accessible, mais avec usage pour plan gratuit --}}
                        <a href="{{ route('boutique.orders.index') }}" class="quick-btn" style="--q-bg:#fffbeb;--q-border:#fde68a;--q-color:#f59e0b">
                            <div class="quick-btn-ico" style="color:#f59e0b">{!! $I['list_q'] !!}</div>
                            <div class="quick-btn-lbl">Commandes</div>
                            @if(!$isPro)
                                <div class="quick-btn-sub {{ $cmdClass === 'danger' ? '' : '' }}" style="color:{{ $cmdClass === 'danger' ? '#dc2626' : ($cmdClass === 'warn' ? '#d97706' : '') }}">{{ $cmdMonth }}/{{ $maxCmdMois }} ce mois</div>
                            @else
                                <div class="quick-btn-sub">Voir & gérer</div>
                            @endif
                        </a>

                        {{-- Nouveau produit — accessible mais avec usage --}}
                        <a href="{{ $isPro || $totalProduits < $maxProduits ? route('products.create') : route('boutique.subscription.upgrade') }}" class="quick-btn" style="--q-bg:#EAF2FF;--q-border:#7DB4FF;--q-color:#1668D8">
                            <div class="quick-btn-ico" style="color:#1668D8">{!! $I['plus_q'] !!}</div>
                            <div class="quick-btn-lbl">Nouveau produit</div>
                            @if(!$isPro)
                                <div class="quick-btn-sub" style="color:{{ $prodClass === 'danger' ? '#dc2626' : ($prodClass === 'warn' ? '#d97706' : '') }}">{{ $totalProduits }}/{{ $maxProduits }} produits</div>
                                @if($totalProduits >= $maxProduits)
                                    <div class="qb-locked-badge">🔒 Limite atteinte</div>
                                @endif
                            @else
                                <div class="quick-btn-sub">Ajouter au catalogue</div>
                            @endif
                        </a>

                        {{-- Livreurs — bloqué plan gratuit --}}
                        @if($isPro)
                            <a href="{{ route('boutique.livreurs.index') }}" class="quick-btn" style="--q-bg:#EAF2FF;--q-border:#7DB4FF;--q-color:#1F7CF6">
                                <div class="quick-btn-ico" style="color:#1F7CF6">{!! $I['bike_q'] !!}</div>
                                <div class="quick-btn-lbl">Livreurs</div>
                                <div class="quick-btn-sub">Voir en ligne</div>
                            </a>
                        @else
                            <a href="{{ route('boutique.subscription.upgrade') }}" class="quick-btn locked" style="--q-bg:#fef2f2;--q-border:#fca5a5;--q-color:#ef4444">
                                <div class="quick-btn-ico" style="color:#ef4444;filter:grayscale(.5)">{!! $I['bike_q'] !!}</div>
                                <div class="quick-btn-lbl" style="color:var(--muted)">Livreurs</div>
                                <div class="qb-locked-badge">🔒 Plan Pro</div>
                            </a>
                        @endif

                        {{-- Paiements — toujours accessible --}}
                        <a href="{{ route('boutique.payments.index') }}" class="quick-btn" style="--q-bg:#eff6ff;--q-border:#93c5fd;--q-color:#3b82f6">
                            <div class="quick-btn-ico" style="color:#3b82f6">{!! $I['card_q'] !!}</div>
                            <div class="quick-btn-lbl">Paiements</div>
                            <div class="quick-btn-sub">Revenus reçus</div>
                        </a>
                    </div>
                </div>
            </div>

                <div class="sv-card sv-delivery sv-in">
                    <div class="sv-card-hd">
                        <h2>Livraison</h2>
                        <a href="{{ $isPro ? route('boutique.livreurs.index') : route('boutique.subscription.upgrade') }}" class="sv-btn sv-btn-line-dk sv-btn-sm">Gérer</a>
                    </div>
                    @forelse($livreursDisponibles->take(2) as $lv)
                    @php $lvParts = preg_split('/\s+/', trim($lv->name)); $lvIni = mb_strtoupper(mb_substr($lvParts[0], 0, 1) . mb_substr($lvParts[1] ?? $lvParts[0], isset($lvParts[1]) ? 0 : 1, 1)); @endphp
                    <div class="sv-rider">
                        <span class="sv-rider-av" aria-hidden="true">{{ $lvIni }}<i></i></span>
                        <div><strong>{{ $lv->name }}</strong><div class="sv-rider-st">Disponible</div></div>
                    </div>
                    @empty
                    <div class="sv-rider sv-rider-off">
                        <span class="sv-rider-av" aria-hidden="true">{!! $I['bike'] !!}</span>
                        <div><strong>Aucun livreur disponible</strong><div class="sv-muted">Ajoutez un livreur ou confiez la course à une entreprise partenaire.</div></div>
                    </div>
                    @endforelse
                    <dl class="sv-kv">
                        <div><dt>Entreprises de livraison</dt><dd class="sv-num">{{ $partenairesCount }}</dd></div>
                        <div><dt>Commandes non assignées</dt><dd class="sv-num {{ $unassignedCount > 0 ? 'is-warn' : '' }}">{{ $unassignedCount }}</dd></div>
                    </dl>
                </div>
            </section>
