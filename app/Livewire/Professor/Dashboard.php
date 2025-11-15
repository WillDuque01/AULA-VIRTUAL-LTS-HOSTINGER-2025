<?php

namespace App\Livewire\Professor;

use App\Models\VideoProgress;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class Dashboard extends Component
{
    public array $metrics = [
        'active_students' => 0,
        'avg_completion' => 0,
        'recent_updates' => 0,
    ];

    public Collection $lessonInsights;

    public Collection $recentActivity;

    public function mount(): void
    {
        $this->lessonInsights = collect();
        $this->recentActivity = collect();
        $this->loadData();
    }

    private function loadData(): void
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);

        $this->metrics['active_students'] = VideoProgress::where('updated_at', '>=', $sevenDaysAgo)
            ->distinct('user_id')
            ->count('user_id');

        $this->metrics['recent_updates'] = VideoProgress::where('updated_at', '>=', $sevenDaysAgo)->count();

        $avgCompletion = $this->calculateAverageCompletion();
        $this->metrics['avg_completion'] = $avgCompletion;

        $progress = VideoProgress::with(['lesson.chapter.course'])
            ->whereHas('lesson', fn ($query) => $query->where('type', 'video'))
            ->get();

        $this->lessonInsights = $progress
            ->groupBy('lesson_id')
            ->map(function ($rows) {
                /** @var \Illuminate\Support\Collection<int, \App\Models\VideoProgress> $rows */
                $lesson = $rows->first()?->lesson;
                if (! $lesson) {
                    return null;
                }

                $length = (int) data_get($lesson->config, 'length', 1);
                $length = $length > 0 ? $length : 1;
                $avgWatched = $rows->avg('watched_seconds') ?? 0;
                $avgCompletion = round(min(100, ($avgWatched / $length) * 100), 1);

                return [
                    'lesson' => $lesson,
                    'course' => $lesson->chapter?->course,
                    'viewers' => $rows->unique('user_id')->count(),
                    'avg_completion' => $avgCompletion,
                ];
            })
            ->filter()
            ->sortByDesc('avg_completion')
            ->take(5)
            ->values();

        $this->recentActivity = $progress
            ->sortByDesc('updated_at')
            ->take(5)
            ->values();
    }

    private function calculateAverageCompletion(): float
    {
        $aggregate = VideoProgress::with('lesson')
            ->whereHas('lesson', fn ($q) => $q->where('type', 'video'))
            ->get();

        if ($aggregate->isEmpty()) {
            return 0.0;
        }

        $totals = $aggregate->map(function (VideoProgress $progress) {
            $lesson = $progress->lesson;
            $length = (int) data_get($lesson->config, 'length', 0);
            if ($length <= 0) {
                return null;
            }

            return min(1, ($progress->watched_seconds ?? 0) / $length);
        })->filter();

        if ($totals->isEmpty()) {
            return 0.0;
        }

        return round($totals->avg() * 100, 1);
    }

    public function render()
    {
        return view('livewire.professor.dashboard');
    }
}
