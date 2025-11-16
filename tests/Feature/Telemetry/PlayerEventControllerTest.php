<?php

namespace Tests\Feature\Telemetry;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerEventControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_record_custom_player_event(): void
    {
        $lesson = $this->createLesson();
        $user = User::factory()->create();

        $this->actingAs($user);

        $payload = [
            'lesson_id' => $lesson->id,
            'event' => 'cta_click',
            'playback_seconds' => 48,
            'video_duration' => 300,
            'metadata' => [
                'type' => 'practice',
                'origin' => 'highlight',
            ],
        ];

        $this->postJson(route('api.player.events', ['locale' => 'es']), $payload)
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('video_player_events', [
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
            'event' => 'cta_click',
            'playback_seconds' => 48,
        ]);
    }

    public function test_rejects_invalid_event_name(): void
    {
        $lesson = $this->createLesson();
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->postJson(route('api.player.events', ['locale' => 'es']), [
            'lesson_id' => $lesson->id,
            'event' => 'invalid event',
        ])->assertStatus(422);
    }

    private function createLesson(): Lesson
    {
        $course = Course::create([
            'slug' => 'player-events-'.uniqid(),
            'level' => 'advanced',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Telemetry',
            'position' => 1,
        ]);

        return Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'position' => 1,
            'locked' => false,
            'config' => [
                'title' => 'Evento',
                'source' => 'youtube',
                'video_id' => 'abc123',
                'length' => 300,
            ],
        ]);
    }
}

