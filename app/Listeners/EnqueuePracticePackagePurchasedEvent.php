<?php

namespace App\Listeners;

use App\Events\PracticePackagePurchased;
use App\Support\Integrations\IntegrationDispatcher;

class EnqueuePracticePackagePurchasedEvent
{
    public function handle(PracticePackagePurchased $event): void
    {
        $order = $event->order->load('package', 'user');

        IntegrationDispatcher::dispatch('practice.package.purchased', [
            'package' => [
                'id' => $order->practice_package_id,
                'title' => $order->package?->title,
                'sessions' => $order->package?->sessions_count,
            ],
            'order' => [
                'id' => $order->id,
                'sessions_remaining' => $order->sessions_remaining,
                'payment_reference' => $order->payment_reference,
            ],
            'student' => [
                'id' => $order->user_id,
                'name' => $order->user?->name,
                'email' => $order->user?->email,
            ],
        ]);
    }
}


