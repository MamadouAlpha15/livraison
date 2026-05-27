<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Paiement — {{ config('app.name') }}</title>
<style>
*,*::before,*::after{box-sizing:border-box}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#f0fdf4;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;margin:0}
.box{text-align:center;max-width:420px;width:100%}
.ico-wrap{width:80px;height:80px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:20px;border:4px solid}
.ico-wrap.ok{background:#dcfce7;border-color:#bbf7d0}
.ico-wrap.ok svg{color:#16a34a}
.ico-wrap.pending{background:#fef9c3;border-color:#fde68a}
.ico-wrap.pending svg{color:#ca8a04}
h1{font-size:22px;font-weight:900;color:#0f172a;margin:0 0 8px}
p{font-size:14px;color:#64748b;margin:0 0 24px;line-height:1.6}
.info-box{background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:16px 20px;text-align:left;margin-bottom:20px}
.info-row{display:flex;justify-content:space-between;font-size:13px;padding:5px 0;border-bottom:1px solid #f1f5f9}
.info-row:last-child{border-bottom:none;font-weight:700}
.info-lbl{color:#64748b}
.info-val{color:#0f172a;font-weight:600}
.btn{display:inline-flex;align-items:center;gap:8px;padding:13px 28px;border-radius:10px;background:linear-gradient(135deg,#7c3aed,#4f46e5);color:#fff;text-decoration:none;font-size:14px;font-weight:700;border:none;cursor:pointer;font-family:inherit}
.btn:hover{opacity:.9}
.btn-outline{background:transparent;border:2px solid #7c3aed;color:#7c3aed;margin-left:10px}
.pending-note{background:#fefce8;border:1px solid #fde68a;border-radius:10px;padding:12px 16px;font-size:13px;color:#854d0e;margin-bottom:20px;line-height:1.5}
</style>
</head>
<body>
<div class="box">

@if($subscription && $subscription->status === 'active')
    {{-- ── ABONNEMENT ACTIVÉ ── --}}
    <div class="ico-wrap ok">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <h1>Plan {{ ucfirst($subscription->plan) }} activé !</h1>
    <p>Toutes les fonctionnalités sont maintenant débloquées.<br>Votre abonnement est actif jusqu'au {{ $subscription->expires_at?->format('d/m/Y') }}.</p>

    <div class="info-box">
        <div class="info-row">
            <span class="info-lbl">Plan</span>
            <span class="info-val">{{ ucfirst($subscription->plan) }}</span>
        </div>
        <div class="info-row">
            <span class="info-lbl">Montant payé</span>
            @if($userCountry === 'GN')
                <span class="info-val">{{ number_format($subscription->amount, 0, ',', ' ') }} GNF</span>
            @else
                <span class="info-val">{{ number_format($amountXof, 0, ',', ' ') }} XOF</span>
            @endif
        </div>
        <div class="info-row">
            <span class="info-lbl">Expire le</span>
            <span class="info-val">{{ $subscription->expires_at?->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-lbl">Référence</span>
            <span class="info-val" style="font-size:11px;font-family:monospace">{{ $subscription->payment_reference }}</span>
        </div>
    </div>

    <a href="{{ $dashRoute ?? '/' }}" class="btn">
        Accéder à mon tableau de bord →
    </a>

@else
    {{-- ── PAIEMENT EN COURS DE TRAITEMENT ── --}}
    <div class="ico-wrap pending">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    </div>
    <h1>Paiement en traitement…</h1>
    <p>Votre paiement a bien été reçu. L'activation de votre plan peut prendre quelques secondes.</p>

    <div class="pending-note">
        Cliquez sur <strong>Vérifier l'activation</strong> dans quelques secondes. Si le plan reste bloqué, contactez le support.
    </div>

    <a href="{{ url()->current() }}" class="btn">
        Vérifier l'activation
    </a>
    <a href="{{ $dashRoute ?? '/' }}" class="btn btn-outline">
        Tableau de bord
    </a>
@endif

</div>
</body>
</html>
