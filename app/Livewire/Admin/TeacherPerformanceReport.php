<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use App\Models\TeacherSubmission;
use App\Models\User;
use App\Support\Teachers\TeacherPerformance;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class TeacherPerformanceReport extends Component
{
    use WithPagination;

    public array $filters = [
        'teacher_id' => '',
        'course_id' => '',
        'status' => 'all',
        'type' => 'all',
        'date_from' => null,
        'date_to' => null,
    ];

    public array $summary = [
        'total' => 0,
        'pending' => 0,
        'approved' => 0,
        'rejected' => 0,
        'avg_minutes' => 0,
    ];

    public function mount(): void
    {
        $this->filters['date_from'] = now()->subDays(14)->format('Y-m-d');
        $this->filters['date_to'] = now()->format('Y-m-d');
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $submissions = $this->filteredSubmissions();
        $grouped = $this->groupByTeacher($submissions);
        $paginated = $this->paginateGroups($grouped);

        return view('livewire.admin.teacher-performance-report', [
            'rows' => $paginated,
            'teachers' => User::role('teacher')->orderBy('name')->get(['id', 'name']),
            'courses' => Course::orderBy('slug')->get(['id', 'slug']),
            'trend' => TeacherPerformance::statusTrend(14),
        ]);
    }

    protected function filteredSubmissions(): Collection
    {
        $query = TeacherSubmission::with(['author:id,name,email', 'course:id,slug'])
            ->when($this->filters['teacher_id'], fn ($builder, $teacherId) => $builder->where('user_id', $teacherId))
            ->when($this->filters['course_id'], fn ($builder, $courseId) => $builder->where('course_id', $courseId))
            ->when($this->filters['status'] !== 'all', fn ($builder) => $builder->where('status', $this->filters['status']))
            ->when($this->filters['type'] !== 'all', fn ($builder) => $builder->where('type', $this->filters['type']))
            ->when($this->filters['date_from'], fn ($builder, $date) => $builder->whereDate('created_at', '>=', Carbon::parse($date)))
            ->when($this->filters['date_to'], fn ($builder, $date) => $builder->whereDate('created_at', '<=', Carbon::parse($date)))
            ->latest();

        $collection = $query->get();

        $this->summary['total'] = $collection->count();
        $this->summary['pending'] = $collection->where('status', 'pending')->count();
        $this->summary['approved'] = $collection->where('status', 'approved')->count();
        $this->summary['rejected'] = $collection->where('status', 'rejected')->count();

        $avgMinutes = $collection->where('status', 'approved')
            ->map(function (TeacherSubmission $submission) {
                if (! $submission->approved_at) {
                    return null;
                }

                return $submission->approved_at->diffInMinutes($submission->created_at);
            })
            ->filter()
            ->avg();

        $this->summary['avg_minutes'] = $avgMinutes ? round($avgMinutes, 1) : 0;

        return $collection;
    }

    protected function groupByTeacher(Collection $collection): Collection
    {
        return $collection
            ->groupBy('user_id')
            ->map(function (Collection $items) {
                $approved = $items->where('status', 'approved');
                $avgMinutes = $approved
                    ->map(function (TeacherSubmission $submission) {
                        return $submission->approved_at
                            ? $submission->approved_at->diffInMinutes($submission->created_at)
                            : null;
                    })
                    ->filter()
                    ->avg();

                return [
                    'teacher' => $items->first()->author,
                    'total' => $items->count(),
                    'pending' => $items->where('status', 'pending')->count(),
                    'approved' => $approved->count(),
                    'rejected' => $items->where('status', 'rejected')->count(),
                    'avg_minutes' => $avgMinutes ? round($avgMinutes, 1) : null,
                    'last_submission_at' => optional($items->max('created_at')),
                    'acceptance_rate' => $items->count() > 0
                        ? round($approved->count() / $items->count() * 100, 1)
                        : null,
                ];
            })
            ->sortByDesc('pending');
    }

    protected function paginateGroups(Collection $groups): LengthAwarePaginator
    {
        $perPage = 8;
        $page = $this->page ?? 1;
        $items = $groups->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $groups->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }
}


