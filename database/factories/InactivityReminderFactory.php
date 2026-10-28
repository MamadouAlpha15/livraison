<?php

namespace Database\Factories;

use App\Models\InactivityReminder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InactivityReminder> */
class InactivityReminderFactory extends Factory
{
    protected $model = InactivityReminder::class;

    public function definition(): array
    {
        return [
            'user_id'  => User::factory()->state(['role' => 'client']),
            'audience' => InactivityReminder::AUDIENCE_CLIENT,
            'step'     => 1,
            'channels' => ['mail'],
            'sent_at'  => now(),
        ];
    }
}
