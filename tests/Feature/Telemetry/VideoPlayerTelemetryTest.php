<?php

namespace Tests\Feature\Telemetry;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoPlayerTelemetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_progress_event_persists_player_telemetry(): void
    {
        config()->set('auth.guards.sanctum', [
            'driver' => 'session',
            'provider' => 'users',
        ]);

        $lesson = $this->createLesson();
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/video/progress', [
            'lesson_id' => $lesson->id,
            'source' => 'youtube',
            'last_second' => 42,
            'watched_seconds' => 48,
            'duration' => 120,
        ])->assertOk();

        $this->assertDatabaseHas('video_player_events', [
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
            'event' => 'progress_tick',
            'provider' => 'youtube',
            'playback_seconds' => 42,
            'watched_seconds' => 48,
            'video_duration' => 120,
        ]);
    }

    private function createLesson(int $length = 180): Lesson
    {
        $course = Course::create([
            'slug' => 'telemetry-'.uniqid(),
            'level' => 'advanced',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Telemetry Intro',
            'position' => 1,
        ]);

        return Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'position' => 1,
            'locked' => false,
            'config' => [
                'title' => 'Evento de prueba',
                'source' => 'youtube',
                'video_id' => 'abc123',
                'length' => $length,
            ],
        ]);
    }
}

