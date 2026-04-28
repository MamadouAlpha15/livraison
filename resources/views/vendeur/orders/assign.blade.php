@extends('layouts.app')
@section('title', 'Assigner un livreur')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
:root {
    --navy:#0f172a; --navy2:#1e1b4b; --brand:#6366f1; --brand-dk:#4f46e5;
    --green:#10b981; --green-lt:#d1fae5; --green-dk:#065f46;
    --blue:#3b82f6; --blue-lt:#dbeafe; --border:#e9edef;
    --muted:#64748b; --text:#0f172a; --bg:#f1f5f9;
    --font:'Segoe UI',sans-serif;
}
*,*::before,*::after{box-sizing:border-box}
body{font-family:var(--font);background:var(--bg);color:var(--text)}

/* HERO */
.as-hero { background:linear-gradient(135deg,var(--navy),var(--navy2)); padding:24px 24px 20px; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
.as-hero-back { display:inline-flex; align-items:center; gap:8px; padding:9px 16px; background:rgba(255,255,255,.12); color:#fff; border:1.5px solid rgba(255,255,255,.2); border-radius:10px; font-size:13px; font-weight:700; text-decoration:none; transition:all .15s; }
.as-hero-back:hover { background:rgba(255,255,255,.22); color:#fff; }
.as-hero-title { font-size:18px; font-weight:800; color:#fff; }
.as-hero-sub   { font-size:12px; color:rgba(255,255,255,.6); margin-top:3px; }

/* LAYOUT */
.as-body { display:grid; grid-template-columns:1fr 400px; gap:20px; padding:20px 24px; }

/* CARD */
.as-card { background:#fff; border-radius:16px; box-shadow:0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
.as-card-hd { padding:14px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px; }
.as-card-hd-title { font-size:14px; font-weight:800; color:var(--text); }

/* DÉTAIL COMMANDE */
.as-order-row { display:flex; align-items:center; gap:12px; padding:12px 18px; border-bottom:1px solid #f8fafc; }
.as-order-row:last-child { border-bottom:none; }
.as-prod-img { width:52px; height:52px; border-radius:10px; object-fit:cover; border:1.5px solid var(--border); flex-shrink:0; }
.as-prod-ph  { width:52px; height:52px; border-radius:10px; background:var(--bg); border:1.5px solid var(--border); display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
.as-prod-name { font-size:13px; font-weight:700; color:var(--text); }
.as-prod-sub  { font-size:11.5px; color:var(--muted); margin-top:2px; }
.as-prod-price{ font-size:13px; font-weight:800; color:var(--navy); font-family:monospace; margin-left:auto; flex-shrink:0; }

/* INFOS CLIENT */
.as-info-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; padding:16px 18px; }
.as-info-lbl  { font-size:10.5px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.4px; margin-bottom:3px; }
.as-info-val  { font-size:13px; font-weight:600; color:var(--text); }

/* TOTAL COMMANDE */
.as-total-bar { background:linear-gradient(135deg,var(--navy),var(--navy2)); padding:14px 18px; display:flex; align-items:center; justify-content:space-between; }
.as-total-lbl { font-size:12px; color:rgba(255,255,255,.7); font-weight:600; }
.as-total-val { font-size:20px; font-weight:900; color:#fff; font-family:monospace; }

/* LIVREURS */
.as-livreur-card {
    padding:16px; margin:0 16px 12px; border-radius:14px;
    border:2px solid var(--border); background:#fff;
    transition:all .15s; cursor:pointer;
}
.as-livreur-card:first-child { margin-top:14px; }
.as-livreur-card:last-child  { margin-bottom:14px; }
.as-livreur-card:hover       { border-color:var(--brand); box-shadow:0 4px 16px rgba(99,102,241,.15); }
.as-livreur-card.selected    { border-color:var(--brand); background:#eef2ff; box-shadow:0 4px 20px rgba(99,102,241,.15); }
.as-livreur-card.assigned    { border-color:var(--green); background:#f0fdf4; cursor:default; }

.as-lv-top { display:flex; align-items:center; gap:12px; }
.as-lv-av  { width:44px; height:44px; border-radius:50%; background:linear-gradient(135deg,var(--navy),var(--navy2)); color:#fff; font-size:14px; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.as-lv-name { font-size:14px; font-weight:700; color:var(--text); }
.as-lv-phone { font-size:12px; color:var(--muted); margin-top:2px; }
.as-lv-status { font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; flex-shrink:0; margin-left:auto; }
.as-lv-status.online  { background:var(--green-lt); color:var(--green-dk); }
.as-lv-status.offline { background:#f3f4f6; color:var(--muted); }

/* PANEL FRAIS (inline sous la carte livreur sélectionné) */
.as-fee-panel { display:none; margin-top:14px; padding-top:14px; border-top:1px dashed var(--border); }
.as-fee-panel.open { display:block; }
.as-fee-label { font-size:12px; font-weight:700; color:var(--muted); margin-bottom:6px; display:block; }
.as-fee-row   { display:flex; gap:10px; align-items:stretch; }
.as-fee-input { flex:1; padding:10px 14px; border:1.5px solid var(--border); border-radius:10px; font-size:14px; font-weight:600; font-family:monospace; outline:none; transition:border-color .15s; }
.as-fee-input:focus { border-color:var(--brand); box-shadow:0 0 0 3px rgba(99,102,241,.15); }
.as-dest-input { width:100%; padding:10px 14px; border:1.5px solid var(--border); border-radius:10px; font-size:13px; outline:none; font-family:var(--font); margin-top:8px; transition:border-color .15s; }
.as-dest-input:focus { border-color:var(--brand); box-shadow:0 0 0 3px rgba(99,102,241,.15); }
.as-fee-devise { padding:10px 12px; background:var(--bg); border:1.5px solid var(--border); border-radius:10px; font-size:12px; font-weight:700; color:var(--muted); white-space:nowrap; }
.as-assign-btn { width:100%; margin-top:12px; padding:13px; background:linear-gradient(135deg,var(--brand),var(--brand-dk)); color:#fff; border:none; border-radius:12px; font-size:14px; font-weight:800; cursor:pointer; font-family:var(--font); display:flex; align-items:center; justify-content:center; gap:8px; transition:all .2s; box-shadow:0 4px 14px rgba(99,102,241,.35); }
.as-assign-btn:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(99,102,241,.45); }
.as-assign-btn:disabled { opacity:.6; cursor:not-allowed; transform:none; }

/* Badge assigné */
.as-assigned-badge { display:flex; align-items:center; gap:8px; margin-top:10px; padding:10px 14px; background:var(--green-lt); border-radius:10px; font-size:13px; font-weight:700; color:var(--green-dk); }

/* Alerte */
.as-alert { margin:0 16px 12px; padding:14px 16px; border-radius:12px; font-size:13.5px; font-weight:600; }
.as-alert.success { background:var(--green-lt); color:var(--green-dk); border:1px solid #a7f3d0; }

/* RESPONSIVE */
@media(max-width:900px) {
    .as-body { grid-template-columns:1fr; }
}
@media(max-width:640px) {
    .as-hero { padding:16px; }
    .as-body { padding:14px 16px; }
    .as-info-grid { grid-template-columns:1fr; gap:8px; }
}
</style>
@endpush

@section('content')
@php
    $shop   = auth()->user()->shop ?? auth()->user()->assignedShop;
    $devise = $shop?->currency ?? 'GNF';
    $fmt    = fn($n) => number_format($n ?? 0, 0, ',', ' ') . ' ' . $devise;
@endphp

{{-- HERO --}}
<div class="as-hero">
    <div>
        <div class="as-hero-title">🚴 Assigner un livreur</div>
        <div class="as-hero-sub">Commande #{{ $order->id }} · {{ $fmt($order->total) }}</div>
    </div>
    <a href="{{ url()->previous() }}" class="as-hero-back">← Retour</a>
</div>

<div class="as-body">

    {{-- COLONNE GAUCHE : Détail commande --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Produits --}}
        <div class="as-card">
            <div class="as-card-hd">
                <span style="font-size:18px">📦</span>
                <span class="as-card-hd-title">Produits de la commande</span>
            </div>
            @foreach($order->items as $item)
            <div class="as-order-row">
                @if($item->product?->image)
                    <img src="{{ asset('storage/'.$item->product->image) }}" class="as-prod-img" alt="{{ $item->product->name }}">
                @else
                    <div class="as-prod-ph">🛒</div>
                @endif
                <div style="flex:1;min-width:0">
                    <div class="as-prod-name">{{ $item->product->name ?? 'Produit supprimé' }}</div>
                    <div class="as-prod-sub">Qté : {{ $item->quantity }} · PU : {{ $fmt($item->price) }}</div>
                </div>
                <div class="as-prod-price">{{ $fmt($item->quantity * $item->price) }}</div>
            </div>
            @endforeach
            <div class="as-total-bar">
                <span class="as-total-lbl">Total commande</span>
                <span class="as-total-val">{{ $fmt($order->total) }}</span>
            </div>
        </div>

        {{-- Client --}}
        <div class="as-card">
            <div class="as-card-hd">
                <span style="font-size:18px">👤</span>
                <span class="as-card-hd-title">Informations client</span>
            </div>
            <div class="as-info-grid">
                <div>
                    <div class="as-info-lbl">Nom</div>
                    <div class="as-info-val">{{ $order->client->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="as-info-lbl">Téléphone</div>
                    <div class="as-info-val">
                        @if($order->client?->phone)
                            <a href="tel:{{ $order->client->phone }}" style="color:var(--green-dk);text-decoration:none">📞 {{ $order->client->phone }}</a>
                        @else —
                        @endif
                    </div>
                </div>
                <div>
                    <div class="as-info-lbl">Email</div>
                    <div class="as-info-val" style="font-size:12px">{{ $order->client->email ?? '—' }}</div>
                </div>
                <div>
                    <div class="as-info-lbl">Adresse</div>
                    <div class="as-info-val" style="font-size:12px;color:var(--muted)">📍 {{ $order->client?->address ?? 'Non renseignée' }}</div>
                </div>
            </div>
        </div>

    </div>

    {{-- COLONNE DROITE : Onglets Livreur / Entreprise --}}
    <div style="align-self:start;display:flex;flex-direction:column;gap:14px;">

    {{-- Tabs --}}
    <div style="display:flex;gap:0;background:var(--bg);border:1.5px solid var(--border);border-radius:12px;overflow:hidden;">
        <button id="tabLivreur" type="button" onclick="switchTab('livreur')"
            style="flex:1;padding:11px;font-size:13px;font-weight:800;font-family:var(--font);border:none;cursor:pointer;transition:all .15s;background:linear-gradient(135deg,var(--navy),var(--navy2));color:#fff;">
            🚴 Mon livreur
        </button>
        <button id="tabCompany" type="button" onclick="switchTab('company')"
            style="flex:1;padding:11px;font-size:13px;font-weight:800;font-family:var(--font);border:none;cursor:pointer;transition:all .15s;background:transparent;color:var(--muted);">
            🏢 Entreprise externe
        </button>
    </div>

    {{-- PANEL : Livreurs internes --}}
    <div id="panelLivreur" class="as-card">
        <div class="as-card-hd">
            <span style="font-size:18px">🚴</span>
            <span class="as-card-hd-title">Choisir un livreur interne</span>
        </div>

        @if(session('success'))
        <div class="as-alert success">✅ {{ session('success') }}</div>
        @endif

        @forelse($livreurs as $livreur)
        @php
            $parts = explode(' ', $livreur->name);
            $init  = strtoupper(substr($parts[0],0,1)).strtoupper(substr($parts[1]??'X',0,1));
            $isAssigned = $order->livreur_id == $livreur->id;
        @endphp

        <div class="as-livreur-card {{ $isAssigned ? 'assigned' : '' }}" id="lv_{{ $livreur->id }}"
             @if(!$isAssigned) onclick="selectLivreur({{ $livreur->id }})" @endif>

            <div class="as-lv-top">
                <div class="as-lv-av">{{ $init }}</div>
                <div style="flex:1;min-width:0">
                    <div class="as-lv-name">{{ $livreur->name }}</div>
                    <div class="as-lv-phone">{{ $livreur->phone ?? 'Pas de téléphone' }}</div>
                </div>
                <span class="as-lv-status {{ $livreur->is_available ? 'online' : 'offline' }}">
                    {{ $livreur->is_available ? '🟢 En ligne' : '⚫ Hors ligne' }}
                </span>
            </div>

            @if($isAssigned)
                <div class="as-assigned-badge">
                    ✅ Livreur assigné
                    @if($order->delivery_fee)
                        · Frais : {{ $fmt($order->delivery_fee) }}
                        @if($order->delivery_destination) · {{ $order->delivery_destination }} @endif
                    @endif
                </div>
            @else
            {{-- Panel frais de livraison (caché jusqu'à sélection) --}}
            <div class="as-fee-panel" id="feePanel_{{ $livreur->id }}">
                <form class="assign-form" action="{{ route('orders.assign', $order) }}" method="POST"
                      data-livreur-name="{{ e($livreur->name) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="livreur_id" value="{{ $livreur->id }}">

                    <label class="as-fee-label">💰 Frais de livraison pour cette commande</label>
                    <div class="as-fee-row">
                        <input type="number" name="delivery_fee" class="as-fee-input"
                               placeholder="Ex: 50000" min="0" step="500" required>
                        <div class="as-fee-devise">{{ $devise }}</div>
                    </div>

                    <label class="as-fee-label" style="margin-top:10px;">📍 Destination (optionnel)</label>
                    <input type="text" name="delivery_destination" class="as-dest-input"
                           placeholder="Ex: Kaloum, Ratoma, Matoto…">

                    <button type="submit" class="as-assign-btn">
                        🚴 Assigner ce livreur
                    </button>
                </form>
            </div>
            @endif
        </div>
        @empty
        <div style="padding:32px 20px;text-align:center;color:var(--muted);">
            <div style="font-size:36px;margin-bottom:10px;opacity:.3">🚴</div>
            <div style="font-size:13px;font-weight:600">Aucun livreur rattaché à cette boutique.</div>
            <a href="{{ route('boutique.employees.create') }}" style="display:inline-block;margin-top:12px;padding:9px 16px;background:var(--brand);color:#fff;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;">+ Ajouter un livreur</a>
        </div>
        @endforelse
    </div>{{-- /panelLivreur --}}

    {{-- PANEL : Entreprises de livraison externes --}}
    <div id="panelCompany" class="as-card" style="display:none;">
        <div class="as-card-hd">
            <span style="font-size:18px">🏢</span>
            <span class="as-card-hd-title">Confier à une entreprise</span>
        </div>

        @if($order->delivery_company_id)
        <div class="as-alert success" style="margin:12px 16px;">
            ✅ Déjà confiée à une entreprise de livraison. Vous pouvez en changer ci-dessous.
        </div>
        @endif

        <form action="{{ route('orders.sendToCompany', $order) }}" method="POST" id="companyForm">
            @csrf @method('PUT')
            <div style="padding:14px 16px;display:flex;flex-direction:column;gap:10px;">
                @forelse($deliveryCompanies as $dc)
                @php $isSelected = $order->delivery_company_id == $dc->id; @endphp
                <label class="as-livreur-card {{ $isSelected ? 'assigned' : '' }}" id="dc_{{ $dc->id }}"
                       style="{{ $isSelected ? '' : 'cursor:pointer;' }}"
                       onclick="{{ $isSelected ? '' : 'selectCompany('.$dc->id.')' }}">
                    <input type="radio" name="delivery_company_id" value="{{ $dc->id }}"
                           id="dc_radio_{{ $dc->id }}" style="display:none"
                           {{ $isSelected ? 'checked' : '' }}>
                    <div class="as-lv-top">
                        <div class="as-lv-av" style="border-radius:10px;background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                            @if($dc->image)
                                <img src="{{ asset('storage/'.$dc->image) }}" style="width:100%;height:100%;object-fit:cover;border-radius:10px;" alt="">
                            @else
                                🚚
                            @endif
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div class="as-lv-name">{{ $dc->name }}</div>
                            <div class="as-lv-phone">
                                {{ $dc->phone ?? '' }}
                                @if($dc->commission_percent) · Commission {{ number_format($dc->commission_percent,0) }}% @endif
                            </div>
                        </div>
                        @if($isSelected)
                            <span class="as-lv-status online">✅ Assignée</span>
                        @else
                            <span class="as-lv-status" style="background:#eef2ff;color:#4f46e5;">Choisir</span>
                        @endif
                    </div>
                    @if($isSelected)
                    <div class="as-assigned-badge" style="margin-top:10px;">
                        ✅ Cette entreprise gère actuellement la livraison
                    </div>
                    @endif
                </label>
                @empty
                <div style="padding:24px;text-align:center;color:var(--muted);">
                    <div style="font-size:32px;margin-bottom:8px;opacity:.3">🏢</div>
                    <div style="font-size:13px;font-weight:600;">Aucune entreprise de livraison disponible.</div>
                </div>
                @endforelse
            </div>

            @if($deliveryCompanies->count() > 0)
            <div style="padding:0 16px 16px;">
                <button type="submit" id="companySubmitBtn"
                        style="width:100%;padding:13px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:800;cursor:pointer;font-family:var(--font);display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 4px 14px rgba(99,102,241,.35);transition:all .2s;"
                        disabled>
                    🏢 Confier cette livraison
                </button>
            </div>
            @endif
        </form>
    </div>{{-- /panelCompany --}}

    </div>{{-- /colonne droite --}}

</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/* ── Onglets Livreur / Entreprise ── */
function switchTab(tab) {
    const isLivreur = tab === 'livreur';
    document.getElementById('panelLivreur').style.display = isLivreur ? '' : 'none';
    document.getElementById('panelCompany').style.display = isLivreur ? 'none' : '';

    const tL = document.getElementById('tabLivreur');
    const tC = document.getElementById('tabCompany');
    if (isLivreur) {
        tL.style.background = 'linear-gradient(135deg,var(--navy),var(--navy2))';
        tL.style.color = '#fff';
        tC.style.background = 'transparent';
        tC.style.color = 'var(--muted)';
    } else {
        tC.style.background = 'linear-gradient(135deg,#4f46e5,#7c3aed)';
        tC.style.color = '#fff';
        tL.style.background = 'transparent';
        tL.style.color = 'var(--muted)';
    }
}

/* ── Sélection entreprise ── */
function selectCompany(id) {
    document.querySelectorAll('#panelCompany .as-livreur-card:not(.assigned)').forEach(c => c.classList.remove('selected'));
    const card = document.getElementById('dc_' + id);
    if (card) card.classList.add('selected');
    const radio = document.getElementById('dc_radio_' + id);
    if (radio) radio.checked = true;
    const btn = document.getElementById('companySubmitBtn');
    if (btn) { btn.disabled = false; btn.style.opacity = '1'; }
}

// Si commande déjà confiée → ouvrir l'onglet entreprise automatiquement
@if($order->delivery_company_id)
document.addEventListener('DOMContentLoaded', () => switchTab('company'));
@endif

function selectLivreur(id) {
    // Fermer tous les panels et désélectionner
    document.querySelectorAll('.as-livreur-card:not(.assigned)').forEach(c => c.classList.remove('selected'));
    document.querySelectorAll('.as-fee-panel').forEach(p => p.classList.remove('open'));

    // Ouvrir le panel du livreur cliqué
    const card  = document.getElementById('lv_' + id);
    const panel = document.getElementById('feePanel_' + id);
    if (card)  card.classList.add('selected');
    if (panel) {
        panel.classList.add('open');
        panel.querySelector('input[name="delivery_fee"]')?.focus();
    }
}

document.querySelectorAll('.assign-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn  = form.querySelector('.as-assign-btn');
        const fee  = form.querySelector('input[name="delivery_fee"]').value;
        const dest = form.querySelector('input[name="delivery_destination"]').value;
        const name = form.closest('.as-livreur-card').querySelector('.as-lv-name').textContent;

        if (!fee || parseFloat(fee) < 0) {
            alert('Entrez un frais de livraison valide.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '⏳ Assignation en cours…';

        try {
            const fd = new FormData(form);
            const res = await fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd,
                credentials: 'same-origin',
            });

            if (!res.ok) throw new Error('Erreur serveur');
            const data = await res.json();

            if (data.success) {
                const card = form.closest('.as-livreur-card');
                card.classList.remove('selected');
                card.classList.add('assigned');
                card.onclick = null;

                const devise = document.querySelector('.as-fee-devise')?.textContent?.trim() ?? 'GNF';
                const fmtFee = new Intl.NumberFormat('fr-FR').format(Math.round(fee)) + ' ' + devise;

                const panel = form.closest('.as-fee-panel');
                panel.classList.remove('open');
                panel.innerHTML = `<div class="as-assigned-badge">✅ Livreur assigné · Frais : ${fmtFee}${dest ? ' · ' + dest : ''}</div>`;
                panel.style.display = 'block';

                // Désactiver tous les autres
                document.querySelectorAll('.as-livreur-card:not(.assigned)').forEach(c => {
                    c.style.opacity = '.5';
                    c.style.pointerEvents = 'none';
                });
            } else {
                throw new Error(data.message ?? 'Erreur inconnue');
            }
        } catch(err) {
            alert('Erreur : ' + err.message);
            btn.disabled = false;
            btn.innerHTML = '🚴 Assigner ce livreur';
        }
    });
});
</script>
@endpush
