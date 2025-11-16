<?php

namespace App\Listeners;

use App\Events\DiscordPracticeReserved;
use App\Support\Analytics\TelemetryRecorder;

class RecordPracticeReservationSnapshot
{
    public function __construct(private readonly TelemetryRecorder $recorder)
    {
    }

    public function handle(DiscordPracticeReserved $event): void
    {
        $reservation = $event->reservation->loadMissing('user');
        $practice = $event->practice->loadMissing('lesson.chapter.course');

        if (! $reservation->user_id) {
            return;
        }

        $this->recorder->recordStudentSnapshot($reservation->user_id, 'practice_reservation', [
            'course_id' => $practice->lesson?->chapter?->course?->id,
            'lesson_id' => $practice->lesson_id,
            'practice_package_id' => $practice->practice_package_id,
            'scope' => 'discord_practice',
            'value' => 1,
            'payload' => [
                'practice_id' => $practice->id,
                'practice_title' => $practice->title,
                'practice_type' => $practice->type,
                'cohort_label' => $practice->cohort_label,
                'start_at' => optional($practice->start_at)->toIso8601String(),
                'reservation_id' => $reservation->id,
                'reservation_status' => $reservation->status,
            ],
        ]);
    }
}

