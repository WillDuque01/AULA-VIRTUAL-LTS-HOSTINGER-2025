<?php

namespace App\Listeners;

use App\Events\ModuleUnlocked;
use App\Notifications\ModuleUnlockedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendModuleUnlockedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ModuleUnlocked $event): void
    {
        if ($event->recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $event->recipients,
            new ModuleUnlockedNotification(
                $event->course,
                $event->moduleTitle,
                $event->audienceLabel,
                $event->moduleUrl,
                $event->intro
            )
        );
    }
}

