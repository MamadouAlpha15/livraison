@extends('layouts.app')

@section('content')

@php $bodyClass = 'is-dashboard'; @endphp
<style>
.emp-card { max-width:520px; margin:32px auto; background:#fff; border-radius:16px; box-shadow:0 4px 24px rgba(0,0,0,.08); overflow:hidden; }
.emp-card-head { background:linear-gradient(135deg,#131921,#232f3e); padding:20px 24px; color:#fff; }
.emp-card-head h2 { margin:0; font-size:18px; font-weight:700; }
.emp-card-head p  { margin:4px 0 0; font-size:13px; opacity:.7; }
.emp-back-btn { display:inline-flex; align-items:center; gap:8px; padding:10px 18px; background:#131921; color:#fff; border:none; border-radius:10px; font-size:13.5px; font-weight:700; text-decoration:none; transition:all .15s; margin-bottom:16px; box-shadow:0 2px 8px rgba(0,0,0,.18); }
.emp-back-btn:hover { background:#232f3e; color:#f90; transform:translateX(-3px); }
.emp-card-body { padding:24px; }
.emp-field { margin-bottom:18px; }
.emp-field label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
.emp-field input, .emp-field select {
    width:100%; padding:11px 14px; border:1.5px solid #d1d5db; border-radius:10px;
    font-size:14px; outline:none; font-family:inherit; box-sizing:border-box;
    transition:border-color .15s;
}
.emp-field input:focus, .emp-field select:focus { border-color:#f90; box-shadow:0 0 0 3px rgba(255,153,0,.15); }
.emp-pwd-wrap { position:relative; }
.emp-pwd-wrap input { padding-right:44px; }
.emp-eye { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; font-size:18px; color:#9ca3af; }
.emp-submit { width:100%; padding:13px; background:linear-gradient(135deg,#f90,#e47911); color:#fff; border:none; border-radius:12px; font-size:15px; font-weight:700; cursor:pointer; font-family:inherit; transition:opacity .15s; }
.emp-submit:hover { opacity:.9; }
.emp-info-box { background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:14px 16px; margin-bottom:20px; font-size:13px; color:#1e40af; line-height:1.6; }
.emp-copy-box { display:none; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:14px 16px; margin-top:16px; }
.emp-copy-box p { margin:0 0 10px; font-size:13px; font-weight:700; color:#065f46; }
.emp-copy-row { display:flex; gap:8px; align-items:center; margin-bottom:6px; font-size:13px; color:#374151; }
.emp-copy-row span { font-family:monospace; background:#fff; padding:4px 10px; border-radius:6px; border:1px solid #d1fae5; flex:1; }
.emp-copy-btn { padding:5px 12px; background:#10b981; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:12px; font-weight:700; white-space:nowrap; }
.emp-wa-btn { display:flex; align-items:center; gap:8px; width:100%; padding:10px 16px; background:#25d366; color:#fff; border:none; border-radius:10px; font-size:13.5px; font-weight:700; cursor:pointer; margin-top:8px; font-family:inherit; }
</style>

<a href="{{ route('boutique.employees.index') }}" class="emp-back-btn">
    ← Retour à la liste
</a>

<div class="emp-card">
    <div class="emp-card-head">
        <h2>➕ Ajouter un employé / livreur</h2>
        <p>Le compte sera créé et l'employé devra choisir son propre mot de passe à la première connexion.</p>
    </div>
    <div class="emp-card-body">

        @if (session('success'))
        <div style="background:#d1fae5;border:1px solid #a7f3d0;border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#065f46;font-size:13.5px;font-weight:600;">
            ✅ {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div style="background:#fee2e2;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#991b1b;font-size:13.5px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div class="emp-info-box">
            🔐 <strong>Mot de passe temporaire :</strong> Vous créez un mot de passe provisoire. À la première connexion, l'employé sera obligé de le changer lui-même. Après ça, vous ne connaîtrez plus son mot de passe.
        </div>

        <form method="POST" action="{{ route('boutique.employees.store') }}" id="empForm" onsubmit="onFormSubmit()">
            @csrf

            <div class="emp-field">
                <label>Nom complet <span style="color:#ef4444">*</span></label>
                <input type="text" name="name" id="empName" value="{{ old('name') }}" placeholder="Ex : Mamadou Diallo" required>
            </div>

            <div class="emp-field">
                <label>Téléphone <span style="color:#ef4444">*</span></label>
                <input type="tel" name="phone" id="empPhone" value="{{ old('phone') }}" placeholder="Ex : +224 620 00 00 00" required>
            </div>

            <div class="emp-field">
                <label>Email <span style="color:#ef4444">*</span></label>
                <input type="email" name="email" id="empEmail" value="{{ old('email') }}" placeholder="Ex : mamadou@gmail.com (ou inventé si pas d'email)" required>
                <div style="font-size:11.5px;color:#6b7280;margin-top:4px;">💡 Si l'employé n'a pas d'email, inventez-en un (ex: mamadou.diallo@shopio.local)</div>
            </div>

            <div class="emp-field">
                <label>Mot de passe temporaire <span style="color:#ef4444">*</span></label>
                <div class="emp-pwd-wrap">
                    <input type="text" name="password" id="empPwd" value="{{ old('password', 'Shopio2024!') }}"
                           placeholder="Mot de passe temporaire" required autocomplete="new-password">
                    <button type="button" class="emp-eye" onclick="genPwd()">🔄</button>
                </div>
                <input type="hidden" name="password_confirmation" id="empPwdConf" value="{{ old('password', 'Shopio2024!') }}">
                <div style="font-size:11.5px;color:#6b7280;margin-top:4px;">Cliquez 🔄 pour générer un mot de passe aléatoire</div>
            </div>

            <div class="emp-field">
                <label>Rôle <span style="color:#ef4444">*</span></label>
                <select name="role_in_shop" required>
                    <option value="livreur"  {{ old('role_in_shop')==='livreur'  ?'selected':'' }}>🚚 Livreur</option>
                    <option value="vendeur"  {{ old('role_in_shop')==='vendeur'  ?'selected':'' }}>🛒 Vendeur</option>
                    <option value="employe"  {{ old('role_in_shop')==='employe'  ?'selected':'' }}>👤 Employé</option>
                </select>
            </div>

            <button type="submit" class="emp-submit">➕ Créer le compte</button>
        </form>

        {{-- Zone affichée après soumission réussie --}}
        @if(session('new_email'))
        <div class="emp-copy-box" id="copyBox" style="display:block">
            <p>📋 Identifiants de <strong>{{ session('new_name') }}</strong> — à transmettre maintenant</p>
            <div class="emp-copy-row">
                <strong>Email :</strong>
                <span id="copyEmail">{{ session('new_email') }}</span>
                <button class="emp-copy-btn" onclick="copyText('copyEmail')">Copier</button>
            </div>
            <div class="emp-copy-row">
                <strong>Mot de passe :</strong>
                <span id="copyPwd">{{ session('new_password') }}</span>
                <button class="emp-copy-btn" onclick="copyText('copyPwd')">Copier</button>
            </div>
            <button class="emp-wa-btn" id="waBtn" onclick="sendWhatsApp()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Envoyer par WhatsApp
            </button>
        </div>
        @endif

    </div>
</div>

<script>
function genPwd() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#';
    let pwd = '';
    for (let i = 0; i < 10; i++) pwd += chars[Math.floor(Math.random() * chars.length)];
    document.getElementById('empPwd').value     = pwd;
    document.getElementById('empPwdConf').value = pwd;
}

function onFormSubmit() {
    const email = document.getElementById('empEmail').value;
    const pwd   = document.getElementById('empPwd').value;
    document.getElementById('empPwdConf').value = pwd;
    document.getElementById('copyEmail').textContent = email;
    document.getElementById('copyPwd').textContent   = pwd;
}

function copyText(id) {
    const text = document.getElementById(id).textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.target;
        btn.textContent = '✓ Copié !';
        setTimeout(() => btn.textContent = 'Copier', 2000);
    });
}

function sendWhatsApp() {
    const email = document.getElementById('copyEmail').textContent;
    const pwd   = document.getElementById('copyPwd').textContent;
    const phone = document.getElementById('empPhone')?.value || '';
    const site  = window.location.origin;
    const msg   = `Bonjour 👋\n\nVoici vos identifiants pour accéder à Shopio :\n\n🔗 Site : ${site}/login\n📧 Email : ${email}\n🔑 Mot de passe temporaire : ${pwd}\n\n⚠️ À la première connexion, vous devrez choisir votre propre mot de passe.`;
    const waNum = phone.replace(/\D/g,'');
    const url   = waNum ? `https://wa.me/${waNum}?text=${encodeURIComponent(msg)}` : `https://wa.me/?text=${encodeURIComponent(msg)}`;
    window.open(url, '_blank');
}

</script>

@endsection
