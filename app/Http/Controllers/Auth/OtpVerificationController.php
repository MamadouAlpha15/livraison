<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class OtpVerificationController extends Controller
{
    /**
     * Affiche le formulaire de saisie du code envoyé par email.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser();

        if (!$user) {
            return redirect()->route('register');
        }

        return view('auth.verify-otp', ['email' => $user->email]);
    }

    /**
     * Vérifie le code saisi et finalise l'inscription (login + redirection).
     */
    public function verify(Request $request): RedirectResponse
    {
        $user = $this->pendingUser();

        if (!$user) {
            return redirect()->route('register');
        }

        $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'Veuillez saisir le code reçu par email.',
            'code.digits'   => 'Le code doit contenir 6 chiffres.',
        ]);

        if (!$user->otp_expires_at || now()->greaterThan($user->otp_expires_at)) {
            return back()->withErrors(['code' => 'Ce code a expiré. Cliquez sur "Renvoyer le code".']);
        }

        if ($request->code !== $user->otp_code) {
            return back()->withErrors(['code' => 'Le code saisi est incorrect.']);
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'otp_code'          => null,
            'otp_expires_at'    => null,
        ])->save();

        session()->forget('otp_user_id');

        Auth::login($user);

        if ($user->role === 'client' && session('product_redirect')) {
            return redirect(session()->pull('product_redirect'));
        }

        return match ($user->role) {
            'admin'   => redirect()->route('boutique.dashboard'),
            'company' => redirect()->route('company.dashboard'),
            'livreur' => redirect()->route('livreur.dashboard'),
            default   => redirect()->route('client.dashboard'),
        };
    }

    /**
     * Génère un nouveau code et le renvoie par email.
     */
    public function resend(Request $request): RedirectResponse
    {
        $user = $this->pendingUser();

        if (!$user) {
            return redirect()->route('register');
        }

        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'otp_code'       => $code,
            'otp_expires_at' => now()->addMinutes(10),
        ])->save();

        Mail::to($user->email)->send(new OtpVerificationMail($user->name, $code));

        return back()->with('status', 'Un nouveau code vient d\'être envoyé à votre adresse email.');
    }

    private function pendingUser(): ?User
    {
        $userId = session('otp_user_id');

        if (!$userId) {
            return null;
        }

        return User::whereNull('email_verified_at')->find($userId);
    }
}
