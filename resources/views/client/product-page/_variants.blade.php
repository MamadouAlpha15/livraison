{{-- Page produit · section « variants » (variables de client/product-page) --}}
@php use App\Support\IconLibrary as Ico; use App\Support\Money; use App\Support\PageModels; @endphp
                @if($variants->isNotEmpty())
                <div class="pp-sec">
                    <h2 class="pp-h2">Choisis ton option</h2>
                    <div class="pp-chips" id="ppVariants">
                        <button type="button" class="pp-chip" data-variant="" aria-pressed="true">Sans préférence</button>
                        @foreach($variants as $v)
                        <button type="button" class="pp-chip" data-variant="{{ $v->id }}" data-image="{{ $v->image_url }}" aria-pressed="false" @disabled($v->stock !== null && $v->stock <= 0)>{{ $v->name }}</button>
                        @endforeach
                    </div>
                </div>
                @endif
