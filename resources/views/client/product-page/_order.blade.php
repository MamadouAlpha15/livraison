{{-- Page produit · section « order » (variables de client/product-page) --}}
@php use App\Support\IconLibrary as Ico; use App\Support\Money; use App\Support\PageModels; @endphp
                {{-- Commande --}}
                <section class="pp-order" id="ppOrder" aria-labelledby="ppOrderTitle">
                    @if($unavailable)
                        <h2 id="ppOrderTitle">Bientôt de retour</h2>
                        <p>Ce produit n'est pas disponible pour le moment.</p>
                        @if($user && $user->role === 'client')
                            @php $alert = \App\Models\StockAlert::where('user_id', $user->id)->where('product_id', $product->id)->exists(); @endphp
                            <button type="button" class="pp-cta" id="ppStock" data-url="{{ route('client.products.stock-alert', $product) }}" aria-pressed="{{ $alert ? 'true' : 'false' }}">
                                {!! Ico::svg('bell', '', 18) !!} <span>{{ $alert ? 'Alerte activée : tu seras prévenu' : 'Me prévenir du retour en stock' }}</span></button>
                        @else
                            <a class="pp-cta" href="{{ route('login', ['redirect' => url()->current()]) }}">{!! Ico::svg('bell', '', 18) !!} Me prévenir du retour en stock</a>
                        @endif
                    @else
                    <form id="ppForm" novalidate data-url="{{ route('client.products.order', $product) }}">
                        <div style="display:grid;gap:14px">
                            <h2 id="ppOrderTitle">Commande maintenant</h2>
                            <p style="margin:-8px 0 0;font-size:13px;color:var(--night-text)">Tu paies seulement à la réception du colis.</p>
                            <div class="pp-flash err" id="ppError" role="alert" hidden></div>
                            <input type="hidden" name="offer" id="ppOffer" value="{{ $selected['key'] }}">
                            <input type="hidden" name="variant_id" id="ppVariant" value="">
                            <div class="pp-field">
                                <label for="ppName">Nom complet</label>
                                <input id="ppName" name="name" class="pp-input" autocomplete="name" placeholder="Ton nom" value="{{ $user?->name }}" {{ $user ? '' : 'required' }}>
                                <span class="pp-err" data-err="name"></span>
                            </div>
                            <div class="pp-field">
                                <label for="ppPhone">Téléphone</label>
                                <div class="pp-phone"><span>+224</span><input id="ppPhone" name="phone" type="tel" inputmode="tel" class="pp-input" autocomplete="tel" placeholder="6XX XX XX XX" value="{{ $user?->phone }}" required></div>
                                <span class="pp-err" data-err="phone"></span>
                            </div>
                            <div class="pp-row2">
                                <div class="pp-field">
                                    <label for="ppCommune">Commune</label>
                                    <select id="ppCommune" name="commune" class="pp-select" required>
                                        <option value="">Choisir</option>
                                        @foreach(PageModels::COMMUNES as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                                    </select>
                                    <span class="pp-err" data-err="commune"></span>
                                </div>
                                <div class="pp-field">
                                    <label for="ppQuartier">Quartier</label>
                                    <input id="ppQuartier" name="quartier" class="pp-input" maxlength="120" placeholder="Ex. Kipé">
                                </div>
                            </div>
                            <div class="pp-sum pp-num" aria-live="polite">
                                <div><span id="ppSumLabel">{{ $selected['label'] }}</span><span id="ppSumPrice">{{ Money::gnf($selected['price']) }}</span></div>
                                <div><span id="ppFeeLabel">Livraison</span><span id="ppFee">Choisis ta commune</span></div>
                                <div class="pp-total"><span>Total</span><strong id="ppTotal">{{ Money::gnf($selected['price']) }}</strong></div>
                            </div>
                            <button type="submit" class="pp-cta" id="ppSubmit">{!! Ico::svg('truck', '', 20) !!} Commander · Payer à la livraison</button>
                            @if($showOffer)
                            <a class="pp-ghost" href="{{ $user ? route('client.messages.index', $product) : route('login', ['redirect' => route('client.messages.index', $product)]) }}">{!! Ico::svg('message', '', 18) !!} Faire une offre au vendeur</a>
                            @endif
                        </div>
                    </form>
                    <div class="pp-done" id="ppDone" hidden>
                        <span class="pp-done-ico">{!! Ico::svg('check', '', 28) !!}</span>
                        <h2>Commande envoyée !</h2>
                        <p style="margin:0;color:#C9D6EA">La boutique t'appelle très vite pour confirmer la livraison. Tu paieras <strong id="ppDoneTotal" style="color:#fff"></strong> à la réception.</p>
                        <a class="pp-cta" id="ppTrack" href="#">{!! Ico::svg('map-pin', '', 18) !!} Suivre ma commande</a>
                    </div>
                    @endif
                </section>
