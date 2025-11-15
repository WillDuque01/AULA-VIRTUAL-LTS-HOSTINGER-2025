<?php

namespace App\Listeners;

use App\Events\CourseUnlocked;
use App\Notifications\CourseUnlockedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendCourseUnlockedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CourseUnlocked $event): void
    {
        if ($event->recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $event->recipients,
            new CourseUnlockedNotification(
                $event->course,
                $event->courseTitle,
                $event->courseSummary,
                $event->audienceLabel,
                $event->courseUrl,
                $event->intro
            )
        );
    }
}
