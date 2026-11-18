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
];
