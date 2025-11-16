<?php

namespace App\Listeners;

use App\Events\PracticePackagePurchased;
use App\Support\Analytics\TelemetryRecorder;

class RecordPracticePackPurchaseSnapshot
{
    public function __construct(private readonly TelemetryRecorder $recorder)
    {
    }

    public function handle(PracticePackagePurchased $event): void
    {
        $order = $event->order->loadMissing(['user', 'package.lesson.chapter.course']);

        if (! $order->user_id) {
            return;
        }

        $package = $order->package;
        $lesson = $package?->lesson;
        $course = $lesson?->chapter?->course;

        $this->recorder->recordStudentSnapshot($order->user_id, 'practice_pack_purchase', [
            'course_id' => $course?->id,
            'lesson_id' => $lesson?->id,
            'practice_package_id' => $package?->id,
            'scope' => 'practice_pack',
            'value' => $package?->sessions_count ?? null,
            'payload' => [
                'order_id' => $order->id,
                'sessions_remaining' => $order->sessions_remaining,
                'price_amount' => $package?->price_amount,
                'price_currency' => $package?->price_currency,
                'package_title' => $package?->title,
                'paid_at' => optional($order->paid_at)->toIso8601String(),
            ],
        ]);
    }
}

