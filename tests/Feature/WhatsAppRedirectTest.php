<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Integrations\WhatsAppLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirect_logs_event_and_redirects(): void
    {
        config()->set('services.whatsapp.deeplink', 'https://wa.me/573001112233');

        $user = User::factory()->create();

        $link = WhatsAppLink::assignment(
            ['title' => 'Ensayo', 'status' => 'pending'],
            'test.whatsapp',
            ['foo' => 'bar']
        );

        $response = $this->actingAs($user)->get($link);

        $response->assertRedirect();
        $this->assertStringContainsString('https://wa.me/573001112233', $response->headers->get('Location'));

        $this->assertDatabaseHas('integration_events', [
            'event' => 'whatsapp.cta_clicked',
            'target' => 'whatsapp_cta',
            'status' => 'sent',
        ]);
    }
}


