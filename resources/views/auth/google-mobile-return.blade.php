{{--
    resources/views/auth/google-mobile-return.blade.php
    Filet de sécurité pour Api\V1\GoogleAuthController::returnFallback — n'est
    affiché que si Android App Links n'a pas intercepté le retour vers l'app
    mobile Shopio (cas rare : app non installée, vérification échouée...).
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Retour vers Shopio</title>
    <style>
        body { font-family: -apple-system, 'Open Sans', sans-serif; background: #131921; color: #fff; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .box { text-align: center; padding: 32px; max-width: 340px; }
        .box img { height: 56px; border-radius: 12px; margin-bottom: 16px; }
        h1 { font-size: 18px; margin: 0 0 10px; }
        p { font-size: 13.5px; color: rgba(255,255,255,.7); line-height: 1.6; }
    </style>
</head>
<body>
    <div class="box">
        <img src="{{ asset('images/shopio-logo-192.png') }}" alt="Shopio">
        <h1>Connexion Google réussie ✅</h1>
        <p>Retournez à l'application Shopio pour continuer.<br>Si rien ne se passe, ouvrez l'app manuellement.</p>
    </div>
</body>
</html>
