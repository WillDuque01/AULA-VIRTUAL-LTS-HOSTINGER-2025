<?php

namespace App\Livewire\Admin;

use App\Models\Chapter;
use App\Models\Lesson;
use App\Models\PracticePackage;
use App\Models\TeacherSubmission;
use App\Notifications\TeacherSubmissions\TeacherSubmissionStatusChangedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class TeacherSubmissionsHub extends Component
{
    public string $status = 'pending';
    public array $feedback = [];

    public function setStatus(string $status): void
    {
        $this->status = in_array($status, ['pending', 'approved', 'rejected'], true) ? $status : 'pending';
    }

    public function approve(int $submissionId): void
    {
        $submission = TeacherSubmission::with(['author', 'result'])->findOrFail($submissionId);

        if ($submission->status !== 'pending') {
            return;
        }

        $submission->status = 'approved';
        $submission->approved_by = Auth::id();
        $submission->approved_at = now();
        $submission->feedback = $submission->feedback ?: __('Aprobado y publicado');
        $submission->save();

        $this->updateResultStatus($submission, 'published');
        $this->notifyAuthor($submission);

        $this->dispatch('teacher-submissions:updated');
    }

    public function reject(int $submissionId): void
    {
        $submission = TeacherSubmission::with(['author', 'result'])->findOrFail($submissionId);

        if ($submission->status !== 'pending') {
            return;
        }

        $submission->status = 'rejected';
        $submission->approved_by = Auth::id();
        $submission->approved_at = now();
        $submission->feedback = $this->feedback[$submissionId] ?? __('Rechazado por el equipo académico');
        $submission->save();

        $this->updateResultStatus($submission, 'rejected');
        $this->notifyAuthor($submission);

        unset($this->feedback[$submissionId]);
        $this->dispatch('teacher-submissions:updated');
    }

    protected function updateResultStatus(TeacherSubmission $submission, string $status): void
    {
        $result = $submission->result;
        if (! $result) {
            return;
        }

        $normalized = $status === 'published' ? 'published' : $status;

        if ($result instanceof Chapter || $result instanceof Lesson || $result instanceof PracticePackage) {
            $result->status = $normalized;
            $result->save();
        }
    }

    protected function notifyAuthor(TeacherSubmission $submission): void
    {
        if (! $submission->author) {
            return;
        }

        Notification::send(
            $submission->author,
            new TeacherSubmissionStatusChangedNotification($submission)
        );
    }

    public function render()
    {
        $query = TeacherSubmission::with([
                'author',
                'course' => fn ($builder) => $builder->with(['i18n' => fn ($q) => $q->where('locale', app()->getLocale())]),
                'chapter',
                'result',
            ])
            ->when($this->status, fn ($builder) => $builder->where('status', $this->status))
            ->latest();

        return view('livewire.admin.teacher-submissions-hub', [
            'submissions' => $query->paginate(20),
        ]);
    }
}

