<?php

namespace App\Services\ShopioAI;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Messages\ToolUseBlock;
use App\Support\Ai\AiUnavailable;
use Illuminate\Support\Facades\Log;

/** Claude (SDK Anthropic officiel) avec boucle d'outils. Clé ANTHROPIC_API_KEY lue côté serveur uniquement. */
class ClaudeChatModel implements ChatModel
{
    public const MODEL = 'claude-opus-5-5';

    /** Prix en millionièmes de dollar par token (4 $ / 20 $ par million de tokens) */
    public const INPUT_MICRO_USD  = 4;
    public const OUTPUT_MICRO_USD = 20;

    /** Garde-fou : nombre maximum de tours d'outils pour une seule réponse */
    private const MAX_TOOL_ROUNDS = 5;

    public function __construct(private string $apiKey) {}

    public function run(string $system, array $messages, array $tools, callable $executeTool): ChatResult
    {
        $client = \App\Support\Ai\Anthropic::client($this->apiKey);
        $in = 0;
        $out = 0;

        try {
            $response = $client->messages->create(model: self::MODEL, maxTokens: 1500, system: $system, tools: $tools, messages: $messages);
            $in += $response->usage->inputTokens ?? 0;
            $out += $response->usage->outputTokens ?? 0;

            $rounds = 0;
            while ($response->stopReason === 'tool_use' && $rounds++ < self::MAX_TOOL_ROUNDS) {
                $results = [];
                foreach ($response->content as $block) {
                    if ($block instanceof ToolUseBlock) {
                        $results[] = [
                            'type'      => 'tool_result',
                            'toolUseID' => $block->id,
                            'content'   => json_encode($executeTool($block->name, (array) $block->input), JSON_UNESCAPED_UNICODE) ?: '{}',
                        ];
                    }
                }
                $messages[] = ['role' => 'assistant', 'content' => $response->content];
                $messages[] = ['role' => 'user', 'content' => $results];

                $response = $client->messages->create(model: self::MODEL, maxTokens: 1500, system: $system, tools: $tools, messages: $messages);
                $in += $response->usage->inputTokens ?? 0;
                $out += $response->usage->outputTokens ?? 0;
            }
        } catch (APIStatusException $e) {
            Log::warning('[Shopio IA] Claude indisponible : ' . ($e->type?->value ?? '') . ' ' . $e->getMessage());
            throw new AiUnavailable('Shopio IA est momentanément indisponible.', 0, $e);
        } catch (AiUnavailable $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::warning('[Shopio IA] Appel échoué : ' . $e->getMessage());
            throw new AiUnavailable('Shopio IA n\'a pas pu répondre.', 0, $e);
        }

        $text = '';
        foreach ($response->content as $block) {
            if ($block->type === 'text') {
                $text .= $block->text;
            }
        }
        if (trim($text) === '' || $response->stopReason === 'refusal') {
            throw new AiUnavailable('Shopio IA n\'a pas produit de réponse.');
        }

        return new ChatResult(trim($text), $in, $out);
    }
}
