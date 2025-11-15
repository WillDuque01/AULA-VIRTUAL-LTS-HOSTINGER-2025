<?php

namespace App\Livewire\Lessons;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AssignmentPanel extends Component
{
    public Lesson $lesson;

    public Assignment $assignment;

    public ?AssignmentSubmission $submission = null;

    public string $body = '';

    public ?string $attachmentUrl = null;

    public bool $submitted = false;

    public function mount(Lesson $lesson): void
    {
        $this->lesson = $lesson;
        $this->assignment = Assignment::firstOrCreate(
            ['lesson_id' => $lesson->id],
            [
                'instructions' => data_get($lesson->config, 'instructions', ''),
                'due_at' => data_get($lesson->config, 'due_at'),
                'max_points' => data_get($lesson->config, 'max_points', 100),
                'rubric' => data_get($lesson->config, 'rubric', []),
            ]
        );
        $this->lesson->setRelation('assignment', $this->assignment);

        $this->loadSubmission();
    }

    public function submit(): void
    {
        abort_unless(Auth::check(), 403);

        $data = $this->validate([
            'body' => ['required', 'string', 'min:10'],
            'attachmentUrl' => ['nullable', 'url'],
        ], [], [
            'body' => 'respuesta',
            'attachmentUrl' => 'enlace',
        ]);

        $submission = AssignmentSubmission::updateOrCreate(
            [
                'assignment_id' => $this->assignment->id,
                'user_id' => Auth::id(),
            ],
            [
                'body' => $data['body'],
                'attachment_url' => $data['attachmentUrl'] ?? null,
                'status' => 'submitted',
                'max_points' => $this->assignment->max_points,
                'submitted_at' => now(),
            ]
        );

        $this->submission = $submission;
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.lessons.assignment-panel', [
            'assignment' => $this->assignment,
            'submission' => $this->submission,
        ]);
    }

    private function loadSubmission(): void
    {
        if (! Auth::check()) {
            return;
        }

        $this->submission = AssignmentSubmission::where('assignment_id', $this->assignment->id)
            ->where('user_id', Auth::id())
            ->latest()
            ->first();

        if ($this->submission) {
            $this->body = (string) $this->submission->body;
            $this->attachmentUrl = $this->submission->attachment_url;
            $this->submitted = $this->submission->status !== 'draft';
        }
    }
}


