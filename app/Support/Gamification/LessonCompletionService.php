<?php

namespace App\Support\Gamification;

use App\Events\LessonCompleted;
use App\Models\GamificationEvent;
use App\Models\Lesson;
use App\Models\User;
use App\Models\VideoProgress;
use Illuminate\Support\Carbon;

class LessonCompletionService
{
    public function handle(VideoProgress $progress): ?array
    {
        $progress->loadMissing(['lesson.chapter.course', 'user']);

        $lesson = $progress->lesson;
        $user = $progress->user;

        if (! $lesson || ! $user) {
            return null;
        }

        if ($progress->completed_at) {
            return null;
        }

        $length = (int) data_get($lesson->config, 'length', 0);
        $threshold = $this->threshold($length);

        if ($length > 0 && $progress->watched_seconds < $threshold) {
            return null;
        }

        $progress->forceFill(['completed_at' => now()])->save();

        $points = $this->pointsForLesson($lesson);
        $streak = $this->updateUserStats($user);
        $badge = $this->badgeForStreak($streak);

        GamificationEvent::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'type' => 'lesson_completed',
            'points' => $points,
            'metadata' => [
                'course' => $lesson->chapter?->course?->slug,
                'streak' => $streak,
                'badge' => $badge,
            ],
        ]);

        LessonCompleted::dispatch($user, $lesson, $points, $streak);

        return [
            'celebration' => true,
            'points' => $points,
            'streak' => $streak,
            'badge' => $badge,
            'lesson_title' => data_get($lesson->config, 'title', __('Lesson')),
        ];
    }

    private function threshold(int $length): int
    {
        if ($length <= 0) {
            return 0;
        }

        $percentageThreshold = (int) floor($length * 0.9);

        return max($percentageThreshold, $length - 10);
    }

    private function pointsForLesson(Lesson $lesson): int
    {
        return (int) data_get($lesson->config, 'gamification.points', config('gamification.video_completion_points', 50));
    }

    private function updateUserStats(User $user): int
    {
        $now = now();
        $window = (int) config('gamification.streak_window_hours', 36);
        $continueStreak = $user->last_completion_at instanceof Carbon
            && $user->last_completion_at->greaterThan($now->copy()->subHours($window));

        $user->experience_points = ($user->experience_points ?? 0) + config('gamification.video_completion_points', 50);
        $user->current_streak = $continueStreak ? ($user->current_streak + 1) : 1;
        $user->last_completion_at = $now;
        $user->save();

        return $user->current_streak;
    }

    private function badgeForStreak(int $streak): ?string
    {
        $milestones = collect(config('gamification.milestones', []))
            ->sortKeys();

        return $milestones
            ->filter(fn ($label, $required) => $streak >= (int) $required)
            ->last();
    }
}


