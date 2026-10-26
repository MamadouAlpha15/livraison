            <div class="plan-banner {{ $isPro ? 'pro' : '' }}">
                {{-- Badge plan --}}
                <div class="plan-banner-badge {{ $isPro ? 'pro' : 'free' }}">
                    @if($isOnTrial)
                        🎁 Essai gratuit Pro — encore {{ max(0, (int) now()->diffInDays($shop->plan_expires_at, false)) }} jour{{ (int) now()->diffInDays($shop->plan_expires_at, false) > 1 ? 's' : '' }} (jusqu'au {{ $shop->plan_expires_at->format('d/m/Y') }})
                    @elseif($isPro)
                        ✦ Plan Pro actif — expire le {{ $shop->plan_expires_at->format('d/m/Y') }}
                    @else
                        🛍 Plan Gratuit
                    @endif
                </div>

                {{-- Barres d'usage (seulement plan gratuit) --}}
                @if(!$isPro)
                <div class="plan-usages">
                    {{-- Produits --}}
                    <div class="plan-usage-item">
                        <div class="plan-usage-top">
                            <span class="plan-usage-lbl">Produits</span>
                            <span class="plan-usage-count {{ $prodClass }}">{{ $totalProduits }}/{{ $maxProduits }}</span>
                        </div>
                        <div class="plan-usage-track">
                            <div class="plan-usage-fill {{ $prodClass }}" style="width:{{ $usageProdPct }}%"></div>
                        </div>
                    </div>
                    {{-- Commandes ce mois --}}
                    <div class="plan-usage-item">
                        <div class="plan-usage-top">
                            <span class="plan-usage-lbl">Commandes (mois)</span>
                            <span class="plan-usage-count {{ $cmdClass }}">{{ $cmdMonth }}/{{ $maxCmdMois }}</span>
                        </div>
                        <div class="plan-usage-track">
                            <div class="plan-usage-fill {{ $cmdClass }}" style="width:{{ $usageCmdPct }}%"></div>
                        </div>
                    </div>
                    {{-- Fonctionnalités bloquées --}}
                    <div style="display:flex;flex-wrap:wrap;gap:5px;align-items:center">
                        <span style="font-size:10.5px;font-weight:700;color:var(--text-2);">Bloqués :</span>
                        @foreach(['Livreurs','Partenaires','Rapports','Équipe','Graphiques','Analyse période'] as $feat)
                            <span style="font-size:10px;font-weight:700;background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;padding:2px 8px;border-radius:20px;">🔒 {{ $feat }}</span>
                        @endforeach
                    </div>
                </div>
                {{-- CTA --}}
                <a href="{{ route('boutique.subscription.upgrade') }}" class="plan-banner-cta">
                    ✦ Passer au Plan Pro — {{ $proPriceLabel }}
                </a>
                @else
                {{-- Pro actif : pas de bouton renouveler --}}
                <div style="font-size:12px;color:#1260C9;font-weight:600;flex:1">
                    Toutes les fonctionnalités débloquées.
                </div>
                @endif
            </div>
