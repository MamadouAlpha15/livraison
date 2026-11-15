{{-- Section principale Produit : galerie (miniatures, empilée ou carrousel) et colonne des blocs d'achat --}}
@php
    $photos = $ctx->photos();
    $layout = $settings['gallery_layout'];

    // Moteur d'offres de cette section : les blocs (offres, quantité, complémentaires, formulaire) le lisent
    $checkout = \App\PageBuilder\Commerce\Checkout::fromBlocks($blocks, $ctx->product);
    $ctx->checkout = $checkout;
    $variants = $ctx->variants();
    $firstVariant = $variants->first(fn ($v) => $v->stock === null || $v->stock > 0) ?? $variants->first();

    // Données de calcul pour le navigateur (affichage seulement : le serveur recalcule tout à la commande)
    $pbData = [
        'url'      => route('client.products.page-order', $ctx->product),
        'name'     => $ctx->product->name,
        'unit'     => $checkout->unitPrice(),
        'variants' => $variants->map(fn ($v) => ['id' => $v->id, 'unit' => $checkout->unitPrice($v), 'stock' => $v->stock])->values(),
        'tiers'    => $checkout->offers ? collect($checkout->tiers($checkout->unitPrice()))->map(fn ($t) => [
            'qty' => $t['qty'], 'label' => $t['label'],
            'discount' => $checkout->offers["tier{$t['index']}_discount"], 'value' => (int) $checkout->offers["tier{$t['index']}_value"],
        ])->all() : null,
        'upsells'  => $checkout->upsellProducts()->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'price' => $checkout->upsellPrice($p)])->values(),
        'fees'     => $ctx->deliveryFees(),
        'maxPct'   => \App\PageBuilder\Commerce\Checkout::MAX_PERCENT,
    ];
@endphp

@once
@push('pb-styles')
<style data-pb>
.pb-mp { display: grid; gap: 20px; }
.pb-mp > * { min-width: 0; }
@media (min-width: 900px) {
    .pb-mp { grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr); gap: 48px; align-items: start; }
    .pb-mp--right .pb-mp-gallery { order: 2; }
    .pb-mp--sticky .pb-mp-info { position: sticky; top: 20px; }
}
.pb-mp-info { display: flex; flex-direction: column; gap: 14px; }
.pb-mp-frame { aspect-ratio: 1 / 1; border-radius: 18px; overflow: hidden; background: var(--soft); border: 1px solid var(--cs-line); }
.pb-mp-frame img { width: 100%; height: 100%; object-fit: cover; }
.pb-mp-empty { display: flex; align-items: center; justify-content: center; text-align: center; padding: 24px; color: var(--muted); font-size: 14px; font-weight: 600; }
.pb-mp-thumbs { display: flex; gap: 10px; margin-top: 10px; overflow-x: auto; scrollbar-width: none; }
.pb-mp-thumbs::-webkit-scrollbar { display: none; }
.pb-mp-thumb { flex: none; width: 68px; height: 68px; border-radius: 12px; overflow: hidden; padding: 0; border: 2px solid transparent; background: var(--soft); cursor: pointer; }
.pb-mp-thumb[aria-current="true"] { border-color: var(--cs-button); }
.pb-mp-thumb:focus-visible { outline: 3px solid var(--cs-text); outline-offset: 2px; }
.pb-mp-thumb img { width: 100%; height: 100%; object-fit: cover; }
.pb-mp-stack { display: grid; gap: 10px; }
@media (max-width: 899px) {
    .pb-mp-stack { grid-auto-flow: column; grid-auto-columns: 86%; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; }
    .pb-mp-stack > * { scroll-snap-align: start; }
}
.pb-mp-slider { display: grid; grid-auto-flow: column; grid-auto-columns: 100%; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; border-radius: 18px; }
.pb-mp-slider::-webkit-scrollbar, .pb-mp-stack::-webkit-scrollbar { display: none; }
.pb-mp-slider > * { scroll-snap-align: start; border-radius: 0; border: 0; }
.pb-mp-count { margin-top: 8px; font-size: 13px; color: var(--cs-muted); font-weight: 600; }

/* Champs et boutons des blocs d'achat */
.pb-lbl { display: block; font-size: 14px; font-weight: 700; margin-bottom: 8px; }
.pb-input, .pb-select, .pb-textarea { width: 100%; min-height: 48px; border: 1px solid var(--cs-line); border-radius: 12px; padding: 12px 14px; font: inherit; font-size: 16px; color: #16162A; background: #FFFFFF; }
.pb-textarea { min-height: 88px; resize: vertical; }
.pb-input:focus, .pb-select:focus, .pb-textarea:focus { outline: none; border-color: var(--cs-button); box-shadow: 0 0 0 3px color-mix(in srgb, var(--cs-button) 25%, transparent); }
.pb-input[aria-invalid="true"], .pb-select[aria-invalid="true"], .pb-textarea[aria-invalid="true"] { border-color: #C2410C; }
.pb-err { display: block; color: #C2410C; font-size: 13px; font-weight: 700; margin-top: 6px; }
.pb-err:empty { display: none; }
.pb-btn { width: 100%; min-height: 56px; border: 0; border-radius: 14px; background: var(--cs-button); color: var(--cs-on-button); font: inherit; font-size: 17px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; gap: 10px; cursor: pointer; text-decoration: none; padding: 0 18px; text-align: center; }
.pb-btn:hover:not(:disabled) { filter: brightness(1.06); }
.pb-btn:disabled { opacity: .65; cursor: progress; }
.pb-btn--ghost { background: transparent; color: var(--cs-text); border: 1px solid var(--cs-line); font-size: 15px; min-height: 48px; }
.pb-btn:focus-visible, .pb-choice:focus-within, .pb-step button:focus-visible { outline: 3px solid var(--cs-text); outline-offset: 2px; }
.pb-choices { display: flex; flex-wrap: wrap; gap: 8px; }
.pb-choice { position: relative; }
.pb-choice input { position: absolute; opacity: 0; inset: 0; margin: 0; cursor: pointer; }
.pb-choice span { display: inline-flex; align-items: center; gap: 6px; min-height: 44px; padding: 10px 14px; border: 1.5px solid var(--cs-line); border-radius: 12px; font-weight: 700; font-size: 14px; background: var(--cs-bg); }
.pb-choice input:checked + span { border-color: var(--cs-button); box-shadow: inset 0 0 0 1px var(--cs-button); }
.pb-choice input:disabled + span { opacity: .45; text-decoration: line-through; }
.pb-choice small { color: var(--cs-muted); font-weight: 600; }
.pb-edit-note { margin: 0; padding: 10px 12px; border: 1px dashed #9DB6E0; border-radius: 10px; font-size: 13px; color: #45455E; background: #F6F9FF; }
</style>
@endpush
@endonce

<div class="pb-mp {{ $settings['gallery_side'] === 'right' ? 'pb-mp--right' : '' }} {{ $settings['sticky_info'] ? 'pb-mp--sticky' : '' }}">
    <div class="pb-mp-gallery" data-pb-gallery>
        @if (!$photos)
            <div class="pb-mp-frame pb-mp-empty">Ajoute des photos à ton produit pour les voir ici.</div>
        @elseif ($layout === 'stacked')
            <div class="pb-mp-stack">
                @foreach ($photos as $i => $p)
                    <div class="pb-mp-frame"><img src="{{ $p['medium'] }}" alt="{{ $ctx->product->name }}{{ $i ? ' · photo ' . ($i + 1) : '' }}" @if ($i) loading="lazy" @endif></div>
                @endforeach
            </div>
        @elseif ($layout === 'slider')
            <div class="pb-mp-slider" tabindex="0" aria-label="Photos du produit">
                @foreach ($photos as $i => $p)
                    <div class="pb-mp-frame"><img src="{{ $p['medium'] }}" alt="{{ $ctx->product->name }}{{ $i ? ' · photo ' . ($i + 1) : '' }}" @if ($i) loading="lazy" @endif></div>
                @endforeach
            </div>
            @if (count($photos) > 1)
                <p class="pb-mp-count pb-num">{{ count($photos) }} photos · fais glisser pour les voir</p>
            @endif
        @else
            <div class="pb-mp-frame"><img data-pb-main-img src="{{ $photos[0]['medium'] }}" alt="{{ $ctx->product->name }}"></div>
            @if (count($photos) > 1)
                <div class="pb-mp-thumbs">
                    @foreach ($photos as $i => $p)
                        <button type="button" class="pb-mp-thumb" data-pb-thumb data-src="{{ $p['medium'] }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}" aria-label="Voir la photo {{ $i + 1 }}">
                            <img src="{{ $p['thumb'] }}" alt="" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    {{-- Tous les choix (option, offre, quantité, complémentaires, champs, coordonnées) partent avec un seul formulaire --}}
    <form class="pb-mp-info" data-pb-checkout novalidate>
        <script type="application/json" data-pb-data>@json($pbData)</script>
        @foreach ($blocks as $b)
            @include('page-builder._block', ['b' => $b])
        @endforeach
    </form>
</div>
