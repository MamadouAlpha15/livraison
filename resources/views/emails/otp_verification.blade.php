<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Code de vérification</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f1f5f9;color:#0f172a}
.wrap{max-width:480px;margin:32px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)}
.header{background:linear-gradient(135deg,#6366f1,#4f46e5);padding:36px 32px;text-align:center}
.header-ico{width:60px;height:60px;background:rgba(255,255,255,.15);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:28px;margin-bottom:14px}
.header h1{color:#fff;font-size:22px;font-weight:800;letter-spacing:-.3px}
.header p{color:rgba(255,255,255,.75);font-size:13px;margin-top:6px}
.body{padding:32px}
.greeting{font-size:15px;font-weight:700;color:#0f172a;margin-bottom:6px}
.intro{font-size:13.5px;color:#475569;line-height:1.6;margin-bottom:24px}
.code-box{background:linear-gradient(135deg,#f5f3ff,#ede9fe);border:1px solid rgba(99,102,241,.2);border-radius:12px;padding:24px;margin-bottom:24px;text-align:center}
.code{font-size:36px;font-weight:800;letter-spacing:10px;color:#4338ca}
.code-hint{font-size:12px;color:#7c7fa0;margin-top:10px}
.footer{background:#f8fafc;padding:20px 32px;border-top:1px solid #e2e8f0;text-align:center}
.footer p{font-size:11.5px;color:#94a3b8;line-height:1.7}
</style>
</head>
<body>
<div class="wrap">

    <div class="header">
        <div class="header-ico">✉️</div>
        <h1>Vérifiez votre email</h1>
        <p>Un dernier pas avant de finaliser votre inscription</p>
    </div>

    <div class="body">

        <div class="greeting">Bonjour {{ $userName }},</div>
        <p class="intro">
            Merci de vous être inscrit(e) sur {{ config('app.name', 'Shopio') }}.
            Saisissez le code ci-dessous sur la page de vérification pour confirmer votre adresse email.
        </p>

        <div class="code-box">
            <div class="code">{{ $otpCode }}</div>
            <div class="code-hint">Ce code expire dans 10 minutes.</div>
        </div>

        <p style="font-size:12px;color:#94a3b8;text-align:center;line-height:1.6">
            Si vous n'êtes pas à l'origine de cette inscription, ignorez simplement cet email.
        </p>

    </div>

    <div class="footer">
        <p>
            <strong>{{ config('app.name', 'Shopio') }}</strong><br>
            Cet email a été envoyé automatiquement suite à votre demande d'inscription.
        </p>
    </div>

</div>
</body>
</html>
