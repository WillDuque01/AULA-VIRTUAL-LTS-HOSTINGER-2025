<?php

namespace App\Listeners;

use App\Events\TierUpdated;
use App\Notifications\TierUpdatedNotification;
use App\Support\Integrations\IntegrationDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendTierUpdatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(TierUpdated $event): void
    {
        if ($event->recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $event->recipients,
            new TierUpdatedNotification($event->tier)
        );

        IntegrationDispatcher::dispatch('tier.updated', [
            'tier_id' => $event->tier->id,
            'tier_slug' => $event->tier->slug,
            'tier_name' => $event->tier->name,
            'is_active' => $event->tier->is_active,
            'recipient_ids' => $event->recipients->pluck('id')->filter()->values()->all(),
            'recipient_emails' => $event->recipients->pluck('email')->filter()->values()->all(),
        ]);
    }
}

