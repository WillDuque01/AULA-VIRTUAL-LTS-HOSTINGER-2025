<?php

namespace App\Livewire\Professor;

use App\Events\DiscordPracticeScheduled;
use App\Models\CohortTemplate;
use App\Models\DiscordPractice;
use App\Models\Lesson;
use App\Models\PracticePackage;
use App\Models\PracticeTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class DiscordPracticePlanner extends Component
{
    public Collection $lessons;
    public Collection $packages;
    public Collection $templates;
    public Collection $practices;

    public ?int $selectedLesson = null;
    public string $type = 'cohort';
    public ?string $cohort_label = null;
    public bool $requires_package = false;
    public ?int $practice_package_id = null;
    public string $title = '';
    public string $description = '';
    public string $start_at = '';
    public string $end_at = '';
    public int $duration_minutes = 60;
    public int $capacity = 10;
    public string $discord_channel_url = '';
    public string $templateName = '';
    public ?int $selectedTemplateId = null;
    public array $templateSlots = [
        [
            'weekday' => 'monday',
            'time' => '18:00',
        ],
    ];
    public array $seriesForm = [
        'template_id' => null,
        'start_date' => '',
        'weeks' => 1,
    ];
    public array $cohortTemplates = [];
    public ?string $selectedCohortTemplate = null;
    public ?array $activeCohortTemplate = null;

    public string $calendarRangeStart;
    public string $calendarRangeEnd;
    public array $calendarHours = [];

    public array $weekDuplicationForm = [
        'offset' => 1,
        'repeat' => 1,
    ];

    public function mount(): void
    {
        $this->lessons = Lesson::with('chapter.course')
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get();

        $this->packages = PracticePackage::where('creator_id', auth()->id())
            ->where('status', 'published')
            ->orderBy('title')
            ->get();

        $this->templates = PracticeTemplate::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        $this->loadCohortTemplates();

        $startOfWeek = now()->startOfWeek();
        $this->calendarRangeStart = $startOfWeek->toDateString();
        $this->calendarRangeEnd = $startOfWeek->copy()->addDays(6)->toDateString();
        $this->calendarHours = collect(range(7, 21))
            ->map(fn ($hour) => str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00')
            ->toArray();

        $this->loadPractices();
        $this->seriesForm['template_id'] = null;
    }

    public function loadPractices(): void
    {
        $this->practices = DiscordPractice::with(['lesson.chapter.course', 'package'])
            ->whereBetween('start_at', [
                Carbon::parse($this->calendarRangeStart)->startOfDay(),
                Carbon::parse($this->calendarRangeEnd)->endOfDay(),
            ])
            ->orderBy('start_at')
            ->get();
    }

    public function goToPreviousWeek(): void
    {
        $this->setWeek(Carbon::parse($this->calendarRangeStart)->subWeek());
    }

    public function goToNextWeek(): void
    {
        $this->setWeek(Carbon::parse($this->calendarRangeStart)->addWeek());
    }

    public function resetWeek(): void
    {
        $this->setWeek(now());
    }

    private function setWeek(Carbon $start): void
    {
        $weekStart = $start->copy()->startOfWeek();
        $this->calendarRangeStart = $weekStart->toDateString();
        $this->calendarRangeEnd = $weekStart->copy()->addDays(6)->toDateString();
        $this->loadPractices();
    }

    public function createPractice(): void
    {
        $data = $this->validate([
            'selectedLesson' => ['required', 'exists:lessons,id'],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'type' => ['required', Rule::in(['cohort', 'global'])],
            'cohort_label' => ['nullable', 'string', 'max:120'],
            'start_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:240'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'discord_channel_url' => ['nullable', 'url', 'max:255'],
            'requires_package' => ['boolean'],
            'practice_package_id' => ['nullable', 'exists:practice_packages,id'],
        ], [
            'selectedLesson.required' => __('Selecciona una lección'),
        ]);

        if ($data['requires_package'] && empty($data['practice_package_id'])) {
            $this->addError('practice_package_id', __('Selecciona un pack asociado.'));

            return;
        }

        $start = Carbon::parse($data['start_at']);
        $end = Carbon::parse($data['start_at'])->addMinutes($data['duration_minutes']);

        $practice = DiscordPractice::create([
            'lesson_id' => $data['selectedLesson'],
            'title' => $data['title'],
            'description' => $data['description'],
            'type' => $data['type'],
            'cohort_label' => $data['type'] === 'cohort' ? $data['cohort_label'] : null,
            'practice_package_id' => $data['practice_package_id'] ?? null,
            'start_at' => $start,
            'end_at' => $end,
            'duration_minutes' => $data['duration_minutes'],
            'capacity' => $data['capacity'],
            'discord_channel_url' => $data['discord_channel_url'],
            'created_by' => auth()->id(),
            'requires_package' => $data['requires_package'],
        ]);

        $this->markCohortTemplateAsPublished();

        $this->reset([
            'selectedLesson',
            'title',
            'description',
            'type',
            'cohort_label',
            'start_at',
            'duration_minutes',
            'capacity',
            'discord_channel_url',
            'requires_package',
            'practice_package_id',
        ]);

        $this->loadPractices();
        $this->dispatch('practice-planned');

        event(new DiscordPracticeScheduled($practice->fresh(['lesson.chapter.course', 'creator'])));
    }

    public function movePractice(int $practiceId, string $date, string $hour): void
    {
        $practice = DiscordPractice::findOrFail($practiceId);
        $newStart = Carbon::parse("{$date} {$hour}", config('app.timezone'));

        if ($newStart->isPast()) {
            $this->addError('calendar', __('No puedes mover la práctica al pasado.'));

            return;
        }

        $duration = $practice->duration_minutes ?: optional($practice->start_at)->diffInMinutes($practice->end_at) ?? 60;
        $newEnd = $practice->end_at ? $newStart->copy()->addMinutes($duration) : null;

        $practice->update([
            'start_at' => $newStart,
            'end_at' => $newEnd,
        ]);

        $this->loadPractices();
        $this->dispatch('practice-moved');

        event(new DiscordPracticeScheduled(
            $practice->fresh(['lesson.chapter.course', 'creator'])
        ));
    }

    public function getCalendarDaysProperty(): array
    {
        $start = Carbon::parse($this->calendarRangeStart)->startOfDay();

        return collect(range(0, 6))
            ->map(fn ($offset) => [
                'date' => $start->copy()->addDays($offset),
            ])
            ->all();
    }

    public function saveTemplate(): void
    {
        $this->validate([
            'templateName' => ['required', 'string', 'max:120'],
        ]);

        $payload = [
            'lesson_id' => $this->selectedLesson,
            'title' => $this->title,
            'type' => $this->type,
            'cohort_label' => $this->cohort_label,
            'requires_package' => $this->requires_package,
            'practice_package_id' => $this->practice_package_id,
            'duration_minutes' => $this->duration_minutes,
            'capacity' => $this->capacity,
            'discord_channel_url' => $this->discord_channel_url,
            'description' => $this->description,
            'slots' => $this->normalizeTemplateSlots($this->templateSlots),
        ];

        $template = PracticeTemplate::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'name' => $this->templateName,
            ],
            [
                'payload' => $payload,
            ]
        );

        $this->templateName = '';
        $this->selectedTemplateId = $template->id;
        $this->loadTemplates();
        $this->applyTemplate($template->id);
        $this->dispatch('practice-template-saved');
    }

    public function applyTemplate(?int $templateId): void
    {
        if (! $templateId) {
            return;
        }

        $template = $this->templates->firstWhere('id', $templateId)
            ?? PracticeTemplate::where('user_id', auth()->id())->find($templateId);

        if (! $template) {
            return;
        }

        $payload = $template->payload ?? [];

        $this->selectedLesson = $payload['lesson_id'] ?? $this->selectedLesson;
        $this->title = $payload['title'] ?? $this->title;
        $this->type = $payload['type'] ?? $this->type;
        $this->cohort_label = $payload['cohort_label'] ?? $this->cohort_label;
        $this->requires_package = (bool) ($payload['requires_package'] ?? false);
        $this->practice_package_id = $payload['practice_package_id'] ?? null;
        $this->duration_minutes = (int) ($payload['duration_minutes'] ?? $this->duration_minutes);
        $this->capacity = (int) ($payload['capacity'] ?? $this->capacity);
        $this->discord_channel_url = $payload['discord_channel_url'] ?? $this->discord_channel_url;
        $this->description = $payload['description'] ?? $this->description;
        $this->templateSlots = $this->normalizeTemplateSlots($payload['slots'] ?? $this->templateSlots);

        $this->selectedTemplateId = $template->id;
    }

    public function applyCohortTemplate(?string $templateKey): void
    {
        if (! $templateKey) {
            $this->selectedCohortTemplate = null;
            $this->activeCohortTemplate = null;
            return;
        }

        if (str_contains($templateKey, ':')) {
            [$source, $key] = explode(':', $templateKey, 2);
        } else {
            $source = 'config';
            $key = $templateKey;
        }

        if ($source === 'db' && $key) {
            $template = CohortTemplate::find($key);
            if (! $template) {
                return;
            }
            $payload = [
                'name' => $template->name,
                'type' => $template->type,
                'cohort_label' => $template->cohort_label,
                'duration_minutes' => $template->duration_minutes,
                'capacity' => $template->capacity,
                'requires_package' => $template->requires_package,
                'practice_package_id' => $template->practice_package_id,
                'description' => $template->description,
                'slots' => $template->slots,
            ];
        } else {
            $payload = $this->cohortTemplates["config:{$key}"] ?? $this->cohortTemplates[$templateKey] ?? config("practice.cohort_templates.{$key}");
            if ($payload && ! isset($payload['name'])) {
                $payload['name'] = $key;
            }
        }

        if (! $payload) {
            $this->activeCohortTemplate = null;
            return;
        }

        $this->type = $payload['type'] ?? $this->type;
        $this->cohort_label = $payload['cohort_label'] ?? $this->cohort_label;
        $this->requires_package = (bool) ($payload['requires_package'] ?? $this->requires_package);
        $this->practice_package_id = $payload['practice_package_id'] ?? $this->practice_package_id;
        $this->duration_minutes = (int) ($payload['duration_minutes'] ?? $this->duration_minutes);
        $this->capacity = (int) ($payload['capacity'] ?? $this->capacity);
        $this->discord_channel_url = $payload['discord_channel_url'] ?? $this->discord_channel_url;
        $this->description = $payload['description'] ?? $this->description;
        $this->templateSlots = $this->normalizeTemplateSlots($payload['slots'] ?? $this->templateSlots);
        if (! empty($payload['lesson_id']) && Lesson::whereKey($payload['lesson_id'])->exists()) {
            $this->selectedLesson = $payload['lesson_id'];
        }

        $this->selectedCohortTemplate = $templateKey;
        $this->activeCohortTemplate = null;

        if ($source === 'db' && isset($template)) {
            $this->activeCohortTemplate = [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'price_amount' => (float) $template->price_amount,
                'price_currency' => $template->price_currency,
                'status' => $template->status,
                'is_featured' => (bool) $template->is_featured,
                'product_id' => $template->product?->id,
                'capacity' => (int) $template->capacity,
                'enrolled_count' => (int) ($template->enrolled_count ?? 0),
                'available_slots' => $template->remainingSlots(),
            ];
        }

        $this->dispatch('practice-template-applied', [
            'name' => $payload['name'] ?? $templateKey,
        ]);
    }

    public function deleteTemplate(int $templateId): void
    {
        $template = PracticeTemplate::where('user_id', auth()->id())->findOrFail($templateId);
        $template->delete();
        if ($this->selectedTemplateId === $templateId) {
            $this->selectedTemplateId = null;
        }
        $this->loadTemplates();
        $this->dispatch('practice-template-deleted');
    }

    private function loadTemplates(): void
    {
        $this->templates = PracticeTemplate::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();
    }

    protected function resolveSelectedCohortTemplateId(): ?int
    {
        if (! $this->selectedCohortTemplate || ! Str::startsWith($this->selectedCohortTemplate, 'db:')) {
            return null;
        }

        [, $id] = explode(':', $this->selectedCohortTemplate, 2);

        return $id ? (int) $id : null;
    }

    protected function markCohortTemplateAsPublished(bool $reloadTemplates = true, ?int $templateId = null): void
    {
        $templateId = $templateId ?? ($this->activeCohortTemplate['id'] ?? null) ?? $this->resolveSelectedCohortTemplateId();

        if (! $templateId) {
            return;
        }

        $cohort = CohortTemplate::find($templateId);

        if (! $cohort || $cohort->status === 'archived') {
            return;
        }

        if ($cohort->status !== 'published') {
            $cohort->status = 'published';
            $cohort->save();
        }

        if ($reloadTemplates) {
            $this->loadCohortTemplates();
        }

        if ($this->activeCohortTemplate && ($this->activeCohortTemplate['id'] ?? null) === $templateId) {
            $this->activeCohortTemplate['status'] = 'published';
            $this->activeCohortTemplate['available_slots'] = $cohort->remainingSlots();
            $this->activeCohortTemplate['enrolled_count'] = (int) ($cohort->enrolled_count ?? 0);
            $this->activeCohortTemplate['capacity'] = (int) $cohort->capacity;
        }
    }

    public function addTemplateSlot(): void
    {
        $this->templateSlots[] = [
            'weekday' => 'monday',
            'time' => '18:00',
        ];
    }

    public function removeTemplateSlot(int $index): void
    {
        if (! isset($this->templateSlots[$index])) {
            return;
        }

        unset($this->templateSlots[$index]);
        $this->templateSlots = array_values($this->templateSlots);
        if (empty($this->templateSlots)) {
            $this->templateSlots = [
                [
                    'weekday' => 'monday',
                    'time' => '18:00',
                ],
            ];
        }
    }

    public function scheduleTemplateSeries(): void
    {
        $data = $this->validate([
            'seriesForm.template_id' => ['required', Rule::exists('practice_templates', 'id')->where('user_id', auth()->id())],
            'seriesForm.start_date' => ['required', 'date'],
            'seriesForm.weeks' => ['required', 'integer', 'min:1', 'max:12'],
        ], [], [
            'seriesForm.template_id' => __('plantilla'),
            'seriesForm.start_date' => __('fecha de inicio'),
            'seriesForm.weeks' => __('cantidad de semanas'),
        ]);

        /** @var PracticeTemplate $template */
        $template = PracticeTemplate::where('user_id', auth()->id())->findOrFail($data['seriesForm']['template_id']);
        $payload = $template->payload ?? [];
        $slots = collect($this->normalizeTemplateSlots($payload['slots'] ?? []))
            ->filter(fn ($slot) => ! empty($slot['weekday']) && ! empty($slot['time']))
            ->values();

        if ($slots->isEmpty()) {
            $this->addError('templateSlots', __('La plantilla no tiene bloques configurados.'));

            return;
        }

        $lessonId = $payload['lesson_id'] ?? null;
        if (! $lessonId || ! Lesson::whereKey($lessonId)->exists()) {
            $this->addError('seriesForm.template_id', __('La plantilla no tiene una lección válida.'));

            return;
        }

        $baseDate = Carbon::parse($data['seriesForm']['start_date'], config('app.timezone'))->startOfDay();
        $scheduled = 0;

        foreach (range(0, $data['seriesForm']['weeks'] - 1) as $weekIndex) {
            foreach ($slots as $slot) {
                $startAt = $this->resolveSlotDate($baseDate, $slot, $weekIndex);

                if ($startAt->isPast()) {
                    $startAt = now()->addHour();
                }

                $endAt = $startAt->copy()->addMinutes((int) ($payload['duration_minutes'] ?? 60));

                $practice = DiscordPractice::create([
                    'lesson_id' => $lessonId,
                    'title' => $payload['title'] ?? $template->name,
                    'description' => $payload['description'] ?? null,
                    'type' => $payload['type'] ?? 'cohort',
                    'cohort_label' => $payload['cohort_label'] ?? null,
                    'practice_package_id' => $payload['practice_package_id'] ?? null,
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'duration_minutes' => (int) ($payload['duration_minutes'] ?? 60),
                    'capacity' => (int) ($payload['capacity'] ?? 10),
                    'discord_channel_url' => $payload['discord_channel_url'] ?? null,
                    'created_by' => auth()->id(),
                    'requires_package' => (bool) ($payload['requires_package'] ?? false),
                ]);

                $scheduled++;

                event(new DiscordPracticeScheduled(
                    $practice->fresh(['lesson.chapter.course', 'creator'])
                ));
            }
        }

        $this->loadPractices();
        $this->dispatch('practice-series-scheduled', ['count' => $scheduled]);

        $this->seriesForm['template_id'] = null;
    }

    public function duplicatePractice(int $practiceId, int $daysOffset = 7): void
    {
        $original = DiscordPractice::findOrFail($practiceId);
        $start = $original->start_at?->copy()->addDays($daysOffset) ?? now()->addDays($daysOffset);

        if ($start->isPast()) {
            $start = now()->addHours(1);
        }

        $end = $original->end_at ? $start->copy()->addMinutes($original->duration_minutes ?: 60) : null;

        $duplicate = DiscordPractice::create([
            'lesson_id' => $original->lesson_id,
            'title' => $original->title,
            'description' => $original->description,
            'type' => $original->type,
            'cohort_label' => $original->cohort_label,
            'practice_package_id' => $original->practice_package_id,
            'start_at' => $start,
            'end_at' => $end,
            'duration_minutes' => $original->duration_minutes,
            'capacity' => $original->capacity,
            'discord_channel_url' => $original->discord_channel_url,
            'created_by' => auth()->id(),
            'requires_package' => $original->requires_package,
        ]);

        $this->loadPractices();
        $this->dispatch('practice-duplicated');

        event(new DiscordPracticeScheduled(
            $duplicate->fresh(['lesson.chapter.course', 'creator'])
        ));
    }

    public function duplicateWeekSeries(): void
    {
        $data = $this->validate([
            'weekDuplicationForm.offset' => ['required', 'integer', 'min:1', 'max:12'],
            'weekDuplicationForm.repeat' => ['required', 'integer', 'min:1', 'max:6'],
        ], [], [
            'weekDuplicationForm.offset' => __('semanas hacia adelante'),
            'weekDuplicationForm.repeat' => __('cantidad de repeticiones'),
        ])['weekDuplicationForm'];

        $weekStart = Carbon::parse($this->calendarRangeStart)->startOfDay();
        $weekEnd = Carbon::parse($this->calendarRangeEnd)->endOfDay();

        $basePractices = DiscordPractice::whereBetween('start_at', [$weekStart, $weekEnd])->get();

        if ($basePractices->isEmpty()) {
            $this->addError('weekDuplicationForm', __('No hay prácticas en la semana seleccionada para duplicar.'));

            return;
        }

        $created = 0;

        foreach (range(0, $data['repeat'] - 1) as $iteration) {
            $offsetWeeks = $data['offset'] + $iteration;
            foreach ($basePractices as $practice) {
                $start = optional($practice->start_at)->copy()->addWeeks($offsetWeeks) ?? $weekStart->copy()->addWeeks($offsetWeeks);

                if ($start->isPast()) {
                    $start = now()->addHours(1);
                }

                $end = $practice->end_at ? $practice->end_at->copy()->addWeeks($offsetWeeks) : null;

                $duplicate = DiscordPractice::create([
                    'lesson_id' => $practice->lesson_id,
                    'title' => $practice->title,
                    'description' => $practice->description,
                    'type' => $practice->type,
                    'cohort_label' => $practice->cohort_label,
                    'practice_package_id' => $practice->practice_package_id,
                    'start_at' => $start,
                    'end_at' => $end,
                    'duration_minutes' => $practice->duration_minutes,
                    'capacity' => $practice->capacity,
                    'discord_channel_url' => $practice->discord_channel_url,
                    'created_by' => auth()->id(),
                    'requires_package' => $practice->requires_package,
                ]);

                $created++;

                event(new DiscordPracticeScheduled(
                    $duplicate->fresh(['lesson.chapter.course', 'creator'])
                ));
            }
        }

        $this->weekDuplicationForm = [
            'offset' => 1,
            'repeat' => 1,
        ];

        $this->loadPractices();
        $this->dispatch('practice-week-duplicated', ['count' => $created]);
    }

    public function render()
    {
        return view('livewire.professor.discord-practice-planner', [
            'cohortTemplates' => $this->cohortTemplates,
        ]);
    }

    private function loadCohortTemplates(): void
    {
        $configTemplates = collect(config('practice.cohort_templates', []))
            ->mapWithKeys(fn ($preset, $key) => [
                "config:{$key}" => array_merge($preset, [
                    'source' => 'config',
                    'key' => $key,
                ]),
            ]);

        $databaseTemplates = CohortTemplate::with('product')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (CohortTemplate $template) => [
                "db:{$template->id}" => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'description' => $template->description,
                    'type' => $template->type,
                    'cohort_label' => $template->cohort_label,
                    'duration_minutes' => $template->duration_minutes,
                    'capacity' => $template->capacity,
                    'requires_package' => $template->requires_package,
                    'practice_package_id' => $template->practice_package_id,
                    'slots' => $template->slots,
                    'price_amount' => (float) $template->price_amount,
                    'price_currency' => $template->price_currency,
                    'status' => $template->status,
                    'is_featured' => (bool) $template->is_featured,
                    'product_id' => $template->product?->id,
                    'source' => 'database',
                    'enrolled_count' => (int) ($template->enrolled_count ?? 0),
                    'available_slots' => $template->remainingSlots(),
                ],
            ]);

        $this->cohortTemplates = $configTemplates
            ->union($databaseTemplates)
            ->all();
    }

    private function normalizeTemplateSlots(array $slots): array
    {
        $normalized = collect($slots)
            ->map(function ($slot) {
                $weekday = strtolower((string) ($slot['weekday'] ?? 'monday'));
                $time = $slot['time'] ?? '18:00';

                return [
                    'weekday' => in_array($weekday, array_keys($this->weekdayMap()), true) ? $weekday : 'monday',
                    'time' => preg_match('/^\d{2}:\d{2}$/', $time) ? $time : '18:00',
                ];
            })
            ->values()
            ->all();

        return empty($normalized) ? [
            ['weekday' => 'monday', 'time' => '18:00'],
        ] : $normalized;
    }

    private function resolveSlotDate(Carbon $baseDate, array $slot, int $weekOffset): Carbon
    {
        $weekdayIndex = $this->weekdayMap()[$slot['weekday']] ?? 0;
        $weekStart = $baseDate->copy()->startOfWeek()->addWeeks($weekOffset);

        $target = $weekStart->copy()->addDays($weekdayIndex);
        $target->setTimeFromTimeString($slot['time']);

        return $target;
    }

    private function weekdayMap(): array
    {
        return [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6,
        ];
    }
}


