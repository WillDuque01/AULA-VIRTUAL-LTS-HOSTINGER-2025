<?php

namespace Tests\Feature;

use App\Livewire\Professor\Dashboard;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Models\VideoHeatmapSegment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfessorDashboardHeatmapTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_exposes_heatmap_data(): void
    {
        $lesson = $this->createLesson('Heatmap Lesson');

        VideoHeatmapSegment::create([
            'lesson_id' => $lesson->id,
            'bucket' => 0,
            'reach_count' => 5,
        ]);

        VideoHeatmapSegment::create([
            'lesson_id' => $lesson->id,
            'bucket' => 1,
            'reach_count' => 3,
        ]);

        $teacher = User::factory()->create();

        Livewire::actingAs($teacher)
            ->test(Dashboard::class)
            ->assertSet('heatmap.lesson', 'Heatmap Lesson')
            ->assertSet('heatmap.segments.0.bucket', 0)
            ->assertSet('heatmap.segments.1.reach', 3);
    }

    public function test_dashboard_lists_assignments_due_soon(): void
    {
        $teacher = User::factory()->create();
        $course = Course::create([
            'slug' => 'assignments-course',
            'level' => 'c1',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Unidad 2',
            'position' => 1,
        ]);

        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'assignment',
            'position' => 1,
            'config' => [
                'title' => 'Proyecto colaborativo',
            ],
        ]);

        $assignment = Assignment::create([
            'lesson_id' => $lesson->id,
            'instructions' => 'Sube tu proyecto colaborativo.',
            'due_at' => now()->addDays(3),
            'max_points' => 100,
            'passing_score' => 70,
            'requires_approval' => true,
        ]);

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => User::factory()->create()->id,
            'body' => 'Mi entrega',
            'status' => 'submitted',
            'max_points' => 100,
            'submitted_at' => now(),
        ]);

        app()->setLocale('es');

        Livewire::actingAs($teacher)
            ->test(Dashboard::class)
            ->assertSee(__('dashboard.assignments.professor_title'))
            ->assertSee('Proyecto colaborativo');
    }

    public function test_dashboard_shows_rejected_chip(): void
    {
        $teacher = User::factory()->create();
        $course = Course::create([
            'slug' => 'assignments-estado',
            'level' => 'c2',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Alertas',
            'position' => 1,
        ]);

        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'assignment',
            'position' => 1,
            'config' => [
                'title' => 'Ensayo crítico',
                'requires_approval' => true,
            ],
        ]);

        $assignment = Assignment::create([
            'lesson_id' => $lesson->id,
            'instructions' => 'Entrega el ensayo.',
            'due_at' => now()->subDay(),
            'requires_approval' => true,
        ]);

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => User::factory()->create()->id,
            'body' => 'Versión incompleta',
            'status' => 'rejected',
            'max_points' => 100,
            'submitted_at' => now(),
        ]);

        app()->setLocale('es');

        Livewire::actingAs($teacher)
            ->test(Dashboard::class)
            ->assertSee('Ensayo crítico')
            ->assertSee(__('dashboard.assignments.professor_rejected_chip', ['count' => 1]));
    }

    public function test_dashboard_shows_whatsapp_cta_when_configured(): void
    {
        config()->set('services.whatsapp.deeplink', 'https://wa.me/573001112233');

        $teacher = User::factory()->create();
        $course = Course::create([
            'slug' => 'assignments-support',
            'level' => 'c2',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Alertas',
            'position' => 1,
        ]);

        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'assignment',
            'position' => 1,
            'config' => [
                'title' => 'CTA WhatsApp',
            ],
        ]);

        Assignment::create([
            'lesson_id' => $lesson->id,
            'instructions' => 'Entrega el ensayo.',
            'due_at' => now()->addDay(),
        ]);

        Livewire::actingAs($teacher)
            ->test(Dashboard::class)
            ->assertSee('https://wa.me/573001112233', false);
    }

    private function createLesson(string $title): Lesson
    {
        $course = Course::create([
            'slug' => 'heatmap-'.uniqid(),
            'level' => 'intermediate',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Análisis',
            'position' => 1,
        ]);

        return Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'position' => 1,
            'locked' => false,
            'config' => [
                'title' => $title,
                'source' => 'vimeo',
                'video_id' => 'xyz123',
                'length' => 120,
            ],
        ]);
    }
}

