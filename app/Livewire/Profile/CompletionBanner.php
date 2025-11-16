<?php

namespace App\Livewire\Profile;

use App\Support\Profile\ProfileCompletion;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CompletionBanner extends Component
{
    public array $summary = [];

    public array $form = [
        'first_name' => '',
        'last_name' => '',
        'phone' => '',
        'country' => '',
        'state' => '',
        'city' => '',
        'headline' => '',
        'bio' => '',
        'teaching_since' => '',
        'specialties_input' => '',
        'languages_input' => '',
        'certifications_input' => '',
        'linkedin_url' => '',
        'teacher_notes' => '',
    ];

    public ?string $expanded = null;

    public bool $isTeacher = false;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $this->summary = $user->profileSummary();
        $this->isTeacher = $user->hasAnyRole(['teacher', 'teacher_admin']);
        $this->form = array_merge($this->form, $user->only([
            'first_name',
            'last_name',
            'phone',
            'country',
            'state',
            'city',
            'headline',
            'bio',
            'teaching_since',
            'linkedin_url',
            'teacher_notes',
        ]));

        $this->form['specialties_input'] = implode(', ', $user->specialties ?? []);
        $this->form['languages_input'] = implode(', ', $user->languages ?? []);
        $this->form['certifications_input'] = implode(', ', $user->certifications ?? []);

        $firstIncomplete = collect($this->summary['steps'] ?? [])
            ->firstWhere('completed', false);

        $this->expanded = $firstIncomplete['key'] ?? 'basic';
    }

    public function saveSection(string $section): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $rules = $this->sectionRules($section);
        $data = $this->validate($rules, [], [
            'first_name' => __('First name'),
            'last_name' => __('Last name'),
            'phone' => __('Phone / WhatsApp'),
            'country' => __('Country'),
            'state' => __('State / Region'),
            'city' => __('City'),
        ]);

        $user->fill($data);

        if ($section === 'teacher') {
            $user->specialties = $this->stringToList($this->form['specialties_input']);
            $user->languages = $this->stringToList($this->form['languages_input']);
            $user->certifications = $this->stringToList($this->form['certifications_input']);
        }

        ProfileCompletion::syncDisplayName($user);
        ProfileCompletion::updateUserMetrics($user);
        $user->save();

        $this->form = array_merge($this->form, $user->only(array_keys($this->form)));
        $this->summary = $user->profileSummary();

        if ($this->summary['is_complete']) {
            $this->expanded = null;
        } else {
            $next = collect($this->summary['steps'])
                ->firstWhere('completed', false);
            $this->expanded = $next['key'] ?? $section;
        }

        $this->dispatch('notify', message: __('Perfil actualizado'));
    }

    public function dismiss(): void
    {
        $this->expanded = null;
        $this->summary['is_complete'] = true;
    }

    private function sectionRules(string $section): array
    {
        return match ($section) {
            'basic' => [
                'form.first_name' => ['required', 'string', 'max:120'],
                'form.last_name' => ['required', 'string', 'max:120'],
            ],
            'contact' => [
                'form.phone' => ['required', 'string', 'max:40'],
            ],
            'location' => [
                'form.country' => ['required', 'string', 'max:120'],
                'form.state' => ['required', 'string', 'max:120'],
                'form.city' => ['required', 'string', 'max:120'],
            ],
            'teacher' => [
                'form.headline' => ['required', 'string', 'max:140'],
                'form.bio' => ['nullable', 'string', 'max:1000'],
                'form.teaching_since' => ['nullable', 'string', 'max:10'],
                'form.linkedin_url' => ['nullable', 'url', 'max:255'],
                'form.teacher_notes' => ['nullable', 'string', 'max:1000'],
            ],
            default => [],
        };
    }

    private function stringToList(?string $value): array
    {
        return collect(preg_split('/[,;\n]+/', (string) $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.profile.completion-banner');
    }
}

