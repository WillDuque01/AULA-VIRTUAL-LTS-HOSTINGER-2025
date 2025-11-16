<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VideoProgress;
use App\Support\Analytics\TelemetryRecorder;
use App\Support\Analytics\VideoHeatmapRecorder;
use App\Support\Gamification\LessonCompletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoProgressController extends Controller
{
    public function __construct(
        private readonly VideoHeatmapRecorder $heatmapRecorder,
        private readonly LessonCompletionService $completionService,
        private readonly TelemetryRecorder $telemetryRecorder,
    ) {
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lesson_id' => 'required|integer',
            'source' => 'required|in:vimeo,cloudflare,youtube',
            'last_second' => 'required|integer|min:0',
            'watched_seconds' => 'nullable|integer|min:0',
            'duration' => 'nullable|integer|min:0',
        ]);

        $userId = Auth::id();

        $progress = VideoProgress::updateOrCreate(
            ['user_id' => $userId, 'lesson_id' => $data['lesson_id']],
            [
                'source' => $data['source'],
                'last_second' => $data['last_second'],
                'watched_seconds' => $data['watched_seconds'] ?? $data['last_second'],
            ]
        );

        $this->heatmapRecorder->record($progress, (int) $data['last_second']);
        $rewards = $this->completionService->handle($progress);
        $this->telemetryRecorder->recordPlayerTick($progress, [
            'provider' => $data['source'],
            'playback_seconds' => (int) $data['last_second'],
            'watched_seconds' => (int) ($data['watched_seconds'] ?? $data['last_second']),
            'video_duration' => $data['duration'] ?? null,
        ]);

        return response()->json([
            'ok' => true,
            'progress' => $progress->fresh(),
            'celebration' => (bool) $rewards,
            'rewards' => $rewards,
        ]);
    }
}
