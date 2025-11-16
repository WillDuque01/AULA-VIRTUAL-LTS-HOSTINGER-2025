<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TeacherManager extends Component
{
    public Collection $teachers;
    public Collection $courses;
    public array $selected = [];
    public string $search = '';
    public array $courseAssignments = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function updatedSearch(): void
    {
        $this->loadData();
    }

    public function toggleSelection(int $userId): void
    {
        if (in_array($userId, $this->selected, true)) {
            $this->selected = array_values(array_diff($this->selected, [$userId]));
        } else {
            $this->selected[] = $userId;
        }
    }

    public function selectAll(): void
    {
        $this->selected = $this->teachers->pluck('id')->all();
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function promoteSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        User::whereIn('id', $this->selected)->each(function (User $user) {
            $user->assignRole('teacher_admin');
            if (! $user->hasRole('teacher')) {
                $user->assignRole('teacher');
            }
        });

        $this->dispatch('teacher-manager:updated');
        $this->loadData();
    }

    public function demoteSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        User::whereIn('id', $this->selected)->each(function (User $user) {
            $user->removeRole('teacher_admin');
        });

        $this->dispatch('teacher-manager:updated');
        $this->loadData();
    }

    public function removeSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        DB::transaction(function () {
            User::whereIn('id', $this->selected)->each(function (User $user) {
                $user->removeRole('teacher_admin');
                $user->removeRole('teacher');
                $user->teachingCourses()->detach();
            });
        });

        $this->selected = [];
        $this->dispatch('teacher-manager:updated');
        $this->loadData();
    }

    public function saveCourseAssignment(int $userId): void
    {
        $courses = $this->courseAssignments[$userId] ?? [];

        $this->validate([
            "courseAssignments.$userId.*" => [
                'nullable',
                Rule::exists('courses', 'id'),
            ],
        ]);

        $user = User::where('id', $userId)->firstOrFail();
        $user->teachingCourses()->sync($courses);

        $this->dispatch('teacher-manager:updated');
        $this->loadData();
    }

    protected function loadData(): void
    {
        $query = User::query()
            ->role(['teacher', 'teacher_admin'])
            ->with(['roles', 'teachingCourses:id,slug'])
            ->orderBy('name');

        if ($this->search !== '') {
            $query->where(function ($inner) {
                $inner->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        $this->teachers = $query->limit(50)->get();
        $this->courses = Course::orderBy('slug')->get(['id', 'slug']);

        $this->courseAssignments = $this->teachers
            ->mapWithKeys(fn (User $teacher) => [$teacher->id => $teacher->teachingCourses->pluck('id')->all()])
            ->all();
    }

    public function render()
    {
        return view('livewire.admin.teacher-manager');
    }
}

