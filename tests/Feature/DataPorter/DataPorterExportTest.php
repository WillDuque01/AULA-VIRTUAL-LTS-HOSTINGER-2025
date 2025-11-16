<?php

namespace Tests\Feature\DataPorter;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\DiscordPractice;
use App\Models\Lesson;
use App\Models\PracticePackage;
use App\Models\PracticePackageOrder;
use App\Models\TeacherSubmission;
use App\Models\User;
use App\Models\VideoPlayerEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DataPorterExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_csv_with_signed_url(): void
    {
        Gate::define('manage-settings', fn () => true);

        $admin = User::factory()->create();
        $lesson = $this->createLesson();

        VideoPlayerEvent::create([
            'user_id' => $admin->id,
            'lesson_id' => $lesson->id,
            'course_id' => $lesson->chapter->course->id,
            'event' => 'progress_tick',
            'provider' => 'youtube',
            'playback_seconds' => 30,
            'watched_seconds' => 30,
            'video_duration' => 120,
            'metadata' => ['source' => 'test'],
        ]);

        $this->actingAs($admin);

        $url = URL::temporarySignedRoute('admin.data-porter.export', now()->addMinutes(5), [
            'locale' => 'es',
            'dataset' => 'video_player_events',
            'format' => 'csv',
            'course_id' => $lesson->chapter->course->id,
        ]);

        $response = $this->get($url);

        $response->assertOk();
        $this->assertTrue(str_starts_with($response->headers->get('content-type'), 'text/csv'));
        $this->assertStringContainsString('video_player_events', $response->headers->get('content-disposition'));
    }

    public function test_teacher_admin_requires_scope_filter(): void
    {
        Role::findOrCreate('teacher_admin');
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher_admin');

        $this->actingAs($teacher);

        $url = URL::temporarySignedRoute('admin.data-porter.export', now()->addMinutes(5), [
            'locale' => 'es',
            'dataset' => 'video_player_events',
            'format' => 'csv',
        ]);

        $this->get($url)->assertForbidden();
    }

    public function test_admin_can_export_teacher_submissions_dataset(): void
    {
        Gate::define('manage-settings', fn () => true);
        $admin = User::factory()->create();
        $course = Course::factory()->create();
        $teacher = User::factory()->create();

        TeacherSubmission::create([
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'type' => 'module',
            'title' => 'Nueva unidad',
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $url = URL::temporarySignedRoute('admin.data-porter.export', now()->addMinutes(5), [
            'locale' => 'es',
            'dataset' => 'teacher_submissions',
            'format' => 'csv',
        ]);

        $response = $this->get($url);

        $response->assertOk();
        $this->assertStringContainsString('teacher_submissions', $response->headers->get('content-disposition'));
    }

    public function test_teacher_admin_needs_course_scope_for_teacher_submissions(): void
    {
        Role::findOrCreate('teacher_admin');
        $teacherAdmin = User::factory()->create();
        $teacherAdmin->assignRole('teacher_admin');
        $course = Course::factory()->create();
        $teacher = User::factory()->create();

        TeacherSubmission::create([
            'user_id' => $teacher->id,
            'course_id' => $course->id,
            'type' => 'lesson',
            'title' => 'Demo',
            'status' => 'pending',
        ]);

        $this->actingAs($teacherAdmin);

        $urlWithoutScope = URL::temporarySignedRoute('admin.data-porter.export', now()->addMinutes(5), [
            'locale' => 'es',
            'dataset' => 'teacher_submissions',
            'format' => 'csv',
        ]);
        $this->get($urlWithoutScope)->assertForbidden();

        $urlWithScope = URL::temporarySignedRoute('admin.data-porter.export', now()->addMinutes(5), [
            'locale' => 'es',
            'dataset' => 'teacher_submissions',
            'format' => 'csv',
            'course_id' => $course->id,
        ]);

        $this->get($urlWithScope)->assertOk();
    }

    public function test_admin_can_export_practice_package_orders_dataset(): void
    {
        Gate::define('manage-settings', fn () => true);
        $admin = User::factory()->create();
        $lesson = $this->createLesson();
        $package = $this->createPracticePackage($lesson);

        PracticePackageOrder::create([
            'practice_package_id' => $package->id,
            'user_id' => $admin->id,
            'status' => 'paid',
            'sessions_remaining' => 2,
            'payment_reference' => 'ref_001',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        $url = URL::temporarySignedRoute('admin.data-porter.export', now()->addMinutes(5), [
            'locale' => 'es',
            'dataset' => 'practice_package_orders',
            'format' => 'csv',
        ]);

        $this->get($url)->assertOk();
    }

    public function test_teacher_admin_requires_scope_for_practice_package_orders(): void
    {
        Role::findOrCreate('teacher_admin');
        $teacherAdmin = User::factory()->create();
        $teacherAdmin->assignRole('teacher_admin');
        $lesson = $this->createLesson();
        $package = $this->createPracticePackage($lesson);

        PracticePackageOrder::create([
            'practice_package_id' => $package->id,
            'user_id' => $teacherAdmin->id,
            'status' => 'paid',
            'sessions_remaining' => 1,
            'payment_reference' => 'ref_002',
            'paid_at' => now(),
        ]);

        $this->actingAs($teacherAdmin);

        $unsigned = URL::temporarySignedRoute('admin.data-porter.export', now()->addMinutes(5), [
            'locale' => 'es',
            'dataset' => 'practice_package_orders',
            'format' => 'csv',
        ]);

        $this->get($unsigned)->assertForbidden();

        $scoped = URL::temporarySignedRoute('admin.data-porter.export', now()->addMinutes(5), [
            'locale' => 'es',
            'dataset' => 'practice_package_orders',
            'format' => 'csv',
            'course_id' => $lesson->chapter->course->id,
        ]);

        $this->get($scoped)->assertOk();
    }

    public function test_admin_can_export_discord_practices_dataset(): void
    {
        Gate::define('manage-settings', fn () => true);
        $admin = User::factory()->create();
        $lesson = $this->createLesson();

        DiscordPractice::create([
            'lesson_id' => $lesson->id,
            'type' => 'coaching',
            'title' => 'Intensivo',
            'description' => 'QA',
            'cohort_label' => 'A1',
            'practice_package_id' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDays(2),
            'duration_minutes' => 45,
            'capacity' => 8,
            'discord_channel_url' => 'https://discord.test',
            'meeting_token' => null,
            'status' => 'scheduled',
            'created_by' => $admin->id,
            'requires_package' => false,
        ]);

        $this->actingAs($admin);

        $url = URL::temporarySignedRoute('admin.data-porter.export', now()->addMinutes(5), [
            'locale' => 'es',
            'dataset' => 'discord_practices',
            'format' => 'csv',
        ]);

        $this->get($url)->assertOk();
    }

    public function test_teacher_admin_requires_scope_for_discord_practices(): void
    {
        Role::findOrCreate('teacher_admin');
        $teacherAdmin = User::factory()->create();
        $teacherAdmin->assignRole('teacher_admin');
        $lesson = $this->createLesson();

        DiscordPractice::create([
            'lesson_id' => $lesson->id,
            'type' => 'laboratorio',
            'title' => 'QA lab',
            'description' => 'Lab',
            'cohort_label' => 'B2',
            'practice_package_id' => null,
            'start_at' => now()->addHours(6),
            'end_at' => now()->addHours(7),
            'duration_minutes' => 60,
            'capacity' => 6,
            'discord_channel_url' => 'https://discord.test/lab',
            'meeting_token' => null,
            'status' => 'scheduled',
            'created_by' => $teacherAdmin->id,
            'requires_package' => false,
        ]);

        $this->actingAs($teacherAdmin);

        $urlWithoutScope = URL::temporarySignedRoute('admin.data-porter.export', now()->addMinutes(5), [
            'locale' => 'es',
            'dataset' => 'discord_practices',
            'format' => 'csv',
        ]);

        $this->get($urlWithoutScope)->assertForbidden();

        $urlWithScope = URL::temporarySignedRoute('admin.data-porter.export', now()->addMinutes(5), [
            'locale' => 'es',
            'dataset' => 'discord_practices',
            'format' => 'csv',
            'course_id' => $lesson->chapter->course->id,
        ]);

        $this->get($urlWithScope)->assertOk();
    }

    private function createLesson(): Lesson
    {
        $course = Course::create([
            'slug' => 'dp-'.uniqid(),
            'level' => 'intermediate',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Dataset',
            'position' => 1,
        ]);

        return Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'position' => 1,
            'locked' => false,
            'config' => [
                'title' => 'DataPorter Demo',
                'source' => 'youtube',
                'video_id' => 'xyz',
                'length' => 180,
            ],
        ]);
    }

    private function createPracticePackage(Lesson $lesson): PracticePackage
    {
        return PracticePackage::create([
            'creator_id' => User::factory()->create()->id,
            'lesson_id' => $lesson->id,
            'title' => 'Pack QA',
            'subtitle' => '4 sesiones',
            'description' => 'Pack para pruebas',
            'sessions_count' => 4,
            'price_amount' => 120.00,
            'price_currency' => 'USD',
            'is_global' => false,
            'visibility' => 'private',
            'delivery_platform' => 'discord',
            'delivery_url' => null,
            'status' => 'published',
        ]);
    }
}

