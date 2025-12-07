<?php

namespace Tests\Feature\Integrations;

use App\Jobs\RecordPlayerEventJob;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Support\Analytics\TelemetryRecorder;
use Database\Seeders\AuditorProfilesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class TelemetryQueueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AuditorProfilesSeeder::class);
    }

    public function test_record_player_event_is_dispatched_to_telemetry_queue(): void
    {
        Bus::fake();

        $student = User::whereEmail('student.paid@letstalkspanish.io')->firstOrFail();
        $course = Course::factory()->create();
        $chapter = Chapter::factory()->create([
            'course_id' => $course->id,
        ]);
        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'config' => ['title' => 'Telemetry QA'],
            'position' => 1,
            'locked' => false,
            'status' => 'published',
            'created_by' => $student->id,
        ]);

        $recorder = app(TelemetryRecorder::class);

        $reflection = new \ReflectionClass($recorder);
        $property = $reflection->getProperty('useQueue');
        $property->setAccessible(true);
        $property->setValue($recorder, true);

        $recorder->recordPlayerEvent($student->id, $lesson, [
            'event' => 'play',
            'playback_seconds' => 10,
        ]);

        Bus::assertDispatched(RecordPlayerEventJob::class, function ($job) use ($student, $lesson) {
            $reflection = new \ReflectionClass($job);

            $userProp = $reflection->getProperty('userId');
            $userProp->setAccessible(true);

            $lessonProp = $reflection->getProperty('lessonId');
            $lessonProp->setAccessible(true);

            return $job->queue === 'telemetry'
                && $userProp->getValue($job) === $student->id
                && $lessonProp->getValue($job) === $lesson->id;
        });
    }
}

