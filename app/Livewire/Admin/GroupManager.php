<?php

namespace App\Livewire\Admin;

use App\Models\StudentGroup;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class GroupManager extends Component
{
    use WithPagination;

    public array $form = [];

    public string $studentSearch = '';

    public array $selectedStudents = [];

    public ?int $editingId = null;

    public array $tiers = [];

    public bool $showAssignModal = false;

    protected $queryString = [
        'studentSearch' => ['except' => ''],
    ];

    protected $rules = [
        'form.name' => ['required', 'string', 'max:255'],
        'form.slug' => ['nullable', 'string', 'max:255'],
        'form.tier_id' => ['nullable', 'exists:tiers,id'],
        'form.description' => ['nullable', 'string'],
        'form.capacity' => ['nullable', 'integer', 'min:1'],
        'form.starts_at' => ['nullable', 'date'],
        'form.ends_at' => ['nullable', 'date', 'after_or_equal:form.starts_at'],
        'form.is_active' => ['boolean'],
    ];

    public function mount(): void
    {
        $this->tiers = Tier::orderBy('priority')->orderBy('name')->get(['id', 'name', 'slug'])->toArray();
        $this->resetForm();
    }

    public function updatingStudentSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $groups = StudentGroup::query()
            ->withCount(['students as active_students_count' => function ($query) {
                $query->whereNull('group_user.left_at');
            }])
            ->with('tier:id,name,slug')
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        $students = User::query()
            ->when($this->studentSearch, function ($query, string $search) {
                $like = "%{$search}%";
                $query->where(function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.group-manager', [
            'groups' => $groups,
            'students' => $students,
        ]);
    }

    public function updatedFormName(string $value): void
    {
        if (! $this->editingId && blank($this->form['slug'])) {
            $this->form['slug'] = Str::slug($value);
        }
    }

    public function createGroup(): void
    {
        $this->resetForm();
    }

    public function editGroup(int $groupId): void
    {
        $group = StudentGroup::withCount(['students' => function ($query) {
            $query->whereNull('group_user.left_at');
        }])->findOrFail($groupId);

        $this->editingId = $group->id;
        $this->form = [
            'name' => $group->name,
            'slug' => $group->slug,
            'tier_id' => $group->tier_id,
            'description' => $group->description,
            'capacity' => $group->capacity,
            'starts_at' => optional($group->starts_at)->format('Y-m-d'),
            'ends_at' => optional($group->ends_at)->format('Y-m-d'),
            'is_active' => (bool) $group->is_active,
        ];
        $this->selectedStudents = $group->students()->whereNull('group_user.left_at')->pluck('id')->toArray();
    }

    public function saveGroup(): void
    {
        $rules = $this->rules;
        $rules['form.slug'][] = Rule::unique('student_groups', 'slug')->ignore($this->editingId);

        $validated = $this->validate($rules)['form'];

        if (blank($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        $group = $this->editingId ? StudentGroup::findOrFail($this->editingId) : new StudentGroup();
        $group->fill(Arr::except($validated, ['is_active']));
        $group->is_active = $validated['is_active'];
        $group->save();

        $this->syncStudents($group);

        $this->resetForm();
        session()->flash('status', __('Grupo guardado correctamente.'));
    }

    public function toggleActive(int $groupId): void
    {
        $group = StudentGroup::findOrFail($groupId);
        $group->is_active = ! $group->is_active;
        $group->save();

        if ($this->editingId === $groupId) {
            $this->form['is_active'] = $group->is_active;
        }

        session()->flash('status', $group->is_active ? __('Grupo activado.') : __('Grupo desactivado.'));
    }

    public function deleteGroup(int $groupId): void
    {
        $group = StudentGroup::findOrFail($groupId);

        if ($group->students()->exists()) {
            session()->flash('error', __('No puedes eliminar un grupo con estudiantes asignados.'));
            return;
        }

        $group->delete();

        if ($this->editingId === $groupId) {
            $this->resetForm();
        }

        session()->flash('status', __('Grupo eliminado correctamente.'));
    }

    public function openAssignModal(int $groupId): void
    {
        $this->editGroup($groupId);
        $this->showAssignModal = true;
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
    }

    public function toggleStudent(int $studentId): void
    {
        if (in_array($studentId, $this->selectedStudents, true)) {
            $this->selectedStudents = array_values(array_diff($this->selectedStudents, [$studentId]));
        } else {
            $this->selectedStudents[] = $studentId;
        }
    }

    public function assignSelected(): void
    {
        if (! $this->editingId) {
            return;
        }

        $group = StudentGroup::findOrFail($this->editingId);
        $this->syncStudents($group);
        $this->showAssignModal = false;
        session()->flash('status', __('Asignaciones actualizadas.'));
    }

    private function syncStudents(StudentGroup $group): void
    {
        $payload = collect($this->selectedStudents)
            ->mapWithKeys(fn ($studentId) => [$studentId => ['assigned_by' => auth()->id(), 'joined_at' => now(), 'metadata' => null]])
            ->all();

        $group->students()->sync($payload);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->showAssignModal = false;
        $this->selectedStudents = [];
        $this->form = [
            'name' => '',
            'slug' => '',
            'tier_id' => $this->tiers[0]['id'] ?? null,
            'description' => '',
            'capacity' => null,
            'starts_at' => null,
            'ends_at' => null,
            'is_active' => true,
        ];
    }
}
