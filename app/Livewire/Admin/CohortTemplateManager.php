<?php

namespace App\Livewire\Admin;

use App\Models\CohortTemplate;
use App\Models\PracticePackage;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class CohortTemplateManager extends Component
{
    public Collection $templates;

    public array $form = [
        'id' => null,
        'name' => '',
        'slug' => '',
        'description' => '',
        'type' => 'cohort',
        'cohort_label' => '',
        'duration_minutes' => 60,
        'capacity' => 10,
        'requires_package' => false,
        'practice_package_id' => null,
        'slots' => [
            ['weekday' => 'monday', 'time' => '09:00'],
        ],
    ];

    public array $weekdayOptions = [
        'monday' => 'Lunes',
        'tuesday' => 'Martes',
        'wednesday' => 'Miércoles',
        'thursday' => 'Jueves',
        'friday' => 'Viernes',
        'saturday' => 'Sábado',
        'sunday' => 'Domingo',
    ];

    public Collection $packages;

    public function mount(): void
    {
        $this->packages = PracticePackage::orderBy('title')->get();
        $this->loadTemplates();
    }

    public function render()
    {
        return view('livewire.admin.cohort-template-manager', [
            'weekdayOptions' => $this->weekdayOptions,
        ]);
    }

    public function edit(int $templateId): void
    {
        $template = CohortTemplate::findOrFail($templateId);
        $this->form = [
            'id' => $template->id,
            'name' => $template->name,
            'slug' => $template->slug,
            'description' => $template->description,
            'type' => $template->type,
            'cohort_label' => $template->cohort_label,
            'duration_minutes' => $template->duration_minutes,
            'capacity' => $template->capacity,
            'requires_package' => $template->requires_package,
            'practice_package_id' => $template->practice_package_id,
            'slots' => $this->normalizeSlots($template->slots),
        ];
    }

    public function addSlot(): void
    {
        $this->form['slots'][] = ['weekday' => 'monday', 'time' => '09:00'];
    }

    public function removeSlot(int $index): void
    {
        unset($this->form['slots'][$index]);
        $this->form['slots'] = array_values($this->form['slots']);
        if (empty($this->form['slots'])) {
            $this->form['slots'] = [['weekday' => 'monday', 'time' => '09:00']];
        }
    }

    public function resetForm(): void
    {
        $this->form = [
            'id' => null,
            'name' => '',
            'slug' => '',
            'description' => '',
            'type' => 'cohort',
            'cohort_label' => '',
            'duration_minutes' => 60,
            'capacity' => 10,
            'requires_package' => false,
            'practice_package_id' => null,
            'slots' => [
                ['weekday' => 'monday', 'time' => '09:00'],
            ],
        ];
    }

    public function save(): void
    {
        $data = $this->validate([
            'form.id' => ['nullable', 'exists:cohort_templates,id'],
            'form.name' => ['required', 'string', 'max:120'],
            'form.slug' => ['nullable', 'string', 'max:160'],
            'form.description' => ['nullable', 'string', 'max:500'],
            'form.type' => ['required', 'in:cohort,global'],
            'form.cohort_label' => ['nullable', 'string', 'max:120'],
            'form.duration_minutes' => ['required', 'integer', 'min:15', 'max:240'],
            'form.capacity' => ['required', 'integer', 'min:1', 'max:60'],
            'form.requires_package' => ['boolean'],
            'form.practice_package_id' => ['nullable', 'exists:practice_packages,id'],
            'form.slots' => ['required', 'array', 'min:1'],
            'form.slots.*.weekday' => ['required', 'in:'.implode(',', array_keys($this->weekdayOptions))],
            'form.slots.*.time' => ['required', 'date_format:H:i'],
        ], [], [
            'form.name' => __('nombre'),
            'form.slots.*.weekday' => __('día'),
            'form.slots.*.time' => __('hora'),
        ])['form'];

        $slug = $data['slug'] ?: Str::slug($data['name']);

        $payload = [
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'],
            'type' => $data['type'],
            'cohort_label' => $data['cohort_label'],
            'duration_minutes' => $data['duration_minutes'],
            'capacity' => $data['capacity'],
            'requires_package' => (bool) $data['requires_package'],
            'practice_package_id' => $data['practice_package_id'],
            'slots' => $this->normalizeSlots($data['slots']),
            'created_by' => auth()->id(),
        ];

        CohortTemplate::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $payload
        );

        $this->resetForm();
        $this->loadTemplates();
        $this->dispatch('notify', message: __('Plantilla guardada correctamente.'));
    }

    public function delete(int $templateId): void
    {
        CohortTemplate::findOrFail($templateId)->delete();
        if ($this->form['id'] === $templateId) {
            $this->resetForm();
        }
        $this->loadTemplates();
        $this->dispatch('notify', message: __('Plantilla eliminada.'));
    }

    protected function loadTemplates(): void
    {
        $this->templates = CohortTemplate::orderBy('name')->get();
    }

    private function normalizeSlots(array $slots): array
    {
        $normalized = collect($slots)
            ->map(function ($slot) {
                $weekday = strtolower((string) ($slot['weekday'] ?? 'monday'));
                $time = $slot['time'] ?? '09:00';

                return [
                    'weekday' => array_key_exists($weekday, $this->weekdayOptions) ? $weekday : 'monday',
                    'time' => preg_match('/^\d{2}:\d{2}$/', $time) ? $time : '09:00',
                ];
            })
            ->filter()
            ->values()
            ->all();

        return empty($normalized) ? [['weekday' => 'monday', 'time' => '09:00']] : $normalized;
    }
}


