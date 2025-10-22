<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ScopeShop
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // superadmin a accès à tout
        if ($user && $user->role === 'superadmin') {
            return $next($request);
        }

        // autres doivent être rattachés à une boutique
        if (!$user || !$user->shop_id) {
            abort(403, 'Aucune boutique associée.');
        }

        // rendre l’ID de boutique dispo globalement
        app()->instance('scoped.shop_id', $user->shop_id);

        return $next($request);
    }
}
