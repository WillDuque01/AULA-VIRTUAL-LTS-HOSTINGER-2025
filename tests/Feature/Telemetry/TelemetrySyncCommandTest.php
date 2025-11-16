<?php

namespace Tests\Feature\Telemetry;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Models\VideoPlayerEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelemetrySyncCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sends_events_to_ga4_and_marks_synced(): void
    {
        config()->set('telemetry.ga4.enabled', true);
        config()->set('telemetry.ga4.measurement_id', 'G-TEST123');
        config()->set('telemetry.ga4.api_secret', 'secret');
        config()->set('telemetry.ga4.endpoint', 'https://www.google-analytics.com/mp/collect');
        config()->set('telemetry.mixpanel.enabled', false);

        Http::fake([
            'https://www.google-analytics.com/*' => Http::response([], 204),
        ]);

        $lesson = $this->createLesson();
        $user = User::factory()->create();

        $event = VideoPlayerEvent::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'course_id' => $lesson->chapter->course->id,
            'event' => 'progress_tick',
            'provider' => 'youtube',
            'playback_seconds' => 45,
            'watched_seconds' => 50,
            'video_duration' => 120,
            'context_tag' => 'player',
            'recorded_at' => now()->subMinute(),
        ]);

        $this->artisan('telemetry:sync')
            ->expectsOutput('Eventos sincronizados: 1')
            ->assertExitCode(0);

        Http::assertSent(function ($request) use ($event) {
            return str_contains($request->url(), 'google-analytics.com')
                && str_contains($request->body(), 'progress_tick')
                && str_contains($request->body(), (string) $event->lesson_id);
        });

        $this->assertNotNull($event->fresh()->synced_at);
    }

    public function test_command_skips_when_no_driver_enabled(): void
    {
        config()->set('telemetry.ga4.enabled', false);
        config()->set('telemetry.mixpanel.enabled', false);

        $lesson = $this->createLesson();
        $event = VideoPlayerEvent::create([
            'lesson_id' => $lesson->id,
            'course_id' => $lesson->chapter->course->id,
            'event' => 'cta_view',
            'recorded_at' => now(),
        ]);

        $this->artisan('telemetry:sync')
            ->expectsOutput('No se encontraron eventos pendientes o no hay drivers habilitados.')
            ->assertExitCode(0);

        $this->assertNull($event->fresh()->synced_at);
    }

    private function createLesson(): Lesson
    {
        $course = Course::create([
            'slug' => 'telemetry-'.uniqid(),
            'level' => 'advanced',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Sync test',
            'position' => 1,
        ]);

        return Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'position' => 1,
            'locked' => false,
            'config' => [
                'title' => 'Driver demo',
                'source' => 'youtube',
                'video_id' => 'XYZ',
                'length' => 120,
            ],
        ]);
    }
}

