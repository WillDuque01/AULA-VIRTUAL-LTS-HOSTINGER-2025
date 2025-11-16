<?php

namespace App\Listeners;

use App\Events\DiscordPracticeReserved;
use App\Models\User;
use App\Notifications\DiscordPracticeReservedNotification;
use Illuminate\Support\Facades\Notification;

class SendDiscordPracticeReservedNotification
{
    public function handle(DiscordPracticeReserved $event): void
    {
        $practice = $event->practice->loadMissing('creator');
        $recipients = collect([$practice->creator])
            ->filter()
            ->merge($this->teacherAdmins())
            ->unique('id');

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new DiscordPracticeReservedNotification(
            $practice,
            $event->reservation->loadMissing('user')
        ));
    }

    private function teacherAdmins()
    {
        return User::role('teacher_admin')->get();
    }
}


