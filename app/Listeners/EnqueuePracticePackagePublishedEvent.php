<?php

namespace App\Listeners;

use App\Events\PracticePackagePublished;
use App\Support\Integrations\IntegrationDispatcher;

class EnqueuePracticePackagePublishedEvent
{
    public function handle(PracticePackagePublished $event): void
    {
        $package = $event->package;

        IntegrationDispatcher::dispatch('practice.package.published', [
            'package' => [
                'id' => $package->id,
                'title' => $package->title,
                'sessions' => $package->sessions_count,
                'price' => $package->price_amount,
                'currency' => $package->price_currency,
                'is_global' => $package->is_global,
            ],
            'creator' => [
                'id' => $package->creator_id,
                'name' => $package->creator?->name,
                'email' => $package->creator?->email,
            ],
        ]);
    }
}


