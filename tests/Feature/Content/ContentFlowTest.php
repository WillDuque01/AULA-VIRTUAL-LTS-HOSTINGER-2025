<?php

namespace Tests\Feature\Content;

use App\Exceptions\CohortSoldOutException;
use App\Models\Chapter;
use App\Models\CohortTemplate;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\CohortRegistration;
use App\Models\User;
use App\Services\CohortEnrollmentService;
use Database\Seeders\AuditorProfilesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AuditorProfilesSeeder::class);
    }

    public function test_player_progress_endpoint_records_event(): void
    {
        $student = User::whereEmail('student.paid@letstalkspanish.io')->firstOrFail();

        $course = Course::factory()->create();
        $chapter = Chapter::factory()->create([
            'course_id' => $course->id,
            'title' => 'QA Chapter',
            'position' => 1,
        ]);
        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'config' => [
                'title' => 'QA Lesson',
                'length' => 120,
            ],
            'position' => 1,
            'locked' => false,
            'status' => 'published',
            'created_by' => $student->id,
        ]);

        $payload = [
            'lesson_id' => $lesson->id,
            'event' => 'play',
            'playback_seconds' => 15,
            'watched_seconds' => 15,
            'video_duration' => 120,
            'provider' => 'vimeo',
            'context_tag' => 'player_test',
            'metadata' => ['source' => 'phpunit'],
        ];

        $response = $this
            ->actingAs($student)
            ->postJson(route('api.player.events', ['locale' => 'es']), $payload);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('video_player_events', [
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'event' => 'play',
            'provider' => 'vimeo',
            'context_tag' => 'player_test',
        ]);
    }

    public function test_teacher_admin_can_open_course_builder(): void
    {
        $teacherAdmin = User::whereEmail('teacher.admin.qa@letstalkspanish.io')->firstOrFail();
        $course = Course::factory()->create();

        $response = $this
            ->actingAs($teacherAdmin)
            ->get(route('courses.builder', ['locale' => 'es', 'course' => $course->id]));

        $response->assertOk();
    }

    public function test_student_waitlist_cannot_enroll_in_full_cohort(): void
    {
        $waitlist = User::whereEmail('student.waitlist@letstalkspanish.io')->firstOrFail();
        $occupied = User::factory()->create();

        $template = CohortTemplate::factory()->create([
            'capacity' => 1,
            'status' => 'published',
            'slots' => [
                ['weekday' => 'monday', 'time' => '08:00'],
            ],
        ]);

        CohortRegistration::create([
            'cohort_template_id' => $template->id,
            'user_id' => $occupied->id,
            'status' => 'paid',
            'payment_reference' => 'FULL-COHORT',
            'amount' => 49.99,
            'currency' => 'USD',
        ]);

        $template->refreshEnrollmentMetrics();

        $service = app(CohortEnrollmentService::class);

        $this->expectException(CohortSoldOutException::class);

        $service->enroll(
            $waitlist,
            $template,
            49.99,
            'USD',
            'WAITLIST-'.Str::upper(Str::random(6))
        );
    }
}

