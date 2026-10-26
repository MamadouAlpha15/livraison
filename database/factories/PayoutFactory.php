<?php

namespace Database\Factories;

use App\Enums\PayoutStatus;
use App\Models\Affiliate;
use App\Models\Payout;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payout> */
class PayoutFactory extends Factory
{
    protected $model = Payout::class;

    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory(),
            'amount'       => 60000,
            'method'       => 'orange_money',
            'phone'        => '622458137',
            'status'       => PayoutStatus::Requested,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => ['status' => PayoutStatus::Paid, 'reference' => 'OM-' . fake()->numerify('########'), 'processed_at' => now()]);
    }
}
