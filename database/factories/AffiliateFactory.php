<?php

namespace Database\Factories;

use App\Enums\AffiliateStatus;
use App\Enums\AffiliateType;
use App\Models\Affiliate;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Affiliate> */
class AffiliateFactory extends Factory
{
    protected $model = Affiliate::class;

    public function definition(): array
    {
        return [
            'user_id'         => null,
            'type'            => AffiliateType::External,
            'code'            => strtoupper(fake()->unique()->bothify('AF????##')),
            'name'            => fake()->name(),
            'email'           => fake()->unique()->safeEmail(),
            'phone'           => '62' . fake()->unique()->numerify('#######'),
            'commission_rate' => 20,
            'status'          => AffiliateStatus::Active,
        ];
    }

    /** Propriétaire d'une boutique qui parraine (compte « admin » avec sa boutique) */
    public function vendor(): static
    {
        return $this->state(fn () => [
            'type'    => AffiliateType::Vendor,
            'name'    => null,
            'email'   => null,
            'phone'   => null,
            'user_id' => function () {
                $user = User::factory()->create(['role' => 'admin', 'phone' => '66' . fake()->unique()->numerify('#######')]);
                $shop = Shop::forceCreate([
                    'user_id'     => $user->id,
                    'name'        => 'Boutique ' . $user->name,
                    'email'       => "parrain{$user->id}@test.invalid",
                    'is_approved' => true,
                ]);
                $user->forceFill(['shop_id' => $shop->id])->save();
                return $user->id;
            },
        ]);
    }

    /** Affilié externe avec un compte pour son tableau de bord */
    public function withAccount(): static
    {
        return $this->state(fn () => [
            'user_id' => User::factory()->state(['role' => 'affilie']),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => AffiliateStatus::Suspended]);
    }
}
