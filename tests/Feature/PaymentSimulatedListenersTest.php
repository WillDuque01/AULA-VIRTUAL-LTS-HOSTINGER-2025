<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseI18n;
use App\Models\StudentGroup;
use App\Models\Tier;
use App\Models\User;
use App\Notifications\CourseUnlockedNotification;
use App\Notifications\SimulatedPaymentNotification;
use App\Support\Payments\PaymentSimulator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentSimulatedListenersTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulated_payment_creates_event_and_notifies_user(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $tier = Tier::factory()->create(['access_type' => 'paid', 'price_monthly' => 49, 'slug' => 'premium', 'name' => 'Premium']);
        StudentGroup::factory()->create(['tier_id' => $tier->id, 'capacity' => null]);

        $course = $this->createCourseForTier($tier);

        $simulator = app(PaymentSimulator::class);
        $simulator->simulate($user, $tier, [
            'provider' => 'listener-test',
            'metadata' => ['origin' => 'unit-test'],
        ]);

        $this->assertDatabaseHas('payment_events', [
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'provider' => 'listener-test',
            'status' => 'active',
        ]);

        Notification::assertSentTo(
            $user,
            SimulatedPaymentNotification::class,
            function (SimulatedPaymentNotification $notification) use ($tier) {
                return $notification->subscription->tier_id === $tier->id;
            }
        );

        Notification::assertSentTo($user, CourseUnlockedNotification::class);
    }

    private function createCourseForTier(Tier $tier): Course
    {
        $course = Course::create([
            'slug' => 'nivel-a1',
            'level' => 'beginner',
            'published' => true,
        ]);

        $course->tiers()->attach($tier->id);

        CourseI18n::create([
            'course_id' => $course->id,
            'locale' => 'es',
            'title' => 'Curso Nivel A1',
            'description' => 'Contenido introductor para estudiantes Premium.',
        ]);

        return $course;
    }
}
