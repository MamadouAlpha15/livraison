<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $subjectLine ?? config('app.name', 'Shopio') }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f1f5f9;color:#0f172a}
.wrap{max-width:580px;margin:32px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)}
.header{background:linear-gradient(135deg,#4c1d95,#7c3aed);padding:32px 32px 26px;text-align:center}
.header-ico{width:52px;height:52px;background:rgba(255,255,255,.15);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:12px}
.header h1{color:#fff;font-size:19px;font-weight:800;letter-spacing:-.3px}
.body{padding:32px}
.greeting{font-size:15px;font-weight:700;color:#0f172a;margin-bottom:14px}
.msg{font-size:13.5px;color:#334155;line-height:1.75;white-space:pre-line}
.divider{height:1px;background:#e2e8f0;margin:26px 0}
.footer{background:#f8fafc;padding:20px 32px;border-top:1px solid #e2e8f0;text-align:center}
.footer p{font-size:11.5px;color:#94a3b8;line-height:1.7}
.footer strong{color:#64748b}
</style>
</head>
<body>
<div class="wrap">

    <div class="header">
        <div class="header-ico">📣</div>
        <h1>{{ config('app.name', 'Shopio') }}</h1>
    </div>

    <div class="body">
        <div class="greeting">Bonjour {{ $recipientName }},</div>
        <div class="msg">{{ $bodyText }}</div>

        <div class="divider"></div>

        <p style="font-size:11.5px;color:#94a3b8;text-align:center;line-height:1.6">
            Vous recevez cet email car vous avez un compte sur {{ config('app.name', 'Shopio') }}.
        </p>
    </div>

    <div class="footer">
        <p>
            <strong>{{ config('app.name', 'Shopio') }}</strong> — Plateforme double SaaS
        </p>
    </div>

</div>
</body>
</html>
