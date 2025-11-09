<?php

namespace App\Http\Livewire\Builder;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Support\Arr;
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

        if ($type === 'video') {
            $config['source'] = in_array($lessonData['source'] ?? '', array_keys($this->videoSources), true)
                ? $lessonData['source']
                : 'youtube';
            $config['video_id'] = trim((string) ($lessonData['video_id'] ?? ''));
            $config['length'] = (int) ($lessonData['length'] ?? 0);
        } else {
            $config['resource_url'] = trim((string) ($lessonData['resource_url'] ?? data_get($config, 'resource_url', '')));
        }

        if ($type === 'text') {
            $config['body'] = $lessonData['body'] ?? data_get($config, 'body', '');
        }

        $lesson->type = $type;
        $lesson->locked = (bool) ($lessonData['locked'] ?? false);
        $lesson->config = $config;
        $lesson->save();

        $this->refreshState();
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
                    ];
                })->toArray(),
            ];
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
            ],
            default => [
                'title' => 'Bloque de contenido',
                'body' => '',
            ],
        };
    }
}


