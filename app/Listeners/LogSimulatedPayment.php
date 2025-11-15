<?php

namespace App\Listeners;

use App\Events\PaymentSimulated;
use App\Models\PaymentEvent;

class LogSimulatedPayment
{
    public function handle(PaymentSimulated $event): void
    {
        $subscription = $event->subscription;

        PaymentEvent::create([
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'tier_id' => $subscription->tier_id,
            'provider' => $subscription->provider,
            'status' => $subscription->status,
            'amount' => $subscription->amount,
            'currency' => $subscription->currency,
            'metadata' => $subscription->metadata ?? ['simulated' => true],
        ]);
    }
}
