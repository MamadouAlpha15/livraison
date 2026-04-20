<x-guest-layout>

<div class="ab-title">🔐 Nouveau mot de passe</div>
<p class="ab-sub">Pour votre sécurité, choisissez un mot de passe personnel avant de continuer.</p>

@if ($errors->any())
<div style="background:#fee2e2;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#991b1b;font-size:13.5px;">
    <ul style="margin:0;padding-left:18px;">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('password.change.update') }}">
    @csrf

    <div style="margin-bottom:16px;">
        <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">
            Nouveau mot de passe <span style="color:#ef4444">*</span>
        </label>
        <div style="position:relative;">
            <input type="password" name="password" id="pwd1"
                   style="width:100%;padding:11px 44px 11px 14px;border:1.5px solid #d1d5db;border-radius:10px;font-size:14px;outline:none;box-sizing:border-box;"
                   placeholder="Minimum 8 caractères" required autofocus>
            <button type="button" onclick="togglePwd('pwd1',this)"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:18px;color:#9ca3af;">👁</button>
        </div>
    </div>

    <div style="margin-bottom:24px;">
        <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">
            Confirmer le mot de passe <span style="color:#ef4444">*</span>
        </label>
        <div style="position:relative;">
            <input type="password" name="password_confirmation" id="pwd2"
                   style="width:100%;padding:11px 44px 11px 14px;border:1.5px solid #d1d5db;border-radius:10px;font-size:14px;outline:none;box-sizing:border-box;"
                   placeholder="Répétez votre mot de passe" required>
            <button type="button" onclick="togglePwd('pwd2',this)"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:18px;color:#9ca3af;">👁</button>
        </div>
    </div>

    <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:10px;padding:12px 14px;margin-bottom:20px;font-size:12.5px;color:#92400e;">
        💡 Ce mot de passe est personnel. Ne le partagez avec personne, même pas votre employeur.
    </div>

    <button type="submit"
            style="width:100%;padding:13px;background:linear-gradient(135deg,#f90,#e47911);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;">
        ✅ Enregistrer mon mot de passe
    </button>
</form>

<script>
function togglePwd(id, btn) {
    const inp = document.getElementById(id);
    const show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    btn.textContent = show ? '🙈' : '👁';
}
</script>

</x-guest-layout>
