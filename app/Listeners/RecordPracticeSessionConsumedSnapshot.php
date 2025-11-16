<?php

namespace App\Listeners;

use App\Events\PracticePackageSessionConsumed;
use App\Support\Analytics\TelemetryRecorder;

class RecordPracticeSessionConsumedSnapshot
{
    public function __construct(private readonly TelemetryRecorder $recorder)
    {
    }

    public function handle(PracticePackageSessionConsumed $event): void
    {
        $order = $event->order->loadMissing(['package.lesson.chapter.course']);

        if (! $order->user_id) {
            return;
        }

        $package = $order->package;
        $lesson = $package?->lesson;
        $course = $lesson?->chapter?->course;

        $this->recorder->recordStudentSnapshot($order->user_id, 'practice_pack_consumption', [
            'course_id' => $course?->id,
            'lesson_id' => $lesson?->id,
            'practice_package_id' => $package?->id,
            'scope' => 'practice_pack',
            'value' => 1,
            'payload' => [
                'order_id' => $order->id,
                'sessions_remaining' => $order->sessions_remaining,
                'package_title' => $package?->title,
                'consumed_at' => now()->toIso8601String(),
            ],
        ]);
    }
}


