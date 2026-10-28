<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\SupportAiService;
use App\Support\Ai\AiUnavailable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Support conversations (équipe Shopio) → bouton « IA » : propose une réponse ou améliore le brouillon.
 * La proposition est seulement renvoyée dans la zone de réponse ; l'admin la relit et l'envoie lui-même.
 */
class SupportAiController extends Controller
{
    public function __invoke(Request $request, SupportTicket $ticket, SupportAiService $ai): JsonResponse
    {
        $data = $request->validate([
            'mode'        => ['required', Rule::in([SupportAiService::MODE_DRAFT, SupportAiService::MODE_IMPROVE])],
            'draft'       => ['nullable', 'string', 'max:10000'],
            'instruction' => ['nullable', 'string', 'max:300'],
        ]);

        try {
            $reply = $ai->suggest($ticket, $data['mode'], $data['draft'] ?? null, $data['instruction'] ?? null);
        } catch (AiUnavailable $e) {
            return response()->json(['error' => $e->getMessage()], 503);
        }

        return response()->json(['reply' => $reply]);
    }
}
