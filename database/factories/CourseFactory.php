<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $slug = Str::slug($this->faker->unique()->words(3, true));

        return [
            'slug' => $slug,
            'level' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
            'published' => true,
        ];
    }
}

