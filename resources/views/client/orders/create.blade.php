@extends('layouts.app')

@push('styles')
<style>
  .product-thumb {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: .5rem;
  }
  @media (max-width: 767.98px) {
    .product-thumb {
      width: 48px;
      height: 48px;
    }
  }
</style>
@endpush

@section('content')
<div class="container">
    <h2 class="mb-4">🛒 Nouvelle commande</h2>

    <form id="orderForm" method="POST" action="{{ route('client.orders.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Boutique --}}
        <div class="mb-3">
            <label for="shop_id" class="form-label">🏪 Sélectionner une boutique</label>
            <select name="shop_id" id="shop_id" class="form-select" required>
                <option value="">-- Choisir une boutique --</option>
                @foreach($shops as $shop)
                    <option value="{{ $shop->id }}"
                            data-type="{{ strtolower($shop->type ?? 'boutique') }}"
                            {{ (isset($selectedShopId) && (int)$selectedShopId === $shop->id) ? 'selected' : '' }}>
                        {{ $shop->name }} @if(!empty($shop->type)) — {{ ucfirst($shop->type) }} @endif
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Produits (non pharmacie) --}}
        <div class="mb-3" id="productGroup" style="display:none;">
            <label for="product_id" class="form-label">🍔 Choisir un produit</label>
            <select name="product_id" id="product_id" class="form-select">
                <option value="">-- Choisir un produit --</option>
            </select>
        </div>

        {{-- Aperçu produit (miniature + infos) --}}
        <div class="mb-3" id="productPreview" style="display:none;">
            <div class="card shadow-sm p-2">
                <div class="d-flex align-items-center">
                    <img id="previewImage" src=""
                         class="product-thumb me-3 border d-none"
                         alt="aperçu produit">
                    <div id="previewNoImg"
                         class="bg-light d-flex align-items-center justify-content-center product-thumb me-3">
                        <span class="text-muted small">Pas d’image</span>
                    </div>
                    <div>
                        <h6 id="previewName" class="mb-1"></h6>
                        <div class="text-muted">💰 <span id="previewPrice"></span> GNF</div>
                        <small id="previewDesc" class="text-muted"></small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quantité (non pharmacie) --}}
        <div class="mb-3" id="quantityGroup" style="display:none;max-width:220px;">
            <label for="quantity" class="form-label">🔢 Quantité</label>
            <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1">
        </div>

        {{-- Montant total --}}
        <div class="mb-3" id="totalGroup" style="max-width:260px;">
            <label for="total" class="form-label">💰 Montant total (GNF)</label>
            <input type="number" name="total" id="total" class="form-control" readonly>
            <small class="text-muted" id="totalHelp" style="display:none;">Pour les pharmacies, saisissez le total.</small>
        </div>

        {{-- Ordonnance (pharmacie) --}}
        <div class="mb-3" id="ordonnanceGroup" style="display:none;">
            <label for="ordonnance" class="form-label">📎 Joindre une ordonnance (PDF/JPG/PNG)</label>
            <input type="file" name="ordonnance" id="ordonnance" class="form-control" accept="image/*,.pdf">
            <small class="text-muted">Requis uniquement pour les pharmacies</small>
        </div>

        <div class="alert alert-info">💵 Paiement : <strong>Cash à la livraison</strong></div>

        <button type="submit" class="btn btn-success w-100">✅ Valider ma commande</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const allProducts = @json($allProducts);

    const routeStore         = "{{ route('client.orders.store') }}";
    const routeStoreProduct  = "{{ route('client.orders.storeProduct') }}";

    const form            = document.getElementById('orderForm');
    const shopSelect      = document.getElementById('shop_id');
    const productGroup    = document.getElementById('productGroup');
    const productSelect   = document.getElementById('product_id');
    const productPreview  = document.getElementById('productPreview');
    const previewImage    = document.getElementById('previewImage');
    const previewNoImg    = document.getElementById('previewNoImg');
    const previewName     = document.getElementById('previewName');
    const previewPrice    = document.getElementById('previewPrice');
    const previewDesc     = document.getElementById('previewDesc');

    const quantityGroup   = document.getElementById('quantityGroup');
    const quantityInput   = document.getElementById('quantity');
    const totalInput      = document.getElementById('total');
    const totalHelp       = document.getElementById('totalHelp');
    const ordonnanceGroup = document.getElementById('ordonnanceGroup');

    function formatPrice(n) {
        try { return Number(n).toLocaleString('fr-FR'); } catch { return n; }
    }

    function setFormForPharmacy() {
        form.action = routeStore;
        ordonnanceGroup.style.display = 'block';
        productGroup.style.display    = 'none';
        productPreview.style.display  = 'none';
        quantityGroup.style.display   = 'none';

        totalInput.value = '';
        totalInput.readOnly = false;
        totalHelp.style.display = 'inline';
    }

    function setFormForShop() {
        form.action = routeStoreProduct;
        ordonnanceGroup.style.display = 'none';
        productGroup.style.display    = 'block';
        quantityGroup.style.display   = 'none';
        productPreview.style.display  = 'none';

        totalInput.value = '';
        totalInput.readOnly = true;
        totalHelp.style.display = 'none';

        fillProducts();
    }

    function onShopChange() {
        const type = shopSelect.options[shopSelect.selectedIndex]?.dataset.type || '';
        if (type === 'pharmacie') setFormForPharmacy();
        else setFormForShop();
    }

    function fillProducts() {
        productSelect.innerHTML = `<option value="">-- Choisir un produit --</option>`;
        const shopId = shopSelect.value;
        const items = allProducts[shopId] || [];
        items.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = `${p.name} — ${formatPrice(p.price)} GNF`;
            opt.dataset.name = p.name;
            opt.dataset.price = p.price;
            opt.dataset.imageUrl = p.image_url || '';
            opt.dataset.desc = p.description || '';
            productSelect.appendChild(opt);
        });
    }

    function showPreview(opt) {
        previewName.textContent  = opt.dataset.name || '';
        previewPrice.textContent = formatPrice(opt.dataset.price || 0);
        previewDesc.textContent  = opt.dataset.desc || '';

        const url = opt.dataset.imageUrl || '';
        if (url) {
            previewImage.src = url;
            previewImage.classList.remove('d-none');
            previewNoImg.style.display = 'none';
        } else {
            previewImage.src = '';
            previewImage.classList.add('d-none');
            previewNoImg.style.display = '';
        }

        productPreview.style.display = 'block';
        quantityGroup.style.display  = 'block';
        if (!quantityInput.value || Number(quantityInput.value) < 1) quantityInput.value = 1;
        updateTotal();
    }

    function hidePreview() {
        productPreview.style.display = 'none';
        quantityGroup.style.display  = 'none';
        totalInput.value = '';
    }

    function updateTotal() {
        const opt = productSelect.options[productSelect.selectedIndex];
        const price = opt ? parseFloat(opt.dataset.price || 0) : 0;
        const qty = parseInt(quantityInput.value || '1', 10) || 1;
        totalInput.value = Math.round(price * qty);
    }

    shopSelect.addEventListener('change', onShopChange);
    productSelect.addEventListener('change', function () {
        const opt = productSelect.options[productSelect.selectedIndex];
        if (opt && opt.value) showPreview(opt); else hidePreview();
    });
    quantityInput.addEventListener('input', updateTotal);

    (function init() {
        const initShop    = "{{ $selectedShopId ?? '' }}";
        const initProduct = "{{ $selectedProductId ?? '' }}";
        if (initShop) shopSelect.value = initShop;
        onShopChange();
        if (initProduct) {
            productSelect.value = initProduct;
            const opt = productSelect.options[productSelect.selectedIndex];
            if (opt && opt.value) showPreview(opt);
        }
    })();
});
</script>
@endpush
