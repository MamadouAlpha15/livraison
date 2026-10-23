<x-guest-layout>

    <h1 class="ab-title">Bon retour sur Shopio</h1>
    <p class="ab-sub">Connectez-vous pour suivre vos commandes ou gérer votre boutique.</p>

    {{-- Message d'information (ex. mot de passe réinitialisé) --}}
    @if (session('status'))
        <div class="f-alert f-alert-success" role="status">{{ session('status') }}</div>
    @endif

    {{-- Erreur générale --}}
    @if ($errors->any())
        <div class="f-alert f-alert-err" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        {{-- Email --}}
        <div class="f-group">
            <label class="f-label" for="email">Adresse email</label>
            <div class="f-icon-wrap">
                <span class="f-ico">{!! \App\Support\IconLibrary::svg('mail', '', 16) !!}</span>
                <input id="email" class="f-input {{ $errors->has('email') ? 'f-input-err':'' }}"
                       type="email" name="email" value="{{ old('email') }}"
                       required autofocus autocomplete="username"
                       placeholder="exemple@email.com">
            </div>
            @error('email')<p class="f-error">{{ $message }}</p>@enderror
        </div>

        {{-- Mot de passe --}}
        <div class="f-group">
            <label class="f-label" for="password">Mot de passe</label>
            <div class="f-icon-wrap f-pw-wrap">
                <span class="f-ico">{!! \App\Support\IconLibrary::svg('lock', '', 16) !!}</span>
                <input id="password" class="f-input {{ $errors->has('password') ? 'f-input-err':'' }}"
                       type="password" name="password"
                       required autocomplete="current-password"
                       placeholder="••••••••">
                <button type="button" class="f-pw-eye" onclick="togglePw('password',this)" aria-label="Afficher le mot de passe" title="Afficher le mot de passe">{!! \App\Support\IconLibrary::svg('eye', '', 16) !!}</button>
            </div>
            @error('password')<p class="f-error">{{ $message }}</p>@enderror
        </div>

        {{-- Remember + forgot --}}
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:22px">
            <label class="f-check">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked':'' }}>
                <span>Se souvenir de moi</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="auth-link" style="font-size:13.5px">Mot de passe oublié ?</a>
            @endif
        </div>

        <button type="submit" class="btn-auth" id="loginSubmitBtn">Se connecter</button>
    </form>

    <div class="g-sep" aria-hidden="true">OU</div>

    {{-- Bouton Google --}}
    <a href="{{ route('google.redirect') }}" id="googleBtn" class="btn-google">
        <svg id="googleBtnIco" width="18" height="18" viewBox="0 0 48 48">
            <path fill="#4285F4" d="M44.5 20H24v8.5h11.8C34.7 33.9 29.8 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 5.1 29.6 3 24 3 12.4 3 3 12.4 3 24s9.4 21 21 21c10.5 0 20-7.5 20-21 0-1.4-.1-2.7-.5-4z"/>
            <path fill="#34A853" d="M6.3 14.7l7 5.1C15.1 16.1 19.2 13 24 13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 5.1 29.6 3 24 3c-7.7 0-14.4 4.4-17.7 11.7z"/>
            <path fill="#FBBC05" d="M24 45c5.6 0 10.5-1.9 14.4-5l-6.7-5.5C29.7 36.3 27 37 24 37c-5.8 0-10.7-3.9-12.4-9.1l-7 5.4C8.1 40.7 15.5 45 24 45z"/>
            <path fill="#EA4335" d="M44.5 20H24v8.5h11.8c-.8 2.9-2.7 5.4-5.3 7l6.7 5.5C41.6 37.5 45 31.3 45 24c0-1.4-.2-2.7-.5-4z"/>
        </svg>
        <span id="googleBtnTxt">Continuer avec Google</span>
    </a>

    <p style="text-align:center;margin-top:22px;font-size:14px;color:var(--g-ink2)">
        Pas encore de compte ?
        <a href="{{ route('register') }}" class="auth-link">Créer un compte</a>
    </p>

    <script>
        const ICO_EYE     = @json(\App\Support\IconLibrary::svg('eye', '', 16));
        const ICO_EYE_OFF = @json(\App\Support\IconLibrary::svg('eye-off', '', 16));

        function togglePw(id, btn) {
            const inp = document.getElementById(id);
            if (inp.type === 'password') { inp.type = 'text';     btn.innerHTML = ICO_EYE_OFF; btn.setAttribute('aria-label', 'Masquer le mot de passe'); }
            else                         { inp.type = 'password'; btn.innerHTML = ICO_EYE;     btn.setAttribute('aria-label', 'Afficher le mot de passe'); }
        }

        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = document.getElementById('loginSubmitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="btn-spinner"></span> Connexion…';
        });

        {{-- Dans l'app Capacitor, le flux Google doit s'ouvrir dans le vrai navigateur
             (Google bloque les webviews intégrées) plutôt que dans la webview de l'app —
             sinon la session Google finit dans un contexte que l'app ne peut pas lire. --}}
        const isCapacitorApp = !!(window.Capacitor && window.Capacitor.isNativePlatform && window.Capacitor.isNativePlatform());
        const googleBtn = document.getElementById('googleBtn');
        const googleBtnOriginalHtml = googleBtn.innerHTML;

        if (isCapacitorApp) {
            googleBtn.href = "{{ route('google.redirect.app') }}";
        }

        function resetGoogleBtn() {
            googleBtn.style.pointerEvents = '';
            googleBtn.style.opacity = '';
            googleBtn.innerHTML = googleBtnOriginalHtml;
        }

        googleBtn.addEventListener('click', function (e) {
            this.style.pointerEvents = 'none';
            this.style.opacity = '.7';
            document.getElementById('googleBtnIco').outerHTML = '<span class="btn-spinner-dark"></span>';
            document.getElementById('googleBtnTxt').textContent = 'Connexion…';

            if (isCapacitorApp) {
                e.preventDefault();
                window.open(this.href, '_system');
            }
        });

        {{-- Si l'utilisateur revient dans l'app sans avoir terminé (ex: fermé le
             navigateur externe), le bouton resterait sinon bloqué en "Connexion…"
             indéfiniment, puisque cette page-ci ne navigue jamais ailleurs. --}}
        if (isCapacitorApp) {
            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'visible') resetGoogleBtn();
            });
        }
    </script>

</x-guest-layout>
