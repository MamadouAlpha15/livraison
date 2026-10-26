<?php

namespace App\Http\Requests\Affiliation;

use App\Models\Affiliate;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Équipe Shopio : nouvel affilié externe (influenceur, partenaire…), avec ou sans accès au tableau de bord. */
class StoreAffiliateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Affiliate::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'with_access' => $this->boolean('with_access'),
            'code'        => $this->filled('code') ? strtoupper(trim((string) $this->input('code'))) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:120'],
            'email'           => ['nullable', 'email', 'max:191', Rule::requiredIf($this->boolean('with_access')),
                                  Rule::when($this->boolean('with_access'), [Rule::unique(User::class, 'email')])],
            'phone'           => ['nullable', 'string', 'max:30'],
            'code'            => ['nullable', 'regex:/^[A-Z0-9]{4,12}$/', Rule::unique('affiliates', 'code'), Rule::unique('users', 'referral_code')],
            'commission_rate' => ['nullable', 'numeric', 'between:0,100'],
            'payout_method'   => ['nullable', Rule::in(array_keys(Affiliate::PAYOUT_METHODS))],
            'payout_phone'    => ['nullable', 'string', 'max:30'],
            'with_access'     => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Une adresse e-mail est nécessaire pour créer un accès au tableau de bord.',
            'email.unique'   => 'Un compte Shopio utilise déjà cette adresse e-mail.',
            'code.regex'     => 'Le code contient 4 à 12 lettres ou chiffres, sans espace.',
            'code.unique'    => 'Ce code est déjà utilisé.',
        ];
    }
}
