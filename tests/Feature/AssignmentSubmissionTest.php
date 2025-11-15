<?php

namespace Tests\Feature;

use App\Livewire\Admin\AssignmentsManager;
use App\Livewire\Lessons\AssignmentPanel;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssignmentSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_assignment(): void
    {
        $student = User::factory()->create();
        $lesson = $this->createAssignmentLesson();

        Livewire::actingAs($student)
            ->test(AssignmentPanel::class, ['lesson' => $lesson])
            ->set('body', 'Respuesta detallada con enlaces y observaciones.')
            ->set('attachmentUrl', 'https://example.com/documento.pdf')
            ->call('submit')
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $lesson->assignment->id,
            'user_id' => $student->id,
            'status' => 'submitted',
        ]);
    }

    public function test_teacher_can_grade_submission(): void
    {
        Role::create(['name' => 'teacher_admin']);
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher_admin');

        $lesson = $this->createAssignmentLesson();
        $assignment = $lesson->assignment;
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => User::factory()->create()->id,
            'body' => 'Mi tarea completa',
            'status' => 'submitted',
            'max_points' => 100,
            'submitted_at' => now(),
        ]);

        Livewire::actingAs($teacher)
            ->test(AssignmentsManager::class)
            ->set('selectedAssignmentId', $assignment->id)
            ->call('editSubmission', $submission->id)
            ->set('score', 90)
            ->set('feedback', 'Excelente gramática y ejemplos.')
            ->call('saveGrade')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
            'status' => 'graded',
            'score' => 90,
        ]);
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
                'instructions' => 'Escribe un ensayo de 400 palabras sobre tu ciudad.',
                'max_points' => 100,
                'rubric' => ['Contenido', 'Gramática', 'Vocabulario'],
            ],
        ]);

        Assignment::create([
            'lesson_id' => $lesson->id,
            'instructions' => 'Escribe un ensayo de 400 palabras sobre tu ciudad.',
            'max_points' => 100,
            'rubric' => ['Contenido', 'Gramática', 'Vocabulario'],
        ]);

        return $lesson->fresh('assignment');
    }
}


