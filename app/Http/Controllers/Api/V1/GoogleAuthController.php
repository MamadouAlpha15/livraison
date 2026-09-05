<?php

namespace App\Http\Controllers\Api\V1;

// ─────────────────────────────────────────────────────────────────────────────
// GoogleAuthController (API v1 — app mobile Flutter)
//
// Le flux OAuth lui-même (redirect → Google → callback) reste sur
// Auth\GoogleController::redirectMobile()/callback() : le client OAuth Google
// n'a qu'un seul redirect_uri enregistré (celui du site), donc on ne peut pas
// faire revenir Google directement sur une route API. Un flag en session fait
// bifurquer callback() vers un retour par jeton (Sanctum) + Android App Links
// au lieu d'une session web classique. Ce contrôleur ne gère que la suite :
// finaliser un nouveau compte, et servir la page de secours du retour vers l'app.
// ─────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /** POST /api/v1/auth/google/complete — finalise un nouveau compte Google (pays + CGU) */
    public function complete(Request $request)
    {
        $request->validate([
            'google_id'    => ['required', 'string'],
            'google_name'  => ['required', 'string', 'max:255'],
            'google_email' => ['required', 'email'],
            'country'      => ['required', 'string', 'size:2'],
            'terms'        => ['required', 'accepted'],
        ], [
            'country.required' => 'Veuillez sélectionner votre pays.',
            'terms.required'   => 'Vous devez accepter les conditions d\'utilisation.',
            'terms.accepted'   => 'Vous devez accepter les conditions d\'utilisation.',
        ]);

        // Déjà créé entre-temps (retour en arrière puis nouvelle tentative) : on réutilise le compte
        $user = User::where('email', $request->google_email)
                    ->orWhere('google_id', $request->google_id)
                    ->first();

        if (!$user) {
            $user = User::create([
                'name'              => $request->google_name,
                'email'             => $request->google_email,
                'google_id'         => $request->google_id,
                'password'          => bcrypt(Str::random(32)),
                'role'              => 'client',
                'country'           => strtoupper($request->country),
                'email_verified_at' => now(),
            ]);
        }

        return response()->json([
            'token' => $user->createToken('flutter-client')->plainTextToken,
            'user'  => [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'phone'          => $user->phone,
                'address'        => $user->address,
                'country'        => $user->country,
                'loyalty_points' => $user->loyalty_points ?? 0,
                'referral_code'  => $user->referral_code,
            ],
        ]);
    }

    /** GET /api/v1/auth/google/return — filet de sécurité si Android App Links n'a pas intercepté */
    public function returnFallback()
    {
        return view('auth.google-mobile-return');
    }
}
