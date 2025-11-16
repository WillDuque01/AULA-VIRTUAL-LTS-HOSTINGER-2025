<?php

namespace Tests\Feature\Telemetry;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Models\VideoPlayerEvent;
use App\Notifications\Telemetry\TelemetryBacklogAlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TelemetryBacklogMonitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_monitor_command_sends_alert_when_threshold_exceeded(): void
    {
        Notification::fake();

        Role::findOrCreate('Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $course = Course::create(['slug' => 'telemetry', 'level' => 'b1', 'published' => true]);
        $chapter = Chapter::create(['course_id' => $course->id, 'title' => 'Módulo', 'position' => 1]);
        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'position' => 1,
            'locked' => false,
            'config' => ['title' => 'Demo'],
        ]);

        VideoPlayerEvent::create([
            'user_id' => $admin->id,
            'lesson_id' => $lesson->id,
            'course_id' => $course->id,
            'event' => 'progress_tick',
            'provider' => 'youtube',
            'playback_seconds' => 10,
            'watched_seconds' => 10,
            'recorded_at' => now(),
            'synced_at' => null,
        ]);

        config()->set('services.telemetry_alerts.threshold', 1);
        config()->set('services.telemetry_alerts.cooldown_minutes', 1);

        $this->artisan('telemetry:monitor-backlog')
            ->assertExitCode(0);

        Notification::assertSentTo($admin, TelemetryBacklogAlertNotification::class);
    }
}


