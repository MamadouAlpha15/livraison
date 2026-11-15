{{-- Bloc Options du produit : boutons ou liste ; options épuisées désactivées --}}
@if ($variants->isEmpty())
    @if ($ctx->editing)<p class="pb-edit-note">Options du produit : ce produit n'a pas d'options (taille, couleur…).</p>@endif
@else
    <div role="group" aria-labelledby="pb-var-{{ $block['id'] }}">
        <span class="pb-lbl" id="pb-var-{{ $block['id'] }}">{{ $settings['label'] }}</span>
        @if ($settings['style'] === 'dropdown')
            <select class="pb-select" name="variant_id" data-pb-variant aria-labelledby="pb-var-{{ $block['id'] }}">
                @foreach ($variants as $v)
                    <option value="{{ $v->id }}" @selected($v->is($firstVariant)) @disabled($v->stock !== null && $v->stock <= 0)>
                        {{ $v->name }}{{ $v->stock !== null && $v->stock <= 0 ? ' · épuisé' : '' }}
                    </option>
                @endforeach
            </select>
        @else
            <div class="pb-choices">
                @foreach ($variants as $v)
                    <label class="pb-choice">
                        <input type="radio" name="variant_id" value="{{ $v->id }}" data-pb-variant @checked($v->is($firstVariant)) @disabled($v->stock !== null && $v->stock <= 0)>
                        <span>{{ $v->name }}@if ($v->price !== null && (float) $v->price !== $checkout->unitPrice())<small class="pb-num">{{ \App\Support\Money::gnf($v->price) }}</small>@endif</span>
                    </label>
                @endforeach
            </div>
        @endif
    </div>
@endif
