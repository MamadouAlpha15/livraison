<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Paiement échoué — {{ config('app.name') }}</title>
<style>
*,*::before,*::after{box-sizing:border-box}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#fef2f2;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;margin:0}
.box{text-align:center;max-width:420px;width:100%}
.ico-wrap{width:80px;height:80px;border-radius:50%;background:#fee2e2;display:inline-flex;align-items:center;justify-content:center;margin-bottom:20px;border:4px solid #fecaca}
.ico-wrap svg{color:#dc2626}
h1{font-size:22px;font-weight:900;color:#0f172a;margin:0 0 8px}
p{font-size:14px;color:#64748b;margin:0 0 24px;line-height:1.6}
.btns{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 22px;border-radius:10px;text-decoration:none;font-size:14px;font-weight:700;border:none;cursor:pointer;font-family:inherit}
.btn.primary{background:linear-gradient(135deg,#7c3aed,#4f46e5);color:#fff}
.btn.ghost{background:#fff;border:1.5px solid #e2e8f0;color:#0f172a}
.btn:hover{opacity:.88}
</style>
</head>
<body>
<div class="box">
    <div class="ico-wrap">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </div>
    <h1>Paiement non abouti</h1>
    <p>Le paiement n'a pas pu être confirmé.<br>Vérifiez votre solde et réessayez.</p>

    @if($subscription)
        <p style="font-size:12px;color:#94a3b8;margin-bottom:24px">
            Référence : <code>{{ $subscription->payment_reference }}</code>
        </p>
    @endif

    <div class="btns">
        <a href="javascript:history.back()" class="btn primary">Réessayer le paiement</a>
        <a href="{{ url('/') }}" class="btn ghost">Retour à l'accueil</a>
    </div>
</div>
</body>
</html>
