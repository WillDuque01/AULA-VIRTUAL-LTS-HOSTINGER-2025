<?php

namespace App\Livewire\Admin;

use App\Models\Chapter;
use App\Models\Certificate;
use App\Models\CertificateVerificationLog;
use App\Models\IntegrationEvent;
use App\Models\Lesson;
use App\Models\PaymentEvent;
use App\Models\PracticePackage;
use App\Models\Subscription;
use App\Models\TeacherSubmission;
use App\Models\User;
use App\Models\VideoHeatmapSegment;
use App\Models\VideoProgress;
use App\Support\Guides\IntegrationPlaybook;
use App\Support\Guides\GuideRegistry;
use App\Support\Integrations\WhatsAppMetrics;
use App\Support\Teachers\TeacherPerformance;
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
        'verified_total' => 0,
    ];

    public Collection $recentCertificates;

    public Collection $recentVerifications;

    public array $whatsappStats = [
        'today' => 0,
        'week' => 0,
        'trend' => [],
        'contexts' => [],
    ];

    public array $guideContext = [];

    public array $integrationPlaybook = [];

    public int $pendingTeacherSubmissions = 0;

    public array $pendingContent = [
        'modules' => 0,
        'lessons' => 0,
        'packs' => 0,
    ];

    public array $pendingApprovals = [
        'submissions' => 0,
        'modules' => 0,
        'lessons' => 0,
        'packs' => 0,
    ];

    public Collection $teacherBacklog;

    public Collection $approvalTrend;

    public array $contentStatusTotals = [
        'modules' => [],
        'lessons' => [],
        'packs' => [],
    ];

    public function mount(): void
    {
        $this->revenueTrend = collect();
        $this->watchPerCourse = collect();
        $this->abandonmentInsights = collect();
        $this->topXpStudents = collect();
        $this->topStreaks = collect();
        $this->recentCertificates = collect();
        $this->recentVerifications = collect();
        $this->teacherBacklog = collect();
        $this->approvalTrend = collect();
        $this->loadMetrics();
        $this->guideContext = GuideRegistry::context('admin.dashboard');
        $this->integrationPlaybook = IntegrationPlaybook::grouped('admin');
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
        $this->recentVerifications = $this->loadRecentVerifications();
        $this->whatsappStats = WhatsAppMetrics::summary();
        $this->pendingTeacherSubmissions = TeacherSubmission::where('status', 'pending')->count();
        $this->pendingContent = [
            'modules' => Chapter::where('status', 'pending')->count(),
            'lessons' => Lesson::where('status', 'pending')->count(),
            'packs' => PracticePackage::where('status', 'pending')->count(),
        ];

        $this->pendingApprovals = [
            'submissions' => $this->pendingTeacherSubmissions,
            'modules' => $this->pendingContent['modules'],
            'lessons' => $this->pendingContent['lessons'],
            'packs' => $this->pendingContent['packs'],
        ];

        $this->teacherBacklog = TeacherPerformance::backlogByTeacher();
        $this->approvalTrend = TeacherPerformance::statusTrend();
        $this->contentStatusTotals = TeacherPerformance::contentStatusTotals();
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'guideContext' => $this->guideContext,
            'pendingApprovals' => $this->pendingApprovals,
            'teacherBacklog' => $this->teacherBacklog,
            'approvalTrend' => $this->approvalTrend,
            'contentStatusTotals' => $this->contentStatusTotals,
        ]);
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
            'verified_total' => Certificate::sum('verified_count'),
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
                'verified' => $certificate->verified_count,
            ]);
    }

    private function loadRecentVerifications(): Collection
    {
        return CertificateVerificationLog::with(['certificate.user', 'certificate.course'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($log) => [
                'student' => $log->certificate?->user?->name,
                'course' => $log->certificate?->course?->slug,
                'verified_at' => optional($log->created_at)->diffForHumans(),
                'verified_at_absolute' => optional($log->created_at)->toDayDateTimeString(),
                'code' => $log->certificate?->code,
                'source' => $log->source,
                'ip' => $log->ip,
                'user_agent' => $log->user_agent,
            ]);
    }
}
