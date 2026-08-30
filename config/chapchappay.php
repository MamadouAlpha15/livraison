<?php

return [

    // URL de base de l'API ChapChap Pay
    'base_url' => env('CHAPCHAPPAY_BASE_URL', 'https://chapchappay.com/api'),

    // Clé API — en-tête CCP-Api-Key. Utilisez la clé Test en sandbox, Production en live.
    'api_key' => env('CHAPCHAPPAY_API_KEY', ''),

    // Clé d'encryptage (HMAC) — sert à signer/vérifier les webhooks (en-tête CCP-HMAC-Signature)
    'hmac_key' => env('CHAPCHAPPAY_HMAC_KEY', ''),

    // true = clé Test (sandbox), false = clé Production
    'sandbox' => env('CHAPCHAPPAY_SANDBOX', true),

    // ── Reversement aux boutiques (paiements en ligne des commandes) ──────────
    // Agent "Règlements" créé dans le tableau de bord ChapChap Pay (type API,
    // permission "API Payout" activée). Sert à envoyer la part de la boutique
    // après une commande payée en ligne — distinct de la clé API ci-dessus qui
    // sert elle à encaisser (E-Commerce) et aux abonnements.
    'payout_access_code' => env('CHAPCHAPPAY_PAYOUT_ACCESS_CODE', ''),
    'payout_pin'          => env('CHAPCHAPPAY_PAYOUT_PIN', ''),

    // Commission Shopio sur chaque commande payée en ligne (en %)
    'platform_fee_percent' => env('CHAPCHAPPAY_PLATFORM_FEE_PERCENT', 1),

    // Tarifs des abonnements en GNF (devise unique — ChapChap Pay opère en Guinée)
    'plans' => [
        'pro'      => 150000, // Plan Pro — boutiques
        'business' => 100000, // Plan Business — entreprises de livraison
    ],
];
