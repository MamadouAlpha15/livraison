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

    // Tarifs des abonnements en GNF (devise unique — ChapChap Pay opère en Guinée)
    'plans' => [
        'pro'      => 150000, // Plan Pro — boutiques
        'business' => 100000, // Plan Business — entreprises de livraison
    ],
];
