<?php

namespace App\Http\Requests;

use App\Models\Referral;
use App\Services\AffiliateService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Création de la boutique (fin de l'inscription vendeur) : champs de la boutique, code de parrainage
 * (pré-rempli depuis le cookie d'un lien ?ref=) et « Comment as-tu connu Shopio ? ».
 */
class StoreShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('referral_code')) {
            $this->merge(['referral_code' => strtoupper(trim((string) $this->input('referral_code')))]);
        }
    }

    public function rules(): array
    {
        $emailRules = ['nullable', 'email'];
        // Unique uniquement si l'email n'est pas celui de l'utilisateur connecté
        if ($this->filled('email') && $this->input('email') !== $this->user()->email) {
            $emailRules[] = Rule::unique('shops', 'email');
        }

        return [
            'name'        => 'required|string|max:255',
            'type'        => 'nullable|string|max:100',
            'address'     => 'nullable|string|max:255',
            'email'       => $emailRules,
            'phone'       => 'nullable|string|max:20',
            'image'       => 'nullable|image',
            'description' => 'nullable|string',
            'commission_rate' => ['nullable', 'numeric', 'between:0,100'],
            'currency' => ['nullable', 'string', 'max:10'],
            // Mobile Money où reverser les paiements en ligne (ChapChap Pay)
            'payout_wallet_type'   => ['nullable', Rule::in(['paycard', 'orange_money', 'mtn_momo', 'kulu', 'soutra_money', 'akiba'])],
            'payout_wallet_number' => ['nullable', 'string', 'max:30'],
            // Programme d'affiliation
            'referral_code' => ['nullable', 'string', 'max:20'],
            'source'        => ['nullable', Rule::in(array_keys(Referral::SOURCES))],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if (!$this->filled('referral_code') || $validator->errors()->has('referral_code')) {
                return;
            }
            $affiliates = app(AffiliateService::class);
            $affiliate = $affiliates->findActiveByCode($this->input('referral_code'));
            if (!$affiliate) {
                $validator->errors()->add('referral_code', 'Ce code de parrainage n\'existe pas ou n\'est plus actif. Vérifie-le ou laisse le champ vide.');
                return;
            }
            $email = $this->input('email') ?: $this->user()->email;
            if ($reason = $affiliates->selfReferralReason($affiliate, $this->user(), $email, $this->input('phone'))) {
                $validator->errors()->add('referral_code', $reason);
            }
        }];
    }

    public function attributes(): array
    {
        return ['referral_code' => 'code de parrainage', 'source' => 'réponse « Comment as-tu connu Shopio ? »'];
    }
}
