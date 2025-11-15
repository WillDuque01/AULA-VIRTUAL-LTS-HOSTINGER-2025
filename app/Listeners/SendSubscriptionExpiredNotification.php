<?php

namespace App\Listeners;

use App\Events\SubscriptionExpired;
use App\Notifications\SubscriptionExpiredNotification;
use App\Support\Integrations\IntegrationDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSubscriptionExpiredNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(SubscriptionExpired $event): void
    {
        $user = $event->subscription->user;

        if (! $user) {
            return;
        }

        $user->notify(new SubscriptionExpiredNotification($event->subscription));

        IntegrationDispatcher::dispatch('subscriptions.expired', [
            'subscription_id' => $event->subscription->id,
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'tier' => $event->subscription->tier?->name,
            'expired_at' => now()->toIso8601String(),
            'status' => $event->subscription->status,
        ]);
    }
}
