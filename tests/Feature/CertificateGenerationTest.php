<?php

namespace Tests\Feature;

use App\Jobs\DispatchIntegrationEventJob;
use App\Livewire\Student\Dashboard as StudentDashboard;
use App\Models\Certificate;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Models\VideoProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CertificateGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_generates_certificate_after_completion(): void
    {
        Storage::fake('local');
        Notification::fake();

        $user = $this->seedCompletedCourse();

        Livewire::actingAs($user)
            ->test(StudentDashboard::class);

        $certificate = Certificate::first();
        $this->assertNotNull($certificate);
        Storage::disk('local')->assertExists($certificate->file_path);
        Notification::assertSentTo($user, \App\Notifications\CertificateIssuedNotification::class);
    }

    public function test_certificate_issue_enqueues_integration_event(): void
    {
        Storage::fake('local');
        Notification::fake();
        Bus::fake();

        $previousAutoIssue = config('certificates.auto_issue');
        $previousWebhook = config('services.make.webhook_url');

        config(['certificates.auto_issue' => false]);
        config(['services.make.webhook_url' => 'https://example.test']);

        $user = $this->seedCompletedCourse();

        Livewire::actingAs($user)
            ->test(StudentDashboard::class)
            ->call('generateCertificate');

        $this->assertDatabaseHas('certificates', ['user_id' => $user->id]);
        $this->assertDatabaseHas('integration_events', [
            'event' => 'certificate.issued',
            'target' => 'make',
        ]);
        Bus::assertDispatched(DispatchIntegrationEventJob::class);

        config(['certificates.auto_issue' => $previousAutoIssue]);
        config(['services.make.webhook_url' => $previousWebhook]);
    }

    private function seedCompletedCourse(): User
    {
        $user = User::factory()->create();
        $course = Course::create([
            'slug' => 'curso-cert',
            'level' => 'intermediate',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Modulo 1',
            'position' => 1,
        ]);

        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'position' => 1,
            'config' => [
                'title' => 'Video final',
                'length' => 200,
                'source' => 'youtube',
                'video_id' => 'abc123',
            ],
        ]);

        VideoProgress::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'source' => 'youtube',
            'last_second' => 200,
            'watched_seconds' => 200,
        ]);

        return $user;
    }
}


