<?php

namespace Tests\Feature;

use App\Http\Livewire\Player;
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

class PlayerTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeline_marks_assignment_as_pending(): void
    {
        $user = User::factory()->create();
        $lesson = $this->createAssignmentLesson();

        Livewire::actingAs($user)
            ->test(Player::class, ['lesson' => $lesson])
            ->assertSet('timeline.0.lessons.0.status', 'pending');
    }

    public function test_timeline_marks_assignment_as_approved(): void
    {
        $user = User::factory()->create();
        $lesson = $this->createAssignmentLesson();

        AssignmentSubmission::create([
            'assignment_id' => $lesson->assignment->id,
            'user_id' => $user->id,
            'body' => 'Entrega final',
            'status' => 'approved',
            'score' => 95,
            'max_points' => 100,
            'submitted_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(Player::class, ['lesson' => $lesson])
            ->assertSet('timeline.0.lessons.0.status', 'approved');
    }

    private function createAssignmentLesson(): Lesson
    {
        $course = Course::create([
            'slug' => Str::uuid(),
            'level' => 'b1',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Timeline',
            'position' => 1,
        ]);

        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'assignment',
            'position' => 1,
            'config' => [
                'title' => 'Tarea crítica',
                'instructions' => 'Envía tu pitch.',
                'max_points' => 100,
                'passing_score' => 80,
                'requires_approval' => true,
            ],
        ]);

        Assignment::create([
            'lesson_id' => $lesson->id,
            'instructions' => 'Envía tu pitch.',
            'max_points' => 100,
            'passing_score' => 80,
            'requires_approval' => true,
        ]);

        return $lesson->fresh('assignment');
    }
}


