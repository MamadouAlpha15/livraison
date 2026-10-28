<?php

namespace App\Support\Ai;

/**
 * Modèle d'IA qui écrit du texte (côté serveur uniquement). Implémentation réelle : ClaudeTextModel ;
 * sans clé ANTHROPIC_API_KEY, UnconfiguredTextModel répond par une erreur claire (voir AppServiceProvider).
 */
interface TextModel
{
    /**
     * @param array<int, array{role: string, content: string}> $messages
     * @throws AiUnavailable
     */
    public function complete(string $system, array $messages, int $maxTokens = 1500): string;
}
