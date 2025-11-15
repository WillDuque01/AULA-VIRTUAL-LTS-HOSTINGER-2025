<?php

namespace App\Listeners;

use App\Events\AssignmentRejected;
use App\Support\Integrations\IntegrationDispatcher;

class EnqueueAssignmentRejectedIntegrationEvent
{
    public function handle(AssignmentRejected $event): void
    {
        $submission = $event->submission;
        $assignment = $submission->assignment;
        $lesson = $assignment?->lesson;
        $course = $lesson?->chapter?->course;
        $user = $submission->user;

        IntegrationDispatcher::dispatch('assignment.rejected', [
            'assignment' => [
                'id' => $assignment?->id,
                'title' => data_get($lesson?->config, 'title'),
                'course' => $course?->slug,
            ],
            'submission' => [
                'id' => $submission->id,
                'reason' => $event->reason,
                'feedback' => $submission->feedback,
            ],
            'student' => [
                'id' => $user?->id,
                'name' => $user?->name,
                'email' => $user?->email,
            ],
        ]);
    }
}


