<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictEmployeeToOrders
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user && $user->role === 'employe' && $user->orders_only) {
            $routeName = $request->route()?->getName() ?? '';

            if (!str_starts_with($routeName, 'employe.orders.')) {
                return redirect()->route('employe.orders.index')
                    ->with('danger', "Accès limité : le propriétaire de la boutique vous a donné accès uniquement aux commandes.");
            }
        }

        return $next($request);
    }
}
