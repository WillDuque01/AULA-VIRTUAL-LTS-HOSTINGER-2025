<?php

namespace App\Listeners;

use App\Events\AssignmentApproved;
use App\Notifications\AssignmentApprovedNotification;
use Illuminate\Support\Facades\Notification;

class SendAssignmentApprovedNotification
{
    public function handle(AssignmentApproved $event): void
    {
        $user = $event->submission->user;

        if (! $user) {
            return;
        }

        Notification::send($user, new AssignmentApprovedNotification($event->submission));
    }
}


