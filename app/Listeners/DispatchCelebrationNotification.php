<?php

namespace App\Listeners;

use App\Events\LessonCompleted;
use App\Models\GamificationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class DispatchCelebrationNotification implements ShouldQueue
{
    public function handle(LessonCompleted $event): void
    {
        $latest = GamificationEvent::where('user_id', $event->user->id)
            ->where('lesson_id', $event->lesson->id)
            ->latest('id')
            ->first();

        Log::channel('stack')->info('Lesson completed celebration', [
            'user' => $event->user->id,
            'lesson' => $event->lesson->id,
            'points' => $event->points,
            'streak' => $event->streak,
            'badge' => $latest?->metadata['badge'] ?? null,
        ]);
    }
}


