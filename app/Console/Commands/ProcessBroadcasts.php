<?php

namespace App\Console\Commands;

use App\Mail\BroadcastMail;
use App\Models\Broadcast;
use App\Models\BroadcastRecipient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ProcessBroadcasts extends Command
{
    /**
     * Traite un lot de destinataires "en attente" (toutes campagnes confondues).
     * Pensée pour être appelée par une tâche cron toutes les minutes — n'envoie
     * qu'un nombre limité d'emails à chaque passage pour ne jamais bloquer
     * longtemps et pour rester raisonnable vis-à-vis du serveur SMTP.
     */
    protected $signature = 'broadcasts:process {--limit=40}';

    protected $description = "Envoie un lot d'emails en attente pour les annonces (diffusions) SuperAdmin";

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $recipients = BroadcastRecipient::where('status', 'pending')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($recipients->isEmpty()) {
            $this->info('Rien à envoyer.');
            return self::SUCCESS;
        }

        $touchedBroadcastIds = [];

        foreach ($recipients as $recipient) {
            $touchedBroadcastIds[$recipient->broadcast_id] = true;
            $broadcast = $recipient->broadcast;

            try {
                Mail::to($recipient->email)->send(
                    new BroadcastMail($broadcast->subject, $broadcast->body, $recipient->name ?: 'là')
                );
                $recipient->update(['status' => 'sent', 'sent_at' => now(), 'error' => null]);
                $broadcast->increment('sent_count');
                $this->line("  ✓ {$recipient->email}");
            } catch (\Throwable $e) {
                $recipient->update(['status' => 'failed', 'error' => substr($e->getMessage(), 0, 250)]);
                $broadcast->increment('failed_count');
                $this->line("  ✗ {$recipient->email} — " . $e->getMessage());
            }
        }

        // Marque "done" les campagnes dont tous les destinataires sont traités.
        foreach (array_keys($touchedBroadcastIds) as $broadcastId) {
            $stillPending = BroadcastRecipient::where('broadcast_id', $broadcastId)
                ->where('status', 'pending')
                ->exists();
            if (!$stillPending) {
                Broadcast::where('id', $broadcastId)->update(['status' => 'done']);
            }
        }

        $this->info(count($recipients) . ' email(s) traité(s).');
        return self::SUCCESS;
    }
}
