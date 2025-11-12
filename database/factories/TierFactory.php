<?php

namespace Database\Factories;

use App\Models\Tier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TierFactory extends Factory
{
    protected $model = Tier::class;

    public function definition(): array
    {
        $name = ucfirst($this->faker->unique()->word());
        $access = $this->faker->randomElement(['free', 'paid', 'vip']);

        return [
            'name' => $name,
            'slug' => Str::slug($name.'-'.$this->faker->unique()->numberBetween(1, 999)),
            'tagline' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'priority' => $this->faker->numberBetween(0, 50),
            'access_type' => $access,
            'is_default' => false,
            'is_active' => true,
            'price_monthly' => $access === 'free' ? null : $this->faker->randomFloat(2, 10, 199),
            'price_yearly' => $access === 'free' ? null : $this->faker->randomFloat(2, 100, 999),
            'currency' => 'USD',
            'features' => $this->faker->randomElements([
                'live-sessions',
                'community-access',
                'downloadables',
                'coaching',
            ], $this->faker->numberBetween(1, 3)),
            'metadata' => ['color' => $this->faker->hexColor()],
        ];
    }
}
