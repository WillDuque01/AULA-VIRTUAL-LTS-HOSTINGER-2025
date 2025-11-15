<?php

namespace App\Livewire\Admin;

use App\Models\PaymentEvent;
use App\Models\Subscription;
use App\Models\User;
use App\Models\VideoProgress;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class Dashboard extends Component
{
    public array $metrics = [
        'users' => 0,
        'active_subscriptions' => 0,
        'mrr' => 0.0,
        'watch_hours' => 0.0,
    ];

    public array $integrationStatus = [];

    public Collection $revenueTrend;

    public Collection $watchPerCourse;

    public function mount(): void
    {
        $this->revenueTrend = collect();
        $this->watchPerCourse = collect();
        $this->loadMetrics();
    }

    private function loadMetrics(): void
    {
        $this->metrics['users'] = User::count();
        $this->metrics['active_subscriptions'] = Subscription::where('status', 'active')->count();
        $this->metrics['mrr'] = (float) PaymentEvent::where('created_at', '>=', now()->subDays(30))->sum('amount');
        $this->metrics['watch_hours'] = round((VideoProgress::sum('watched_seconds') ?? 0) / 3600, 1);

        $this->integrationStatus = config('integrations.status', []);

        $this->revenueTrend = PaymentEvent::selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->where('created_at', '>=', Carbon::now()->subDays(14))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(function ($row) {
                return [
                    'day' => Carbon::parse($row->day)->format('d M'),
                    'total' => (float) $row->total,
                ];
            });

        $this->watchPerCourse = VideoProgress::selectRaw('courses.slug as course, SUM(video_progress.watched_seconds) as total_seconds')
            ->join('lessons', 'video_progress.lesson_id', '=', 'lessons.id')
            ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
            ->join('courses', 'chapters.course_id', '=', 'courses.id')
            ->groupBy('courses.slug')
            ->orderByDesc('total_seconds')
            ->get()
            ->map(function ($row) {
                return [
                    'course' => $row->course,
                    'hours' => round(($row->total_seconds ?? 0) / 3600, 1),
                ];
            })
            ->take(5);
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
