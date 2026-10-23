            <div class="content-grid">
                <div class="card">
                    <div class="card-hd"><span class="card-title">{{ __('app.recent_orders') }}</span><a href="{{ route('boutique.orders.index') }}" class="btn-ghost btn btn-sm">{{ __('app.see_all') }}</a></div>
                    {{-- Desktop : tableau --}}
                    <div class="tbl-wrap" style="padding:0 18px">
                        @if($recentOrders->isEmpty())<div style="padding:28px 0;text-align:center;font-size:13px;color:var(--muted)">Aucune commande pour le moment.</div>
                        @else
                        <table class="tbl">
                            <thead><tr><th>Réf / Client</th><th>Statut</th><th style="text-align:right">Montant</th></tr></thead>
                            <tbody>
                            @foreach($recentOrders as $order)
                            @php $st = $statusMap[$order->status] ?? ['label'=>ucfirst($order->status),'cls'=>'p-muted']; @endphp
                            <tr>
                                <td><div class="oid">#{{ $order->id }}</div><div class="onam">{{ $order->user->name ?? 'Client inconnu' }}</div></td>
                                <td><span class="pill {{ $st['cls'] }}">{{ $st['label'] }}</span></td>
                                <td class="oamt">{{ number_format($order->total,0,',',' ') }} <span style="font-size:9px;color:var(--muted)"> {{ $devise }}</span></td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>

                    {{-- Mobile : cartes --}}
                    @if($recentOrders->isEmpty())
                    <div class="orders-mobile" style="padding:28px 14px;text-align:center;font-size:13px;color:var(--muted);">Aucune commande pour le moment.</div>
                    @else
                    <div class="orders-mobile">
                        @foreach($recentOrders as $order)
                        @php $st = $statusMap[$order->status] ?? ['label'=>ucfirst($order->status),'cls'=>'p-muted']; @endphp
                        <a href="{{ route('boutique.orders.index') }}" class="om-card">
                            <div class="om-card-av">#{{ substr($order->id, -2) }}</div>
                            <div class="om-card-body">
                                <div class="om-card-ref">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div class="om-card-client">{{ $order->user->name ?? 'Client inconnu' }}</div>
                            </div>
                            <div class="om-card-right">
                                <span class="pill {{ $st['cls'] }}" style="font-size:10px;padding:3px 8px;">{{ $st['label'] }}</span>
                                <div class="om-card-amt" style="margin-top:4px;">
                                    {{ number_format($order->total,0,',',' ') }}
                                    <span class="om-card-devise">{{ $devise }}</span>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="right-col">
                    @if($hasLivreurs)
                    <div class="card delivery-card">
                        {{-- Hero header --}}
                        <div class="delivery-card-hero">
                            <div class="delivery-card-hero-left">
                                <div class="delivery-card-icon">{!! $I['bike_hero'] !!}</div>
                                <div>
                                    <div class="delivery-card-title">Livraison</div>
                                    <div class="delivery-card-sub">{{ $livreursDisponibles->count() }} livreur(s) disponible(s)</div>
                                </div>
                            </div>
                            <a href="{{ route('boutique.employees.index') }}" class="delivery-card-manage">Gérer →</a>
                        </div>
                        {{-- Tabs --}}
                        <div class="tab-bar">
                            <button class="tab-btn active" data-tab="livreurs" style="gap:5px">{!! $I['bike_tab'] !!} Livreurs <span class="tab-count">{{ $livreursDisponibles->count() }}</span></button>
                            @if($hasCompanies)
                                @if($isPro)
                                <button class="tab-btn" data-tab="companies" style="gap:5px">{!! $I['bldg_tab'] !!} Entreprises <span class="tab-count {{ $deliveryCompanies->count() === 0 ? 'zero':'' }}">{{ $deliveryCompanies->count() }}</span></button>
                                @else
                                <button class="tab-btn" style="gap:5px;opacity:.55;cursor:default;" type="button" onclick="document.getElementById('planLockPartenairesModal').style.display='flex'">{!! $I['bldg_tab'] !!} Entreprises 🔒</button>
                                @endif
                            @endif
                        </div>
                        {{-- Liste livreurs --}}
                        <div class="tab-panel active" id="tab-livreurs">
                            <div class="lv-list">
                                @foreach($livreursDisponibles->take(5) as $i => $livreur)
                                @php
                                    $lp    = explode(' ', $livreur->name);
                                    $linit = strtoupper(substr($lp[0],0,1)).(isset($lp[1]) ? strtoupper(substr($lp[1],0,1)) : strtoupper(substr($lp[0],1,1)));
                                    $lcol  = $avColors[$i % count($avColors)];
                                    $busy  = !empty($livreur->current_order_id);
                                    $waNum = preg_replace('/\D/', '', $livreur->phone ?? '');
                                @endphp
                                <div class="lv-row">
                                    <div class="lv-av" style="background:{{ $lcol }}">{{ $linit }}</div>
                                    <div class="lv-info">
                                        <div class="lv-nm">{{ $livreur->name }}</div>
                                        @if($livreur->phone)
                                            <div class="lv-phone">
                                                {!! $I['phone_sm'] !!}
                                                <a href="tel:{{ $livreur->phone }}" style="color:inherit;text-decoration:none">{{ $livreur->phone }}</a>
                                            </div>
                                        @else
                                            <div class="lv-phone-warn" style="display:flex;align-items:center;gap:4px">{!! $I['warn_sm'] !!} Pas de téléphone</div>
                                        @endif
                                    </div>
                                    <span class="lv-status-badge {{ $busy ? 'busy' : 'available' }}">{{ $busy ? 'En course' : 'Dispo' }}</span>
                                    @if($waNum)
                                    <a href="https://wa.me/{{ $waNum }}" target="_blank" class="lv-wa-btn" title="Contacter par WhatsApp">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    </a>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @if($livreursDisponibles->count() > 5)
                            <div class="lv-footer">
                                <a href="{{ route('boutique.employees.index') }}">+ {{ $livreursDisponibles->count()-5 }} autre(s) livreur(s) →</a>
                            </div>
                            @endif
                        </div>
                        @if($hasCompanies)
                        <div class="tab-panel plan-locked-wrap" id="tab-companies">
                            <div class="co-list">
                                @foreach($deliveryCompanies->take(4) as $company)
                                <div class="co-row" onclick="window.location='{{ route('company.chat.show', $company) }}'" title="Ouvrir la discussion">
                                    <div class="co-logo">@if(!empty($company->logo))<img src="{{ \App\Services\ImageOptimizer::url($company->logo, 'thumb') ?? asset('storage/'.$company->logo) }}" alt="{{ $company->name }}" loading="lazy" width="28" height="28">@else {!! $I['truck_co'] !!} @endif</div>
                                    <div class="co-info"><div class="co-nm">{{ $company->name }}</div><div class="co-mt">{{ $company->phone ?? 'Contact non renseigné' }}</div></div>
                                    @if($company->commission_rate)<span class="co-commission">{{ number_format($company->commission_rate*100,1) }}%</span>@endif
                                    <a href="{{ route('company.chat.show', $company) }}" class="btn btn-sm btn-primary" onclick="event.stopPropagation()" style="gap:4px">{!! $I['msg_btn'] !!} Contacter</a>
                                </div>
                                @endforeach
                            </div>
                            @if($deliveryCompanies->count() > 4)<div class="lv-footer"><a href="{{ route('delivery.companies.index') }}">Voir toutes →</a></div>@endif
                            @if(!$isPro)
                            <div class="plan-locked-overlay">
                                <div class="plan-locked-ico">🚚</div>
                                <div class="plan-locked-title">Partenaires — Plan Pro requis</div>
                                <div class="plan-locked-sub">L'accès aux entreprises de livraison partenaires est réservé au Plan Pro. Passez au Pro pour contacter et collaborer avec les transporteurs.</div>
                                <a href="{{ route('boutique.subscription.upgrade') }}" class="plan-locked-btn">✦ Débloquer les partenaires — {{ $proPriceLabel }}</a>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="card delivery-card">
                        {{-- Hero header vide --}}
                        <div class="delivery-card-hero">
                            <div class="delivery-card-hero-left">
                                <div class="delivery-card-icon">{!! $I['truck_hero'] !!}</div>
                                <div>
                                    <div class="delivery-card-title">Livraison</div>
                                    <div class="delivery-card-sub">Aucun livreur actif</div>
                                </div>
                            </div>
                            <a href="{{ route('boutique.employees.create') }}" class="delivery-card-manage">+ Ajouter →</a>
                        </div>
                        @if(!$hasCompanies)
                        <div class="no-livreur-notice">
                            <div class="notice-icon">{!! $I['bike_noti'] !!}</div>
                            <div class="notice-title">Aucun livreur disponible</div>
                            <div class="notice-sub">Ajoutez vos propres livreurs dans <strong>Équipe</strong>, ou contactez une entreprise partenaire.</div>
                            <div style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap;justify-content:center">
                                <a href="{{ route('boutique.employees.create') }}" class="btn btn-sm" style="gap:4px">{!! $I['users_btn'] !!} Ajouter un livreur</a>
                                <a href="{{ route('delivery.companies.index') }}" class="btn btn-primary btn-sm" style="gap:4px">{!! $I['bldg_btn'] !!} Entreprise partenaire</a>
                            </div>
                        </div>
                        @else
                        <div class="plan-locked-wrap">
                            <div style="padding:12px 18px;background:#fffbeb;border-bottom:1px solid #fde68a;display:flex;align-items:flex-start;gap:10px">
                                <span style="flex-shrink:0;display:flex;margin-top:2px">{!! $I['warn_a'] !!}</span>
                                <div><div style="font-size:12.5px;color:#92400e;font-weight:700;margin-bottom:3px">Vous n'avez pas de livreurs</div><div style="font-size:11.5px;color:#b45309;line-height:1.55">Contactez une entreprise partenaire ci-dessous. Cliquez sur <strong>Contacter</strong> pour ouvrir une discussion.</div></div>
                            </div>
                            <div class="co-list">
                                @foreach($deliveryCompanies->take(4) as $company)
                                <div class="co-row" onclick="window.location='{{ route('company.chat.show', $company) }}'" title="Ouvrir la discussion">
                                    <div class="co-logo">@if(!empty($company->logo))<img src="{{ \App\Services\ImageOptimizer::url($company->logo, 'thumb') ?? asset('storage/'.$company->logo) }}" alt="{{ $company->name }}" loading="lazy" width="28" height="28">@else {!! $I['truck_co'] !!} @endif</div>
                                    <div class="co-info"><div class="co-nm">{{ $company->name }}</div><div class="co-mt">{{ $company->phone ?? 'Contact non renseigné' }}</div></div>
                                    @if($company->commission_rate)<span class="co-commission">{{ number_format($company->commission_rate*100,1) }}%</span>@endif
                                    <a href="{{ route('company.chat.show', $company) }}" class="btn btn-sm btn-primary" onclick="event.stopPropagation()" style="gap:4px">{!! $I['msg_btn'] !!} Contacter</a>
                                </div>
                                @endforeach
                            </div>
                            @if(!$isPro)
                            <div class="plan-locked-overlay">
                                <div class="plan-locked-ico">🚚</div>
                                <div class="plan-locked-title">Partenaires — Plan Pro requis</div>
                                <div class="plan-locked-sub">L'accès aux entreprises de livraison partenaires est réservé au Plan Pro. Passez au Pro pour collaborer avec les transporteurs.</div>
                                <a href="{{ route('boutique.subscription.upgrade') }}" class="plan-locked-btn">✦ Débloquer les partenaires — {{ $proPriceLabel }}</a>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
