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

        $captured = [];

        Http::fake([
            'https://discord.test/*' => function ($request) use (&$captured) {
                $captured = $request->data();

                return Http::response(['ok' => true], 204);
            },
        ]);

        $event = IntegrationEvent::factory()->create([
            'target' => 'discord',
        ]);

        (new DispatchIntegrationEventJob($event->id))->handle();

        $event->refresh();
        $this->assertEquals('sent', $event->status);
        $this->assertNotNull($event->sent_at);
        $this->assertArrayHasKey('embeds', $captured);
        $this->assertSame(['parse' => []], $captured['allowed_mentions']);
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

    public function test_whatsapp_event_is_marked_as_sent(): void
    {
        config()->set('services.whatsapp.enabled', true);
        config()->set('services.whatsapp.token', 'token');
        config()->set('services.whatsapp.phone_number_id', '999999');
        config()->set('services.whatsapp.default_to', '+573001112233');
        config()->set('services.whatsapp.deeplink', 'https://wa.me/573001112233');

        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['messages' => []], 200),
        ]);

        $event = IntegrationEvent::factory()->create([
            'target' => 'whatsapp',
            'payload' => [
                'assignment' => ['title' => 'Ensayo', 'course' => 'B1'],
                'student' => ['name' => 'Carla'],
                'submission' => ['reason' => 'Faltan referencias'],
            ],
        ]);

        (new DispatchIntegrationEventJob($event->id))->handle();

        $event->refresh();
        $this->assertEquals('sent', $event->status);
    }
}

