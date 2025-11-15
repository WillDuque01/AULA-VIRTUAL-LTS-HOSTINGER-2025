<?php

namespace Tests\Feature\Webhooks;

use App\Models\MakeWebhookLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakeWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_accepts_valid_signature(): void
    {
        config()->set('services.make.secret', 'top-secret');

        $payload = ['demo' => 'ok'];
        $raw = json_encode($payload);
        $signature = base64_encode(hash_hmac('sha256', $raw, 'top-secret', true));

        $response = $this->postJson('/api/webhooks/make', $payload, [
            'X-LMS-Signature' => $signature,
            'X-LMS-Event' => 'demo.event',
        ]);

        $response->assertStatus(202);
        $this->assertDatabaseHas('make_webhook_logs', ['event' => 'demo.event']);
    }

    public function test_rejects_invalid_signature(): void
    {
        config()->set('services.make.secret', 'top-secret');

        $response = $this->postJson('/api/webhooks/make', ['demo' => 'ok'], [
            'X-LMS-Signature' => 'invalid',
        ]);

        $response->assertStatus(401);
        $this->assertSame(0, MakeWebhookLog::count());
    }
}

