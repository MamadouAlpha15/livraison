<?php

namespace Database\Factories;

use App\Models\AffiliateSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AffiliateSetting> */
class AffiliateSettingFactory extends Factory
{
    protected $model = AffiliateSetting::class;

    public function definition(): array
    {
        return AffiliateSetting::DEFAULTS;
    }
}
