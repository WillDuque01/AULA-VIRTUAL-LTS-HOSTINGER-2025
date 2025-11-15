<?php

namespace App\Listeners;

use App\Events\AssignmentRejected;
use App\Notifications\AssignmentRejectedNotification;
use Illuminate\Support\Facades\Notification;

class SendAssignmentRejectedNotification
{
    public function handle(AssignmentRejected $event): void
    {
        $user = $event->submission->user;

        if (! $user) {
            return;
        }

        Notification::send($user, new AssignmentRejectedNotification($event->submission, $event->reason));
    }
}


