{{-- Lien d'aperçu de vitrine expiré ou désactivé (Faire grandir ma vitrine → Qualité). --}}
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Aperçu expiré · Shopio</title>
<link href="{{ \App\Support\Assets::v('fonts/dashboard-fonts.css') }}" rel="stylesheet">
<style>
html, body { margin: 0; min-height: 100%; background: #F6F9FF; color: #16162A; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
main { max-width: 440px; margin: 12vh auto; padding: 28px max(16px, env(safe-area-inset-left, 0px)); text-align: center; }
.card { padding: 28px 24px; border-radius: 26px; background: #fff; box-shadow: 0 0 0 1px #E3E8F2, 0 24px 50px -30px rgba(11,30,61,.4); }
img { width: 52px; height: 52px; border-radius: 14px; }
h1 { margin: 14px 0 6px; font-size: 22px; font-weight: 800; letter-spacing: -.02em; }
p { margin: 0; color: #45455E; }
a { display: inline-flex; margin-top: 18px; min-height: 46px; align-items: center; padding: 0 22px; border-radius: 999px; background: #1668D8; color: #fff; font-weight: 800; text-decoration: none; }
</style>
</head>
<body>
<main><div class="card">
    <img src="{{ \App\Support\Assets::v('images/shopio-logo-192.png') }}" alt="Shopio" width="52" height="52">
    <h1>Ce lien d’aperçu n’est plus valable</h1>
    <p>Les liens d’aperçu durent 48 heures, ou la boutique l’a désactivé. Demandez un nouveau lien à la personne qui vous l’a envoyé.</p>
    <a href="{{ url('/') }}">Découvrir Shopio</a>
</div></main>
</body>
</html>
