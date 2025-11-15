<?php

namespace App\Livewire\Professor;

use App\Models\Assignment;
use App\Models\Certificate;
use App\Models\Lesson;
use App\Models\VideoHeatmapSegment;
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

    public array $heatmap = [
        'lesson' => null,
        'course' => null,
        'segments' => [],
        'bucket_seconds' => 0,
        'duration' => null,
    ];

    public Collection $recentCertificates;

    public Collection $assignmentAlerts;

    public function mount(): void
    {
        $this->lessonInsights = collect();
        $this->recentActivity = collect();
        $this->heatmap['bucket_seconds'] = max(1, (int) config('player.heatmap_bucket_seconds', 15));
        $this->recentCertificates = collect();
        $this->assignmentAlerts = collect();
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

        $this->loadHeatmapData();
        $this->recentCertificates = Certificate::with(['user', 'course'])
            ->latest('issued_at')
            ->limit(5)
            ->get();

        $now = now();
        $upcomingWindow = $now->copy()->addDays(7);

        $this->assignmentAlerts = Assignment::with(['lesson.chapter.course'])
            ->withCount([
                'submissions as pending_submissions' => fn ($query) => $query->where('status', 'submitted'),
                'submissions as rejected_submissions' => fn ($query) => $query->where('status', 'rejected'),
                'submissions as approved_submissions' => fn ($query) => $query->where('status', 'approved'),
            ])
            ->where(function ($query) use ($now, $upcomingWindow) {
                $query->whereBetween('due_at', [$now, $upcomingWindow])
                    ->orWhereHas('submissions', fn ($sub) => $sub->whereIn('status', ['submitted', 'rejected']));
            })
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END, due_at ASC')
            ->take(6)
            ->get()
            ->map(function (Assignment $assignment) {
                $requiresApproval = (bool) data_get($assignment->lesson->config, 'requires_approval', false);

                return [
                    'id' => $assignment->id,
                    'title' => data_get($assignment->lesson->config, 'title', 'Tarea'),
                    'course' => $assignment->lesson->chapter?->course?->slug,
                    'due_at' => $assignment->due_at,
                    'pending' => (int) $assignment->pending_submissions,
                    'rejected' => (int) $assignment->rejected_submissions,
                    'approved' => (int) $assignment->approved_submissions,
                    'requires_approval' => $requiresApproval,
                ];
            });
    }

    private function loadHeatmapData(): void
    {
        $bucketSeconds = max(1, (int) config('player.heatmap_bucket_seconds', 15));

        $lessonId = VideoHeatmapSegment::select('lesson_id')
            ->selectRaw('SUM(reach_count) as total_reach')
            ->groupBy('lesson_id')
            ->orderByDesc('total_reach')
            ->value('lesson_id');

        if (! $lessonId) {
            $this->heatmap = [
                'lesson' => null,
                'course' => null,
                'segments' => [],
                'bucket_seconds' => $bucketSeconds,
                'duration' => null,
            ];

            return;
        }

        $lesson = Lesson::with('chapter.course')->find($lessonId);
        if (! $lesson) {
            return;
        }

        $segments = VideoHeatmapSegment::where('lesson_id', $lessonId)
            ->orderBy('bucket')
            ->get()
            ->map(function (VideoHeatmapSegment $segment) use ($bucketSeconds) {
                $seconds = $segment->bucket * $bucketSeconds;

                return [
                    'bucket' => $segment->bucket,
                    'seconds' => $seconds,
                    'label' => gmdate('i:s', $seconds),
                    'reach' => $segment->reach_count,
                ];
            })
            ->values()
            ->all();

        $this->heatmap = [
            'lesson' => data_get($lesson->config, 'title', 'Lección '.$lesson->position),
            'course' => $lesson->chapter?->course?->slug,
            'segments' => $segments,
            'bucket_seconds' => $bucketSeconds,
            'duration' => data_get($lesson->config, 'length'),
        ];
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
