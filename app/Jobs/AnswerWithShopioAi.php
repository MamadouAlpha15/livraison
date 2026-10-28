<?php

namespace App\Jobs;

use App\Models\ShopMessage;
use App\Services\ShopioAI\ShopioAssistant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Réponse de Shopio IA à un message client, en file d'attente : 60 s au plus par essai, une nouvelle
 * tentative en cas d'erreur, puis le message de secours (« le vendeur te répond très vite »).
 */
class AnswerWithShopioAi implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 2;
    public int $timeout = 60;
    public array $backoff = [10];
    public int $uniqueFor = 300;

    public function __construct(public int $messageId) {}

    public function uniqueId(): string
    {
        return (string) $this->messageId;
    }

    public function handle(ShopioAssistant $assistant): void
    {
        $message = ShopMessage::find($this->messageId);
        if ($message) {
            $assistant->handle($message);
        }
    }

    public function failed(\Throwable $e): void
    {
        $message = ShopMessage::find($this->messageId);
        if ($message) {
            app(ShopioAssistant::class)->fallback($message);
        }
    }
}
