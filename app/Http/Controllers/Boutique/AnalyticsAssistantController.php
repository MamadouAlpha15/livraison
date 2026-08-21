<?php

// ============================================================
// FICHIER : app/Http/Controllers/Boutique/AnalyticsAssistantController.php
// RÔLE    : Point d'entrée HTTP de l'assistant d'analyse des ventes (vendeur).
// ============================================================

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use App\Services\VendorAnalyticsAssistantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsAssistantController extends Controller
{
    private const SESSION_KEY = 'vendor_assistant_history';
    private const MAX_HISTORY = 20;

    public function chat(Request $request, VendorAnalyticsAssistantService $assistant)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        $shop = $user->shop;

        if (!$shop) {
            return response()->json([
                'reply' => "Vous devez avoir une boutique pour utiliser cet assistant.",
            ], 200);
        }

        $history = session(self::SESSION_KEY, []);

        try {
            $result = $assistant->chat($history, $request->string('message')->toString(), $shop->id, $shop->name);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'reply' => "Désolé, l'assistant est temporairement indisponible. Réessayez dans un instant.",
            ], 200);
        }

        $history[] = ['role' => 'user', 'content' => $request->string('message')->toString()];
        $history[] = ['role' => 'assistant', 'content' => $result['reply']];
        $history = array_slice($history, -self::MAX_HISTORY);
        session([self::SESSION_KEY => $history]);

        return response()->json([
            'reply' => $result['reply'],
        ]);
    }

    public function reset()
    {
        session()->forget(self::SESSION_KEY);
        return response()->json(['ok' => true]);
    }
}
