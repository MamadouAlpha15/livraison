<?php

// ============================================================
// FICHIER : app/Http/Controllers/Client/AssistantController.php
// RÔLE    : Point d'entrée HTTP de l'assistant d'achat IA (chat).
//           L'historique de conversation est gardé en session (par
//           client connecté), limité aux 20 derniers messages pour
//           contrôler le coût des appels à l'API Claude.
// ============================================================

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\ShoppingAssistantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssistantController extends Controller
{
    private const SESSION_KEY = 'assistant_history';
    private const MAX_HISTORY = 20;

    public function chat(Request $request, ShoppingAssistantService $assistant)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $history = session(self::SESSION_KEY, []);
        $country = Auth::user()->country ?? null;

        try {
            $result = $assistant->chat($history, $request->string('message')->toString(), $country, Auth::id());
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'reply'       => "Désolé, l'assistant est temporairement indisponible. Réessayez dans un instant.",
                'products'    => [],
                'recommended' => [],
                'order'       => null,
            ], 200);
        }

        $history[] = ['role' => 'user', 'content' => $request->string('message')->toString()];
        $history[] = ['role' => 'assistant', 'content' => $result['reply']];
        $history = array_slice($history, -self::MAX_HISTORY);
        session([self::SESSION_KEY => $history]);

        return response()->json([
            'reply'       => $result['reply'],
            'products'    => $result['products'],
            'recommended' => $result['recommended'] ?? [],
            'order'       => $result['order'] ?? null,
        ]);
    }

    /** Repart d'une conversation vierge (bouton "Nouvelle conversation"). */
    public function reset()
    {
        session()->forget(self::SESSION_KEY);
        return response()->json(['ok' => true]);
    }
}
