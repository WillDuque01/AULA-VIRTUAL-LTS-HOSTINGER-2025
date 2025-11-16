<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Support\Analytics\TelemetryRecorder;
use Illuminate\Http\Request;

class PlayerEventController extends Controller
{
    public function __construct(
        private readonly TelemetryRecorder $telemetryRecorder
    ) {
    }

    public function __invoke(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $data = $request->validate([
            'lesson_id' => 'required|integer|exists:lessons,id',
            'event' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9._-]+$/i'],
            'playback_seconds' => 'nullable|integer|min:0',
            'video_duration' => 'nullable|integer|min:0',
            'watched_seconds' => 'nullable|integer|min:0',
            'provider' => 'nullable|string|max:20',
            'context_tag' => 'nullable|string|max:50',
            'metadata' => 'nullable|array',
        ]);

        $lesson = Lesson::find($data['lesson_id']);

        $this->telemetryRecorder->recordPlayerEvent(
            $user->id,
            $lesson,
            [
                'event' => $data['event'],
                'provider' => $data['provider'] ?? null,
                'playback_seconds' => $data['playback_seconds'] ?? null,
                'watched_seconds' => $data['watched_seconds'] ?? null,
                'video_duration' => $data['video_duration'] ?? null,
                'context_tag' => $data['context_tag'] ?? 'player',
                'metadata' => $data['metadata'] ?? [],
            ]
        );

        return response()->json(['ok' => true]);
    }
}

