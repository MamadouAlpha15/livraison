{{-- Page produit · section « why » (variables de client/product-page) --}}
@php use App\Support\IconLibrary as Ico; use App\Support\Money; use App\Support\PageModels; @endphp
                @if(filled($intro) || $points)
                <section class="pp-sec pp-why">
                    <h2 class="pp-h2">Pourquoi tu vas l'adorer</h2>
                    @if(filled($intro))<p class="pp-text">{{ $intro }}</p>@endif
                    @if($points)
                    <ul class="pp-points">@foreach($points as $pt)<li><span>{!! Ico::svg('check', '', 13) !!}</span><span>{{ $pt }}</span></li>@endforeach</ul>
                    @endif
                </section>
                @endif
