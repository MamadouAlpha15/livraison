{{-- Page produit · section « faq » (variables de client/product-page) --}}
@php use App\Support\IconLibrary as Ico; use App\Support\Money; use App\Support\PageModels; @endphp
                @if($faq)
                <section class="pp-sec pp-faq">
                    <h2 class="pp-h2">Questions fréquentes</h2>
                    @foreach($faq as $f)
                    <details><summary>{{ $f['q'] }}</summary><p>{{ $f['a'] }}</p></details>
                    @endforeach
                </section>
                @endif
