<x-guest-layout>

    <div class="ab-title">Créer un compte 🚀</div>
    <p class="ab-sub">Rejoignez des milliers d'utilisateurs sur Shopio</p>

    @if ($errors->any())
        <div class="f-alert" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;margin-bottom:18px">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- Rôle --}}
        <div class="f-group">
            <label class="f-label">Je m'inscris en tant que</label>
            <input type="hidden" name="role" id="roleInput" value="{{ old('role','client') }}">
            <div class="role-cards">
                <div class="role-card {{ old('role','client') === 'client'  ? 'active':'' }}" onclick="setRole('client',this)">
                    <span class="rc-ico">🛒</span>Client
                </div>
                <div class="role-card {{ old('role') === 'admin'            ? 'active':'' }}" onclick="setRole('admin',this)">
                    <span class="rc-ico">🏪</span>Admin boutique
                </div>
                <div class="role-card {{ old('role') === 'company'          ? 'active':'' }}" onclick="setRole('company',this)">
                    <span class="rc-ico">🚚</span>Entreprise livraison
                </div>
                <div class="role-card {{ old('role') === 'livreur'          ? 'active':'' }}" onclick="setRole('livreur',this)">
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
                       required autofocus autocomplete="name" placeholder="Votre nom complet">
            </div>
            @error('name')<p class="f-error">{{ $message }}</p>@enderror
        </div>

        {{-- Email --}}
        <div class="f-group">
            <label class="f-label" for="email">Adresse email</label>
            <div class="f-icon-wrap">
                <span class="f-ico">✉️</span>
                <input id="email" class="f-input {{ $errors->has('email') ? 'f-input-err':'' }}"
                       type="email" name="email" value="{{ old('email') }}"
                       required autocomplete="username" placeholder="exemple@email.com">
            </div>
            @error('email')<p class="f-error">{{ $message }}</p>@enderror
        </div>

        {{-- Pays --}}
        <div class="f-group">
            <label class="f-label" for="country">🌍 Votre pays</label>
            <div class="f-flag-wrap">
                <span class="f-flag-preview" id="flagPreview">🌍</span>
                <select id="country" name="country" class="f-select" required onchange="updateFlag(this)">
                    <option value="">-- Sélectionner votre pays --</option>
                    @php
                    $countries = [
                        /* ── Afrique de l'Ouest ── */
                        'BJ'=>['🇧🇯','Bénin'],'BF'=>['🇧🇫','Burkina Faso'],'CV'=>['🇨🇻','Cap-Vert'],
                        'CI'=>['🇨🇮','Côte d\'Ivoire'],'GM'=>['🇬🇲','Gambie'],'GH'=>['🇬🇭','Ghana'],
                        'GN'=>['🇬🇳','Guinée'],'GW'=>['🇬🇼','Guinée-Bissau'],'GQ'=>['🇬🇶','Guinée équatoriale'],
                        'LR'=>['🇱🇷','Libéria'],'ML'=>['🇲🇱','Mali'],'MR'=>['🇲🇷','Mauritanie'],
                        'NE'=>['🇳🇪','Niger'],'NG'=>['🇳🇬','Nigeria'],'SN'=>['🇸🇳','Sénégal'],
                        'SL'=>['🇸🇱','Sierra Leone'],'TG'=>['🇹🇬','Togo'],
                        /* ── Afrique Centrale ── */
                        'AO'=>['🇦🇴','Angola'],'CM'=>['🇨🇲','Cameroun'],'CF'=>['🇨🇫','Centrafrique'],
                        'TD'=>['🇹🇩','Tchad'],'CG'=>['🇨🇬','Congo'],'CD'=>['🇨🇩','RD Congo'],
                        'GA'=>['🇬🇦','Gabon'],'ST'=>['🇸🇹','São Tomé-et-Príncipe'],
                        /* ── Afrique de l'Est ── */
                        'BI'=>['🇧🇮','Burundi'],'KM'=>['🇰🇲','Comores'],'DJ'=>['🇩🇯','Djibouti'],
                        'ER'=>['🇪🇷','Érythrée'],'ET'=>['🇪🇹','Éthiopie'],'KE'=>['🇰🇪','Kenya'],
                        'MG'=>['🇲🇬','Madagascar'],'MW'=>['🇲🇼','Malawi'],'MU'=>['🇲🇺','Maurice'],
                        'MZ'=>['🇲🇿','Mozambique'],'RW'=>['🇷🇼','Rwanda'],'SC'=>['🇸🇨','Seychelles'],
                        'SO'=>['🇸🇴','Somalie'],'SS'=>['🇸🇸','Soudan du Sud'],'SD'=>['🇸🇩','Soudan'],
                        'TZ'=>['🇹🇿','Tanzanie'],'UG'=>['🇺🇬','Ouganda'],'ZM'=>['🇿🇲','Zambie'],'ZW'=>['🇿🇼','Zimbabwe'],
                        /* ── Afrique Australe ── */
                        'BW'=>['🇧🇼','Botswana'],'LS'=>['🇱🇸','Lesotho'],'NA'=>['🇳🇦','Namibie'],
                        'ZA'=>['🇿🇦','Afrique du Sud'],'SZ'=>['🇸🇿','Eswatini'],
                        /* ── Afrique du Nord ── */
                        'DZ'=>['🇩🇿','Algérie'],'EG'=>['🇪🇬','Égypte'],'LY'=>['🇱🇾','Libye'],
                        'MA'=>['🇲🇦','Maroc'],'TN'=>['🇹🇳','Tunisie'],
                        /* ── Europe ── */
                        'AL'=>['🇦🇱','Albanie'],'DE'=>['🇩🇪','Allemagne'],'AT'=>['🇦🇹','Autriche'],
                        'BE'=>['🇧🇪','Belgique'],'BY'=>['🇧🇾','Biélorussie'],'BA'=>['🇧🇦','Bosnie-Herzégovine'],
                        'BG'=>['🇧🇬','Bulgarie'],'HR'=>['🇭🇷','Croatie'],'CY'=>['🇨🇾','Chypre'],
                        'CZ'=>['🇨🇿','Tchéquie'],'DK'=>['🇩🇰','Danemark'],'ES'=>['🇪🇸','Espagne'],
                        'EE'=>['🇪🇪','Estonie'],'FI'=>['🇫🇮','Finlande'],'FR'=>['🇫🇷','France'],
                        'GR'=>['🇬🇷','Grèce'],'HU'=>['🇭🇺','Hongrie'],'IE'=>['🇮🇪','Irlande'],
                        'IT'=>['🇮🇹','Italie'],'LV'=>['🇱🇻','Lettonie'],'LT'=>['🇱🇹','Lituanie'],
                        'LU'=>['🇱🇺','Luxembourg'],'MK'=>['🇲🇰','Macédoine du Nord'],'MT'=>['🇲🇹','Malte'],
                        'MD'=>['🇲🇩','Moldavie'],'ME'=>['🇲🇪','Monténégro'],'NL'=>['🇳🇱','Pays-Bas'],
                        'NO'=>['🇳🇴','Norvège'],'PL'=>['🇵🇱','Pologne'],'PT'=>['🇵🇹','Portugal'],
                        'RO'=>['🇷🇴','Roumanie'],'GB'=>['🇬🇧','Royaume-Uni'],'RU'=>['🇷🇺','Russie'],
                        'RS'=>['🇷🇸','Serbie'],'SK'=>['🇸🇰','Slovaquie'],'SI'=>['🇸🇮','Slovénie'],
                        'SE'=>['🇸🇪','Suède'],'CH'=>['🇨🇭','Suisse'],'UA'=>['🇺🇦','Ukraine'],
                        /* ── Amériques ── */
                        'AR'=>['🇦🇷','Argentine'],'BO'=>['🇧🇴','Bolivie'],'BR'=>['🇧🇷','Brésil'],
                        'CA'=>['🇨🇦','Canada'],'CL'=>['🇨🇱','Chili'],'CO'=>['🇨🇴','Colombie'],
                        'CR'=>['🇨🇷','Costa Rica'],'CU'=>['🇨🇺','Cuba'],'DO'=>['🇩🇴','Rép. Dominicaine'],
                        'EC'=>['🇪🇨','Équateur'],'SV'=>['🇸🇻','Salvador'],'GT'=>['🇬🇹','Guatemala'],
                        'HT'=>['🇭🇹','Haïti'],'HN'=>['🇭🇳','Honduras'],'JM'=>['🇯🇲','Jamaïque'],
                        'MX'=>['🇲🇽','Mexique'],'NI'=>['🇳🇮','Nicaragua'],'PA'=>['🇵🇦','Panama'],
                        'PY'=>['🇵🇾','Paraguay'],'PE'=>['🇵🇪','Pérou'],'PR'=>['🇵🇷','Porto Rico'],
                        'TT'=>['🇹🇹','Trinité-et-Tobago'],'US'=>['🇺🇸','États-Unis'],
                        'UY'=>['🇺🇾','Uruguay'],'VE'=>['🇻🇪','Venezuela'],
                        /* ── Asie ── */
                        'SA'=>['🇸🇦','Arabie Saoudite'],'AM'=>['🇦🇲','Arménie'],'AZ'=>['🇦🇿','Azerbaïdjan'],
                        'BH'=>['🇧🇭','Bahreïn'],'BD'=>['🇧🇩','Bangladesh'],'KH'=>['🇰🇭','Cambodge'],
                        'CN'=>['🇨🇳','Chine'],'KP'=>['🇰🇵','Corée du Nord'],'KR'=>['🇰🇷','Corée du Sud'],
                        'AE'=>['🇦🇪','Émirats arabes unis'],'GE'=>['🇬🇪','Géorgie'],'IN'=>['🇮🇳','Inde'],
                        'ID'=>['🇮🇩','Indonésie'],'IQ'=>['🇮🇶','Irak'],'IR'=>['🇮🇷','Iran'],
                        'IL'=>['🇮🇱','Israël'],'JP'=>['🇯🇵','Japon'],'JO'=>['🇯🇴','Jordanie'],
                        'KZ'=>['🇰🇿','Kazakhstan'],'KW'=>['🇰🇼','Koweït'],'KG'=>['🇰🇬','Kirghizistan'],
                        'LA'=>['🇱🇦','Laos'],'LB'=>['🇱🇧','Liban'],'MY'=>['🇲🇾','Malaisie'],
                        'MV'=>['🇲🇻','Maldives'],'MN'=>['🇲🇳','Mongolie'],'MM'=>['🇲🇲','Myanmar'],
                        'NP'=>['🇳🇵','Népal'],'OM'=>['🇴🇲','Oman'],'UZ'=>['🇺🇿','Ouzbékistan'],
                        'PK'=>['🇵🇰','Pakistan'],'PS'=>['🇵🇸','Palestine'],'PH'=>['🇵🇭','Philippines'],
                        'QA'=>['🇶🇦','Qatar'],'SG'=>['🇸🇬','Singapour'],'LK'=>['🇱🇰','Sri Lanka'],
                        'SY'=>['🇸🇾','Syrie'],'TJ'=>['🇹🇯','Tadjikistan'],'TW'=>['🇹🇼','Taïwan'],
                        'TH'=>['🇹🇭','Thaïlande'],'TM'=>['🇹🇲','Turkménistan'],'TR'=>['🇹🇷','Turquie'],
                        'VN'=>['🇻🇳','Vietnam'],'YE'=>['🇾🇪','Yémen'],
                        /* ── Océanie ── */
                        'AU'=>['🇦🇺','Australie'],'FJ'=>['🇫🇯','Fidji'],'NZ'=>['🇳🇿','Nouvelle-Zélande'],
                        'PG'=>['🇵🇬','Papouasie-Nouvelle-Guinée'],'WS'=>['🇼🇸','Samoa'],'VU'=>['🇻🇺','Vanuatu'],
                    ];
                    @endphp
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

        {{-- Mot de passe --}}
        <div class="f-group">
            <label class="f-label" for="password">Mot de passe</label>
            <div class="f-icon-wrap f-pw-wrap">
                <span class="f-ico">🔒</span>
                <input id="password" class="f-input {{ $errors->has('password') ? 'f-input-err':'' }}"
                       type="password" name="password" required autocomplete="new-password" placeholder="Min. 8 caractères">
                <button type="button" class="f-pw-eye" onclick="togglePw('password',this)">👁️</button>
            </div>
            @error('password')<p class="f-error">{{ $message }}</p>@enderror
        </div>

        {{-- Confirmation --}}
        <div class="f-group">
            <label class="f-label" for="password_confirmation">Confirmer le mot de passe</label>
            <div class="f-icon-wrap f-pw-wrap">
                <span class="f-ico">🔒</span>
                <input id="password_confirmation" class="f-input"
                       type="password" name="password_confirmation"
                       required autocomplete="new-password" placeholder="Répéter le mot de passe">
                <button type="button" class="f-pw-eye" onclick="togglePw('password_confirmation',this)">👁️</button>
            </div>
        </div>

        <button type="submit" class="btn-auth">Créer mon compte →</button>

        <p style="text-align:center;margin-top:18px;font-size:13px;color:#6b7280">
            Déjà inscrit ?
            <a href="{{ route('login') }}" class="auth-link">Se connecter</a>
        </p>
    </form>

    <script>
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
            if (inp.type === 'password') { inp.type = 'text';     btn.textContent = '🙈'; }
            else                         { inp.type = 'password'; btn.textContent = '👁️'; }
        }
        document.addEventListener('DOMContentLoaded', function () {
            var cs = document.getElementById('country');
            if (cs && cs.value) updateFlag(cs);
            // Restore toggle fields based on old role
            var cur = document.getElementById('roleInput').value;
            if (cur === 'admin' || cur === 'company') {
                document.getElementById('phoneField').style.display   = 'none';
                document.getElementById('addressField').style.display = 'none';
            }
        });
    </script>

</x-guest-layout>
