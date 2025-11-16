<?php

namespace Tests\Feature;

use App\Events\DiscordPracticeRequestEscalated;
use App\Events\DiscordPracticeReserved;
use App\Events\DiscordPracticeScheduled;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\DiscordPractice;
use App\Models\DiscordPracticeRequest;
use App\Models\DiscordPracticeReservation;
use App\Models\Lesson;
use App\Models\PracticePackage;
use App\Models\User;
use App\Notifications\DiscordPracticeRequestEscalatedNotification;
use App\Notifications\DiscordPracticeReservedNotification;
use App\Notifications\DiscordPracticeScheduledNotification;
use App\Notifications\DiscordPracticeSlotAvailableNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DiscordPracticeNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('teacher_admin');
        config()->set('services.discord.webhook_url', 'https://discord.test/hook');
    }

    public function test_reserved_event_notifies_and_dispatches_integration(): void
    {
        Notification::fake();
        Queue::fake();

        $teacher = User::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole('teacher_admin');
        $student = User::factory()->create();
        $lesson = $this->createLesson();

        $practice = DiscordPractice::create([
            'lesson_id' => $lesson->id,
            'title' => 'Conversación B2',
            'type' => 'global',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'duration_minutes' => 60,
            'capacity' => 10,
            'status' => 'scheduled',
            'created_by' => $teacher->id,
        ]);

        $reservation = DiscordPracticeReservation::create([
            'discord_practice_id' => $practice->id,
            'user_id' => $student->id,
            'status' => 'confirmed',
        ]);

        event(new DiscordPracticeReserved($practice, $reservation));

        Notification::assertSentTo([$teacher, $admin], DiscordPracticeReservedNotification::class);
        $this->assertDatabaseHas('integration_events', ['event' => 'discord.practice.reserved']);
    }

    public function test_scheduled_event_notifies_and_dispatches_integration(): void
    {
        Notification::fake();
        Queue::fake();

        $teacher = User::factory()->create();
        $lesson = $this->createLesson();

        $practice = DiscordPractice::create([
            'lesson_id' => $lesson->id,
            'title' => 'Pronunciación avanzada',
            'type' => 'cohort',
            'start_at' => now()->addDays(2),
            'end_at' => now()->addDays(2)->addMinutes(90),
            'duration_minutes' => 90,
            'capacity' => 15,
            'status' => 'scheduled',
            'created_by' => $teacher->id,
        ]);

        event(new DiscordPracticeScheduled($practice));

        Notification::assertSentTo($teacher, DiscordPracticeScheduledNotification::class);
        $this->assertDatabaseHas('integration_events', ['event' => 'discord.practice.scheduled']);
    }

    public function test_scheduled_event_notifies_pending_requests(): void
    {
        Notification::fake();
        Queue::fake();

        $teacher = User::factory()->create();
        $lesson = $this->createLesson();
        $watcherA = User::factory()->create();
        $watcherB = User::factory()->create();

        $package = PracticePackage::create([
            'creator_id' => $teacher->id,
            'lesson_id' => $lesson->id,
            'title' => 'Pack intensivo',
            'subtitle' => '3 sesiones guiadas',
            'description' => 'Incluye feedback y plan personalizado.',
            'sessions_count' => 3,
            'price_amount' => 90,
            'price_currency' => 'USD',
            'is_global' => true,
            'status' => 'published',
        ]);

        $practice = DiscordPractice::create([
            'lesson_id' => $lesson->id,
            'title' => 'Slot solicitado',
            'type' => 'cohort',
            'start_at' => now()->addDays(3),
            'end_at' => now()->addDays(3)->addMinutes(60),
            'duration_minutes' => 60,
            'capacity' => 5,
            'status' => 'scheduled',
            'created_by' => $teacher->id,
            'practice_package_id' => $package->id,
            'requires_package' => true,
        ]);

        DiscordPracticeRequest::create([
            'lesson_id' => $lesson->id,
            'user_id' => $watcherA->id,
            'status' => 'pending',
        ]);

        DiscordPracticeRequest::create([
            'lesson_id' => $lesson->id,
            'user_id' => $watcherB->id,
            'status' => 'pending',
        ]);

        event(new DiscordPracticeScheduled($practice));

        Notification::assertSentTo($teacher, DiscordPracticeScheduledNotification::class);
        Notification::assertSentTo($watcherA, DiscordPracticeSlotAvailableNotification::class, function ($notification) use ($watcherA, $package) {
            $payload = $notification->toArray($watcherA);
            $this->assertEquals($package->id, data_get($payload, 'pack_recommendation.id'));
            $this->assertTrue(data_get($payload, 'pack_recommendation.requires_package'));
            $this->assertFalse(data_get($payload, 'pack_recommendation.has_order'));

            return true;
        });

        Notification::assertSentTo($watcherB, DiscordPracticeSlotAvailableNotification::class);

        $this->assertDatabaseHas('discord_practice_requests', [
            'lesson_id' => $lesson->id,
            'user_id' => $watcherA->id,
            'status' => 'fulfilled',
        ]);
    }

    public function test_request_escalated_event_notifies_teacher_admins(): void
    {
        Notification::fake();
        Queue::fake();

        $admin = User::factory()->create();
        $admin->assignRole('teacher_admin');
        $lesson = $this->createLesson();

        event(new DiscordPracticeRequestEscalated($lesson, 5));

        Notification::assertSentTo($admin, DiscordPracticeRequestEscalatedNotification::class);
        $this->assertDatabaseHas('integration_events', ['event' => 'discord.practice.requests_escalated']);
    }

    private function createLesson(): Lesson
    {
        $course = Course::create(['slug' => 'demo-course', 'level' => 'B1', 'published' => true]);
        $chapter = Chapter::create(['course_id' => $course->id, 'title' => 'Unidad 1', 'position' => 1]);

        return Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'text',
            'position' => 1,
            'config' => ['title' => 'Lección demo'],
            'locked' => false,
        ]);
    }
}


