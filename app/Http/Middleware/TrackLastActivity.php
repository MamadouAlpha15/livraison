<?php

namespace App\Http\Middleware;

use App\Services\InactivityReminderService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Note la dernière visite des clients et des entreprises de livraison (site et application),
 * au plus toutes les 15 minutes : sert à repérer les inactifs à relancer et à compter leur retour.
 */
class TrackLastActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Sur l'API, le jeton n'est pas encore lu à ce stade : on le lit seulement s'il est envoyé
            $user = $request->user() ?? ($request->bearerToken() ? auth('sanctum')->user() : null);
            if ($user && in_array($user->role, ['client', 'company'], true)) {
                app(InactivityReminderService::class)->touch($user);
            }
        } catch (\Throwable $e) {
            report($e);
        }
        return $next($request);
    }
}
