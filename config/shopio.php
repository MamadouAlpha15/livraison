<?php

/*
|--------------------------------------------------------------------------
| Réglages publics de Shopio (page d'accueil)
|--------------------------------------------------------------------------
| SHOPIO_SUPPORT_WHATSAPP : numéro WhatsApp du support, au format international,
| ex. 224620000000. Vide : le bouton WhatsApp n'apparaît pas sur la page d'accueil.
*/
return [
    'support_whatsapp'      => env('SHOPIO_SUPPORT_WHATSAPP', ''),
    'support_whatsapp_text' => env('SHOPIO_SUPPORT_WHATSAPP_TEXT', 'Bonjour Shopio, j’ai une question sur la création de ma boutique.'),

    // Détourage des photos (Boutique → Looks → « Retirer le fond ») : commande Python qui a rembg installé,
    // ex. « python » ou « /usr/bin/python3 ». Vide : le détourage n'est pas proposé.
    // Installation : pip install "rembg[cpu]" pillow-heif scipy
    'rembg_python'          => env('SHOPIO_REMBG_PYTHON', ''),

    // Lien d'invitation du groupe (ou de la chaîne) WhatsApp des vendeurs, ex. https://chat.whatsapp.com/XXXX.
    // Vide : la carte « Rejoins les vendeurs Shopio » n'apparaît pas (Académie et page d'accueil).
    'community_whatsapp'    => env('SHOPIO_COMMUNITY_WHATSAPP', ''),
];
