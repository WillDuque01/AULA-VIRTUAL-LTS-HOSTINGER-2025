<?php

namespace Tests\Feature;

use App\Livewire\Student\Dashboard as StudentDashboard;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StudentDashboardRejectedAssignmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_highlights_rejected_assignments(): void
    {
        $student = User::factory()->create();
        $lesson = $this->createAssignmentLesson();

        AssignmentSubmission::create([
            'assignment_id' => $lesson->assignment->id,
            'user_id' => $student->id,
            'body' => 'Entrega',
            'status' => 'rejected',
            'feedback' => 'Necesitas expandir tu argumento',
            'max_points' => 100,
            'submitted_at' => now(),
        ]);

        Livewire::actingAs($student)
            ->test(StudentDashboard::class)
            ->assertSee('Necesitas expandir tu argumento');
    }

    private function createAssignmentLesson(): Lesson
    {
        $course = Course::create([
            'slug' => 'dashboard-assignments',
            'level' => 'b1',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Unidad 1',
            'position' => 1,
        ]);

        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'assignment',
            'position' => 1,
            'config' => [
                'title' => 'Ensayo cultural',
                'instructions' => 'Describe la gastronomía local.',
                'max_points' => 100,
                'passing_score' => 80,
                'requires_approval' => true,
            ],
        ]);

        Assignment::create([
            'lesson_id' => $lesson->id,
            'instructions' => 'Describe la gastronomía local.',
            'max_points' => 100,
            'passing_score' => 80,
            'requires_approval' => true,
        ]);

        return $lesson->fresh('assignment');
    }
}


