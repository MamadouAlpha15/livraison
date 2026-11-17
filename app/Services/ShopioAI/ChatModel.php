<?php

namespace App\Services\ShopioAI;

use App\Support\Ai\AiUnavailable;

/**
 * Modèle d'IA qui sait appeler des outils. Implémentation réelle : ClaudeChatModel (côté serveur) ;
 * dans les tests, un modèle simulé. L'IA n'accède jamais à la base : elle demande des outils, que
 * $executeTool exécute après contrôle des permissions.
 */
interface ChatModel
{
    /**
     * @param array<int, array{name:string, description:string, inputSchema:array}> $tools
     * @param callable(string $tool, array $input): array $executeTool
     * @throws AiUnavailable
     */
    public function run(string $system, array $messages, array $tools, callable $executeTool): ChatResult;
}
