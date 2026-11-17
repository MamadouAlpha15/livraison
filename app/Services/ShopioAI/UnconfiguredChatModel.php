<?php

namespace App\Services\ShopioAI;

use App\Support\Ai\AiUnavailable;

/** Sans clé ANTHROPIC_API_KEY : Shopio IA ne répond pas et le message de secours prend le relais. */
class UnconfiguredChatModel implements ChatModel
{
    public function run(string $system, array $messages, array $tools, callable $executeTool): ChatResult
    {
        throw new AiUnavailable('Shopio IA n\'est pas configurée : ajoute la clé ANTHROPIC_API_KEY dans le fichier .env du serveur.');
    }
}
