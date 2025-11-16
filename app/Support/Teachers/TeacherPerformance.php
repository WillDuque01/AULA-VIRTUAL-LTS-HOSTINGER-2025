<?php

namespace App\Support\Teachers;

use App\Models\Chapter;
use App\Models\Lesson;
use App\Models\PracticePackage;
use App\Models\TeacherSubmission;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TeacherPerformance
{
    public static function backlogByTeacher(int $limit = 5): Collection
    {
        return TeacherSubmission::select('user_id')
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved")
            ->selectRaw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected")
            ->whereNotNull('user_id')
            ->with('author:id,name,email')
            ->groupBy('user_id')
            ->orderByDesc(DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END)"))
            ->limit($limit)
            ->get();
    }

    public static function contentStatusTotals(): array
    {
        return [
            'modules' => self::statusCounts(Chapter::query()),
            'lessons' => self::statusCounts(Lesson::query()),
            'packs' => self::statusCounts(PracticePackage::query()),
        ];
    }

    public static function statusTrend(int $days = 14): Collection
    {
        $start = Carbon::now()->subDays($days - 1)->startOfDay();

        $submitted = TeacherSubmission::selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at', '>=', $start)
            ->groupBy('day')
            ->pluck('total', 'day');

        $approved = TeacherSubmission::selectRaw('DATE(approved_at) as day, COUNT(*) as total')
            ->whereNotNull('approved_at')
            ->where('approved_at', '>=', $start)
            ->where('status', 'approved')
            ->groupBy('day')
            ->pluck('total', 'day');

        $rejected = TeacherSubmission::selectRaw('DATE(approved_at) as day, COUNT(*) as total')
            ->whereNotNull('approved_at')
            ->where('status', 'rejected')
            ->where('approved_at', '>=', $start)
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(range(0, $days - 1))->map(function (int $offset) use ($start, $submitted, $approved, $rejected) {
            $day = $start->copy()->addDays($offset);
            $key = $day->toDateString();

            return [
                'day' => $day->translatedFormat('d M'),
                'submitted' => (int) ($submitted[$key] ?? 0),
                'approved' => (int) ($approved[$key] ?? 0),
                'rejected' => (int) ($rejected[$key] ?? 0),
            ];
        });
    }

    protected static function statusCounts($query): array
    {
        $defaults = [
            'pending' => 0,
            'published' => 0,
            'rejected' => 0,
        ];

        $counts = $query->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return array_merge($defaults, $counts);
    }
}

