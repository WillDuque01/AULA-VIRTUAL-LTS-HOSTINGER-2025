<?php

namespace Tests\Feature\Notifications;

use App\Events\CourseUnlocked;
use App\Events\ModuleUnlocked;
use App\Events\OfferLaunched;
use App\Events\TierUpdated;
use App\Jobs\DispatchIntegrationEventJob;
use App\Models\Course;
use App\Models\Tier;
use App\Models\User;
use App\Notifications\CourseUnlockedNotification;
use App\Notifications\ModuleUnlockedNotification;
use App\Notifications\OfferLaunchedNotification;
use App\Notifications\TierUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BroadcastedNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.make.webhook_url', 'https://hooks.make.test/webhook');
        config()->set('services.make.secret', 'test-secret');
    }

    public function test_course_unlocked_event_notifies_recipients(): void
    {
        Bus::fake([DispatchIntegrationEventJob::class]);
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
        Bus::assertDispatched(DispatchIntegrationEventJob::class);
        $this->assertDatabaseHas('integration_events', ['event' => 'course.unlocked']);
    }

    public function test_module_unlocked_event_notifies_recipients(): void
    {
        Bus::fake([DispatchIntegrationEventJob::class]);
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
        Bus::assertDispatched(DispatchIntegrationEventJob::class);
        $this->assertDatabaseHas('integration_events', ['event' => 'module.unlocked']);
    }

    public function test_offer_launched_event_notifies_recipients(): void
    {
        Bus::fake([DispatchIntegrationEventJob::class]);
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
        Bus::assertDispatched(DispatchIntegrationEventJob::class);
        $this->assertDatabaseHas('integration_events', ['event' => 'offer.launched']);
    }

    public function test_tier_updated_event_notifies_recipients(): void
    {
        Bus::fake([DispatchIntegrationEventJob::class]);
        Notification::fake();

        $tier = Tier::factory()->create();
        $user = User::factory()->create();

        event(new TierUpdated($tier, [$user]));

        Notification::assertSentTo($user, TierUpdatedNotification::class);
        Bus::assertDispatched(DispatchIntegrationEventJob::class);
        $this->assertDatabaseHas('integration_events', ['event' => 'tier.updated']);
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

