<?php

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\AffiliateReward;
use App\Models\Referral;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AffiliateReward> */
class AffiliateRewardFactory extends Factory
{
    protected $model = AffiliateReward::class;

    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory()->vendor(),
            'referral_id'  => fn (array $attributes) => Referral::factory()->paying()->create(['affiliate_id' => $attributes['affiliate_id']])->id,
            'shop_id'      => fn (array $attributes) => Affiliate::find($attributes['affiliate_id'])->user->shop_id,
            'kind'         => AffiliateReward::KIND_EXTENSION,
            'months'       => 1,
            'amount_gnf'   => 0,
        ];
    }
}
