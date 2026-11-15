{{-- Page produit · section « offers » (variables de client/product-page) --}}
@php use App\Support\IconLibrary as Ico; use App\Support\Money; use App\Support\PageModels; @endphp
                @if(count($offers) > 1)
                <div class="pp-sec">
                    <h2 class="pp-h2">Choisis ton offre</h2>
                    <div class="pp-offers" id="ppOffers">
                        @foreach($offers as $o)
                        <label class="pp-offer">
                            <input type="radio" name="offer_pick" value="{{ $o['key'] }}" @checked($o['key'] === $selected['key'])>
                            <span class="pp-offer-dot" aria-hidden="true"></span>
                            <span class="pp-offer-main">
                                <strong>{{ $o['label'] }}</strong>
                                @if($o['badge'])<span class="pp-offer-badge">{{ $o['badge'] }}</span>@endif
                                @if($o['subtitle'])<span>{{ $o['subtitle'] }}</span>@endif
                            </span>
                            <span class="pp-offer-price pp-num">
                                <strong>{{ Money::gnf($o['price']) }}</strong>
                                @if($o['savings'] > 0)<span>Tu économises {{ Money::gnf($o['savings'], false) }}</span>@endif
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif
