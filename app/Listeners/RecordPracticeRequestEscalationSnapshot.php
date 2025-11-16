<?php

namespace App\Listeners;

use App\Events\DiscordPracticeRequestEscalated;
use App\Models\User;
use App\Support\Analytics\TelemetryRecorder;

class RecordPracticeRequestEscalationSnapshot
{
    public function __construct(private readonly TelemetryRecorder $recorder)
    {
    }

    public function handle(DiscordPracticeRequestEscalated $event): void
    {
        $lesson = $event->lesson->loadMissing(['chapter.course.teachers']);
        $course = $lesson->chapter?->course;

        $teachers = $course?->teachers ?? collect();

        if ($teachers->isEmpty()) {
            $teachers = User::role('teacher_admin')->get();
        }

        $teachers
            ->unique('id')
            ->each(function (User $teacher) use ($course, $lesson, $event): void {
                $this->recorder->recordTeacherSnapshot($teacher->id, 'practice_request_escalated', [
                    'course_id' => $course?->id,
                    'lesson_id' => $lesson->id,
                    'scope' => 'discord_practice',
                    'value' => $event->pendingCount,
                    'payload' => [
                        'course_slug' => $course?->slug,
                        'lesson_title' => data_get($lesson->config, 'title'),
                        'pending_requests' => $event->pendingCount,
                    ],
                ]);
            });
    }
}


