<?php

namespace Tests\Feature\Student;

use App\Livewire\Student\DiscordPracticeBrowser;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\DiscordPractice;
use App\Models\Lesson;
use App\Models\PracticePackage;
use App\Models\PracticePackageOrder;
use App\Models\User;
use App\Notifications\DiscordPracticeSlotAvailableNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class DiscordPracticeBrowserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('notifications');

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Role::findOrCreate('teacher_admin');
        Role::findOrCreate('teacher');
        Role::findOrCreate('Admin');
    }

    public function test_practice_requires_pack_and_shows_cta_when_user_has_none(): void
    {
        $user = User::factory()->create();
        $teacher = User::factory()->create();
        $lesson = $this->createLesson();

        $package = PracticePackage::create([
            'creator_id' => $teacher->id,
            'lesson_id' => $lesson->id,
            'title' => 'Pack esencial',
            'sessions_count' => 3,
            'price_amount' => 90,
            'price_currency' => 'USD',
            'is_global' => true,
            'status' => 'published',
        ]);

        DiscordPractice::create([
            'lesson_id' => $lesson->id,
            'title' => 'Pronunciación crítica',
            'type' => 'global',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addMinutes(45),
            'duration_minutes' => 45,
            'capacity' => 5,
            'status' => 'scheduled',
            'created_by' => $teacher->id,
            'requires_package' => true,
            'practice_package_id' => $package->id,
        ]);

        $this->actingAs($user);

        $expectedUrl = route('dashboard', ['locale' => app()->getLocale()]).'?pack='.$package->id.'#practice-packs';

        Livewire::test(DiscordPracticeBrowser::class)
            ->assertSet('practices.0.requires_package', true)
            ->assertSet('practices.0.has_required_pack', false)
            ->assertSet('practices.0.pack_url', $expectedUrl);
    }

    public function test_practice_detects_active_pack_when_user_has_order(): void
    {
        $user = User::factory()->create();
        $teacher = User::factory()->create();
        $lesson = $this->createLesson();

        $package = PracticePackage::create([
            'creator_id' => $teacher->id,
            'lesson_id' => $lesson->id,
            'title' => 'Pack premium',
            'sessions_count' => 4,
            'price_amount' => 120,
            'price_currency' => 'USD',
            'is_global' => true,
            'status' => 'published',
        ]);

        DiscordPractice::create([
            'lesson_id' => $lesson->id,
            'title' => 'Pronunciación crítica',
            'type' => 'global',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addMinutes(45),
            'duration_minutes' => 45,
            'capacity' => 5,
            'status' => 'scheduled',
            'created_by' => $teacher->id,
            'requires_package' => true,
            'practice_package_id' => $package->id,
        ]);

        PracticePackageOrder::create([
            'practice_package_id' => $package->id,
            'user_id' => $user->id,
            'status' => 'paid',
            'sessions_remaining' => 2,
        ]);

        $this->actingAs($user);

        $expectedUrl = route('dashboard', ['locale' => app()->getLocale()]).'?pack='.$package->id.'#practice-packs';

        Livewire::test(DiscordPracticeBrowser::class)
            ->assertSet('practices.0.has_required_pack', true)
            ->assertSet('practices.0.pack_url', $expectedUrl);
    }

    public function test_pack_reminder_is_exposed_when_notification_exists(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => DiscordPracticeSlotAvailableNotification::class,
            'data' => [
                'title' => 'Slot B2',
                'start_at' => now()->addDay()->toIso8601String(),
                'practice_url' => 'https://example.test/practices',
                'packs_url' => 'https://example.test/dashboard#practice-packs',
                'pack_recommendation' => [
                    'id' => 99,
                    'title' => 'Pack intensivo',
                    'sessions' => 3,
                    'price_amount' => 90,
                    'currency' => 'USD',
                    'price_per_session' => 30,
                    'requires_package' => true,
                    'has_order' => false,
                ],
            ],
        ]);

        Livewire::test(DiscordPracticeBrowser::class)
            ->assertSet('packReminder.pack.title', 'Pack intensivo');
    }

    public function test_dismissing_pack_reminder_marks_notification_as_read(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => DiscordPracticeSlotAvailableNotification::class,
            'data' => [
                'title' => 'Slot B2',
                'start_at' => now()->addDay()->toIso8601String(),
                'practice_url' => 'https://example.test/practices',
                'packs_url' => 'https://example.test/dashboard#practice-packs',
                'pack_recommendation' => [
                    'id' => 1,
                    'title' => 'Pack exprés',
                    'sessions' => 2,
                    'price_amount' => 60,
                    'currency' => 'USD',
                    'price_per_session' => 30,
                    'requires_package' => true,
                    'has_order' => false,
                ],
            ],
        ]);

        Livewire::test(DiscordPracticeBrowser::class)
            ->call('dismissPackReminder')
            ->assertSet('packReminder', null);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_can_cancel_reservation_and_restore_pack_session(): void
    {
        $user = User::factory()->create();
        $teacher = User::factory()->create();
        $lesson = $this->createLesson();

        $package = PracticePackage::create([
            'creator_id' => $teacher->id,
            'lesson_id' => $lesson->id,
            'title' => 'Pack cancelable',
            'sessions_count' => 2,
            'price_amount' => 80,
            'price_currency' => 'USD',
            'is_global' => false,
            'status' => 'published',
        ]);

        $order = PracticePackageOrder::create([
            'practice_package_id' => $package->id,
            'user_id' => $user->id,
            'status' => 'paid',
            'sessions_remaining' => 2,
        ]);

        $practice = DiscordPractice::create([
            'lesson_id' => $lesson->id,
            'title' => 'Pronunciación',
            'type' => 'cohort',
            'start_at' => now()->addHours(3),
            'end_at' => now()->addHours(4),
            'duration_minutes' => 60,
            'capacity' => 5,
            'status' => 'scheduled',
            'created_by' => $teacher->id,
            'requires_package' => true,
            'practice_package_id' => $package->id,
        ]);

        $this->actingAs($user);

        Livewire::test(DiscordPracticeBrowser::class)
            ->call('reserve', $practice->id);

        $this->assertDatabaseHas('discord_practice_reservations', [
            'discord_practice_id' => $practice->id,
            'user_id' => $user->id,
            'status' => 'confirmed',
        ]);

        $this->assertSame(1, $order->fresh()->sessions_remaining);

        Livewire::test(DiscordPracticeBrowser::class)
            ->call('cancelReservation', $practice->id);

        $this->assertDatabaseHas('discord_practice_reservations', [
            'discord_practice_id' => $practice->id,
            'user_id' => $user->id,
            'status' => 'cancelled',
        ]);

        $this->assertSame(2, $order->fresh()->sessions_remaining);
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


