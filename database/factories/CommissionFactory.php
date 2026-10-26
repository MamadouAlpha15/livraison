<?php

namespace Database\Factories;

use App\Enums\CommissionStatus;
use App\Models\Commission;
use App\Models\Referral;
use App\Models\Shop;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Commission> */
class CommissionFactory extends Factory
{
    protected $model = Commission::class;

    public function definition(): array
    {
        return [
            'referral_id'  => Referral::factory()->paying(),
            'affiliate_id' => fn (array $attributes) => Referral::find($attributes['referral_id'])->affiliate_id,
            'subscription_payment_id' => fn (array $attributes) => Subscription::create([
                'subscriber_type'   => Shop::class,
                'subscriber_id'     => Referral::find($attributes['referral_id'])->referred_shop_id,
                'plan'              => 'pro',
                'amount'            => 150000,
                'currency'          => 'GNF',
                'payment_method'    => 'orange_money',
                'payment_reference' => 'TEST-' . fake()->unique()->uuid(),
                'status'            => 'active',
                'started_at'        => now(),
                'expires_at'        => now()->addDays(30),
            ])->id,
            'amount'       => 30000,
            'status'       => CommissionStatus::Pending,
            'available_at' => now()->addDays(30),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => CommissionStatus::Approved, 'available_at' => now()->subDay()]);
    }
}
