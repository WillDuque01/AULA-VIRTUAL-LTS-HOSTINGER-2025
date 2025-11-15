<?php

namespace Tests\Feature\Notifications;

use App\Events\SubscriptionExpiring;
use App\Events\SubscriptionExpired;
use App\Listeners\SendSubscriptionExpiringNotification;
use App\Listeners\SendSubscriptionExpiredNotification;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SubscriptionNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_expiring_listener_sends_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $tier = Tier::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'active',
            'renews_at' => now()->addDay(),
        ]);

        $listener = new SendSubscriptionExpiringNotification();
        $listener->handle(new SubscriptionExpiring($subscription));

        Notification::assertSentTo($user, \App\Notifications\SubscriptionExpiringNotification::class);
    }

    public function test_subscription_expired_listener_sends_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $tier = Tier::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'cancelled',
            'cancelled_at' => now()->subMinute(),
        ]);

        $listener = new SendSubscriptionExpiredNotification();
        $listener->handle(new SubscriptionExpired($subscription));

        Notification::assertSentTo($user, \App\Notifications\SubscriptionExpiredNotification::class);
    }
}
