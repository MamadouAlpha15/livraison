<?php

namespace App\Http\Requests\Affiliation;

use App\Models\Affiliate;
use Illuminate\Foundation\Http\FormRequest;

/** Réglages du programme d'affiliation (équipe Shopio). */
class AffiliateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Affiliate::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'default_rate'        => ['required', 'numeric', 'between:0,100'],
            'commission_months'   => ['required', 'integer', 'between:1,120'],
            'validation_days'     => ['required', 'integer', 'between:0,365'],
            'payout_threshold'    => ['required', 'integer', 'between:0,100000000'],
            'reward_months'       => ['required', 'integer', 'between:0,12'],
            'cookie_days'         => ['required', 'integer', 'between:1,365'],
            'annual_reward_cap'   => ['required', 'integer', 'between:0,60'],
            'active_min_products' => ['required', 'integer', 'between:0,1000'],
            'active_min_orders'   => ['required', 'integer', 'between:0,1000'],
        ];
    }
}
