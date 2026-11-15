{{-- Page produit · section « steps » (variables de client/product-page) --}}
@php use App\Support\IconLibrary as Ico; use App\Support\Money; use App\Support\PageModels; @endphp
                @if(array_filter($shopPage['steps']))
                <section class="pp-sec">
                    <h2 class="pp-h2">Comment ça se passe</h2>
                    <ol class="pp-steps">@foreach(array_filter($shopPage['steps']) as $step)<li>{{ $step }}</li>@endforeach</ol>
                </section>
                @endif
