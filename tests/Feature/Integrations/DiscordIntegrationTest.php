<?php

namespace Tests\Feature\Integrations;

use App\Jobs\DispatchIntegrationEventJob;
use App\Models\IntegrationEvent;
use App\Models\PracticePackageOrder;
use App\Services\PracticePackageOrderService;
use Database\Seeders\AuditorProfilesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DiscordIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AuditorProfilesSeeder::class);
    }

    public function test_order_paid_dispatches_discord_integration_event(): void
    {
        config([
            'services.discord.webhook_url' => 'https://discord.test/webhook',
            'services.make.webhook_url' => null,
            'services.google.enabled' => false,
            'services.mailerlite.api_key' => null,
            'services.whatsapp.enabled' => false,
        ]);

        Queue::fake();

        $order = PracticePackageOrder::where('status', 'pending')->firstOrFail();

        $service = app(PracticePackageOrderService::class);
        $service->markAsPaid($order, 'DISCORD-UNIT');

        $this->assertDatabaseHas('integration_events', [
            'event' => 'practice.package.purchased',
            'target' => 'discord',
        ]);

        Queue::assertPushed(DispatchIntegrationEventJob::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            $property = $reflection->getProperty('eventId');
            $property->setAccessible(true);
            $eventId = $property->getValue($job);

            return IntegrationEvent::find($eventId)?->target === 'discord';
        });
    }
}

