{{--
    resources/views/boutique/clients/show.blade.php
    Route  : GET /boutique/clients/{user} → Boutique\ClientController@show → name('boutique.clients.show')
    Variables :
      $user      → User (le client)
      $commandes → LengthAwarePaginator<Order>
      $stats     → object (total_depense, nb_commandes, derniere_cmd, premiere_cmd)
      $isTop     → bool
      $shop      → Shop
--}}

@extends('layouts.app')

@section('title', 'Fiche · ' . $user->name)

@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    --brand:     #10b981; --brand-dk: #059669; --brand-lt: #d1fae5; --brand-mlt: #ecfdf5;
    --bg:        #f6f8f7; --surface:  #ffffff; --border:   #e8eceb; --border-dk: #d4d9d7;
    --text:      #0f1c18; --text-2:   #4b5c56; --muted:    #8a9e98;
    --font:      'Plus Jakarta Sans', sans-serif;
    --mono:      'JetBrains Mono', monospace;
    --r: 14px; --r-sm: 9px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.06);
    --shadow:    0 4px 16px rgba(0,0,0,.07);
}

html, body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; -webkit-font-smoothing: antialiased; }
.page-wrap { max-width: 900px; margin: 0 auto; padding: 28px 24px 60px; }

/* Back */
.back-link {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12.5px; font-weight: 600; color: var(--muted);
    text-decoration: none; margin-bottom: 22px;
    padding: 6px 10px 6px 6px; border-radius: var(--r-sm);
    transition: background .15s, color .15s;
}
.back-link:hover { background: var(--surface); color: var(--brand); }

/* ── Profil header ── */
.profile-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 24px 28px;
    display: flex; align-items: flex-start; gap: 20px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 20px;
    position: relative; overflow: hidden;
}
/* Bande de couleur en haut */
.profile-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--brand), #34d399);
}

/* Grand avatar */
.profile-av {
    width: 64px; height: 64px; border-radius: 50%;
    background: linear-gradient(135deg, #059669, #2563eb);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; font-weight: 700; color: #fff; flex-shrink: 0;
    box-shadow: 0 0 0 3px rgba(16,185,129,.2);
}

.profile-info { flex: 1; min-width: 0; }
.profile-name {
    font-size: 20px; font-weight: 700; color: var(--text);
    letter-spacing: -.3px; margin: 0 0 6px;
}
.profile-contacts {
    display: flex; flex-wrap: wrap; gap: 12px;
    font-size: 12.5px; color: var(--text-2);
}
.profile-contact-item { display: flex; align-items: center; gap: 5px; }

/* Badge top client */
.top-badge {
    display: inline-flex; align-items: center; gap: 5px;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #92400e; border: 1px solid #fcd34d;
    font-size: 11px; font-weight: 700;
    padding: 4px 12px; border-radius: 20px;
    margin-top: 8px;
}

/* ── Stats du client ── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 22px;
}
.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 14px 16px;
    box-shadow: var(--shadow-sm);
    text-align: center;
}
.stat-ico   { font-size: 20px; margin-bottom: 6px; }
.stat-val   { font-size: 20px; font-weight: 700; font-family: var(--mono); color: var(--text); letter-spacing: -.5px; }
.stat-lbl   { font-size: 10.5px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .3px; margin-top: 3px; }

/* ── Table commandes ── */
.orders-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.card-hd {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.card-title { font-size: 13px; font-weight: 700; color: var(--text); }

.tbl { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.tbl th {
    padding: 11px 16px; text-align: left;
    font-size: 10px; font-weight: 700; color: var(--muted);
    text-transform: uppercase; letter-spacing: .6px;
    background: var(--bg); border-bottom: 1px solid var(--border);
}
.tbl td { padding: 12px 16px; border-bottom: 1px solid #f3f6f4; vertical-align: middle; }
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: #fafcfb; }

.oid { font-family: var(--mono); font-size: 11px; color: var(--muted); }
.oamt { font-family: var(--mono); font-weight: 700; color: var(--text); }

/* Pills statut */
.pill { display: inline-block; font-size: 10.5px; font-weight: 600; padding: 3px 9px; border-radius: 20px; }
.p-success { background: #d1fae5; color: #065f46; }
.p-warning { background: #fef3c7; color: #92400e; }
.p-info    { background: #dbeafe; color: #1e40af; }
.p-danger  { background: #fee2e2; color: #991b1b; }
.p-muted   { background: #f3f6f4; color: #6b7280; }

/* Pagination */
.pagination-wrap { display: flex; justify-content: center; padding: 16px; }

@media (max-width: 640px) {
    .stats-grid { grid-template-columns: repeat(2,1fr); }
    .profile-card { flex-direction: column; }
    .tbl th:nth-child(4), .tbl td:nth-child(4) { display: none; }
}
</style>
@endpush

@section('content')
<div class="page-wrap">

    {{-- ── Retour à la liste ── --}}
    <a href="{{ route('boutique.clients.index') }}" class="back-link">
        ← Retour aux clients
    </a>

    {{-- ════════════════════════════════════════════════════════════
         PROFIL DU CLIENT
         Avatar grand format, infos de contact, badge top si applicable
         ════════════════════════════════════════════════════════════ --}}
    <div class="profile-card">
        @php
            $nameParts = explode(' ', $user->name ?? 'C L');
            $initials  = strtoupper(substr($nameParts[0],0,1)) . strtoupper(substr($nameParts[1] ?? 'X',0,1));
        @endphp

        {{-- Grand avatar --}}
        <div class="profile-av">{{ $initials }}</div>

        {{-- Infos --}}
        <div class="profile-info">
            <h1 class="profile-name">{{ $user->name }}</h1>

            {{-- Contacts --}}
            <div class="profile-contacts">
                @if($user->email)
                <span class="profile-contact-item">✉️ {{ $user->email }}</span>
                @endif
                @if($user->phone)
                <span class="profile-contact-item">📞 {{ $user->phone }}</span>
                @endif
                @if($user->address)
                <span class="profile-contact-item">📍 {{ $user->address }}</span>
                @endif
                <span class="profile-contact-item">
                    🗓️ Client depuis {{ \Carbon\Carbon::parse($stats->premiere_cmd ?? now())->format('M Y') }}
                </span>
            </div>

            {{-- Badge top client ce mois --}}
            @if($isTop)
            <div class="top-badge">
                🏆 Top client ce mois
            </div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         STATISTIQUES DU CLIENT
         4 chiffres clés : total dépensé, nb commandes, panier moyen, fidélité
         ════════════════════════════════════════════════════════════ --}}
    <div class="stats-grid">

        {{-- Total dépensé --}}
        <div class="stat-card" style="border-top:3px solid #10b981">
            <div class="stat-ico">💰</div>
            <div class="stat-val">{{ number_format(($stats->total_depense ?? 0)/1000, 0) }}k</div>
            <div class="stat-lbl">GNF dépensés</div>
        </div>

        {{-- Nombre de commandes --}}
        <div class="stat-card" style="border-top:3px solid #3b82f6">
            <div class="stat-ico">📦</div>
            <div class="stat-val">{{ $stats->nb_commandes ?? 0 }}</div>
            <div class="stat-lbl">Commandes</div>
        </div>

        {{-- Panier moyen --}}
        <div class="stat-card" style="border-top:3px solid #f59e0b">
            <div class="stat-ico">🛒</div>
            <div class="stat-val">
                @php
                    $panierMoyen = ($stats->nb_commandes > 0)
                        ? round(($stats->total_depense / $stats->nb_commandes) / 1000)
                        : 0;
                @endphp
                {{ $panierMoyen }}k
            </div>
            <div class="stat-lbl">Panier moyen</div>
        </div>

        {{-- Dernière commande --}}
        <div class="stat-card" style="border-top:3px solid #8b5cf6">
            <div class="stat-ico">🕐</div>
            <div class="stat-val" style="font-size:13px">
                {{ $stats->derniere_cmd ? \Carbon\Carbon::parse($stats->derniere_cmd)->diffForHumans() : '—' }}
            </div>
            <div class="stat-lbl">Dernière commande</div>
        </div>

    </div>

    {{-- ════════════════════════════════════════════════════════════
         HISTORIQUE DES COMMANDES
         Toutes les commandes de ce client dans cette boutique
         ════════════════════════════════════════════════════════════ --}}
    <div class="orders-card">
        <div class="card-hd">
            <span class="card-title">📋 Historique des commandes</span>
            <span style="font-size:11px;color:var(--muted)">{{ $stats->nb_commandes ?? 0 }} commande(s) au total</span>
        </div>

        @if($commandes->isEmpty())
            <div style="padding:32px;text-align:center;font-size:13px;color:var(--muted)">
                Aucune commande trouvée.
            </div>
        @else
        <table class="tbl">
            <thead>
                <tr>
                    <th>Réf</th>
                    <th>Produits</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @php
                /* Map des statuts → labels et couleurs */
                $statusMap = [
                    'livrée'       => ['label'=>'Livré',       'cls'=>'p-success'],
                    'pending'      => ['label'=>'En attente',  'cls'=>'p-warning'],
                    'en attente'   => ['label'=>'En attente',  'cls'=>'p-warning'],
                    'confirmée'    => ['label'=>'Confirmée',   'cls'=>'p-info'],
                    'en_livraison' => ['label'=>'En livraison','cls'=>'p-info'],
                    'annulée'      => ['label'=>'Annulée',     'cls'=>'p-danger'],
                ];
                @endphp

                @foreach($commandes as $order)
                @php $st = $statusMap[$order->status] ?? ['label'=>ucfirst($order->status),'cls'=>'p-muted']; @endphp
                <tr>
                    {{-- Référence --}}
                    <td><span class="oid">#{{ $order->id }}</span></td>

                    {{-- Produits de la commande --}}
                    <td>
                        @if($order->items && $order->items->count() > 0)
                            @foreach($order->items->take(2) as $item)
                            <div style="font-size:12px;font-weight:500;color:var(--text)">
                                {{ $item->product->name ?? 'Produit supprimé' }}
                                <span style="color:var(--muted)">×{{ $item->quantity }}</span>
                            </div>
                            @endforeach
                            @if($order->items->count() > 2)
                            <div style="font-size:11px;color:var(--muted)">
                                +{{ $order->items->count()-2 }} autre(s)
                            </div>
                            @endif
                        @else
                            <span style="color:var(--muted);font-size:12px">—</span>
                        @endif
                    </td>

                    {{-- Montant --}}
                    <td>
                        <span class="oamt">{{ number_format($order->total, 0, ',', ' ') }}</span>
                        <span style="font-size:10px;color:var(--muted)"> GNF</span>
                    </td>

                    {{-- Statut --}}
                    <td><span class="pill {{ $st['cls'] }}">{{ $st['label'] }}</span></td>

                    {{-- Date --}}
                    <td style="font-size:12px;color:var(--text-2)">
                        {{ $order->created_at->format('d/m/Y') }}
                        <div style="font-size:10px;color:var(--muted)">
                            {{ $order->created_at->format('H:i') }}
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($commandes->hasPages())
        <div class="pagination-wrap">
            {{ $commandes->links() }}
        </div>
        @endif
        @endif

    </div>

</div>
@endsection