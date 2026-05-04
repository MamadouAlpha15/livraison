<x-guest-layout>

    <div class="ab-title">Bon retour 👋</div>
    <p class="ab-sub">Connectez-vous à votre compte Shopio</p>

    {{-- Status message --}}
    @if (session('status'))
        <div class="f-alert f-alert-success">{{ session('status') }}</div>
    @endif

    {{-- Errors globaux --}}
    @if ($errors->any())
        <div class="f-alert" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;margin-bottom:18px">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email --}}
        <div class="f-group">
            <label class="f-label" for="email">Adresse email</label>
            <div class="f-icon-wrap">
                <span class="f-ico">✉️</span>
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
                <span class="f-ico">🔒</span>
                <input id="password" class="f-input {{ $errors->has('password') ? 'f-input-err':'' }}"
                       type="password" name="password"
                       required autocomplete="current-password"
                       placeholder="••••••••">
                <button type="button" class="f-pw-eye" onclick="togglePw('password',this)" title="Afficher">👁️</button>
            </div>
            @error('password')<p class="f-error">{{ $message }}</p>@enderror
        </div>

        {{-- Remember + forgot --}}
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:24px">
            <label class="f-check">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked':'' }}>
                <span>Se souvenir de moi</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="auth-link" style="font-size:13px">Mot de passe oublié ?</a>
            @endif
        </div>

        <button type="submit" class="btn-auth">Se connecter →</button>
    </form>

    {{-- Séparateur --}}
    <div style="display:flex;align-items:center;gap:10px;margin:20px 0;">
        <div style="flex:1;height:1px;background:#e5e7eb;"></div>
        <span style="font-size:12px;color:#9ca3af;font-weight:600;">OU</span>
        <div style="flex:1;height:1px;background:#e5e7eb;"></div>
    </div>

    {{-- Bouton Google --}}
    <a href="{{ route('google.redirect') }}"
       style="display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:11px 16px;border-radius:10px;border:1.5px solid #e5e7eb;background:#fff;color:#374151;font-size:14px;font-weight:600;text-decoration:none;transition:all .2s;box-shadow:0 1px 3px rgba(0,0,0,.06);"
       onmouseover="this.style.borderColor='#4285F4';this.style.boxShadow='0 3px 10px rgba(66,133,244,.2)'"
       onmouseout="this.style.borderColor='#e5e7eb';this.style.boxShadow='0 1px 3px rgba(0,0,0,.06)'">
        <svg width="18" height="18" viewBox="0 0 48 48">
            <path fill="#4285F4" d="M44.5 20H24v8.5h11.8C34.7 33.9 29.8 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 5.1 29.6 3 24 3 12.4 3 3 12.4 3 24s9.4 21 21 21c10.5 0 20-7.5 20-21 0-1.4-.1-2.7-.5-4z"/>
            <path fill="#34A853" d="M6.3 14.7l7 5.1C15.1 16.1 19.2 13 24 13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 5.1 29.6 3 24 3c-7.7 0-14.4 4.4-17.7 11.7z"/>
            <path fill="#FBBC05" d="M24 45c5.6 0 10.5-1.9 14.4-5l-6.7-5.5C29.7 36.3 27 37 24 37c-5.8 0-10.7-3.9-12.4-9.1l-7 5.4C8.1 40.7 15.5 45 24 45z"/>
            <path fill="#EA4335" d="M44.5 20H24v8.5h11.8c-.8 2.9-2.7 5.4-5.3 7l6.7 5.5C41.6 37.5 45 31.3 45 24c0-1.4-.2-2.7-.5-4z"/>
        </svg>
        Continuer avec Google
    </a>

    <p style="text-align:center;margin-top:20px;font-size:13px;color:#6b7280">
        Pas encore de compte ?
        <a href="{{ route('register') }}" class="auth-link">Créer un compte</a>
    </p>

    <script>
        function togglePw(id, btn) {
            const inp = document.getElementById(id);
            if (inp.type === 'password') { inp.type = 'text';     btn.textContent = '🙈'; }
            else                         { inp.type = 'password'; btn.textContent = '👁️'; }
        }
    </script>

</x-guest-layout>
