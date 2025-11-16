<?php

namespace Tests\Feature\Telemetry;

use App\Events\DiscordPracticeRequestEscalated;
use App\Events\DiscordPracticeReserved;
use App\Events\PracticePackagePurchased;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\DiscordPractice;
use App\Models\DiscordPracticeReservation;
use App\Models\Lesson;
use App\Models\PracticePackage;
use App\Models\PracticePackageOrder;
use App\Models\User;
use App\Services\PracticePackageOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentActivitySnapshotAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Role::findOrCreate('teacher_admin');
    }

    public function test_practice_reservation_records_snapshot(): void
    {
        $teacher = User::factory()->create();
        $student = User::factory()->create();
        $lesson = $this->createLesson($teacher);
        $practice = DiscordPractice::create([
            'lesson_id' => $lesson->id,
            'type' => 'coaching',
            'title' => 'Sesión Discord',
            'description' => 'Reserva QA',
            'cohort_label' => 'B1-Noche',
            'practice_package_id' => null,
            'start_at' => now()->addDay(),
            'duration_minutes' => 45,
            'capacity' => 8,
            'status' => 'scheduled',
            'created_by' => $teacher->id,
            'requires_package' => false,
        ]);

        $reservation = DiscordPracticeReservation::create([
            'discord_practice_id' => $practice->id,
            'user_id' => $student->id,
            'status' => 'confirmed',
        ]);

        event(new DiscordPracticeReserved($practice, $reservation));

        $this->assertDatabaseHas('student_activity_snapshots', [
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'category' => 'practice_reservation',
            'scope' => 'discord_practice',
        ]);
    }

    public function test_pack_purchase_records_snapshot(): void
    {
        $teacher = User::factory()->create();
        $student = User::factory()->create();
        $lesson = $this->createLesson($teacher);

        $package = PracticePackage::create([
            'creator_id' => $teacher->id,
            'lesson_id' => $lesson->id,
            'title' => 'Pack Intensivo',
            'subtitle' => '6 sesiones',
            'description' => 'Paquete QA',
            'sessions_count' => 6,
            'price_amount' => 199.00,
            'price_currency' => 'USD',
            'is_global' => false,
            'visibility' => 'private',
            'delivery_platform' => 'discord',
            'delivery_url' => null,
            'status' => 'published',
        ]);

        $order = PracticePackageOrder::create([
            'practice_package_id' => $package->id,
            'user_id' => $student->id,
            'status' => 'paid',
            'sessions_remaining' => 6,
            'payment_reference' => 'txn_123',
            'paid_at' => now(),
        ]);

        event(new PracticePackagePurchased($order));

        $this->assertDatabaseHas('student_activity_snapshots', [
            'user_id' => $student->id,
            'practice_package_id' => $package->id,
            'category' => 'practice_pack_purchase',
            'scope' => 'practice_pack',
        ]);
    }

    public function test_pack_session_consumption_records_snapshot(): void
    {
        $teacher = User::factory()->create();
        $student = User::factory()->create();
        $lesson = $this->createLesson($teacher);

        $package = PracticePackage::create([
            'creator_id' => $teacher->id,
            'lesson_id' => $lesson->id,
            'title' => 'Pack QA',
            'subtitle' => '3 sesiones',
            'description' => 'Consumo automático',
            'sessions_count' => 3,
            'price_amount' => 99.00,
            'price_currency' => 'USD',
            'is_global' => false,
            'visibility' => 'private',
            'delivery_platform' => 'discord',
            'delivery_url' => null,
            'status' => 'published',
        ]);

        $order = PracticePackageOrder::create([
            'practice_package_id' => $package->id,
            'user_id' => $student->id,
            'status' => 'paid',
            'sessions_remaining' => 3,
            'payment_reference' => 'txn_999',
            'paid_at' => now(),
        ]);

        $service = new PracticePackageOrderService();
        $service->consumeSession($order);

        $this->assertDatabaseHas('student_activity_snapshots', [
            'user_id' => $student->id,
            'practice_package_id' => $package->id,
            'category' => 'practice_pack_consumption',
            'scope' => 'practice_pack',
        ]);
    }

    public function test_practice_request_escalation_records_teacher_snapshot(): void
    {
        $teacherAdmin = User::factory()->create();
        $teacherAdmin->assignRole('teacher_admin');

        $lesson = $this->createLesson($teacherAdmin);
        $course = $lesson->chapter->course;
        $course->teachers()->attach($teacherAdmin->id, ['assigned_by' => $teacherAdmin->id]);

        event(new DiscordPracticeRequestEscalated($lesson, 5));

        $this->assertDatabaseHas('teacher_activity_snapshots', [
            'teacher_id' => $teacherAdmin->id,
            'lesson_id' => $lesson->id,
            'category' => 'practice_request_escalated',
            'scope' => 'discord_practice',
            'value' => 5,
        ]);
    }

    private function createLesson(User $creator): Lesson
    {
        $course = Course::create([
            'slug' => 'telemetry-'.uniqid(),
            'level' => 'b1',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Módulo QA',
            'position' => 1,
        ]);

        return Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'position' => 1,
            'locked' => false,
            'config' => [
                'title' => 'Lección QA',
                'source' => 'youtube',
                'video_id' => 'abc123',
                'length' => 120,
                'badge' => 'QA',
                'estimated_minutes' => 15,
            ],
        ]);
    }
}

