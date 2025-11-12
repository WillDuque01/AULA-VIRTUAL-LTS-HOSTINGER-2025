<?php

namespace Database\Factories;

use App\Models\StudentGroup;
use App\Models\Tier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StudentGroupFactory extends Factory
{
    protected $model = StudentGroup::class;

    public function definition(): array
    {
        $name = 'Group '.$this->faker->unique()->monthName().' '.$this->faker->year();

        return [
            'name' => $name,
            'slug' => Str::slug($name.'-'.$this->faker->unique()->numberBetween(1, 999)),
            'tier_id' => Tier::query()->inRandomOrder()->value('id') ?? Tier::factory()->create()->id,
            'description' => $this->faker->sentence(12),
            'capacity' => $this->faker->numberBetween(10, 40),
            'starts_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'ends_at' => $this->faker->optional()->dateTimeBetween('+1 month', '+3 months'),
            'is_active' => true,
            'metadata' => null,
        ];
    }
}
