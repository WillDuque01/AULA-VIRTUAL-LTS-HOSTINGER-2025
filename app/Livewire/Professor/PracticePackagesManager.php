<?php

namespace App\Livewire\Professor;

use App\Events\PracticePackagePublished;
use App\Models\Lesson;
use App\Models\PracticePackage;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;

class PracticePackagesManager extends Component
{
    public Collection $packages;
    public Collection $lessons;

    public array $form = [
        'title' => '',
        'subtitle' => '',
        'description' => '',
        'sessions_count' => 3,
        'price_amount' => 49,
        'price_currency' => 'USD',
        'lesson_id' => null,
        'delivery_platform' => 'discord',
        'delivery_url' => '',
        'is_global' => false,
        'visibility' => 'private',
    ];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $userId = auth()->id();

        $this->packages = PracticePackage::where('creator_id', $userId)
            ->latest()
            ->get();

        $this->lessons = Lesson::with('chapter.course')
            ->orderByDesc('updated_at')
            ->take(50)
            ->get();
    }

    public function savePackage(): void
    {
        $user = auth()->user();
        $canGlobal = $user?->hasAnyRole(['Admin', 'teacher_admin']);

        $data = $this->validate([
            'form.title' => ['required', 'string', 'max:140'],
            'form.subtitle' => ['nullable', 'string', 'max:140'],
            'form.description' => ['nullable', 'string', 'max:2000'],
            'form.sessions_count' => ['required', 'integer', 'min:1', 'max:30'],
            'form.price_amount' => ['required', 'numeric', 'min:0'],
            'form.price_currency' => ['required', 'string', 'size:3'],
            'form.lesson_id' => ['nullable', 'exists:lessons,id'],
            'form.delivery_platform' => ['required', Rule::in(['discord', 'zoom', 'meet', 'custom'])],
            'form.delivery_url' => ['nullable', 'url'],
            'form.is_global' => ['boolean'],
            'form.visibility' => ['required', Rule::in(['private', 'public'])],
        ])['form'];

        if (! $canGlobal) {
            $data['is_global'] = false;
            $data['visibility'] = 'private';
        }

        $package = PracticePackage::create([
            ...$data,
            'creator_id' => $user->id,
            'status' => 'draft',
        ]);

        $this->reset('form');
        $this->form['sessions_count'] = 3;
        $this->form['price_amount'] = 49;
        $this->form['price_currency'] = 'USD';
        $this->form['delivery_platform'] = 'discord';
        $this->form['visibility'] = $canGlobal ? 'public' : 'private';

        $this->loadData();

        $this->dispatch('package-created', id: $package->id);
    }

    public function publish(int $packageId): void
    {
        $package = PracticePackage::where('creator_id', auth()->id())->findOrFail($packageId);
        $package->update(['status' => 'published']);

        PracticePackagePublished::dispatch($package->fresh());
        $this->loadData();
    }

    public function archive(int $packageId): void
    {
        $package = PracticePackage::where('creator_id', auth()->id())->findOrFail($packageId);
        $package->update(['status' => 'archived']);

        $this->loadData();
    }

    public function render()
    {
        return view('livewire.professor.practice-packages-manager');
    }
}


