<?php

namespace Database\Factories;

use App\Models\CohortTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CohortTemplate>
 */
class CohortTemplateFactory extends Factory
{
    protected $model = CohortTemplate::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->sentence(3);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->randomNumber(4),
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['cohort', 'global']),
            'cohort_label' => $this->faker->randomElement(['B1-AM', 'B2-PM', 'Coaching']),
            'duration_minutes' => $this->faker->numberBetween(45, 90),
            'capacity' => $this->faker->numberBetween(6, 20),
            'enrolled_count' => 0,
            'price_amount' => $this->faker->randomElement([79, 99, 129]),
            'price_currency' => 'USD',
            'status' => $this->faker->randomElement(['draft', 'published']),
            'is_featured' => $this->faker->boolean(),
            'requires_package' => $this->faker->boolean(),
            'practice_package_id' => null,
            'slots' => [
                ['weekday' => 'monday', 'time' => '09:00'],
                ['weekday' => 'wednesday', 'time' => '09:00'],
            ],
            'meta' => [],
            'created_by' => User::factory(),
        ];
    }
}


