<?php

namespace App\Services\ShopioAI;

/** Réponse finale de l'IA et consommation (tokens) de toute la boucle d'outils. */
class ChatResult
{
    public function __construct(
        public string $text,
        public int $inputTokens = 0,
        public int $outputTokens = 0,
    ) {}
}
