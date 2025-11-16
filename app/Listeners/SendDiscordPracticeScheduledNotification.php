<?php

namespace App\Listeners;

use App\Events\DiscordPracticeScheduled;
use App\Models\DiscordPracticeRequest;
use App\Models\User;
use App\Notifications\DiscordPracticeScheduledNotification;
use App\Notifications\DiscordPracticeSlotAvailableNotification;
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
        $this->notifyPendingRequests($practice);
    }

    private function teacherAdmins()
    {
        return User::role('teacher_admin')->get();
    }

    private function notifyPendingRequests($practice): void
    {
        $requests = DiscordPracticeRequest::with('user')
            ->where('lesson_id', $practice->lesson_id)
            ->where('status', 'pending')
            ->get();

        if ($requests->isEmpty()) {
            return;
        }

        $students = $requests->pluck('user')->filter()->unique('id');

        if ($students->isNotEmpty()) {
            Notification::send($students, new DiscordPracticeSlotAvailableNotification($practice));
        }

        DiscordPracticeRequest::whereIn('id', $requests->pluck('id'))
            ->update(['status' => 'fulfilled']);
    }
}


