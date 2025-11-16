<?php

namespace App\Listeners;

use App\Events\DiscordPracticeScheduled;
use App\Models\User;
use App\Notifications\DiscordPracticeScheduledNotification;
use Illuminate\Support\Facades\Notification;

class SendDiscordPracticeScheduledNotification
{
    public function handle(DiscordPracticeScheduled $event): void
    {
        $practice = $event->practice->loadMissing('creator', 'lesson.chapter.course');
        $recipients = collect([$practice->creator])
            ->filter()
            ->merge($this->teacherAdmins())
            ->unique('id');

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new DiscordPracticeScheduledNotification($practice));
    }

    private function teacherAdmins()
    {
        return User::role('teacher_admin')->get();
    }
}


