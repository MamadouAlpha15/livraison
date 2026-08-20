{{--
    resources/views/client/cart/index.blade.php
    Route : GET /client/cart → Client\CartController@index
    Panier multi-boutiques : $groups = Collection<{shop, items, subtotal}>, $grandTotal, $cartCount
--}}
@extends('layouts.app')

@section('title', 'Mon panier')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    --orange:    #6366f1;
    --orange-dk: #4f46e5;
    --orange-lt: #e0e7ff;
    --orange-bd: #c7d2fe;
    --red:       #ef4444;
    --red-lt:    #fef2f2;
    --text:      #0f172a;
    --text-2:    #475569;
    --muted:     #94a3b8;
    --border:    #e2e8f0;
    --surface:   #ffffff;
    --bg:        #f8f9fc;
    --font:      system-ui, -apple-system, 'Segoe UI', sans-serif;
    --mono:      'JetBrains Mono', 'Fira Code', monospace;
    --r:         14px;
    --r-sm:      9px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.06);
    --shadow:    0 4px 20px rgba(0,0,0,.08);
}

html, body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; -webkit-font-smoothing: antialiased; }

.cart-page { max-width: 960px; margin: 0 auto; padding: 28px 16px 100px; }

.top-bar {
    background: linear-gradient(135deg, var(--orange) 0%, var(--orange-dk) 60%, #3730a3 100%);
    margin: -28px -16px 24px; padding: 0 20px; height: 62px;
    display: flex; align-items: center; gap: 14px; box-shadow: 0 4px 18px rgba(99,102,241,.35);
}
.btn-back { display: flex; align-items: center; gap: 6px; color: #fff; text-decoration: none; font-size: 13px; font-weight: 600; opacity: .9; flex-shrink: 0; }
.btn-back:hover { opacity: 1; }
.btn-back svg { width: 18px; height: 18px; }
.top-bar-title { flex: 1; color: #fff; }
.top-bar-title h1 { font-size: 18px; font-weight: 800; margin: 0; }
.top-bar-title p { font-size: 12px; margin: 2px 0 0; opacity: .85; }
.top-bar-ico { font-size: 26px; }

.c-flash { display: flex; align-items: center; gap: 8px; padding: 12px 16px; border-radius: var(--r-sm); font-size: 13px; font-weight: 600; margin-bottom: 18px; }
.c-flash-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.c-flash-error   { background: var(--red-lt); color: #991b1b; border: 1px solid #fecaca; }

/* ── Vide ── */
.cart-empty { text-align: center; padding: 70px 20px; background: var(--surface); border-radius: var(--r); border: 1px dashed var(--border); }
.cart-empty-ico { font-size: 48px; opacity: .35; margin-bottom: 12px; }
.cart-empty-title { font-size: 17px; font-weight: 800; margin-bottom: 6px; }
.cart-empty-sub { font-size: 13px; color: var(--muted); margin-bottom: 18px; }
.cart-empty-btn { display: inline-flex; align-items: center; gap: 6px; padding: 11px 22px; border-radius: 30px; background: var(--orange); color: #fff; font-size: 13px; font-weight: 700; text-decoration: none; }

/* ── Groupe par boutique ── */
.shop-group { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); margin-bottom: 16px; overflow: hidden; box-shadow: var(--shadow-sm); }
.shop-group-hd { display: flex; align-items: center; gap: 8px; padding: 12px 16px; background: var(--orange-lt); border-bottom: 1px solid var(--border); font-size: 13px; font-weight: 800; color: var(--orange-dk); }

.cart-line { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--border); }
.cart-line:last-child { border-bottom: none; }
.cart-line-img { width: 60px; height: 60px; border-radius: 10px; background: var(--bg); flex-shrink: 0; overflow: hidden; display: flex; align-items: center; justify-content: center; }
.cart-line-img img { width: 100%; height: 100%; object-fit: cover; }
.cart-line-ph { font-size: 22px; opacity: .3; }
.cart-line-info { flex: 1; min-width: 0; }
.cart-line-name { font-size: 13.5px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cart-line-variant { font-size: 11.5px; color: var(--muted); margin-top: 1px; }
.cart-line-price { font-size: 12.5px; font-weight: 700; color: var(--orange-dk); font-family: var(--mono); margin-top: 3px; }

.qty-stepper { display: flex; align-items: center; gap: 0; border: 1.5px solid var(--border); border-radius: 30px; overflow: hidden; flex-shrink: 0; }
.qty-btn { width: 28px; height: 28px; border: none; background: var(--bg); color: var(--text); font-size: 15px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.qty-btn:hover { background: var(--orange-lt); color: var(--orange-dk); }
.qty-val { width: 30px; text-align: center; font-size: 13px; font-weight: 700; font-family: var(--mono); }

.cart-line-total { font-size: 13.5px; font-weight: 800; font-family: var(--mono); color: var(--text); min-width: 78px; text-align: right; flex-shrink: 0; }
.cart-line-remove { background: none; border: none; color: var(--muted); cursor: pointer; padding: 6px; flex-shrink: 0; display: flex; }
.cart-line-remove:hover { color: var(--red); }
.cart-line-remove svg { width: 17px; height: 17px; }

.shop-group-subtotal { display: flex; justify-content: flex-end; gap: 8px; padding: 10px 16px; font-size: 12.5px; color: var(--text-2); background: var(--bg); }
.shop-group-subtotal strong { color: var(--text); font-family: var(--mono); }

/* ── Livraison + total ── */
.checkout-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); padding: 20px; box-shadow: var(--shadow); margin-top: 20px; }
.checkout-card h3 { font-size: 15px; font-weight: 800; margin: 0 0 14px; }
.field { margin-bottom: 12px; }
.field label { display: block; font-size: 12px; font-weight: 700; color: var(--text-2); margin-bottom: 5px; }
.field input { width: 100%; padding: 11px 14px; border: 1.5px solid var(--border); border-radius: var(--r-sm); font-size: 13.5px; font-family: var(--font); outline: none; transition: border-color .15s; }
.field input:focus { border-color: var(--orange); box-shadow: 0 0 0 3px var(--orange-lt); }
.field .err { color: var(--red); font-size: 11.5px; margin-top: 4px; font-weight: 600; }

.grand-total-row { display: flex; justify-content: space-between; align-items: center; padding: 14px 0; border-top: 1px dashed var(--border); margin-top: 6px; }
.grand-total-lbl { font-size: 14px; font-weight: 700; }
.grand-total-val { font-size: 20px; font-weight: 900; font-family: var(--mono); color: var(--orange-dk); }

.btn-checkout { width: 100%; padding: 14px; border: none; border-radius: 30px; background: linear-gradient(135deg, var(--orange), var(--orange-dk)); color: #fff; font-size: 14.5px; font-weight: 800; cursor: pointer; box-shadow: 0 6px 20px rgba(99,102,241,.35); transition: transform .15s; }
.btn-checkout:hover { transform: translateY(-1px); }
.btn-checkout:disabled { opacity: .6; cursor: not-allowed; transform: none; }

@media (max-width: 600px) {
    .cart-line-img { width: 50px; height: 50px; }
    .cart-line-total { min-width: 60px; font-size: 12px; }
    .qty-btn { width: 25px; height: 25px; }
}
</style>
@endpush

@section('content')
<div class="cart-page">

    <div class="top-bar">
        <a href="{{ route('client.dashboard') }}" class="btn-back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Retour
        </a>
        <div class="top-bar-title">
            <h1>Mon panier</h1>
            <p>{{ $cartCount }} article{{ $cartCount > 1 ? 's' : '' }}</p>
        </div>
        <div class="top-bar-ico">🛒</div>
    </div>

    @if(session('success'))
    <div class="c-flash c-flash-success">✓ {{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="c-flash c-flash-error">✕ {{ $errors->first() }}</div>
    @endif

    @if($groups->isEmpty())
    <div class="cart-empty">
        <div class="cart-empty-ico">🛒</div>
        <div class="cart-empty-title">Votre panier est vide</div>
        <div class="cart-empty-sub">Ajoutez des produits pour les retrouver ici.</div>
        <a href="{{ route('client.dashboard') }}#catalogue" class="cart-empty-btn">Découvrir des produits</a>
    </div>
    @else

    <form id="checkoutForm" method="POST" action="{{ route('client.cart.checkout') }}">
        @csrf

        <div id="cartGroups">
        @foreach($groups as $group)
        <div class="shop-group" data-shop-id="{{ $group['shop']->id }}">
            <div class="shop-group-hd">🏪 {{ $group['shop']->name }}</div>
            @foreach($group['items'] as $item)
            <div class="cart-line" data-item-id="{{ $item->id }}" data-unit-price="{{ $item->unit_price }}">
                <div class="cart-line-img">
                    @if($item->product->image)
                        <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}">
                    @else
                        <span class="cart-line-ph">📦</span>
                    @endif
                </div>
                <div class="cart-line-info">
                    <div class="cart-line-name">{{ $item->product->name }}</div>
                    @if($item->variant)<div class="cart-line-variant">{{ $item->variant->name }}</div>@endif
                    <div class="cart-line-price">{{ number_format($item->unit_price, 0, ',', ' ') }} GNF / unité</div>
                </div>
                <div class="qty-stepper">
                    <button type="button" class="qty-btn" onclick="cartChangeQty({{ $item->id }}, -1)">−</button>
                    <span class="qty-val" id="qtyVal{{ $item->id }}">{{ $item->quantity }}</span>
                    <button type="button" class="qty-btn" onclick="cartChangeQty({{ $item->id }}, 1)">+</button>
                </div>
                <div class="cart-line-total" id="lineTotal{{ $item->id }}">{{ number_format($item->subtotal, 0, ',', ' ') }}</div>
                <button type="button" class="cart-line-remove" onclick="cartRemoveItem({{ $item->id }})" title="Retirer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                </button>
            </div>
            @endforeach
            <div class="shop-group-subtotal">Sous-total boutique : <strong id="shopSubtotal{{ $group['shop']->id }}">{{ number_format($group['subtotal'], 0, ',', ' ') }} GNF</strong></div>
        </div>
        @endforeach
        </div>

        <div class="checkout-card">
            <h3>📍 Livraison</h3>
            <div class="field">
                <label for="delivery_destination">Adresse de livraison</label>
                <input type="text" name="delivery_destination" id="delivery_destination" value="{{ old('delivery_destination', auth()->user()->address ?? '') }}" placeholder="Quartier, rue, point de repère…" required>
                @error('delivery_destination')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="client_phone">Numéro de téléphone</label>
                <input type="tel" name="client_phone" id="client_phone" value="{{ old('client_phone', auth()->user()->phone ?? '') }}" placeholder="622 00 00 00" required>
                @error('client_phone')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="grand-total-row">
                <span class="grand-total-lbl">Total à payer</span>
                <span class="grand-total-val" id="grandTotal">{{ number_format($grandTotal, 0, ',', ' ') }} GNF</span>
            </div>

            <button type="submit" class="btn-checkout" id="btnCheckout">Valider {{ $groups->count() > 1 ? 'les ' . $groups->count() . ' commandes' : 'la commande' }} →</button>
        </div>
    </form>
    @endif
</div>

<script>
const _csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const _cartUpdateBase = '{{ url("/client/cart") }}';

function _formatGNF(n) { return Math.round(n).toLocaleString('fr-FR').replace(/,/g, ' '); }

function _updateNavBadge(count) {
    const badge = document.getElementById('navCartBadge');
    if (badge) {
        badge.textContent = count > 0 ? count : '';
        badge.classList.toggle('show', count > 0);
    }
}

function _recalcGrandTotal() {
    let total = 0;
    document.querySelectorAll('.cart-line').forEach(line => {
        const t = document.getElementById('lineTotal' + line.dataset.itemId);
        if (t) total += parseInt(t.textContent.replace(/\s/g, ''), 10) || 0;
    });
    const el = document.getElementById('grandTotal');
    if (el) el.textContent = _formatGNF(total) + ' GNF';
}

function cartChangeQty(itemId, delta) {
    const qtyEl = document.getElementById('qtyVal' + itemId);
    if (!qtyEl) return;
    const newQty = Math.max(1, parseInt(qtyEl.textContent, 10) + delta);

    fetch(`${_cartUpdateBase}/${itemId}/quantity`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ quantity: newQty })
    })
    .then(r => r.json())
    .then(data => {
        qtyEl.textContent = data.quantity;
        const lineTotalEl = document.getElementById('lineTotal' + itemId);
        if (lineTotalEl) lineTotalEl.textContent = _formatGNF(data.lineTotal);

        const line = document.querySelector(`.cart-line[data-item-id="${itemId}"]`);
        const group = line?.closest('.shop-group');
        if (group) {
            let shopTotal = 0;
            group.querySelectorAll('.cart-line-total').forEach(el => shopTotal += parseInt(el.textContent.replace(/\s/g,''),10) || 0);
            const shopId = group.dataset.shopId;
            const shopSubEl = document.getElementById('shopSubtotal' + shopId);
            if (shopSubEl) shopSubEl.textContent = _formatGNF(shopTotal) + ' GNF';
        }

        _recalcGrandTotal();
        _updateNavBadge(data.cartCount);
    })
    .catch(() => {});
}

function cartRemoveItem(itemId) {
    fetch(`${_cartUpdateBase}/${itemId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const line = document.querySelector(`.cart-line[data-item-id="${itemId}"]`);
        const group = line?.closest('.shop-group');
        if (line) line.remove();

        if (group && !group.querySelector('.cart-line')) {
            group.remove();
        } else if (group) {
            let shopTotal = 0;
            group.querySelectorAll('.cart-line-total').forEach(el => shopTotal += parseInt(el.textContent.replace(/\s/g,''),10) || 0);
            const shopSubEl = document.getElementById('shopSubtotal' + group.dataset.shopId);
            if (shopSubEl) shopSubEl.textContent = _formatGNF(shopTotal) + ' GNF';
        }

        _recalcGrandTotal();
        _updateNavBadge(data.cartCount);

        if (!document.querySelector('.cart-line')) {
            location.reload();
        }
    })
    .catch(() => {});
}

document.getElementById('checkoutForm')?.addEventListener('submit', function () {
    const btn = document.getElementById('btnCheckout');
    if (btn) { btn.disabled = true; btn.textContent = 'Validation en cours…'; }
});
</script>
@endsection
