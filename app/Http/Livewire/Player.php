<?php

namespace App\Http\Livewire;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Lesson;
use App\Models\VideoProgress;
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

        $this->timeline = $course->chapters
            ->sortBy('position')
            ->map(function ($chapter) use ($submissionMap) {
                return [
                    'id' => $chapter->id,
                    'title' => $chapter->title,
                    'lessons' => $chapter->lessons
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
                        ->toArray(),
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
}


