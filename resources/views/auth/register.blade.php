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
            <x-input-label for="role" :value="__('Je m’inscris en tant que')" />
            <select id="role" name="role" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                <option value="client" {{ old('role') === 'client' ? 'selected' : '' }}>Client</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin de boutique</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
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
                          type="password"
                          name="password"
                          required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmer mot de passe -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                          type="password"
                          name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Bouton -->
        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
               href="{{ route('login') }}">
                {{ __('Déjà inscrit ?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Créer mon compte') }}
            </x-primary-button>
        </div>
    </form>

    <!-- Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const roleSelect = document.getElementById("role");
            const phoneField = document.getElementById("phoneField");
            const addressField = document.getElementById("addressField");

            function toggleFields() {
                if (roleSelect.value === "admin") {
                    phoneField.style.display = "none";
                    addressField.style.display = "none";
                } else {
                    phoneField.style.display = "block";
                    addressField.style.display = "block";
                }
            }

            toggleFields();
            roleSelect.addEventListener("change", toggleFields);
        });
    </script>
</x-guest-layout>
