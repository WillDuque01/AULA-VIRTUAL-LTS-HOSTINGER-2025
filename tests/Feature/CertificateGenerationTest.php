<?php

namespace Tests\Feature;

use App\Livewire\Student\Dashboard as StudentDashboard;
use App\Models\Certificate;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Models\VideoProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class CertificateGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_generates_certificate_after_completion(): void
    {
        Storage::fake('local');

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

        Notification::fake();

        Livewire::actingAs($user)
            ->test(StudentDashboard::class);

        $certificate = Certificate::first();
        $this->assertNotNull($certificate);
        Storage::disk('local')->assertExists($certificate->file_path);
        Notification::assertSentTo($user, \App\Notifications\CertificateIssuedNotification::class);
    }
}


