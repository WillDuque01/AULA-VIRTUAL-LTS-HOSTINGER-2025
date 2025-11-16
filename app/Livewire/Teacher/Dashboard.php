<?php

namespace App\Livewire\Teacher;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\PracticePackage;
use App\Models\TeacherSubmission;
use App\Models\User;
use App\Notifications\TeacherSubmissions\TeacherSubmissionCreatedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Dashboard extends Component
{
    public Collection $courses;
    public Collection $submissions;
    public Collection $chapters;

    public array $form = [
        'type' => 'lesson',
        'course_id' => null,
        'chapter_id' => null,
        'title' => '',
        'summary' => '',
        'lesson_type' => 'video',
        'estimated_minutes' => 10,
        'pack_sessions' => 4,
        'pack_price' => 59,
        'pack_currency' => 'USD',
        'notes' => '',
    ];

    public bool $showSubmissionModal = false;

    public function mount(): void
    {
        $this->loadData();
    }

    public function updatedFormCourseId(): void
    {
        $this->loadChapters();
    }

    protected function loadData(): void
    {
        $user = Auth::user();

        $this->courses = $user?->teachingCourses()
            ->with([
                'chapters' => fn ($query) => $query->select('id', 'course_id', 'title'),
                'i18n' => fn ($query) => $query->where('locale', app()->getLocale()),
            ])
            ->orderBy('slug')
            ->get() ?? collect();

        $this->submissions = $user?->teacherSubmissions()
            ->with([
                'result',
                'history' => fn ($query) => $query->orderByDesc('created_at')->limit(10),
            ])
            ->latest()
            ->limit(25)
            ->get() ?? collect();

        $this->loadChapters();
    }

    protected function loadChapters(): void
    {
        $courseId = $this->form['course_id'];

        if (! $courseId) {
            $this->chapters = collect();

            return;
        }

        $this->chapters = Chapter::query()
            ->where('course_id', $courseId)
            ->orderBy('position')
            ->get(['id', 'course_id', 'title']);
    }

    public function openSubmissionModal(string $type): void
    {
        $this->resetErrorBag();
        $this->form['type'] = $type;
        if (! $this->form['course_id'] && $this->courses->count()) {
            $this->form['course_id'] = $this->courses->first()->id;
            $this->loadChapters();
        }
        $this->form['title'] = '';
        $this->form['summary'] = '';
        $this->form['notes'] = '';
        $this->form['lesson_type'] = 'video';
        $this->form['estimated_minutes'] = 10;
        $this->form['pack_sessions'] = 4;
        $this->form['pack_price'] = 59;
        $this->form['pack_currency'] = 'USD';
        $this->showSubmissionModal = true;
    }

    public function submitProposal(): void
    {
        $user = Auth::user();

        $data = $this->validate($this->rules())['form'];
        $course = $user?->teachingCourses()->whereKey($data['course_id'])->first();

        if (! $course) {
            $this->addError('form.course_id', __('Selecciona un curso válido.'));

            return;
        }

        if ($data['type'] === 'lesson') {
            $chapter = Chapter::where('course_id', $course->id)->whereKey($data['chapter_id'])->first();

            if (! $chapter) {
                $this->addError('form.chapter_id', __('Selecciona un módulo válido.'));

                return;
            }
        }

        $result = $this->createPendingResource($user, $data);

        $submission = TeacherSubmission::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'chapter_id' => $data['type'] === 'lesson' ? $data['chapter_id'] : null,
            'type' => $data['type'],
            'title' => $data['title'],
            'summary' => $data['summary'] ?? null,
            'payload' => [
                'lesson_type' => $data['lesson_type'] ?? null,
                'estimated_minutes' => $data['estimated_minutes'] ?? null,
                'pack_sessions' => $data['pack_sessions'] ?? null,
                'pack_price' => $data['pack_price'] ?? null,
                'pack_currency' => $data['pack_currency'] ?? null,
                'notes' => $data['notes'] ?? null,
            ],
            'result_type' => $result['type'],
            'result_id' => $result['id'],
            'status' => 'pending',
        ]);

        $this->notifyReviewers($submission);

        $this->showSubmissionModal = false;
        $this->reset('form');
        $this->form['type'] = 'lesson';
        $this->form['lesson_type'] = 'video';
        $this->form['estimated_minutes'] = 10;
        $this->form['pack_sessions'] = 4;
        $this->form['pack_price'] = 59;
        $this->form['pack_currency'] = 'USD';
        $this->loadData();

        $this->dispatch('teacher-proposal:created');
    }

    protected function rules(): array
    {
        return [
            'form.type' => ['required', Rule::in(['module', 'lesson', 'pack'])],
            'form.course_id' => ['required', 'exists:courses,id'],
            'form.title' => ['required', 'string', 'max:140'],
            'form.summary' => ['nullable', 'string', 'max:500'],
            'form.chapter_id' => [
                Rule::requiredIf(fn () => $this->form['type'] === 'lesson'),
                'nullable',
                'exists:chapters,id',
            ],
            'form.lesson_type' => [
                Rule::requiredIf(fn () => $this->form['type'] === 'lesson'),
                Rule::in(['video', 'text', 'pdf', 'quiz', 'assignment']),
            ],
            'form.estimated_minutes' => [
                Rule::requiredIf(fn () => $this->form['type'] === 'lesson'),
                'nullable',
                'integer',
                'min:1',
                'max:600',
            ],
            'form.pack_sessions' => [
                Rule::requiredIf(fn () => $this->form['type'] === 'pack'),
                'nullable',
                'integer',
                'min:1',
                'max:30',
            ],
            'form.pack_price' => [
                Rule::requiredIf(fn () => $this->form['type'] === 'pack'),
                'nullable',
                'numeric',
                'min:0',
            ],
            'form.pack_currency' => [
                Rule::requiredIf(fn () => $this->form['type'] === 'pack'),
                'nullable',
                'string',
                'size:3',
            ],
            'form.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function render()
    {
        return view('livewire.teacher.dashboard');
    }

    protected function createPendingResource(User $user, array $data): array
    {
        return match ($data['type']) {
            'module' => $this->createPendingChapter($user, $data),
            'pack' => $this->createPendingPack($user, $data),
            default => $this->createPendingLesson($user, $data),
        };
    }

    protected function createPendingChapter(User $user, array $data): array
    {
        $position = (int) (Chapter::where('course_id', $data['course_id'])->max('position') ?? 0) + 1;

        $chapter = Chapter::create([
            'course_id' => $data['course_id'],
            'title' => $data['title'],
            'position' => $position,
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        return ['type' => Chapter::class, 'id' => $chapter->id];
    }

    protected function createPendingLesson(User $user, array $data): array
    {
        $position = (int) (Lesson::where('chapter_id', $data['chapter_id'])->max('position') ?? 0) + 1;
        $config = [
            'title' => $data['title'],
            'badge' => null,
            'estimated_minutes' => (int) ($data['estimated_minutes'] ?? 10),
            'body' => $data['summary'] ?? null,
            'source' => $data['lesson_type'] === 'video' ? 'youtube' : null,
            'video_id' => null,
        ];

        $lesson = Lesson::create([
            'chapter_id' => $data['chapter_id'],
            'type' => $data['lesson_type'] ?? 'text',
            'config' => $config,
            'position' => $position,
            'locked' => false,
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        return ['type' => Lesson::class, 'id' => $lesson->id];
    }

    protected function createPendingPack(User $user, array $data): array
    {
        $pack = PracticePackage::create([
            'creator_id' => $user->id,
            'lesson_id' => null,
            'title' => $data['title'],
            'subtitle' => $data['summary'],
            'description' => $data['notes'],
            'sessions_count' => $data['pack_sessions'] ?? 3,
            'price_amount' => $data['pack_price'] ?? 0,
            'price_currency' => $data['pack_currency'] ?? 'USD',
            'is_global' => false,
            'visibility' => 'private',
            'delivery_platform' => 'discord',
            'delivery_url' => null,
            'status' => 'pending',
        ]);

        return ['type' => PracticePackage::class, 'id' => $pack->id];
    }

    protected function notifyReviewers(TeacherSubmission $submission): void
    {
        $reviewers = User::role(['Admin', 'teacher_admin'])->get();
        if ($reviewers->isEmpty()) {
            return;
        }

        Notification::send($reviewers, new TeacherSubmissionCreatedNotification($submission));
    }
}

