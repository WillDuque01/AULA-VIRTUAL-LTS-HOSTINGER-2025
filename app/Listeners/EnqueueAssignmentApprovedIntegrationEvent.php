<?php

namespace App\Listeners;

use App\Events\AssignmentApproved;
use App\Support\Integrations\IntegrationDispatcher;
class EnqueueAssignmentApprovedIntegrationEvent
{
    public function handle(AssignmentApproved $event): void
    {
        $submission = $event->submission;
        $assignment = $submission->assignment;
        $lesson = $assignment?->lesson;
        $course = $lesson?->chapter?->course;
        $user = $submission->user;

        IntegrationDispatcher::dispatch('assignment.approved', [
            'assignment' => [
                'id' => $assignment?->id,
                'title' => data_get($lesson?->config, 'title', 'Assignment'),
                'course' => $course?->slug,
                'max_points' => $assignment?->max_points,
                'passing_score' => $assignment?->passing_score,
            ],
            'submission' => [
                'id' => $submission->id,
                'score' => $submission->score,
                'approved_at' => optional($submission->approved_at)->toIso8601String(),
            ],
            'student' => [
                'id' => $user?->id,
                'name' => $user?->name,
                'email' => $user?->email,
            ],
        ]);
    }
}


