<?php

namespace App\Http\Livewire\Builder;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CourseBuilder extends Component
{
    public Course $course;

    /**
     * Estructura editable utilizada por la vista.
     */
    public array $state = [
        'chapters' => [],
    ];

    public array $lessonTypes = [
        'video' => 'Video',
        'audio' => 'Audio',
        'pdf' => 'PDF',
        'text' => 'Texto enriquecido',
        'iframe' => 'Iframe',
        'quiz' => 'Quiz',
    ];

    public array $videoSources = [
        'youtube' => 'YouTube',
        'vimeo' => 'Vimeo',
        'cloudflare' => 'Cloudflare Stream',
    ];

    public array $availablePrerequisites = [];

    protected $listeners = [
        'builder-reorder' => 'saveOrder',
    ];

    public function mount(Course $course): void
    {
        $this->course = $course;
        $this->refreshState();
    }

    public function addChapter(): void
    {
        $nextPosition = (int) (Chapter::where('course_id', $this->course->id)->max('position') ?? 0) + 1;

        Chapter::create([
            'course_id' => $this->course->id,
            'title' => 'Nuevo capítulo',
            'position' => $nextPosition,
        ]);

        $this->refreshState(true);
    }

    public function removeChapter(int $chapterId): void
    {
        Chapter::whereKey($chapterId)->delete();
        $this->refreshState(true);
    }

    public function addLesson(int $chapterId, string $type): void
    {
        $type = array_key_exists($type, $this->lessonTypes) ? $type : 'text';
        $nextPosition = (int) (Lesson::where('chapter_id', $chapterId)->max('position') ?? 0) + 1;

        $defaultConfig = $this->defaultLessonConfig($type);

        Lesson::create([
            'chapter_id' => $chapterId,
            'type' => $type,
            'position' => $nextPosition,
            'config' => $defaultConfig,
            'locked' => false,
        ]);

        $this->refreshState(true);
    }

    public function removeLesson(int $lessonId): void
    {
        Lesson::whereKey($lessonId)->delete();
        $this->refreshState(true);
    }

    public function saveChapterTitle(int $chapterId, int $chapterIndex): void
    {
        $title = trim((string) data_get($this->state, "chapters.$chapterIndex.title"));
        if ($title === '') {
            $this->addError("state.chapters.$chapterIndex.title", 'El título no puede estar vacío.');

            return;
        }

        Chapter::whereKey($chapterId)->update(['title' => $title]);
        $this->refreshState();
    }

    public function saveLesson(int $chapterIndex, int $lessonIndex): void
    {
        $lessonData = data_get($this->state, "chapters.$chapterIndex.lessons.$lessonIndex", []);
        $lesson = Lesson::findOrFail((int) ($lessonData['id'] ?? 0));

        $type = $lessonData['type'] ?? $lesson->type;
        if (! array_key_exists($type, $this->lessonTypes)) {
            $type = 'text';
        }

        $config = $lesson->config ?? [];
        $config['title'] = trim((string) ($lessonData['title'] ?? data_get($config, 'title', 'Lección')));
        $config['estimated_minutes'] = max(0, (int) ($lessonData['estimated_minutes'] ?? data_get($config, 'estimated_minutes', 0)));
        $config['badge'] = trim((string) ($lessonData['badge'] ?? data_get($config, 'badge', '')));
        $config['cta_label'] = trim((string) ($lessonData['cta_label'] ?? data_get($config, 'cta_label', '')));
        $config['cta_url'] = trim((string) ($lessonData['cta_url'] ?? data_get($config, 'cta_url', '')));
        $config['release_at'] = $this->normalizeDate($lessonData['release_at'] ?? data_get($config, 'release_at'));

        if ($type === 'video') {
            $config['source'] = in_array($lessonData['source'] ?? '', array_keys($this->videoSources), true)
                ? $lessonData['source']
                : 'youtube';
            $config['video_id'] = trim((string) ($lessonData['video_id'] ?? data_get($config, 'video_id', '')));
            $config['length'] = max(0, (int) ($lessonData['length'] ?? data_get($config, 'length', 0)));
        } else {
            $config['resource_url'] = trim((string) ($lessonData['resource_url'] ?? data_get($config, 'resource_url', '')));
            $config['length'] = max(0, (int) ($lessonData['length'] ?? data_get($config, 'length', 0)));
        }

        if ($type === 'text') {
            $config['body'] = $lessonData['body'] ?? data_get($config, 'body', '');
        }

        if ($type === 'quiz') {
            $config['quiz_ref'] = trim((string) ($lessonData['quiz_ref'] ?? data_get($config, 'quiz_ref', '')));
        }

        $config['prerequisite_lesson_id'] = $this->sanitizePrerequisite(
            (int) ($lessonData['prerequisite_lesson_id'] ?? data_get($config, 'prerequisite_lesson_id', 0)),
            $lesson->id
        );

        $this->resetErrorBag();
        $validationErrors = $this->validateLessonConfig($type, $config, $chapterIndex, $lessonIndex);
        if (! empty($validationErrors)) {
            $this->dispatchBrowserEvent('builder:flash', [
                'variant' => 'error',
                'message' => collect($validationErrors)->implode("\n"),
            ]);

            return;
        }

        $lesson->type = $type;
        $lesson->locked = (bool) ($lessonData['locked'] ?? false);
        $lesson->config = $config;
        $lesson->save();

        $this->refreshState();
        $this->dispatchBrowserEvent('builder:flash', [
            'variant' => 'success',
            'message' => 'Lección actualizada con éxito',
        ]);
    }

    public function saveOrder(array $payload): void
    {
        $chapters = $payload['chapters'] ?? $payload;

        DB::transaction(function () use ($chapters) {
            foreach ($chapters as $chapterIndex => $chapterPayload) {
                $chapterId = (int) ($chapterPayload['id'] ?? 0);
                if (! $chapterId) {
                    continue;
                }

                Chapter::whereKey($chapterId)->update(['position' => $chapterIndex + 1]);

                foreach (Arr::get($chapterPayload, 'lessons', []) as $lessonIndex => $lessonPayload) {
                    $lessonId = (int) ($lessonPayload['id'] ?? 0);
                    if (! $lessonId) {
                        continue;
                    }

                    Lesson::whereKey($lessonId)->update([
                        'chapter_id' => $chapterId,
                        'position' => $lessonIndex + 1,
                    ]);
                }
            }
        });

        $this->refreshState();
    }

    public function render()
    {
        return view('livewire.builder.course-builder', [
            'lessonTypes' => $this->lessonTypes,
            'videoSources' => $this->videoSources,
            'availablePrerequisites' => $this->availablePrerequisites,
        ]);
    }

    private function refreshState(bool $shouldReorder = false): void
    {
        $this->course->refresh();

        $chapters = $this->course
            ->chapters()
            ->orderBy('position')
            ->with(['lessons' => function ($query) {
                $query->orderBy('position');
            }])
            ->get();

        $this->state['chapters'] = $chapters->map(function (Chapter $chapter) {
            return [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'position' => $chapter->position,
                'lessons' => $chapter->lessons->map(function (Lesson $lesson) {
                    $config = $lesson->config ?? [];

                    return [
                        'id' => $lesson->id,
                        'type' => $lesson->type,
                        'title' => data_get($config, 'title', "Lección #{$lesson->position}"),
                        'locked' => (bool) $lesson->locked,
                        'source' => data_get($config, 'source', 'youtube'),
                        'video_id' => data_get($config, 'video_id'),
                        'length' => data_get($config, 'length'),
                        'resource_url' => data_get($config, 'resource_url'),
                        'body' => data_get($config, 'body'),
                        'estimated_minutes' => data_get($config, 'estimated_minutes', 0),
                        'badge' => data_get($config, 'badge'),
                        'cta_label' => data_get($config, 'cta_label'),
                        'cta_url' => data_get($config, 'cta_url'),
                        'release_at' => data_get($config, 'release_at'),
                        'prerequisite_lesson_id' => data_get($config, 'prerequisite_lesson_id'),
                        'quiz_ref' => data_get($config, 'quiz_ref'),
                    ];
                })->toArray(),
            ];
        })->toArray();

        $this->availablePrerequisites = $chapters->flatMap(function (Chapter $chapter) {
            return $chapter->lessons->mapWithKeys(function (Lesson $lesson) use ($chapter) {
                $title = "{$chapter->title} · ".data_get($lesson->config, 'title', "Lección {$lesson->position}");

                return [$lesson->id => $title];
            });
        })->toArray();

        if ($shouldReorder) {
            $this->resequenceChapters();
        }

        $this->dispatchBrowserEvent('builder:refresh-sortables');
    }

    private function resequenceChapters(): void
    {
        DB::transaction(function () {
            $chapters = $this->course
                ->chapters()
                ->orderBy('position')
                ->with('lessons')
                ->get();

            foreach ($chapters as $chapterIndex => $chapter) {
                $chapter->update(['position' => $chapterIndex + 1]);

                foreach ($chapter->lessons as $lessonIndex => $lesson) {
                    $lesson->update(['position' => $lessonIndex + 1]);
                }
            }
        });
    }

    private function defaultLessonConfig(string $type): array
    {
        return match ($type) {
            'video' => [
                'title' => 'Nueva lección de video',
                'source' => 'youtube',
                'video_id' => '',
                'length' => 0,
            ],
            'audio' => [
                'title' => 'Clip de audio',
                'resource_url' => '',
            ],
            'pdf' => [
                'title' => 'Recurso PDF',
                'resource_url' => '',
            ],
            'iframe' => [
                'title' => 'Actividad embebida',
                'resource_url' => '',
            ],
            'quiz' => [
                'title' => 'Nuevo quiz',
                'quiz_ref' => '',
            ],
            default => [
                'title' => 'Bloque de contenido',
                'body' => '',
            ],
        };
    }

    private function validateLessonConfig(string $type, array $config, int $chapterIndex, int $lessonIndex): array
    {
        $errors = [];

        if ($config['title'] === '') {
            $errors[] = 'El título es obligatorio.';
            $this->addError("state.chapters.$chapterIndex.lessons.$lessonIndex.title", 'Requerido');
        }

        if ($type === 'video') {
            if ($config['video_id'] === '') {
                $errors[] = 'Debes indicar el ID del video.';
                $this->addError("state.chapters.$chapterIndex.lessons.$lessonIndex.video_id", 'Requerido');
            }
        }

        if (in_array($type, ['audio', 'pdf', 'iframe'], true) && $config['resource_url'] === '') {
            $errors[] = 'Debes indicar la URL del recurso.';
            $this->addError("state.chapters.$chapterIndex.lessons.$lessonIndex.resource_url", 'Requerido');
        }

        if ($config['cta_url'] && ! filter_var($config['cta_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'La URL del CTA no es válida.';
            $this->addError("state.chapters.$chapterIndex.lessons.$lessonIndex.cta_url", 'Formato no válido');
        }

        return $errors;
    }

    private function normalizeDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->toIso8601String();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function sanitizePrerequisite(int $candidate, int $currentLessonId): ?int
    {
        if ($candidate === 0 || $candidate === $currentLessonId) {
            return null;
        }

        return array_key_exists($candidate, $this->availablePrerequisites) ? $candidate : null;
    }
}


