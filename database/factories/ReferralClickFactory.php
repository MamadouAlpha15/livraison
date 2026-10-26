<?php

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\ReferralClick;
use App\Services\AffiliateService;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ReferralClick> */
class ReferralClickFactory extends Factory
{
    protected $model = ReferralClick::class;

    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory(),
            'ip_hash'      => AffiliateService::hash(fake()->ipv4()),
            'user_agent'   => fake()->userAgent(),
            'landing_url'  => url('/?ref=CODE'),
        ];
    }
}
