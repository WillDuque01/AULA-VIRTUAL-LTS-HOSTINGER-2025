<?php

namespace App\Http\Livewire\Builder;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\DiscordPractice;
use App\Models\Lesson;
use App\Models\PracticePackage;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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

    public array $metrics = [
        'totals' => [
            'chapters' => 0,
            'lessons' => 0,
            'locked' => 0,
            'estimated_minutes' => 0,
        ],
        'chapters' => [],
    ];

    public ?int $focusedLessonId = null;

    public ?array $focus = null;

    public string $focusTab = 'content';

    public ?int $savingLessonId = null;

    public array $lessonTypes = [
        'video' => 'Video',
        'audio' => 'Audio',
        'pdf' => 'PDF',
        'text' => 'Texto enriquecido',
        'iframe' => 'Iframe',
        'quiz' => 'Quiz',
        'assignment' => 'Tarea / Entrega',
    ];

    public array $videoSources = [
        'youtube' => 'YouTube',
        'vimeo' => 'Vimeo',
        'cloudflare' => 'Cloudflare Stream',
    ];

    public string $statusFilter = 'all';

    public array $statusFilterOptions = [
        'all' => 'Todos',
        'pending' => 'Pendientes',
        'published' => 'Publicados',
        'rejected' => 'Rechazados',
    ];

    public array $availablePrerequisites = [];

    protected $listeners = [
        'builder-reorder' => 'saveOrder',
        'builder-new-chapter' => 'addChapter',
        'builder-save-focused' => 'saveFocusedLesson',
    ];

    public function mount(Course $course): void
    {
        $this->course = $course;
        $this->refreshState();
    }

    public function cycleFocusTab(string $direction = 'next'): void
    {
        $tabs = $this->focusTabs();
        $currentIndex = array_search($this->focusTab, $tabs, true);

        if ($currentIndex === false) {
            $this->focusTab = $tabs[0];

            return;
        }

        $offset = $direction === 'prev' ? -1 : 1;
        $newIndex = ($currentIndex + $offset + count($tabs)) % count($tabs);
        $this->focusTab = $tabs[$newIndex];
    }

    public function addChapter(): void
    {
        $nextPosition = (int) (Chapter::where('course_id', $this->course->id)->max('position') ?? 0) + 1;

        Chapter::create([
            'course_id' => $this->course->id,
            'title' => 'Nuevo capítulo',
            'position' => $nextPosition,
            'status' => 'published',
            'created_by' => Auth::id(),
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

        $lesson = Lesson::create([
            'chapter_id' => $chapterId,
            'type' => $type,
            'position' => $nextPosition,
            'config' => $defaultConfig,
            'locked' => false,
            'status' => 'published',
            'created_by' => Auth::id(),
        ]);

        $this->refreshState(true);

        if ($lesson) {
            $this->focusLesson($lesson->id);
        }
    }

    public function removeLesson(int $lessonId): void
    {
        Lesson::whereKey($lessonId)->delete();
        $this->refreshState(true);

        if ($this->focusedLessonId === $lessonId) {
            $this->clearFocus();
        }
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
        $lessonId = (int) ($lessonData['id'] ?? 0);
        $lesson = Lesson::findOrFail($lessonId);
        $this->savingLessonId = $lessonId ?: null;

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

        if ($type === 'assignment') {
            $config['instructions'] = $lessonData['instructions'] ?? data_get($config, 'instructions', '');
            $config['due_at'] = $this->normalizeDate($lessonData['due_at'] ?? data_get($config, 'due_at'));
            $config['max_points'] = max(1, (int) ($lessonData['max_points'] ?? data_get($config, 'max_points', 100)));
            $config['rubric'] = $this->normalizeRubric($lessonData['rubric'] ?? data_get($config, 'rubric', []));
            $config['passing_score'] = $this->sanitizePassingScore($lessonData['passing_score'] ?? data_get($config, 'passing_score', 70));
            $config['requires_approval'] = (bool) ($lessonData['requires_approval'] ?? data_get($config, 'requires_approval', true));
        }

        $config['prerequisite_lesson_id'] = $this->sanitizePrerequisite(
            (int) ($lessonData['prerequisite_lesson_id'] ?? data_get($config, 'prerequisite_lesson_id', 0)),
            $lesson->id
        );

        $this->resetErrorBag();
        $validationErrors = $this->validateLessonConfig($type, $config, $chapterIndex, $lessonIndex);
        if (! empty($validationErrors)) {
            $this->dispatch('builder:flash', [
                'variant' => 'error',
                'message' => collect($validationErrors)->implode("\n"),
            ]);

            $this->savingLessonId = null;

            return;
        }

        $lesson->type = $type;
        $lesson->locked = (bool) ($lessonData['locked'] ?? false);
        $lesson->config = $config;
        $lesson->save();

        if ($type === 'assignment') {
            $this->syncAssignment($lesson, $config);
        } elseif ($lesson->assignment) {
            $lesson->assignment()->delete();
        }

        $this->refreshState();

        if ($this->focusedLessonId === $lesson->id) {
            $this->focusLesson($lesson->id);
        }

        $this->dispatch('builder:flash', [
            'variant' => 'success',
            'message' => 'Lección actualizada con éxito',
        ]);

        if ($this->savingLessonId === $lessonId) {
            $this->savingLessonId = null;
        }
    }

    public function saveFocusedLesson(): void
    {
        $lessonId = $this->focus['lesson']['id'] ?? $this->focusedLessonId;
        if (! $lessonId) {
            return;
        }

        [$chapterIndex, $lessonIndex] = $this->findLessonIndexes($lessonId);
        if ($chapterIndex === null || $lessonIndex === null) {
            return;
        }

        $this->saveLesson($chapterIndex, $lessonIndex);
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

    public function focusLesson(int $lessonId): void
    {
        $payload = $this->findLessonInState($lessonId);

        if (! $payload) {
            $this->clearFocus();

            return;
        }

        $this->focusedLessonId = $lessonId;
        $this->focus = $payload;
        $this->focusTab = 'content';
        $this->dispatch('builder:focus-open', ['lessonId' => $lessonId]);
    }

    public function clearFocus(): void
    {
        $this->focusedLessonId = null;
        $this->focus = null;
        $this->focusTab = 'content';
        $this->dispatch('builder:focus-open', ['lessonId' => null]);
    }

    public function setFocusTab(string $tab): void
    {
        if (! in_array($tab, $this->focusTabs(), true)) {
            return;
        }

        $this->focusTab = $tab;
    }

    public function duplicateLesson(int $lessonId): void
    {
        $lesson = Lesson::with(['assignment', 'chapter'])->findOrFail($lessonId);
        $this->assertLessonInCourse($lesson);

        $copy = $lesson->replicate(['position']);
        $copy->position = (int) (Lesson::where('chapter_id', $lesson->chapter_id)->max('position') ?? 0) + 1;
        $copy->save();

        if ($lesson->assignment) {
            $assignmentClone = $lesson->assignment->replicate(['lesson_id']);
            $assignmentClone->lesson_id = $copy->id;
            $assignmentClone->save();
        }

        $this->refreshState(true);
        $this->focusLesson($copy->id);

        $this->dispatch('builder:flash', [
            'variant' => 'success',
            'message' => __('Lección duplicada'),
        ]);
        $this->dispatch('builder:celebrate');
    }

    public function quickMoveLesson(int $lessonId, $targetChapterId): void
    {
        $chapterId = (int) $targetChapterId;
        if ($chapterId <= 0) {
            return;
        }

        $lesson = Lesson::with('chapter')->findOrFail($lessonId);
        $this->assertLessonInCourse($lesson);

        if ($lesson->chapter_id === $chapterId) {
            return;
        }

        $chapter = Chapter::where('course_id', $this->course->id)->whereKey($chapterId)->first();
        if (! $chapter) {
            return;
        }

        $lesson->chapter_id = $chapterId;
        $lesson->position = (int) (Lesson::where('chapter_id', $chapterId)->max('position') ?? 0) + 1;
        $lesson->save();

        $this->refreshState(true);
        $this->focusLesson($lesson->id);

        $this->dispatch('builder:flash', [
            'variant' => 'success',
            'message' => __('Lección movida correctamente'),
        ]);
    }

    public function quickConvertLesson(int $lessonId, string $type): void
    {
        if (! array_key_exists($type, $this->lessonTypes)) {
            return;
        }

        $lesson = Lesson::with(['chapter', 'assignment'])->findOrFail($lessonId);
        $this->assertLessonInCourse($lesson);

        if ($lesson->type === $type) {
            return;
        }

        $currentConfig = $lesson->config ?? [];
        $defaults = $this->defaultLessonConfig($type);

        $lesson->type = $type;
        $lesson->config = array_merge($defaults, [
            'title' => $currentConfig['title'] ?? $defaults['title'] ?? __('Lección'),
            'badge' => $currentConfig['badge'] ?? null,
            'cta_label' => $currentConfig['cta_label'] ?? null,
            'cta_url' => $currentConfig['cta_url'] ?? null,
            'estimated_minutes' => $currentConfig['estimated_minutes'] ?? null,
            'prerequisite_lesson_id' => $currentConfig['prerequisite_lesson_id'] ?? null,
        ]);

        $lesson->save();

        if ($type === 'assignment') {
            $this->syncAssignment($lesson, $lesson->config ?? []);
        } elseif ($lesson->assignment) {
            $lesson->assignment()->delete();
        }

        $this->refreshState();
        $this->focusLesson($lesson->id);

        $this->dispatch('builder:flash', [
            'variant' => 'success',
            'message' => __('Tipo de lección actualizado'),
        ]);
        $this->dispatch('builder:celebrate');
    }

    private function findLessonIndexes(?int $lessonId): array
    {
        if (! $lessonId) {
            return [null, null];
        }

        foreach ($this->state['chapters'] as $chapterIndex => $chapter) {
            foreach (($chapter['lessons'] ?? []) as $lessonIndex => $lesson) {
                if ((int) ($lesson['id'] ?? 0) === $lessonId) {
                    return [$chapterIndex, $lessonIndex];
                }
            }
        }

        return [null, null];
    }

    public function render()
    {
        return view('livewire.builder.course-builder', [
            'lessonTypes' => $this->lessonTypes,
            'videoSources' => $this->videoSources,
            'availablePrerequisites' => $this->availablePrerequisites,
            'statusFilterOptions' => $this->statusFilterOptions,
        ]);
    }

    private function focusTabs(): array
    {
        return ['content', 'config', 'practice', 'gamification'];
    }

    private function assertLessonInCourse(Lesson $lesson): void
    {
        $lesson->loadMissing('chapter');
        if (! $lesson->chapter || (int) $lesson->chapter->course_id !== (int) $this->course->id) {
            abort(403, __('No puedes modificar esta lección.'));
        }
    }

    private function refreshState(bool $shouldReorder = false): void
    {
        $this->course->refresh();

        $chapters = $this->course
            ->chapters()
            ->orderBy('position')
            ->with(['lessons' => function ($query) {
                $query->orderBy('position')->with('assignment');
            }])
            ->get();

        $assignmentStats = $this->resolveAssignmentStats($chapters);
        $lessonIds = $chapters
            ->flatMap(fn (Chapter $chapter) => $chapter->lessons->pluck('id'))
            ->filter()
            ->values();
        $practiceMeta = $this->resolvePracticeMeta($lessonIds);
        $packMeta = $this->resolvePackMeta($lessonIds);

        $this->state['chapters'] = $chapters->map(function (Chapter $chapter) use ($assignmentStats, $practiceMeta, $packMeta) {
            return [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'position' => $chapter->position,
                'status' => $chapter->status,
                'lessons' => $chapter->lessons->map(function (Lesson $lesson) use ($assignmentStats, $practiceMeta, $packMeta) {
                    $config = $lesson->config ?? [];
                    $assignmentId = $lesson->assignment?->id;

                    return [
                        'id' => $lesson->id,
                        'status' => $lesson->status,
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
                        'instructions' => data_get($config, 'instructions'),
                        'due_at' => data_get($config, 'due_at'),
                        'max_points' => data_get($config, 'max_points'),
                        'passing_score' => data_get($config, 'passing_score', 70),
                        'requires_approval' => (bool) data_get($config, 'requires_approval', true),
                        'rubric' => is_array(data_get($config, 'rubric'))
                            ? implode(PHP_EOL, data_get($config, 'rubric'))
                            : data_get($config, 'rubric'),
                        'assignment_id' => $assignmentId,
                        'stats' => $assignmentId
                            ? ($assignmentStats[$assignmentId] ?? ['pending' => 0, 'approved' => 0, 'rejected' => 0])
                            : null,
                        'practice_meta' => $practiceMeta[(int) $lesson->id] ?? null,
                        'pack_meta' => $packMeta[(int) $lesson->id] ?? null,
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

        $this->recalculateMetrics($chapters);
        $this->hydrateFocusFromState();

        if ($shouldReorder) {
            $this->resequenceChapters();
        }

        $this->dispatch('builder:refresh-sortables');
    }

    /**
     * @param  \Illuminate\Support\Collection|array  $lessonIds
     */
    private function resolvePracticeMeta($lessonIds): array
    {
        $ids = collect($lessonIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $practices = DiscordPractice::query()
            ->whereIn('lesson_id', $ids)
            ->where('status', 'scheduled')
            ->where('start_at', '>=', now()->subDays(7))
            ->orderBy('start_at')
            ->get()
            ->groupBy('lesson_id')
            ->map(function (Collection $items) {
                /** @var \App\Models\DiscordPractice|null $next */
                $next = $items->sortBy('start_at')->first();

                return [
                    'total' => $items->count(),
                    'requires_pack' => $items->contains(fn (DiscordPractice $practice) => (bool) $practice->requires_package),
                    'next_start' => optional($next?->start_at)->toDateTimeString(),
                ];
            });

        return $practices->toArray();
    }

    /**
     * @param  \Illuminate\Support\Collection|array  $lessonIds
     */
    private function resolvePackMeta($lessonIds): array
    {
        $ids = collect($lessonIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $packages = PracticePackage::query()
            ->where('status', 'published')
            ->whereIn('lesson_id', $ids)
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy('lesson_id')
            ->map(function (Collection $items) {
                /** @var PracticePackage|null $primary */
                $primary = $items->first();
                if (! $primary) {
                    return null;
                }

                return [
                    'id' => $primary->id,
                    'title' => $primary->title,
                    'sessions' => (int) $primary->sessions_count,
                    'price' => (float) $primary->price_amount,
                    'currency' => $primary->price_currency,
                    'updated_at' => optional($primary->updated_at)->toDateTimeString(),
                ];
            })
            ->filter()
            ->toArray();

        return $packages;
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
            'assignment' => [
                'title' => 'Nueva tarea',
                'instructions' => '',
                'due_at' => null,
                'max_points' => 100,
                'passing_score' => 70,
                'requires_approval' => true,
                'rubric' => [],
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

        if ($type === 'assignment' && $config['requires_approval'] && ($config['passing_score'] ?? 0) <= 0) {
            $errors[] = 'Define un puntaje mínimo para aprobar la tarea.';
            $this->addError("state.chapters.$chapterIndex.lessons.$lessonIndex.passing_score", 'Requerido');
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

    private function syncAssignment(Lesson $lesson, array $config): void
    {
        Assignment::updateOrCreate(
            ['lesson_id' => $lesson->id],
            [
                'instructions' => $config['instructions'] ?? '',
                'due_at' => $config['due_at'] ?? null,
                'max_points' => $config['max_points'] ?? 100,
                'passing_score' => $config['passing_score'] ?? 70,
                'requires_approval' => $config['requires_approval'] ?? true,
                'rubric' => $config['rubric'] ?? [],
            ]
        );
    }

    private function sanitizePassingScore($value): int
    {
        $score = (int) $value;

        return max(0, min(100, $score));
    }

    private function normalizeRubric($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value));
        }

        if (is_string($value) && trim($value) !== '') {
            return collect(preg_split('/\r\n|\r|\n/', $value))
                ->filter()
                ->map(fn ($line) => trim($line))
                ->values()
                ->all();
        }

        return [];
    }

    private function resolveAssignmentStats(Collection $chapters): array
    {
        $assignmentIds = $chapters->flatMap(function (Chapter $chapter) {
            return $chapter->lessons
                ->filter(fn (Lesson $lesson) => $lesson->assignment)
                ->map(fn (Lesson $lesson) => $lesson->assignment?->id);
        })->filter()->values();

        if ($assignmentIds->isEmpty()) {
            return [];
        }

        return AssignmentSubmission::selectRaw('assignment_id, status, COUNT(*) as total')
            ->whereIn('assignment_id', $assignmentIds)
            ->groupBy('assignment_id', 'status')
            ->get()
            ->groupBy('assignment_id')
            ->map(function ($group) {
                $pending = $group
                    ->whereIn('status', ['submitted', 'graded', 'draft'])
                    ->sum('total');

                $approved = optional($group->firstWhere('status', 'approved'))->total ?? 0;
                $rejected = optional($group->firstWhere('status', 'rejected'))->total ?? 0;

                return [
                    'pending' => (int) $pending,
                    'approved' => (int) $approved,
                    'rejected' => (int) $rejected,
                ];
            })
            ->toArray();
    }

    private function recalculateMetrics(Collection $chapters): void
    {
        $totals = [
            'chapters' => $chapters->count(),
            'lessons' => 0,
            'locked' => 0,
            'estimated_minutes' => 0,
        ];

        $byChapter = [];

        foreach ($chapters as $chapter) {
            $lessonsCount = $chapter->lessons->count();
            $lockedCount = $chapter->lessons->where('locked', true)->count();
            $estimatedMinutes = $chapter->lessons->sum(fn (Lesson $lesson) => (int) data_get($lesson->config, 'estimated_minutes', 0));
            $assignmentsPending = $chapter->lessons
                ->filter(fn (Lesson $lesson) => $lesson->type === 'assignment')
                ->count();

            $totals['lessons'] += $lessonsCount;
            $totals['locked'] += $lockedCount;
            $totals['estimated_minutes'] += $estimatedMinutes;

            $byChapter[$chapter->id] = [
                'lessons' => $lessonsCount,
                'locked' => $lockedCount,
                'estimated_minutes' => $estimatedMinutes,
                'assignments' => $assignmentsPending,
            ];
        }

        $this->metrics = [
            'totals' => $totals,
            'chapters' => $byChapter,
        ];
    }

    private function findLessonInState(int $lessonId): ?array
    {
        foreach ($this->state['chapters'] as $chapter) {
            foreach ($chapter['lessons'] as $lesson) {
                if ((int) ($lesson['id'] ?? 0) === $lessonId) {
                    return [
                        'lesson' => $lesson,
                        'chapter' => [
                            'id' => $chapter['id'],
                            'title' => $chapter['title'],
                            'position' => $chapter['position'],
                            'metrics' => $this->metrics['chapters'][$chapter['id']] ?? null,
                        ],
                    ];
                }
            }
        }

        return null;
    }

    private function hydrateFocusFromState(): void
    {
        if (! $this->focusedLessonId) {
            return;
        }

        $this->focus = $this->findLessonInState($this->focusedLessonId);

        if (! $this->focus) {
            $this->focusedLessonId = null;
        }
    }
}


