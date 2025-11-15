<?php

namespace Tests\Feature\Integrations;

use App\Jobs\DispatchIntegrationEventJob;
use App\Models\IntegrationEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DispatchIntegrationEventJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_discord_event_is_marked_as_sent(): void
    {
        config()->set('services.discord.webhook_url', 'https://discord.test/hook');

        Http::fake([
            'https://discord.test/*' => Http::response(['ok' => true], 204),
        ]);

        $event = IntegrationEvent::factory()->create([
            'target' => 'discord',
        ]);

        (new DispatchIntegrationEventJob($event->id))->handle();

        $event->refresh();
        $this->assertEquals('sent', $event->status);
        $this->assertNotNull($event->sent_at);
    }

    public function test_mailerlite_event_without_email_is_skipped(): void
    {
        config()->set('services.mailerlite.api_key', 'fake-key');

        Http::fake();

        $event = IntegrationEvent::factory()->create([
            'target' => 'mailerlite',
            'payload' => ['foo' => 'bar'],
        ]);

        (new DispatchIntegrationEventJob($event->id))->handle();

        $event->refresh();
        $this->assertEquals('skipped', $event->status);
    }
}

