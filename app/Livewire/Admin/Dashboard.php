<?php

namespace App\Livewire\Admin;

use App\Models\Certificate;
use App\Models\IntegrationEvent;
use App\Models\PaymentEvent;
use App\Models\Subscription;
use App\Models\User;
use App\Models\VideoHeatmapSegment;
use App\Models\VideoProgress;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class Dashboard extends Component
{
    public array $metrics = [
        'users' => 0,
        'active_subscriptions' => 0,
        'mrr' => 0.0,
        'watch_hours' => 0.0,
    ];

    public array $integrationStatus = [];

    public array $outboxStats = [
        'pending' => 0,
        'failed' => 0,
        'last_failed_at' => null,
    ];

    public Collection $revenueTrend;

    public Collection $watchPerCourse;

    public Collection $abandonmentInsights;

    public Collection $topXpStudents;

    public Collection $topStreaks;

    public array $certificateStats = [
        'total' => 0,
        'last_24h' => 0,
    ];

    public Collection $recentCertificates;

    public function mount(): void
    {
        $this->revenueTrend = collect();
        $this->watchPerCourse = collect();
        $this->abandonmentInsights = collect();
        $this->topXpStudents = collect();
        $this->topStreaks = collect();
        $this->recentCertificates = collect();
        $this->loadMetrics();
    }

    private function loadMetrics(): void
    {
        $this->metrics['users'] = User::count();
        $this->metrics['active_subscriptions'] = Subscription::where('status', 'active')->count();
        $this->metrics['mrr'] = (float) PaymentEvent::where('created_at', '>=', now()->subDays(30))->sum('amount');
        $this->metrics['watch_hours'] = round((VideoProgress::sum('watched_seconds') ?? 0) / 3600, 1);

        $this->integrationStatus = config('integrations.status', []);
        $this->outboxStats = $this->loadOutboxStats();

        $this->revenueTrend = PaymentEvent::selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->where('created_at', '>=', Carbon::now()->subDays(14))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(function ($row) {
                return [
                    'day' => Carbon::parse($row->day)->format('d M'),
                    'total' => (float) $row->total,
                ];
            });

        $this->watchPerCourse = VideoProgress::selectRaw('courses.slug as course, SUM(video_progress.watched_seconds) as total_seconds')
            ->join('lessons', 'video_progress.lesson_id', '=', 'lessons.id')
            ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
            ->join('courses', 'chapters.course_id', '=', 'courses.id')
            ->groupBy('courses.slug')
            ->orderByDesc('total_seconds')
            ->get()
            ->map(function ($row) {
                return [
                    'course' => $row->course,
                    'hours' => round(($row->total_seconds ?? 0) / 3600, 1),
                ];
            })
            ->take(5);

        $this->abandonmentInsights = $this->loadAbandonmentInsights();
        $this->topXpStudents = $this->loadTopXpStudents();
        $this->topStreaks = $this->loadTopStreaks();
        $this->certificateStats = $this->loadCertificateStats();
        $this->recentCertificates = $this->loadRecentCertificates();
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }

    private function loadOutboxStats(): array
    {
        $pending = IntegrationEvent::where('status', 'pending')->count();
        $failed = IntegrationEvent::where('status', 'failed')->count();
        $lastFailed = IntegrationEvent::where('status', 'failed')->latest()->first();

        return [
            'pending' => $pending,
            'failed' => $failed,
            'last_failed_at' => $lastFailed?->created_at,
        ];
    }

    private function loadAbandonmentInsights(): Collection
    {
        $bucketSeconds = max(1, (int) config('player.heatmap_bucket_seconds', 15));

        return VideoHeatmapSegment::selectRaw('video_heatmap_segments.bucket, video_heatmap_segments.reach_count, lessons.config, courses.slug')
            ->join('lessons', 'video_heatmap_segments.lesson_id', '=', 'lessons.id')
            ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
            ->join('courses', 'chapters.course_id', '=', 'courses.id')
            ->where('video_heatmap_segments.bucket', '>', 0)
            ->orderByDesc('video_heatmap_segments.reach_count')
            ->limit(5)
            ->get()
            ->map(function ($row) use ($bucketSeconds) {
                $seconds = (int) $row->bucket * $bucketSeconds;
                $config = json_decode($row->config ?? '[]', true) ?: [];

                return [
                    'course' => $row->slug,
                    'lesson' => data_get($config, 'title', __('Lesson')),
                    'timestamp' => gmdate('i:s', $seconds),
                    'reach' => $row->reach_count,
                ];
            });
    }

    private function loadTopXpStudents(): Collection
    {
        return User::select('id', 'name', 'experience_points')
            ->where('experience_points', '>', 0)
            ->orderByDesc('experience_points')
            ->limit(5)
            ->get()
            ->map(fn ($user) => [
                'name' => $user->name,
                'xp' => $user->experience_points,
            ]);
    }

    private function loadTopStreaks(): Collection
    {
        return User::select('id', 'name', 'current_streak')
            ->where('current_streak', '>', 0)
            ->orderByDesc('current_streak')
            ->limit(5)
            ->get()
            ->map(fn ($user) => [
                'name' => $user->name,
                'streak' => $user->current_streak,
            ]);
    }

    private function loadCertificateStats(): array
    {
        return [
            'total' => Certificate::count(),
            'last_24h' => Certificate::where('issued_at', '>=', now()->subDay())->count(),
        ];
    }

    private function loadRecentCertificates(): Collection
    {
        return Certificate::with(['user', 'course'])
            ->latest('issued_at')
            ->limit(5)
            ->get()
            ->map(fn ($certificate) => [
                'student' => $certificate->user?->name,
                'course' => $certificate->course?->slug,
                'issued_at' => optional($certificate->issued_at)->diffForHumans(),
                'code' => $certificate->code,
            ]);
    }
}
