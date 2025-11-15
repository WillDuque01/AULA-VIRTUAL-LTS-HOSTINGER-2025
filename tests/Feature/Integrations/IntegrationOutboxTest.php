<?php

namespace Tests\Feature\Integrations;

use App\Jobs\DispatchIntegrationEventJob;
use App\Livewire\Admin\IntegrationOutbox;
use App\Models\IntegrationEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class IntegrationOutboxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Gate::define('manage-settings', fn () => true);
    }

    public function test_admin_can_view_outbox_events(): void
    {
        $user = User::factory()->create();
        IntegrationEvent::factory()->count(3)->create([
            'status' => 'failed',
            'event' => 'course.unlocked',
            'target' => 'make',
        ]);

        $response = $this->actingAs($user)->get('/es/admin/integrations/outbox');

        $response->assertOk();
        $response->assertSee('course.unlocked');
    }

    public function test_admin_can_retry_outbox_event(): void
    {
        Bus::fake();
        $user = User::factory()->create();
        $event = IntegrationEvent::factory()->create([
            'status' => 'failed',
            'attempts' => 2,
        ]);

        Livewire::actingAs($user)
            ->test(IntegrationOutbox::class)
            ->call('retry', $event->id);

        $this->assertDatabaseHas('integration_events', [
            'id' => $event->id,
            'status' => 'pending',
            'attempts' => 0,
        ]);

        Bus::assertDispatched(DispatchIntegrationEventJob::class);
    }

    public function test_target_filter_shows_only_selected(): void
    {
        $user = User::factory()->create();
        IntegrationEvent::factory()->create(['target' => 'make', 'event' => 'course.unlocked']);
        IntegrationEvent::factory()->create(['target' => 'discord', 'event' => 'offer.launched']);

        Livewire::actingAs($user)
            ->test(IntegrationOutbox::class)
            ->set('target', 'discord')
            ->assertSee('offer.launched')
            ->assertDontSee('course.unlocked');
    }
}

