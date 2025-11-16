<?php

namespace Database\Factories;

use App\Models\PracticePackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PracticePackage>
 */
class PracticePackageFactory extends Factory
{
    protected $model = PracticePackage::class;

    public function definition(): array
    {
        return [
            'creator_id' => User::factory(),
            'lesson_id' => null,
            'title' => $this->faker->sentence(3),
            'subtitle' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'sessions_count' => 4,
            'price_amount' => 59.00,
            'price_currency' => 'USD',
            'is_global' => true,
            'visibility' => 'public',
            'delivery_platform' => 'discord',
            'delivery_url' => null,
            'status' => 'published',
            'meta' => [],
        ];
    }
}


