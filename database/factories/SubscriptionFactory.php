<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tier_id' => Tier::factory(),
            'provider' => 'simulator',
            'status' => 'active',
            'amount' => $this->faker->randomFloat(2, 5, 199),
            'currency' => 'USD',
            'starts_at' => now(),
            'renews_at' => now()->addMonth(),
            'metadata' => ['origin' => 'factory'],
        ];
    }
}
