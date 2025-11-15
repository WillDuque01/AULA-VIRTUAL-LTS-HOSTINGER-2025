<?php

namespace Tests\Feature;

use App\Models\StudentGroup;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SimulatePaymentCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_simulator_assigns_tier_and_subscription(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'student@example.com']);
        $tier = Tier::factory()->create(['slug' => 'pro', 'price_monthly' => 19.00]);
        $group = StudentGroup::factory()->create(['tier_id' => $tier->id]);

        Artisan::call('simulate:payment', [
            'email' => $user->email,
            'tier' => $tier->slug,
            '--provider' => 'simulator-test',
            '--amount' => 19.00,
            '--currency' => 'USD',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'provider' => 'simulator-test',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('tier_user', [
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('group_user', [
            'user_id' => $user->id,
            'student_group_id' => $group->id,
        ]);
    }
}
