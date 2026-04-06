{{--
    resources/views/boutique/clients/index.blade.php
    Route  : GET /boutique/clients → Boutique\ClientController@index → name('boutique.clients.index')
    Variables :
      $clients        → LengthAwarePaginator (user_id, total_depense, nb_commandes, derniere_cmd)
      $topClientIds   → array des IDs top 5 ce mois
      $search         → string|null
      $sortBy         → string
      $totalClients   → int
      $nouveauxCeMois → int
      $caTotal        → float
      $shop           → Shop
--}}

@extends('layouts.app')

@section('title', 'Clients · ' . $shop->name)

@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }

/* ── Variables identiques au dashboard ── */
:root {
    --brand:     #10b981;
    --brand-dk:  #059669;
    --brand-lt:  #d1fae5;
    --brand-mlt: #ecfdf5;
    --bg:        #f6f8f7;
    --surface:   #ffffff;
    --border:    #e8eceb;
    --border-dk: #d4d9d7;
    --text:      #0f1c18;
    --text-2:    #4b5c56;
    --muted:     #8a9e98;
    --font:      'Plus Jakarta Sans', sans-serif;
    --mono:      'JetBrains Mono', monospace;
    --r:         14px;
    --r-sm:      9px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.06);
    --shadow:    0 4px 16px rgba(0,0,0,.07);
}

html, body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; -webkit-font-smoothing: antialiased; }

/* ── Layout page ── */
.page-wrap { max-width: 1100px; margin: 0 auto; padding: 28px 24px 60px; }

/* ── Back link ── */
.back-link {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12.5px; font-weight: 600; color: var(--muted);
    text-decoration: none; margin-bottom: 22px;
    padding: 6px 10px 6px 6px; border-radius: var(--r-sm);
    transition: background .15s, color .15s;
}
.back-link:hover { background: var(--surface); color: var(--brand); }

/* ── Header de page ── */
.page-hd {
    display: flex; align-items: flex-start;
    justify-content: space-between; gap: 16px;
    margin-bottom: 24px; flex-wrap: wrap;
}
.page-title { font-size: 22px; font-weight: 700; color: var(--text); letter-spacing: -.4px; margin: 0 0 4px; }
.page-sub   { font-size: 13px; color: var(--text-2); margin: 0; }

/* ── KPI mini ── */
.kpi-mini {
    display: flex; gap: 12px; flex-wrap: wrap;
    margin-bottom: 22px;
}
.kpi-chip {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 14px 18px;
    min-width: 150px;
    box-shadow: var(--shadow-sm);
    flex: 1;
}
.kpi-chip-lbl { font-size: 10.5px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; }
.kpi-chip-val { font-size: 22px; font-weight: 700; color: var(--text); font-family: var(--mono); letter-spacing: -.5px; }
.kpi-chip-sub { font-size: 10px; color: var(--muted); margin-top: 2px; }

/* ── Barre recherche + tri ── */
.toolbar {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 18px; flex-wrap: wrap;
}
.search-box {
    flex: 1; min-width: 200px;
    display: flex; align-items: center; gap: 8px;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r-sm); padding: 9px 14px;
    box-shadow: var(--shadow-sm);
}
.search-box input {
    flex: 1; border: none; outline: none;
    font-size: 13px; font-family: var(--font); color: var(--text);
    background: transparent;
}
.search-box input::placeholder { color: var(--muted); }

.sort-select {
    padding: 9px 14px; border-radius: var(--r-sm);
    border: 1px solid var(--border-dk); background: var(--surface);
    font-size: 12px; font-weight: 600; font-family: var(--font);
    color: var(--text-2); cursor: pointer; outline: none;
    box-shadow: var(--shadow-sm);
}

/* ── Table clients ── */
.clients-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    margin-bottom: 22px;
}

.tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
.tbl thead th {
    padding: 12px 16px;
    text-align: left; font-size: 10.5px; font-weight: 700;
    color: var(--muted); text-transform: uppercase; letter-spacing: .6px;
    background: var(--bg); border-bottom: 1px solid var(--border);
}
.tbl tbody td {
    padding: 13px 16px;
    border-bottom: 1px solid #f3f6f4;
    vertical-align: middle;
}
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: #fafcfb; cursor: pointer; }

/* Colonne client : avatar + nom + email */
.client-cell { display: flex; align-items: center; gap: 12px; }
.c-av {
    width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.c-name { font-size: 13.5px; font-weight: 600; color: var(--text); }
.c-email { font-size: 11px; color: var(--muted); margin-top: 1px; }
.c-phone { font-size: 11px; color: var(--muted); }

/* Montant */
.c-amount { font-family: var(--mono); font-weight: 700; color: var(--text); font-size: 13px; }
.c-amount-sub { font-size: 10px; color: var(--muted); }

/* Badge top client */
.badge-top {
    display: inline-flex; align-items: center; gap: 4px;
    background: #fef3c7; color: #92400e;
    border: 1px solid #fde68a;
    font-size: 10px; font-weight: 700;
    padding: 2px 8px; border-radius: 20px;
}

/* Badge nb commandes */
.badge-cmd {
    display: inline-flex; align-items: center;
    background: var(--brand-mlt); color: var(--brand-dk);
    border: 1px solid var(--brand-lt);
    font-size: 11px; font-weight: 700; font-family: var(--mono);
    padding: 3px 10px; border-radius: 20px;
}

/* Bouton voir fiche */
.btn-fiche {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 6px 12px; border-radius: var(--r-sm);
    font-size: 11.5px; font-weight: 600; font-family: var(--font);
    border: 1px solid var(--border-dk); background: var(--surface);
    color: var(--text-2); text-decoration: none; transition: all .15s;
}
.btn-fiche:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-mlt); }

/* ── Pagination ── */
.pagination-wrap { display: flex; justify-content: center; margin-top: 8px; }

/* ── Couleurs avatars ── */
.av-green  { background: #059669; }
.av-blue   { background: #2563eb; }
.av-amber  { background: #d97706; }
.av-purple { background: #7c3aed; }
.av-teal   { background: #0891b2; }
.av-rose   { background: #e11d48; }

/* ── TOP CLIENTS PODIUM ─────────────────────────────────────────
   Card avec 5 colonnes représentant le classement du mois.
   Chaque colonne : médaille + avatar + nom + barre + montant.
   ─────────────────────────────────────────────────────────────── */
.top-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    margin-bottom: 22px;
}
.top-card-hd {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.top-card-title {
    font-size: 13px; font-weight: 700; color: var(--text);
    display: flex; align-items: center; gap: 6px;
}
.top-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0;
}
.top-item {
    display: flex; flex-direction: column;
    align-items: center; gap: 6px;
    padding: 20px 12px 18px;
    border-right: 1px solid var(--border);
    text-decoration: none;
    transition: background .15s;
}
.top-item:last-child { border-right: none; }
.top-item:hover { background: var(--bg); }

/* Médaille en grand */
.top-medal { font-size: 22px; line-height: 1; }

/* Avatar circulaire */
.top-av {
    width: 44px; height: 44px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 700; color: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
}

/* Nom du client */
.top-name {
    font-size: 12px; font-weight: 700; color: var(--text);
    text-align: center; line-height: 1.2;
}

/* Barre de progression */
.top-bar-track {
    width: 100%; height: 5px;
    background: #eef1f0; border-radius: 3px; overflow: hidden;
}
.top-bar-fill {
    height: 100%; border-radius: 3px;
    transition: width 1s cubic-bezier(.23,1,.32,1);
}

/* Montant et nb commandes */
.top-amount {
    font-size: 11px; font-weight: 700; font-family: var(--mono);
    color: var(--text); text-align: center;
}
.top-cmds {
    font-size: 10px; color: var(--muted); text-align: center;
}

/* ── Responsive ── */
@media (max-width: 760px) {
    .page-wrap { padding: 16px; }
    .tbl thead th:nth-child(3),
    .tbl tbody td:nth-child(3) { display: none; }
    .kpi-chip { min-width: 120px; }
    .top-grid { grid-template-columns: repeat(3,1fr); }
}
@media (max-width: 480px) {
    .top-grid { grid-template-columns: repeat(2,1fr); }
}
</style>
@endpush

@section('content')
<div class="page-wrap">

    {{-- ── Retour au dashboard ── --}}
    <a href="{{ route('boutique.dashboard') }}" class="back-link">
        ← Retour au dashboard
    </a>

    {{-- ── En-tête de page ── --}}
    <div class="page-hd">
        <div>
            <h1 class="page-title">👥 Clients</h1>
            <p class="page-sub">Tous les clients qui ont commandé dans {{ $shop->name }}</p>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         KPI GLOBAUX : 3 chiffres clés en haut de page
         ════════════════════════════════════════════════════════════ --}}
    <div class="kpi-mini">

        {{-- Total clients distincts --}}
        <div class="kpi-chip">
            <div class="kpi-chip-lbl">Total clients</div>
            <div class="kpi-chip-val">{{ $totalClients }}</div>
            <div class="kpi-chip-sub">clients uniques</div>
        </div>

        {{-- Nouveaux ce mois --}}
        <div class="kpi-chip" style="border-top:3px solid #10b981">
            <div class="kpi-chip-lbl">Nouveaux ce mois</div>
            <div class="kpi-chip-val" style="color:var(--brand)">{{ $nouveauxCeMois }}</div>
            <div class="kpi-chip-sub">ont commandé ce mois</div>
        </div>

        {{-- CA total généré par les clients --}}
        <div class="kpi-chip" style="border-top:3px solid #3b82f6">
            <div class="kpi-chip-lbl">CA total généré</div>
            <div class="kpi-chip-val" style="color:#2563eb">{{ number_format($caTotal/1000, 0) }}k</div>
            <div class="kpi-chip-sub">GNF cumulés</div>
        </div>

    </div>

    {{-- Flash messages --}}
    @foreach(['success','info','warning','danger'] as $type)
        @if(session($type))
        <div style="margin-bottom:16px;padding:10px 14px;font-size:12.5px;font-weight:500;border-radius:var(--r-sm);border:1px solid;
            background:{{ $type==='success'?'#ecfdf5':($type==='danger'?'#fef2f2':($type==='info'?'#eff6ff':'#fffbeb')) }};
            border-color:{{ $type==='success'?'#6ee7b7':($type==='danger'?'#fca5a5':($type==='info'?'#93c5fd':'#fcd34d')) }};
            color:{{ $type==='success'?'#065f46':($type==='danger'?'#991b1b':($type==='info'?'#1e40af':'#92400e')) }}">
            {{ session($type) }}
        </div>
        @endif
    @endforeach

    {{-- ════════════════════════════════════════════════════════════
         BARRE RECHERCHE + TRI
         ════════════════════════════════════════════════════════════ --}}
    <form method="GET" action="{{ route('boutique.clients.index') }}">
        <div class="toolbar">

            {{-- Recherche par nom / email / téléphone --}}
            <div class="search-box">
                <span style="font-size:15px">🔍</span>
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Rechercher par nom, email, téléphone…"
                       autocomplete="off">
            </div>

            {{-- Tri --}}
            <select name="sort" class="sort-select" onchange="this.form.submit()">
                <option value="total_depense"  {{ $sortBy === 'total_depense'  ? 'selected' : '' }}>Trier : Plus dépensier</option>
                <option value="nb_commandes"   {{ $sortBy === 'nb_commandes'   ? 'selected' : '' }}>Trier : Plus de commandes</option>
                <option value="derniere_cmd"   {{ $sortBy === 'derniere_cmd'   ? 'selected' : '' }}>Trier : Dernière commande</option>
            </select>

            {{-- Bouton recherche --}}
            <button type="submit" style="padding:9px 18px;border-radius:var(--r-sm);background:var(--brand);color:#fff;border:none;font-size:12.5px;font-weight:600;font-family:var(--font);cursor:pointer">
                Rechercher
            </button>

            {{-- Reset --}}
            @if($search)
            <a href="{{ route('boutique.clients.index') }}"
               style="padding:9px 14px;border-radius:var(--r-sm);border:1px solid var(--border-dk);background:var(--surface);font-size:12px;font-weight:600;color:var(--text-2);text-decoration:none">
                ✕ Effacer
            </a>
            @endif

        </div>
    </form>

    {{-- ════════════════════════════════════════════════════════════
         TOP 5 CLIENTS DU MOIS
         Podium visuel des meilleurs clients ce mois.
         Affiché au-dessus de la table pour une visibilité immédiate.
         ════════════════════════════════════════════════════════════ --}}
    @php
        /* Calcul du top 5 clients du mois pour l'affichage podium */
        $topClientsMonth = $shop->orders()
            ->with('user')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->select('user_id',
                \Illuminate\Support\Facades\DB::raw('SUM(total) as total_mois'),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as nb_cmd_mois')
            )
            ->groupBy('user_id')
            ->orderByDesc('total_mois')
            ->take(5)
            ->get();
        $maxTop = $topClientsMonth->max('total_mois') ?: 1;
        $avColors = ['av-green','av-blue','av-amber','av-purple','av-teal'];
        $medailles = ['🥇','🥈','🥉','4e','5e'];
        $medalColors = ['#f59e0b','#9ca3af','#b45309','#6b7280','#6b7280'];
    @endphp

    @if($topClientsMonth->isNotEmpty())
    <div class="top-card">
        <div class="top-card-hd">
            <div class="top-card-title">
                <span>🏆</span> Top clients — {{ now()->translatedFormat('F Y') }}
            </div>
            <span style="font-size:11px;color:var(--muted)">par montant dépensé ce mois</span>
        </div>
        <div class="top-grid">
            @foreach($topClientsMonth as $i => $item)
            @php
                $c     = $item->user;
                if (!$c) continue;
                $parts = explode(' ', $c->name ?? 'C L');
                $init  = strtoupper(substr($parts[0],0,1)).strtoupper(substr($parts[1]??'X',0,1));
                $col   = $avColors[$i % count($avColors)];
                $pct   = round(($item->total_mois / $maxTop) * 100);
            @endphp
            <a href="{{ route('boutique.clients.show', $c) }}" class="top-item">
                {{-- Médaille --}}
                <div class="top-medal" style="color:{{ $medalColors[$i] }}">
                    {{ $medailles[$i] }}
                </div>
                {{-- Avatar --}}
                <div class="top-av {{ $col }}">{{ $init }}</div>
                {{-- Nom --}}
                <div class="top-name">{{ Str::limit($c->name, 14) }}</div>
                {{-- Barre de progression --}}
                <div class="top-bar-track">
                    <div class="top-bar-fill" data-pct="{{ $pct }}" style="width:0%;background:{{ $medalColors[$i] }}"></div>
                </div>
                {{-- Montant --}}
                <div class="top-amount">{{ number_format($item->total_mois/1000,0) }}k GNF</div>
                <div class="top-cmds">{{ $item->nb_cmd_mois }} cmd</div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════
         TABLE CLIENTS
         Chaque ligne : avatar + nom + email + téléphone +
         montant total + nb commandes + dernière cmd + badge top + lien fiche
         ════════════════════════════════════════════════════════════ --}}
    <div class="clients-card">
        @if($clients->isEmpty())
            <div style="padding:48px;text-align:center;font-size:14px;color:var(--muted)">
                @if($search)
                    Aucun client trouvé pour « {{ $search }} »
                @else
                    Aucun client pour le moment.
                @endif
            </div>
        @else
        <table class="tbl">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Montant total</th>
                    <th>Commandes</th>
                    <th>Dernière commande</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @php
                    /* Couleurs des avatars en rotation */
                    $avCols = ['av-green','av-blue','av-amber','av-purple','av-teal','av-rose'];
                @endphp

                @foreach($clients as $i => $item)
                @php
                    $client = $item->user;
                    if (!$client) continue;

                    /* Initiales pour l'avatar */
                    $parts  = explode(' ', $client->name ?? 'C L');
                    $init   = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
                    $col    = $avCols[$i % count($avCols)];

                    /* Top client ce mois ? */
                    $isTop  = in_array($client->id, $topClientIds);
                    $rang   = array_search($client->id, $topClientIds);
                    $medailles = ['🥇','🥈','🥉','4e','5e'];
                @endphp
                <tr onclick="window.location='{{ route('boutique.clients.show', $client) }}'"
                    style="cursor:pointer">

                    {{-- Colonne 1 : Avatar + nom + contact --}}
                    <td>
                        <div class="client-cell">
                            <div class="c-av {{ $col }}">{{ $init }}</div>
                            <div>
                                <div class="c-name">{{ $client->name }}</div>
                                @if($client->email)
                                    <div class="c-email">{{ $client->email }}</div>
                                @endif
                                @if($client->phone)
                                    <div class="c-phone">📞 {{ $client->phone }}</div>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Colonne 2 : Montant total dépensé --}}
                    <td>
                        <div class="c-amount">{{ number_format($item->total_depense, 0, ',', ' ') }}</div>
                        <div class="c-amount-sub">GNF</div>
                    </td>

                    {{-- Colonne 3 : Nombre de commandes --}}
                    <td>
                        <span class="badge-cmd">{{ $item->nb_commandes }}</span>
                    </td>

                    {{-- Colonne 4 : Date dernière commande --}}
                    <td style="font-size:12px;color:var(--text-2)">
                        {{ \Carbon\Carbon::parse($item->derniere_cmd)->diffForHumans() }}
                        <div style="font-size:10px;color:var(--muted)">
                            {{ \Carbon\Carbon::parse($item->derniere_cmd)->format('d/m/Y') }}
                        </div>
                    </td>

                    {{-- Colonne 5 : Badge top client ou client régulier --}}
                    <td>
                        @if($isTop)
                            <span class="badge-top">
                                {{ $medailles[$rang] }} Top client
                            </span>
                        @else
                            <span style="font-size:11px;color:var(--muted)">Client régulier</span>
                        @endif
                    </td>

                    {{-- Colonne 6 : Lien vers la fiche complète --}}
                    <td>
                        <a href="{{ route('boutique.clients.show', $client) }}"
                           class="btn-fiche"
                           onclick="event.stopPropagation()">
                            Voir fiche →
                        </a>
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Pagination --}}
    @if($clients->hasPages())
    <div class="pagination-wrap">
        {{ $clients->links() }}
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    /* Animation barres top clients */
    document.querySelectorAll('.top-bar-fill').forEach((el, i) => {
        setTimeout(() => { el.style.width = el.dataset.pct + '%'; }, 100 + i * 120);
    });
});
</script>
@endpush