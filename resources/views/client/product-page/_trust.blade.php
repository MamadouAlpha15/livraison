{{-- Page produit · section « trust » (variables de client/product-page) --}}
@php use App\Support\IconLibrary as Ico; use App\Support\Money; use App\Support\PageModels; @endphp
                @if($badges)
                <div class="pp-trust">
                    @foreach($badges as $b)<div>{!! Ico::svg($b['icon'], '', 22) !!}<span>{{ $b['label'] }}</span></div>@endforeach
                </div>
                @endif
