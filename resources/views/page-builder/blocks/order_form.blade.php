{{-- Bloc Formulaire de commande : coordonnées, récapitulatif (calculé par le navigateur, recalculé par le serveur), paiement à la livraison --}}
@php
    use App\Support\IconLibrary as Ico;
    $user = $ctx->user();
    $fees = $ctx->deliveryFees();
    $wa = $settings['show_whatsapp'] ? \App\Support\ProductPage::whatsappLink($ctx->shop, $ctx->product) : null;
    $uid = $block['id'];
    $alerted = $user && $user->role === 'client' && $ctx->unavailable()
        ? \App\Models\StockAlert::where('user_id', $user->id)->where('product_id', $ctx->product->id)->exists() : false;
@endphp
@once
@push('pb-styles')
<style data-pb>
.pb-order { display: grid; gap: 14px; padding: 18px; border-radius: 18px; border: 1px solid var(--cs-line); background: color-mix(in srgb, var(--cs-text) 3%, var(--cs-bg)); }
.pb-order h2 { margin: 0; font-size: 20px; font-weight: 800; }
.pb-order-sub { margin: -8px 0 0; font-size: 14px; color: var(--cs-muted); }
.pb-row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.pb-row2 > * { min-width: 0; }
@media (max-width: 380px) { .pb-row2 { grid-template-columns: 1fr; } }
.pb-phone { display: flex; }
.pb-phone span { display: inline-flex; align-items: center; padding: 0 12px; border: 1px solid var(--cs-line); border-right: 0; border-radius: 12px 0 0 12px; background: color-mix(in srgb, var(--cs-text) 5%, #FFFFFF); color: #16162A; font-weight: 700; }
.pb-phone .pb-input { border-radius: 0 12px 12px 0; }
.pb-sum { display: grid; gap: 6px; padding: 12px 14px; border-radius: 12px; background: var(--cs-bg); border: 1px solid var(--cs-line); font-size: 14px; }
.pb-sum > div, .pb-sum-line { display: flex; justify-content: space-between; gap: 12px; }
.pb-sum > [data-pb-sum-lines] { display: grid; gap: 6px; }
.pb-sum > [data-pb-sum-lines]:empty { display: none; }
.pb-sum > div > span:first-child, .pb-sum-line > span:first-child { color: var(--cs-muted); min-width: 0; overflow-wrap: anywhere; }
.pb-sum .pb-total { padding-top: 8px; border-top: 1px dashed var(--cs-line); font-size: 16px; }
.pb-sum .pb-total span { color: var(--cs-text) !important; font-weight: 800; }
.pb-flash { padding: 12px 14px; border-radius: 12px; background: #FFF4ED; color: #9A3412; font-weight: 700; font-size: 14px; }
.pb-done { display: grid; gap: 12px; justify-items: center; text-align: center; padding: 22px 18px; border-radius: 18px; background: #0B1E3D; color: #FFFFFF; }
.pb-done h2 { margin: 0; font-size: 22px; }
.pb-done p { margin: 0; color: #C9D6EA; }
.pb-done-ico { width: 56px; height: 56px; border-radius: 999px; background: #15803D; display: inline-flex; align-items: center; justify-content: center; }
.pb-soldout { padding: 18px; border-radius: 16px; border: 1px solid var(--cs-line); }
.pb-soldout h2 { margin: 0 0 6px; font-size: 19px; }
.pb-soldout { display: grid; gap: 12px; }
.pb-soldout p { margin: 0; color: var(--cs-muted); }
.pb-soldout h2 { margin: 0; }
.pb-soldout small { color: var(--cs-muted); font-size: 13px; }
.pb-otp, .pb-dup { display: grid; gap: 8px; padding: 14px; border-radius: 12px; border: 1px solid var(--cs-button); background: var(--cs-bg); }
.pb-otp p, .pb-dup p { margin: 0; font-size: 14px; font-weight: 600; }
.pb-otp .pb-input { font-size: 22px; letter-spacing: .3em; text-align: center; font-weight: 800; }
.pb-linkbtn { justify-self: start; border: 0; background: none; padding: 4px 0; color: var(--cs-text); font: inherit; font-size: 14px; font-weight: 700; text-decoration: underline; cursor: pointer; }
</style>
@endpush
@endonce

@if ($ctx->unavailable())
    <div class="pb-soldout" role="status">
        <h2>Bientôt de retour</h2>
        <p>Ce produit n'est pas disponible pour le moment.</p>
        @if (!$ctx->editing && $user && $user->role === 'client')
            <button type="button" class="pb-btn" data-pb-stock="{{ route('client.products.stock-alert', $ctx->product) }}" aria-pressed="{{ $alerted ? 'true' : 'false' }}">
                {!! Ico::svg('bell', '', 20) !!} <span>{{ $alerted ? 'Alerte activée : tu seras prévenu' : 'Me prévenir du retour en stock' }}</span>
            </button>
        @elseif ($ctx->editing || !$user)
            <a class="pb-btn" href="{{ $ctx->editing ? '#' : route('login', ['redirect' => route('client.products.show', $ctx->product)]) }}">{!! Ico::svg('bell', '', 20) !!} Me prévenir du retour en stock</a>
            <small>Connecte-toi ou crée ton compte en 30 secondes : tu reçois un message dès son retour.</small>
        @endif
    </div>
@else
    <div class="pb-order" id="commander" data-pb-order>
        <h2 id="pb-order-title">{{ $settings['heading'] }}</h2>
        @if (filled($settings['subtitle']))<p class="pb-order-sub">{{ $settings['subtitle'] }}</p>@endif
        <div class="pb-flash" data-pb-error role="alert" hidden></div>

        <div>
            <label class="pb-lbl" for="pb-name-{{ $uid }}">Nom complet</label>
            <input id="pb-name-{{ $uid }}" name="name" class="pb-input" autocomplete="name" maxlength="255" placeholder="Ton prénom et ton nom" value="{{ $user?->name }}" @required(!$user)>
            <span class="pb-err" data-err="name"></span>
        </div>
        <div>
            <label class="pb-lbl" for="pb-phone-{{ $uid }}">Téléphone</label>
            <div class="pb-phone"><span>+224</span><input id="pb-phone-{{ $uid }}" name="phone" type="tel" inputmode="tel" class="pb-input" autocomplete="tel" maxlength="30" placeholder="6XX XX XX XX" value="{{ $user?->phone }}" required></div>
            <span class="pb-err" data-err="phone"></span>
        </div>
        <div class="{{ $settings['show_quartier'] ? 'pb-row2' : '' }}">
            <div>
                <label class="pb-lbl" for="pb-commune-{{ $uid }}">Commune</label>
                <select id="pb-commune-{{ $uid }}" name="commune" class="pb-select" required data-pb-commune>
                    <option value="">Choisir</option>
                    @foreach (\App\Support\PageModels::COMMUNES as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                </select>
                <span class="pb-err" data-err="commune"></span>
            </div>
            @if ($settings['show_quartier'])
                <div>
                    <label class="pb-lbl" for="pb-quartier-{{ $uid }}">Quartier</label>
                    <input id="pb-quartier-{{ $uid }}" name="quartier" class="pb-input" maxlength="120" placeholder="Ex. Kipé">
                </div>
            @endif
        </div>

        <div class="pb-sum pb-num" aria-live="polite" data-pb-summary>
            <div data-pb-sum-lines></div>
            <div><span>Livraison</span><span data-pb-fee>{{ $fees ? 'Choisis ta commune' : 'À confirmer par la boutique' }}</span></div>
            <div class="pb-total"><span>Total</span><strong data-pb-total>—</strong></div>
        </div>

        {{-- Même produit déjà commandé avec ce numéro il y a peu : le client confirme --}}
        <div class="pb-dup" data-pb-dup hidden>
            <p data-pb-dup-msg></p>
            <button type="button" class="pb-btn pb-btn--ghost" data-pb-dup-confirm>C'est une nouvelle commande : l'envoyer</button>
        </div>
        <input type="hidden" name="confirm_duplicate" value="0" data-pb-dup-flag>

        {{-- Code SMS (selon le réglage « Confirmer le numéro par code SMS ») --}}
        <div class="pb-otp" data-pb-otp hidden>
            <p data-pb-otp-msg></p>
            <label class="pb-lbl" for="pb-otp-{{ $uid }}" style="margin:0">Code reçu par SMS</label>
            <input id="pb-otp-{{ $uid }}" name="otp" class="pb-input pb-num" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="••••••">
            <span class="pb-err" data-err="otp"></span>
            <button type="button" class="pb-linkbtn" data-pb-otp-resend>Je n'ai rien reçu : renvoyer le code</button>
        </div>
        @if ($ctx->editing && $settings['verify_phone'] !== 'never' && !\App\Support\OrderPhoneVerification::available())
            <p class="pb-edit-note">Code SMS : pas encore actif, aucun fournisseur SMS n'est configuré sur le serveur. Les commandes passent sans code en attendant.</p>
        @endif

        <button type="submit" class="pb-btn" data-pb-submit>{!! Ico::svg('truck', '', 20) !!} <span>{{ $settings['button'] }}</span></button>
        @if ($settings['show_offer_button'])
            <a class="pb-btn pb-btn--ghost" href="{{ $user ? route('client.messages.index', $ctx->product) : route('login', ['redirect' => route('client.messages.index', $ctx->product)]) }}">{!! Ico::svg('message', '', 18) !!} Faire une offre au vendeur</a>
        @endif
        @if ($wa)
            <a class="pb-btn pb-btn--ghost" href="{{ $wa }}" target="_blank" rel="noopener">{!! Ico::svg('phone', '', 18) !!} Une question ? WhatsApp</a>
        @endif
    </div>

    <div class="pb-done" data-pb-done hidden tabindex="-1">
        <span class="pb-done-ico">{!! Ico::svg('check', '', 28) !!}</span>
        <h2>Commande envoyée !</h2>
        <p>La boutique t'appelle très vite pour confirmer la livraison. Tu paieras <strong data-pb-done-total style="color:#fff"></strong> à la réception.</p>
        <a class="pb-btn" data-pb-track href="#" style="background:#FFFFFF;color:#0B1E3D">{!! Ico::svg('map-pin', '', 18) !!} Suivre ma commande</a>
    </div>
@endif
