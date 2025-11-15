<?php

namespace App\Livewire\Student;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\VideoProgress;
use App\Notifications\CertificateIssuedNotification;
use App\Support\Certificates\CertificateGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class Dashboard extends Component
{
    public array $stats = [
        'completed' => 0,
        'total' => 0,
        'percent' => 0,
        'watch_minutes' => 0,
    ];

    public array $gamification = [
        'xp' => 0,
        'streak' => 0,
        'last_completion' => null,
    ];

    public Collection $gamificationFeed;

    public ?Certificate $latestCertificate = null;

    public bool $canGenerateCertificate = false;

    public ?string $certificateDownloadUrl = null;

    public ?Course $course = null;

    public ?Lesson $resumeLesson = null;

    public Collection $upcomingLessons;

    public function mount(): void
    {
        $this->upcomingLessons = collect();
        $this->gamificationFeed = collect();
        $this->latestCertificate = null;
        $this->certificateDownloadUrl = null;
        $this->loadProgress();
    }

    private function loadProgress(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $this->gamification = [
            'xp' => $user->experience_points ?? 0,
            'streak' => $user->current_streak ?? 0,
            'last_completion' => optional($user->last_completion_at)?->diffForHumans(),
        ];
        $this->gamificationFeed = $user->gamificationEvents()
            ->latest()
            ->take(5)
            ->get();

        $this->course = Course::with('chapters.lessons')
            ->where('published', true)
            ->orderBy('id')
            ->first();

        if (! $this->course) {
            return;
        }

        $lessons = $this->course->chapters
            ->flatMap->lessons
            ->sortBy([
                ['chapter_id', 'asc'],
                ['position', 'asc'],
            ]);

        $lessonIds = $lessons->pluck('id');

        $progressMap = VideoProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->keyBy('lesson_id');

        $completed = 0;
        foreach ($lessons as $lesson) {
            $length = (int) data_get($lesson->config, 'length', 0);
            $watched = (int) $progressMap->get($lesson->id)?->watched_seconds ?? 0;
            if ($length > 0 && $watched >= ($length * 0.9)) {
                $completed++;
            }
        }

        $total = $lessons->count();
        $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
        $watchMinutes = round(($progressMap->sum('watched_seconds') ?? 0) / 60, 1);

        $this->stats = [
            'completed' => $completed,
            'total' => $total,
            'percent' => $percent,
            'watch_minutes' => $watchMinutes,
        ];

        $this->canGenerateCertificate = $this->course && $percent >= 90;

        $resumeProgress = $progressMap
            ->sortByDesc(fn ($progress) => $progress->updated_at)
            ->first();

        if ($resumeProgress) {
            $this->resumeLesson = $lessons->firstWhere('id', $resumeProgress->lesson_id);
        } else {
            $this->resumeLesson = $lessons->first();
        }

        $pendingLessons = $lessons->filter(function ($lesson) use ($progressMap) {
            $length = (int) data_get($lesson->config, 'length', 0);
            $watched = (int) $progressMap->get($lesson->id)?->watched_seconds ?? 0;

            if ($length <= 0) {
                return true;
            }

            return $watched < ($length * 0.9);
        });

        $this->upcomingLessons = $pendingLessons->take(4);

        $this->refreshCertificateState($user, $percent);
    }

    public function generateCertificate(CertificateGenerator $generator): void
    {
        $user = Auth::user();

        if (! $user || ! $this->course || ! $this->canGenerateCertificate) {
            return;
        }

        $certificate = $generator->generate($user, $this->course, [
            'percent' => $this->stats['percent'],
        ]);

        Notification::send($user, new CertificateIssuedNotification($certificate));

        $this->latestCertificate = $certificate;
        $this->certificateDownloadUrl = route('certificates.show', ['locale' => app()->getLocale(), 'certificate' => $certificate]);
        session()->flash('certificate_status', __('Certificado generado correctamente.'));
    }

    public function render()
    {
        return view('livewire.student.dashboard');
    }

    private function refreshCertificateState($user, int $percent): void
    {
        if (! $this->course) {
            $this->latestCertificate = null;
            $this->certificateDownloadUrl = null;

            return;
        }

        $certificate = Certificate::where('user_id', $user->id)
            ->where('course_id', $this->course->id)
            ->latest()
            ->first();

        if (! $certificate && config('certificates.auto_issue', true)) {
            $threshold = (int) config('certificates.completion_threshold', 90);
            if ($percent >= $threshold) {
                $certificate = app(CertificateGenerator::class)->generate($user, $this->course, [
                    'percent' => $percent,
                ]);
                Notification::send($user, new CertificateIssuedNotification($certificate));
                session()->flash('certificate_status', __('Tu certificado se emitió automáticamente. 💫'));
            }
        }

        $this->latestCertificate = $certificate;
        $this->certificateDownloadUrl = $certificate
            ? route('certificates.show', ['locale' => app()->getLocale(), 'certificate' => $certificate])
            : null;
    }
}
