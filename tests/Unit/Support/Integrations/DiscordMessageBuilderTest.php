<?php

namespace Tests\Unit\Support\Integrations;

use App\Models\IntegrationEvent;
use App\Support\Integrations\DiscordMessageBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscordMessageBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_payload_with_highlight_field(): void
    {
        $event = IntegrationEvent::factory()->make([
            'event' => 'assignment.approved',
            'target' => 'discord',
            'payload' => [
                'assignment' => [
                    'title' => 'Proyecto final',
                    'course' => 'b2-avanzado',
                ],
                'student' => [
                    'name' => 'Lucía',
                ],
                'submission' => [
                    'score' => 95,
                ],
            ],
        ]);

        $payload = (new DiscordMessageBuilder($event))->toPayload();

        $this->assertSame(['parse' => []], $payload['allowed_mentions']);
        $this->assertNotEmpty($payload['embeds']);

        $embed = $payload['embeds'][0];
        $this->assertSame('Assignment Approved', $embed['title']);
        $this->assertEquals(0x22c55e, $embed['color']);
        $this->assertTrue(str_contains($embed['description'], 'Entorno'));
        $this->assertTrue(str_contains($embed['fields'][0]['value'], 'Proyecto final'));
    }
}


