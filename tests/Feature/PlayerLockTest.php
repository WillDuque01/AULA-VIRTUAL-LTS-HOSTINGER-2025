<?php

namespace Tests\Feature;

use App\Http\Livewire\Player;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Models\VideoProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class PlayerLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_is_locked_until_release_date(): void
    {
        $user = User::factory()->create();
        $chapter = $this->createChapter();
        $lesson = $this->createLesson($chapter, [
            'release_at' => now()->addDay()->toIso8601String(),
        ]);

        $this->actingAs($user);

        Livewire::test(Player::class, ['lesson' => $lesson])
            ->assertSet('isLocked', true)
            ->assertSee('Lección bloqueada');
    }

    public function test_player_requires_prerequisite_completion(): void
    {
        $user = User::factory()->create();
        $chapter = $this->createChapter();
        $prerequisite = $this->createLesson($chapter, ['title' => 'Intro']);
        $lesson = $this->createLesson($chapter, [
            'title' => 'Modulo 2',
            'prerequisite_lesson_id' => $prerequisite->id,
        ], [
            'position' => 2,
        ]);

        $this->actingAs($user);

        Livewire::test(Player::class, ['lesson' => $lesson])
            ->assertSet('isLocked', true)
            ->assertSee('Completa');

        VideoProgress::create([
            'lesson_id' => $prerequisite->id,
            'user_id' => $user->id,
            'source' => 'youtube',
            'last_second' => 140,
            'watched_seconds' => 140,
        ]);

        Livewire::test(Player::class, ['lesson' => $lesson])
            ->assertSet('isLocked', false);
    }

    public function test_assignment_prerequisite_requires_teacher_approval(): void
    {
        $user = User::factory()->create();
        $chapter = $this->createChapter();

        $assignmentLesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'assignment',
            'position' => 1,
            'config' => [
                'title' => 'Tarea clave',
                'instructions' => 'Sube tu proyecto final.',
                'max_points' => 100,
                'passing_score' => 80,
                'requires_approval' => true,
            ],
        ]);

        Assignment::create([
            'lesson_id' => $assignmentLesson->id,
            'instructions' => 'Sube tu proyecto final.',
            'max_points' => 100,
            'passing_score' => 80,
            'requires_approval' => true,
        ]);

        $assignmentLesson->load('assignment');

        $lesson = $this->createLesson($chapter, [
            'title' => 'Siguiente módulo',
            'prerequisite_lesson_id' => $assignmentLesson->id,
        ], ['position' => 2]);

        $this->actingAs($user);

        Livewire::test(Player::class, ['lesson' => $lesson->fresh()])
            ->assertSet('isLocked', true)
            ->assertSee('Completa');

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignmentLesson->assignment->id,
            'user_id' => $user->id,
            'body' => 'Mi entrega',
            'status' => 'submitted',
            'max_points' => 100,
            'submitted_at' => now(),
        ]);

        Livewire::test(Player::class, ['lesson' => $lesson->fresh()])
            ->assertSet('isLocked', true);

        $submission->update([
            'score' => 70,
            'status' => 'graded',
            'graded_at' => now(),
        ]);

        Livewire::test(Player::class, ['lesson' => $lesson->fresh()])
            ->assertSet('isLocked', true);

        $submission->update([
            'score' => 90,
        ]);

        Livewire::test(Player::class, ['lesson' => $lesson->fresh()])
            ->assertSet('isLocked', false);
    }

    public function test_can_toggle_strict_mode_for_premium_providers(): void
    {
        $user = User::factory()->create();
        config()->set('integrations.force_youtube_only', false);
        config()->set('integrations.status.video.driver', 'vimeo');

        $chapter = $this->createChapter();
        $lesson = $this->createLesson($chapter, [
            'source' => 'vimeo',
            'video_id' => '123',
        ]);

        $this->actingAs($user);

        Livewire::test(Player::class, ['lesson' => $lesson])
            ->assertSet('strictSeeking', true)
            ->call('toggleStrict')
            ->assertSet('strictSeeking', false);
    }

    private function createChapter(): Chapter
    {
        $course = Course::create([
            'slug' => Str::uuid(),
            'level' => 'beginner',
            'published' => true,
        ]);

        return Chapter::create([
            'course_id' => $course->id,
            'title' => 'Capítulo 1',
            'position' => 1,
        ]);
    }

    private function createLesson(Chapter $chapter, array $config = [], array $overrides = []): Lesson
    {
        $baseConfig = [
            'title' => 'Video de prueba',
            'source' => 'youtube',
            'video_id' => 'abcd1234',
            'length' => 150,
        ];

        return Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'position' => $overrides['position'] ?? 1,
            'locked' => $overrides['locked'] ?? false,
            'config' => array_merge($baseConfig, $config),
        ]);
    }
}

