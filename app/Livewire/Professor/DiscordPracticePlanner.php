<?php

namespace App\Livewire\Professor;

use App\Events\DiscordPracticeScheduled;
use App\Models\DiscordPractice;
use App\Models\Lesson;
use App\Models\PracticePackage;
use App\Models\PracticeTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
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

    public string $calendarRangeStart;
    public string $calendarRangeEnd;
    public array $calendarHours = [];

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

        $startOfWeek = now()->startOfWeek();
        $this->calendarRangeStart = $startOfWeek->toDateString();
        $this->calendarRangeEnd = $startOfWeek->copy()->addDays(6)->toDateString();
        $this->calendarHours = collect(range(7, 21))
            ->map(fn ($hour) => str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00')
            ->toArray();

        $this->loadPractices();
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
            'type' => $this->type,
            'cohort_label' => $this->cohort_label,
            'requires_package' => $this->requires_package,
            'practice_package_id' => $this->practice_package_id,
            'duration_minutes' => $this->duration_minutes,
            'capacity' => $this->capacity,
            'discord_channel_url' => $this->discord_channel_url,
            'description' => $this->description,
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

        $this->type = $payload['type'] ?? $this->type;
        $this->cohort_label = $payload['cohort_label'] ?? $this->cohort_label;
        $this->requires_package = (bool) ($payload['requires_package'] ?? false);
        $this->practice_package_id = $payload['practice_package_id'] ?? null;
        $this->duration_minutes = (int) ($payload['duration_minutes'] ?? $this->duration_minutes);
        $this->capacity = (int) ($payload['capacity'] ?? $this->capacity);
        $this->discord_channel_url = $payload['discord_channel_url'] ?? $this->discord_channel_url;
        $this->description = $payload['description'] ?? $this->description;

        $this->selectedTemplateId = $template->id;
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

    public function render()
    {
        return view('livewire.professor.discord-practice-planner');
    }
}


