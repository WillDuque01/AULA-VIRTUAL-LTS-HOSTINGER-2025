<?php

namespace App\Listeners;

use App\Events\ModuleUnlocked;
use App\Notifications\ModuleUnlockedNotification;
use App\Support\Integrations\IntegrationDispatcher;
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

        IntegrationDispatcher::dispatch('module.unlocked', [
            'course_id' => $event->course->id,
            'course_slug' => $event->course->slug,
            'module_title' => $event->moduleTitle,
            'audience' => $event->audienceLabel,
            'recipient_ids' => $event->recipients->pluck('id')->filter()->values()->all(),
            'recipient_emails' => $event->recipients->pluck('email')->filter()->values()->all(),
        ]);
    }
}

