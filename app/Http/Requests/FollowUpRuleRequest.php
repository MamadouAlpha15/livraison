<?php

namespace App\Http\Requests;

use App\Services\FollowUp\FollowUpCatalog as Cat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** Réglage d'une relance (vendeur, ou admin pour « vendeur inactif »). Délais saisis en heures ou en jours. */
class FollowUpRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'enabled'       => $this->boolean('enabled'),
            'excluded_days' => array_map('intval', (array) $this->input('excluded_days', [])),
            'channels'      => array_values((array) $this->input('channels', [])),
        ]);
    }

    public function rules(): array
    {
        $shopId = $this->user()->shop_id;
        return [
            'enabled'          => ['boolean'],
            'delay_value'      => ['required', 'integer', 'min:0', 'max:365'],
            'delay_unit'       => ['required', Rule::in(['heures', 'jours'])],
            'max_attempts'     => ['required', 'integer', 'between:1,5'],
            'interval_value'   => ['required', 'integer', 'min:1', 'max:365'],
            'interval_unit'    => ['required', Rule::in(['heures', 'jours'])],
            'template'         => ['nullable', 'string', 'max:1000'],
            'tone'             => ['required', Rule::in(array_keys(Cat::TONES))],
            'locale'           => ['required', Rule::in(array_keys(Cat::LOCALES))],
            'promo_code_id'    => ['nullable', 'integer', Rule::exists('promo_codes', 'id')->where('shop_id', $shopId)],
            'send_from_hour'   => ['required', 'integer', 'between:0,22'],
            'send_to_hour'     => ['required', 'integer', 'between:1,23', 'gt:send_from_hour'],
            'excluded_days'    => ['array', 'max:6'],
            'excluded_days.*'  => ['integer', 'between:0,6'],
            'channels'         => ['required', 'array', 'min:1'],
            'channels.*'       => [Rule::in(array_keys(Cat::CHANNELS))],
        ];
    }

    public function messages(): array
    {
        return [
            'channels.required' => 'Choisis au moins un canal d\'envoi.',
            'channels.min'      => 'Choisis au moins un canal d\'envoi.',
            'excluded_days.max' => 'Garde au moins un jour d\'envoi dans la semaine.',
            'promo_code_id.exists' => 'Ce code promo n\'appartient pas à ta boutique.',
            'send_to_hour.gt'      => 'L\'heure de fin doit être après l\'heure de début.',
        ];
    }

    public function after(): array
    {
        return [function (Validator $v) {
            if ($this->filled('template') && ($unknown = \App\Services\FollowUp\TemplateRenderer::unknownVariables($this->input('template')))) {
                $v->errors()->add('template', 'Variable inconnue : ' . implode(', ', $unknown) . '. Utilise seulement celles de la liste.');
            }
        }];
    }

    /** Valeurs à enregistrer (délais convertis en minutes) */
    public function ruleData(): array
    {
        $minutes = fn ($value, $unit) => (int) $value * ($unit === 'jours' ? 1440 : 60);
        $data = $this->safe()->only(['enabled', 'max_attempts', 'tone', 'locale', 'promo_code_id', 'send_from_hour', 'send_to_hour', 'excluded_days', 'channels']);
        $data['delay_minutes'] = $minutes($this->input('delay_value'), $this->input('delay_unit'));
        $data['interval_minutes'] = $minutes($this->input('interval_value'), $this->input('interval_unit'));
        $template = filled($this->input('template')) ? trim($this->input('template')) : null;
        // Texte par défaut laissé tel quel : on garde « par défaut » pour suivre le ton et la langue choisis
        $default = \App\Services\FollowUp\FollowUpCatalog::template((string) ($this->route('type') ?: Cat::INACTIVE_VENDOR), $data['tone'], $data['locale']);
        $data['template'] = $template === trim($default) ? null : $template;
        return $data;
    }
}
