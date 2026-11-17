<?php

namespace App\Services\ShopioAI;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopAiSetting;
use App\Models\User;

/** Ce que l'IA a le droit de voir pour une réponse : la boutique de la conversation et le client connecté. */
class AiContext
{
    /** @var array<int, array> résultats d'outils de cette réponse (pour vérifier qu'elle n'invente rien) */
    public array $toolResults = [];
    public bool $handedOff = false;

    public function __construct(
        public Shop $shop,
        public ?User $client,
        public ShopAiSetting $settings,
        public ?Product $product = null,
        public ?int $messageId = null,
    ) {}
}
