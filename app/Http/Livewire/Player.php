<?php

namespace App\Http\Livewire;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\DiscordPractice;
use App\Models\Lesson;
use App\Models\PracticePackage;
use App\Models\PracticePackageOrder;
use App\Models\VideoProgress;
use App\Models\VideoHeatmapSegment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Player extends Component
{
    public Lesson $lesson;
    public array $config = [];
    public string $provider = 'youtube';
    public int $resumeAt = 0;
    public ?int $duration = null;
    public bool $strictSeeking = false;
    public bool $isVideo = true;
    public bool $isLocked = false;
    public string $lockReason = '';
    public ?string $releaseAtHuman = null;
    public ?Lesson $prerequisiteLesson = null;
    public ?string $badge = null;
    public ?int $estimatedMinutes = null;
    public ?string $ctaLabel = null;
    public ?string $ctaUrl = null;
    public array $timeline = [];
    public ?string $courseTitle = null;
    public ?array $practiceCta = null;
    public ?array $practicePackCta = null;
    public array $heatmap = [];
    public array $heatmapHighlights = [];
    public float $progressPercent = 0.0;
    public array $progressMarkers = [];
    public ?array $returnHint = null;
    public ?array $ctaHighlight = null;

    protected ?VideoProgress $progressRecord = null;

    public function mount(Lesson $lesson): void
    {
        $this->lesson = $lesson;
        $this->lesson->loadMissing([
            'assignment',
            'chapter.course.chapters.lessons.assignment',
            'chapter.course.i18n',
        ]);
        $this->config = $lesson->config ?? [];
        $this->isVideo = $lesson->type === 'video';
        $this->provider = $this->isVideo ? $this->resolveProvider() : 'static';
        $this->duration = $this->isVideo ? $this->resolveDuration() : null;
        $this->resumeAt = $this->isVideo ? $this->resolveResumePoint() : 0;
        $this->strictSeeking = $this->isVideo && $this->provider !== 'youtube';
        $this->badge = $this->configValue('badge');
        $this->estimatedMinutes = $this->configValue('estimated_minutes');
        $this->ctaLabel = $this->configValue('cta_label');
        $this->ctaUrl = $this->configValue('cta_url');

        $this->evaluateLocks();
        $this->buildTimeline();
        $this->loadPracticeHooks();
        $this->loadHeatmap();
        $this->calculateProgressPercent();
        $this->buildReturnHint();
    }

    public function render()
    {
        return view('livewire.player', [
            'provider' => $this->provider,
            'resumeAt' => $this->resumeAt,
            'duration' => $this->duration,
            'videoId' => $this->resolveVideoId(),
            'resourceUrl' => $this->resolveResourceUrl(),
            'isVideo' => $this->isVideo,
            'strictSeeking' => $this->strictSeeking,
            'isLocked' => $this->isLocked,
            'lockReason' => $this->lockReason,
            'releaseAtHuman' => $this->releaseAtHuman,
            'badge' => $this->badge,
            'estimatedMinutes' => $this->estimatedMinutes,
            'ctaLabel' => $this->ctaLabel,
            'ctaUrl' => $this->ctaUrl,
            'prerequisiteLesson' => $this->prerequisiteLesson,
            'timeline' => $this->timeline,
            'courseTitle' => $this->courseTitle,
            'practiceCta' => $this->practiceCta,
            'practicePackCta' => $this->practicePackCta,
            'progressMarkers' => $this->progressMarkers,
            'returnHint' => $this->returnHint,
            'ctaHighlight' => $this->ctaHighlight,
            'heatmapHighlights' => $this->heatmapHighlights,
        ]);
    }

    public function toggleStrict(): void
    {
        if (! $this->isVideo || $this->provider === 'youtube') {
            return;
        }

        $this->strictSeeking = ! $this->strictSeeking;
    }

    private function resolveProvider(): string
    {
        $provider = (string) data_get($this->config, 'source', 'youtube');

        if (config('integrations.force_youtube_only')) {
            return 'youtube';
        }

        if (! in_array($provider, ['youtube', 'vimeo', 'cloudflare'], true)) {
            $provider = 'youtube';
        }

        if ($provider !== 'youtube') {
            $status = config('integrations.status.video.driver', 'youtube');
            if ($status === 'youtube') {
                $provider = 'youtube';
            }
        }

        return $provider;
    }

    private function resolveDuration(): ?int
    {
        $length = data_get($this->config, 'length');

        return $length !== null ? (int) $length : null;
    }

    private function resolveResumePoint(): int
    {
        $userId = Auth::id();
        if (! $userId) {
            return 0;
        }

        $progress = VideoProgress::where('lesson_id', $this->lesson->id)
            ->where('user_id', $userId)
            ->first();

        $this->progressRecord = $progress;

        return $progress?->last_second ?? 0;
    }

    private function resolveVideoId(): ?string
    {
        if ($this->provider === 'youtube' || $this->provider === 'vimeo' || $this->provider === 'cloudflare') {
            return data_get($this->config, 'video_id');
        }

        return null;
    }

    private function resolveResourceUrl(): ?string
    {
        return data_get($this->config, 'resource_url');
    }

    private function evaluateLocks(): void
    {
        if ($this->lesson->locked) {
            $this->isLocked = true;
            $this->lockReason = __('Esta lección está bloqueada por el profesor.');
        }

        $releaseAt = $this->configValue('release_at');
        if (! $this->isLocked && $releaseAt) {
            try {
                $date = Carbon::parse($releaseAt);
                if ($date->isFuture()) {
                    $this->isLocked = true;
                    $this->lockReason = __('Disponible el :date', ['date' => $date->translatedFormat('d M Y H:i')]);
                    $this->releaseAtHuman = $date->diffForHumans(null, true, false, 2);
                }
            } catch (\Throwable) {
                // ignore parse issues
            }
        }

        $prerequisiteId = (int) ($this->configValue('prerequisite_lesson_id') ?? 0);
        if (! $this->isLocked && $prerequisiteId > 0) {
            $lesson = Lesson::find($prerequisiteId);
            if ($lesson && ! $this->prerequisiteCompleted($lesson)) {
                $this->isLocked = true;
                $this->prerequisiteLesson = $lesson;
                $this->lockReason = __('Completa ":lesson" antes de continuar.', [
                    'lesson' => data_get($lesson->config, 'title', $lesson->chapter?->title ?? 'lección'),
                ]);
            }
        }
    }

    private function prerequisiteCompleted(Lesson $lesson): bool
    {
        $userId = Auth::id();
        if (! $userId) {
            return false;
        }

        if ($lesson->type === 'assignment') {
            $assignment = $lesson->assignment;
            if (! $assignment) {
                return false;
            }

            $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->where('user_id', $userId)
                ->latest()
                ->first();

            if (! $submission) {
                return false;
            }

            if (! $assignment->requires_approval) {
                return true;
            }

            if ($submission->score === null) {
                return false;
            }

            $requiredPoints = (int) ceil(($assignment->passing_score ?? 70) / 100 * ($assignment->max_points ?: 100));

            return $submission->score >= max(1, $requiredPoints);
        }

        if ($lesson->type !== 'video') {
            return true;
        }

        $progress = VideoProgress::where('lesson_id', $lesson->id)
            ->where('user_id', $userId)
            ->first();

        if (! $progress) {
            return false;
        }

        $expected = (int) data_get($lesson->config, 'length', 0);

        if ($expected > 0) {
            return $progress->watched_seconds >= max(0, $expected - 10);
        }

        return $progress->watched_seconds > 0;
    }

    private function configValue(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    private function buildTimeline(): void
    {
        $course = $this->lesson->chapter?->course;
        if (! $course) {
            $this->timeline = [];
            $this->progressMarkers = [];

            return;
        }

        $course->loadMissing(['chapters.lessons.assignment', 'i18n']);

        $assignmentIds = $course->chapters
            ->flatMap(function ($chapter) {
                return $chapter->lessons
                    ->map(fn (Lesson $lesson) => $lesson->assignment?->id);
            })
            ->filter()
            ->values();

        $submissionMap = collect();
        $userId = Auth::id();

        if ($userId && $assignmentIds->isNotEmpty()) {
            $submissionMap = AssignmentSubmission::whereIn('assignment_id', $assignmentIds)
                ->where('user_id', $userId)
                ->latest('submitted_at')
                ->get()
                ->groupBy('assignment_id')
                ->map(fn ($items) => $items->first());
        }

        $this->courseTitle = optional($course->i18n->firstWhere('locale', app()->getLocale()))
            ?->title ?? $course->slug;

        $totalLessons = max(1, $course->chapters->sum(fn ($chapter) => max(1, $chapter->lessons->count())));
        $lessonsAccumulated = 0;
        $this->progressMarkers = [];

        $this->timeline = $course->chapters
            ->sortBy('position')
            ->map(function ($chapter) use ($submissionMap, $totalLessons, &$lessonsAccumulated) {
                $lessons = $chapter->lessons
                    ->sortBy('position')
                    ->map(function (Lesson $lesson) use ($submissionMap) {
                        $item = [
                            'id' => $lesson->id,
                            'title' => data_get($lesson->config, 'title', "Lección #{$lesson->position}"),
                            'type' => $lesson->type,
                            'current' => $lesson->id === $this->lesson->id,
                            'requiresApproval' => (bool) data_get($lesson->config, 'requires_approval', false),
                        ];

                        if ($lesson->assignment) {
                            $submission = $submissionMap->get($lesson->assignment->id);
                            $item['status'] = $this->resolveAssignmentTimelineStatus($submission, $lesson->assignment);
                            $item['score'] = $submission?->score;
                        } else {
                            $item['status'] = null;
                            $item['score'] = null;
                        }

                        return $item;
                    })
                    ->values()
                    ->toArray();

                $lessonsAccumulated += max(1, count($lessons));
                $chapterPercent = round(($lessonsAccumulated / $totalLessons) * 100, 1);

                $this->progressMarkers[] = [
                    'percent' => min(100, $chapterPercent),
                    'label' => $chapter->title,
                    'lessons' => count($lessons),
                ];

                return [
                    'id' => $chapter->id,
                    'title' => $chapter->title,
                    'lessons' => $lessons,
                ];
            })
            ->values()
            ->toArray();
    }

    private function resolveAssignmentTimelineStatus(?AssignmentSubmission $submission, Assignment $assignment): string
    {
        if (! $submission) {
            return 'pending';
        }

        return match ($submission->status) {
            'approved' => 'approved',
            'rejected' => 'rejected',
            'graded' => $assignment->requires_approval ? 'graded' : 'approved',
            'submitted' => 'submitted',
            default => 'pending',
        };
    }

    private function loadPracticeHooks(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $practice = DiscordPractice::with(['package', 'reservations'])
            ->where('lesson_id', $this->lesson->id)
            ->where('status', 'scheduled')
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->first();

        if ($practice) {
            $reservedCount = $practice->reservations->count();
            $this->practiceCta = [
                'id' => $practice->id,
                'title' => $practice->title,
                'start_at' => $practice->start_at,
                'capacity' => $practice->capacity,
                'available' => max(0, $practice->capacity - $reservedCount),
                'requires_package' => $practice->requires_package,
                'package_title' => $practice->package?->title,
                'has_reservation' => $practice->reservations->contains('user_id', $user->id),
            ];
        }

        $pack = PracticePackage::query()
            ->where('status', 'published')
            ->where(function ($query) {
                $query->where('lesson_id', $this->lesson->id)
                    ->orWhere(fn ($q) => $q->whereNull('lesson_id')->where('is_global', true));
            })
            ->orderByDesc('lesson_id')
            ->orderByDesc('is_global')
            ->first();

        if ($pack) {
            $order = PracticePackageOrder::where('practice_package_id', $pack->id)
                ->where('user_id', $user->id)
                ->where('status', 'paid')
                ->first();

            $this->practicePackCta = [
                'id' => $pack->id,
                'title' => $pack->title,
                'sessions' => $pack->sessions_count,
                'price' => $pack->price_amount,
                'currency' => $pack->price_currency,
                'is_global' => $pack->is_global,
                'has_order' => (bool) $order,
            ];
        }

        $this->resolveContextualCta();
    }

    private function loadHeatmap(): void
    {
        $segments = VideoHeatmapSegment::where('lesson_id', $this->lesson->id)
            ->orderBy('bucket')
            ->get();

        if ($segments->isEmpty()) {
            $this->heatmap = [];
            $this->heatmapHighlights = [];

            return;
        }

        $maxReach = (int) max($segments->pluck('reach_count')->all());

        $this->heatmap = $segments->map(function (VideoHeatmapSegment $segment) use ($maxReach) {
            return [
                'bucket' => $segment->bucket,
                'reach' => $segment->reach_count,
                'intensity' => $maxReach > 0 ? round($segment->reach_count / $maxReach, 3) : 0,
            ];
        })->toArray();

        $this->buildHeatmapHighlights();
    }

    private function calculateProgressPercent(): void
    {
        if (! $this->progressRecord || ! $this->duration || $this->duration <= 0) {
            $this->progressPercent = 0;

            return;
        }

        $watched = $this->progressRecord->watched_seconds ?? $this->progressRecord->last_second ?? 0;
        $this->progressPercent = round(min(1, $watched / $this->duration) * 100, 1);
    }

    private function buildReturnHint(): void
    {
        if (! $this->isVideo || $this->resumeAt <= 0) {
            $this->returnHint = null;

            return;
        }

        if ($this->duration && $this->resumeAt < ($this->duration * 0.1)) {
            $this->returnHint = null;

            return;
        }

        $this->returnHint = [
            'seconds' => $this->resumeAt,
            'label' => gmdate('H:i:s', $this->resumeAt),
        ];
    }

    private function resolveContextualCta(): void
    {
        if ($this->practiceCta) {
            $this->ctaHighlight = [
                'type' => 'practice',
                'title' => $this->practiceCta['has_reservation']
                    ? __('Tienes una práctica confirmada')
                    : __('Reserva tu práctica en vivo'),
                'description' => optional($this->practiceCta['start_at'])->translatedFormat('d M · H:i') ?? __('Próximamente'),
                'status' => $this->practiceCta['has_reservation'] ? 'reserved' : (($this->practiceCta['available'] ?? 0) > 0 ? 'open' : 'full'),
            ];

            return;
        }

        if ($this->practicePackCta) {
            $this->ctaHighlight = [
                'type' => 'pack',
                'title' => $this->practicePackCta['has_order']
                    ? __('Gestiona tus sesiones activas')
                    : __('Impulsa tu avance con sesiones guiadas'),
                'description' => sprintf('%s · %s %s',
                    $this->practicePackCta['title'],
                    $this->practicePackCta['sessions'],
                    __('sesiones')
                ),
                'status' => $this->practicePackCta['has_order'] ? 'owned' : 'cta',
            ];

            return;
        }

        if ($this->ctaLabel && $this->ctaUrl) {
            $this->ctaHighlight = [
                'type' => 'resource',
                'title' => $this->ctaLabel,
                'description' => __('Recurso recomendado al finalizar esta lección.'),
                'status' => 'link',
            ];

            return;
        }

        $this->ctaHighlight = null;
    }

    private function buildHeatmapHighlights(): void
    {
        if (empty($this->heatmap) || ! $this->duration) {
            $this->heatmapHighlights = [];

            return;
        }

        $segmentWindow = max(5, (int) floor($this->duration / 20));

        $this->heatmapHighlights = collect($this->heatmap)
            ->sortByDesc('reach')
            ->take(3)
            ->map(function (array $segment) use ($segmentWindow) {
                $startSeconds = max(0, (int) ($segment['bucket'] ?? 0));
                $endSeconds = min($this->duration, $startSeconds + $segmentWindow);

                return [
                    'bucket' => $segment['bucket'],
                    'reach' => $segment['reach'],
                    'percent' => round(($segment['intensity'] ?? 0) * 100),
                    'label' => sprintf('%s – %s', gmdate('i:s', $startSeconds), gmdate('i:s', $endSeconds)),
                ];
            })
            ->values()
            ->toArray();
    }
}


