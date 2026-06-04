<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

class AiDescriptionController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'name'  => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric'],
            'image' => ['nullable', 'string'],
        ]);

        if (!$request->filled('name') && !$request->filled('image')) {
            return response()->json(['error' => 'Ajoutez au moins une photo ou un nom de produit.'], 422);
        }

        // Max 20 générations par vendeur par heure
        $key = 'ai-desc:' . auth()->id();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            return response()->json(['error' => 'Limite atteinte. Réessayez dans une heure.'], 429);
        }
        RateLimiter::hit($key, 3600);

        $name  = $request->name;
        $price = $request->price ? number_format((float) $request->price, 0, ',', ' ') : null;
        $shop  = auth()->user()->shop?->name ?? 'notre boutique';

        $styles = [
            "mets en avant la qualité et la durabilité",
            "mets en avant l'offre et la valeur pour le client",
            "commence par une accroche émotionnelle",
            "mets en avant l'utilité au quotidien",
            "utilise un ton enthousiaste et dynamique",
            "commence par une question qui attire l'attention",
            "mets en avant la rareté et l'exclusivité du produit",
            "utilise un style simple et direct qui inspire confiance",
        ];
        $style = $styles[array_rand($styles)];

        $prompt = "Tu es Shopio IA, un assistant marketing pour une marketplace africaine. "
            . "Génère une description commerciale courte (3-5 phrases), percutante et adaptée au marché local africain. "
            . "Ton chaleureux et vendeur. Ne commence PAS par 'Voici' ou 'Description'. "
            . "Réponds UNIQUEMENT avec la description, sans titre ni explication.\n\n"
            . ($name ? "Nom du produit : {$name}\n" : "Identifie le produit depuis l'image et génère sa description.\n")
            . ($price ? "Prix : {$price} FCFA\n" : '')
            . "Boutique : {$shop}\n"
            . ($request->filled('image') ? "Analyse bien l'image : couleur, matière, forme, état — intègre ces détails.\n" : '')
            . "Style d'écriture : {$style}.\n"
            . "\nDescription (3-5 phrases, français, ton vendeur africain) :";

        // Construire le contenu du message
        $content = [];

        if ($request->filled('image')) {
            $imageData = $request->image;
            if (str_contains($imageData, ',')) {
                [$meta, $base64] = explode(',', $imageData, 2);
                $mimeType = str_contains($meta, 'png')  ? 'image/png'
                          : (str_contains($meta, 'webp') ? 'image/webp' : 'image/jpeg');
            } else {
                $base64   = $imageData;
                $mimeType = 'image/jpeg';
            }

            $content[] = [
                'type'   => 'image',
                'source' => [
                    'type'       => 'base64',
                    'media_type' => $mimeType,
                    'data'       => $base64,
                ],
            ];
        }

        $content[] = ['type' => 'text', 'text' => $prompt];

        try {
            $response = Http::withOptions(['verify' => app()->isProduction()])
                ->timeout(30)
                ->withHeaders([
                    'x-api-key'         => config('services.anthropic.key'),
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'       => 'claude-haiku-4-5-20251001',
                    'max_tokens'  => 300,
                    'temperature' => 1,
                    'messages'    => [
                        ['role' => 'user', 'content' => $content],
                    ],
                ]);

            if (!$response->successful()) {
                \Log::error('Anthropic API error: ' . $response->body());
                $errMsg = $response->json('error.message') ?? 'Erreur API Claude.';
                return response()->json(['error' => app()->isLocal() ? $errMsg : 'Erreur Shopio IA. Réessayez.'], 500);
            }

            $description = trim($response->json('content.0.text') ?? '');

            if (!$description) {
                return response()->json(['error' => 'Réponse vide. Réessayez.'], 500);
            }

            return response()->json(['description' => $description]);

        } catch (\Exception $e) {
            \Log::error('Shopio IA error: ' . $e->getMessage());
            $msg = app()->isLocal() ? $e->getMessage() : 'Erreur Shopio IA. Réessayez.';
            return response()->json(['error' => $msg], 500);
        }
    }
}
