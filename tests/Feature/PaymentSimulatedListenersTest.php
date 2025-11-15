<?php

namespace Tests\Feature;

use App\Models\StudentGroup;
use App\Models\Tier;
use App\Models\User;
use App\Notifications\SimulatedPaymentNotification;
use App\Support\Payments\PaymentSimulator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentSimulatedListenersTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulated_payment_creates_event_and_notifies_user(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $tier = Tier::factory()->create(['access_type' => 'paid', 'price_monthly' => 49, 'slug' => 'premium']);
        StudentGroup::factory()->create(['tier_id' => $tier->id, 'capacity' => null]);

        $simulator = app(PaymentSimulator::class);
        $simulator->simulate($user, $tier, [
            'provider' => 'listener-test',
            'metadata' => ['origin' => 'unit-test'],
        ]);

        $this->assertDatabaseHas('payment_events', [
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'provider' => 'listener-test',
            'status' => 'active',
        ]);

        Notification::assertSentTo(
            $user,
            SimulatedPaymentNotification::class,
            function (SimulatedPaymentNotification $notification) use ($tier) {
                return $notification->subscription->tier_id === $tier->id;
            }
        );
    }
}
