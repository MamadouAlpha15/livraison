<?php

namespace App\Support\Ai;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIStatusException;
use Illuminate\Support\Facades\Log;

/** Texte écrit par Claude (SDK Anthropic officiel), clé ANTHROPIC_API_KEY lue côté serveur. */
class ClaudeTextModel implements TextModel
{
    public const MODEL = 'claude-opus-5-5';

    public function __construct(private string $apiKey) {}

    public function complete(string $system, array $messages, int $maxTokens = 1500): string
    {
        try {
            $response = Anthropic::client($this->apiKey)->messages->create(
                model: self::MODEL,
                maxTokens: $maxTokens,
                system: $system,
                messages: $messages,
            );
        } catch (APIStatusException $e) {
            Log::warning('[IA] Claude indisponible : ' . ($e->type?->value ?? '') . ' ' . $e->getMessage());
            throw new AiUnavailable('L\'IA est momentanément indisponible. Réessaie dans un instant ou écris ta réponse toi-même.', 0, $e);
        } catch (\Throwable $e) {
            Log::warning('[IA] Appel Claude échoué : ' . $e->getMessage());
            throw new AiUnavailable('L\'IA n\'a pas pu répondre. Réessaie dans un instant ou écris ta réponse toi-même.', 0, $e);
        }

        $text = '';
        foreach ($response->content as $block) {
            if ($block->type === 'text') {
                $text .= $block->text;
            }
        }
        $text = trim($text);
        if ($text === '' || $response->stopReason === 'refusal') {
            throw new AiUnavailable('L\'IA n\'a pas proposé de réponse pour cette conversation : écris-la toi-même.');
        }

        Log::info('[IA] Réponse support rédigée', [
            'input_tokens'  => $response->usage->inputTokens ?? null,
            'output_tokens' => $response->usage->outputTokens ?? null,
        ]);
        return $text;
    }
}
