<?php

namespace Tests\Feature;

use App\Livewire\Student\Dashboard;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StudentDashboardAssignmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_lists_upcoming_assignments(): void
    {
        $user = User::factory()->create();

        $course = Course::create([
            'slug' => 'student-dashboard-course',
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
                'title' => 'Proyecto Final',
                'instructions' => 'Entrega tu pitching deck.',
                'due_at' => now()->addDays(2)->toIso8601String(),
                'max_points' => 100,
                'passing_score' => 80,
                'requires_approval' => true,
            ],
        ]);

        Assignment::create([
            'lesson_id' => $lesson->id,
            'instructions' => 'Entrega tu pitching deck.',
            'due_at' => now()->addDays(2),
            'max_points' => 100,
            'passing_score' => 80,
            'requires_approval' => true,
        ]);

        app()->setLocale('es');

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSee(__('dashboard.assignments.student_title'))
            ->assertSee(__('dashboard.assignments.requires_approval'))
            ->assertSee('Proyecto Final');
    }

    public function test_dashboard_exposes_assignment_summary(): void
    {
        $user = User::factory()->create();

        $course = Course::create([
            'slug' => 'student-summary-course',
            'level' => 'b2',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Unidad 2',
            'position' => 1,
        ]);

        $lessons = collect(['pending', 'submitted', 'rejected', 'approved'])->map(function ($status, $index) use ($chapter) {
            return Lesson::create([
                'chapter_id' => $chapter->id,
                'type' => 'assignment',
                'position' => $index + 1,
                'config' => [
                    'title' => ucfirst($status).' Task',
                ],
            ]);
        });

        $assignments = $lessons->map(fn ($lesson) => Assignment::create([
            'lesson_id' => $lesson->id,
            'instructions' => 'Entrega',
            'due_at' => now()->addDays(5),
            'max_points' => 100,
        ]));

        AssignmentSubmission::create([
            'assignment_id' => $assignments[1]->id,
            'user_id' => $user->id,
            'body' => 'Listo',
            'status' => 'submitted',
            'max_points' => 100,
            'submitted_at' => now(),
        ]);

        AssignmentSubmission::create([
            'assignment_id' => $assignments[2]->id,
            'user_id' => $user->id,
            'body' => 'Intento',
            'status' => 'rejected',
            'max_points' => 100,
            'submitted_at' => now(),
        ]);

        AssignmentSubmission::create([
            'assignment_id' => $assignments[3]->id,
            'user_id' => $user->id,
            'body' => 'Entrega final',
            'status' => 'approved',
            'score' => 95,
            'max_points' => 100,
            'submitted_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSet('assignmentSummary.pending', 1)
            ->assertSet('assignmentSummary.submitted', 1)
            ->assertSet('assignmentSummary.rejected', 1)
            ->assertSet('assignmentSummary.approved', 1);
    }
}


