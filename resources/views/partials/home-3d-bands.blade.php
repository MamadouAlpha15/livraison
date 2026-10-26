{{--
    Bandeaux 02 → 06 de l'accueil 3D (après le catalogue). Masqués pendant une recherche.
    Variables : $testimonials
--}}
@php
    $I = fn ($n, $s = 24) => \App\Support\IconLibrary::svg($n, '', $s);
    $blds = [[30,20,60,50,40],[175,10,40,40,56],[250,20,50,32,34],[20,110,60,50,48],[380,110,50,40,44],[340,210,60,56,64],[180,250,50,46,30],[420,240,46,50,38],[60,200,40,36,26]];
    $route = 'M70 300 C 140 290, 150 210, 230 190 S 360 140, 410 70';
@endphp
<div class="sp3">

{{-- ═════════════ 02 · LIVRAISON ═════════════ --}}
<section class="band3-deep" id="livraison" aria-labelledby="dlv3Title">
    <span class="tab3 tab3-b" aria-hidden="true">02</span>
    <div class="w3 sec3">
        <div class="dlv3">
            <div class="rv3 stack3">
                <h2 class="h2-3 on-deep" id="dlv3Title">Livraison rapide. <span class="sky3">Suivi en temps réel.</span></h2>
                <p class="p3 on-deep-2">Le livreur part de la boutique, vous suivez chaque étape de votre commande jusqu'à votre porte.</p>
                <ul class="dlv3-feats">
                    <li class="feat3-d"><span class="feat3-ic-d">{!! $I('map-pin') !!}</span>Suivi de votre commande</li>
                    <li class="feat3-d"><span class="feat3-ic-d">{!! $I('phone') !!}</span>Notifications en temps réel</li>
                    <li class="feat3-d"><span class="feat3-ic-d">{!! $I('shield') !!}</span>Livraison sûre et fiable</li>
                </ul>
            </div>

            <div class="map3" aria-hidden="true">
                <div class="iso3 isoin3">
                    <svg width="480" height="360" viewBox="0 0 480 360" style="position:absolute;inset:0">
                        <g stroke="#1A3A6B" stroke-width="10" fill="none" stroke-linecap="round"><path d="M-20 90 L500 60"/><path d="M-20 250 L500 290"/><path d="M120 -20 L160 380"/><path d="M330 -20 L300 380"/><path d="M-20 180 Q240 140 500 200"/></g>
                        <g stroke="#163260" stroke-width="4" fill="none"><path d="M40 -20 L60 380"/><path d="M230 -20 L220 380"/><path d="M420 -20 L440 380"/><path d="M-20 320 L500 330"/></g>
                        <path d="{{ $route }}" fill="none" stroke="#12305C" stroke-width="12" stroke-linecap="round"/>
                        <path class="route3" pathLength="1" d="{{ $route }}" fill="none" stroke="#4A9BFF" stroke-width="6" stroke-linecap="round"/>
                        <circle cx="70" cy="300" r="10" fill="#F59E0B"/>
                    </svg>
                    <span class="pulse3" style="left:394px;top:54px"></span>
                    @foreach($blds as $b)
                    <div class="bb3" style="left:{{ $b[0] }}px;top:{{ $b[1] }}px;width:{{ $b[2] }}px;height:{{ $b[3] }}px">
                        <div class="bb3-t" style="transform:translateZ({{ $b[4] }}px)"></div>
                        <div class="bb3-f" style="top:{{ $b[3] }}px;width:{{ $b[2] }}px;height:{{ $b[4] }}px"></div>
                        <div class="bb3-r" style="left:{{ $b[2] }}px;width:{{ $b[4] }}px;height:{{ $b[3] }}px"></div>
                    </div>
                    @endforeach
                    <div class="stand3" style="left:394px;top:38px"><svg width="32" height="32" viewBox="0 0 24 24" fill="#4A9BFF" stroke="#0B1E3D" stroke-width="1.5"><path d="M12 21s-6.5-6-6.5-11a6.5 6.5 0 0 1 13 0c0 5-6.5 11-6.5 11zM12 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/></svg></div>
                    <div class="rider3 stand3" style="offset-path:path('{{ $route }}')">{!! $I('bike', 20) !!}</div>
                </div>
                <div class="map3-card">
                    <div class="map3-card-hd"><span>{!! $I('store', 18) !!}</span><div><strong>Votre commande</strong><em data-dlv-label>En cours de livraison</em></div></div>
                    <div class="map3-card-ft">Arrivée prévue : <strong>[heure estimée]</strong></div>
                </div>
            </div>

            <div class="dlv3-ph rv3 rv3-2" aria-hidden="true">
                <div class="dlv3-ph-scr">
                    <strong style="font-size:15px">Suivi de votre commande</strong>
                    <ol class="track3" id="track3">
                        <li class="done"><span>{!! $I('check', 14) !!}</span><div><b>Commande confirmée</b><small>La boutique a accepté</small></div></li>
                        <li class="done"><span>{!! $I('check', 14) !!}</span><div><b>En préparation</b><small>Votre colis est emballé</small></div></li>
                        <li class="cur"><span>{!! $I('check', 14) !!}</span><div><b>En cours de livraison</b><small>Le livreur est en route</small></div></li>
                        <li><span>{!! $I('check', 14) !!}</span><div><b>Livrée</b><small>Payez à la réception</small></div></li>
                    </ol>
                    <div class="dlv3-pay">Paiement à la livraison</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═════════════ 03 · PAIEMENTS ═════════════ --}}
<section class="rel3" id="paiements" aria-labelledby="pay3Title">
    <span class="tab3 tab3-y" aria-hidden="true">03</span>
    <div class="w3 sec3">
        <div class="pay3-g">
            <div class="rv3 stack3">
                <h2 class="h2-3" id="pay3Title">Paiements sécurisés. <span class="blue3">Plusieurs options.</span></h2>
                <p class="p3">Payez comme vous voulez. Par défaut, vous payez en espèces au livreur, à la réception.</p>
                <div class="coin3d shield3" aria-hidden="true">{!! $I('shield', 52) !!}</div>
            </div>
            <div class="g4-3">
                <div class="pay3 pay3-def rv3"><span class="pay3-ic pay3-ic-def">{!! $I('wallet', 26) !!}</span><strong>Paiement à la livraison</strong><span>En espèces, au livreur, à la réception.</span><span class="b3 b3-g">Par défaut</span></div>
                <div class="pay3 rv3 rv3-2"><span class="pay3-ic">{!! $I('smartphone', 26) !!}</span><strong>Orange Money</strong><span>Paiement mobile [à confirmer].</span></div>
                <div class="pay3 rv3 rv3-3"><span class="pay3-ic">{!! $I('phone', 26) !!}</span><strong>Wave</strong><span>Paiement mobile [à confirmer].</span></div>
                <div class="pay3 rv3 rv3-4"><span class="pay3-ic">{!! $I('lock', 26) !!}</span><strong>Carte bancaire</strong><span>Visa, Mastercard [à confirmer].</span></div>
            </div>
        </div>
    </div>
</section>

{{-- ═════════════ 04 · VENDEURS ═════════════ --}}
<section class="band3-amber" id="vendre" aria-labelledby="sell3Title">
    <span class="tab3 tab3-y" aria-hidden="true">04</span>
    <div class="band3-amber-sphere" aria-hidden="true"></div>
    <div class="w3 sec3 rel3">
        <div class="g2-3">
            <div class="rv3 stack3">
                <h2 class="h2-3" id="sell3Title">Vendez plus. <span class="blue3">Gérez mieux.</span></h2>
                <p class="p3">Ouvrez votre boutique en quelques minutes et touchez des clients partout en Guinée. Pas de site à créer, plus de cahier de commandes.</p>
                <ul class="checks3">
                    <li><span>{!! $I('check', 15) !!}</span>Création facile de votre boutique</li>
                    <li><span>{!! $I('check', 15) !!}</span>Suivi des commandes en temps réel</li>
                    <li><span>{!! $I('check', 15) !!}</span>Négociation des prix dans le chat</li>
                    <li><span>{!! $I('check', 15) !!}</span>Statistiques de vos ventes</li>
                </ul>
                <div class="h3-cta"><a class="btn3 btn3-p btn3-lg" href="{{ route('register') }}">Ouvrir ma boutique</a><a class="btn3 btn3-s btn3-lg" href="{{ route('register') }}">Devenir livreur partenaire</a></div>
            </div>
            <div class="lap3d" aria-hidden="true">
                <div class="dash3-frame">
                    <div class="dash3">
                        <div class="dash3-side">
                            <div class="mini3-logo" style="padding:0 4px 8px"><img src="/images/shopio-logo-192.png" alt=""> <span>Shop<b>io</b></span></div>
                            <span class="on">Tableau de bord</span><span>Produits</span><span>Commandes</span><span>Clients</span><span>Livraisons</span>
                        </div>
                        <div class="dash3-main">
                            <div class="dash3-hd"><strong>Tableau de bord</strong><span>Données de démonstration</span></div>
                            <div class="dash3-kpis" id="kpi3">
                                <div><small>Ventes totales</small><b class="num" data-kpi="48500000" data-gnf>0 GNF</b></div>
                                <div><small>Commandes</small><b class="num" data-kpi="126">0</b></div>
                                <div><small>Visites</small><b class="num" data-kpi="3240">0</b></div>
                                <div><small>Clients</small><b class="num" data-kpi="87">0</b></div>
                            </div>
                            <div class="dash3-chart">
                                <div style="font-weight:700;margin-bottom:6px">Évolution des ventes</div>
                                <svg width="100%" height="110" viewBox="0 0 400 110" preserveAspectRatio="none"><g stroke="#EEF2F8"><path d="M0 27h400M0 55h400M0 83h400"/></g><path d="M0 95 L50 88 L100 90 L150 72 L200 76 L250 58 L300 50 L350 30 L400 14 L400 110 L0 110Z" fill="#EAF2FF"/><path class="chartline3" pathLength="1" d="M0 95 L50 88 L100 90 L150 72 L200 76 L250 58 L300 50 L350 30 L400 14" fill="none" stroke="#1F7CF6" stroke-width="3" stroke-linejoin="round" vector-effect="non-scaling-stroke"/></svg>
                            </div>
                            <div class="dash3-row new"><i></i><strong>Nouvelle commande</strong><span class="ok">Confirmée</span><b class="num">[montant] GNF</b></div>
                            <div class="dash3-row"><i class="y"></i><span>[Produit]</span><span class="go">En livraison</span><b class="num">[montant] GNF</b></div>
                        </div>
                    </div>
                </div>
                <div class="dash3-base"></div>
            </div>
        </div>
    </div>
</section>

{{-- ═════════════ AFFILIATION : faire connaître Shopio (chiffres = réglages réels du programme) ═════════════ --}}
@php
    $aff3 = \App\Models\AffiliateSetting::current();
    $aff3Rate = rtrim(rtrim(number_format((float) $aff3->default_rate, 2, ',', ''), '0'), ',');
@endphp
<section class="rel3" id="affiliation" aria-labelledby="aff3Title">
    <div class="w3 sec3">
        <div class="g2-3 aff3-g">
            <div class="rv3 stack3">
                <h2 class="h2-3" id="aff3Title">Faites connaître Shopio. <span class="blue3">Gagnez de l'argent.</span></h2>
                <p class="p3">Pas besoin d'avoir une boutique. Partagez votre lien à des commerçants : quand ils ouvrent leur boutique et paient leur abonnement, vous touchez une commission.</p>
                <ul class="checks3">
                    <li><span>{!! $I('check', 15) !!}</span>{{ $aff3Rate }} % de chaque abonnement payé, pendant {{ $aff3->commission_months }} mois</li>
                    <li><span>{!! $I('check', 15) !!}</span>Un lien et un QR code à partager sur WhatsApp</li>
                    <li><span>{!! $I('check', 15) !!}</span>Retrait par Mobile Money dès {{ \App\Support\Money::gnf($aff3->payout_threshold) }}</li>
                    <li><span>{!! $I('check', 15) !!}</span>Inscription gratuite</li>
                </ul>
                <div class="h3-cta"><a class="btn3 btn3-p btn3-lg" href="{{ route('affiliation.join') }}">{!! $I('gift', 20) !!} Devenir affilié</a></div>
            </div>
            <ol class="aff3-steps rv3 rv3-2">
                <li><span class="aff3-n">1</span><div><strong>Inscrivez-vous</strong><span>Après validation par l'équipe Shopio, vous recevez votre code et votre lien.</span></div></li>
                <li><span class="aff3-n">2</span><div><strong>Partagez votre lien</strong><span>À des vendeurs sur WhatsApp, Facebook, au marché.</span></div></li>
                <li><span class="aff3-n">3</span><div><strong>Ils ouvrent leur boutique</strong><span>Votre code est déjà rempli pour eux.</span></div></li>
                <li><span class="aff3-n">4</span><div><strong>Vous êtes payé</strong><span>Une commission à chacun de leurs abonnements.</span></div></li>
            </ol>
        </div>
    </div>
</section>
<style>
.aff3-g { align-items: center; }
.aff3-steps { list-style: none; margin: 0; padding: 24px; display: grid; gap: 18px; background: #fff; border-radius: 24px;
    box-shadow: 0 1px 0 #E3E8F2, 0 24px 48px -28px rgba(11,30,61,.35); }
.aff3-steps li { display: flex; gap: 14px; align-items: flex-start; }
.aff3-steps strong { display: block; font-size: 16px; color: #16162A; }
.aff3-steps li span:not(.aff3-n) { display: block; font-size: 14px; color: #45455E; margin-top: 2px; }
.aff3-n { flex: none; width: 36px; height: 36px; border-radius: 999px; background: #EAF2FF; color: #1668D8; font-weight: 800; display: flex; align-items: center; justify-content: center; }
[data-theme="dark"] .aff3-steps { background: #10203B; box-shadow: 0 0 0 1px #22355A; }
[data-theme="dark"] .aff3-steps strong { color: #EAF0FA; }
[data-theme="dark"] .aff3-steps li span:not(.aff3-n) { color: #C3CEE0; }
[data-theme="dark"] .aff3-n { background: #13284B; color: #7DB4FF; }
</style>

{{-- ═════════════ TÉMOIGNAGES (avis réels uniquement) ═════════════ --}}
@if(($testimonials ?? collect())->isNotEmpty())
<section class="w3 sec3" aria-labelledby="rev3Title">
    <h2 class="h2-3 rv3" id="rev3Title" style="margin:0 0 40px;text-align:center">Ce que disent nos clients</h2>
    <div class="g3-3">
        @foreach($testimonials->take(3) as $r)
        <figure class="rev3 {{ $loop->first ? 'rev3-hl' : '' }} rv3 {{ ['', 'rv3-2', 'rv3-3'][$loop->index] }}">
            <div class="rev3-stars" role="img" aria-label="{{ (int) $r->rating }} étoiles sur 5">{!! \App\Support\IconLibrary::stars((float) $r->rating, 18) !!}</div>
            <blockquote>« {{ Str::limit($r->comment, 160) }} »</blockquote>
            <figcaption><span class="rev3-av">{{ mb_strtoupper(mb_substr($r->client->name ?? '?', 0, 1)) }}</span><span><strong>{{ $r->client->name ?? 'Client Shopio' }}</strong>@if($r->vendeur)<small>Achat chez {{ $r->vendeur->name }}</small>@endif</span></figcaption>
        </figure>
        @endforeach
    </div>
</section>
@endif

{{-- ═════════════ 05 · APPLICATION ═════════════ --}}
<section class="band3-deep" id="application" aria-labelledby="app3Title">
    <span class="tab3 tab3-b" aria-hidden="true">05</span>
    <div class="w3 sec3">
        <div class="g2-3">
            <div class="rv3 stack3">
                <h2 class="h2-3 on-deep" id="app3Title">Shopio est partout <span class="sky3">avec vous.</span></h2>
                <p class="p3 on-deep-2">Installez l'application en un geste, sans passer par un store. Suivez vos commandes et gérez votre boutique depuis votre téléphone.</p>
                <ul class="checks3 checks3-d">
                    <li><span>{!! $I('check', 15) !!}</span>Notifications instantanées</li>
                    <li><span>{!! $I('check', 15) !!}</span>Ouverture directe depuis l'écran d'accueil</li>
                    <li><span>{!! $I('check', 15) !!}</span>Légère pour votre forfait internet</li>
                </ul>
                <div class="h3-cta" style="align-items:center"><button type="button" class="btn3 btn3-y btn3-lg" id="appInstallBtn">{!! $I('smartphone', 20) !!} Installer l'application</button><span class="on-deep-2" style="font-weight:700">{{ request()->getHost() }}</span></div>
            </div>
            <div class="app3-vis" aria-hidden="true">
                <div class="app3-disc"></div>
                <div class="phone3d app3-phone">
                    <div class="app3-scr">
                        <div class="notif3"><span><img src="/images/shopio-logo-192.png" alt=""></span><div><b>Shopio · maintenant</b><small>Votre commande est en route.</small></div></div>
                        <b style="font-size:18px;margin-top:30px">Bienvenue</b>
                        <small style="color:#62627C;margin-top:-8px">Que voulez-vous faire aujourd'hui ?</small>
                        <i class="app3-search"></i>
                        <div class="app3-tiles"><i></i><i style="background:#FFF4DB"></i><i style="background:#F1F4FA"></i><i></i></div>
                        <div class="app3-tabs"><i class="on"></i><i></i><i></i><i></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═════════════ 06 · APPEL FINAL ═════════════ --}}
<section class="rel3" aria-labelledby="cta3Title">
    <span class="tab3 tab3-y" aria-hidden="true">06</span>
    <div class="w3 sec3">
        <div class="cta3 cta3d rv3">
            <div class="stack3" style="flex:1 1 460px">
                <h2 class="h2-3" id="cta3Title" style="font-size:42px">Rejoignez les vendeurs qui réussissent avec <span class="blue3">Shopio.</span></h2>
                <p class="p3">Créez votre boutique en quelques minutes et commencez à vendre dès aujourd'hui.</p>
                <div class="h3-cta" style="align-items:center"><a class="btn3 btn3-p btn3-lg" href="{{ route('register') }}">Commencer maintenant →</a><span style="font-weight:700;color:#62627C">{{ request()->getHost() }}</span></div>
            </div>
            <div class="cta3-art" aria-hidden="true">
                <svg width="300" height="220" viewBox="0 0 300 220" style="position:absolute;inset:0"><path class="chartline3" pathLength="1" d="M10 200 C 80 190, 110 150, 150 140 S 230 90, 280 20" fill="none" stroke="#F59E0B" stroke-width="6" stroke-linecap="round"/><path d="M262 18 L284 16 L280 38" fill="none" stroke="#F59E0B" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <div class="cta3-shadow"></div>
                <div class="cta3-bagwrap"><div class="bag3d">
                    <svg width="70" height="44" viewBox="0 0 70 44" style="position:absolute;left:30px;top:-38px"><path d="M8 44V26a27 22 0 0 1 54 0v18" fill="none" stroke="#1260C9" stroke-width="7" stroke-linecap="round"/></svg>
                    <div class="bag3-top"></div><div class="bag3-side"></div>
                    <div class="bag3-front"><svg width="70" height="40" viewBox="0 0 32 18"><path d="M8 4c2.4 3.6 5 5.4 8 5.4s5.6-1.8 8-5.4" fill="none" stroke="#F59E0B" stroke-width="3" stroke-linecap="round"/></svg></div>
                </div></div>
                <span class="cta3-dot" style="left:20px;top:60px;background:#EAF2FF;color:#1668D8">{!! $I('user', 24) !!}</span>
                <span class="cta3-dot" style="right:10px;bottom:10px;background:#FFF4DB;color:#8A5300">{!! $I('cart', 24) !!}</span>
            </div>
        </div>
    </div>
</section>
</div>
