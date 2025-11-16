<?php

namespace App\Livewire\Admin;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\PracticePackage;
use App\Models\TeacherSubmission;
use App\Models\User;
use App\Notifications\TeacherSubmissions\TeacherSubmissionStatusChangedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;
use Livewire\WithPagination;

class TeacherSubmissionsHub extends Component
{
    use WithPagination;

    public string $status = 'pending';
    public array $feedback = [];

    public array $filters = [
        'teacher_id' => '',
        'course_id' => '',
        'type' => 'all',
        'content_status' => 'all',
        'date_from' => null,
        'date_to' => null,
    ];

    protected $queryString = [
        'status' => ['except' => 'pending'],
    ];

    public function setStatus(string $status): void
    {
        $this->status = in_array($status, ['pending', 'approved', 'rejected'], true) ? $status : 'pending';
        $this->resetPage();
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
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
                'history' => fn ($builder) => $builder->latest()->limit(10),
            ])
            ->when($this->status, fn ($builder) => $builder->where('status', $this->status))
            ->when($this->filters['teacher_id'], fn ($builder, $teacherId) => $builder->where('user_id', $teacherId))
            ->when($this->filters['course_id'], fn ($builder, $courseId) => $builder->where('course_id', $courseId))
            ->when($this->filters['type'] !== 'all', fn ($builder) => $builder->where('type', $this->filters['type']))
            ->when($this->filters['content_status'] !== 'all', function ($builder) {
                $status = $this->filters['content_status'];

                $builder->whereHas('result', fn ($relation) => $relation->where('status', $status));
            })
            ->when($this->filters['date_from'], fn ($builder, $date) => $builder->whereDate('created_at', '>=', Carbon::parse($date)))
            ->when($this->filters['date_to'], fn ($builder, $date) => $builder->whereDate('created_at', '<=', Carbon::parse($date)))
            ->latest();

        return view('livewire.admin.teacher-submissions-hub', [
            'submissions' => $query->paginate(20),
            'teacherOptions' => User::role('teacher')->orderBy('name')->get(['id', 'name']),
            'courseOptions' => Course::orderBy('slug')->get(['id', 'slug']),
        ]);
    }
}

