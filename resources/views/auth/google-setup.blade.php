<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Finaliser l'inscription · Shopio</title>
    <link rel="icon" type="image/jpeg" href="/images/Shopio2.jpeg">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Figtree',sans-serif;min-height:100vh;background:#f8fafc;display:flex;align-items:center;justify-content:center;padding:24px}

        .setup-card{background:#fff;border-radius:16px;padding:36px 32px;max-width:460px;width:100%;box-shadow:0 4px 32px rgba(0,0,0,.10);border:1px solid #e5e7eb;}

        /* Logo */
        .setup-logo{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:24px;}
        .setup-logo img{height:40px;width:40px;object-fit:cover;border-radius:10px;}
        .setup-logo-name{font-size:20px;font-weight:800;background:linear-gradient(90deg,#291d95,#aa28d9);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}

        .ab-title{font-size:22px;font-weight:800;color:#111827;text-align:center;margin-bottom:4px;}
        .ab-sub{font-size:13.5px;color:#6b7280;text-align:center;margin-bottom:22px;}

        /* Profil Google */
        .google-profile{display:flex;align-items:center;gap:12px;padding:12px 14px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;margin-bottom:22px;}
        .google-av{width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid #22c55e;flex-shrink:0;}
        .google-av-fallback{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#4f46e5);display:flex;align-items:center;justify-content:center;font-size:17px;font-weight:800;color:#fff;flex-shrink:0;}
        .google-name{font-size:14px;font-weight:700;color:#166534;}
        .google-email{font-size:12px;color:#16a34a;margin-top:1px;}
        .google-badge{display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;background:#dcfce7;color:#166534;border:1px solid #86efac;padding:2px 8px;border-radius:20px;margin-top:4px;}

        /* Form */
        .f-group{margin-bottom:18px;}
        .f-label{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;}

        /* Role cards */
        .role-cards{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
        .role-card{padding:12px 10px;border-radius:10px;border:2px solid #e5e7eb;background:#f9fafb;text-align:center;cursor:pointer;transition:all .18s;font-size:13px;font-weight:600;color:#6b7280;display:flex;flex-direction:column;align-items:center;gap:4px;}
        .role-card:hover{border-color:#a5b4fc;background:#eef2ff;color:#4338ca;}
        .role-card.active{border-color:#6366f1;background:#eef2ff;color:#4338ca;box-shadow:0 0 0 3px rgba(99,102,241,.12);}
        .rc-ico{font-size:22px;}

        /* Country */
        .f-select{width:100%;padding:10px 36px 10px 12px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13.5px;color:#111827;background:#fff;outline:none;transition:border-color .15s;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;background-size:16px;font-family:inherit;}
        .f-select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.10);}

        /* Submit */
        .btn-submit{width:100%;padding:13px;border-radius:10px;border:none;background:linear-gradient(135deg,#291d95,#aa28d9);color:#fff;font-size:14.5px;font-weight:700;cursor:pointer;transition:all .2s;letter-spacing:.2px;margin-top:6px;font-family:inherit;}
        .btn-submit:hover{opacity:.92;transform:translateY(-1px);}

        .f-error{font-size:11.5px;color:#ef4444;margin-top:4px;}
        .alert-err{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;}
    </style>
</head>
<body>

<div class="setup-card">
    <div class="setup-logo">
        <img src="/images/Shopio3.jpeg" alt="Shopio">
        <span class="setup-logo-name">Shopio</span>
    </div>

    <div class="ab-title">Presque là ! 🎉</div>
    <p class="ab-sub">Choisissez votre rôle et votre pays pour finaliser</p>

    {{-- Profil Google --}}
    <div class="google-profile">
        @if($googleAvatar)
            <img src="{{ $googleAvatar }}" alt="{{ $googleName }}" class="google-av">
        @else
            <div class="google-av-fallback">{{ strtoupper(substr($googleName ?? 'U', 0, 1)) }}</div>
        @endif
        <div>
            <div class="google-name">{{ $googleName }}</div>
            <div class="google-email">{{ $googleEmail }}</div>
            <div class="google-badge">✓ Compte Google vérifié</div>
        </div>
    </div>

    @if($errors->any())
    <div class="alert-err">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('google.setup.store') }}">
        @csrf

        {{-- Rôle --}}
        <div class="f-group">
            <label class="f-label">Je m'inscris en tant que</label>
            <input type="hidden" name="role" id="roleInput" value="{{ old('role','client') }}">
            <div class="role-cards">
                <div class="role-card {{ old('role','client') === 'client'  ? 'active':'' }}" onclick="setRole('client',this)">
                    <span class="rc-ico">🛒</span> Client
                </div>
                <div class="role-card {{ old('role') === 'admin' ? 'active':'' }}" onclick="setRole('admin',this)">
                    <span class="rc-ico">🏪</span> Admin boutique
                </div>
                <div class="role-card {{ old('role') === 'company' ? 'active':'' }}" onclick="setRole('company',this)">
                    <span class="rc-ico">🚚</span> Entreprise livraison
                </div>
                <div class="role-card {{ old('role') === 'livreur' ? 'active':'' }}" onclick="setRole('livreur',this)">
                    <span class="rc-ico">🛵</span> Livreur
                </div>
            </div>
            @error('role')<p class="f-error">{{ $message }}</p>@enderror
        </div>

        {{-- Pays --}}
        <div class="f-group">
            <label class="f-label" for="country">Votre pays</label>
            <select name="country" id="country" class="f-select" required>
                <option value="">— Sélectionner votre pays —</option>
                @foreach($countries as $code => [$flag, $name])
                <option value="{{ $code }}" {{ old('country') === $code ? 'selected' : '' }}>
                    {{ $flag }} {{ $name }}
                </option>
                @endforeach
            </select>
            @error('country')<p class="f-error">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="btn-submit">Accéder à mon espace →</button>
    </form>
</div>

<script>
function setRole(role, el) {
    document.getElementById('roleInput').value = role;
    document.querySelectorAll('.role-card').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
}
</script>

</body>
</html>
