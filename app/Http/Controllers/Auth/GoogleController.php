<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    private function driver()
    {
        return Socialite::driver('google')
            ->stateless()
            ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
    }

    public function redirect()
    {
        return $this->driver()->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = $this->driver()->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Connexion Google échouée.');
        }

        // Utilisateur existant avec cet email
        $user = User::where('email', $googleUser->getEmail())
                    ->orWhere('google_id', $googleUser->getId())
                    ->first();

        if ($user) {
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
            Auth::login($user, true);
            return $this->redirectToDashboard($user);
        }

        // Nouvel utilisateur → stocker dans la session et choisir rôle + pays
        session([
            'google_id'     => $googleUser->getId(),
            'google_name'   => $googleUser->getName(),
            'google_email'  => $googleUser->getEmail(),
            'google_avatar' => $googleUser->getAvatar(),
        ]);

        return redirect()->route('google.setup');
    }

    public function setup()
    {
        if (!session('google_email')) {
            return redirect()->route('login');
        }

        return view('auth.google-setup', [
            'googleName'  => session('google_name'),
            'googleEmail' => session('google_email'),
            'googleAvatar'=> session('google_avatar'),
            'countries'   => $this->countries(),
        ]);
    }

    public function storeSetup(Request $request)
    {
        if (!session('google_email')) {
            return redirect()->route('login');
        }

        $request->validate([
            'role'    => ['required', 'in:client,admin,company,livreur'],
            'country' => ['required', 'string', 'size:2'],
        ], [
            'role.required'    => 'Veuillez choisir votre type de compte.',
            'role.in'          => 'Type de compte invalide.',
            'country.required' => 'Veuillez sélectionner votre pays.',
        ]);

        $user = User::create([
            'name'              => session('google_name'),
            'email'             => session('google_email'),
            'google_id'         => session('google_id'),
            'password'          => bcrypt(Str::random(32)),
            'role'              => $request->role,
            'country'           => strtoupper($request->country),
            'email_verified_at' => now(),
        ]);

        session()->forget(['google_id', 'google_name', 'google_email', 'google_avatar']);

        Auth::login($user, true);

        return $this->redirectToDashboard($user);
    }

    private function redirectToDashboard(User $user)
    {
        return match($user->role) {
            'superadmin' => redirect()->route('admin.dashboard'),
            'admin'      => redirect()->route('boutique.dashboard'),
            'company'    => redirect()->route('company.dashboard'),
            'livreur'    => redirect()->route('livreur.dashboard'),
            'vendeur'    => redirect()->route('vendeur.dashboard'),
            'employe'    => redirect()->route('employe.dashboard'),
            default      => redirect()->route('client.dashboard'),
        };
    }

    private function countries(): array
    {
        return [
            'BJ'=>['🇧🇯','Bénin'],'BF'=>['🇧🇫','Burkina Faso'],'CV'=>['🇨🇻','Cap-Vert'],
            'CI'=>['🇨🇮',"Côte d'Ivoire"],'GM'=>['🇬🇲','Gambie'],'GH'=>['🇬🇭','Ghana'],
            'GN'=>['🇬🇳','Guinée'],'GW'=>['🇬🇼','Guinée-Bissau'],'GQ'=>['🇬🇶','Guinée équatoriale'],
            'LR'=>['🇱🇷','Libéria'],'ML'=>['🇲🇱','Mali'],'MR'=>['🇲🇷','Mauritanie'],
            'NE'=>['🇳🇪','Niger'],'NG'=>['🇳🇬','Nigeria'],'SN'=>['🇸🇳','Sénégal'],
            'SL'=>['🇸🇱','Sierra Leone'],'TG'=>['🇹🇬','Togo'],
            'AO'=>['🇦🇴','Angola'],'CM'=>['🇨🇲','Cameroun'],'CF'=>['🇨🇫','Centrafrique'],
            'TD'=>['🇹🇩','Tchad'],'CG'=>['🇨🇬','Congo'],'CD'=>['🇨🇩','RD Congo'],
            'GA'=>['🇬🇦','Gabon'],'DZ'=>['🇩🇿','Algérie'],'EG'=>['🇪🇬','Égypte'],
            'MA'=>['🇲🇦','Maroc'],'TN'=>['🇹🇳','Tunisie'],
            'FR'=>['🇫🇷','France'],'BE'=>['🇧🇪','Belgique'],'CH'=>['🇨🇭','Suisse'],
            'CA'=>['🇨🇦','Canada'],'US'=>['🇺🇸','États-Unis'],
        ];
    }
}
