<?php

namespace App\Support\Ai;

/** Utilisé tant qu'aucune clé ANTHROPIC_API_KEY n'est configurée : l'admin voit pourquoi l'IA ne répond pas. */
class UnconfiguredTextModel implements TextModel
{
    public function complete(string $system, array $messages, int $maxTokens = 1500): string
    {
        throw new AiUnavailable('L\'IA de Shopio n\'est pas encore configurée : ajoute la clé ANTHROPIC_API_KEY dans le fichier .env du serveur.');
    }
}
