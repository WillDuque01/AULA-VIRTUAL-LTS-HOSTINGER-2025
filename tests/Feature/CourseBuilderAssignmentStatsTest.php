<?php

namespace Tests\Feature;

use App\Http\Livewire\Builder\CourseBuilder;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class CourseBuilderAssignmentStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_builder_exposes_assignment_stats(): void
    {
        $user = User::factory()->create();
        $lesson = $this->createAssignmentLesson();
        $assignment = $lesson->assignment;

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'body' => 'Primer intento',
            'status' => 'submitted',
            'max_points' => 100,
            'submitted_at' => now()->subDay(),
        ]);

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => User::factory()->create()->id,
            'body' => 'Entrega aprobada',
            'status' => 'approved',
            'score' => 90,
            'max_points' => 100,
            'submitted_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(CourseBuilder::class, ['course' => $lesson->chapter->course])
            ->assertSet('state.chapters.0.lessons.0.stats.pending', 1)
            ->assertSet('state.chapters.0.lessons.0.stats.approved', 1);
    }

    private function createAssignmentLesson(): Lesson
    {
        $course = Course::create([
            'slug' => Str::uuid(),
            'level' => 'b2',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Capítulo pruebas',
            'position' => 1,
        ]);

        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'assignment',
            'position' => 1,
            'config' => [
                'title' => 'Entrega builder',
                'instructions' => 'Describe tu proyecto.',
                'max_points' => 100,
                'passing_score' => 80,
                'requires_approval' => true,
            ],
        ]);

        Assignment::create([
            'lesson_id' => $lesson->id,
            'instructions' => 'Describe tu proyecto.',
            'max_points' => 100,
            'passing_score' => 80,
            'requires_approval' => true,
        ]);

        return $lesson->fresh('assignment', 'chapter.course');
    }
}


