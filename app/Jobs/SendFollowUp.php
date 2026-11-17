<?php

namespace App\Jobs;

use App\Services\FollowUpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/** Envoi d'une relance due. Unique par relance et verrouillé dans FollowUpService::send() : jamais deux envois. */
class SendFollowUp implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;
    public array $backoff = [60, 300];
    public int $uniqueFor = 900;

    public function __construct(public int $followUpId) {}

    public function uniqueId(): string
    {
        return (string) $this->followUpId;
    }

    public function handle(FollowUpService $followUps): void
    {
        $followUps->send($this->followUpId);
    }
}
