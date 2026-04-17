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
                    <option value="SN" data-flag="🇸🇳" {{ old('country') === 'SN' ? 'selected' : '' }}>🇸🇳 Sénégal</option>
                    <option value="GN" data-flag="🇬🇳" {{ old('country') === 'GN' ? 'selected' : '' }}>🇬🇳 Guinée</option>
                    <option value="CI" data-flag="🇨🇮" {{ old('country') === 'CI' ? 'selected' : '' }}>🇨🇮 Côte d'Ivoire</option>
                    <option value="ML" data-flag="🇲🇱" {{ old('country') === 'ML' ? 'selected' : '' }}>🇲🇱 Mali</option>
                    <option value="BF" data-flag="🇧🇫" {{ old('country') === 'BF' ? 'selected' : '' }}>🇧🇫 Burkina Faso</option>
                    <option value="CM" data-flag="🇨🇲" {{ old('country') === 'CM' ? 'selected' : '' }}>🇨🇲 Cameroun</option>
                    <option value="TG" data-flag="🇹🇬" {{ old('country') === 'TG' ? 'selected' : '' }}>🇹🇬 Togo</option>
                    <option value="BJ" data-flag="🇧🇯" {{ old('country') === 'BJ' ? 'selected' : '' }}>🇧🇯 Bénin</option>
                    <option value="NE" data-flag="🇳🇪" {{ old('country') === 'NE' ? 'selected' : '' }}>🇳🇪 Niger</option>
                    <option value="GA" data-flag="🇬🇦" {{ old('country') === 'GA' ? 'selected' : '' }}>🇬🇦 Gabon</option>
                    <option value="CG" data-flag="🇨🇬" {{ old('country') === 'CG' ? 'selected' : '' }}>🇨🇬 Congo</option>
                    <option value="CD" data-flag="🇨🇩" {{ old('country') === 'CD' ? 'selected' : '' }}>🇨🇩 RD Congo</option>
                    <option value="GH" data-flag="🇬🇭" {{ old('country') === 'GH' ? 'selected' : '' }}>🇬🇭 Ghana</option>
                    <option value="NG" data-flag="🇳🇬" {{ old('country') === 'NG' ? 'selected' : '' }}>🇳🇬 Nigeria</option>
                    <option value="MA" data-flag="🇲🇦" {{ old('country') === 'MA' ? 'selected' : '' }}>🇲🇦 Maroc</option>
                    <option value="DZ" data-flag="🇩🇿" {{ old('country') === 'DZ' ? 'selected' : '' }}>🇩🇿 Algérie</option>
                    <option value="TN" data-flag="🇹🇳" {{ old('country') === 'TN' ? 'selected' : '' }}>🇹🇳 Tunisie</option>
                    <option value="FR" data-flag="🇫🇷" {{ old('country') === 'FR' ? 'selected' : '' }}>🇫🇷 France</option>
                    <option value="BE" data-flag="🇧🇪" {{ old('country') === 'BE' ? 'selected' : '' }}>🇧🇪 Belgique</option>
                    <option value="CA" data-flag="🇨🇦" {{ old('country') === 'CA' ? 'selected' : '' }}>🇨🇦 Canada</option>
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
