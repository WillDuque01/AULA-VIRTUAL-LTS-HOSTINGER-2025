<?php

namespace Tests\Feature;

use App\Events\TierUpdated;
use App\Livewire\Admin\TierManager;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class TierManagerEventsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Gate::define('manage-settings', fn () => true);
    }

    public function test_tier_update_dispatches_event(): void
    {
        Event::fake(TierUpdated::class);

        $admin = User::factory()->create();
        $tier = Tier::factory()->create(['is_active' => true]);
        $tier->users()->attach($admin->id, ['status' => 'active']);

        Livewire::actingAs($admin)
            ->test(TierManager::class)
            ->call('toggleActive', $tier->id);

        Event::assertDispatched(TierUpdated::class, function (TierUpdated $event) use ($tier, $admin) {
            return $event->tier->id === $tier->id
                && $event->recipients->pluck('id')->contains($admin->id);
        });
    }
}

