<?php

namespace App\Http\Middleware;

use App\Services\AffiliateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lien de parrainage d'une boutique ou d'un affilié : ?ref=CODE. Si le code est valide, il est gardé
 * dans le cookie shopio_ref (durée réglable, 30 jours au départ) et le clic est compté.
 * Les codes de parrainage des clients (users.referral_code) passent aussi par ?ref= et ne sont pas concernés.
 */
class TrackReferralLink
{
    public function handle(Request $request, Closure $next): Response
    {
        $code = $request->query('ref');
        if ($request->isMethod('GET') && is_string($code) && preg_match('/^[A-Za-z0-9]{4,20}$/', $code)) {
            try {
                app(AffiliateService::class)->trackLink($code, $request);
            } catch (\Throwable $e) {
                report($e);
            }
        }
        return $next($request);
    }
}
