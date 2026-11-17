<?php

namespace App\Support\Ai;

/** L'IA n'a pas pu répondre (clé absente, service saturé, réponse vide…) ; le message s'affiche tel quel. */
class AiUnavailable extends \RuntimeException
{
}
