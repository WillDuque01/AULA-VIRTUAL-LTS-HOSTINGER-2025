<?php

namespace Tests\Feature\Integrations;

use App\Jobs\DispatchIntegrationEventJob;
use App\Models\IntegrationEvent;
use App\Support\Integrations\IntegrationDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class IntegrationDispatcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_records_for_active_targets(): void
    {
        config()->set('services.make.webhook_url', 'https://hooks.make.test/webhook');
        config()->set('services.discord.webhook_url', 'https://discord.test/hook');
        config()->set('services.google.enabled', false);
        config()->set('services.mailerlite.api_key', null);

        Queue::fake();

        IntegrationDispatcher::dispatch('demo.event', ['foo' => 'bar']);

        $this->assertSame(2, IntegrationEvent::count());
        $this->assertDatabaseHas('integration_events', ['target' => 'make']);
        $this->assertDatabaseHas('integration_events', ['target' => 'discord']);
        Queue::assertPushed(DispatchIntegrationEventJob::class, 2);
    }

    public function test_whatsapp_target_is_included_when_enabled(): void
    {
        config()->set('services.whatsapp.enabled', true);
        config()->set('services.whatsapp.token', 'token');
        config()->set('services.whatsapp.phone_number_id', '12345');
        config()->set('services.whatsapp.default_to', '+573001112233');

        Queue::fake();

        IntegrationDispatcher::dispatch('demo.event', ['foo' => 'bar']);

        $this->assertDatabaseHas('integration_events', ['target' => 'whatsapp']);
        Queue::assertPushed(DispatchIntegrationEventJob::class);
    }
}

