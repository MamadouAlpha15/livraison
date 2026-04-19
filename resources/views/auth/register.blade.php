<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nom complet -->
        <div>
            <x-input-label for="name" :value="__('Nom complet')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                          :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Adresse email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                          :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Choix du rôle -->
        <div class="mt-4">
            <x-input-label for="role" value="Je m'inscris en tant que" />
            <select id="role" name="role"
                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring focus:ring-indigo-200"
                    required>
                <option value="client"  {{ old('role','client') === 'client'  ? 'selected' : '' }}>🛒 Client</option>
                <option value="admin"   {{ old('role') === 'admin'   ? 'selected' : '' }}>🏪 Admin de boutique</option>
                <option value="company" {{ old('role') === 'company' ? 'selected' : '' }}>🚚 Entreprise de livraison</option>
                <option value="livreur" {{ old('role') === 'livreur' ? 'selected' : '' }}>🛵 Livreur</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Pays -->
        <div class="mt-4">
            <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#374151;margin-bottom:5px">
                🌍 Votre pays
            </label>
            <div style="position:relative">
                <span id="flagPreview"
                      style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:20px;pointer-events:none;line-height:1;z-index:1">
                    🌍
                </span>
                <select id="country" name="country" required
                        onchange="updateFlag(this)"
                        style="width:100%;padding:9px 12px 9px 40px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;color:#374151;background:#fff;appearance:none;-webkit-appearance:none;cursor:pointer;box-shadow:0 1px 2px rgba(0,0,0,.05)">
                    <option value="">-- Sélectionner votre pays --</option>
                    @php
                    $countries = [
                        /* ── Afrique de l'Ouest ── */
                        'BJ'=>['🇧🇯','Bénin'],
                        'BF'=>['🇧🇫','Burkina Faso'],
                        'CV'=>['🇨🇻','Cap-Vert'],
                        'CI'=>['🇨🇮','Côte d\'Ivoire'],
                        'GM'=>['🇬🇲','Gambie'],
                        'GH'=>['🇬🇭','Ghana'],
                        'GN'=>['🇬🇳','Guinée'],
                        'GW'=>['🇬🇼','Guinée-Bissau'],
                        'GQ'=>['🇬🇶','Guinée équatoriale'],
                        'LR'=>['🇱🇷','Libéria'],
                        'ML'=>['🇲🇱','Mali'],
                        'MR'=>['🇲🇷','Mauritanie'],
                        'NE'=>['🇳🇪','Niger'],
                        'NG'=>['🇳🇬','Nigeria'],
                        'SN'=>['🇸🇳','Sénégal'],
                        'SL'=>['🇸🇱','Sierra Leone'],
                        'TG'=>['🇹🇬','Togo'],
                        /* ── Afrique Centrale ── */
                        'AO'=>['🇦🇴','Angola'],
                        'CM'=>['🇨🇲','Cameroun'],
                        'CF'=>['🇨🇫','Centrafrique'],
                        'TD'=>['🇹🇩','Tchad'],
                        'CG'=>['🇨🇬','Congo'],
                        'CD'=>['🇨🇩','RD Congo'],
                        'GA'=>['🇬🇦','Gabon'],
                        'ST'=>['🇸🇹','São Tomé-et-Príncipe'],
                        /* ── Afrique de l'Est ── */
                        'BI'=>['🇧🇮','Burundi'],
                        'KM'=>['🇰🇲','Comores'],
                        'DJ'=>['🇩🇯','Djibouti'],
                        'ER'=>['🇪🇷','Érythrée'],
                        'ET'=>['🇪🇹','Éthiopie'],
                        'KE'=>['🇰🇪','Kenya'],
                        'MG'=>['🇲🇬','Madagascar'],
                        'MW'=>['🇲🇼','Malawi'],
                        'MU'=>['🇲🇺','Maurice'],
                        'MZ'=>['🇲🇿','Mozambique'],
                        'RW'=>['🇷🇼','Rwanda'],
                        'SC'=>['🇸🇨','Seychelles'],
                        'SO'=>['🇸🇴','Somalie'],
                        'SS'=>['🇸🇸','Soudan du Sud'],
                        'SD'=>['🇸🇩','Soudan'],
                        'TZ'=>['🇹🇿','Tanzanie'],
                        'UG'=>['🇺🇬','Ouganda'],
                        'ZM'=>['🇿🇲','Zambie'],
                        'ZW'=>['🇿🇼','Zimbabwe'],
                        /* ── Afrique Australe ── */
                        'BW'=>['🇧🇼','Botswana'],
                        'LS'=>['🇱🇸','Lesotho'],
                        'NA'=>['🇳🇦','Namibie'],
                        'ZA'=>['🇿🇦','Afrique du Sud'],
                        'SZ'=>['🇸🇿','Eswatini'],
                        /* ── Afrique du Nord ── */
                        'DZ'=>['🇩🇿','Algérie'],
                        'EG'=>['🇪🇬','Égypte'],
                        'LY'=>['🇱🇾','Libye'],
                        'MA'=>['🇲🇦','Maroc'],
                        'TN'=>['🇹🇳','Tunisie'],
                        /* ── Europe ── */
                        'AL'=>['🇦🇱','Albanie'],
                        'DE'=>['🇩🇪','Allemagne'],
                        'AT'=>['🇦🇹','Autriche'],
                        'BE'=>['🇧🇪','Belgique'],
                        'BY'=>['🇧🇾','Biélorussie'],
                        'BA'=>['🇧🇦','Bosnie-Herzégovine'],
                        'BG'=>['🇧🇬','Bulgarie'],
                        'HR'=>['🇭🇷','Croatie'],
                        'CY'=>['🇨🇾','Chypre'],
                        'CZ'=>['🇨🇿','Tchéquie'],
                        'DK'=>['🇩🇰','Danemark'],
                        'ES'=>['🇪🇸','Espagne'],
                        'EE'=>['🇪🇪','Estonie'],
                        'FI'=>['🇫🇮','Finlande'],
                        'FR'=>['🇫🇷','France'],
                        'GR'=>['🇬🇷','Grèce'],
                        'HU'=>['🇭🇺','Hongrie'],
                        'IE'=>['🇮🇪','Irlande'],
                        'IT'=>['🇮🇹','Italie'],
                        'LV'=>['🇱🇻','Lettonie'],
                        'LT'=>['🇱🇹','Lituanie'],
                        'LU'=>['🇱🇺','Luxembourg'],
                        'MK'=>['🇲🇰','Macédoine du Nord'],
                        'MT'=>['🇲🇹','Malte'],
                        'MD'=>['🇲🇩','Moldavie'],
                        'ME'=>['🇲🇪','Monténégro'],
                        'NL'=>['🇳🇱','Pays-Bas'],
                        'NO'=>['🇳🇴','Norvège'],
                        'PL'=>['🇵🇱','Pologne'],
                        'PT'=>['🇵🇹','Portugal'],
                        'RO'=>['🇷🇴','Roumanie'],
                        'GB'=>['🇬🇧','Royaume-Uni'],
                        'RU'=>['🇷🇺','Russie'],
                        'RS'=>['🇷🇸','Serbie'],
                        'SK'=>['🇸🇰','Slovaquie'],
                        'SI'=>['🇸🇮','Slovénie'],
                        'SE'=>['🇸🇪','Suède'],
                        'CH'=>['🇨🇭','Suisse'],
                        'UA'=>['🇺🇦','Ukraine'],
                        /* ── Amériques ── */
                        'AR'=>['🇦🇷','Argentine'],
                        'BO'=>['🇧🇴','Bolivie'],
                        'BR'=>['🇧🇷','Brésil'],
                        'CA'=>['🇨🇦','Canada'],
                        'CL'=>['🇨🇱','Chili'],
                        'CO'=>['🇨🇴','Colombie'],
                        'CR'=>['🇨🇷','Costa Rica'],
                        'CU'=>['🇨🇺','Cuba'],
                        'DO'=>['🇩🇴','Rép. Dominicaine'],
                        'EC'=>['🇪🇨','Équateur'],
                        'SV'=>['🇸🇻','Salvador'],
                        'GT'=>['🇬🇹','Guatemala'],
                        'HT'=>['🇭🇹','Haïti'],
                        'HN'=>['🇭🇳','Honduras'],
                        'JM'=>['🇯🇲','Jamaïque'],
                        'MX'=>['🇲🇽','Mexique'],
                        'NI'=>['🇳🇮','Nicaragua'],
                        'PA'=>['🇵🇦','Panama'],
                        'PY'=>['🇵🇾','Paraguay'],
                        'PE'=>['🇵🇪','Pérou'],
                        'PR'=>['🇵🇷','Porto Rico'],
                        'TT'=>['🇹🇹','Trinité-et-Tobago'],
                        'US'=>['🇺🇸','États-Unis'],
                        'UY'=>['🇺🇾','Uruguay'],
                        'VE'=>['🇻🇪','Venezuela'],
                        /* ── Asie ── */
                        'SA'=>['🇸🇦','Arabie Saoudite'],
                        'AM'=>['🇦🇲','Arménie'],
                        'AZ'=>['🇦🇿','Azerbaïdjan'],
                        'BH'=>['🇧🇭','Bahreïn'],
                        'BD'=>['🇧🇩','Bangladesh'],
                        'KH'=>['🇰🇭','Cambodge'],
                        'CN'=>['🇨🇳','Chine'],
                        'KP'=>['🇰🇵','Corée du Nord'],
                        'KR'=>['🇰🇷','Corée du Sud'],
                        'AE'=>['🇦🇪','Émirats arabes unis'],
                        'GE'=>['🇬🇪','Géorgie'],
                        'IN'=>['🇮🇳','Inde'],
                        'ID'=>['🇮🇩','Indonésie'],
                        'IQ'=>['🇮🇶','Irak'],
                        'IR'=>['🇮🇷','Iran'],
                        'IL'=>['🇮🇱','Israël'],
                        'JP'=>['🇯🇵','Japon'],
                        'JO'=>['🇯🇴','Jordanie'],
                        'KZ'=>['🇰🇿','Kazakhstan'],
                        'KW'=>['🇰🇼','Koweït'],
                        'KG'=>['🇰🇬','Kirghizistan'],
                        'LA'=>['🇱🇦','Laos'],
                        'LB'=>['🇱🇧','Liban'],
                        'MY'=>['🇲🇾','Malaisie'],
                        'MV'=>['🇲🇻','Maldives'],
                        'MN'=>['🇲🇳','Mongolie'],
                        'MM'=>['🇲🇲','Myanmar'],
                        'NP'=>['🇳🇵','Népal'],
                        'OM'=>['🇴🇲','Oman'],
                        'UZ'=>['🇺🇿','Ouzbékistan'],
                        'PK'=>['🇵🇰','Pakistan'],
                        'PS'=>['🇵🇸','Palestine'],
                        'PH'=>['🇵🇭','Philippines'],
                        'QA'=>['🇶🇦','Qatar'],
                        'SG'=>['🇸🇬','Singapour'],
                        'LK'=>['🇱🇰','Sri Lanka'],
                        'SY'=>['🇸🇾','Syrie'],
                        'TJ'=>['🇹🇯','Tadjikistan'],
                        'TW'=>['🇹🇼','Taïwan'],
                        'TH'=>['🇹🇭','Thaïlande'],
                        'TM'=>['🇹🇲','Turkménistan'],
                        'TR'=>['🇹🇷','Turquie'],
                        'VN'=>['🇻🇳','Vietnam'],
                        'YE'=>['🇾🇪','Yémen'],
                        /* ── Océanie ── */
                        'AU'=>['🇦🇺','Australie'],
                        'FJ'=>['🇫🇯','Fidji'],
                        'NZ'=>['🇳🇿','Nouvelle-Zélande'],
                        'PG'=>['🇵🇬','Papouasie-Nouvelle-Guinée'],
                        'WS'=>['🇼🇸','Samoa'],
                        'VU'=>['🇻🇺','Vanuatu'],
                    ];
                    @endphp
                    @foreach($countries as $code => $info)
                    <option value="{{ $code }}" data-flag="{{ $info[0] }}" {{ old('country') === $code ? 'selected' : '' }}>{{ $info[0] }} {{ $info[1] }}</option>
                    @endforeach
                </select>
            </div>
            @error('country')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p style="font-size:11px;color:#9ca3af;margin-top:4px">
                Vous verrez uniquement les boutiques disponibles dans votre pays.
            </p>
        </div>

        <!-- Téléphone -->
        <div id="phoneField" class="mt-4">
            <x-input-label for="phone" :value="__('Téléphone')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone"
                          :value="old('phone')" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Adresse -->
        <div id="addressField" class="mt-4">
            <x-input-label for="address" :value="__('Adresse de livraison')" />
            <x-text-input id="address" class="block mt-1 w-full" type="text" name="address"
                          :value="old('address')" />
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <!-- Mot de passe -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Mot de passe')" />
            <x-text-input id="password" class="block mt-1 w-full"
                          type="password" name="password"
                          required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmer mot de passe -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                          type="password" name="password_confirmation"
                          required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Bouton -->
        <div class="flex items-center justify-end mt-6">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
               href="{{ route('login') }}">
                {{ __('Déjà inscrit ?') }}
            </a>
            <x-primary-button class="ms-4">
                {{ __('Créer mon compte') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        function updateFlag(select) {
            var opt  = select.options[select.selectedIndex];
            var flag = opt.getAttribute('data-flag') || '\uD83C\uDF0D';
            document.getElementById('flagPreview').textContent = flag;
        }

        document.addEventListener('DOMContentLoaded', function () {
            var countrySelect = document.getElementById('country');
            if (countrySelect.value) updateFlag(countrySelect);

            var roleSelect   = document.getElementById('role');
            var phoneField   = document.getElementById('phoneField');
            var addressField = document.getElementById('addressField');

            function toggleFields() {
                var hide = roleSelect.value === 'admin' || roleSelect.value === 'company';
                phoneField.style.display   = hide ? 'none' : 'block';
                addressField.style.display = hide ? 'none' : 'block';
            }

            toggleFields();
            roleSelect.addEventListener('change', toggleFields);
        });
    </script>
</x-guest-layout>
