<?php

namespace Tests\Feature\Integrations;

use App\Console\Commands\RetryIntegrationEvents;
use App\Jobs\DispatchIntegrationEventJob;
use App\Models\IntegrationEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RetryIntegrationEventsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_retries_failed_events(): void
    {
        Bus::fake();

        $failed = IntegrationEvent::factory()->count(2)->create(['status' => 'failed', 'attempts' => 3, 'last_error' => 'timeout']);
        IntegrationEvent::factory()->create(['status' => 'sent']);

        $this->artisan('integration:retry', ['status' => 'failed'])
            ->expectsOutput('Requeued 2 event(s).')
            ->assertExitCode(0);

        foreach ($failed as $event) {
            $this->assertDatabaseHas('integration_events', [
                'id' => $event->id,
                'status' => 'pending',
                'attempts' => 0,
                'last_error' => null,
            ]);
        }

        Bus::assertDispatched(DispatchIntegrationEventJob::class, 2);
    }

    public function test_retries_by_target(): void
    {
        Bus::fake();

        $targetEvent = IntegrationEvent::factory()->create(['status' => 'failed', 'target' => 'make']);
        IntegrationEvent::factory()->create(['status' => 'failed', 'target' => 'discord']);

        $this->artisan('integration:retry', ['status' => 'failed', '--target' => 'make'])
            ->expectsOutput('Requeued 1 event(s).')
            ->assertExitCode(0);

        $this->assertDatabaseHas('integration_events', [
            'id' => $targetEvent->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('integration_events', [
            'target' => 'discord',
            'status' => 'failed',
        ]);
    }
}

