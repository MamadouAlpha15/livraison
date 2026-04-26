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

        <p style="text-align:center;margin-top:20px;font-size:13px;color:#6b7280">
            Pas encore de compte ?
            <a href="{{ route('register') }}" class="auth-link">Créer un compte</a>
        </p>
    </form>

    <script>
        function togglePw(id, btn) {
            const inp = document.getElementById(id);
            if (inp.type === 'password') { inp.type = 'text';     btn.textContent = '🙈'; }
            else                         { inp.type = 'password'; btn.textContent = '👁️'; }
        }
    </script>

</x-guest-layout>
