<?php

namespace App\Listeners;

use App\Events\TierUpdated;
use App\Notifications\TierUpdatedNotification;
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
    }
}

