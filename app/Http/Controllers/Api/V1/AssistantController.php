<?php

namespace App\Http\Controllers\Api\V1;

// ─────────────────────────────────────────────────────────────────────────────
// AssistantController (API v1) — assistant d'achat IA. Contrairement au site
// (qui garde l'historique en session serveur), l'app Flutter envoie elle-même
// l'historique complet à chaque message (l'API étant sans session) : plus
// simple, et ça permet à l'app de le garder même après fermeture/réouverture.
// ─────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Controller;
use App\Services\ShoppingAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function chat(Request $request, ShoppingAssistantService $assistant): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'history' => 'nullable|array|max:20',
        ]);

        $user    = $request->user();
        $history = $request->input('history', []);

        try {
            $result = $assistant->chat($history, $request->string('message')->toString(), $user->country, $user->id);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'reply'       => "Désolé, l'assistant est temporairement indisponible. Réessayez dans un instant.",
                'products'    => [],
                'recommended' => [],
                'order'       => null,
            ]);
        }

        return response()->json([
            'reply'       => $result['reply'],
            'products'    => $result['products'],
            'recommended' => $result['recommended'] ?? [],
            'order'       => $result['order'] ?? null,
        ]);
    }
}
