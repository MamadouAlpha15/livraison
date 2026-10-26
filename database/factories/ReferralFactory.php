<?php

namespace Database\Factories;

use App\Enums\ReferralStatus;
use App\Models\Affiliate;
use App\Models\Referral;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Referral> */
class ReferralFactory extends Factory
{
    protected $model = Referral::class;

    public function definition(): array
    {
        return [
            'affiliate_id'     => Affiliate::factory(),
            'referred_shop_id' => function () {
                $owner = User::factory()->create(['role' => 'admin']);
                $shop = Shop::forceCreate([
                    'user_id'     => $owner->id,
                    'name'        => 'Filleule ' . $owner->id,
                    'email'       => "filleule{$owner->id}@test.invalid",
                    'is_approved' => true,
                ]);
                $owner->forceFill(['shop_id' => $shop->id])->save();
                return $shop->id;
            },
            'status'  => ReferralStatus::Registered,
            'source'  => 'whatsapp',
            'flagged' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => ReferralStatus::Active, 'activated_at' => now()]);
    }

    public function paying(): static
    {
        return $this->state(fn () => ['status' => ReferralStatus::Paying, 'activated_at' => now(), 'first_paid_at' => now()]);
    }

    public function flagged(string $reason = 'Même adresse IP que le parrain.'): static
    {
        return $this->state(fn () => ['flagged' => true, 'flag_reason' => $reason]);
    }
}
