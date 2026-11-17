<?php

namespace App\Support\Ai;

use Anthropic\Client;

/**
 * Accès à l'API Claude pour tout Shopio. Une clé « utilisateur » (sk-ant-usr-…) n'est rattachée à aucun
 * espace de travail : chaque appel doit alors indiquer ANTHROPIC_WORKSPACE_ID (en-tête anthropic-workspace-id).
 */
class Anthropic
{
    public static function client(?string $apiKey = null): Client
    {
        $headers = self::workspaceHeader();
        return new Client(
            apiKey: $apiKey ?? (string) config('services.anthropic.key'),
            requestOptions: $headers ? ['extraHeaders' => $headers] : null,
        );
    }

    /** En-tête de l'espace de travail, s'il est configuré */
    public static function workspaceHeader(): array
    {
        $id = config('services.anthropic.workspace_id');
        return filled($id) ? ['anthropic-workspace-id' => (string) $id] : [];
    }
}
