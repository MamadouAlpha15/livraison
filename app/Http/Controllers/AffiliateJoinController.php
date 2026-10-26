<?php

namespace App\Http\Controllers;

use App\Http\Requests\Affiliation\JoinAffiliateRequest;
use App\Mail\OtpVerificationMail;
use App\Models\User;
use App\Services\AffiliateService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * Page publique « Devenir affilié » (/devenir-affilie), liée depuis l'accueil : une personne sans boutique
 * crée un compte « affilie » pour faire la promotion de Shopio. Même vérification par code e-mail que
 * l'inscription classique ; son code de parrainage est créé à sa première visite du tableau de bord.
 */
class AffiliateJoinController extends Controller
{
    public function show(AffiliateService $affiliates)
    {
        return view('affiliation.join', ['settings' => $affiliates->settings()]);
    }

    public function store(JoinAffiliateRequest $request)
    {
        // Une inscription précédente jamais vérifiée avec cet e-mail : on repart de zéro
        User::where('email', $request->validated('email'))->whereNull('email_verified_at')->delete();

        $user = User::create([
            'name'     => $request->validated('name'),
            'email'    => $request->validated('email'),
            'phone'    => $request->validated('phone'),
            'password' => Hash::make($request->validated('password')),
            'role'     => 'affilie',
        ]);

        $code = (string) random_int(100000, 999999);
        $user->forceFill(['otp_code' => $code, 'otp_expires_at' => now()->addMinutes(10)])->save();
        Mail::to($user->email)->send(new OtpVerificationMail($user->name, $code));

        session(['otp_user_id' => $user->id]);

        return redirect()->route('verification.otp.show');
    }
}
