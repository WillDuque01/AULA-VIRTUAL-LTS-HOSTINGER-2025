<?php

namespace App\Listeners;

use App\Events\DiscordPracticeRequestEscalated;
use App\Models\DiscordPractice;
use App\Models\User;
use App\Notifications\DiscordPracticeRequestEscalatedNotification;
use Illuminate\Support\Facades\Notification;

class SendDiscordPracticeRequestEscalatedNotification
{
    public function handle(DiscordPracticeRequestEscalated $event): void
    {
        $lesson = $event->lesson->loadMissing('chapter.course');

        $creator = DiscordPractice::where('lesson_id', $lesson->id)
            ->latest('start_at')
            ->first()?->creator;

        $recipients = collect([$creator])
            ->filter()
            ->merge($this->teacherAdmins())
            ->unique('id');

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new DiscordPracticeRequestEscalatedNotification($lesson, $event->pendingCount));
    }

    private function teacherAdmins()
    {
        return User::role('teacher_admin')->get();
    }
}


