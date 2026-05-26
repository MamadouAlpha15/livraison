<?php

return [

    // X-API-Key header (pk_sandbox_... ou pk_live_...)
    'api_key'        => env('GENUISPAY_PUBLIC_KEY', ''),

    // X-API-Secret header (sk_sandbox_... ou sk_live_...)
    'api_secret'     => env('GENUISPAY_SECRET_KEY', ''),

    // Secret webhook (whsec_sandbox_... ou whsec_live_...)
    'webhook_secret' => env('GENUISPAY_WEBHOOK_SECRET', ''),

    // URL de base de l'API
    'base_url'       => env('GENUISPAY_BASE_URL', 'https://pay.genius.ci/api/v1/merchant'),

    // Seule devise acceptée par l'API GenuisPay
    'currency'       => 'XOF',

    // Taux de conversion GNF → XOF (1 XOF ≈ 13 GNF)
    // Mettre à jour si le taux change significativement
    'gnf_to_xof_rate' => 13.15,

    // true = sandbox, false = production
    'sandbox'        => env('GENUISPAY_SANDBOX', true),

    // Tarifs des abonnements en GNF (devise d'affichage pour les clients guinéens)
    'plans_gnf' => [
        'pro'      => 100000,   // 100 000 GNF/mois  ≈ 7 600 XOF
        'business' => 150000,   // 150 000 GNF/mois  ≈ 11 400 XOF
    ],

    // Tarifs en XOF (envoyés à l'API GenuisPay)
    'plans' => [
        'pro'      => 7600,    // ≈ 100 000 GNF
        'business' => 11400,   // ≈ 150 000 GNF
    ],
];
