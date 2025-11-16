<?php

namespace Database\Factories;

use App\Models\TeacherActivitySnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TeacherActivitySnapshot>
 */
class TeacherActivitySnapshotFactory extends Factory
{
    protected $model = TeacherActivitySnapshot::class;

    public function definition(): array
    {
        return [
            'teacher_id' => User::factory(),
            'course_id' => null,
            'lesson_id' => null,
            'practice_package_id' => null,
            'category' => 'practice_followup',
            'scope' => 'planner',
            'value' => $this->faker->numberBetween(0, 25),
            'payload' => ['notes' => $this->faker->sentence()],
            'captured_at' => now(),
        ];
    }
}

