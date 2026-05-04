<x-guest-layout>

@php
$hasStep2Error = $errors->hasAny(['country','phone','address']);
$startStep     = ($errors->any() && !$errors->hasAny(['name','email','password','role']) && $hasStep2Error) ? 2
               : ($errors->any() ? 1 : 1);
$countries = [
    'BJ'=>['🇧🇯','Bénin'],'BF'=>['🇧🇫','Burkina Faso'],'CV'=>['🇨🇻','Cap-Vert'],
    'CI'=>['🇨🇮',"Côte d'Ivoire"],'GM'=>['🇬🇲','Gambie'],'GH'=>['🇬🇭','Ghana'],
    'GN'=>['🇬🇳','Guinée'],'GW'=>['🇬🇼','Guinée-Bissau'],'GQ'=>['🇬🇶','Guinée équatoriale'],
    'LR'=>['🇱🇷','Libéria'],'ML'=>['🇲🇱','Mali'],'MR'=>['🇲🇷','Mauritanie'],
    'NE'=>['🇳🇪','Niger'],'NG'=>['🇳🇬','Nigeria'],'SN'=>['🇸🇳','Sénégal'],
    'SL'=>['🇸🇱','Sierra Leone'],'TG'=>['🇹🇬','Togo'],
    'AO'=>['🇦🇴','Angola'],'CM'=>['🇨🇲','Cameroun'],'CF'=>['🇨🇫','Centrafrique'],
    'TD'=>['🇹🇩','Tchad'],'CG'=>['🇨🇬','Congo'],'CD'=>['🇨🇩','RD Congo'],
    'GA'=>['🇬🇦','Gabon'],'ST'=>['🇸🇹','São Tomé-et-Príncipe'],
    'BI'=>['🇧🇮','Burundi'],'KM'=>['🇰🇲','Comores'],'DJ'=>['🇩🇯','Djibouti'],
    'ER'=>['🇪🇷','Érythrée'],'ET'=>['🇪🇹','Éthiopie'],'KE'=>['🇰🇪','Kenya'],
    'MG'=>['🇲🇬','Madagascar'],'MW'=>['🇲🇼','Malawi'],'MU'=>['🇲🇺','Maurice'],
    'MZ'=>['🇲🇿','Mozambique'],'RW'=>['🇷🇼','Rwanda'],'SC'=>['🇸🇨','Seychelles'],
    'SO'=>['🇸🇴','Somalie'],'SS'=>['🇸🇸','Soudan du Sud'],'SD'=>['🇸🇩','Soudan'],
    'TZ'=>['🇹🇿','Tanzanie'],'UG'=>['🇺🇬','Ouganda'],'ZM'=>['🇿🇲','Zambie'],'ZW'=>['🇿🇼','Zimbabwe'],
    'BW'=>['🇧🇼','Botswana'],'LS'=>['🇱🇸','Lesotho'],'NA'=>['🇳🇦','Namibie'],
    'ZA'=>['🇿🇦','Afrique du Sud'],'SZ'=>['🇸🇿','Eswatini'],
    'DZ'=>['🇩🇿','Algérie'],'EG'=>['🇪🇬','Égypte'],'LY'=>['🇱🇾','Libye'],
    'MA'=>['🇲🇦','Maroc'],'TN'=>['🇹🇳','Tunisie'],
    'AL'=>['🇦🇱','Albanie'],'DE'=>['🇩🇪','Allemagne'],'AT'=>['🇦🇹','Autriche'],
    'BE'=>['🇧🇪','Belgique'],'FR'=>['🇫🇷','France'],'GB'=>['🇬🇧','Royaume-Uni'],
    'IT'=>['🇮🇹','Italie'],'ES'=>['🇪🇸','Espagne'],'PT'=>['🇵🇹','Portugal'],
    'NL'=>['🇳🇱','Pays-Bas'],'CH'=>['🇨🇭','Suisse'],'BE'=>['🇧🇪','Belgique'],
    'CA'=>['🇨🇦','Canada'],'US'=>['🇺🇸','États-Unis'],'BR'=>['🇧🇷','Brésil'],
    'AR'=>['🇦🇷','Argentine'],'MX'=>['🇲🇽','Mexique'],
    'CN'=>['🇨🇳','Chine'],'JP'=>['🇯🇵','Japon'],'IN'=>['🇮🇳','Inde'],
    'AU'=>['🇦🇺','Australie'],'NZ'=>['🇳🇿','Nouvelle-Zélande'],
];
@endphp

<style>
/* ── Stepper ── */
.ms-stepper{display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:28px}
.ms-step{display:flex;flex-direction:column;align-items:center;gap:5px;position:relative}
.ms-dot{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;transition:all .3s;border:2px solid #e5e7eb;background:#f9fafb;color:#9ca3af}
.ms-dot.done{background:#10b981;border-color:#10b981;color:#fff}
.ms-dot.active{background:#6366f1;border-color:#6366f1;color:#fff;box-shadow:0 0 0 4px rgba(99,102,241,.15)}
.ms-label{font-size:10.5px;font-weight:600;color:#9ca3af;letter-spacing:.3px;white-space:nowrap}
.ms-label.active{color:#6366f1}
.ms-label.done{color:#10b981}
.ms-line{width:60px;height:2px;background:#e5e7eb;margin:0 4px;margin-bottom:18px;transition:background .3s;flex-shrink:0}
.ms-line.done{background:#10b981}

/* ── Steps ── */
.reg-step{display:none;animation:fadeSlide .25s ease}
.reg-step.active{display:block}
@keyframes fadeSlide{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}

/* ── Nav buttons ── */
.step-nav{display:flex;gap:10px;margin-top:6px}
.btn-back{flex:0 0 auto;padding:0 20px;height:46px;border-radius:10px;border:1.5px solid #e5e7eb;background:#fff;color:#374151;font-size:14px;font-weight:600;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:6px}
.btn-back:hover{border-color:#6366f1;color:#6366f1}
.btn-next{flex:1;height:46px;border-radius:10px;border:none;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;font-size:14.5px;font-weight:700;cursor:pointer;transition:all .2s;letter-spacing:.2px}
.btn-next:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(99,102,241,.35)}
.btn-submit{width:100%;height:46px;border-radius:10px;border:none;background:linear-gradient(135deg,#10b981,#059669);color:#fff;font-size:14.5px;font-weight:700;cursor:pointer;transition:all .2s;letter-spacing:.2px;margin-top:6px}
.btn-submit:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(16,185,129,.35)}

/* ── Inline errors ── */
.f-input-err{border-color:#f87171!important;background:#fff5f5!important}
.f-inline-err{font-size:11.5px;color:#ef4444;margin-top:4px;display:none}
.f-inline-err.show{display:block}
.f-input.valid{border-color:#10b981!important}

/* ── Hint ── */
.f-hint{font-size:11.5px;color:#9ca3af;margin-top:5px}
</style>

{{-- Titre --}}
<div class="ab-title">Créer un compte 🚀</div>
<p class="ab-sub">Rejoignez des milliers d'utilisateurs sur Shopio</p>

{{-- Erreur serveur globale --}}
@if ($errors->any())
<div class="f-alert" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;margin-bottom:18px;padding:10px 14px;border-radius:8px;font-size:13px">
    {{ $errors->first() }}
</div>
@endif

{{-- Stepper visuel --}}
<div class="ms-stepper" id="stepper">
    <div class="ms-step">
        <div class="ms-dot active" id="dot1">1</div>
        <span class="ms-label active" id="lbl1">Identité</span>
    </div>
    <div class="ms-line" id="line1"></div>
    <div class="ms-step">
        <div class="ms-dot" id="dot2">2</div>
        <span class="ms-label" id="lbl2">Coordonnées</span>
    </div>
</div>

<form method="POST" action="{{ route('register') }}" id="regForm" novalidate>
    @csrf

    {{-- ══════════════ ÉTAPE 1 ══════════════ --}}
    <div class="reg-step active" id="step1">

        {{-- Rôle --}}
        <div class="f-group">
            <label class="f-label">Je m'inscris en tant que</label>
            <input type="hidden" name="role" id="roleInput" value="{{ old('role','client') }}">
            <div class="role-cards">
                <div class="role-card {{ old('role','client') === 'client'  ? 'active':'' }}" onclick="setRole('client',this)">
                    <span class="rc-ico">🛒</span>Client
                </div>
                <div class="role-card {{ old('role') === 'admin'   ? 'active':'' }}" onclick="setRole('admin',this)">
                    <span class="rc-ico">🏪</span>Admin boutique
                </div>
                <div class="role-card {{ old('role') === 'company' ? 'active':'' }}" onclick="setRole('company',this)">
                    <span class="rc-ico">🚚</span>Entreprise livraison
                </div>
                <div class="role-card {{ old('role') === 'livreur' ? 'active':'' }}" onclick="setRole('livreur',this)">
                    <span class="rc-ico">🛵</span>Livreur
                </div>
            </div>
            @error('role')<p class="f-error">{{ $message }}</p>@enderror
        </div>

        {{-- Nom --}}
        <div class="f-group">
            <label class="f-label" for="name">Nom complet</label>
            <div class="f-icon-wrap">
                <span class="f-ico">👤</span>
                <input id="name" class="f-input {{ $errors->has('name') ? 'f-input-err':'' }}"
                       type="text" name="name" value="{{ old('name') }}"
                       autocomplete="name" placeholder="Votre nom complet"
                       oninput="liveCheck(this,'nameErr','name')">
            </div>
            <p class="f-inline-err {{ $errors->has('name') ? 'show':'' }}" id="nameErr">
                {{ $errors->first('name') ?? 'Ce champ est requis.' }}
            </p>
        </div>

        {{-- Email --}}
        <div class="f-group">
            <label class="f-label" for="email">Adresse email</label>
            <div class="f-icon-wrap">
                <span class="f-ico">✉️</span>
                <input id="email" class="f-input {{ $errors->has('email') ? 'f-input-err':'' }}"
                       type="email" name="email" value="{{ old('email') }}"
                       autocomplete="username" placeholder="exemple@email.com"
                       oninput="liveCheck(this,'emailErr','email')">
            </div>
            <p class="f-inline-err {{ $errors->has('email') ? 'show':'' }}" id="emailErr">
                {{ $errors->first('email') ?? 'Entrez une adresse email valide.' }}
            </p>
        </div>

        {{-- Mot de passe --}}
        <div class="f-group">
            <label class="f-label" for="password">Mot de passe</label>
            <div class="f-icon-wrap f-pw-wrap">
                <span class="f-ico">🔒</span>
                <input id="password" class="f-input {{ $errors->has('password') ? 'f-input-err':'' }}"
                       type="password" autocomplete="new-password" placeholder="Min. 8 caractères"
                       oninput="liveCheck(this,'pwErr','password')">
                <button type="button" class="f-pw-eye" onclick="togglePw('password',this)">👁️</button>
            </div>
            <p class="f-inline-err {{ $errors->has('password') ? 'show':'' }}" id="pwErr">
                {{ $errors->first('password') ?? 'Minimum 8 caractères requis.' }}
            </p>
        </div>

        {{-- Confirmation --}}
        <div class="f-group">
            <label class="f-label" for="password_confirmation">Confirmer le mot de passe</label>
            <div class="f-icon-wrap f-pw-wrap">
                <span class="f-ico">🔒</span>
                <input id="password_confirmation" class="f-input"
                       type="password" autocomplete="new-password" placeholder="Répéter le mot de passe"
                       oninput="checkConfirm()">
                <button type="button" class="f-pw-eye" onclick="togglePw('password_confirmation',this)">👁️</button>
            </div>
            <p class="f-inline-err" id="confirmErr">Les mots de passe ne correspondent pas.</p>
        </div>

        <button type="button" class="btn-next" onclick="goStep2()">Suivant →</button>

        {{-- Séparateur Google --}}
        <div style="display:flex;align-items:center;gap:10px;margin:18px 0 12px;">
            <div style="flex:1;height:1px;background:#e5e7eb;"></div>
            <span style="font-size:12px;color:#9ca3af;font-weight:600;">OU</span>
            <div style="flex:1;height:1px;background:#e5e7eb;"></div>
        </div>
        <a href="{{ route('google.redirect') }}"
           style="display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:11px 16px;border-radius:10px;border:1.5px solid #e5e7eb;background:#fff;color:#374151;font-size:14px;font-weight:600;text-decoration:none;transition:all .2s;box-shadow:0 1px 3px rgba(0,0,0,.06);"
           onmouseover="this.style.borderColor='#4285F4';this.style.boxShadow='0 3px 10px rgba(66,133,244,.2)'"
           onmouseout="this.style.borderColor='#e5e7eb';this.style.boxShadow='0 1px 3px rgba(0,0,0,.06)'">
            <svg width="18" height="18" viewBox="0 0 48 48">
                <path fill="#4285F4" d="M44.5 20H24v8.5h11.8C34.7 33.9 29.8 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 5.1 29.6 3 24 37c-7.7 0-14.4 4.4-17.7 11.7z"/>
                <path fill="#4285F4" d="M44.5 20H24v8.5h11.8C34.7 33.9 29.8 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 5.1 29.6 3 24 3 12.4 3 3 12.4 3 24s9.4 21 21 21c10.5 0 20-7.5 20-21 0-1.4-.1-2.7-.5-4z"/>
                <path fill="#34A853" d="M6.3 14.7l7 5.1C15.1 16.1 19.2 13 24 13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 5.1 29.6 3 24 3c-7.7 0-14.4 4.4-17.7 11.7z"/>
                <path fill="#FBBC05" d="M24 45c5.6 0 10.5-1.9 14.4-5l-6.7-5.5C29.7 36.3 27 37 24 37c-5.8 0-10.7-3.9-12.4-9.1l-7 5.4C8.1 40.7 15.5 45 24 45z"/>
                <path fill="#EA4335" d="M44.5 20H24v8.5h11.8c-.8 2.9-2.7 5.4-5.3 7l6.7 5.5C41.6 37.5 45 31.3 45 24c0-1.4-.2-2.7-.5-4z"/>
            </svg>
            S'inscrire avec Google
        </a>

        <p style="text-align:center;margin-top:16px;font-size:13px;color:#6b7280">
            Déjà inscrit ? <a href="{{ route('login') }}" class="auth-link">Se connecter</a>
        </p>
    </div>

    {{-- ══════════════ ÉTAPE 2 ══════════════ --}}
    <div class="reg-step" id="step2">

        {{-- Champs cachés pour transmettre le mot de passe (Chrome vide les inputs password masqués) --}}
        <input type="hidden" id="pw_submit" name="password">
        <input type="hidden" id="pw_conf_submit" name="password_confirmation">

        {{-- Pays --}}
        <div class="f-group">
            <label class="f-label" for="country">🌍 Votre pays</label>
            <div class="f-flag-wrap">
                <span class="f-flag-preview" id="flagPreview">🌍</span>
                <select id="country" name="country" class="f-select {{ $errors->has('country') ? 'f-input-err':'' }}"
                        onchange="updateFlag(this)">
                    <option value="">-- Sélectionner votre pays --</option>
                    @foreach($countries as $code => $info)
                    <option value="{{ $code }}" data-flag="{{ $info[0] }}" {{ old('country') === $code ? 'selected':'' }}>
                        {{ $info[0] }} {{ $info[1] }}
                    </option>
                    @endforeach
                </select>
            </div>
            @error('country')<p class="f-error">{{ $message }}</p>@enderror
            <p class="f-hint">Vous verrez uniquement les boutiques disponibles dans votre pays.</p>
        </div>

        {{-- Téléphone --}}
        <div class="f-group" id="phoneField">
            <label class="f-label" for="phone">Téléphone</label>
            <div class="f-icon-wrap">
                <span class="f-ico">📞</span>
                <input id="phone" class="f-input {{ $errors->has('phone') ? 'f-input-err':'' }}"
                       type="text" name="phone" value="{{ old('phone') }}" placeholder="+224 6XX XX XX XX">
            </div>
            @error('phone')<p class="f-error">{{ $message }}</p>@enderror
        </div>

        {{-- Adresse --}}
        <div class="f-group" id="addressField">
            <label class="f-label" for="address">Adresse de livraison</label>
            <div class="f-icon-wrap">
                <span class="f-ico">📍</span>
                <input id="address" class="f-input {{ $errors->has('address') ? 'f-input-err':'' }}"
                       type="text" name="address" value="{{ old('address') }}" placeholder="Votre adresse complète">
            </div>
            @error('address')<p class="f-error">{{ $message }}</p>@enderror
        </div>

        <div class="step-nav">
            <button type="button" class="btn-back" onclick="goStep1()">← Retour</button>
            <button type="submit" class="btn-next">Créer mon compte ✓</button>
        </div>

        <p style="text-align:center;margin-top:16px;font-size:13px;color:#6b7280">
            Déjà inscrit ? <a href="{{ route('login') }}" class="auth-link">Se connecter</a>
        </p>
    </div>

</form>

<script>
var currentStep = {{ $startStep }};

function setStep(n) {
    currentStep = n;
    document.getElementById('step1').classList.toggle('active', n === 1);
    document.getElementById('step2').classList.toggle('active', n === 2);

    // Stepper dots
    var dot1 = document.getElementById('dot1'), lbl1 = document.getElementById('lbl1');
    var dot2 = document.getElementById('dot2'), lbl2 = document.getElementById('lbl2');
    var line1 = document.getElementById('line1');

    dot1.className = 'ms-dot ' + (n === 1 ? 'active' : 'done');
    dot1.textContent = n === 1 ? '1' : '✓';
    lbl1.className   = 'ms-label ' + (n === 1 ? 'active' : 'done');
    dot2.className   = 'ms-dot ' + (n === 2 ? 'active' : '');
    lbl2.className   = 'ms-label ' + (n === 2 ? 'active' : '');
    line1.className  = 'ms-line ' + (n === 2 ? 'done' : '');
}

function goStep2() {
    var ok = true;

    var name = document.getElementById('name');
    if (!name.value.trim()) {
        showErr('nameErr', 'Le nom est requis.'); markErr(name); ok = false;
    } else { hideErr('nameErr'); markOk(name); }

    var email = document.getElementById('email');
    if (!email.value.trim() || !email.value.includes('@')) {
        showErr('emailErr', 'Entrez une adresse email valide.'); markErr(email); ok = false;
    } else { hideErr('emailErr'); markOk(email); }

    var pw = document.getElementById('password');
    if (pw.value.length < 8) {
        showErr('pwErr', 'Minimum 8 caractères requis.'); markErr(pw); ok = false;
    } else { hideErr('pwErr'); markOk(pw); }

    var conf = document.getElementById('password_confirmation');
    if (conf.value !== pw.value || conf.value === '') {
        showErr('confirmErr', 'Les mots de passe ne correspondent pas.'); markErr(conf); ok = false;
    } else { hideErr('confirmErr'); markOk(conf); }

    if (ok) {
        // Copier les mots de passe dans les inputs cachés avant de masquer l'étape 1
        document.getElementById('pw_submit').value      = pw.value;
        document.getElementById('pw_conf_submit').value = conf.value;
        setStep(2);
    } else document.getElementById('name').scrollIntoView({behavior:'smooth', block:'start'});
}

function goStep1() { setStep(1); }

function liveCheck(inp, errId, type) {
    if (type === 'name' && inp.value.trim()) { hideErr(errId); markOk(inp); }
    if (type === 'email' && inp.value.includes('@') && inp.value.includes('.')) { hideErr(errId); markOk(inp); }
    if (type === 'password' && inp.value.length >= 8) { hideErr(errId); markOk(inp); checkConfirm(); }
}

function checkConfirm() {
    var pw   = document.getElementById('password').value;
    var conf = document.getElementById('password_confirmation');
    if (conf.value && conf.value === pw) { hideErr('confirmErr'); markOk(conf); }
    else if (conf.value) { showErr('confirmErr', 'Les mots de passe ne correspondent pas.'); markErr(conf); }
}

function showErr(id, msg) {
    var el = document.getElementById(id);
    if (el) { if (msg) el.textContent = msg; el.classList.add('show'); }
}
function hideErr(id) {
    var el = document.getElementById(id); if (el) el.classList.remove('show');
}
function markErr(inp) {
    inp.classList.add('f-input-err'); inp.classList.remove('valid');
}
function markOk(inp) {
    inp.classList.remove('f-input-err'); inp.classList.add('valid');
}

function setRole(val, el) {
    document.getElementById('roleInput').value = val;
    document.querySelectorAll('.role-card').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    var hide = val === 'admin' || val === 'company';
    document.getElementById('phoneField').style.display   = hide ? 'none' : 'block';
    document.getElementById('addressField').style.display = hide ? 'none' : 'block';
}

function updateFlag(select) {
    var opt  = select.options[select.selectedIndex];
    var flag = opt.getAttribute('data-flag') || '🌍';
    document.getElementById('flagPreview').textContent = flag;
}

function togglePw(id, btn) {
    var inp = document.getElementById(id);
    if (inp.type === 'password') { inp.type = 'text'; btn.textContent = '🙈'; }
    else { inp.type = 'password'; btn.textContent = '👁️'; }
}

document.addEventListener('DOMContentLoaded', function () {
    // Init flag
    var cs = document.getElementById('country');
    if (cs && cs.value) updateFlag(cs);

    // Restore role visibility
    var cur = document.getElementById('roleInput').value;
    if (cur === 'admin' || cur === 'company') {
        document.getElementById('phoneField').style.display   = 'none';
        document.getElementById('addressField').style.display = 'none';
    }

    // Si erreurs serveur → go bon step
    setStep(currentStep);

    // Intercepter la soumission du formulaire
    // (cas : appui sur Entrée depuis step 1 → ne pas soumettre, aller step 2)
    document.getElementById('regForm').addEventListener('submit', function(e) {
        if (currentStep === 1) {
            e.preventDefault();
            goStep2();
            return;
        }
        // Step 2 : re-copier le mot de passe juste avant l'envoi
        // (sécurité supplémentaire si le navigateur avait vidé le champ)
        var pw   = document.getElementById('password');
        var conf = document.getElementById('password_confirmation');
        var pws  = document.getElementById('pw_submit');
        var pcs  = document.getElementById('pw_conf_submit');
        if (pw && pw.value)   pws.value = pw.value;
        if (conf && conf.value) pcs.value = conf.value;
        // Si les champs visibles sont vides (Chrome les a effacés) mais les cachés ont déjà
        // une valeur copiée par goStep2(), on garde la valeur déjà là
    });
});
</script>

</x-guest-layout>
