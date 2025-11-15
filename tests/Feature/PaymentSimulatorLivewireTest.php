<?php

namespace Tests\Feature;

use App\Livewire\Admin\PaymentSimulator;
use App\Models\PaymentEvent;
use App\Models\StudentGroup;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentSimulatorLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('manage-settings', fn ($user = null) => true);
    }

    public function test_admin_can_simulate_payment_from_panel(): void
    {
        $admin = User::factory()->create();
        $student = User::factory()->create(['email' => 'student@example.com']);
        $tier = Tier::factory()->create(['slug' => 'plus', 'access_type' => 'paid', 'price_monthly' => 15]);
        StudentGroup::factory()->create(['tier_id' => $tier->id]);

        Livewire::actingAs($admin)
            ->test(PaymentSimulator::class)
            ->set('form.email', $student->email)
            ->set('form.tier_id', $tier->id)
            ->set('form.provider', 'admin-panel')
            ->set('form.status', 'active')
            ->set('form.amount', 15)
            ->set('form.currency', 'usd')
            ->call('simulate')
            ->assertSet('flashError', null)
            ->assertSet('flashStatus', __('Suscripcion simulada para :user en el tier :tier.', [
                'user' => $student->email,
                'tier' => $tier->name,
            ]));

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $student->id,
            'tier_id' => $tier->id,
            'provider' => 'admin-panel',
        ]);

        $this->assertDatabaseHas('payment_events', [
            'user_id' => $student->id,
            'tier_id' => $tier->id,
            'provider' => 'admin-panel',
        ]);

    }

    public function test_shows_error_when_user_not_found(): void
    {
        $admin = User::factory()->create();
        $tier = Tier::factory()->create();

        Livewire::actingAs($admin)
            ->test(PaymentSimulator::class)
            ->set('form.email', 'missing@example.com')
            ->set('form.tier_id', $tier->id)
            ->call('simulate')
            ->assertSet('flashError', __('No se encontro un usuario con ese correo.'));
    }
}

