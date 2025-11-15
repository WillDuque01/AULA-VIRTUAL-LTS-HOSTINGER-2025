<?php

namespace App\Listeners;

use App\Events\CourseUnlocked;
use App\Notifications\CourseUnlockedNotification;
use App\Support\Integrations\IntegrationDispatcher;
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

        IntegrationDispatcher::dispatch('course.unlocked', [
            'course_id' => $event->course->id,
            'course_slug' => $event->course->slug,
            'course_title' => $event->courseTitle,
            'audience' => $event->audienceLabel,
            'summary' => $event->courseSummary,
            'recipient_ids' => $event->recipients->pluck('id')->filter()->values()->all(),
            'recipient_emails' => $event->recipients->pluck('email')->filter()->values()->all(),
        ]);
    }
}
