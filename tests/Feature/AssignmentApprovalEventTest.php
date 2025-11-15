<?php

namespace Tests\Feature;

use App\Events\AssignmentApproved;
use App\Livewire\Admin\AssignmentsManager;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssignmentApprovalEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_approval_dispatches_notification_and_outbox(): void
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
            'body' => 'Mi entrega',
            'status' => 'submitted',
            'max_points' => 100,
            'submitted_at' => now(),
        ]);

        config(['services.make.webhook_url' => 'https://example.test/webhook']);
        Notification::fake();
        Bus::fake([\App\Jobs\DispatchIntegrationEventJob::class]);
        Http::fake();

        Livewire::actingAs($teacher)
            ->test(AssignmentsManager::class)
            ->set('selectedAssignmentId', $assignment->id)
            ->call('editSubmission', $submission->id)
            ->set('score', 90)
            ->set('feedback', 'Listo')
            ->call('saveGrade')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
        ]);
        $this->assertNotNull($submission->fresh()->approved_at);

        Notification::assertSentTo($student, \App\Notifications\AssignmentApprovedNotification::class);
        $this->assertDatabaseHas('integration_events', [
            'event' => 'assignment.approved',
            'target' => 'make',
        ]);
        Bus::assertDispatched(\App\Jobs\DispatchIntegrationEventJob::class);
    }

    private function createAssignmentLesson(): Lesson
    {
        $course = Course::create([
            'slug' => 'approval-course',
            'level' => 'advanced',
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
                'title' => 'Pitch deck',
                'max_points' => 100,
                'passing_score' => 80,
                'requires_approval' => true,
            ],
        ]);

        Assignment::create([
            'lesson_id' => $lesson->id,
            'instructions' => 'Entrega tu pitch deck',
            'max_points' => 100,
            'passing_score' => 80,
            'requires_approval' => true,
        ]);

        return $lesson->fresh('assignment');
    }
}


