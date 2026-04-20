<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->must_change_password) {
            // Laisser passer ces routes pour éviter une boucle infinie
            if ($request->routeIs('password.change.form', 'password.change.update', 'logout')) {
                return $next($request);
            }

            return redirect()->route('password.change.form');
        }

        return $next($request);
    }
}
