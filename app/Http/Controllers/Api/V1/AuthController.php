<?php

namespace App\Http\Controllers\Api\V1;

// ─────────────────────────────────────────────────────────────────────────────
// AuthController (API v1 — app mobile Flutter, rôle client uniquement)
//
// Reprend exactement la même logique que l'inscription/connexion du site web
// (app/Http/Controllers/Auth/*), adaptée pour une API sans session : on
// utilise des jetons Sanctum au lieu de cookies, et l'identifiant "en attente
// de vérification OTP" transite dans les requêtes (user_id) au lieu de la
// session serveur.
//
// Ne touche à AUCUN contrôleur web existant — code entièrement nouveau, à
// côté, qui réutilise le même modèle User et le même mail OTP.
// ─────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/register
     * Crée un compte client (non vérifié), envoie un code OTP par email.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique(User::class)->where(fn ($q) => $q->whereNotNull('email_verified_at')),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone'    => ['nullable', 'string', 'max:20'],
            'address'  => ['nullable', 'string', 'max:255'],
            'country'  => ['required', 'string', 'size:2'],
            'ref'      => ['nullable', 'string', 'max:20'],
            'terms'    => ['required', 'accepted'],
        ], [
            'email.unique'   => 'Cette adresse email est déjà utilisée.',
            'terms.required' => "Vous devez accepter les conditions d'utilisation.",
            'terms.accepted' => "Vous devez accepter les conditions d'utilisation.",
        ]);

        // Une éventuelle inscription précédente jamais vérifiée : on repart de zéro
        User::where('email', $request->email)->whereNull('email_verified_at')->delete();

        // Code de parrainage optionnel (même principe que le site : ignoré silencieusement s'il est invalide)
        $referredBy = null;
        if ($request->filled('ref')) {
            $referredBy = User::where('referral_code', strtoupper($request->ref))->value('id');
        }

        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'phone'       => $request->phone,
            'address'     => $request->address,
            'country'     => strtoupper($request->country),
            'role'        => 'client',
            'referred_by' => $referredBy,
        ]);

        Order::attachGuestOrderFromSession($user);

        $this->sendOtp($user);

        return response()->json([
            'message' => 'Compte créé. Un code de vérification a été envoyé par email.',
            'user_id' => $user->id,
        ], 201);
    }

    /**
     * POST /api/v1/auth/verify-otp
     * Confirme le code reçu par email, active le compte et retourne un jeton d'accès.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => ['required', 'integer'],
            'code'    => ['required', 'digits:6'],
        ], [
            'code.digits' => 'Le code doit contenir 6 chiffres.',
        ]);

        $user = User::whereNull('email_verified_at')->find($request->user_id);

        if (!$user) {
            return response()->json(['message' => 'Compte introuvable ou déjà vérifié.'], 404);
        }

        if (!$user->otp_expires_at || now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['message' => 'Ce code a expiré. Demandez-en un nouveau.'], 422);
        }

        if ($request->code !== $user->otp_code) {
            return response()->json(['message' => 'Le code saisi est incorrect.'], 422);
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'otp_code'          => null,
            'otp_expires_at'    => null,
        ])->save();

        return response()->json([
            'message' => 'Compte vérifié.',
            'token'   => $user->createToken('flutter-client')->plainTextToken,
            'user'    => $this->userPayload($user),
        ]);
    }

    /**
     * POST /api/v1/auth/resend-otp
     * Génère et renvoie un nouveau code par email.
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate(['user_id' => ['required', 'integer']]);

        $user = User::whereNull('email_verified_at')->find($request->user_id);

        if (!$user) {
            return response()->json(['message' => 'Compte introuvable ou déjà vérifié.'], 404);
        }

        $this->sendOtp($user);

        return response()->json(['message' => 'Un nouveau code vient d\'être envoyé par email.']);
    }

    /**
     * POST /api/v1/auth/login
     * Connexion par email/mot de passe. Renvoie un jeton d'accès si le compte
     * est déjà vérifié, sinon relance la vérification OTP (comme sur le site).
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Identifiants incorrects.'], 401);
        }

        // Cette app mobile est réservée aux clients (voir SubscriptionService/roles) —
        // les autres rôles restent sur le site + l'app Capacitor pour l'instant.
        if ($user->role !== 'client') {
            return response()->json(['message' => 'Ce compte n\'est pas un compte client.'], 403);
        }

        if (!$user->email_verified_at) {
            $this->sendOtp($user);

            return response()->json([
                'message'              => 'Confirmez votre adresse email pour continuer : un code vient de vous être envoyé.',
                'needs_verification'   => true,
                'user_id'              => $user->id,
            ], 403);
        }

        Order::attachGuestOrderFromSession($user);

        return response()->json([
            'token' => $user->createToken('flutter-client')->plainTextToken,
            'user'  => $this->userPayload($user),
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     * Révoque uniquement le jeton utilisé pour cette requête (l'utilisateur peut
     * rester connecté sur ses autres appareils).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }

    /**
     * GET /api/v1/auth/me
     * Infos du compte connecté (utile au démarrage de l'app pour restaurer la session).
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    private function sendOtp(User $user): void
    {
        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'otp_code'       => $code,
            'otp_expires_at' => now()->addMinutes(10),
        ])->save();

        Mail::to($user->email)->send(new OtpVerificationMail($user->name, $code));
    }

    private function userPayload(User $user): array
    {
        return [
            'id'      => $user->id,
            'name'    => $user->name,
            'email'   => $user->email,
            'phone'   => $user->phone,
            'address' => $user->address,
            'country' => $user->country,
            'loyalty_points' => $user->loyalty_points ?? 0,
            'referral_code'  => $user->referral_code,
        ];
    }
}
