<?php

namespace App\Http\Requests\Affiliation;

use App\Models\Affiliate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Demande de retrait du solde disponible par Mobile Money. */
class PayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'method' => ['required', Rule::in(array_keys(Affiliate::PAYOUT_METHODS))],
            'phone'  => ['required', 'string', 'regex:/^[+0-9 ().-]{8,20}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'method.required' => 'Choisis où recevoir ton argent.',
            'method.in'       => 'Choisis Orange Money, MTN MoMo ou PayCard.',
            'phone.required'  => 'Indique le numéro qui reçoit l\'argent.',
            'phone.regex'     => 'Ce numéro n\'est pas valide (exemple : 622 45 81 37).',
        ];
    }
}
