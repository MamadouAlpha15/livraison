            <div class="perf-grid">
            <div class="plan-locked-wrap">
            @if(!$isPro)
            <div class="plan-locked-overlay">
                <div class="plan-locked-ico">📅</div>
                <div class="plan-locked-title">Analyse par période — Plan Pro requis</div>
                <div class="plan-locked-sub">Analysez vos revenus semaine par semaine, mois par mois. Disponible avec le Plan Pro.</div>
                <a href="{{ route('boutique.subscription.upgrade') }}" class="plan-locked-btn">✦ Débloquer l'analyse — {{ $proPriceLabel }}</a>
            </div>
            @endif
            <div class="card period-card" id="periodCard" style="margin-bottom:0;{{ !$isPro ? 'filter:blur(3px);pointer-events:none;user-select:none' : '' }}">
                <div class="card-hd">
                    <span class="card-title" style="display:inline-flex;align-items:center;gap:5px">{!! $I['cal_t'] !!} Analyse par période</span>
                    <span class="period-label" id="periodLabel">Période : <strong>Ce mois</strong></span>
                </div>
                <div class="period-btns">
                    <button class="period-btn" data-period="yesterday">Hier</button>
                    <button class="period-btn" data-period="today">Aujourd'hui</button>
                    <button class="period-btn" data-period="7days">7 derniers jours</button>
                    <button class="period-btn" data-period="30days">30 derniers jours</button>
                    <button class="period-btn active" data-period="this_month">Ce mois</button>
                    <button class="period-btn" data-period="last_month">Mois dernier</button>
                    <button class="period-btn" data-period="this_year">Cette année</button>
                    <button class="period-btn" data-period="last_year">Année dernière</button>
                </div>
                <div class="period-loading" id="periodLoading"><div class="spin"></div> Chargement…</div>
                <div id="periodStatsWrap">
                    <div class="period-stats">
                        <div class="period-stat">
                            <div class="period-stat-lbl">Chiffre d'affaires</div>
                            <div class="period-stat-val" id="pCA" title="" style="cursor:help">—</div>
                            <div class="period-stat-sub">{{ $devise }}</div>
                            <div id="pCA-full" style="font-size:10px;color:var(--brand);font-family:var(--mono);margin-top:3px;display:none;font-weight:600"></div>
                        </div>
                        <div class="period-stat">
                            <div class="period-stat-lbl">Commandes</div>
                            <div class="period-stat-val" id="pCMD">—</div>
                            <div class="period-stat-sub">commandes</div>
                        </div>
                        <div class="period-stat">
                            <div class="period-stat-lbl">Panier moyen</div>
                            <div class="period-stat-val" id="pPANIER" title="" style="cursor:help">—</div>
                            <div class="period-stat-sub">{{ $devise }} / cmd</div>
                            <div id="pPANIER-full" style="font-size:10px;color:var(--brand);font-family:var(--mono);margin-top:3px;display:none;font-weight:600"></div>
                        </div>
                        <div class="period-stat">
                            <div class="period-stat-lbl">Taux livraison</div>
                            <div class="period-stat-val" id="pTAUX">—</div>
                            <div class="period-stat-sub">% livrées</div>
                        </div>
                    </div>
                    <div class="period-chart">
                        <div class="period-bars" id="periodBars"></div>
                        <div class="period-bar-labels" id="periodLabels"></div>
                    </div>
                </div>
            </div>
            </div>{{-- /plan-locked-wrap period --}}

            {{-- Résumé rapide --}}
            <div class="card" style="margin-bottom:0">
                <div class="card-hd"><span class="card-title" style="display:inline-flex;align-items:center;gap:5px">{!! $I['chart_t'] !!} Résumé rapide</span></div>
                <div class="card-bd">
                    <div class="resume-items">
                        <div class="resume-item">
                            <div class="resume-item-ico">{!! $I['cart_r'] !!}</div>
                            <div>
                                <div class="resume-item-val">{{ $totalProduits }}</div>
                                <div class="resume-item-lbl">Total produits</div>
                            </div>
                        </div>
                        <div class="resume-item">
                            <div class="resume-item-ico">{!! $I['users_r'] !!}</div>
                            <div>
                                <div class="resume-item-val">{{ $clientsActifsCount }}</div>
                                <div class="resume-item-lbl">Clients actifs</div>
                            </div>
                        </div>
                        <div class="resume-item">
                            <div class="resume-item-ico">{!! $I['bike_r'] !!}</div>
                            <div>
                                <div class="resume-item-val">{{ $livreursActifsCount }}</div>
                                <div class="resume-item-lbl">Livreurs actifs</div>
                            </div>
                        </div>
                        <div class="resume-item">
                            <div class="resume-item-ico">{!! $I['bldg_r'] !!}</div>
                            <div>
                                <div class="resume-item-val">{{ $partenairesCount }}</div>
                                <div class="resume-item-lbl">Partenaires</div>
                            </div>
                        </div>
                        <div class="resume-item" style="border-color:var(--brand-lt);background:var(--brand-mlt)">
                            <div class="resume-item-ico" style="background:var(--brand-mlt);border-color:var(--brand-lt);color:var(--brand)">{!! $I['dollar_r'] !!}</div>
                            <div>
                                <div class="resume-item-val" style="color:var(--brand);font-size:16px">{{ number_format($caMonth/1000000,1) }}M</div>
                                <div class="resume-item-lbl" style="color:var(--brand-dk)">Revenu net ce mois</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </div>{{-- /perf-grid --}}
