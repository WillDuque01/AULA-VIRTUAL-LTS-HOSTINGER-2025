<?php

namespace Tests\Feature\Notifications;

use App\Events\CourseUnlocked;
use App\Events\ModuleUnlocked;
use App\Events\OfferLaunched;
use App\Events\TierUpdated;
use App\Models\Course;
use App\Models\Tier;
use App\Models\User;
use App\Notifications\CourseUnlockedNotification;
use App\Notifications\ModuleUnlockedNotification;
use App\Notifications\OfferLaunchedNotification;
use App\Notifications\TierUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BroadcastedNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_unlocked_event_notifies_recipients(): void
    {
        Notification::fake();

        $course = $this->createCourse();
        $user = User::factory()->create();

        event(new CourseUnlocked(
            $course,
            [$user],
            'Curso Intensivo A1',
            'Accede al contenido completo del módulo intensivo.',
            'VIP Members',
            'https://academy.test/es/cursos/a1',
            '¡Nuevo contenido disponible!'
        ));

        Notification::assertSentTo($user, CourseUnlockedNotification::class);
    }

    public function test_module_unlocked_event_notifies_recipients(): void
    {
        Notification::fake();

        $course = $this->createCourse();
        $user = User::factory()->create();

        event(new ModuleUnlocked(
            $course,
            [$user],
            'Gramática aplicada',
            'VIP Members',
            'https://academy.test/es/cursos/a1#modulo-2'
        ));

        Notification::assertSentTo($user, ModuleUnlockedNotification::class);
    }

    public function test_offer_launched_event_notifies_recipients(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        event(new OfferLaunched(
            [$user],
            'Oferta verano',
            '40% OFF en la membresía anual',
            'Students VIP',
            'https://academy.test/es/ofertas/verano',
            '2025-08-01',
            '$199',
            '40%',
            'Solo por tiempo limitado.'
        ));

        Notification::assertSentTo($user, OfferLaunchedNotification::class);
    }

    public function test_tier_updated_event_notifies_recipients(): void
    {
        Notification::fake();

        $tier = Tier::factory()->create();
        $user = User::factory()->create();

        event(new TierUpdated($tier, [$user]));

        Notification::assertSentTo($user, TierUpdatedNotification::class);
    }

    private function createCourse(): Course
    {
        return Course::create([
            'slug' => 'demo-'.uniqid(),
            'level' => 'beginner',
            'published' => true,
        ]);
    }
}

