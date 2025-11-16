<?php

namespace Database\Factories;

use App\Models\StudentActivitySnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\StudentActivitySnapshot>
 */
class StudentActivitySnapshotFactory extends Factory
{
    protected $model = StudentActivitySnapshot::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => null,
            'lesson_id' => null,
            'practice_package_id' => null,
            'category' => 'lesson_progress',
            'scope' => 'player',
            'value' => $this->faker->numberBetween(0, 100),
            'payload' => ['notes' => $this->faker->sentence()],
            'captured_at' => now(),
        ];
    }
}

