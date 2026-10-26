<?php

namespace App\Http\Requests\Affiliation;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

/** Inscription d'une personne externe au programme d'affiliation (page publique /devenir-affilie). */
class JoinAffiliateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() === null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
        }
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:120'],
            // Comme l'inscription classique : une inscription abandonnée (jamais vérifiée) libère l'e-mail
            'email'    => ['required', 'email', 'max:191',
                           Rule::unique(User::class)->where(fn ($q) => $q->whereNotNull('email_verified_at'))],
            'phone'    => ['required', 'string', 'regex:/^[+0-9 ().-]{8,20}$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms'    => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Indique ton nom.',
            'email.required'     => 'Indique ton adresse e-mail : ton code de vérification y sera envoyé.',
            'email.email'        => 'Cette adresse e-mail n\'est pas valide.',
            'email.unique'       => 'Un compte Shopio utilise déjà cette adresse. Connecte-toi, ou utilise une autre adresse.',
            'phone.required'     => 'Indique ton numéro : il sert à te verser tes commissions.',
            'phone.regex'        => 'Ce numéro n\'est pas valide (exemple : 622 45 81 37).',
            'password.required'  => 'Choisis un mot de passe.',
            'password.confirmed' => 'Les deux mots de passe ne sont pas identiques.',
            'terms.accepted'     => 'Tu dois accepter les conditions d\'utilisation.',
        ];
    }
}
