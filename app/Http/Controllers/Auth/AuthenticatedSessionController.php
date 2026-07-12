<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        if ($request->filled('redirect')) {
            $target = $request->redirect;
            if (str_starts_with($target, url('/'))) {
                session(['product_redirect' => $target]);
            }
        }

        // Commande passée sans compte : à rattacher au compte une fois connecté
        if ($request->filled('order_id')) {
            session(['link_order_id' => $request->order_id]);
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        Order::attachGuestOrderFromSession($user);

        if ($user->role === 'superadmin') return redirect()->route('admin.dashboard');
        if ($user->role === 'admin')      return redirect()->route('boutique.dashboard');
        if ($user->role === 'company')    return redirect()->route('company.dashboard');
        if ($user->role === 'livreur')    return redirect()->route('livreur.dashboard');
        if ($user->role === 'vendeur')    return redirect()->route('vendeur.dashboard');

        // Client : retourner sur la page produit s'il venait d'un lien partagé
        if ($user->role === 'client' && session('product_redirect')) {
            $target = session()->pull('product_redirect');
            return redirect($target);
        }

        return redirect()->route('client.dashboard');
        
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
