{{--
    resources/views/client/loyalty/index.blade.php
    Route : GET /client/fidelite → Client\LoyaltyController@index
--}}
@extends('layouts.app')
@section('title', 'Mes points & Parrainage')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --orange:    #f90;
    --orange-dk: #e47911;
    --orange-lt: #fff8e7;
    --navy:      #131921;
    --navy-2:    #232f3e;
    --green:     #067d62;
    --green-lt:  #e8f5e9;
    --purple:    #7c3aed;
    --purple-lt: #ede9fe;
    --grey:      #f3f3f3;
    --grey-2:    #eaeded;
    --border:    #ddd;
    --text:      #0f1111;
    --text-2:    #333;
    --muted:     #565959;
    --surface:   #fff;
    --font:      'Open Sans', sans-serif;
    --display:   'Nunito', sans-serif;
    --r:         10px;
    --r-sm:      6px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.08);
    --nav-h:     56px;
}
html { font-family: var(--font); }
body { background: var(--grey); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; }

.nav { background: var(--navy); height: var(--nav-h); display: flex; align-items: center; padding: 0 16px; gap: 10px; position: sticky; top: 0; z-index: 100; }
.nav-logo { font-family: var(--display); font-size: 18px; font-weight: 900; color: var(--orange); text-decoration: none; flex-shrink: 0; }
.nav-logo span { color: #fff; }
.nav-back { color: rgba(255,255,255,.8); font-size: 12.5px; font-weight: 600; text-decoration: none; padding: 5px 10px; border: 1px solid transparent; border-radius: var(--r-sm); transition: all .15s; white-space: nowrap; }
.nav-back:hover { border-color: rgba(255,255,255,.4); color: #fff; }

.page-wrap { max-width: 920px; margin: 0 auto; padding: 24px 16px 80px; }

/* Hero solde de points */
.points-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
    border-radius: var(--r); padding: 28px 26px; margin-bottom: 18px;
    display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;
    position: relative; overflow: hidden;
}
.points-hero::before {
    content: ''; position: absolute; top: -60px; right: -40px;
    width: 220px; height: 220px; border-radius: 50%;
    background: radial-gradient(circle, rgba(255,153,0,.18) 0%, transparent 70%);
}
.points-hero-lbl { font-size: 12.5px; font-weight: 700; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: .6px; margin-bottom: 6px; }
.points-hero-val { font-family: var(--display); font-size: 40px; font-weight: 900; color: #fff; letter-spacing: -1px; line-height: 1; }
.points-hero-val span { font-size: 18px; font-weight: 700; color: var(--orange); margin-left: 6px; }
.points-hero-sub { font-size: 12.5px; color: rgba(255,255,255,.55); margin-top: 6px; }
.points-hero-ico { font-size: 52px; flex-shrink: 0; z-index: 1; }

/* Card générique */
.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); box-shadow: var(--shadow-sm); margin-bottom: 18px; overflow: hidden; }
.card-hd { padding: 14px 20px; border-bottom: 1px solid var(--border); background: var(--grey); display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 800; font-family: var(--display); }
.card-body { padding: 20px; }

/* Comment ça marche */
.how-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; }
.how-item { text-align: center; padding: 10px; }
.how-ico { font-size: 30px; margin-bottom: 8px; }
.how-txt { font-size: 12.5px; color: var(--text-2); line-height: 1.5; }
.how-txt strong { color: var(--text); }

/* Parrainage */
.ref-link-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.ref-link-input {
    flex: 1; min-width: 200px; padding: 11px 14px; border: 1.5px solid var(--border); border-radius: var(--r-sm);
    font-size: 13px; font-family: monospace; color: var(--text); background: var(--grey); outline: none;
}
.btn-copy, .btn-share {
    display: inline-flex; align-items: center; gap: 6px; padding: 11px 18px; border-radius: var(--r-sm);
    font-size: 13px; font-weight: 700; font-family: var(--font); cursor: pointer; border: none; transition: all .15s;
    white-space: nowrap; text-decoration: none;
}
.btn-copy { background: var(--orange); color: var(--navy); }
.btn-copy:hover { background: var(--orange-dk); }
.btn-share { background: #25D366; color: #fff; }
.btn-share:hover { filter: brightness(.95); }
.ref-bonus-note { font-size: 12px; color: var(--muted); margin-top: 10px; }
.ref-bonus-note strong { color: var(--green); }

/* Liste filleuls */
.ref-list { display: flex; flex-direction: column; gap: 0; }
.ref-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 0; border-bottom: 1px solid var(--grey-2); }
.ref-row:last-child { border-bottom: none; }
.ref-name { font-size: 13px; font-weight: 700; color: var(--text); }
.ref-date { font-size: 11px; color: var(--muted); }
.ref-status { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; white-space: nowrap; }
.ref-status.done { background: var(--green-lt); color: #065f46; }
.ref-status.pending { background: #fef3c7; color: #92400e; }
.empty-txt { text-align: center; padding: 24px; font-size: 13px; color: var(--muted); }

/* Historique transactions */
.tx-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 20px; border-bottom: 1px solid var(--grey-2); }
.tx-row:last-child { border-bottom: none; }
.tx-desc { font-size: 12.5px; color: var(--text); font-weight: 600; }
.tx-date { font-size: 11px; color: var(--muted); margin-top: 2px; }
.tx-pts { font-family: monospace; font-size: 14px; font-weight: 800; white-space: nowrap; }
.tx-pts.pos { color: var(--green); }
.tx-pts.neg { color: #b12704; }

.pag-wrap { padding: 12px 20px; }

@media (max-width: 600px) {
    .page-wrap { padding: 16px 12px 60px; }
    .points-hero { padding: 20px 18px; }
    .points-hero-val { font-size: 32px; }
    .points-hero-ico { font-size: 40px; }
}
</style>
@endpush

@section('content')

<nav class="nav">
    <a href="{{ route('client.dashboard') }}" class="nav-logo">Ma<span>Boutique</span></a>
    <a href="{{ route('client.dashboard') }}" class="nav-back">← Retour à l'accueil</a>
</nav>

<div class="page-wrap">

    {{-- HERO SOLDE --}}
    <div class="points-hero">
        <div>
            <div class="points-hero-lbl">🎁 Mon solde de points fidélité</div>
            <div class="points-hero-val">{{ number_format($user->loyalty_points, 0, ',', ' ') }}<span>points</span></div>
            <div class="points-hero-sub">1 point = 1 GNF de réduction sur votre prochaine commande</div>
        </div>
        <div class="points-hero-ico">💎</div>
    </div>

    {{-- COMMENT ÇA MARCHE --}}
    <div class="card">
        <div class="card-hd">✨ Comment gagner des points</div>
        <div class="card-body">
            <div class="how-grid">
                <div class="how-item">
                    <div class="how-ico">🛒</div>
                    <div class="how-txt"><strong>0,1% de cashback</strong><br>sur chaque commande livrée</div>
                </div>
                <div class="how-item">
                    <div class="how-ico">👥</div>
                    <div class="how-txt"><strong>2 000 points</strong><br>pour chaque ami parrainé qui commande</div>
                </div>
                <div class="how-item">
                    <div class="how-ico">🏷️</div>
                    <div class="how-txt"><strong>Utilisez vos points</strong><br>jusqu'à 50% de réduction à la commande</div>
                </div>
            </div>
        </div>
    </div>

    {{-- PARRAINAGE --}}
    <div class="card">
        <div class="card-hd">🔗 Parrainez vos amis</div>
        <div class="card-body">
            <p style="font-size:13px;color:var(--text-2);margin:0 0 14px;line-height:1.6">
                Partagez votre lien : dès que votre filleul reçoit sa première commande, vous gagnez
                <strong style="color:var(--green)">2 000 points</strong> et lui reçoit
                <strong style="color:var(--green)">1 000 points</strong> de bienvenue.
            </p>
            <div class="ref-link-row">
                <input type="text" class="ref-link-input" id="refLinkInput" value="{{ $referralUrl }}" readonly>
                <button type="button" class="btn-copy" onclick="copyRefLink()">📋 Copier</button>
                <a class="btn-share" target="_blank"
                   href="https://wa.me/?text={{ urlencode('Rejoins-moi sur Shopio et profite de bons plans ! ' . $referralUrl) }}">
                   💬 Partager sur WhatsApp
                </a>
            </div>

            @if($referrals->isNotEmpty())
            <div style="margin-top:20px">
                <div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">
                    Vos filleuls ({{ $referrals->count() }})
                </div>
                <div class="ref-list">
                    @foreach($referrals as $ref)
                    <div class="ref-row">
                        <div>
                            <div class="ref-name">{{ $ref->name }}</div>
                            <div class="ref-date">Inscrit le {{ $ref->created_at->format('d/m/Y') }}</div>
                        </div>
                        @if($ref->referral_rewarded_at)
                            <span class="ref-status done">✓ Récompensé</span>
                        @else
                            <span class="ref-status pending">⏳ En attente de sa 1ère commande</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- HISTORIQUE --}}
    <div class="card">
        <div class="card-hd">📜 Historique de mes points</div>
        @if($transactions->isEmpty())
            <div class="empty-txt">Aucun mouvement pour le moment. Passez votre première commande pour gagner des points !</div>
        @else
            @foreach($transactions as $tx)
            <div class="tx-row">
                <div>
                    <div class="tx-desc">{{ $tx->description }}</div>
                    <div class="tx-date">{{ $tx->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div class="tx-pts {{ $tx->points >= 0 ? 'pos' : 'neg' }}">{{ $tx->points >= 0 ? '+' : '' }}{{ number_format($tx->points, 0, ',', ' ') }}</div>
            </div>
            @endforeach
            @if($transactions->hasPages())
            <div class="pag-wrap">{{ $transactions->links() }}</div>
            @endif
        @endif
    </div>

</div>

@endsection

@push('scripts')
<script>
function copyRefLink() {
    const input = document.getElementById('refLinkInput');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard?.writeText(input.value).then(() => {
        const btn = event.target.closest('button');
        const original = btn.textContent;
        btn.textContent = '✓ Copié !';
        setTimeout(() => { btn.textContent = original; }, 1800);
    });
}
</script>
@endpush
