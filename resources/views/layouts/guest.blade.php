<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Shopio') }}</title>
    <link rel="icon" type="image/jpeg" href="/images/Shopio2.jpeg">
    <link rel="shortcut icon" type="image/jpeg" href="/images/Shopio.jpeg">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Figtree', sans-serif; min-height: 100vh; display: flex; background: #f0fdf4; }

        /* LEFT PANEL */
        .auth-left {
            width: 44%; min-height: 100vh;
            background: linear-gradient(145deg, #064e3b 0%, #065f46 45%, #059669 100%);
            display: flex; flex-direction: column; justify-content: space-between;
            padding: 48px 52px; position: relative; overflow: hidden;
        }
        .auth-left::before {
            content:''; position:absolute; width:420px; height:420px;
            background:rgba(255,255,255,.06); border-radius:50%;
            top:-120px; right:-140px;
        }
        .auth-left::after {
            content:''; position:absolute; width:260px; height:260px;
            background:rgba(255,255,255,.04); border-radius:50%;
            bottom:-60px; left:-70px;
        }
        .al-logo { display:flex; align-items:center; gap:14px; position:relative; z-index:1; }
        .al-logo img { height:48px; width:48px; object-fit:cover; border-radius:12px; border:2px solid rgba(255,255,255,.25); }
        .al-logo-name { font-size:22px; font-weight:800; color:#fff; letter-spacing:-.5px; }
        .al-body { position:relative; z-index:1; }
        .al-title { font-size:34px; font-weight:800; color:#fff; line-height:1.2; margin-bottom:14px; letter-spacing:-.5px; }
        .al-title span { color:#6ee7b7; }
        .al-sub { font-size:14.5px; color:rgba(255,255,255,.7); line-height:1.65; margin-bottom:36px; }
        .al-features { display:flex; flex-direction:column; gap:18px; }
        .al-feature { display:flex; align-items:flex-start; gap:14px; }
        .al-ico { width:40px; height:40px; border-radius:10px; background:rgba(255,255,255,.12); display:flex; align-items:center; justify-content:center; font-size:19px; flex-shrink:0; }
        .al-ftxt strong { display:block; font-size:13.5px; font-weight:700; color:#fff; margin-bottom:1px; }
        .al-ftxt span { font-size:12px; color:rgba(255,255,255,.6); }
        .al-footer { position:relative; z-index:1; font-size:11.5px; color:rgba(255,255,255,.35); }

        /* RIGHT PANEL */
        .auth-right { flex:1; min-height:100vh; background:#fff; display:flex; align-items:center; justify-content:center; padding:48px 32px; overflow-y:auto; }
        .auth-box { width:100%; max-width:440px; }

        /* FORM STYLES — used by login.blade & register.blade */
        .ab-title { font-size:26px; font-weight:800; color:#111827; margin-bottom:5px; letter-spacing:-.4px; }
        .ab-sub   { font-size:14px; color:#6b7280; margin-bottom:30px; }
        .f-group  { margin-bottom:18px; }
        .f-label  { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
        .f-input  {
            width:100%; padding:11px 14px; border:1.5px solid #e5e7eb; border-radius:10px;
            font-size:14px; color:#111827; background:#fff; outline:none;
            transition:border-color .15s, box-shadow .15s; font-family:inherit;
        }
        .f-input:focus { border-color:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,.13); }
        .f-input::placeholder { color:#9ca3af; }
        .f-input-err { border-color:#ef4444 !important; }
        .f-icon-wrap { position:relative; }
        .f-icon-wrap .f-input { padding-left:42px; }
        .f-icon-wrap .f-ico  { position:absolute; left:13px; top:50%; transform:translateY(-50%); font-size:16px; pointer-events:none; }
        .f-pw-wrap { position:relative; }
        .f-pw-wrap .f-input  { padding-left:42px; padding-right:44px; }
        .f-pw-eye { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; font-size:17px; color:#9ca3af; padding:0; line-height:1; }
        .f-pw-eye:hover { color:#6b7280; }
        .f-select {
            width:100%; padding:11px 36px 11px 14px; border:1.5px solid #e5e7eb; border-radius:10px;
            font-size:14px; color:#111827; background:#fff; appearance:none; -webkit-appearance:none;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat:no-repeat; background-position:right 10px center; background-size:16px;
            transition:border-color .15s, box-shadow .15s; outline:none; cursor:pointer; font-family:inherit;
        }
        .f-select:focus { border-color:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,.13); }
        .f-flag-wrap { position:relative; }
        .f-flag-wrap .f-select { padding-left:44px; }
        .f-flag-preview { position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:20px; pointer-events:none; line-height:1; z-index:1; }
        .f-error { font-size:12px; color:#ef4444; margin-top:5px; }
        .f-hint  { font-size:11px; color:#9ca3af; margin-top:4px; }
        .btn-auth {
            width:100%; padding:13px; background:linear-gradient(135deg,#059669,#10b981);
            color:#fff; font-size:15px; font-weight:700; border:none; border-radius:10px;
            cursor:pointer; transition:opacity .15s, transform .1s; font-family:inherit; letter-spacing:.01em;
        }
        .btn-auth:hover { opacity:.92; transform:translateY(-1px); }
        .btn-auth:active { transform:translateY(0); }
        .role-cards { display:grid; grid-template-columns:repeat(2,1fr); gap:8px; margin-top:2px; }
        .role-card {
            display:flex; flex-direction:column; align-items:center; gap:5px;
            padding:12px 8px; border:1.5px solid #e5e7eb; border-radius:10px;
            cursor:pointer; transition:border-color .15s, background .15s;
            font-size:12px; font-weight:600; color:#374151; user-select:none; text-align:center;
        }
        .role-card:hover { border-color:#10b981; background:#f0fdf4; }
        .role-card.active { border-color:#10b981; background:#ecfdf5; color:#065f46; }
        .role-card .rc-ico { font-size:22px; }
        .role-input-hidden { display:none; }
        .f-check { display:flex; align-items:center; gap:8px; cursor:pointer; }
        .f-check input[type=checkbox] { width:16px; height:16px; accent-color:#10b981; cursor:pointer; flex-shrink:0; }
        .f-check span { font-size:13px; color:#6b7280; }
        .auth-link { color:#10b981; font-weight:600; text-decoration:none; }
        .auth-link:hover { text-decoration:underline; }
        .auth-row { display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; margin-top:24px; }
        .auth-row-end { display:flex; justify-content:flex-end; gap:12px; align-items:center; margin-top:24px; }
        .f-alert { padding:10px 14px; border-radius:8px; font-size:13px; margin-bottom:18px; }
        .f-alert-success { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }

        @media (max-width: 768px) {
            .auth-left { display:none; }
            .auth-right { padding:32px 20px; }
        }
    </style>
</head>
<body>
    <div class="auth-left">
        <div class="al-logo">
            <img src="/images/Shopio2.jpeg" alt="Shopio">
            <span class="al-logo-name">Shopio</span>
        </div>
        <div class="al-body">
            <div class="al-title">La marketplace<br>made in <span>Guinée</span> 🇬🇳</div>
            <p class="al-sub">Commandez, vendez et livrez en toute confiance.<br>Des milliers de boutiques à portée de main.</p>
            <div class="al-features">
                <div class="al-feature"><div class="al-ico">🛒</div><div class="al-ftxt"><strong>Commandes faciles</strong><span>Parcourez et commandez en quelques clics</span></div></div>
                <div class="al-feature"><div class="al-ico">🚚</div><div class="al-ftxt"><strong>Livraison rapide</strong><span>Suivi en temps réel de vos colis</span></div></div>
                <div class="al-feature"><div class="al-ico">🏪</div><div class="al-ftxt"><strong>Ouvrez votre boutique</strong><span>Vendez vos produits partout en Guinée</span></div></div>
                <div class="al-feature"><div class="al-ico">🔒</div><div class="al-ftxt"><strong>Transactions sécurisées</strong><span>Vos données et paiements sont protégés</span></div></div>
            </div>
        </div>
        <div class="al-footer">© {{ date('Y') }} Shopio · Tous droits réservés</div>
    </div>

    <div class="auth-right">
        <div class="auth-box">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
