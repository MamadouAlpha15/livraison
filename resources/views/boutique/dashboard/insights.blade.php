            @if($topProducts->isNotEmpty())
            <div class="card">
                <div class="card-hd"><span class="card-title" style="cursor:pointer;display:inline-flex;align-items:center;gap:5px" onclick="window.location='{{ route('products.top') }}'">Top produits — ventes du mois {!! $I['trophy_t'] !!}</span><a href="{{ route('products.top') }}" class="btn btn-ghost btn-sm">Voir le classement →</a></div>
                <div class="card-bd">
                    @foreach($topProducts as $product)
                    @php $pct = round(($product->unites_vendues / $maxSales)*100); @endphp
                    <div class="sp-row">
                        <span class="sp-lbl" title="{{ $product->name }}">{{ Str::limit($product->name, 18) }}</span>
                        <div class="sp-track"><div class="sp-fill" data-pct="{{ $pct }}" style="width:{{ $pct }}%;transform:scaleX(0)"></div></div>
                        <span class="sp-val">{{ $product->unites_vendues }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- COMPARAISON SECTEUR (Plan Pro) --}}
            @if($sectorAvg !== null)
            <div class="card" style="margin-top:22px">
                <div class="card-hd"><span class="card-title" style="display:inline-flex;align-items:center;gap:5px">📊 Comparaison avec votre secteur — {{ $shop->type }}</span></div>
                <div class="plan-locked-wrap">
                @if(!$isPro)
                <div class="plan-locked-overlay">
                    <div class="plan-locked-ico">📈</div>
                    <div class="plan-locked-title">Comparaison secteur — Plan Pro requis</div>
                    <div class="plan-locked-sub">Découvrez comment vous vous situez face aux autres boutiques {{ $shop->type }} de votre pays — une info que vous ne trouverez nulle part ailleurs. Réservé au Plan Pro.</div>
                    <a href="{{ route('boutique.subscription.upgrade') }}" class="plan-locked-btn">✦ Débloquer la comparaison — {{ $proPriceLabel }}</a>
                </div>
                @endif
                <div style="padding:20px 18px;{{ !$isPro ? 'filter:blur(4px);pointer-events:none;user-select:none' : '' }}">
                    <div style="display:flex;align-items:center;justify-content:center;gap:22px;flex-wrap:wrap;text-align:center">
                        <div>
                            <div style="font-size:10.5px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.4px">Votre revenu ce mois</div>
                            <div style="font-size:21px;font-weight:900;color:var(--text);margin-top:2px">{{ number_format($caGrossMonth,0,',',' ') }} {{ $devise }}</div>
                        </div>
                        <div style="font-size:13px;color:var(--muted);font-weight:700">vs</div>
                        <div>
                            <div style="font-size:10.5px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.4px">Moyenne du secteur</div>
                            <div style="font-size:21px;font-weight:900;color:var(--muted);margin-top:2px">{{ number_format($sectorAvg,0,',',' ') }} {{ $devise }}</div>
                        </div>
                    </div>
                    <div style="text-align:center;margin-top:16px">
                        <span style="display:inline-block;font-size:13px;font-weight:800;padding:7px 18px;border-radius:20px;background:{{ $sectorDelta >= 0 ? '#ecfdf5' : '#fef2f2' }};color:{{ $sectorDelta >= 0 ? '#065f46' : '#991b1b' }}">
                            {{ $sectorDelta >= 0 ? '↑' : '↓' }} Vous vendez {{ number_format(abs($sectorDelta),1,',',' ') }}% {{ $sectorDelta >= 0 ? 'de plus' : 'de moins' }} que la moyenne des boutiques {{ $shop->type }} de {{ $shop->country }}
                        </span>
                    </div>
                    <div style="text-align:center;margin-top:8px;font-size:10.5px;color:var(--muted)">Basé sur {{ $sectorCount }} boutique{{ $sectorCount > 1 ? 's' : '' }} {{ $shop->type }} approuvée{{ $sectorCount > 1 ? 's' : '' }} — comparaison anonyme, aucune boutique individuelle n'est identifiable</div>
                </div>
                </div>
            </div>
            @endif

            {{-- PRODUITS A RISQUE --}}
            <div class="card" style="margin-bottom:22px">
                <div class="card-hd"><span class="card-title" style="display:inline-flex;align-items:center;gap:5px">{!! $I['warn_t'] !!} Produits à risque — 0 vente ce mois</span><a href="{{ route('products.index') }}" class="btn btn-ghost btn-sm">Gérer les produits →</a></div>
                @if($produitsRisque->isEmpty())
                    <div class="risk-empty"><span class="ico">{!! $I['check_big'] !!}</span>Tous vos produits ont été vendus ce mois !</div>
                @else
                <div style="display:flex;flex-wrap:wrap;gap:0;padding:0">
                    @foreach($produitsRisque as $product)
                    <div class="risk-row" style="flex:1;min-width:180px;border-right:1px solid #f3f6f4;border-bottom:none">
                        @if(!empty($product->image))<img src="{{ \App\Services\ImageOptimizer::url($product->image, 'thumb') ?? asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="risk-img" loading="lazy" width="38" height="38">
                        @else<div class="risk-img-placeholder">{!! $I['tag_ph'] !!}</div>@endif
                        <div class="risk-info">
                            <div class="risk-name" title="{{ $product->name }}">{{ Str::limit($product->name, 20) }}</div>
                            <div class="risk-meta">{{ $product->price ? number_format($product->price,0,',',' ').' '.$devise : 'Prix non défini' }}</div>
                        </div>
                        <span class="risk-badge">0 vente</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
