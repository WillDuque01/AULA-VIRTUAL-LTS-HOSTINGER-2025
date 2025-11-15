<?php

namespace App\Listeners;

use App\Events\PaymentSimulated;
use App\Notifications\SimulatedPaymentNotification;
use App\Support\Integrations\IntegrationDispatcher;

class SendSimulatedPaymentNotification
{
    public function handle(PaymentSimulated $event): void
    {
        $subscription = $event->subscription;
        $user = $subscription->user;

        if (! $user) {
            return;
        }

        $user->notify(new SimulatedPaymentNotification($subscription));

        IntegrationDispatcher::dispatch('payments.simulated', [
            'subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'tier' => $subscription->tier?->name,
            'amount' => $subscription->amount,
            'currency' => $subscription->currency,
            'metadata' => $subscription->metadata,
        ]);
    }
}
