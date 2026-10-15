<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PushService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    private function driver()
    {
        return Socialite::driver('google')
            ->stateless()
            ->setHttpClient(new \GuzzleHttp\Client(['verify' => true]))
            // Sans ce paramètre, Google reconnecte silencieusement au compte déjà
            // actif sur l'appareil/navigateur sans jamais proposer le choix — gênant
            // si l'utilisateur a plusieurs comptes Google et veut en choisir un autre
            // (ex: créer un compte Shopio avec une adresse différente).
            ->with(['prompt' => 'select_account']);
    }

    public function redirect(Request $request)
    {
        // Sauvegarder en session : le callback n'a pas accès aux query params d'origine
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

        return $this->driver()->redirect();
    }

    /**
     * GET /auth/google/mobile — point d'entrée pour l'app Flutter (ouvert dans
     * le navigateur externe). Même redirect_uri Google que le site (le client
     * OAuth n'en a qu'un seul d'enregistré) ; un flag en session fait bifurquer
     * callback() vers un retour par jeton au lieu d'une session web classique.
     */
    public function redirectMobile(Request $request)
    {
        session(['mobile_google_auth' => true]);

        return $this->driver()->redirect();
    }

    /**
     * GET /auth/google/app — point d'entrée pour l'app Capacitor, ouvert
     * directement dans le navigateur externe (voir auth.php). Un flag en
     * session (qui survit puisque tout le flux reste dans ce même navigateur)
     * fait bifurquer callback() vers un retour par URL signée + Android App
     * Links, pour établir la session DANS l'app plutôt que dans le navigateur.
     */
    public function redirectCapacitor(Request $request)
    {
        session(['capacitor_google_auth' => true]);

        return $this->driver()->redirect();
    }

    public function callback()
    {
        $isCapacitor = session()->pull('capacitor_google_auth', false);
        $isMobile    = session()->pull('mobile_google_auth', false);

        try {
            $googleUser = $this->driver()->user();
        } catch (\Exception $e) {
            if ($isCapacitor) {
                return redirect()->route('login')->with('error', 'Connexion Google échouée.');
            }
            if ($isMobile) {
                return $this->returnToMobileApp(['error' => 'Connexion Google échouée.']);
            }
            return redirect()->route('login')->with('error', 'Connexion Google échouée.');
        }

        // Utilisateur existant avec cet email
        $user = User::where('email', $googleUser->getEmail())
                    ->orWhere('google_id', $googleUser->getId())
                    ->first();

        if ($user) {
            $updates = [];
            if (!$user->google_id) {
                $updates['google_id'] = $googleUser->getId();
            }
            // Google confirme la propriété de l'email → on considère l'email vérifié
            if (!$user->email_verified_at) {
                $updates['email_verified_at'] = now();
            }
            if ($updates) {
                $user->update($updates);
            }

            if ($isMobile) {
                // App mobile réservée aux clients (voir Api\V1\AuthController::login)
                if ($user->role !== 'client') {
                    return $this->returnToMobileApp(['error' => "Ce compte n'est pas un compte client."]);
                }
                return $this->returnToMobileApp(['token' => $user->createToken('flutter-client')->plainTextToken]);
            }

            if ($isCapacitor) {
                return redirect(URL::temporarySignedRoute('google.mobile-bridge', now()->addMinutes(3), ['user' => $user->id]));
            }

            Auth::login($user, true);
            return $this->redirectToDashboard($user);
        }

        // Nouvel utilisateur Capacitor → les infos Google transitent par une URL
        // signée (pas la session, perdue entre navigateur externe et webview de
        // l'app) jusqu'à ce qu'une vraie session s'établisse DANS l'app.
        if ($isCapacitor) {
            return redirect(URL::temporarySignedRoute('google.mobile-bridge-setup', now()->addMinutes(3), [
                'google_id'    => $googleUser->getId(),
                'google_name'  => $googleUser->getName(),
                'google_email' => $googleUser->getEmail(),
                'google_avatar'=> $googleUser->getAvatar(),
            ]));
        }

        // Nouvel utilisateur mobile → pas de session web, tout se termine dans
        // l'app via POST /api/v1/auth/google/complete (pays + CGU)
        if ($isMobile) {
            return $this->returnToMobileApp([
                'needs_setup'  => '1',
                'google_id'    => $googleUser->getId(),
                'google_name'  => $googleUser->getName(),
                'google_email' => $googleUser->getEmail(),
            ]);
        }

        // Nouvel utilisateur (site) → stocker dans la session et choisir rôle + pays
        session([
            'google_id'     => $googleUser->getId(),
            'google_name'   => $googleUser->getName(),
            'google_email'  => $googleUser->getEmail(),
            'google_avatar' => $googleUser->getAvatar(),
        ]);

        return redirect()->route('google.setup');
    }

    /**
     * GET /auth/mobile-bridge — établit la vraie session DANS l'app Capacitor.
     * Ouverte via une URL signée (pas de session/cookie requis) interceptée par
     * Android App Links puis chargée dans la webview de l'app elle-même
     * (MainActivity::handleIncomingUrl) : Auth::login() ici pose donc le cookie
     * de session dans le bon contexte (celui de l'app), pas celui du navigateur
     * externe utilisé pour le passage par Google.
     */
    public function mobileBridge(Request $request)
    {
        $user = User::find($request->query('user'));
        if (!$user) {
            return redirect()->route('login')->with('error', 'Lien de connexion expiré, réessayez.');
        }

        Auth::login($user, true);
        return $this->redirectToDashboard($user);
    }

    /**
     * GET /auth/mobile-bridge/setup — même principe que mobileBridge() mais pour
     * un nouvel utilisateur Google : transfère les infos Google (transmises dans
     * l'URL signée) vers la session DANS l'app, puis affiche normalement la
     * page de choix rôle + pays.
     */
    public function mobileBridgeSetup(Request $request)
    {
        $request->validate([
            'google_id'    => ['required', 'string'],
            'google_name'  => ['required', 'string'],
            'google_email' => ['required', 'email'],
        ]);

        session([
            'google_id'     => $request->query('google_id'),
            'google_name'   => $request->query('google_name'),
            'google_email'  => $request->query('google_email'),
            'google_avatar' => $request->query('google_avatar'),
        ]);

        return redirect()->route('google.setup');
    }

    /**
     * Redirige vers une URL https://shopio-app.com/... interceptée par l'app
     * mobile via Android App Links (déjà en place pour l'app Capacitor) — sert
     * aussi de page de secours si l'app n'intercepte pas.
     */
    private function returnToMobileApp(array $params)
    {
        return redirect(route('api.v1.auth.google.return') . '?' . http_build_query($params));
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
            'terms'   => ['required', 'accepted'],
        ], [
            'role.required'    => 'Veuillez choisir votre type de compte.',
            'role.in'          => 'Type de compte invalide.',
            'country.required' => 'Veuillez sélectionner votre pays.',
            'terms.required'   => 'Vous devez accepter les conditions d\'utilisation pour créer un compte.',
            'terms.accepted'   => 'Vous devez accepter les conditions d\'utilisation pour créer un compte.',
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

        try {
            app(PushService::class)->notifySuperadmins(
                'Nouvel inscrit',
                $user->name . ' vient de s\'inscrire (' . $user->role . ')',
                route('admin.users.index')
            );
        } catch (\Throwable $e) {}

        Auth::login($user, true);

        return $this->redirectToDashboard($user);
    }

    private function redirectToDashboard(User $user)
    {
        // Signaler au layout qu'on vient de Google → reset PWA modal côté JS
        session()->flash('google_just_authed', true);

        Order::attachGuestOrderFromSession($user);

        if ($user->role === 'client' && session('product_redirect')) {
            return redirect(session()->pull('product_redirect'));
        }

        return match($user->role) {
            'superadmin' => redirect()->route('admin.dashboard'),
            'staff'      => redirect()->route('admin.dashboard'),
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
