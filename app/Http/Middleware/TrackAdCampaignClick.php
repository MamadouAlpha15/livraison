<?php

namespace App\Http\Middleware;

use App\Support\Campaigns\AdAttribution;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Clic sur un élément sponsorisé ou un lien de campagne réseaux sociaux : ?c=<id de campagne>.
 * Compte le clic et mémorise la campagne 7 jours pour attribuer la commande (App\Support\Campaigns\AdAttribution).
 */
class TrackAdCampaignClick
{
    public function handle(Request $request, Closure $next): Response
    {
        $c = $request->query('c');
        if ($request->isMethod('GET') && is_string($c) && ctype_digit($c) && strlen($c) <= 10) {
            AdAttribution::registerClick((int) $c, $request, $request->user());
        }
        return $next($request);
    }
}
