<?php

namespace App\Livewire\Admin;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AssignmentsManager extends Component
{
    public $assignments = [];

    public ?int $selectedAssignmentId = null;

    public array $submissions = [];

    public ?int $editingSubmissionId = null;

    public ?int $score = null;

    public ?string $feedback = null;

    public function mount(): void
    {
        abort_unless(Auth::user()?->can('manage-settings') || Auth::user()?->hasAnyRole(['teacher_admin', 'teacher']), 403);

        $this->assignments = Assignment::with('lesson')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn ($assignment) => [
                'id' => $assignment->id,
                'title' => data_get($assignment->lesson->config, 'title', 'Tarea'),
                'course' => $assignment->lesson->chapter?->course?->slug,
            ])
            ->toArray();

        $this->selectedAssignmentId = $this->assignments[0]['id'] ?? null;
        $this->loadSubmissions();
    }

    public function updatedSelectedAssignmentId(): void
    {
        $this->loadSubmissions();
    }

    public function editSubmission(int $submissionId): void
    {
        $submission = AssignmentSubmission::findOrFail($submissionId);
        $this->editingSubmissionId = $submissionId;
        $this->score = $submission->score;
        $this->feedback = $submission->feedback;
    }

    public function saveGrade(): void
    {
        $submission = AssignmentSubmission::with('assignment')->findOrFail($this->editingSubmissionId);

        $validated = $this->validate([
            'score' => ['required', 'integer', 'min:0', 'max:' . ($submission->assignment->max_points ?? 100)],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $submission->update([
            'score' => $validated['score'],
            'feedback' => $validated['feedback'],
            'status' => 'graded',
            'graded_at' => now(),
        ]);

        $this->editingSubmissionId = null;
        $this->loadSubmissions();

        session()->flash('status', 'Calificación guardada');
    }

    public function render()
    {
        return view('livewire.admin.assignments-manager');
    }

    private function loadSubmissions(): void
    {
        if (! $this->selectedAssignmentId) {
            $this->submissions = [];

            return;
        }

        $this->submissions = AssignmentSubmission::with('user')
            ->where('assignment_id', $this->selectedAssignmentId)
            ->latest('submitted_at')
            ->get()
            ->map(fn ($submission) => [
                'id' => $submission->id,
                'student' => $submission->user?->name,
                'status' => $submission->status,
                'score' => $submission->score,
                'submitted_at' => optional($submission->submitted_at)?->diffForHumans(),
                'body' => $submission->body,
                'attachment_url' => $submission->attachment_url,
            ])
            ->toArray();
    }
}


