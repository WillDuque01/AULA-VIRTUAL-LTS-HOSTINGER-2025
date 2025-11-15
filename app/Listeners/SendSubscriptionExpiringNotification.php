<?php

namespace App\Listeners;

use App\Events\SubscriptionExpiring;
use App\Notifications\SubscriptionExpiringNotification;
use App\Support\Integrations\IntegrationDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSubscriptionExpiringNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(SubscriptionExpiring $event): void
    {
        $user = $event->subscription->user;

        if (! $user) {
            return;
        }

        $user->notify(new SubscriptionExpiringNotification($event->subscription));

        IntegrationDispatcher::dispatch('subscriptions.expiring', [
            'subscription_id' => $event->subscription->id,
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'tier' => $event->subscription->tier?->name,
            'renews_at' => optional($event->subscription->renews_at)?->toIso8601String(),
            'status' => $event->subscription->status,
        ]);
    }
}
