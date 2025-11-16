<?php

namespace Tests\Feature\Teacher;

use App\Livewire\Admin\TeacherManager;
use App\Livewire\Admin\TeacherPerformanceReport;
use App\Livewire\Admin\TeacherSubmissionsHub;
use App\Livewire\Teacher\Dashboard as TeacherDashboard;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\TeacherSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class TeacherWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('Admin');
        Role::findOrCreate('teacher_admin');
        Role::findOrCreate('teacher');
    }

    public function test_teacher_dashboard_allows_creating_submission(): void
    {
        Notification::fake();
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');

        $course = Course::factory()->create();
        $chapter = Chapter::factory()->create(['course_id' => $course->id]);
        $teacher->teachingCourses()->attach($course->id);

        $this->actingAs($teacher);

        Livewire::test(TeacherDashboard::class)
            ->set('form', [
                'type' => 'lesson',
                'course_id' => $course->id,
                'chapter_id' => $chapter->id,
                'title' => 'Intro propuesta',
                'summary' => 'Resumen breve',
                'lesson_type' => 'video',
                'estimated_minutes' => 12,
                'notes' => 'Notas internas',
            ])
            ->call('submitProposal');

        $submission = TeacherSubmission::first();

        $this->assertNotNull($submission);
        $this->assertSame('pending', $submission->status);
        $this->assertSame('lesson', $submission->type);
        $this->assertNotNull($submission->result_id);

        $lesson = Lesson::find($submission->result_id);
        $this->assertNotNull($lesson);
        $this->assertSame('pending', $lesson->status);
    }

    public function test_teacher_admin_can_approve_submission_and_publish_content(): void
    {
        Notification::fake();
        $teacherAdmin = User::factory()->create();
        $teacherAdmin->assignRole(['teacher_admin']);

        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');
        $course = Course::factory()->create();
        $teacher->teachingCourses()->attach($course->id);

        $this->actingAs($teacher);

        Livewire::test(TeacherDashboard::class)
            ->set('form', [
                'type' => 'module',
                'course_id' => $course->id,
                'title' => 'Nuevo módulo',
                'summary' => 'Objetivos',
            ])
            ->call('submitProposal');

        $submission = TeacherSubmission::first();
        $this->assertNotNull($submission);
        $chapterId = $submission->result_id;
        $this->assertNotNull($chapterId);

        $this->actingAs($teacherAdmin);

        Livewire::test(TeacherSubmissionsHub::class)
            ->call('approve', $submission->id);

        $this->assertDatabaseHas('teacher_submissions', [
            'id' => $submission->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('teacher_submission_histories', [
            'teacher_submission_id' => $submission->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('chapters', [
            'id' => $chapterId,
            'status' => 'published',
        ]);
    }

    public function test_teacher_admin_can_reject_submission_and_flag_content(): void
    {
        Notification::fake();
        $teacherAdmin = User::factory()->create();
        $teacherAdmin->assignRole(['teacher_admin']);

        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');
        $course = Course::factory()->create();
        $chapter = Chapter::factory()->create(['course_id' => $course->id]);
        $teacher->teachingCourses()->attach($course->id);

        $this->actingAs($teacher);

        Livewire::test(TeacherDashboard::class)
            ->set('form', [
                'type' => 'lesson',
                'course_id' => $course->id,
                'chapter_id' => $chapter->id,
                'title' => 'Audio Guía',
                'lesson_type' => 'text',
                'estimated_minutes' => 5,
            ])
            ->call('submitProposal');

        $submission = TeacherSubmission::first();
        $lessonId = $submission->result_id;
        $this->assertNotNull($lessonId);

        $this->actingAs($teacherAdmin);

        Livewire::test(TeacherSubmissionsHub::class)
            ->set("feedback.{$submission->id}", 'Necesita ajustes')
            ->call('reject', $submission->id);

        $this->assertDatabaseHas('teacher_submissions', [
            'id' => $submission->id,
            'status' => 'rejected',
            'feedback' => 'Necesita ajustes',
        ]);

        $this->assertDatabaseHas('teacher_submission_histories', [
            'teacher_submission_id' => $submission->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseHas('lessons', [
            'id' => $lessonId,
            'status' => 'rejected',
        ]);
    }

    public function test_admin_can_promote_teachers_and_assign_courses(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');

        $course = Course::factory()->create();

        $this->actingAs($admin);

        Livewire::test(TeacherManager::class)
            ->call('toggleSelection', $teacher->id)
            ->call('promoteSelected');

        $this->assertTrue($teacher->fresh()->hasRole('teacher_admin'));

        Livewire::test(TeacherManager::class)
            ->set("courseAssignments.$teacher->id", [$course->id])
            ->call('saveCourseAssignment', $teacher->id);

        $this->assertDatabaseHas('course_teacher', [
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_teacher_performance_report_shows_metrics(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');

        $course = Course::factory()->create();
        $chapter = Chapter::factory()->create(['course_id' => $course->id]);
        $teacher->teachingCourses()->attach($course->id);

        $this->actingAs($teacher);

        Livewire::test(TeacherDashboard::class)
            ->set('form', [
                'type' => 'lesson',
                'course_id' => $course->id,
                'chapter_id' => $chapter->id,
                'title' => 'Reporte',
                'lesson_type' => 'video',
                'estimated_minutes' => 10,
            ])
            ->call('submitProposal');

        $submission = TeacherSubmission::first();
        $submission->update([
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => Carbon::now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(TeacherPerformanceReport::class)
            ->assertSee('Reporte de desempeño docente')
            ->assertSee($teacher->name)
            ->assertSee('Aprobadas');
    }
}

