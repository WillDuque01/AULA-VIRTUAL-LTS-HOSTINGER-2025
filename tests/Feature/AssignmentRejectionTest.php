<?php

namespace Tests\Feature;

use App\Events\AssignmentRejected;
use App\Livewire\Admin\AssignmentsManager;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssignmentRejectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_reject_assignment_and_dispatch_event(): void
    {
        Role::create(['name' => 'teacher_admin']);
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher_admin');
        $student = User::factory()->create();

        $lesson = $this->createAssignmentLesson();
        $assignment = $lesson->assignment;
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'body' => 'Entrega',
            'status' => 'submitted',
            'max_points' => 100,
            'submitted_at' => now(),
        ]);

        Event::fake();

        Livewire::actingAs($teacher)
            ->test(AssignmentsManager::class)
            ->set('selectedAssignmentId', $assignment->id)
            ->call('editSubmission', $submission->id)
            ->set('selectedRejectionReason', 'quality')
            ->set('feedback', 'Por favor mejora el análisis')
            ->call('rejectSubmission', $submission->id);

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
            'status' => 'rejected',
        ]);

        Event::assertDispatched(AssignmentRejected::class);
    }

    private function createAssignmentLesson(): Lesson
    {
        $course = Course::create([
            'slug' => 'assignment-course',
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
                'instructions' => 'Describe una tradición local.',
                'max_points' => 100,
                'passing_score' => 70,
                'requires_approval' => true,
            ],
        ]);

        Assignment::create([
            'lesson_id' => $lesson->id,
            'instructions' => 'Describe una tradición local.',
            'max_points' => 100,
            'passing_score' => 70,
            'requires_approval' => true,
        ]);

        return $lesson->fresh('assignment');
    }
}


